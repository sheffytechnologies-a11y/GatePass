<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'pass_id',
        'name',
        'description',
        'photo',
    ];

    public function pass()
    {
        return $this->belongsTo(Pass::class);
    }
}
