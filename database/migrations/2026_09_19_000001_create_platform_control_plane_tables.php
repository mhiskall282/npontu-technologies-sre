<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add platform-level administration attributes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('platform_role')->nullable()->after('role')->index()
                ->comment('Platform-level role: super_admin, platform_admin, platform_ops, security_admin, billing_admin, support_admin, auditor');
            $table->timestamp('suspended_at')->nullable()->after('deleted_at');
        });

        // 2. SaaS Plans & Entitlements catalog
        Schema::create('saas_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('tier')->default('free')->index(); // free, team, enterprise, customer_hosted
            $table->unsignedInteger('price_cents')->default(0);
            $table->string('billing_interval')->default('monthly'); // monthly, annual, perpetual
            $table->unsignedSmallInteger('trial_days')->default(14);
            $table->json('features')->nullable(); // Entitlements: max_workspaces, max_users, custom_sla, etc.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Organization Subscriptions & Billing State
        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('saas_plans')->cascadeOnDelete();
            $table->string('status')->default('active')->index(); // active, trialing, past_due, canceled, paused
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('billing_provider')->default('ready'); // ready, stripe, invoice, manual
            $table->string('billing_account_id')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });

        // 4. Feature Flags Engine
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false)->index();
            $table->json('target_tiers')->nullable(); // Target specific organization tiers ['enterprise', 'team']
            $table->json('target_org_ids')->nullable(); // Target specific org IDs [1, 5]
            $table->json('rules')->nullable(); // Extended conditions / rollout percentages
            $table->timestamps();
        });

        // 5. Security & SIEM Event Log
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name')->nullable();
            $table->string('event_type')->index(); // failed_login, password_reset, role_changed, token_revoked, suspension, elevation
            $table->string('severity')->default('info')->index(); // info, warning, critical
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->nullable()->index();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('saas_subscriptions');
        Schema::dropIfExists('saas_plans');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['platform_role', 'suspended_at']);
        });
    }
};
