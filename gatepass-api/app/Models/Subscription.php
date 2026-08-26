<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'estate_id',
        'plan_id',
        'billing_cycle',
        'status',
        'current_period_start',
        'current_period_end',
        'extra_units',
        'paystack_reference',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'extra_units' => 'integer',
    ];

    public function estate(): BelongsTo
    {
        return $this->belongsTo(Estate::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class);
    }

    /**
     * Days remaining until the current period ends (null when the
     * subscription has no period, e.g. an untouched free trial).
     */
    public function daysUntilRenewal(): ?int
    {
        if (! $this->current_period_end) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->current_period_end->startOfDay(), false);
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }
}
