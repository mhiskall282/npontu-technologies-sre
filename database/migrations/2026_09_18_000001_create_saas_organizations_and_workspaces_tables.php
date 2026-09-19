<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Organizations table
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_code')->unique();
            $table->string('status')->default('active'); // active, pending, suspended, rejected
            $table->string('tier')->default('free'); // free, team, enterprise, customer_hosted
            $table->string('deployment_model')->default('shared_saas'); // shared_saas, dedicated_managed, customer_funded, customer_hosted
            $table->string('preferred_region')->default('af-south'); // af-south, us-east, eu-west
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'tier']);
            $table->index('company_code');
        });

        // 2. Organization Applications (Self-service registration & admin review lifecycle)
        Schema::create('organization_applications', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_slug')->index();
            $table->foreignId('applicant_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('contact_email');
            $table->string('status')->default('pending_review'); // pending_review, approved, rejected, additional_info_required
            $table->string('deployment_model')->default('shared_saas');
            $table->string('preferred_region')->default('af-south');
            $table->string('tier')->default('team');
            $table->text('notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->integer('risk_score')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // 3. Organization Memberships
        Schema::create('organization_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member'); // owner, admin, member, billing_admin, security_officer
            $table->string('department')->nullable();
            $table->string('grade')->nullable();
            $table->json('privileges')->nullable();
            $table->string('status')->default('active'); // active, suspended, invited
            $table->timestamps();

            $table->unique(['organization_id', 'user_id'], 'org_user_unique');
            $table->index(['user_id', 'status']);
        });

        // 4. Workspaces table
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->boolean('is_personal')->default(false);
            $table->string('status')->default('active'); // active, suspended, archived
            $table->integer('retention_days')->default(90);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index('owner_user_id');
            $table->index('subdomain');
        });

        // 5. Workspace Memberships
        Schema::create('workspace_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('agent'); // admin, lead, agent, viewer
            $table->string('status')->default('active'); // active, suspended
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id'], 'ws_user_unique');
            $table->index(['user_id', 'status']);
        });

        // 6. Add workspace_id foreign keys to operational tables
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index('workspace_id');
            $table->index(['workspace_id', 'is_active']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index('workspace_id');
        });

        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index(['workspace_id', 'date']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index('workspace_id');
        });

        Schema::table('operational_notifications', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index('workspace_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('workspace_id')->nullable()->after('id')->constrained('workspaces')->nullOnDelete();
            $table->index('workspace_id');
        });

        // 7. Seed Legacy Default Organization and Workspace for Zero-Data-Loss Migration
        $now = now();
        $orgId = DB::table('organizations')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => 'Opsora SRE (Legacy Npontu)',
            'slug' => 'opsora-sre-primary',
            'company_code' => 'NPT-OPS-01',
            'status' => 'active',
            'tier' => 'enterprise',
            'deployment_model' => 'shared_saas',
            'preferred_region' => 'af-south',
            'settings' => json_encode(['migrated_from_legacy' => true, 'legacy_support' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $wsId = DB::table('workspaces')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $orgId,
            'owner_user_id' => null,
            'name' => 'Primary SRE Operations',
            'slug' => 'primary-sre',
            'subdomain' => 'primary',
            'custom_domain' => null,
            'is_personal' => false,
            'status' => 'active',
            'retention_days' => 90,
            'settings' => json_encode(['migrated_from_legacy' => true]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Backfill all existing users into default organization & workspace
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $orgRole = match ($user->role ?? 'agent') {
                'admin' => 'owner',
                'lead' => 'admin',
                default => 'member',
            };

            $wsRole = match ($user->role ?? 'agent') {
                'admin' => 'admin',
                'lead' => 'lead',
                default => 'agent',
            };

            DB::table('organization_memberships')->insertOrIgnore([
                'organization_id' => $orgId,
                'user_id' => $user->id,
                'role' => $orgRole,
                'department' => $user->department ?? null,
                'grade' => $user->grade ?? null,
                'privileges' => $user->privileges ?? null,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('workspace_memberships')->insertOrIgnore([
                'workspace_id' => $wsId,
                'user_id' => $user->id,
                'role' => $wsRole,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Backfill workspace_id = $wsId onto all existing operational records
        DB::table('activities')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
        DB::table('activity_logs')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
        DB::table('shift_handovers')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
        DB::table('conversations')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
        DB::table('operational_notifications')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
        DB::table('audit_logs')->whereNull('workspace_id')->update(['workspace_id' => $wsId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys and columns from operational tables
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        if (Schema::hasTable('operational_notifications')) {
            Schema::table('operational_notifications', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        if (Schema::hasTable('shift_handovers')) {
            Schema::table('shift_handovers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        // 2. Drop multi-tenant tables in reverse dependency order
        Schema::dropIfExists('workspace_memberships');
        Schema::dropIfExists('workspaces');
        Schema::dropIfExists('organization_memberships');
        Schema::dropIfExists('organization_applications');
        Schema::dropIfExists('organizations');
    }
};
