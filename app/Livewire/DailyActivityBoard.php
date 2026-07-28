<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\ReportingService;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Daily Activity Board — Support Tracker')]
class DailyActivityBoard extends Component
{
    public string $date;

    public function mount(): void
    {
        $this->date = today()->format('Y-m-d');
    }

    public function updatedDate(): void
    {
        // Reactive: when $date changes, render() is called automatically
    }

    public function render(ReportingService $service): View
    {
        $activities = $service->dailySummary($this->date);

        $pending = $activities->filter(fn ($a) => $a->current_status === 'pending');
        $done = $activities->filter(fn ($a) => $a->current_status === 'done');

        return view('livewire.daily-activity-board', compact('pending', 'done'));
    }
}
