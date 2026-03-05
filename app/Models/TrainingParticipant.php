<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingParticipant extends Model
{
    protected $table = 'training_participants';
    protected $fillable = [
        'employee_id', 
        'training_id', 
        'score'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id', 'training_id');
    }
}
