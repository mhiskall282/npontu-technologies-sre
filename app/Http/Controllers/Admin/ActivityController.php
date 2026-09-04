<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Activities\CreateActivityAction;
use App\Actions\Activities\DeleteActivityAction;
use App\Actions\Activities\UpdateActivityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin\ActivityController — Supervisor Console for Activity Administration
 *
 * Provides dedicated administrative routes for Leads and Admins to manage operational checks:
 *   - index(): Catalog including soft-deleted checks with creator and assignee relationships pre-loaded
 *   - create() / store(): Provision recurring operational checks with optional team assignment
 *   - edit() / update(): Modify titles, categories, recurrence intervals, assignee, and active status
 *   - destroy(): Soft-delete checks and record security audit log
 *
 * ARCHITECTURAL DESIGN:
 * - Thin Controller: Delegates validation to Form Requests and logic to Actions.
 * - Authorization: Enforced via ActivityPolicy.
 */
class ActivityController extends Controller
{
    /**
     * Display a paginated list of all operational checks, including archived/soft-deleted checks.
     *
     * @return View Administrative activities index view
     */
    public function index(): View
    {
        // Enforce policy: verify supervisor access
        $this->authorize('create', Activity::class);

        $activities = Activity::withTrashed()
            ->with(['creator', 'assignee'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }

    /**
     * Show form to provision a new operational check.
     *
     * @return View Activity creation view
     */
    public function create(): View
    {
        $this->authorize('create', Activity::class);

        $users = User::orderBy('name')->get();

        return view('admin.activities.create', compact('users'));
    }

    /**
     * Store a newly created operational check and log compliance audit trail.
     *
     * @param  StoreActivityRequest  $request  Validated form inputs
     * @param  CreateActivityAction  $action  Domain action executing persistence and audit logging
     * @return RedirectResponse Redirect to admin activity list
     */
    public function store(StoreActivityRequest $request, CreateActivityAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activity created.');
    }

    /**
     * Show form to edit check parameters.
     *
     * @param  Activity  $activity  Activity model instance
     * @return View Edit activity view
     */
    public function edit(Activity $activity): View
    {
        $this->authorize('update', $activity);

        $users = User::orderBy('name')->get();

        return view('admin.activities.edit', compact('activity', 'users'));
    }

    /**
     * Update an operational check definition.
     *
     * @param  UpdateActivityRequest  $request  Validated update inputs
     * @param  Activity  $activity  Activity instance to update
     * @param  UpdateActivityAction  $action  Domain action recording before/after diffs in audit log
     * @return RedirectResponse Redirect to admin activity list
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity,
        UpdateActivityAction $action,
    ): RedirectResponse {
        $action->execute($activity, $request->validated());

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activity updated.');
    }

    /**
     * Soft-delete an operational check and write compliance audit record.
     *
     * @param  Activity  $activity  Activity instance to soft-delete
     * @param  DeleteActivityAction  $action  Domain action soft-deleting the record
     * @return RedirectResponse Redirect to admin activity list
     */
    public function destroy(Activity $activity, DeleteActivityAction $action): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $action->execute($activity);

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activity deleted.');
    }
}
