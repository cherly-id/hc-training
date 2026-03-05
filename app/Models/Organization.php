<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Organization extends Model
{
    // protected $table = 'organizations';
    // protected $primaryKey = 'org_id'; // Definisi PK sesuai ERD
    protected $fillable = ['org_name'];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    // Relasi ke Employee (One to Many)
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
