<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Carbon\Carbon;


new class extends Component {

    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // ==============================
    // FORM STATES
    // ==============================
    public $training_id = null;
    public $title, $held_by, $activity_name, $skill_name;
    public $training_date, $start_time, $finish_time;
    public $fee = 0;
    public $is_certified = 'No';

    public $trainer_type = 'internal';
    public $trainer_employee_id = null;
    public $trainer_external_name = '';
    public $showFormModal = false;

    // ==============================
    // UI STATES
    // ==============================
    public $sidebar_search = '';
    public $dept_filter = '';
    public $show_preview = false;
    public $preview_employees = [];
    public $selected_participants = [];
    public $search_participant = '';

    // ==============================
    // IMPORT EXCEL
    // ==============================
    public $show_import_modal = false;
    public $excel_file;

    public function updatingSidebarSearch()
    {
        $this->resetPage();
    }

    public function getFilteredEmployeesProperty()
    {
        if (strlen($this->search_participant) < 2) return [];

        return DB::table('employees as e')
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->where('e.name', 'like', '%' . $this->search_participant . '%')
            ->orWhere('e.nik', 'like', '%' . $this->search_participant . '%')
            ->select(
                'e.id',
                'e.name',
                'e.nik',
                DB::raw("IFNULL(o.org_name, 'DEPT TIDAK TERDAFTAR') as org_name")
            )
            ->limit(5)
            ->get();
    }

    public function addSelectedParticipant($id)
    {
        $emp = DB::table('employees as e')
        ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
        ->where('e.id', $id)
        ->select(
            'e.id', 
            'e.name', 
            'e.nik', 
            DB::raw("IFNULL(o.org_name, 'DEPT TIDAK TERDAFTAR') as org_name")
        )
            ->first();

        if ($emp && !collect($this->selected_participants)->contains('id', $emp->id)) {
            $this->selected_participants[] = (array)$emp;
        }
        $this->search_participant = '';
    }

    // ==============================
    // LOAD TRAINING (PERBAIKAN DI SINI)
    // ==============================
    public function loadTraining($id)
    {
        $training = DB::table('trainings')->find($id);
        if (!$training) return;

        $this->training_id = $training->id;
        $this->title = $training->title;
        $this->held_by = $training->held_by;
        $this->activity_name = $training->activity_name;
        $this->skill_name = $training->skill_name;
        $this->training_date = $training->training_date;
        $this->start_time = $training->start_time;
        $this->finish_time = $training->finish_time;
        $this->fee = $training->fee;
        $this->is_certified = $training->is_certified ?? 'No';
        $this->showFormModal = true;

        // Logika Pintar: Mencocokkan Nama Database dengan Dropdown Layout
        if ($training->trainer_internal_name) {
            $this->trainer_type = 'internal';

            // Cek apakah isi database mengandung NIK (format "NIK - Nama")
            if (str_contains($training->trainer_internal_name, ' - ')) {
                $this->trainer_employee_id = $training->trainer_internal_name;
            } else {
                // Jika hanya Nama (hasil import), cari NIK-nya agar Dropdown terpilih otomatis
                $cek = DB::table('employees')
                    ->where('name', 'like', '%' . $training->trainer_internal_name . '%')
                    ->first();

                $this->trainer_employee_id = $cek ? ($cek->nik . ' - ' . $cek->name) : $training->trainer_internal_name;
            }
            $this->trainer_external_name = '';
        } else {
            $this->trainer_type = 'external';
            $this->trainer_external_name = $training->trainer_external_name;
            $this->trainer_employee_id = null;
        }

        $this->selected_participants = DB::table('training_participants as tp')
        ->join('employees as e', 'tp.employee_id', '=', 'e.id')
        ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id') // Join ke tabel org
        ->where('tp.training_id', $id)
        ->select(
            'e.id', 
            'e.name', 
            'e.nik', 
            DB::raw("IFNULL(o.org_name, 'DEPT TIDAK TERDAFTAR') as org_name")
        )
            ->get()
            ->map(fn($item) => (array)$item)
            ->toArray();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required',
            'training_date' => 'required|date',
            'is_certified' => 'required|in:Yes,No',
        ]);

        DB::transaction(function () {
            $data = [
                'title' => $this->title,
                'held_by' => $this->held_by,
                'activity_name' => $this->activity_name,
                'skill_name' => $this->skill_name,
                'training_date' => $this->training_date,
                'start_time' => $this->start_time,
                'finish_time' => $this->finish_time,
                'fee' => $this->fee,
                'is_certified' => $this->is_certified,
                'trainer_internal_name' => $this->trainer_type === 'internal' ? $this->trainer_employee_id : null,
                'trainer_external_name' => $this->trainer_type === 'external' ? $this->trainer_external_name : null,
                'updated_at' => now(),
            ];

            if ($this->training_id) {
                DB::table('trainings')->where('id', $this->training_id)->update($data);
                DB::table('training_participants')->where('training_id', $this->training_id)->delete();
                $id = $this->training_id;
            } else {
                $data['created_at'] = now();
                $id = DB::table('trainings')->insertGetId($data);
                $this->training_id = $id;
            }

            foreach ($this->selected_participants as $p) {
                DB::table('training_participants')->insert([
                    'training_id' => $id,
                    'employee_id' => $p['id']
                ]);
            }
        });

        session()->flash('message', 'Data berhasil disimpan!');
        $this->resetForm();
        $this->showFormModal = false;
    }

    private function resetForm()
    {
        $this->reset([
            'training_id',
            'title',
            'held_by',
            'activity_name',
            'skill_name',
            'training_date',
            'start_time',
            'finish_time',
            'fee',
            'trainer_employee_id',
            'trainer_external_name',
            'selected_participants'
        ]);
        $this->trainer_type = 'internal';
        $this->is_certified = 'No';
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function importExcel()
    {
        $this->validate(['excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        try {
            Excel::import(new UsersImport, $this->excel_file);
            $this->reset(['excel_file', 'show_import_modal']);
            return redirect()->route('training-data-index');
        } catch (\Exception $e) {
            session()->flash('error', 'IMPORT GAGAL: ' . $e->getMessage());
        }
    }

    public function with()
    {
        return [
            'trainings' => DB::table('trainings')
                ->where('title', 'like', "%{$this->sidebar_search}%")
                ->orderBy('training_date', 'desc')
                ->paginate(15),
            'employees_list' => DB::table('employees')->orderBy('name')->get(),
        ];
    }

    // ==============================
    // ACTION METHODS
    // ==============================

    public function removeParticipant($id)
    {
        // Menghapus peserta dari array sementara berdasarkan ID
        $this->selected_participants = collect($this->selected_participants)
            ->filter(fn($p) => $p['id'] != $id)
            ->values()
            ->toArray();
    }

    public function deleteTraining($id)
    {
        // Menghapus data training dan relasi pesertanya
        DB::transaction(function () use ($id) {
            DB::table('training_participants')->where('training_id', $id)->delete();
            DB::table('trainings')->where('id', $id)->delete();
        });

        session()->flash('message', 'Data training berhasil dihapus!');
    }
};
