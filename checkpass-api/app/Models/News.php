<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'estate_id',
        'admin_id',
        'title',
        'content',
        'image',
    ];

    protected $with = ['admin'];

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

}
