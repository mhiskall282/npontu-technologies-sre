<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Activities\CreateActivityAction;
use App\Actions\Activities\DeleteActivityAction;
use App\Actions\Activities\UpdateActivityAction;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Activity::class);

        $activities = Activity::with('creator')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('activities.index', compact('activities'));
    }

    public function create(): View
    {
        $this->authorize('create', Activity::class);

        return view('activities.create');
    }

    public function store(StoreActivityRequest $request, CreateActivityAction $action): RedirectResponse
    {
        // Authorization handled in StoreActivityRequest::authorize()
        $activity = $action->execute($request->validated());

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity): View
    {
        $this->authorize('view', $activity);

        $activity->load(['creator', 'logs' => fn ($q) => $q->orderByDesc('id')->limit(20)]);

        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity): View
    {
        $this->authorize('update', $activity);

        return view('activities.edit', compact('activity'));
    }

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

    public function destroy(Activity $activity, DeleteActivityAction $action): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $action->execute($activity);

        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity deleted.');
    }
}
