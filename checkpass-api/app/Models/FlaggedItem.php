<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlaggedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pass_id',
        'description',
        'photo_url',
        'photo_path',
        'flagged_at',
    ];

    protected $casts = [
        'flagged_at' => 'datetime',
    ];

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }
}
