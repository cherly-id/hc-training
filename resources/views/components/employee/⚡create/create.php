<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\Position;
use App\Imports\EmployeeImport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithPagination, WithFileUploads;

    // State pencarian & UI
    public $search = '';
    public $show_import_modal = false;
    public $editingId = null;
    public $excel_file;

    // Properti Form
    public $nik, $name, $org_id, $position_id, $status = 'Active', $status_employee;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'nik', 'name', 'org_id', 'position_id', 'status', 'status_employee']);
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $this->editingId = $id;
        $this->nik = $employee->nik;
        $this->name = $employee->name;
        $this->org_id = $employee->org_id; 
        $this->position_id = $employee->position_id;
        $this->status = $employee->status;
        $this->status_employee = $employee->status_employee;
    }

    public function save()
    {
        $this->validate([
            'nik' => 'required|unique:employees,nik,' . $this->editingId,
            'name' => 'required|min:3',
            'org_id' => 'required',
            'position_id' => 'required',
            'status' => 'required',
            'status_employee' => 'required',
        ]);

        Employee::updateOrCreate(['id' => $this->editingId], [
            'nik' => $this->nik,
            'name' => $this->name,
            'org_id' => $this->org_id,
            'position_id' => $this->position_id,
            'status' => $this->status,
            'status_employee' => $this->status_employee,
        ]);

        $this->resetForm();
        session()->flash('status', 'Data Employee berhasil diperbarui.');
        
        // Tambahkan ini untuk memicu SweetAlert2
        $this->dispatch('swal:success', message: 'Data Employee Berhasil Disimpan!');
    }

    public function delete($id)
    {
        Employee::destroy($id);
        session()->flash('status', 'Data karyawan telah dihapus.');

        // Tambahkan ini untuk memicu SweetAlert2
        $this->dispatch('swal:success', message: 'Data Karyawan Telah Dihapus.');
    }

    public function importExcel()
    {
        $this->validate([
            'excel_file' => 'required|mimes:xlsx,xls|max:5048',
        ]);

        try {
            Excel::import(new EmployeeImport, $this->excel_file->getRealPath());

            $this->reset(['show_import_modal', 'excel_file']);
            session()->flash('status', 'Data Employee berhasil di-import!');

            // Tambahkan ini untuk memicu SweetAlert2
            $this->dispatch('swal:success', message: 'Import Data Employee Berhasil!');
            
            return redirect()->to('/employee'); 

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $fileName = 'Employee_Report_' . date('Ymd_His') . '.csv';
        
        $data = Employee::query()
            ->with(['organization', 'position'])
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('nik', 'like', "%{$this->search}%");
            })
            ->orderBy('name', 'asc')
            ->get();

        return response()->streamDownload(function () use ($data) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM untuk Excel
            echo "sep=;\n"; // Semicolon separator
            echo "NIK;Nama Karyawan;Organization;Position;Status Employee;System Status\n";

            foreach ($data as $row) {
                $line = [
                    $row->nik,
                    $row->name,
                    $row->organization->org_name ?? '-',
                    $row->position->position_name ?? '-',
                    $row->status_employee,
                    $row->status
                ];
                
                $cleanLine = array_map(fn($val) => '"' . str_replace('"', '""', $val) . '"', $line);
                echo implode(';', $cleanLine) . "\n";
            }
        }, $fileName);
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->with(['organization', 'position']) 
            ->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('nik', 'like', "%{$this->search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10);
    }

    #[Computed]
    public function orgs()
    {
        return Organization::orderBy('org_name')->get();
    }

    #[Computed]
    public function positions()
    {
        return Position::orderBy('position_name')->get();
    }

    public function render()
    {
        return view('components.employee.⚡create.create', [
            'show_import_modal' => $this->show_import_modal,
        ]);
    }
};