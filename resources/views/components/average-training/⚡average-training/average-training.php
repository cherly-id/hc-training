<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

return new class extends Component
{
    // =========================================
    // 1. PROPERTI
    // =========================================
    public $year;

    public function mount()
    {
        // Default Tahun
        $this->year = date('Y');

        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    // =========================================
    // 2. LOGIC DATA
    // =========================================
    public function buildData()
{
    // 1. Ambil semua organisasi (ID & Nama)
    $orgs = DB::table('organizations')->orderBy('org_name', 'ASC')->get();

    // 2. Hitung total karyawan (Kecuali Harian Lepas)
    $emp_counts = DB::table('employees')
        ->select('org_id', DB::raw('count(*) as total'))
        ->where('status', 'Active') 
        ->where('status_employee', '!=', 'Harian Lepas')
        ->groupBy('org_id')
        ->pluck('total', 'org_id')
        ->all();

    // 3. Hitung durasi training (Kecuali Harian Lepas)
    $raw_hours = DB::table('training_participants as tp')
        ->join('trainings as t', 'tp.training_id', '=', 't.id')
        ->join('employees as e', 'tp.employee_id', '=', 'e.id')
        ->select(
            'e.org_id',
            DB::raw('MONTH(t.training_date) as bln'),
            DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes')
        )
        ->where('e.status', 'Active') // Pastikan hanya yang aktif
        ->where('e.status_employee', '!=', 'Harian Lepas')
        ->whereYear('t.training_date', $this->year)
        ->groupBy('e.org_id', 'bln')
        ->get();

    // 4. Mapping data ke matrix [ID_ORG][BULAN]
    $matrix = [];
    foreach ($raw_hours as $row) {
        $matrix[$row->org_id][$row->bln] = $row->total_minutes;
    }

    return [
        'orgs' => $orgs,
        'emp_counts' => $emp_counts,
        'matrix' => $matrix
    ];
}

    // =========================================
    // 3. RENDER
    // =========================================
    public function render()
{
    
    return view('components.average-training.⚡average-training.average-training', $this->buildData());
}
};