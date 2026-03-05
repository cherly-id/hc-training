<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $trainingId = '';

    public $selectedDept = null;
    public $selectedType = null;
    public $employeeList = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'trainingId' => ['except' => ''],
    ];

    public function resetFilters()
    {
        $this->reset(['search', 'dateFrom', 'dateTo', 'trainingId']);
    }

    public function exportExcel()
    {
        if (ob_get_level() > 0) ob_end_clean();

        $fileName = 'Detail_Penetrasi_Training_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, "sep=,\n");
            fputcsv($file, ['REPORT DETAIL PENETRASI TRAINING']);
            fputcsv($file, ['Tanggal Filter:', ($this->dateFrom ?: '-') . ' s/d ' . ($this->dateTo ?: '-')]);
            fputcsv($file, []);
            fputcsv($file, ['Department', 'Nama Karyawan', 'ID Karyawan', 'Status Training', 'Judul Training']);

            $orgs = DB::table('organizations')
                ->when($this->search, function ($query) {
                    $query->where('org_name', 'like', '%' . $this->search . '%');
                })->get();

            foreach ($orgs as $org) {
                $employees = DB::table('employees')
                    ->where('org_id', $org->id)
                    ->where('status', 'Active') // Fix Case Sensitive
                    ->get();

                foreach ($employees as $emp) {
                    $training = DB::table('training_participants as tp')
                        ->join('trainings as t', 'tp.training_id', '=', 't.id')
                        ->where('tp.employee_id', $emp->id)
                        ->when($this->dateFrom && $this->dateTo, function ($q) {
                            $q->whereBetween('t.training_date', [$this->dateFrom, $this->dateTo]);
                        })
                        ->when($this->trainingId, function ($q) {
                            $q->where('t.id', $this->trainingId);
                        })
                        ->select('t.title')
                        ->first();

                    fputcsv($file, [
                        $org->org_name,
                        $emp->name,
                        $emp->nik,
                        $training ? 'SUDAH TRAINING' : 'BELUM TRAINING',
                        $training ? $training->title : '-'
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function showDetail($type, $deptId)
    {
        $this->selectedType = $type;
        $this->selectedDept = DB::table('organizations')->where('id', $deptId)->value('org_name');

        $query = DB::table('employees as e')
            ->where('e.org_id', $deptId)
            ->where('e.status', 'ACTIVE')
            ->where('e.status_employee', '!=', 'Harian Lepas');

        if ($type == 'trained') {
            $query->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('training_participants as tp')
                    ->join('trainings as t', 'tp.training_id', '=', 't.id')
                    ->whereColumn('tp.employee_id', 'e.id')
                    ->when($this->dateFrom && $this->dateTo, function ($dq) {
                        $dq->whereBetween('t.training_date', [$this->dateFrom, $this->dateTo]);
                    })
                    ->when($this->trainingId, function ($tq) {
                        $tq->where('t.id', $this->trainingId);
                    });
            });
        } else {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('training_participants as tp')
                    ->join('trainings as t', 'tp.training_id', '=', 't.id')
                    ->whereColumn('tp.employee_id', 'e.id')
                    ->when($this->dateFrom && $this->dateTo, function ($dq) {
                        $dq->whereBetween('t.training_date', [$this->dateFrom, $this->dateTo]);
                    })
                    ->when($this->trainingId, function ($tq) {
                        $tq->where('t.id', $this->trainingId);
                    });
            });
        }

        $this->employeeList = $query->select('e.id', 'e.name', 'e.nik')->get();
    }

    public function with()
    {
        $allOrganizations = DB::table('organizations')
            ->select('id', 'org_name')
            ->orderBy('org_name')
            ->get();
        $trainings = DB::table('trainings')->select('id', 'title')->orderBy('title')->get();

        // Hitung Total Employee di List Table
        $orgs = DB::table('organizations as mo')
            ->select(
                'mo.id',
                'mo.org_name',
                DB::raw('(SELECT COUNT(*) FROM employees WHERE org_id = mo.id AND status = "Active" AND status_employee != "Harian Lepas") as total_emp')
    )
            ->when($this->search, function ($query) {
                $query->where('mo.org_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('mo.org_name')
            ->get();

        $trainedData = DB::table('training_participants as tp')
            ->join('trainings as t', 'tp.training_id', '=', 't.id')
            ->join('employees as e', 'tp.employee_id', '=', 'e.id')
            ->select('e.org_id', DB::raw('COUNT(DISTINCT e.id) as trained_count'))
            ->where('e.status', 'Active')
            ->where('e.status_employee', '!=', 'Harian Lepas')
            ->when($this->dateFrom, function ($query) {
                $query->where('t.training_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('t.training_date', '<=', $this->dateTo);
            })
            ->when($this->trainingId, function ($query) {
                $query->where('t.id', $this->trainingId);
            })
            ->groupBy('e.org_id')
            ->pluck('trained_count', 'e.org_id')
            ->all();

        $sumTotal = 0;
        $sumTrained = 0;

        $results = $orgs->map(function ($org) use ($trainedData, &$sumTotal, &$sumTrained) {
            $trained = $trainedData[$org->id] ?? 0;
            $total   = (int) $org->total_emp;
            $sumTotal += $total;
            $sumTrained += $trained;

            return (object)[
                'org_id'    => $org->id,
                'org_name'  => $org->org_name,
                'total_emp' => $total,
                'trained'   => $trained,
                'percentage' => $total > 0 ? round(($trained / $total) * 100, 1) : 0
            ];
        });

        return [
            'results'      => $results,
            'sumTotal'     => $sumTotal,
            'sumTrained'   => $sumTrained,
            'totalPct'     => $sumTotal > 0 ? round(($sumTrained / $sumTotal) * 100, 1) : 0,
            'allTrainings' => $trainings,
            'allOrganizations' => $allOrganizations
        ];
    }
};
