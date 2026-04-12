<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Estate;
use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('admins') || ! Schema::hasTable('estates') || ! Schema::hasTable('news')) {
            $this->command?->warn('Skipping NewsSeeder: required tables admins, estates, or news do not exist yet.');

            return;
        }

        $estate = Estate::query()->first() ?? Estate::create([
            'name' => 'PHDL Estate',
            'address' => '1 PHDL Drive',
            'city' => 'Port Harcourt',
            'state' => 'Rivers',
            'country' => 'Nigeria',
            'bank_name' => 'Fidelity Bank',
            'account_number' => '1234567890',
            'account_name' => 'PHDL Estate',
            'is_active' => true,
        ]);

        $admin = Admin::query()->first() ?? Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@gatepass.com',
            'password' => Hash::make('123456'),
            'is_active' => true,
        ]);

        $newsItems = [
            [
                'title' => 'Scheduled Water Maintenance',
                'content' => 'Water supply will be interrupted on Saturday from 9:00 AM to 1:00 PM for routine maintenance across the estate.',
                'image' => null,
            ],
            [
                'title' => 'New Visitor Pass Policy',
                'content' => 'Residents are advised to create visitor passes at least 30 minutes before guest arrival to reduce gate delays.',
                'image' => null,
            ],
            [
                'title' => 'Community Sanitation Exercise',
                'content' => 'A community sanitation exercise will hold this last Saturday of the month. Residents are encouraged to participate.',
                'image' => null,
            ],
        ];

        foreach ($newsItems as $item) {
            News::updateOrCreate(
                [
                    'estate_id' => $estate->id,
                    'title' => $item['title'],
                ],
                [
                    'admin_id' => $admin->id,
                    'content' => $item['content'],
                    'image' => $item['image'],
                ]
            );
        }
    }
}
