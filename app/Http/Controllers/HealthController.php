<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SystemHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HealthController — SRE System Health & Software Status Dashboard
 *
 * Serves dual modes:
 *   1. Automated Health Probe (Render, Pingdom, Docker, Uptime monitors):
 *      Returns JSON payload when requested via API headers or ?format=json.
 *   2. Interactive SRE System Health Dashboard:
 *      Renders real-time telemetry metrics, subsystem matrix, and availability timeline plots.
 */
class HealthController extends Controller
{
    public function __construct(
        protected SystemHealthService $healthService
    ) {}

    /**
     * Display the System Health Dashboard or return JSON health payload.
     *
     * @param  Request  $request  Incoming HTTP request
     * @return JsonResponse|View JSON status for probes, or HTML dashboard for browsers
     */
    public function index(Request $request): JsonResponse|View
    {
        // Automated health probe check (Pingdom, Render, Docker, curl)
        if ($request->wantsJson() || $request->query('format') === 'json' || $request->is('api/*')) {
            $payload = $this->healthService->getJsonHealthPayload();
            $status = $payload['status'] === 'ok' ? 200 : 503;

            return response()->json($payload, $status);
        }

        // Full Interactive Dashboard for web users
        $metrics = $this->healthService->getFullHealthMetrics();

        return view('health.index', $metrics);
    }

    /**
     * Stream real-time performance telemetry JSON for live dashboard charts.
     *
     * @param  Request  $request  Incoming HTTP request
     * @return JsonResponse Real-time performance metrics
     */
    public function telemetry(Request $request): JsonResponse
    {
        return response()->json($this->healthService->getRealtimeTelemetry());
    }
}
