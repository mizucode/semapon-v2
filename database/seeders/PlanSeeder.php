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
                'name' => 'Starter',
                'slug' => 'starter',
                'price_monthly' => 99000,
                'price_yearly' => 990000,
                'max_technicians' => 1,
                'features' => [
                    'max_technicians' => 1,
                    'service_orders' => true,
                    'customer_tracking' => true,
                    'thermal_print' => true,
                    'inventory' => true,
                    'analytics' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price_monthly' => 199000,
                'price_yearly' => 1990000,
                'max_technicians' => 3,
                'features' => [
                    'max_technicians' => 3,
                    'service_orders' => true,
                    'customer_tracking' => true,
                    'thermal_print' => true,
                    'inventory' => true,
                    'supplier_purchase' => true,
                    'finance_cashbook' => true,
                    'analytics' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price_monthly' => 399000,
                'price_yearly' => 3990000,
                'max_technicians' => 10,
                'features' => [
                    'max_technicians' => 10,
                    'service_orders' => true,
                    'customer_tracking' => true,
                    'thermal_print' => true,
                    'inventory' => true,
                    'supplier_purchase' => true,
                    'finance_cashbook' => true,
                    'analytics' => true,
                    'priority_support' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
