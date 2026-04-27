<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $date_from;
    public $date_to;
    public $title_filter = '';
    public $trainer_filter = '';
    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingTitleFilter()
    {
        $this->resetPage();
    }
    public function updatingDateFrom()
    {
        $this->resetPage();
    }
    public function updatingTrainerFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'date_from', 'date_to', 'title_filter', 'trainer_filter']);
        $this->resetPage();
    }

    public function updateScore($participantId, $newScore)
    {
        DB::table('training_participants')
            ->where('id', $participantId)
            ->update([
                'score' => $newScore,
                'updated_at' => now()
            ]);

        session()->flash('status', 'Skor berhasil diperbarui!');
    }

    private function buildQuery()
    {
        return DB::table('training_participants as tp')
            ->join('trainings as t', 'tp.training_id', '=', 't.id')
            ->whereNull('t.deleted_at')
            ->leftjoin('employees as e', 'tp.employee_id', '=', 'e.id')
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->leftJoin('positions as p', 'e.position_id', '=', 'p.id')
            ->leftJoin('employees as tr', 't.trainer_employee_id', '=', 'tr.id')
            ->select(
                'tp.id as participant_id',
                'tp.score',
                'e.nik',
                'e.name as employee_name',
                'o.org_name as department',
                'e.status as employee_status',
                'p.position_name',
                't.title',
                't.held_by',
                't.training_date',
                't.start_time',
                't.finish_time',
                't.fee',
                't.activity_name',
                't.skill_name',
                't.is_certified',
                't.trainer_external_name',
                'tr.name as trainer_internal_name',
                'tr.nik as trainer_internal_nik'
            )
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('e.name', 'like', '%' . $this->search . '%')
                        ->orWhere('e.nik', 'like', '%' . $this->search . '%')
                        ->orWhere('o.org_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->title_filter, function ($q) {
                $q->where('t.title', 'like', '%' . $this->title_filter . '%');
            })
            ->when($this->trainer_filter, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('tr.name', 'like', '%' . $this->trainer_filter . '%')
                        ->orWhere('t.trainer_external_name', 'like', '%' . $this->trainer_filter . '%');
                });
            })
            ->when($this->date_from, function ($q) {
                $q->whereDate('t.training_date', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($q) {
                $q->whereDate('t.training_date', '<=', $this->date_to);
            })
            ->orderBy('t.training_date', 'desc');
    }

    public function exportExcel()
    {
        $fileName = 'Training_Report_' . date('Ymd_His') . '.csv';
        $data = $this->buildQuery()->get();

        return response()->streamDownload(function () use ($data) {
            echo "\xEF\xBB\xBF";
            echo "sep=;\n";
            echo "REKAPITULASI DURASI PELATIHAN KARYAWAN;\n";
            $periode = ($this->date_from && $this->date_to)
                ? Carbon::parse($this->date_from)->format('d/m/Y') . ' s/d ' . Carbon::parse($this->date_to)->format('d/m/Y')
                : 'Semua Periode';

            echo "Periode: ;" . $periode . "\n";
            echo "Tanggal Cetak: ;" . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') . " WIB\n\n";

            echo "NIK;Nama Karyawan;Departemen;Judul Training;Trainer;Held By;Activities;Skill;Tanggal;Jam Mulai;Jam Selesai;Durasi;Biaya;Score;Sertifikat\n";

            foreach ($data as $row) {
                $trainer = '-';
                if ($row->trainer_internal_name) {
                    // Jika internal, gabungkan NIK dan Nama
                    $trainer = ($row->trainer_internal_nik ? $row->trainer_internal_nik . ' - ' : '') . $row->trainer_internal_name;
                } elseif ($row->trainer_external_name) {
                    // Jika external, pakai nama external
                    $trainer = $row->trainer_external_name;
                }

                $duration = 0;
                if ($row->start_time && $row->finish_time) {
                    $start = Carbon::parse($row->start_time);
                    $end = Carbon::parse($row->finish_time);
                    $duration = round($start->diffInMinutes($end) / 60, 1);
                }

                $line = [
                    $row->nik,
                    $row->employee_name,
                    $row->department ?? 'N/A',
                    $row->title,
                    $trainer,
                    $row->held_by,
                    $row->activity_name,
                    $row->skill_name,
                    $row->training_date,
                    $row->start_time,
                    $row->finish_time,
                    str_replace('.', ',', $duration),
                    $row->fee,
                    $row->score ?? 0,
                    $row->is_certified
                ];

                $cleanLine = array_map(fn($val) => '"' . str_replace('"', '""', $val ?? '') . '"', $line);
                echo implode(';', $cleanLine) . "\n";
            }
        }, $fileName);
    }

    public function exportRekap()
    {
        $fileName = 'Rekap_Jam_Training_Seluruh_Karyawan_' . date('Ymd_His') . '.csv';

        // 1. Ambil data karyawan (Sudah diperbaiki penutup query-nya)
        $allEmployees = DB::table('employees as e')
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->leftJoin('positions as p', 'e.position_id', '=', 'p.id')
            ->select('e.id as employee_id', 'e.nik', 'e.name as employee_name', 'e.status_employee', 'o.org_name as department', 'p.position_name')
            ->where('e.status', 'Active')
            ->whereNull('e.deleted_at')
            ->orderBy('o.org_name', 'asc')
            ->orderBy('e.name', 'asc')
            ->get(); // <-- Tadi kurang ini

        // 2. Ambil data jam training
        $trainingLogs = DB::table('training_participants as tp')
            ->join('trainings as t', 'tp.training_id', '=', 't.id')
            ->whereNull('t.deleted_at')
            ->when($this->date_from, fn($q) => $q->whereDate('t.training_date', '>=', $this->date_from))
            ->when($this->date_to, fn($q) => $q->whereDate('t.training_date', '<=', $this->date_to))
            ->select('tp.employee_id', 't.start_time', 't.finish_time')
            ->get()
            ->groupBy('employee_id');

        $periode = ($this->date_from && $this->date_to) ? $this->date_from . ' s/d ' . $this->date_to : 'Semua Periode';
        $namaUser = Auth::user()->name ?? 'Admin';
        $waktuCetak = now()->timezone('Asia/Jakarta')->format('d/m/Y H:i');

        return response()->streamDownload(function () use ($allEmployees, $trainingLogs, $periode, $namaUser, $waktuCetak) {
            echo "\xEF\xBB\xBF";
            echo "sep=;\n";

            echo "REKAPITULASI TOTAL JAM PELATIHAN KARYAWAN AKTIF;\n";
            echo "Periode: ;" . $periode . "\n";
            echo "Tanggal Cetak: ;" . $waktuCetak . " WIB\n";
            echo "Dicetak Oleh: ;" . $namaUser . "\n\n";

            echo "NIK;Nama;Status;Departemen;Posisi;Jam Training\n";

            foreach ($allEmployees as $emp) {
                $totalMinutes = 0;
                if (isset($trainingLogs[$emp->employee_id])) {
                    foreach ($trainingLogs[$emp->employee_id] as $log) {
                        if ($log->start_time && $log->finish_time) {
                            $totalMinutes += Carbon::parse($log->start_time)->diffInMinutes(Carbon::parse($log->finish_time));
                        }
                    }
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $decimalHours = round($totalMinutes / 60, 2);
                $totalString = $totalMinutes > 0 ? "{$hours} Jam {$minutes} Menit (" . str_replace('.', ',', $decimalHours) . " Jam)" : "0 Jam";

                $line = [$emp->nik, $emp->employee_name, $emp->status_employee ?? '-', $emp->department ?? 'N/A', $emp->position_name ?? 'N/A', $totalString];
                $cleanLine = array_map(fn($val) => '"' . str_replace('"', '""', $val ?? '') . '"', $line);
                echo implode(';', $cleanLine) . "\n";
            }
        }, $fileName);
    }

    public function render()
    {
        $query = $this->buildQuery();
        $statsData = (clone $query)->get();
        $total_minutes = $statsData->sum(fn($row) => Carbon::parse($row->start_time)->diffInMinutes(Carbon::parse($row->finish_time)));

        return view('components.trainingdetail.⚡trainingdetail.trainingdetail', [
            'rows' => $query->paginate($this->perPage),
            'total_trainings' => $statsData->count(),
            'total_hours' => number_format($total_minutes / 60, 1, ',', '.'),
            'allTitles' => DB::table('trainings')->select('title')->distinct()->orderBy('title', 'asc')->get()
            ->whereNull('deleted_at')
        ]);
    }
};
