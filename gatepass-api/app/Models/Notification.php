<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'pass_id',
        'type',
        'message',
        'read',
        'read_at',
    ];

    protected $casts = [
        'read'    => 'boolean',
        'read_at' => 'datetime',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function pass()
    {
        return $this->belongsTo(Pass::class)->withDefault();
    }
}
