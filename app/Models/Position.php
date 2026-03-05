<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';
    protected $primaryKey = 'position_id';
    protected $fillable = ['position_name', 'organization_id'];
    

    // Relasi balik ke Organization (Belongs To)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'org_id');
    }

    // Relasi ke Employee (One to Many)
    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id', 'position_id');
    }
}