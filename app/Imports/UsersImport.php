<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow, WithCalculatedFormulas
{
    private static $trainingCache = [];

    public function headingRow(): int
    {
        return 1;
    }

    public function model(array $row)
{
    // Gunakan trim dan pastikan data benar-benar ada
    $judulTraining = isset($row['judul_training']) ? trim($row['judul_training']) : '';
    $nikKaryawan = isset($row['nik']) ? trim((string)$row['nik']) : '';

    // PROTEKSI: Jika kolom utama kosong, langsung berhenti. 
    // Jangan biarkan proses berlanjut ke transformDate
    if (empty($judulTraining) || empty($nikKaryawan) || $judulTraining == '') {
        return null;
    }

    $tanggalRaw = $row['tanggal'] ?? null;
    $tanggal = $this->transformDate($tanggalRaw);

    // PROTEKSI: Jika tanggal gagal diparse (null), jangan import!
    if (!$tanggal) {
        return null;
    }

    $cacheKey = md5($judulTraining . $tanggal);

        // 2. LOGIKA TRAINER
        $trainerNameFromExcel = trim($row['trainer'] ?? '');
        $trainerEmployeeId = null;
        $trainerExternalName = null;

        if ($trainerNameFromExcel) {
            // Cari apakah ada angka (NIK) di dalam teks tersebut
            if (preg_match('/\d+/', $trainerNameFromExcel, $matches)) {
                // JIKA ADA ANGKA: Berarti Internal. Ambil NIK-nya (angka pertama yang ditemukan)
                $nikTrainer = $matches[0];

                $empTrainer = DB::table('employees')
                    ->where('nik', $nikTrainer)
                    ->first();

                if ($empTrainer) {
                    $trainerEmployeeId = $empTrainer->id;
                    $trainerExternalName = null; // Pastikan eksternal kosong
                } else {
                    // Jika ada NIK tapi tidak terdaftar di tabel employees, masukkan ke eksternal sebagai fallback
                    $trainerExternalName = $trainerNameFromExcel;
                }
            } else {
                // JIKA TIDAK ADA ANGKA: Berarti murni nama, kelompokkan sebagai Eksternal
                $trainerExternalName = $trainerNameFromExcel;
                $trainerEmployeeId = null;
            }
        }
        
        // 3. PROSES DATA TRAINING (INDUK)
        if (isset(self::$trainingCache[$cacheKey])) {
            $trainingId = self::$trainingCache[$cacheKey];
        } else {
            $training = DB::table('trainings')
                ->where('title', $judulTraining)
                ->where('training_date', $tanggal)
                ->first();

            $dataTraining = [
                'title'                 => $judulTraining,
                'held_by'               => $row['held_by'] ?? 'JEMBO',
                'activity_name'         => $row['activities'] ?? 'Internal',
                'skill_name'            => $row['skill'] ?? 'Hard Skill',
                'training_date'         => $tanggal,
                'start_time'            => $row['jam_mulai'] ?? null, // Sesuaikan header
                'finish_time'           => $row['jam_selesai'] ?? null, // Sesuaikan header
                'fee'                   => $row['biaya'] ?? 0,
                'is_certified'          => ($row['sertifikat'] ?? 'No') == 'Ada' ? 'Yes' : 'No',
                'trainer_employee_id'   => $trainerEmployeeId,
                'trainer_external_name' => $trainerExternalName,
                'updated_at'            => now(),
            ];

            if (!$training) {
                $dataTraining['created_at'] = now();
                $trainingId = DB::table('trainings')->insertGetId($dataTraining);
            } else {
                DB::table('trainings')->where('id', $training->id)->update($dataTraining);
                $trainingId = $training->id;
            }
            self::$trainingCache[$cacheKey] = $trainingId;
        }

        // 4. PROSES PESERTA
        $employee = DB::table('employees')->where('nik', $nikKaryawan)->first();

        if ($employee) {
            DB::table('training_participants')->updateOrInsert(
                ['training_id' => $trainingId, 'employee_id' => $employee->id],
                ['score' => $row['score'] ?? 0, 'updated_at' => now()]
            );
        }

        return null;
    }

    private function transformDate($value)
{
    if (empty($value)) return null; // Jangan diubah jadi now()

    try {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        
        // Pastikan format tanggal valid
        $parsed = Carbon::parse($value);
        return $parsed->format('Y-m-d');
    } catch (\Exception $e) {
        return null; // Jika format ngaco, kembalikan null agar baris ini dilewati
    }
}
}
