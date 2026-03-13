<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'unit_id',
        'estate_id',
        'role',
        'push_enabled',
        'push_token',
        'arrival_alerts',
        'expiry_alerts',
        'is_active',
    ];

    protected $casts = [
        'push_enabled'   => 'boolean',
        'arrival_alerts' => 'boolean',
        'expiry_alerts'  => 'boolean',
        'is_active'      => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function emergencies()
    {
        return $this->hasMany(Emergency::class);
    }

    // Convenience: flat address from unit
    public function getFlatAddressAttribute(): string
    {
        return $this->unit?->flat_address ?? '';
    }

    // Convenience: estate name
    public function getEstateNameAttribute(): string
    {
        return $this->estate?->name ?? '';
    }
}
