<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Activities\CreateActivityAction;
use App\Actions\Activities\DeleteActivityAction;
use App\Actions\Activities\UpdateActivityAction;
use App\Actions\Activities\UpdateActivityStatusAction;
use App\Http\Requests\Api\V1\BulkAssignActivitiesApiRequest;
use App\Http\Requests\Api\V1\StoreActivityApiRequest;
use App\Http\Requests\Api\V1\UpdateActivityApiRequest;
use App\Http\Requests\Api\V1\UpdateActivityStatusApiRequest;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Http\Resources\Api\V1\ActivityResource;
use App\Models\Activity;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityController extends ApiController
{
    public function __construct(
        private readonly ReportingService $reportingService,
        private readonly AuditService $auditService
    ) {}

    /**
     * List operational checks with shift filtering, priority sorting, and personal queue scoping.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $date = $request->query('date', today()->toDateString());
        $statusFilter = $request->query('status');
        $priorityFilter = $request->query('priority');
        $assignedFilter = $request->query('assigned'); // 'me' | 'pool' | int
        $searchQuery = $request->query('search');

        // Fetch all active activities for shift date with derived status and latest log
        $activities = $this->reportingService->dailySummary($date);

        // Apply filters in-memory on the daily summary collection
        if ($statusFilter !== null && in_array($statusFilter, ['pending', 'done'], true)) {
            $activities = $activities->filter(fn ($a) => $a->current_status === $statusFilter);
        }

        if ($priorityFilter !== null && $priorityFilter !== '') {
            $activities = $activities->filter(fn ($a) => $a->priority === $priorityFilter);
        }

        if ($assignedFilter === 'me') {
            $activities = $activities->filter(fn ($a) => $a->assigned_to === $user->id);
        } elseif ($assignedFilter === 'pool') {
            $activities = $activities->filter(fn ($a) => $a->assigned_to === null);
        } elseif ($assignedFilter !== null && is_numeric($assignedFilter)) {
            $activities = $activities->filter(fn ($a) => $a->assigned_to === (int) $assignedFilter);
        }

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $term = strtolower(trim($searchQuery));
            $activities = $activities->filter(function ($a) use ($term) {
                return str_contains(strtolower($a->title), $term)
                    || str_contains(strtolower($a->category ?? ''), $term)
                    || str_contains(strtolower($a->description ?? ''), $term);
            });
        }

        $activities = $activities->values();

        $meta = [
            'date' => $date,
            'total' => $activities->count(),
            'pending_count' => $activities->filter(fn ($a) => $a->current_status === 'pending')->count(),
            'done_count' => $activities->filter(fn ($a) => $a->current_status === 'done')->count(),
        ];

        return $this->respondWithSuccess(
            ActivityResource::collection($activities),
            'Activities retrieved successfully.',
            200,
            $meta
        );
    }

    /**
     * Provision a new operational check definition.
     */
    public function store(StoreActivityApiRequest $request, CreateActivityAction $action): JsonResponse
    {
        $activity = $action->execute($request->validated());

        return $this->respondCreated(
            new ActivityResource($activity->load(['creator', 'assignee'])),
            'Operational check created successfully.'
        );
    }

    /**
     * Get activity definition and complete historical update timeline.
     */
    public function show(Activity $activity): JsonResponse
    {
        $activity->load([
            'creator',
            'assignee',
            'logs.updater',
        ]);

        return $this->respondWithSuccess(
            new ActivityResource($activity),
            'Activity details retrieved.'
        );
    }

    /**
     * Update operational check definition.
     */
    public function update(
        UpdateActivityApiRequest $request,
        Activity $activity,
        UpdateActivityAction $action
    ): JsonResponse {
        $activity = $action->execute($activity, $request->validated());

        return $this->respondWithSuccess(
            new ActivityResource($activity->load(['creator', 'assignee'])),
            'Operational check updated successfully.'
        );
    }

    /**
     * Soft-delete operational check definition.
     */
    public function destroy(Activity $activity, DeleteActivityAction $action): JsonResponse
    {
        $this->authorize('delete', $activity);

        $action->execute($activity);

        return $this->respondWithSuccess(
            null,
            'Operational check archived successfully.'
        );
    }

    /**
     * Transition activity checkoff status (append-only event store).
     */
    public function updateStatus(
        UpdateActivityStatusApiRequest $request,
        Activity $activity,
        UpdateActivityStatusAction $action
    ): JsonResponse {
        $validated = $request->validated();
        $date = $validated['date'] ?? today()->toDateString();
        $status = $validated['status'];
        $remark = $validated['remark'] ?? null;
        $incidentTicket = $validated['incident_ticket'] ?? null;
        $isEscalated = (bool) ($validated['is_escalated'] ?? false);

        $log = $action->execute(
            activity: $activity,
            status: $status,
            remark: $remark,
            date: $date,
            incidentTicket: $incidentTicket,
            isEscalated: $isEscalated
        );

        return $this->respondWithSuccess([
            'log' => new ActivityLogResource($log->load('updater')),
            'activity' => new ActivityResource($activity->fresh(['assignee'])),
        ], 'Status transition recorded successfully.');
    }

    /**
     * Bulk delegate operational checks to a designated engineer or back to the pool.
     */
    public function bulkAssign(BulkAssignActivitiesApiRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $activityIds = $validated['activity_ids'];
        $assignedTo = $validated['assigned_to'] ?? null;

        $targetUser = $assignedTo ? User::find($assignedTo) : null;
        $targetName = $targetUser ? $targetUser->name : 'Unassigned Shift Pool';

        $activities = Activity::whereIn('id', $activityIds)->get();

        foreach ($activities as $activity) {
            $oldAssignee = $activity->assigned_to;
            $activity->update(['assigned_to' => $assignedTo]);

            $this->auditService->log(
                subject: $activity,
                event: 'assignment_updated',
                oldValues: ['assigned_to' => $oldAssignee],
                newValues: ['assigned_to' => $assignedTo, 'assignee_name' => $targetName],
            );
        }

        logger()->channel('state_changes')->info('activity.bulk_assigned', [
            'activity_ids' => $activityIds,
            'assigned_to' => $assignedTo,
            'actor_id' => $request->user()?->id,
        ]);

        return $this->respondWithSuccess([
            'count' => count($activityIds),
            'assigned_to' => $assignedTo,
            'assignee_name' => $targetName,
        ], count($activityIds).' checks successfully delegated to '.$targetName.'.');
    }
}
