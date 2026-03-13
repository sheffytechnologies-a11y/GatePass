<?php

namespace Database\Seeders;

use App\Models\Estate;
use App\Models\Resident;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $estate = Estate::create([
            'name' => 'PHDL Estate',
            'address' => '1 PHDL Drive',
            'city' => 'Port Harcourt',
            'state' => 'Rivers',
            'country' => 'Nigeria',
        ]);

        $unit = Unit::create([
            'estate_id' => $estate->id,
            'lane' => 'FA',
            'house' => 'H7',
            'flat' => 'L3',
            'flat_address' => 'L3, H7, FA',
        ]);

        Unit::create([
            'estate_id' => $estate->id,
            'lane' => 'FA',
            'house' => 'H7',
            'flat' => 'L4',
            'flat_address' => 'L4, H7, FA',
        ]);

        $user = User::create([
            'name' => 'Demo Resident',
            'email' => 'demo@phdlestate.ng',
            'phone' => '+2348000000001',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        Resident::create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
            'estate_id' => $estate->id,
            'role' => 'primary',
            'push_enabled' => false,
            'arrival_alerts' => true,
            'expiry_alerts' => true,
            'is_active' => true,
        ]);
    }
}
