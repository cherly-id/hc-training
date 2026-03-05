<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Menambahkan kolom trainer setelah kolom 'held_by'
            $table->unsignedBigInteger('trainer_employee_id')->nullable()->after('held_by');
            $table->string('trainer_external_name')->nullable()->after('trainer_employee_id');

            // Opsional: Tambahkan foreign key agar relasi ke tabel employees terjaga
            $table->foreign('trainer_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Hapus foreign key dan kolom jika migration di-rollback
            $table->dropForeign(['trainer_employee_id']);
            $table->dropColumn(['trainer_employee_id', 'trainer_external_name']);
        });
    }
};