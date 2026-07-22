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
                'slug' => 'free',
                'name' => 'Free',
                'stripe_price_id' => null,
                'price_jpy' => 0,
                'trial_days' => 0,
                'features' => [
                    'document_limit' => 3,
                    'attachments' => false,
                    'priority_support' => false,
                ],
                'sort_order' => 1,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'stripe_price_id' => config('services.stripe.prices.pro'),
                'price_jpy' => 1500,
                'trial_days' => 14,
                'features' => [
                    'document_limit' => null,
                    'attachments' => true,
                    'priority_support' => false,
                ],
                'sort_order' => 2,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'stripe_price_id' => config('services.stripe.prices.enterprise'),
                'price_jpy' => 5000,
                'trial_days' => 14,
                'features' => [
                    'document_limit' => null,
                    'attachments' => true,
                    'priority_support' => true,
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
