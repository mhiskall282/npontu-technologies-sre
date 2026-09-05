<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DocsController extends Controller
{
    /**
     * Render the comprehensive high-level SRE documentation and platform guide.
     */
    public function index(): View
    {
        $stats = [
            'total_activities' => Activity::count(),
            'total_users' => User::count(),
            'subsystems_monitored' => 8,
            'sla_uptime' => '99.98%',
            'test_assertions' => 409,
            'feature_tests' => 79,
        ];

        return view('docs.index', compact('stats'));
    }
}
