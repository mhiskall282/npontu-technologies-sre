<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ReportingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * DailyActivityBoard — Real-Time SRE Shift Handover Board
 *
 * Implements FR-4 (Daily View & Shift Handover) and FR-1.1 (Team Task Assignment):
 *   - Serves as the primary operational console for incoming/outgoing SRE support shifts.
 *   - Displays checks categorized into "Needs Attention (Pending)" and "Completed (Done)".
 *   - Supports team member task delegation:
 *     * Filter by "Assigned to Me" for quick operator task discovery.
 *     * Filter by specific engineer or unassigned shift pool tasks.
 *     * Inline re-assignment directly from the board for Admins and Team Leads.
 *   - Provides real-time event-driven reactivity via Livewire 3 listeners:
 *     #[On('status-updated')] intercepts updates dispatched by child ActivityStatusUpdater components
 *     and triggers an instantaneous server re-render (~100ms) without full page reload.
 *   - Features wire:poll.30000ms as a background multi-operator sync mechanism.
 *   - Includes instant search, category, and assignee filtering for managing large catalogs (100+ checks).
 */
#[Layout('layouts.app')]
#[Title('Daily Activity Board — Support Tracker')]
class DailyActivityBoard extends Component
{
    /**
     * Active calendar date for the shift board (Y-m-d format).
     * Defaults to current calendar date on mount.
     */
    public string $date;

    /**
     * Real-time search query for filtering activities by title, description, or category.
     */
    public string $search = '';

    /**
     * Selected category filter (e.g. 'Infrastructure', 'Database', 'Application', 'Security').
     */
    public string $category = '';

    /**
     * Selected assignee filter:
     *   - ''           : Show all activities
     *   - 'me'         : Only activities assigned to current authenticated user
     *   - 'unassigned' : Only unassigned activities in the shift pool
     *   - '{id}'       : Only activities assigned to a specific user ID
     */
    public string $assigneeFilter = '';

    /**
     * Initialize the component state with today's date.
     */
    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    /**
     * Hook triggered when the date property changes.
     * Livewire re-renders the component automatically with the selected date's snapshot.
     */
    public function updatedDate(): void
    {
        // Reactive hook: changing $date triggers render() with the historical snapshot
    }

    /**
     * Event listener responding to status updates from child components.
     *
     * Handles 'status-updated' and 'statusUpdated' events dispatched by ActivityStatusUpdater.
     * Re-renders the board immediately and flashes a user confirmation toast.
     *
     * @param  string|null  $message  Optional confirmation notification to flash to the operator
     */
    #[On('status-updated')]
    #[On('statusUpdated')]
    public function refreshBoard(?string $message = null): void
    {
        if ($message) {
            session()->flash('success', $message);
        }
    }

    /**
     * Quick setter for assignee filter.
     */
    public function setAssigneeFilter(string $filter): void
    {
        $this->assigneeFilter = $filter;
    }

    /**
     * Reassign an operational check directly from the shift handover board.
     *
     * Permitted only for users with canManageActivities() (Admins and Team Leads).
     *
     * @param  int  $activityId  Activity model ID
     * @param  int|null  $userId  New assignee user ID or null to return to shift pool
     */
    public function assignActivity(int $activityId, ?int $userId = null, ?AuditService $auditService = null): void
    {
        if (! auth()->user()->canManageActivities()) {
            abort(403, 'Unauthorized to assign activities.');
        }

        $activity = Activity::findOrFail($activityId);
        $oldAssigneeId = $activity->assigned_to;

        $activity->update(['assigned_to' => $userId]);

        // Audit the assignment mutation
        $auditService = $auditService ?? app(AuditService::class);
        $auditService->log(
            subject: $activity,
            event: 'updated',
            oldValues: ['assigned_to' => $oldAssigneeId],
            newValues: ['assigned_to' => $userId],
        );

        $newAssigneeName = $userId ? User::find($userId)?->name : 'General Shift Pool';
        session()->flash('success', "Check '{$activity->title}' assigned to {$newAssigneeName}.");
    }

    /**
     * Reset search, category, and assignee filters back to the full activity catalog.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->assigneeFilter = '';
    }

    /**
     * Render the shift handover board view.
     *
     * Orchestration:
     *   1. Queries ReportingService::dailySummary() to eager-load day logs, assignee, and derive current statuses.
     *   2. Extracts available category tokens and active team members for filter controls.
     *   3. Computes day-wide stats (total, pending, done, personal tasks, unassigned pool).
     *   4. Applies search, category, and assignee filters to pending and completed collections.
     *   5. For leads/admins, loads the 5 most recent compliance audit log entries.
     *
     * @param  ReportingService  $service  Reporting service dependency injected by Livewire
     * @return View Rendered Blade view
     */
    public function render(ReportingService $service): View
    {
        $allActivities = $service->dailySummary($this->date);

        // Extract unique category names present in the active checks for this date
        $categories = $allActivities->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Team members for filter dropdown and inline assignment
        $teamMembers = User::orderBy('name')->get();

        // Compute overall shift metrics before applying search/category filters
        $totalActivitiesCount = $allActivities->count();
        $totalPendingCount = $allActivities->filter(fn ($a) => $a->current_status === 'pending')->count();
        $totalDoneCount = $allActivities->filter(fn ($a) => $a->current_status === 'done')->count();
        $myTasksCount = $allActivities->filter(fn ($a) => $a->assigned_to === auth()->id())->count();
        $myPendingTasksCount = $allActivities->filter(fn ($a) => $a->assigned_to === auth()->id() && $a->current_status === 'pending')->count();
        $unassignedCount = $allActivities->filter(fn ($a) => empty($a->assigned_to))->count();

        // Apply interactive client filters
        $filtered = $allActivities;

        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $filtered = $filtered->filter(function ($activity) use ($term) {
                return str_contains(mb_strtolower((string) $activity->title), $term)
                    || str_contains(mb_strtolower((string) $activity->description), $term)
                    || str_contains(mb_strtolower((string) $activity->category), $term)
                    || ($activity->assignee && str_contains(mb_strtolower((string) $activity->assignee->name), $term));
            });
        }

        if ($this->category !== '') {
            $filtered = $filtered->filter(fn ($activity) => $activity->category === $this->category);
        }

        // Apply assignee filter
        if ($this->assigneeFilter === 'me') {
            $filtered = $filtered->filter(fn ($a) => $a->assigned_to === auth()->id());
        } elseif ($this->assigneeFilter === 'unassigned') {
            $filtered = $filtered->filter(fn ($a) => empty($a->assigned_to));
        } elseif ($this->assigneeFilter !== '') {
            $userId = (int) $this->assigneeFilter;
            $filtered = $filtered->filter(fn ($a) => $a->assigned_to === $userId);
        }

        // Separate into priority groups (Pending items visually prominent above Completed items)
        $pending = $filtered->filter(fn ($a) => $a->current_status === 'pending');
        $done = $filtered->filter(fn ($a) => $a->current_status === 'done');

        // Supervisor Console: Recent security audit trail for leads and admins
        $recentAudits = null;
        if (auth()->user()->canManageActivities()) {
            $recentAudits = AuditLog::latest()->limit(5)->get();
        }

        return view('livewire.daily-activity-board', compact(
            'pending',
            'done',
            'recentAudits',
            'categories',
            'teamMembers',
            'totalActivitiesCount',
            'totalPendingCount',
            'totalDoneCount',
            'myTasksCount',
            'myPendingTasksCount',
            'unassignedCount'
        ));
    }
}
