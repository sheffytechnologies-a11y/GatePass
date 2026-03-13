<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    public function emergencies()
    {
        return $this->hasMany(Emergency::class);
    }
}
