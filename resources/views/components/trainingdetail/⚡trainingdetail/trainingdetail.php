<?php

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $date_from;
    public $date_to;
    public $title_filter = '';
    public $trainer_filter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingTitleFilter() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingTrainerFilter() { $this->resetPage(); }

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

    // 🔥 Fungsi untuk simpan skor langsung dari tabel
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
            ->join('employees as e', 'tp.employee_id', '=', 'e.id') 
            ->leftJoin('organizations as o', 'e.org_id', '=', 'o.id')
            ->leftJoin('positions as p', 'e.position_id', '=', 'p.id')
            ->select(
                'tp.id as participant_id',
                'tp.score', // 🔥 Ditambahkan agar tidak error "Undefined property"
                'e.nik',
                'e.name as employee_name',
                'o.org_name as department',
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
                't.trainer_internal_name',
                't.trainer_external_name'
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
                $q->where(function($sub) {
                    $sub->where('t.trainer_internal_name', 'like', '%' . $this->trainer_filter . '%')
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
            echo "NIK;Nama Karyawan;Departemen;Judul Training;Trainer;Held By;Activities;Skill;Tanggal;Jam Mulai;Jam Selesai;Durasi;Biaya;Score;Sertifikat\n";

            foreach ($data as $row) {
                $trainer = $row->trainer_internal_name ?: ($row->trainer_external_name ?: '-');
                $start = \Carbon\Carbon::parse($row->start_time);
                $end = \Carbon\Carbon::parse($row->finish_time);
                $duration = round($start->diffInMinutes($end) / 60, 1);

                $line = [
                    $row->nik,
                    $row->employee_name,
                    $row->department,
                    $row->title,
                    $trainer,
                    $row->held_by,
                    $row->activity_name,
                    $row->skill_name,
                    $row->training_date,
                    $row->start_time,
                    $row->finish_time,
                    $duration,
                    $row->fee,
                    $row->score ?? 0, // 🔥 Pastikan score masuk ke export
                    $row->is_certified
                ];
                
                $cleanLine = array_map(fn($val) => '"' . str_replace('"', '""', $val) . '"', $line);
                echo implode(';', $cleanLine) . "\n";
            }
        }, $fileName);
    }

    public function render()
    {
        $query = $this->buildQuery();
        
        $statsData = (clone $query)->get();
        $total_trainings = $statsData->count();
        
        $total_minutes = $statsData->sum(function($row) {
            return \Carbon\Carbon::parse($row->start_time)->diffInMinutes(\Carbon\Carbon::parse($row->finish_time));
        });
        
        $total_hours = number_format($total_minutes / 60, 1, ',', '.');

        $allTitles = DB::table('trainings')
            ->select('title')
            ->distinct()
            ->orderBy('title', 'asc')
            ->get();

        return view('components.trainingdetail.⚡trainingdetail.trainingdetail', [
            'rows' => $query->paginate($this->perPage),
            'total_trainings' => $total_trainings,
            'total_hours' => $total_hours,
            'allTitles' => $allTitles
        ]);
    }
};