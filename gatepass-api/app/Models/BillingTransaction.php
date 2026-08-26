<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'estate_id',
        'subscription_id',
        'paystack_reference',
        'amount',
        'currency',
        'type',
        'status',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
