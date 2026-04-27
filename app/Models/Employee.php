<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Prunable; // Import ini

class Employee extends Model
{
    use SoftDeletes; 

    protected $table = 'employees';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'org_id', 'position_id', 'level_id', 'trainer_id',
        'training_id', 'nik', 'name', 'status', 'status_employee',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'training_participants', 'employee_id', 'training_id')
            ->withPivot('score');
    }

    // public function prunable()
    // {
    //     // Ambil data yang sudah di-soft delete lebih dari 30 hari
    //     return static::where('deleted_at', '<=', now()->subDays(30));
    // }
}