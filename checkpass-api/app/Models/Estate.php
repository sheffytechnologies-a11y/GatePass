<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'bank_name',
        'account_number',
        'account_name',
        'property_structure',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'property_structure' => 'array',
    ];

    public function getPropertyStructureOrDefault(): array
    {
        if (! empty($this->property_structure)) {
            return $this->property_structure;
        }

        return [
            'preset' => 'lane_house_flat',
            'levels' => [
                ['level' => 1, 'name' => 'Lane', 'slug' => 'lane', 'required' => true],
                ['level' => 2, 'name' => 'House', 'slug' => 'house', 'required' => true],
                ['level' => 3, 'name' => 'Flat', 'slug' => 'flat', 'required' => false],
            ],
        ];
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function residents()
    {
        return $this->hasMany(Resident::class);
    }

    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    public function emergencies()
    {
        return $this->hasMany(Emergency::class);
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'admin_estate')->withTimestamps();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
