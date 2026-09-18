<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\OperationalNotification;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Services\AuditService;
use App\Services\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SaasMigrateLegacyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opsora:migrate-legacy
                            {--dry-run : Validate and preview counts without executing database mutations}
                            {--target-org= : Target Organization ID or slug (defaults to default org)}
                            {--target-workspace= : Target Workspace ID or slug (defaults to primary workspace)}
                            {--batch-size=500 : Batch size for chunked updates}
                            {--rollback : Unlink migrated records back to null workspace_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely inspect, validate, and migrate legacy operational records into multi-tenant workspace context with audit reconciliation';

    public function handle(AuditService $auditService): int
    {
        $this->line('');
        $this->info('===========================================================');
        $this->info('  Opsora Multi-Tenant Legacy Migration Engine (Stage 15)  ');
        $this->info('===========================================================');
        $this->line('');

        $isDryRun = (bool) $this->option('dry-run');
        $isRollback = (bool) $this->option('rollback');
        $batchSize = max(10, (int) $this->option('batch-size'));

        // 1. Resolve Target Organization & Workspace
        $organization = $this->resolveOrganization();
        if (! $organization) {
            $this->error('Target organization could not be resolved. Please create or specify an organization first.');

            return Command::FAILURE;
        }

        $workspace = $this->resolveWorkspace($organization);
        if (! $workspace) {
            $this->error('Target workspace could not be resolved for organization: '.$organization->name);

            return Command::FAILURE;
        }

        $this->comment("Target Organization : [ID: {$organization->id}] {$organization->name} ({$organization->slug})");
        $this->comment("Target Workspace    : [ID: {$workspace->id}] {$workspace->name} ({$workspace->slug})");
        $this->comment('Execution Mode      : '.($isDryRun ? '<fg=yellow>DRY-RUN (Read-Only Preview)</>' : ($isRollback ? '<fg=red>ROLLBACK</>' : '<fg=green>ACTIVE PRODUCTION IMPORT</>')));
        $this->line('');

        if ($isRollback) {
            return $this->executeRollback($workspace);
        }

        // 2. Read-Only Extraction & Orphan Inspection
        $stats = $this->inspectUnmappedRecords();

        $this->table(
            ['Operational Domain', 'Table Name', 'Unmapped Records (workspace_id = NULL)'],
            [
                ['Operational Activities', 'activities', $stats['activities']],
                ['Activity Change Logs', 'activity_logs', $stats['activity_logs']],
                ['Shift Handovers', 'shift_handovers', $stats['shift_handovers']],
                ['Operational Conversations', 'conversations', $stats['conversations']],
                ['Operational Notifications', 'operational_notifications', $stats['operational_notifications']],
                ['Compliance Audit Logs', 'audit_logs', $stats['audit_logs']],
                ['Active Users without Workspace', 'users', $stats['users_without_membership']],
            ]
        );

        $totalUnmapped = array_sum(array_slice($stats, 0, 6));

        if ($totalUnmapped === 0 && $stats['users_without_membership'] === 0) {
            $this->info('All operational records and users are already assigned to valid workspaces. No migration necessary.');

            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->line('');
            $this->warn("DRY-RUN COMPLETE: {$totalUnmapped} operational records and {$stats['users_without_membership']} user memberships would be assigned to Workspace #{$workspace->id}.");
            $this->line('No database mutations were executed.');

            return Command::SUCCESS;
        }

        // 3. Active Migration Execution
        $this->line('');
        $this->info('Initiating atomic data migration into workspace context...');

        $migratedCounts = DB::transaction(function () use ($workspace, $organization, $auditService) {
            return TenantContext::withoutTenancy(function () use ($workspace, $organization, $auditService) {
                // 3a. Update unmapped activities
                $actCount = Activity::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3b. Update unmapped activity logs
                $logCount = ActivityLog::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3c. Update unmapped shift handovers
                $handoverCount = ShiftHandover::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3d. Update unmapped conversations
                $convCount = Conversation::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3e. Update unmapped operational notifications
                $notifCount = OperationalNotification::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3f. Update unmapped audit logs
                $auditCount = AuditLog::withoutGlobalScopes()
                    ->whereNull('workspace_id')
                    ->update(['workspace_id' => $workspace->id]);

                // 3g. Backfill memberships for all active users
                $users = User::all();
                $usersEnrolled = 0;
                foreach ($users as $user) {
                    // Organization Membership
                    OrganizationMembership::firstOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'user_id' => $user->id,
                        ],
                        [
                            'role' => $user->role === 'admin' ? 'admin' : ($user->role === 'lead' ? 'admin' : 'member'),
                            'status' => 'active',
                        ]
                    );

                    // Workspace Membership
                    $wsCreated = WorkspaceMembership::firstOrCreate(
                        [
                            'workspace_id' => $workspace->id,
                            'user_id' => $user->id,
                        ],
                        [
                            'role' => $user->role === 'admin' ? 'admin' : ($user->role === 'lead' ? 'lead' : 'agent'),
                            'status' => 'active',
                        ]
                    );

                    if ($wsCreated->wasRecentlyCreated) {
                        $usersEnrolled++;
                    }
                }

                // 3h. Record immutable audit entry
                $auditService->log(
                    subject: $workspace,
                    event: 'legacy_saas_migration',
                    newValues: [
                        'target_organization_id' => $organization->id,
                        'target_workspace_id' => $workspace->id,
                        'activities_migrated' => $actCount,
                        'activity_logs_migrated' => $logCount,
                        'shift_handovers_migrated' => $handoverCount,
                        'conversations_migrated' => $convCount,
                        'notifications_migrated' => $notifCount,
                        'audit_logs_migrated' => $auditCount,
                        'users_enrolled' => $usersEnrolled,
                        'migrated_at' => now()->toIso8601String(),
                    ]
                );

                return [
                    'activities' => $actCount,
                    'activity_logs' => $logCount,
                    'shift_handovers' => $handoverCount,
                    'conversations' => $convCount,
                    'operational_notifications' => $notifCount,
                    'audit_logs' => $auditCount,
                    'users_enrolled' => $usersEnrolled,
                ];
            });
        });

        // 4. Print Reconciliation Summary
        $this->line('');
        $this->info('Migration completed successfully. Reconciliation Summary:');
        $this->table(
            ['Domain', 'Migrated Count', 'Status'],
            [
                ['Activities', $migratedCounts['activities'], 'MIGRATED'],
                ['Activity Logs', $migratedCounts['activity_logs'], 'MIGRATED'],
                ['Shift Handovers', $migratedCounts['shift_handovers'], 'MIGRATED'],
                ['Conversations', $migratedCounts['conversations'], 'MIGRATED'],
                ['Notifications', $migratedCounts['operational_notifications'], 'MIGRATED'],
                ['Audit Logs', $migratedCounts['audit_logs'], 'MIGRATED'],
                ['Users Enrolled in Workspace', $migratedCounts['users_enrolled'], 'ENROLLED'],
            ]
        );

        $this->info("Workspace #{$workspace->id} ({$workspace->name}) is now fully populated with all historical operational records.");

        return Command::SUCCESS;
    }

    /**
     * Inspect and return unmapped record counts.
     *
     * @return array<string, int>
     */
    private function inspectUnmappedRecords(): array
    {
        return TenantContext::withoutTenancy(function () {
            $actCount = Activity::withoutGlobalScopes()->whereNull('workspace_id')->count();
            $logCount = ActivityLog::withoutGlobalScopes()->whereNull('workspace_id')->count();
            $handoverCount = ShiftHandover::withoutGlobalScopes()->whereNull('workspace_id')->count();
            $convCount = Conversation::withoutGlobalScopes()->whereNull('workspace_id')->count();
            $notifCount = OperationalNotification::withoutGlobalScopes()->whereNull('workspace_id')->count();
            $auditCount = AuditLog::withoutGlobalScopes()->whereNull('workspace_id')->count();

            $usersWithoutMembership = User::whereDoesntHave('workspaceMemberships')->count();

            return [
                'activities' => $actCount,
                'activity_logs' => $logCount,
                'shift_handovers' => $handoverCount,
                'conversations' => $convCount,
                'operational_notifications' => $notifCount,
                'audit_logs' => $auditCount,
                'users_without_membership' => $usersWithoutMembership,
            ];
        });
    }

    /**
     * Roll back workspace association for target workspace.
     */
    private function executeRollback(Workspace $workspace): int
    {
        $this->warn("Executing rollback: Unlinking records currently associated with Workspace #{$workspace->id}...");

        TenantContext::withoutTenancy(function () use ($workspace) {
            Activity::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
            ActivityLog::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
            ShiftHandover::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
            Conversation::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
            OperationalNotification::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
            AuditLog::withoutGlobalScopes()->where('workspace_id', $workspace->id)->update(['workspace_id' => null]);
        });

        $this->info("Rollback complete for Workspace #{$workspace->id}. Records are unlinked.");

        return Command::SUCCESS;
    }

    private function resolveOrganization(): ?Organization
    {
        $target = $this->option('target-org');
        if ($target) {
            return is_numeric($target)
                ? Organization::find((int) $target)
                : Organization::where('slug', $target)->first();
        }

        return Organization::first();
    }

    private function resolveWorkspace(Organization $organization): ?Workspace
    {
        $target = $this->option('target-workspace');
        if ($target) {
            return is_numeric($target)
                ? Workspace::find((int) $target)
                : Workspace::where('slug', $target)->first();
        }

        return $organization->workspaces()->first() ?? Workspace::first();
    }
}
