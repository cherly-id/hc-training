<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Training;
use App\Models\Employee;
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
    public $trainer_employee_id = null; // Format: "NIK - Nama"
    public $trainer_external_name = '';

    // ==============================
    // UI STATES
    // ==============================
    public $showFormModal = false;
    public $show_import_modal = false;
    public $sidebar_search = '';
    public $search_participant = '';
    public $selected_participants = [];
    public $excel_file;

    public function updatingSidebarSearch()
    {
        $this->resetPage();
    }

    /**
     * Pencarian Peserta Otomatis (Computed Property)
     */
    public function getFilteredEmployeesProperty()
    {
        if (strlen($this->search_participant) < 2) return [];

        return Employee::query()
            ->leftJoin('organizations as o', 'employees.org_id', '=', 'o.id')
            ->where(function ($q) {
                $q->where('employees.name', 'like', '%' . $this->search_participant . '%')
                    ->orWhere('employees.nik', 'like', '%' . $this->search_participant . '%');
            })
            ->select('employees.id', 'employees.name', 'employees.nik', 'o.org_name')
            ->limit(5)
            ->get();
    }

    /**
     * Menambahkan peserta ke daftar temporary di modal
     */
    public function addSelectedParticipant($id)
    {
        $emp = Employee::query()
            ->leftJoin('organizations as o', 'employees.org_id', '=', 'o.id')
            ->where('employees.id', $id)
            ->select('employees.id', 'employees.name', 'employees.nik', 'o.org_name')
            ->first();

        if ($emp && !collect($this->selected_participants)->contains('id', $emp->id)) {
            $this->selected_participants[] = [
                'id' => $emp->id,
                'name' => $emp->name,
                'nik' => $emp->nik,
                'org_name' => $emp->org_name ?? 'DEPT TIDAK TERDAFTAR'
            ];
        }
        $this->search_participant = '';
    }

    /**
     * Menghapus peserta dari daftar temporary di modal
     */
    public function removeParticipant($id)
    {
        $this->selected_participants = collect($this->selected_participants)
            ->filter(fn($p) => $p['id'] != $id)
            ->values()
            ->toArray();
    }

    public function loadTraining($id)
    {
        $training = Training::with(['participants.organization', 'trainerInternal'])->find($id);
        if (!$training) return;

        $this->training_id = $training->id;
        $this->title = $training->title;
        $this->held_by = $training->held_by;
        $this->activity_name = $training->activity_name;
        $this->skill_name = $training->skill_name;
        $this->training_date = $training->training_date ? $training->training_date->format('Y-m-d') : null;
        $this->start_time = $training->start_time;
        $this->finish_time = $training->finish_time;
        $this->fee = $training->fee;
        $this->is_certified = $training->is_certified ?? 'No';

        // --- LOGIKA PEMISAH TRAINER ---
        if ($training->trainer_employee_id && $training->trainerInternal) {
            // JIKA PUNYA NIK: Masuk tab Internal
            $this->trainer_type = 'internal';
            $this->trainer_employee_id = $training->trainerInternal->nik . ' - ' . $training->trainerInternal->name;
            $this->trainer_external_name = ''; // WAJIB DIKOSONGKAN
        } else {
            // JIKA TIDAK PUNYA NIK: Masuk tab External
            $this->trainer_type = 'external';
            $this->trainer_external_name = $training->trainer_external_name;
            $this->trainer_employee_id = null; // WAJIB DIKOSONGKAN
        }

        $this->selected_participants = $training->participants->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'nik' => $p->nik,
                'org_name' => $p->organization->org_name ?? 'DEPT TIDAK TERDAFTAR'
            ];
        })->toArray();

        $this->showFormModal = true;
    }

    /**
     * Simpan Data (Create/Update)
     */
    public function save()
{
    $this->validate([
        'title' => 'required',
        'training_date' => 'required|date',
        'is_certified' => 'required|in:Yes,No',
    ]);

    DB::transaction(function () {
        $empId = null;
        $extName = null;

        // TENTUKAN DATA BERDASARKAN TAB YANG AKTIF
        if ($this->trainer_type === 'internal') {
            if ($this->trainer_employee_id) {
                $nik = explode(' - ', $this->trainer_employee_id)[0];
                $emp = Employee::where('nik', $nik)->first();
                $empId = $emp ? $emp->id : null;
            }
            $extName = null; // Karena internal, nama eksternal harus dihapus
        } else {
            $extName = $this->trainer_external_name;
            $empId = null; // Karena eksternal, ID internal harus dihapus
        }

        $training = Training::updateOrCreate(
            ['id' => $this->training_id],
            [
                'title' => $this->title,
                'held_by' => $this->held_by,
                'activity_name' => $this->activity_name,
                'skill_name' => $this->skill_name,
                'training_date' => $this->training_date,
                'start_time' => $this->start_time,
                'finish_time' => $this->finish_time,
                'fee' => $this->fee,
                'is_certified' => $this->is_certified,
                'trainer_employee_id' => $empId,
                'trainer_external_name' => $extName,
            ]
        );

        $participantIds = collect($this->selected_participants)->pluck('id')->toArray();
        $training->participants()->sync($participantIds);
    });

    session()->flash('message', 'Data Berhasil Disimpan!');
    $this->resetForm();
    $this->showFormModal = false;
}

    /**
     * Import Excel
     */
    public function importExcel()
    {
        $this->validate(['excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);

        try {
            Excel::import(new UsersImport, $this->excel_file);
            $this->reset(['excel_file', 'show_import_modal']);
            session()->flash('message', 'Import Berhasil!');
            return redirect()->route('training-data-index');
        } catch (\Exception $e) {
            session()->flash('error', 'IMPORT GAGAL: ' . $e->getMessage());
        }
    }

    /**
     * Delete (Soft Delete)
     */
    public function deleteTraining($id)
    {
        $training = Training::find($id);
        if ($training) {
            $training->delete();
            session()->flash('message', 'Data berhasil dihapus!');
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function resetForm()
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
            'selected_participants',
            'search_participant'
        ]);
        $this->trainer_type = 'internal';
        $this->is_certified = 'No';
    }

    public function with()
    {
        return [
            'trainings' => Training::with(['trainerInternal']) // Ini kuncinya agar nama Pak Mangatur muncul
                ->where('title', 'like', "%{$this->sidebar_search}%")
                ->orderBy('training_date', 'desc')
                ->paginate(15),
            'employees_list' => Employee::orderBy('name')->get(),
        ];
    }
};

?>

<div>
</div>