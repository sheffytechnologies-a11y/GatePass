<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artisan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rating' => 'float',
        'experience_years' => 'integer',
        'jobs_completed' => 'integer',
        'gate_pass_cleared' => 'boolean',
        'police_cleared' => 'boolean',
        'id_verified' => 'boolean',
        'skills' => 'array',
        'reviews' => 'array',
        'recent_gate_logs' => 'array',
    ];

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }
}
