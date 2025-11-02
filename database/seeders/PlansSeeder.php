<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Illuminate\Support\Str;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'id' => Str::uuid(),
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Free plan with basic features for personal use',
                'price' => 0.00,
                'currency' => 'USD',
                'billing_cycle' => 'lifetime',
                'billing_cycle_days' => 0,
                'features' => [
                    '1 user',
                    '20 documents',
                    '5GB storage',
                    'Basic support',
                    'Forever free'
                ],
                'limits' => [
                    'max_users' => 1,
                    'max_clients' => 5,
                    'max_documents' => 20,
                    'max_storage_gb' => 5,
                    'has_ai_features' => false,
                    'has_quickbooks_integration' => false,
                    'has_api_access' => false,
                    'has_priority_support' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 0,
                'sort_order' => 0,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Basic plan for small businesses',
                'price' => 29.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'billing_cycle_days' => 30,
                'features' => [
                    'Up to 2 users',
                    'Up to 10 clients',
                    'Up to 100 documents',
                    '1GB storage',
                    'Email support',
                    'Basic QuickBooks integration'
                ],
                'limits' => [
                    'max_users' => 2,
                    'max_clients' => 10,
                    'max_documents' => 100,
                    'max_storage_gb' => 1,
                    'has_ai_features' => false,
                    'has_quickbooks_integration' => true,
                    'has_api_access' => false,
                    'has_priority_support' => false,
                ],
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 14,
                'sort_order' => 1,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Professional plan for growing businesses',
                'price' => 59.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'billing_cycle_days' => 30,
                'features' => [
                    'Up to 10 users',
                    'Up to 50 clients',
                    'Up to 500 documents',
                    '5GB storage',
                    'AI/OCR features',
                    'Full QuickBooks integration',
                    'API access',
                    'Priority support'
                ],
                'limits' => [
                    'max_users' => 10,
                    'max_clients' => 50,
                    'max_documents' => 500,
                    'max_storage_gb' => 5,
                    'has_ai_features' => true,
                    'has_quickbooks_integration' => true,
                    'has_api_access' => true,
                    'has_priority_support' => true,
                ],
                'is_active' => true,
                'is_popular' => true,
                'trial_days' => 14,
                'sort_order' => 2,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Enterprise plan for large organizations',
                'price' => 149.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'billing_cycle_days' => 30,
                'features' => [
                    'Unlimited users',
                    'Unlimited clients',
                    'Unlimited documents',
                    '50GB storage',
                    'Advanced AI/OCR features',
                    'Full QuickBooks integration',
                    'Full API access',
                    '24/7 priority support',
                    'Dedicated account manager'
                ],
                'limits' => [
                    'max_users' => -1, // Unlimited
                    'max_clients' => -1, // Unlimited
                    'max_documents' => -1, // Unlimited
                    'max_storage_gb' => 50,
                    'has_ai_features' => true,
                    'has_quickbooks_integration' => true,
                    'has_api_access' => true,
                    'has_priority_support' => true,
                ],
                'is_active' => true,
                'is_popular' => false,
                'trial_days' => 30,
                'sort_order' => 3,
            ]
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}