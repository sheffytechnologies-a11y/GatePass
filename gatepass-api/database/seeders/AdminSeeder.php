<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('admins')) {
            $this->command?->warn('Skipping AdminSeeder: admins table does not exist yet.');

            return;
        }

        Admin::updateOrCreate(
            ['email' => 'admin@gatepass.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ]
        );
    }
}
