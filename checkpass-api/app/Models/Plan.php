<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit_limit',
        'resident_limit',
        'price_monthly',
        'price_yearly',
        'price_addon_unit_monthly',
        'is_custom',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'unit_limit' => 'integer',
        'resident_limit' => 'integer',
        'price_monthly' => 'float',
        'price_yearly' => 'float',
        'price_addon_unit_monthly' => 'float',
        'is_custom' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
