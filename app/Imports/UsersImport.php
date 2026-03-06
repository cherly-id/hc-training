<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas; // Agar VLOOKUP terbaca nilainya
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        if (!isset($row['training_title']) || empty($row['training_title'])) {
            return null;
        }

        $judulTraining = $row['training_title'];
        $nikKaryawan = isset($row['id']) ? (string) intval($row['id']) : null;

        if (!$judulTraining || !$nikKaryawan) {
            return null;
        }

        $tanggal = $this->transformDate($row['date'] ?? null);

        // 🔥 LOGIKA DETEKSI TRAINER (INTERNAL VS EXTERNAL)
        $trainerNameFromExcel = trim($row['trainer'] ?? '');
        $trainerEmployeeId = null;
        $trainerExternalName = null;

        if ($trainerNameFromExcel) {
            // Cek apakah nama ini ada di tabel employees
            $empTrainer = DB::table('employees')
                ->where('name', 'like', '%' . $trainerNameFromExcel . '%')
                ->first();

            if ($empTrainer) {
                // Jika ketemu, masukkan ke kolom ID (Internal)
                $trainerEmployeeId = $empTrainer->id;
            } else {
                // Jika tidak ketemu, masukkan ke nama manual (External)
                $trainerExternalName = $trainerNameFromExcel;
            }
        }

        // 3. PROSES DATA TRAINING
        $training = DB::table('trainings')
            ->where('title', $judulTraining)
            ->where('training_date', $tanggal)
            ->first();

        $dataTraining = [
            'title'                 => $judulTraining,
            'held_by'               => $row['held_by'] ?? 'JEMBO',
            'activity_name'         => $row['activity'] ?? 'External',
            'skill_name'            => $row['skill'] ?? 'Soft Skill',
            'training_date'         => $tanggal,
            'start_time'            => $this->parseTime($row['time'] ?? null, 'start'),
            'finish_time'           => $this->parseTime($row['time'] ?? null, 'end'),
            'fee'                   => $row['fee'] ?? 0,
            'is_certified'          => 'No',
            'trainer_employee_id'   => $trainerEmployeeId, // Masuk ke ID Employee
            'trainer_external_name' => $trainerExternalName, // Masuk ke Nama Manual
            'updated_at'            => now(),
        ];

        if (!$training) {
            $dataTraining['created_at'] = now();
            $trainingId = DB::table('trainings')->insertGetId($dataTraining);
        } else {
            DB::table('trainings')->where('id', $training->id)->update($dataTraining);
            $trainingId = $training->id;
        }

        // 4. CARI PESERTA
        $employee = DB::table('employees')->where('nik', $nikKaryawan)->first();

        if ($employee) {
            DB::table('training_participants')->updateOrInsert(
                ['training_id' => $trainingId, 'employee_id' => $employee->id],
                ['score' => $row['evaluation'] ?? null, 'updated_at' => now()]
            );
        }

        return null;
    }

    private function parseTime($value, $type)
    {
        if (!$value) return null;
        $value = str_replace('.', ':', $value);
        if (str_contains($value, '-')) {
            $parts = explode('-', $value);
            $time = ($type == 'start') ? ($parts[0] ?? null) : ($parts[1] ?? null);
            return $time ? trim($time) : null;
        }
        return $value;
    }

    private function transformDate($value)
    {
        try {
            if (!$value) return now()->format('Y-m-d');
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}