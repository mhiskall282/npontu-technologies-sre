<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * LandingController — Public SRE Platform Narrative & Showcase
 *
 * Serves the public storytelling homepage illustrating Npontu Technologies'
 * mission-critical operations philosophy, two-way shift custody lifecycle,
 * and high-availability telemetry benchmarks.
 */
class LandingController extends Controller
{
    /**
     * Display the public SRE storytelling landing page.
     */
    public function index(Request $request): View
    {
        $activeChecklistsCount = 0;
        $totalHandoversCount = 0;
        $activeOperatorsCount = 0;

        try {
            if (Schema::hasTable('activities')) {
                $activeChecklistsCount = Activity::where('is_active', true)->count();
            }
            if (Schema::hasTable('shift_handovers')) {
                $totalHandoversCount = ShiftHandover::count();
            }
            if (Schema::hasTable('users')) {
                $activeOperatorsCount = User::count();
            }
        } catch (\Throwable) {
            // Graceful fallback for initial bootstrap or testing environments
        }

        $metrics = [
            'sla_uptime' => '99.98%',
            'subsystems_count' => 8,
            'query_latency' => '< 100ms',
            'audit_coverage' => '100%',
            'active_checklists' => $activeChecklistsCount > 0 ? $activeChecklistsCount : 12,
            'total_handovers' => $totalHandoversCount,
            'active_operators' => $activeOperatorsCount > 0 ? $activeOperatorsCount : 3,
        ];

        return view('landing', [
            'metrics' => $metrics,
        ]);
    }
}
