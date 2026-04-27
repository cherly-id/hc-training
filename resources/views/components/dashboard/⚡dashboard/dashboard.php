<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use ArielMejiaDev\LarapexCharts\LarapexChart;

new class extends Component
{
    // State Filter
    public $filter_month = 'all';
    public $filter_year;
    public $filter_org = 'all';

    //untuk inisialisasi data saat pertama load, berupa tahun dalam format Y
    public function mount()
    {
        $this->filter_year = date('Y');
        $cekData = DB::table('trainings')
            ->whereYear('training_date', $this->filter_year)
            ->count();
        logger("Init Dashboard - Jumlah Data: " . $cekData);
    }

    // Event Listener untuk setiap ada perubahan pada properti pada komponen Livewire
    public function updated($property)
    {
        $this->dispatch('update-chart', chartData: $this->getChartData());
    }

    // Fungsi untuk mendapatkan data chart berdasarkan filter yang dipilih
    private function getChartData()
    {
        $chart_data = array_fill(0, 12, 0);

        $query = DB::table('trainings as t')
            ->whereNull('t.deleted_at')
            ->whereYear('t.training_date', $this->filter_year)
            ->select(
                DB::raw('MONTH(t.training_date) as bln'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_mins')
            );

        if ($this->filter_org !== 'all') {
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('training_participants as tp')
                    ->join('employees as e', 'tp.employee_id', '=', 'e.id')
                    ->whereColumn('tp.training_id', 't.id')
                    ->where('e.org_id', $this->filter_org);
            });
        }

        $results = $query->groupBy('bln')->get();

        foreach ($results as $row) {
            $idx = (int)$row->bln - 1;
            $chart_data[$idx] = round(($row->total_mins ?? 0) / 60, 1);
        }

        return $chart_data;
        
    }

    // Fungsi untuk mendapatkan data training penetration berdasarkan filter yang dipilih
    public function with(): array
    {
        $orgs_master = DB::table('organizations')->orderBy('org_name', 'ASC')->get();
        $dataBulanan = $this->getChartData();
        // dd($dataBulanan);

        // 2. KPI CARDS LOGIC
        $queryKpi = DB::table('trainings as t')
            ->whereNull('t.deleted_at')
            ->whereYear('t.training_date', $this->filter_year);

        // Filter Bulan (Hanya untuk KPI Cards, tidak untuk chart)
        if ($this->filter_month !== 'all') {
            $queryKpi->whereMonth('t.training_date', $this->filter_month);
        }

        // Filter Organisasi
        if ($this->filter_org !== 'all') {
            $queryKpi->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('training_participants as tp')
                    ->join('employees as e', 'tp.employee_id', '=', 'e.id')
                    ->whereColumn('tp.training_id', 't.id')
                    ->where('e.org_id', $this->filter_org);
            });
        }

        $kpi = $queryKpi->select(
            DB::raw('COUNT(t.id) as total_realized'),
            DB::raw('SUM(TIMESTAMPDIFF(MINUTE, t.start_time, t.finish_time)) as total_mins')
        )->first();

        // Hitung Jam Pelatihan (Jam Sesi)
        $total_hours = round(($kpi->total_mins ?? 0) / 60, 1);
        // dd($kpi);


        // Hitung Karyawan Aktif yang sebenarnya
        $empQuery = DB::table('employees')
            ->where('status', 'Active')
            ->whereNull('deleted_at');
        if ($this->filter_org !== 'all') {
            $empQuery->where('org_id', $this->filter_org);
        }

        $total_employees = $empQuery->count(); // Mengambil jumlah asli (0 jika kosong)

        // Hitung rata-rata dengan pengecekan agar tidak division by zero
        $avg_training_hours = ($total_employees > 0)
            ? round($total_hours / $total_employees, 2)
            : 0;
        // -------------------------

       // 3. DATA TRAINING PENETRATION (Tabel Bawah)
        $penetration_list = DB::table('organizations as o')
            ->when($this->filter_org !== 'all', fn($q) => $q->where('o.id', $this->filter_org))
            ->select([
                'o.id',
                'o.org_name',
                // Subquery Total Karyawan
                'total_emp' => DB::table('employees')
                    ->selectRaw('count(*)')
                    ->whereColumn('org_id', 'o.id')
                    ->where('status', 'Active')
                    ->whereNull('deleted_at'), 
                // Subquery Karyawan yang sudah training
                'trained_emp' => DB::table('training_participants as tp')
                    ->join('employees as e', 'tp.employee_id', '=', 'e.id')
                    ->join('trainings as t', 'tp.training_id', '=', 't.id')
                    ->selectRaw('count(distinct tp.employee_id)')
                    ->whereColumn('e.org_id', 'o.id')
                    ->whereNull('e.deleted_at')
                    ->whereNull('t.deleted_at')
                    ->whereYear('t.training_date', $this->filter_year)
                    ->when($this->filter_month !== 'all', fn($q) => $q->whereMonth('t.training_date', $this->filter_month))
            ])
            ->orderBy('o.org_name', 'ASC')
            ->get();

        $chart = (new LarapexChart)->AreaChart()
            ->setTitle('Tren Jam Training Karyawan')
            ->setSubtitle('Tahun ' . $this->filter_year)
            ->addData($dataBulanan, 'Total Jam')
            ->setXAxis(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'])
            ->setHeight(300)
            ->setColors(['#3b82f6']);

        return [
            'chart' => $chart,
            'orgs_master' => $orgs_master,
            'total_hours' => $total_hours,
            'avg_training_hours' => $avg_training_hours,
            'total_employees' => $total_employees,
            'penetration_list' => $penetration_list,
            'months' => [
                '01' => 'Januari',
                '02' => 'Februari',
                '03' => 'Maret',
                '04' => 'April',
                '05' => 'Mei',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'Agustus',
                '09' => 'September',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Desember'
            ]
        ];
    }
};
