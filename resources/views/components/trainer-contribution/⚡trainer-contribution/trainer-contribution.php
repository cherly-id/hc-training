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
    public $date_from;
    public $date_to;
    public $showDetailModal = false;
    public $selectedTrainerName = '';
    public $trainerDetails = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
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
        
        $this->trainerDetails = DB::table('trainings')
            ->where(function($q) use ($trainerName) {
                $q->where('trainer_internal_name', $trainerName)
                  ->orWhere('trainer_external_name', $trainerName);
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

    private function getBaseQuery()
    {
        $query = DB::table('trainings as t')
            // JOIN ke employees dengan memotong NIK (mengambil teks setelah ' - ')
            ->leftJoin('employees as e', function ($join) {
                $join->on(
                    DB::raw("TRIM(SUBSTRING_INDEX(t.trainer_internal_name, ' - ', -1)) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("e.name COLLATE utf8mb4_unicode_ci")
                );
            })
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->select(
                DB::raw('COALESCE(t.trainer_internal_name, t.trainer_external_name) as trainer_name'),
                // Ambil Nama Departemen (Organization)
                DB::raw('COALESCE(o.org_name, "-") as organization'),
                DB::raw("GROUP_CONCAT(DISTINCT t.activity_name SEPARATOR ', ') as activity_name"),
                DB::raw("GROUP_CONCAT(DISTINCT t.skill_name SEPARATOR ', ') as skill_name"),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes')
            );

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('t.trainer_internal_name', 'like', '%' . $this->search . '%')
                    ->orWhere('t.trainer_external_name', 'like', '%' . $this->search . '%')
                    ->orWhere('o.org_name', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->date_from) && !empty($this->date_to)) {
            $query->whereBetween('t.training_date', [$this->date_from, $this->date_to]);
        }

        $query->groupBy(
            't.trainer_internal_name',
            't.trainer_external_name',
            'o.org_name'
        )
            ->orderByDesc('total_minutes');

        return $query;
    }

    public function exportExcel()
    {
        return response()->streamDownload(function () {
            $data = $this->getBaseQuery()->get();
            echo "Nama Trainer\tOrganization\tActivity\tSkill\tDurasi Jam Mengajar\n";
            foreach ($data as $row) {
                $hours = round(($row->total_minutes ?? 0) / 60, 2);
                echo ($row->trainer_name ?? 'Tanpa Nama') . "\t" .
                    ($row->organization ?? '-') . "\t" .
                    ($row->activity_name ?? '-') . "\t" .
                    ($row->skill_name ?? '-') . "\t" .
                    str_replace('.', ',', $hours) . " Jam\n";
            }
        }, 'Trainer_Contribution_Report_' . date('Ymd') . '.xls');
    }

    public function render()
    {
        // Ambil list trainer unik untuk dropdown filter
        $trainerList = DB::table('trainings')
            ->select(DB::raw('COALESCE(trainer_internal_name, trainer_external_name) as name'))
            ->whereNotNull('trainer_internal_name')
            ->orWhereNotNull('trainer_external_name')
            ->distinct()
            ->orderBy('name', 'asc')
            ->get();

        $contributions = $this->getBaseQuery()->paginate(10);
        
        return view('components.trainer-contribution.⚡trainer-contribution.trainer-contribution', [
            'contributions' => $contributions,
            'trainerList' => $trainerList // Kirim data ke view
        ]);
    }
};
