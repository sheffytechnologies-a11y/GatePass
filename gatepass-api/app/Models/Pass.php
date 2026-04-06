<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
class Pass extends Model
{
    use HasFactory;

    protected $fillable = [
        'ulid',
        'resident_id',
        'estate_id',
        'visitor_name',
        'visitor_phone',
        'vehicle_plate',
        'purpose',
        'type',
        'recurring_days',
        'status',
        'items_flagged',
        'qr_data',
        'share_token',
        'expires_at',
        'arrived_at',
        'exited_at',
        'revoked_at',
    ];

    protected $casts = [
        'recurring_days' => 'array',
        'items_flagged'  => 'boolean',
        'expires_at'     => 'datetime',
        'arrived_at'     => 'datetime',
        'exited_at'      => 'datetime',
        'revoked_at'     => 'datetime',
    ];

    // Use ulid as the public route key
    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pass) {
            if (empty($pass->ulid)) {
                $pass->ulid = Str::ulid();
            }
            if (empty($pass->share_token)) {
                $pass->share_token = Str::random(32);
            }
        });
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function flaggedItems()
    {
        return $this->hasMany(FlaggedItem::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['Pending', 'On-site']);
    }

    public function canRevoke(): bool
    {
        return in_array($this->status, ['Pending', 'On-site']);
    }
}
