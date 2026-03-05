<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id'; // Sesuai ERD, PK-nya adalah 'id'
    protected $fillable = [
        'org_id',
        'position_id',
        'level_id',
        'trainer_id',
        'training_id',
        'nik',
        'name',
        'status',
        'status_employee',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    // Relasi Many-to-Many ke Training melalui tabel pivot training_participants
    public function trainings()
    {
        return $this->belongsToMany(Training::class, 'training_participants', 'employee_id', 'training_id')
            ->withPivot('score'); // Mengambil field score di tabel pivot
    }
}
