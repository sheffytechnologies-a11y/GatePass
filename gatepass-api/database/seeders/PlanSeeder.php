<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'free_trial',
                'name' => 'Free Trial',
                'unit_limit' => 3,
                'resident_limit' => 9,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'price_addon_unit_monthly' => 800,
                'is_custom' => false,
                'sort_order' => 0,
            ],
            [
                'code' => 'tier_50',
                'name' => '50 Units Plan',
                'unit_limit' => 50,
                'resident_limit' => 150,
                'price_monthly' => 35000,
                'price_yearly' => 350000,
                'price_addon_unit_monthly' => null,
                'is_custom' => false,
                'sort_order' => 1,
            ],
            [
                'code' => 'tier_200',
                'name' => '200 Units Plan',
                'unit_limit' => 200,
                'resident_limit' => 600,
                'price_monthly' => 95000,
                'price_yearly' => 950000,
                'price_addon_unit_monthly' => null,
                'is_custom' => false,
                'sort_order' => 2,
            ],
            [
                'code' => 'tier_1000',
                'name' => '1,000 Units Plan',
                'unit_limit' => 1000,
                'resident_limit' => 3000,
                'price_monthly' => 250000,
                'price_yearly' => 2500000,
                'price_addon_unit_monthly' => null,
                'is_custom' => false,
                'sort_order' => 3,
            ],
            [
                'code' => 'custom',
                'name' => '1,000+ Units (Custom)',
                'unit_limit' => null,
                'resident_limit' => null,
                'price_monthly' => null,
                'price_yearly' => null,
                'price_addon_unit_monthly' => null,
                'is_custom' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
