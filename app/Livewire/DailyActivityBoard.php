<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Handovers\CreateShiftHandoverAction;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ReportingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * DailyActivityBoard — Real-Time SRE Shift Handover & Delegation Console
 *
 * Implements FR-4 (Daily View & Shift Handover) and FR-1.1 (Team Task Assignment & SRE Priority):
 *   - Serves as the primary operational console for incoming/outgoing SRE support shifts.
 *   - Displays checks categorized into "Needs Attention (Pending)" and "Completed (Done)".
 *   - Features team member task delegation:
 *     * Filter by "Assigned to Me" for quick operator task discovery.
 *     * Filter by specific engineer or unassigned shift pool tasks.
 *     * Inline re-assignment directly from the board for Admins and Team Leads.
 *     * Multi-task bulk selection and re-assignment for Shift Leads.
 *   - Formal SRE Shift Handover Management:
 *     * Outgoing Shift Leads draft and sign digital handovers across Morning, Afternoon, and Night shifts.
 *     * Automatically captures task statistics snapshot and active incident escalations.
 *   - SRE Priority, SLA, and Pinning:
 *     * Visual distinction for Critical (P1), High (P2), Medium (P3), and Low (P4) checks.
 *     * SLA target time indicators and warning status for approaching deadlines.
 *     * Pinned checks float to the top of the operational checklist.
 *   - Real-time event-driven reactivity via Livewire 3 listeners.
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
     * Bulk selected activity IDs for multi-task assignment.
     *
     * @var array<int, int>
     */
    public array $selectedActivities = [];

    /**
     * Toggle for selecting all currently visible pending activities.
     */
    public bool $selectAll = false;

    /**
     * Cache of visible pending activity IDs for bulk selection.
     *
     * @var array<int, int>
     */
    public array $visiblePendingIds = [];

    // ──────────────────────────────────────────
    // Shift Handover Modal State
    // ──────────────────────────────────────────

    public bool $showHandoverModal = false;

    public string $handoverShift = 'morning';

    public ?int $handoverIncomingLeadId = null;

    public string $handoverSummary = '';

    public string $handoverIncidents = '';

    /**
     * Initialize the component state with today's date.
     */
    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    /**
     * Hook triggered when the date property changes.
     */
    public function updatedDate(): void
    {
        $this->selectedActivities = [];
        $this->selectAll = false;
    }

    /**
     * Event listener responding to status updates from child components.
     *
     * @param  string|null  $message  Optional confirmation notification
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
     * Handle bulk select-all toggle.
     */
    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedActivities = $this->visiblePendingIds;
        } else {
            $this->selectedActivities = [];
        }
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
     * Bulk delegate selected activities to a team member or return to shift pool.
     */
    public function bulkAssign(?int $userId = null, ?AuditService $auditService = null): void
    {
        if (! auth()->user()->canManageActivities()) {
            abort(403, 'Unauthorized to assign activities.');
        }

        if (empty($this->selectedActivities)) {
            return;
        }

        $auditService = $auditService ?? app(AuditService::class);
        $activities = Activity::whereIn('id', $this->selectedActivities)->get();
        $targetUser = $userId ? User::find($userId) : null;
        $targetName = $targetUser ? $targetUser->name : 'General Shift Pool';

        foreach ($activities as $activity) {
            $oldAssigneeId = $activity->assigned_to;
            $activity->update(['assigned_to' => $userId]);

            $auditService->log(
                subject: $activity,
                event: 'updated',
                oldValues: ['assigned_to' => $oldAssigneeId],
                newValues: ['assigned_to' => $userId],
            );
        }

        $count = count($this->selectedActivities);
        $this->selectedActivities = [];
        $this->selectAll = false;

        session()->flash('success', "Bulk delegation complete: {$count} check(s) delegated to {$targetName}.");
    }

    /**
     * Open the SRE Shift Handover sign-off modal.
     */
    public function openHandoverModal(): void
    {
        if (! auth()->user()->canManageActivities()) {
            abort(403, 'Unauthorized to create shift handovers.');
        }

        $hour = (int) now()->format('H');
        if ($hour >= 6 && $hour < 14) {
            $this->handoverShift = 'morning';
        } elseif ($hour >= 14 && $hour < 22) {
            $this->handoverShift = 'afternoon';
        } else {
            $this->handoverShift = 'night';
        }

        $this->showHandoverModal = true;
    }

    /**
     * Close the Shift Handover modal.
     */
    public function closeHandoverModal(): void
    {
        $this->showHandoverModal = false;
    }

    /**
     * Validate and sign off the shift handover briefing.
     */
    public function saveHandover(CreateShiftHandoverAction $action, ReportingService $service): void
    {
        if (! auth()->user()->canManageActivities()) {
            abort(403, 'Unauthorized to create shift handovers.');
        }

        $this->validate([
            'handoverShift' => ['required', 'in:morning,afternoon,night'],
            'handoverIncomingLeadId' => ['nullable', 'integer', 'exists:users,id'],
            'handoverSummary' => ['required', 'string', 'min:5', 'max:5000'],
            'handoverIncidents' => ['nullable', 'string', 'max:5000'],
        ]);

        $summaryData = $service->dailySummary($this->date);
        $pendingCount = $summaryData->filter(fn ($a) => $a->current_status === 'pending')->count();
        $doneCount = $summaryData->filter(fn ($a) => $a->current_status === 'done')->count();

        $action->execute([
            'date' => $this->date,
            'shift' => $this->handoverShift,
            'incoming_lead_id' => $this->handoverIncomingLeadId,
            'summary' => $this->handoverSummary,
            'incidents' => $this->handoverIncidents ?: null,
            'pending_tasks_count' => $pendingCount,
            'completed_tasks_count' => $doneCount,
        ]);

        $this->showHandoverModal = false;
        $this->handoverSummary = '';
        $this->handoverIncidents = '';

        session()->flash('success', 'Shift handover report signed and logged successfully.');
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

        // High-level SRE telemetry counters
        $criticalPendingCount = $allActivities->filter(fn ($a) => $a->current_status === 'pending' && in_array($a->priority, ['critical', 'high'], strict: true))->count();
        $escalatedCount = $allActivities->filter(fn ($a) => (bool) $a->latest_log?->is_escalated)->count();

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

        $this->visiblePendingIds = $pending->pluck('id')->map(fn ($id) => (int) $id)->values()->toArray();

        // Supervisor Console: Recent security audit trail for leads and admins
        $recentAudits = null;
        if (auth()->user()->canManageActivities()) {
            $recentAudits = AuditLog::latest()->limit(5)->get();
        }

        // Load shift handovers for the current date
        $shiftHandovers = ShiftHandover::forDate($this->date)
            ->with(['outgoingLead', 'incomingLead'])
            ->latest('id')
            ->get();

        return view('livewire.daily-activity-board', compact(
            'pending',
            'done',
            'recentAudits',
            'categories',
            'teamMembers',
            'shiftHandovers',
            'totalActivitiesCount',
            'totalPendingCount',
            'totalDoneCount',
            'myTasksCount',
            'myPendingTasksCount',
            'unassignedCount',
            'criticalPendingCount',
            'escalatedCount'
        ));
    }
}
