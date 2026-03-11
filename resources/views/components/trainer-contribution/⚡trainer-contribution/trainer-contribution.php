<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

return new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $position_filter = [];
    public $date_from;
    public $date_to;
    public $showDetailModal = false;
    public $selectedTrainerName = '';
    public $trainerDetails = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectAllPositions()
    {
        $this->position_filter = DB::table('positions')->pluck('position_name')->toArray();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->position_filter = [];
        $this->date_from = null;
        $this->date_to = null;
        $this->resetPage();
    }

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    // 🔥 Fungsi untuk mengambil detail riwayat mengajar
    public function showDetail($trainerName)
    {
        $this->selectedTrainerName = $trainerName;

        $this->trainerDetails = DB::table('trainings as t')
            ->leftJoin('employees as tr', 't.trainer_employee_id', '=', 'tr.id')
            ->where(function ($q) use ($trainerName) {
                $q->where('tr.name', $trainerName)
                    ->orWhere('t.trainer_external_name', $trainerName);
            })
            ->when($this->date_from, fn($q) => $q->whereDate('training_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('training_date', '<=', $this->date_to))
            ->select(
                'title',
                'training_date',
                'start_time',
                'finish_time',
                DB::raw('TIMESTAMPDIFF(MINUTE, start_time, finish_time) as minutes')
            )
            ->orderBy('training_date', 'desc')
            ->get();

        $this->showDetailModal = true;
    }

    private function getTrainerList()
{
    return DB::table('trainings as t')
        ->leftJoin('employees as tr', 't.trainer_employee_id', '=', 'tr.id')
        ->select(DB::raw('COALESCE(tr.name, t.trainer_external_name) as name'))
        ->distinct()
        ->whereNotNull(DB::raw('COALESCE(tr.name, t.trainer_external_name)'))
        ->orderBy('name', 'asc')
        ->get();
}

    private function getBaseQuery()
{
    // Mode Monitoring: Jika Jabatan dipilih (Manager/Supervisor)
    if (!empty($this->position_filter)) {
        return DB::table('employees as e')
            ->leftJoin('positions as p', 'e.position_id', '=', 'p.id')
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->leftJoin('trainings as t', 'e.id', '=', 't.trainer_employee_id')
            ->select(
                'e.name as trainer_name',
                'e.nik',
                DB::raw('COALESCE(p.position_name, "-") as position'),
                DB::raw('COALESCE(o.org_name, "-") as organization'),
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT t.activity_name SEPARATOR ', '), '-') as activity_name"),
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT t.skill_name SEPARATOR ', '), '-') as skill_name"), // Kolom ini sudah ada
                DB::raw('SUM(COALESCE(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time), 0)) as total_minutes')
            )
            ->whereIn('p.position_name', (array)$this->position_filter)
            ->when($this->search, fn($q) => $q->where('e.name', 'like', '%' . $this->search . '%'))
            ->when($this->date_from, fn($q) => $q->where(fn($sub) => $sub->whereDate('t.training_date', '>=', $this->date_from)->orWhereNull('t.training_date')))
            ->when($this->date_to, fn($q) => $q->where(fn($sub) => $sub->whereDate('t.training_date', '<=', $this->date_to)->orWhereNull('t.training_date')))
            ->groupBy('e.id', 'e.name', 'e.nik', 'p.position_name', 'o.org_name')
            ->orderByDesc('total_minutes');
            
    } else {
        // Mode Default: Tampilkan semua yang pernah mengajar (Internal + External)
        return DB::table('trainings as t')
            ->leftJoin('employees as tr', 't.trainer_employee_id', '=', 'tr.id')
            ->leftJoin('organizations as o', 'tr.org_id', '=', 'o.id')
            ->leftJoin('positions as p', 'tr.position_id', '=', 'p.id')
            ->select(
                DB::raw('COALESCE(tr.name, t.trainer_external_name) as trainer_name'),
                'tr.nik',
                DB::raw('COALESCE(p.position_name, "EXTERNAL") as position'),
                DB::raw('COALESCE(o.org_name, "-") as organization'),
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT t.activity_name SEPARATOR ', '), '-') as activity_name"),
                DB::raw("COALESCE(GROUP_CONCAT(DISTINCT t.skill_name SEPARATOR ', '), '-') as skill_name"), // TAMBAHKAN BARIS INI
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes')
            )
            ->when($this->search, function ($q) {
                $q->where('tr.name', 'like', '%' . $this->search . '%')
                  ->orWhere('t.trainer_external_name', 'like', '%' . $this->search . '%');
            })
            ->groupBy('tr.name', 'tr.nik', 't.trainer_external_name', 'o.org_name', 'p.position_name')
            ->orderByDesc('total_minutes');
    }
}

    public function exportExcel()
    {
        return response()->streamDownload(function () {
            $data = $this->getBaseQuery()->get();

            // Header Excel: Nama, Jabatan, Org, Activity, Skill, Total Jam
            echo "Nama Trainer\tPosition\tOrganization\tActivity\tSkill\tTotal Jam Mengajar\n";

            foreach ($data as $row) {
                $hours = round(($row->total_minutes ?? 0) / 60, 2);

                // Format output menggunakan Tab (\t) agar rapi di Excel
                echo ($row->trainer_name ?? 'Tanpa Nama') . "\t" .
                    ($row->position ?? '-') . "\t" .
                    ($row->organization ?? '-') . "\t" .
                    ($row->activity_name ?? '-') . "\t" .
                    ($row->skill_name ?? '-') . "\t" .
                    str_replace('.', ',', $hours) . " Jam\n";
            }
        }, 'Trainer_Contribution_Report_' . date('Ymd') . '.xls');
    }

    public function exportDetailExcel()
    {
        // 1. Validasi awal
        if (!$this->selectedTrainerName) return;

        $fileName = 'Detail_Mengajar_' . str_replace(' ', '_', $this->selectedTrainerName) . '_' . date('Ymd') . '.xls';

        // 2. Ambil data dari properti yang sudah diisi oleh fungsi showDetail
        $data = $this->trainerDetails;

        return response()->streamDownload(function () use ($data) {
            // Excel BOM & Header
            echo "Topik Pelatihan\tTanggal\tJam Mulai\tJam Selesai\tDurasi (Jam)\n";

            foreach ($data as $row) {
                // Kita gunakan logic pembagian 60 karena minutes disimpan dalam satuan menit
                $durationHours = round(($row->minutes ?? 0) / 60, 2);

                echo ($row->title ?? '-') . "\t" .
                    ($row->training_date ?? '-') . "\t" .
                    ($row->start_time ?? '-') . "\t" .
                    ($row->finish_time ?? '-') . "\t" .
                    str_replace('.', ',', $durationHours) . " Jam\n";
            }
        }, $fileName);
    }

    public function render()
    {
        $positionList = DB::table('positions')
            ->orderBy('position_name', 'asc')
            ->get();
        $trainerList = DB::table('trainings as t')
            ->leftJoin('employees as tr', 't.trainer_employee_id', '=', 'tr.id')
            ->select(DB::raw('COALESCE(tr.name, t.trainer_external_name) as name'))
            ->distinct()
            ->whereNotNull(DB::raw('COALESCE(tr.name, t.trainer_external_name)'))
            ->orderBy('name', 'asc')
            ->get();

        return view('components.trainer-contribution.⚡trainer-contribution.trainer-contribution', [
            'contributions' => $this->getBaseQuery()->paginate(10),
            'trainerList' => $this->getTrainerList(),
            'positionList' => DB::table('positions')->orderBy('position_name', 'asc')->get()
        ]);
    }
};
