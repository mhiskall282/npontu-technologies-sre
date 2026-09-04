<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AuditLog;
use App\Services\ReportingService;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * DailyActivityBoard — Real-Time SRE Shift Handover Board
 *
 * Implements FR-4 (Daily View & Shift Handover):
 *   - Serves as the primary operational console for incoming/outgoing SRE support shifts.
 *   - Displays checks categorized into "Needs Attention (Pending)" and "Completed (Done)".
 *   - Provides real-time event-driven reactivity via Livewire 3 listeners:
 *     #[On('status-updated')] intercepts updates dispatched by child ActivityStatusUpdater components
 *     and triggers an instantaneous server re-render (~100ms) without full page reload.
 *   - Features wire:poll.30000ms as a background multi-operator sync mechanism.
 *   - Includes instant search and category filtering for managing large catalogs (100+ checks).
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
     * Initialize the component state with today's date.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    /**
     * Hook triggered when the date property changes.
     * Livewire re-renders the component automatically with the selected date's snapshot.
     *
     * @return void
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
     * @return void
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
     * Reset both search and category filters back to the full activity catalog.
     *
     * @return void
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
    }

    /**
     * Render the shift handover board view.
     *
     * Orchestration:
     *   1. Queries ReportingService::dailySummary() to eager-load day logs and derive current statuses.
     *   2. Extracts available category tokens for filter buttons.
     *   3. Computes day-wide stats (total, pending, done) that remain accurate during filtering.
     *   4. Applies search and category filters to pending and completed collections.
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

        // Compute overall shift metrics before applying search/category filters
        $totalActivitiesCount = $allActivities->count();
        $totalPendingCount = $allActivities->filter(fn ($a) => $a->current_status === 'pending')->count();
        $totalDoneCount = $allActivities->filter(fn ($a) => $a->current_status === 'done')->count();

        // Apply interactive client filters
        $filtered = $allActivities;

        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $filtered = $filtered->filter(function ($activity) use ($term) {
                return str_contains(mb_strtolower((string) $activity->title), $term)
                    || str_contains(mb_strtolower((string) $activity->description), $term)
                    || str_contains(mb_strtolower((string) $activity->category), $term);
            });
        }

        if ($this->category !== '') {
            $filtered = $filtered->filter(fn ($activity) => $activity->category === $this->category);
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
            'totalActivitiesCount',
            'totalPendingCount',
            'totalDoneCount'
        ));
    }
}
