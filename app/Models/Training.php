<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// 1. IMPORT the related models if they are in the same folder or different ones
use App\Models\Employee; 
use App\Models\training_schedules; // Ensure this name matches your other Model file

class Training extends Model
{
    protected $table = 'trainings';

    protected $fillable = [
        'title', 'held_by', 'activity_id', 'skill_id', 
        'training_date', 'start_time', 'finish_time', 'fee'
    ];

    public function participants()
    {
        return $this->belongsToMany(Employee::class, 'training_participants', 'training_id', 'employee_id')
                    ->withPivot('score');
    }

    public function trainers()
    {
        return $this->belongsToMany(Employee::class, 'trainer_training', 'training_id', 'employee_id')
                    ->withTimestamps();
    }

    // public function schedules()
    // {
    //     // 2. Use PascalCase for the class name
    //     return $this->hasMany(training_schedules::class, 'training_id', 'id');
    // }
}