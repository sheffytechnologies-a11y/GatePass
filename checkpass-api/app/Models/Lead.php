<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'estate_name',
        'name',
        'role',
        'phone',
        'email',
        'city',
        'units',
        'deal_value',
        'stage',
        'source',
        'chat_transcript',
        'notes',
        'metadata',
        'admin_id',
        'estate_id',
    ];

    protected $casts = [
        'units' => 'integer',
        'deal_value' => 'float',
        'metadata' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->ulid)) {
                $lead->ulid = (string) Str::ulid();
            }
            if (empty($lead->deal_value) && !empty($lead->units)) {
                // CheckPass standard calculation: units * ₦1,200/mo * 12 months
                $lead->deal_value = $lead->units * 1200 * 12;
            }
        });
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function convertedEstate(): BelongsTo
    {
        return $this->belongsTo(Estate::class, 'estate_id');
    }
}
