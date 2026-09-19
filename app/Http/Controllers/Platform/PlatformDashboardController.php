<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\PlatformMetricsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformDashboardController extends Controller
{
    /**
     * Display the Platform Control Plane Master Dashboard.
     */
    public function index(Request $request, PlatformMetricsService $metricsService): View
    {
        $metrics = $metricsService->getDashboardMetrics();
        $health = $metricsService->getSystemHealth();

        return view('admin.platform.dashboard', [
            'metrics' => $metrics,
            'health' => $health,
        ]);
    }
}
