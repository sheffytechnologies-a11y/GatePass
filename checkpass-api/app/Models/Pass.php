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
        'verified_by',
        'qr_data',
        'pass_code',
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
            if (empty($pass->pass_code)) {
                $pass->pass_code = static::generateUniqueCode($pass->estate_id);
            }
        });
    }

    /**
     * Generate a unique 6-digit numeric gate code among active passes in the same estate.
     */
    public static function generateUniqueCode(?int $estateId): string
    {
        for ($i = 0; $i < 25; $i++) {
            $code = (string) random_int(100000, 999999);
            $exists = static::query()
                ->when($estateId !== null, fn($q) => $q->where('estate_id', $estateId))
                ->where('pass_code', $code)
                ->whereIn('status', ['Pending', 'On-site'])
                ->exists();
            if (! $exists) {
                return $code;
            }
        }

        return (string) random_int(100000, 999999);
    }

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function flaggedItems()
    {
        return $this->hasMany(Item::class);
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
