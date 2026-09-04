<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SRE Monitoring Dashboard Controller
 *
 * Provides a real-time operational overview for Admin and Team Lead roles:
 *   - Operational snapshot (total users, active checks, today's completion)
 *   - Security audit log stream (paginated mutation trail with actor bio and diffs)
 *   - 7-day activity completion trends (aggregated via composite index)
 *   - Category breakdown (volume and completion rate per check category)
 *   - Shift operator leaderboard (top active operators by log volume)
 *   - Stale activity alert surface (identifies unfinished checks from previous shifts)
 *   - Audit event type distribution (breakdown of created, updated, status_changed events)
 *
 * PERFORMANCE DESIGN:
 * All queries explicitly leverage composite indexes (date, status) and (date, activity_id).
 * Aggregate queries are used instead of per-day foreach loops to minimize round-trips.
 */
class MonitoringController extends Controller
{
    /**
     * Render the unified SRE operational and compliance dashboard.
     *
     * @param  Request  $request  Incoming HTTP request
     * @return View Rendered monitoring dashboard view
     */
    public function index(Request $request): View
    {
        // Enforce authorization: only users with viewAny permission on Activity can access monitoring
        $this->authorize('viewAny', Activity::class);

        $todayStr = today()->toDateString();
        $thirtyDaysAgo = now()->subDays(30)->toDateString();
        $sevenDaysAgo = now()->subDays(6)->toDateString();

        // ── 1. System Snapshot ───────────────────────────────────────────
        // Fast scalar counts summarizing current system state
        $totalUsers = User::count();
        $totalActivities = Activity::count();

        // Single indexed count query for today's overall and status-filtered logs
        $todayLogs = ActivityLog::where('date', $todayStr)->count();
        $pendingToday = ActivityLog::where('date', $todayStr)->where('status', 'pending')->count();
        $doneToday = ActivityLog::where('date', $todayStr)->where('status', 'done')->count();

        // ── 2. Security Audit Log Stream ─────────────────────────────────
        // Paginated stream of security audit mutations (most recent first)
        $auditLogs = AuditLog::latest('created_at')->paginate(20);

        // ── 3. 7-Day Activity Completion Trend ───────────────────────────
        // Optimized: Single aggregate query grouped by (date, status) replacing 14 separate round-trips
        $trendDays = collect(range(6, 0))->map(fn ($d) => now()->subDays($d)->toDateString());

        $trendLogs = ActivityLog::whereBetween('date', [$sevenDaysAgo, $todayStr])
            ->selectRaw('date, status, COUNT(*) as aggregate_count')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy('date');

        $doneTrend = [];
        $pendTrend = [];

        foreach ($trendDays as $day) {
            $dayGroup = $trendLogs->get($day, collect());
            $doneTrend[] = (int) ($dayGroup->firstWhere('status', 'done')?->aggregate_count ?? 0);
            $pendTrend[] = (int) ($dayGroup->firstWhere('status', 'pending')?->aggregate_count ?? 0);
        }

        $trendChart = [
            'labels' => $trendDays->map(fn ($d) => date('D d/m', strtotime($d)))->values()->toArray(),
            'done' => $doneTrend,
            'pending' => $pendTrend,
        ];

        // ── 4. Category Breakdown (Last 30 Days) ─────────────────────────
        // Evaluates check volume and completion rates across operational domains (e.g. Infrastructure, Database, Application)
        $categoryBreakdown = Activity::withCount([
            'logs as total_logs' => fn ($q) => $q->where('date', '>=', $thirtyDaysAgo),
            'logs as done_count' => fn ($q) => $q->where('status', 'done')->where('date', '>=', $thirtyDaysAgo),
        ])->get()->groupBy('category')->map(function ($group) {
            return [
                'total' => $group->sum('total_logs'),
                'done' => $group->sum('done_count'),
            ];
        });

        // ── 5. Top Shift Contributors (Last 7 Days) ─────────────────────
        // Ranks operators by volume of recorded status checks for team performance reviews
        $topContributors = ActivityLog::selectRaw('actor_name, actor_role, COUNT(*) as log_count')
            ->where('date', '>=', $sevenDaysAgo)
            ->groupBy('actor_name', 'actor_role')
            ->orderByDesc('log_count')
            ->limit(5)
            ->get();

        // ── 6. Stale Pending Alerts ──────────────────────────────────────
        // Identifies activities left incomplete from earlier calendar days to prevent handover blindspots
        $staleAlerts = ActivityLog::with('activity')
            ->where('date', '<', $todayStr)
            ->where('status', 'pending')
            ->whereIn('id', function ($q) use ($todayStr) {
                $q->selectRaw('MAX(id)')
                    ->from('activity_logs')
                    ->where('date', '<', $todayStr)
                    ->groupBy('activity_id', 'date');
            })
            ->latest()
            ->limit(10)
            ->get();

        // ── 7. Audit Event Type Distribution ─────────────────────────────
        // Breakdown of operational mutations (create, update, status_changed, delete) for compliance reporting
        $eventBreakdown = AuditLog::selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->orderByDesc('count')
            ->get();

        return view('monitoring.index', compact(
            'totalUsers',
            'totalActivities',
            'todayLogs',
            'pendingToday',
            'doneToday',
            'auditLogs',
            'trendChart',
            'categoryBreakdown',
            'topContributors',
            'staleAlerts',
            'eventBreakdown'
        ));
    }
}
