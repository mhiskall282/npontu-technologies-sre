<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\PlatformMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class PlatformHealthController extends Controller
{
    /**
     * Display live Platform Infrastructure Diagnostics and Health Probes.
     */
    public function index(PlatformMetricsService $metricsService): View
    {
        $health = $metricsService->getSystemHealth();

        return view('admin.platform.health.index', [
            'health' => $health,
        ]);
    }

    /**
     * Administrative trigger to retry all failed queue jobs.
     */
    public function retryFailedJobs(Request $request): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);
        $output = Artisan::output();

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'QueueSystem',
            'subject_id' => 0,
            'event' => 'queue_jobs_retried',
            'new_values' => ['output' => trim($output)],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Dispatched retry command for failed queue jobs: '.trim($output));
    }

    /**
     * Purge application, route, view, and config caches.
     */
    public function clearCache(Request $request): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'SystemCache',
            'subject_id' => 0,
            'event' => 'platform_cache_cleared',
            'new_values' => ['action' => 'purged_application_and_view_caches'],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Application runtime, view compilation, and routing caches purged successfully.');
    }

    /**
     * Prune expired and stale API tokens.
     */
    public function pruneTokens(Request $request): RedirectResponse
    {
        $pruned = 0;
        if (DB::getSchemaBuilder()->hasTable('personal_access_tokens')) {
            $pruned = DB::table('personal_access_tokens')
                ->where('expires_at', '<', now())
                ->delete();
        }

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'SanctumSecurity',
            'subject_id' => 0,
            'event' => 'platform_tokens_pruned',
            'new_values' => ['pruned_count' => $pruned],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', "Sanctum token security hygiene complete: Pruned {$pruned} expired API token(s).");
    }

    /**
     * Administratively trigger dispatch of scheduled SRE reports.
     */
    public function dispatchReports(Request $request): RedirectResponse
    {
        Artisan::call('reports:send-automated');
        $output = Artisan::output();

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'ReportingEngine',
            'subject_id' => 0,
            'event' => 'platform_reports_dispatched',
            'new_values' => ['output' => trim($output)],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Dispatched automated shift & activity compliance reports: '.trim($output));
    }

    /**
     * Run deep connectivity diagnostics on DB, Cache, and Storage disk.
     */
    public function runDiagnostics(Request $request): RedirectResponse
    {
        $startDb = microtime(true);
        DB::select('SELECT 1');
        $dbLatency = round((microtime(true) - $startDb) * 1000, 2);

        $startCache = microtime(true);
        Cache::put('diagnostics_probe', 'ok', 10);
        $cacheRead = Cache::get('diagnostics_probe');
        $cacheLatency = round((microtime(true) - $startCache) * 1000, 2);

        $startDisk = microtime(true);
        Storage::disk('local')->put('probe.tmp', 'probe');
        Storage::disk('local')->delete('probe.tmp');
        $diskLatency = round((microtime(true) - $startDisk) * 1000, 2);

        $diagnostics = [
            'database' => ['status' => 'operational', 'latency_ms' => $dbLatency],
            'cache' => ['status' => $cacheRead === 'ok' ? 'operational' : 'degraded', 'latency_ms' => $cacheLatency],
            'storage' => ['status' => 'operational', 'latency_ms' => $diskLatency],
            'timestamp' => now()->toIso8601String(),
        ];

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'SystemDiagnostics',
            'subject_id' => 0,
            'event' => 'platform_diagnostics_probed',
            'new_values' => $diagnostics,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->back()->with('diagnostics_results', $diagnostics)
            ->with('success', "Deep diagnostics complete: DB ({$dbLatency}ms), Cache ({$cacheLatency}ms), Storage ({$diskLatency}ms).");
    }
}
