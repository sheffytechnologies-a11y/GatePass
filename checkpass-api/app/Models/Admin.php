<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function estates(): BelongsToMany
    {
        return $this->belongsToMany(Estate::class, 'admin_estate')->withTimestamps();
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isEstateAdmin(): bool
    {
        return $this->role === 'estate_admin';
    }

    /**
     * The single estate an estate_admin manages (self-registered admins
     * are scoped to exactly one estate). Returns null for super_admin
     * or an estate_admin who hasn't created their estate yet.
     */
    public function currentEstateId(): ?int
    {
        return $this->estates()->first()?->id;
    }
}
