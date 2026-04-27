<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Training extends Model
{
    use SoftDeletes;

    protected $table = 'trainings';

    protected $fillable = [
        'title', 
        'held_by', 
        'activity_name', 
        'skill_name',    
        'training_date', 
        'start_time', 
        'finish_time', 
        'fee',
        'is_certified',
        'trainer_employee_id',
        'trainer_external_name'
    ];

    // Penting agar format tanggal konsisten
    protected $casts = [
        'training_date' => 'date',
        'fee' => 'decimal:2',
    ];

    /**
     * Relasi ke Peserta
     * Ini benar jika tabel pivotnya adalah training_participants
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'training_participants', 'training_id', 'employee_id')
                    ->withPivot('id', 'score') // Ambil ID pivot untuk mempermudah update skor
                    ->withTimestamps();
    }

    public function trainerInternal(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'trainer_employee_id');
    }
}