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

#[Layout('layouts.app')]
#[Title('Daily Activity Board — Support Tracker')]
class DailyActivityBoard extends Component
{
    public string $date;

    public string $search = '';

    public string $category = '';

    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    public function updatedDate(): void
    {
        // Reactive: when $date changes, render() is called automatically
    }

    #[On('status-updated')]
    #[On('statusUpdated')]
    public function refreshBoard(?string $message = null): void
    {
        if ($message) {
            session()->flash('success', $message);
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';
    }

    public function render(ReportingService $service): View
    {
        $allActivities = $service->dailySummary($this->date);

        // Extract categories available on this date
        $categories = $allActivities->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // Overall day statistics before filtering
        $totalActivitiesCount = $allActivities->count();
        $totalPendingCount = $allActivities->filter(fn ($a) => $a->current_status === 'pending')->count();
        $totalDoneCount = $allActivities->filter(fn ($a) => $a->current_status === 'done')->count();

        // Apply search and category filters
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

        $pending = $filtered->filter(fn ($a) => $a->current_status === 'pending');
        $done = $filtered->filter(fn ($a) => $a->current_status === 'done');

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
