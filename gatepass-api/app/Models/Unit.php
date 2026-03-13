<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'estate_id',
        'lane',
        'house',
        'flat',
        'flat_address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function passes()
    {
        return $this->hasManyThrough(Pass::class, Resident::class);
    }
}
