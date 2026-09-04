<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Activities\CreateActivityAction;
use App\Actions\Activities\DeleteActivityAction;
use App\Actions\Activities\UpdateActivityAction;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ActivityController — Operational Check Definition Management
 *
 * Implements FR-1 (Activity Configuration) and FR-2 (Activity List/Detail):
 *   - index(): Paginated list of operational check definitions with creator eager-loaded
 *   - create() / store(): Provision a new recurring check definition
 *   - show(): Detailed activity inspection, 7-day completion health trend, and audit logs
 *   - edit() / update(): Modify activity title, description, recurrence, or status
 *   - destroy(): Soft-delete activity while preserving historical audit and reporting logs
 *
 * ARCHITECTURAL DESIGN:
 * Follows strict PSR-12 and thin controller principles:
 * - Validation is encapsulated in Form Requests (StoreActivityRequest, UpdateActivityRequest).
 * - Authorization is enforced through ActivityPolicy via $this->authorize().
 * - Business logic and audit logging are delegated to Actions (app/Actions/Activities/).
 */
class ActivityController extends Controller
{
    /**
     * Display a paginated catalog of all defined operational activities.
     *
     * Eager-loads the 'creator' relation to prevent N+1 queries.
     *
     * @return View Activity index view
     */
    public function index(): View
    {
        // Enforce policy: verify authenticated user can view the activities catalog
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('activities.index', compact('activities'));
    }

    /**
     * Show the form for creating a new operational check.
     *
     * @return View Activity creation form
     */
    public function create(): View
    {
        // Enforce policy: verify user has permission to provision new activities (Admin/Lead)
        $this->authorize('create', Activity::class);

        return view('activities.create');
    }

    /**
     * Store a newly created operational check definition.
     *
     * Validation is executed by StoreActivityRequest before this method runs.
     * Business mutation and immutable audit log generation are handled by CreateActivityAction.
     *
     * @param  StoreActivityRequest  $request  Validated form request
     * @param  CreateActivityAction  $action  Domain action creating activity and audit record
     * @return RedirectResponse Redirect to the new activity show page
     */
    public function store(StoreActivityRequest $request, CreateActivityAction $action): RedirectResponse
    {
        $activity = $action->execute($request->validated());

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Activity created successfully.');
    }

    /**
     * Display activity details, recent update timeline, and 7-day health trend chart.
     *
     * Pre-loads the 20 most recent status logs and computes daily completion percentages
     * across the trailing 7 days for visual trend analysis.
     *
     * @param  Activity  $activity  Implicit model bound activity instance
     * @return View Activity show view
     */
    public function show(Activity $activity): View
    {
        // Enforce policy: verify user is authorized to view this specific activity
        $this->authorize('view', $activity);

        // Eager-load creator and recent 20 logs for the activity timeline
        $activity->load(['creator', 'logs' => fn ($q) => $q->orderByDesc('id')->limit(20)]);

        // ── 7-Day Completion Trend Calculation ───────────────────────────
        $startDate = today()->subDays(6)->format('Y-m-d');
        $endDate = today()->format('Y-m-d');

        $last7Days = collect(range(6, 0))->map(fn ($days) => today()->subDays($days)->format('Y-m-d'));

        $recentLogs = $activity->logs()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $chartLabels = [];
        $chartValues = [];

        foreach ($last7Days as $dateStr) {
            $formattedLabel = Carbon::parse($dateStr)->format('D, d M');
            $chartLabels[] = $formattedLabel;

            // Find the most recent status logged for this activity on this calendar date
            $dayLogs = $recentLogs->filter(fn ($l) => $l->date->format('Y-m-d') === $dateStr);
            $latest = $dayLogs->sortByDesc('id')->first();

            // 100% completion value if marked done, 0% if pending or unlogged
            $chartValues[] = $latest?->status === 'done' ? 100 : 0;
        }

        $trendData = [
            'labels' => $chartLabels,
            'values' => $chartValues,
        ];

        return view('activities.show', compact('activity', 'trendData'));
    }

    /**
     * Show the form for editing an existing operational check definition.
     *
     * @param  Activity  $activity  Activity model to edit
     * @return View Edit activity form
     */
    public function edit(Activity $activity): View
    {
        // Enforce policy: verify user is authorized to update activity metadata
        $this->authorize('update', $activity);

        return view('activities.edit', compact('activity'));
    }

    /**
     * Update an operational check definition in storage.
     *
     * Delegates mutation and audit log generation to UpdateActivityAction.
     *
     * @param  UpdateActivityRequest  $request  Validated update inputs
     * @param  Activity  $activity  Activity to modify
     * @param  UpdateActivityAction  $action  Domain action executing update with before/after audit diff
     * @return RedirectResponse Redirect to show page with confirmation
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity,
        UpdateActivityAction $action,
    ): RedirectResponse {
        $action->execute($activity, $request->validated());

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Activity updated.');
    }

    /**
     * Remove an operational check from active circulation.
     *
     * Uses soft-deletes so historical audit records and reporting statistics
     * remain intact and verifiable.
     *
     * @param  Activity  $activity  Activity to delete
     * @param  DeleteActivityAction  $action  Domain action performing soft-delete and logging audit trail
     * @return RedirectResponse Redirect to index with confirmation
     */
    public function destroy(Activity $activity, DeleteActivityAction $action): RedirectResponse
    {
        // Enforce policy: verify user has permission to delete activities
        $this->authorize('delete', $activity);

        $action->execute($activity);

        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity deleted.');
    }
}
