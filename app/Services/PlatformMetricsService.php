<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\OrganizationApplication;
use App\Models\Plan;
use App\Models\SecurityEvent;
use App\Models\ShiftHandover;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PlatformMetricsService
{
    /**
     * Gather comprehensive, live platform metrics across the entire SaaS infrastructure.
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        return TenantContext::withoutTenancy(function (): array {
            // 1. User Population
            $totalUsers = User::count();
            $suspendedUsers = User::whereNotNull('suspended_at')->count();
            $activeUsers30d = User::whereNull('suspended_at')
                ->where('updated_at', '>=', now()->subDays(30))
                ->count();
            $platformAdminsCount = User::whereNotNull('platform_role')->count();

            // 2. Organizations & Tenants
            $totalOrgs = Organization::count();
            $activeOrgs = Organization::where('status', 'active')->count();
            $suspendedOrgs = Organization::where('status', 'suspended')->count();
            $pendingApplications = OrganizationApplication::where('status', 'pending')->count();
            $orgsByTier = Organization::select('tier', DB::raw('count(*) as count'))
                ->groupBy('tier')
                ->pluck('count', 'tier')
                ->toArray();

            // 3. Workspaces
            $totalWorkspaces = Workspace::count();
            $personalWorkspaces = Workspace::where('is_personal', true)->count();
            $orgWorkspaces = Workspace::where('is_personal', false)->count();

            // 4. Commercial Subscriptions & ARR / MRR
            $activeSubs = Subscription::whereIn('status', ['active', 'trialing'])->count();
            $trialingSubs = Subscription::where('status', 'trialing')
                ->where('trial_ends_at', '>', now())
                ->count();

            // Compute MRR from active subscriptions joined with plan prices
            $mrrCents = (int) DB::table('saas_subscriptions')
                ->join('saas_plans', 'saas_subscriptions.plan_id', '=', 'saas_plans.id')
                ->whereIn('saas_subscriptions.status', ['active', 'trialing'])
                ->sum(DB::raw("CASE WHEN saas_plans.billing_interval = 'annual' THEN saas_plans.price_cents / 12 ELSE saas_plans.price_cents END"));

            $arrCents = $mrrCents * 12;

            // 5. SRE Operations & Incidents
            $activeIncidents = Activity::where('is_incident', true)
                ->whereNull('resolved_at')
                ->count();

            $totalActivities = Activity::count();
            $activitiesToday = Activity::whereDate('activity_date', today())->count();
            $totalHandovers = ShiftHandover::count();

            // 6. Platform Jobs & Queues
            $failedJobsCount = 0;
            try {
                if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                    $failedJobsCount = DB::table('failed_jobs')->count();
                }
            } catch (\Throwable) {
                $failedJobsCount = 0;
            }

            // 7. Security & Audit Telemetry (Past 24 hours)
            $securityEvents24h = SecurityEvent::where('created_at', '>=', now()->subDay())->count();
            $criticalSecurity24h = SecurityEvent::where('created_at', '>=', now()->subDay())
                ->where('severity', 'critical')
                ->count();
            $auditLogs24h = AuditLog::where('created_at', '>=', now()->subDay())->count();

            // 8. Recent Feeds
            $recentAuditLogs = AuditLog::latest('created_at')->limit(8)->get();
            $recentSecurityEvents = SecurityEvent::latest('created_at')->limit(8)->get();
            $recentApplications = OrganizationApplication::where('status', 'pending')
                ->latest('created_at')
                ->limit(5)
                ->get();

            return [
                'users' => [
                    'total' => $totalUsers,
                    'active_30d' => $activeUsers30d,
                    'suspended' => $suspendedUsers,
                    'platform_admins' => $platformAdminsCount,
                ],
                'organizations' => [
                    'total' => $totalOrgs,
                    'active' => $activeOrgs,
                    'suspended' => $suspendedOrgs,
                    'pending_applications' => $pendingApplications,
                    'by_tier' => $orgsByTier,
                ],
                'workspaces' => [
                    'total' => $totalWorkspaces,
                    'personal' => $personalWorkspaces,
                    'organization' => $orgWorkspaces,
                ],
                'subscriptions' => [
                    'active' => $activeSubs,
                    'trialing' => $trialingSubs,
                    'mrr_cents' => $mrrCents,
                    'mrr_formatted' => '$'.number_format($mrrCents / 100, 2),
                    'arr_cents' => $arrCents,
                    'arr_formatted' => '$'.number_format($arrCents / 100, 2),
                ],
                'operations' => [
                    'active_incidents' => $activeIncidents,
                    'total_activities' => $totalActivities,
                    'activities_today' => $activitiesToday,
                    'total_handovers' => $totalHandovers,
                    'failed_jobs' => $failedJobsCount,
                ],
                'security' => [
                    'events_24h' => $securityEvents24h,
                    'critical_24h' => $criticalSecurity24h,
                    'audit_logs_24h' => $auditLogs24h,
                ],
                'recent' => [
                    'audit_logs' => $recentAuditLogs,
                    'security_events' => $recentSecurityEvents,
                    'applications' => $recentApplications,
                ],
            ];
        });
    }

    /**
     * Live infrastructure health probe check.
     *
     * @return array<string, array{status: string, message: string, latency_ms?: float}>
     */
    public function getSystemHealth(): array
    {
        $health = [];

        // Database probe
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $dbStart) * 1000, 2);
            $health['database'] = [
                'status' => 'healthy',
                'message' => 'Connected to MySQL database engine.',
                'latency_ms' => $dbLatency,
            ];
        } catch (\Throwable $e) {
            $health['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
        }

        // Cache probe
        $cacheStart = microtime(true);
        try {
            $testKey = 'opsora_health_check_'.time();
            Cache::put($testKey, 'ok', 5);
            $retrieved = Cache::get($testKey);
            $cacheLatency = round((microtime(true) - $cacheStart) * 1000, 2);
            Cache::forget($testKey);

            $health['cache'] = [
                'status' => $retrieved === 'ok' ? 'healthy' : 'degraded',
                'message' => 'Cache driver functional.',
                'latency_ms' => $cacheLatency,
            ];
        } catch (\Throwable $e) {
            $health['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache failure: '.$e->getMessage(),
            ];
        }

        // Storage probe
        try {
            $storageOk = is_writable(storage_path('framework/views'));
            $health['storage'] = [
                'status' => $storageOk ? 'healthy' : 'degraded',
                'message' => $storageOk ? 'Storage directories writable.' : 'Storage paths read-only.',
            ];
        } catch (\Throwable $e) {
            $health['storage'] = [
                'status' => 'unhealthy',
                'message' => 'Storage check failed: '.$e->getMessage(),
            ];
        }

        // Queue probe
        try {
            $failedCount = 0;
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedCount = DB::table('failed_jobs')->count();
            }
            $health['queue'] = [
                'status' => $failedCount === 0 ? 'healthy' : 'warning',
                'message' => $failedCount === 0 ? 'Queue workers normal, 0 failed jobs.' : "Queue has {$failedCount} failed job(s).",
            ];
        } catch (\Throwable $e) {
            $health['queue'] = [
                'status' => 'degraded',
                'message' => 'Could not inspect queue tables.',
            ];
        }

        return $health;
    }
}
