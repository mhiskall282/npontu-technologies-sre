<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Organization;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Services\PlatformMetricsService;
use App\Services\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class PlatformReportController extends Controller
{
    /**
     * Display SaaS Platform Commercial & Operational Analytics.
     */
    public function index(PlatformMetricsService $metricsService): View
    {
        return TenantContext::withoutTenancy(function () use ($metricsService): View {
            $metrics = $metricsService->getDashboardMetrics();

            $driver = DB::getDriverName();
            $monthExpr = match ($driver) {
                'sqlite' => "strftime('%Y-%m', created_at)",
                'pgsql' => "to_char(created_at, 'YYYY-MM')",
                default => "DATE_FORMAT(created_at, '%Y-%m')",
            };

            // 1. Organization growth by month over past 6 months
            $monthlyOrgs = Organization::select(
                DB::raw("{$monthExpr} as month"),
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            // 2. User growth by month over past 6 months
            $monthlyUsers = User::select(
                DB::raw("{$monthExpr} as month"),
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            // 3. Shift Handover Dual-Acceptance Compliance
            $totalHandovers = ShiftHandover::count();
            $acceptedHandovers = ShiftHandover::where('is_accepted', true)->count();
            $complianceRate = $totalHandovers > 0
                ? round(($acceptedHandovers / $totalHandovers) * 100, 1)
                : 100.0;

            // 4. SRE Incident MTTR / Resolution Rate
            $totalIncidents = Activity::where('is_incident', true)->count();
            $resolvedIncidents = Activity::where('is_incident', true)->whereNotNull('resolved_at')->count();
            $incidentResolutionRate = $totalIncidents > 0
                ? round(($resolvedIncidents / $totalIncidents) * 100, 1)
                : 100.0;

            $metrics['total_organizations'] = $metrics['organizations']['total'] ?? 0;
            $metrics['total_users'] = $metrics['users']['total'] ?? 0;
            $metrics['total_workspaces'] = $metrics['workspaces']['total'] ?? 0;
            $metrics['mrr'] = ($metrics['subscriptions']['mrr_cents'] ?? 0) / 100;
            $metrics['arr'] = ($metrics['subscriptions']['arr_cents'] ?? 0) / 100;
            $metrics['active_subscriptions'] = $metrics['subscriptions']['active'] ?? 0;
            $metrics['paid_subscriptions'] = $metrics['subscriptions']['active'] ?? 0;

            return view('admin.platform.reports.index', [
                'metrics' => $metrics,
                'monthlyOrgs' => $monthlyOrgs,
                'monthlyUsers' => $monthlyUsers,
                'complianceRate' => $complianceRate,
                'incidentResolutionRate' => $incidentResolutionRate,
                'totalHandovers' => $totalHandovers,
                'totalIncidents' => $totalIncidents,
            ]);
        });
    }
}
