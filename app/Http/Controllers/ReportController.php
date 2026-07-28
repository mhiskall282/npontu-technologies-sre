<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Activity;
use App\Services\ReportingService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportingService $reportingService) {}

    public function index(ReportRequest $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $logs = null;
        $activities = Activity::orderBy('title')->get(['id', 'title']);

        $validated = $request->validated();

        if (isset($validated['from']) && isset($validated['to'])) {
            $logs = $this->reportingService->query(
                from: $validated['from'],
                to: $validated['to'],
                status: $validated['status'] ?? null,
                activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
            );
        }

        return view('reports.index', compact('logs', 'activities'));
    }
}
