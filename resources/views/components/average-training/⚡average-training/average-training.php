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
    $orgs = DB::table('organizations')->orderBy('org_name', 'ASC')->get();

    $emp_counts = DB::table('employees')
        ->select('org_id', DB::raw('count(*) as total'))
        ->where('status', 'Active') 
        ->where('status_employee', '!=', 'Harian Lepas')
        ->groupBy('org_id')
        ->pluck('total', 'org_id')
        ->all();

    $raw_hours = DB::table('training_participants as tp')
        ->join('trainings as t', 'tp.training_id', '=', 't.id')
        ->join('employees as e', 'tp.employee_id', '=', 'e.id')
        ->select(
            'e.org_id',
            DB::raw('MONTH(t.training_date) as bln'),
            DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_minutes')
        )
        ->where('e.status', 'Active')
        ->where('e.status_employee', '!=', 'Harian Lepas')
        ->whereYear('t.training_date', $this->year)
        ->groupBy('e.org_id', 'bln')
        ->get();

    // Mapping awal: [ORG_ID][BULAN] = Menit Bulanan
    $monthly_minutes = [];
    foreach ($raw_hours as $row) {
        $monthly_minutes[$row->org_id][$row->bln] = $row->total_minutes;
    }

    // 🔥 LOGIC AKUMULASI (YTD)
    $matrix = [];
    $currentMonth = ($this->year == date('Y')) ? date('n') : 12; // Jika tahun ini, stop di bulan sekarang. Jika tahun lalu, munculkan semua (12).

    foreach ($orgs as $org) {
        $runningSum = 0;
        for ($bln = 1; $bln <= 12; $bln++) {
            // Jika bulan yang di-loop melebihi bulan sekarang, jangan isi datanya
            if ($bln > $currentMonth) {
                $matrix[$org->id][$bln] = null; 
                continue;
            }

            $currentMonthMinutes = $monthly_minutes[$org->id][$bln] ?? 0;
            $runningSum += $currentMonthMinutes;
            
            $matrix[$org->id][$bln] = $runningSum;
        }
    }

    return [
        'orgs' => $orgs,
        'emp_counts' => $emp_counts,
        'matrix' => $matrix
    ];
}


public function exportExcel()
{
    $fileName = 'Average_Training_Hours_' . $this->year . '.csv';
    $data = $this->buildData();

    return response()->streamDownload(function () use ($data) {
        // Excel BOM agar karakter spesial terbaca dengan benar
        echo "\xEF\xBB\xBF"; 
        echo "sep=;\n"; 
        
        // Header Tabel
        echo "Department;Total Employees;Jan;Feb;Mar;Apr;Mei;Jun;Jul;Agu;Sep;Okt;Nov;Des\n";

        foreach ($data['orgs'] as $org) {
            $empCount = $data['emp_counts'][$org->id] ?? 0;
            
            // Kolom Dasar: Nama Departemen dan Jumlah Karyawan
            $line = [
                $org->org_name,
                $empCount
            ];

            // Isi data bulan 1 sampai 12 sesuai logic Average Training
            for ($bln = 1; $bln <= 12; $bln++) {
                $totalMinutes = $data['matrix'][$org->id][$bln] ?? 0;
                
                // Logic Perhitungan Aktual: (Total Menit / 60) / Total Karyawan
                $avgHours = ($empCount > 0) ? ($totalMinutes / 60) / $empCount : 0;
                
                // Masukkan hasil ke array dengan format 2 angka di belakang koma
                $line[] = number_format($avgHours, 2, ',', '');
            }

            // Bersihkan data dari karakter yang bisa merusak separator
            $cleanLine = array_map(function($val) {
                return '"' . str_replace('"', '""', $val) . '"';
            }, $line);

            echo implode(';', $cleanLine) . "\n";
        }
    }, $fileName);
}

    // =========================================
    // 3. RENDER
    // =========================================
    public function render()
{
    
    return view('components.average-training.⚡average-training.average-training', $this->buildData());
}
};