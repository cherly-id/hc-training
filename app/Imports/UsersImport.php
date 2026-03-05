<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow
{
    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
    {
        $judulTraining = $row['training_title'] ?? null;
        $nikKaryawan   = $row['id'] ?? null;

        if (!$judulTraining || !$nikKaryawan) {
            return null;
        }

        $tanggal = $this->transformDate($row['date'] ?? null);

        // 🔥 1. LOGIKA DETEKSI TRAINER (Internal vs External)
        $trainerNameFromExcel = trim($row['trainer'] ?? '');
        $trainerInternalName = null;
        $trainerExternalName = null;

        if ($trainerNameFromExcel) {
            // Cek apakah nama ini ada di tabel employees
            $emp = DB::table('employees')
                ->where('name', 'like', '%' . $trainerNameFromExcel . '%')
                ->first();

            if ($emp) {
                // DISISIPKAN: Gabungkan NIK dan Nama agar cocok dengan Dropdown Layout
                $trainerInternalName = $emp->nik . ' - ' . $emp->name;
            } else {
                $trainerExternalName = $trainerNameFromExcel;
            }
        }

        // 🔥 2. CARI ATAU BUAT DATA TRAINING
        $training = DB::table('trainings')
            ->where('title', $judulTraining)
            ->where('training_date', $tanggal)
            ->first();

        if (!$training) {
            $trainingId = DB::table('trainings')->insertGetId([
                'title'                 => $judulTraining,
                'trainer_internal_name' => $trainerInternalName,
                'trainer_external_name' => $trainerExternalName,
                'held_by'               => $row['held_by'] ?? 'JEMBO',
                'activity_name'         => $row['activity'] ?? 'Internal',
                'skill_name'            => $row['skill'] ?? 'Hard Skill',
                'training_date'         => $tanggal,
                'start_time'            => $this->parseTime($row['time'] ?? null, 'start'),
                'finish_time'           => $this->parseTime($row['time'] ?? null, 'end'),
                'fee'                   => $row['fee'] ?? 0,
                'is_certified'          => 'No',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        } else {
            // Jika training sudah ada, kita update saja nama trainernya
            DB::table('trainings')->where('id', $training->id)->update([
                'trainer_internal_name' => $trainerInternalName,
                'trainer_external_name' => $trainerExternalName,
                'updated_at'            => now(),
            ]);
            $trainingId = $training->id;
        }

        // 🔥 3. Cari employee berdasarkan ID (NIK) untuk Peserta
        $employee = DB::table('employees')
            ->where('nik', trim($nikKaryawan))
            ->first();

        // 🔥 4. Jika karyawan ditemukan, masukkan sebagai peserta
         if ($employee) {
            DB::table('training_participants')->updateOrInsert(
                [
                    'training_id' => $trainingId,
                    'employee_id' => $employee->id,
                    'score'      => $row['evaluation'] ?? null,
                    'updated_at' => now(),
                ]
            );
        }

        return null;
    }
        
    private function parseTime($value, $type)
    {
        if (!$value) return null;

        if (str_contains($value, '-')) {
            $parts = explode('-', $value);
            $time = $type == 'start'
                ? ($parts[0] ?? null)
                : ($parts[1] ?? null);

            return $time ? str_replace('.', ':', trim($time)) : null;
        }

        return str_replace('.', ':', $value);
    }

    private function transformDate($value)
    {
        try {
            if (!$value) return now()->format('Y-m-d');

            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date
                    ::excelToDateTimeObject($value)
                    ->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}
