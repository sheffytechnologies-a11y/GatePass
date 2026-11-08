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
        'unit_type',
        'hierarchy_data',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hierarchy_data' => 'array',
    ];

    public function isOccupied(): bool
    {
        return $this->residents()->where('is_active', true)->exists();
    }

    public function activeResidents()
    {
        return $this->residents()->where('is_active', true);
    }

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
