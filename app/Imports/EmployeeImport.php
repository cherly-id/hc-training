<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Organization;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Ambil NIK dari kolom 'Employee ID' (menjadi 'employee_id')
        $nik = $row['employee_id'] ?? null;
        
        // Jika baris kosong atau NIK tidak ada, lewati
        if (!$nik) return null;

        // 2. Mapping Organisasi (Berdasarkan kolom 'Organization')
        $orgName = strtoupper(trim($row['organization'] ?? 'DEFAULT ORG'));
        $organization = Organization::firstOrCreate(['org_name' => $orgName]);

        // 3. Mapping Posisi (Berdasarkan kolom 'Position')
        $posName = strtoupper(trim($row['job_level'] ?? 'DEFAULT POS'));
        $position = Position::firstOrCreate(['position_name' => $posName]);

        // 4. Simpan ke Database
        return Employee::updateOrCreate(
            ['nik' => (string)$nik], // Identifier unik
            [
                'name'            => trim($row['full_name'] ?? 'No Name'), // Dari kolom 'Full Name'
                'org_id'          => $organization->id,
                'position_id'     => $position->id,
                'status_employee' => trim($row['status_employee'] ?? 'Permanent'), // Dari kolom 'Status Employee'
                'status'          => trim($row['status'] ?? 'Active'), // Dari kolom 'Status'
            ]
        );
    }
}