<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paychat_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('core');
            $table->string('source')->default('catalog');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('paychat_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('INR');
            $table->unsignedInteger('trial_days')->default(0);
            $table->boolean('is_trial')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('paychat_feature_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paychat_feature_id')->constrained('paychat_features')->cascadeOnDelete();
            $table->foreignId('paychat_pricing_plan_id')->constrained('paychat_pricing_plans')->cascadeOnDelete();
            $table->json('limits')->nullable();
            $table->timestamps();

            $table->unique(['paychat_feature_id', 'paychat_pricing_plan_id'], 'feature_plan_unique');
        });

        $this->seedFeatureCatalog();
        $this->seedPricingPlans();
    }

    public function down(): void
    {
        Schema::dropIfExists('paychat_feature_plan');
        Schema::dropIfExists('paychat_pricing_plans');
        Schema::dropIfExists('paychat_features');
    }

    private function seedFeatureCatalog(): void
    {
        $features = [
            ['key' => 'pos', 'name' => 'POS Billing', 'category' => 'billing', 'description' => 'Create bills, carts, payments, and customer receipts.'],
            ['key' => 'orders', 'name' => 'Order Management', 'category' => 'billing', 'description' => 'Track running, completed, cancelled, and synced orders.'],
            ['key' => 'payments', 'name' => 'Payment Collection', 'category' => 'billing', 'description' => 'Collect cash or UPI payments and handle payment correction.'],
            ['key' => 'gst_invoice', 'name' => 'GST Invoice', 'category' => 'billing', 'description' => 'Generate GST-ready invoices and downloadable invoice PDFs.'],
            ['key' => 'offline_orders', 'name' => 'Offline Order Sync', 'category' => 'billing', 'description' => 'Create orders while offline and sync them safely later.'],
            ['key' => 'self_pos', 'name' => 'Self POS QR', 'category' => 'ordering', 'description' => 'Customer self-ordering through table or counter QR links.'],
            ['key' => 'dine_in', 'name' => 'Dine-in Tables', 'category' => 'restaurant', 'description' => 'Manage tables, sessions, guests, and final billing.'],
            ['key' => 'kds', 'name' => 'Kitchen Display', 'category' => 'restaurant', 'description' => 'Send KOT batches to kitchen and update preparation status.'],
            ['key' => 'token_management', 'name' => 'Token Management', 'category' => 'restaurant', 'description' => 'Generate customer-facing token numbers and order status screens.'],
            ['key' => 'inventory', 'name' => 'Inventory', 'category' => 'operations', 'description' => 'Manage stock, low-stock alerts, and product inventory.'],
            ['key' => 'products', 'name' => 'Products & Menu', 'category' => 'operations', 'description' => 'Manage sellable items, pricing, categories, images, and favorites.'],
            ['key' => 'customer_management', 'name' => 'Customer Management', 'category' => 'customers', 'description' => 'Store customers, order history, loyalty balances, and feedback context.'],
            ['key' => 'loyalty', 'name' => 'Loyalty Settings', 'category' => 'customers', 'description' => 'Configure loyalty points and customer reward behavior.'],
            ['key' => 'reports', 'name' => 'Reports', 'category' => 'analytics', 'description' => 'View billing, payment, product, and operational reports.'],
            ['key' => 'settings', 'name' => 'Tenant Settings', 'category' => 'operations', 'description' => 'Manage receipt, tax, module, UPI, and tenant preferences.'],
            ['key' => 'bakery_management', 'name' => 'Bakery Orders', 'category' => 'industry', 'description' => 'Manage custom bakery orders, production, payments, and delivery dates.'],
            ['key' => 'registration_management', 'name' => 'Registration & Membership', 'category' => 'industry', 'description' => 'Manage programs, batches, participants, registrations, and fees.'],
            ['key' => 'staff_assignment', 'name' => 'Staff Assignment', 'category' => 'staff', 'description' => 'Support staff-aware operational workflows.'],
            ['key' => 'appointments', 'name' => 'Appointments', 'category' => 'industry', 'description' => 'Appointment-oriented workflows for service businesses.'],
            ['key' => 'customer_display', 'name' => 'Customer Display', 'category' => 'restaurant', 'description' => 'Display customer-facing order and invoice status screens.'],
        ];

        foreach ($features as $feature) {
            DB::table('paychat_features')->updateOrInsert(
                ['key' => $feature['key']],
                $feature + ['source' => 'codebase', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function seedPricingPlans(): void
    {
        $plans = [
            [
                'key' => 'trial',
                'name' => 'Trial',
                'description' => 'Default onboarding plan for new tenants when no plan is selected.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'trial_days' => 14,
                'is_trial' => true,
                'sort_order' => 1,
                'features' => ['pos', 'orders', 'payments', 'gst_invoice', 'products', 'customer_management', 'settings'],
            ],
            [
                'key' => 'starter',
                'name' => 'Starter',
                'description' => 'Core POS billing plan for small counters.',
                'monthly_price' => 999,
                'yearly_price' => 9990,
                'trial_days' => 0,
                'is_trial' => false,
                'sort_order' => 2,
                'features' => ['pos', 'orders', 'payments', 'gst_invoice', 'products', 'inventory', 'customer_management', 'reports', 'settings'],
            ],
            [
                'key' => 'pro',
                'name' => 'Pro',
                'description' => 'Full operations plan for restaurants, cafes, bakeries, and growing stores.',
                'monthly_price' => 1999,
                'yearly_price' => 19990,
                'trial_days' => 0,
                'is_trial' => false,
                'sort_order' => 3,
                'features' => ['pos', 'orders', 'payments', 'gst_invoice', 'offline_orders', 'self_pos', 'dine_in', 'kds', 'token_management', 'inventory', 'products', 'customer_management', 'loyalty', 'reports', 'settings', 'bakery_management', 'customer_display'],
            ],
        ];

        foreach ($plans as $plan) {
            $featureKeys = $plan['features'];
            unset($plan['features']);

            DB::table('paychat_pricing_plans')->updateOrInsert(
                ['key' => $plan['key']],
                $plan + ['currency' => 'INR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            $planId = DB::table('paychat_pricing_plans')->where('key', $plan['key'])->value('id');

            foreach ($featureKeys as $featureKey) {
                $featureId = DB::table('paychat_features')->where('key', $featureKey)->value('id');

                if ($planId && $featureId) {
                    DB::table('paychat_feature_plan')->updateOrInsert(
                        ['paychat_pricing_plan_id' => $planId, 'paychat_feature_id' => $featureId],
                        ['updated_at' => now(), 'created_at' => now()]
                    );
                }
            }
        }
    }
};
