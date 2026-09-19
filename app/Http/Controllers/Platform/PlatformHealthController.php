<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\PlatformMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
}
