<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\Activities\UpdateActivityStatusAction;
use App\Models\Activity;
use Illuminate\View\View;
use Livewire\Component;

/**
 * ActivityStatusUpdater — Inline Operational Status Toggle & Handover Form
 *
 * Implements FR-3 (Status Updates & Logging) within the Shift Handover context:
 *   - Nested child component within DailyActivityBoard rows.
 *   - Provides inline UI to toggle an activity between "pending" and "done".
 *   - Allows operators to attach optional handover notes or discrepancy remarks.
 *   - Delegates domain mutation to UpdateActivityStatusAction to enforce append-only event logging.
 *   - Dispatches 'status-updated' and 'statusUpdated' browser events to notify the parent board.
 */
class ActivityStatusUpdater extends Component
{
    /**
     * Target activity definition being updated.
     */
    public Activity $activity;

    /**
     * Target calendar date of the operational shift (Y-m-d).
     */
    public string $date;

    /**
     * Selected status value ('pending' or 'done').
     */
    public string $status = 'pending';

    /**
     * Optional shift handover note or observation remark.
     */
    public string $remark = '';

    /**
     * Optional related incident ticket ID (e.g. 'INC-1042').
     */
    public string $incidentTicket = '';

    /**
     * Flag indicating this check is an active operational escalation.
     */
    public bool $isEscalated = false;

    /**
     * Controls visibility of the inline update form popover.
     */
    public bool $showForm = false;

    /**
     * Mount the component with initial activity context.
     *
     * @param  Activity  $activity  Activity instance
     * @param  string  $date  Calendar date (Y-m-d)
     * @param  string  $currentStatus  Current status derived for this date
     */
    public function mount(Activity $activity, string $date, string $currentStatus): void
    {
        $this->activity = $activity;
        $this->date = $date;
        $this->status = $currentStatus;

        // Pre-populate if today's latest log already had an incident ticket or escalation
        $latestLog = $activity->latestLogForDate($date);
        if ($latestLog) {
            $this->incidentTicket = (string) ($latestLog->incident_ticket ?? '');
            $this->isEscalated = (bool) $latestLog->is_escalated;
        }
    }

    /**
     * Toggle the visibility state of the update popover form.
     */
    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
    }

    /**
     * Validate and persist the operational status update.
     *
     * Execution Flow:
     *   1. Authorization: Checks ActivityPolicy::updateStatus().
     *   2. Validation: Ensures status is valid ('pending' or 'done') and remark <= 1000 chars.
     *   3. Action: Calls UpdateActivityStatusAction to append an ActivityLog row and write an AuditLog.
     *   4. Event Dispatch: Dispatches 'status-updated' to trigger immediate re-render of DailyActivityBoard.
     *   5. Flash Notification: Sets a success message for UI feedback.
     *
     * @param  UpdateActivityStatusAction  $action  Domain action executing append-only update
     */
    public function save(UpdateActivityStatusAction $action): void
    {
        // Enforce policy: verify operator has permission to update activity status
        $this->authorize('updateStatus', $this->activity);

        // Validate user inputs
        $this->validate([
            'status' => ['required', 'in:pending,done'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'incidentTicket' => ['nullable', 'string', 'max:50'],
            'isEscalated' => ['boolean'],
        ]);

        // Execute domain action: appends a new ActivityLog event row and writes AuditLog
        $action->execute(
            activity: $this->activity,
            status: $this->status,
            remark: $this->remark ?: null,
            date: $this->date,
            incidentTicket: $this->incidentTicket ? trim($this->incidentTicket) : null,
            isEscalated: $this->isEscalated,
        );

        // Collapse form and reset transient input
        $this->showForm = false;
        $this->remark = '';

        $statusLabel = ucfirst($this->status);
        $escalatedNotice = $this->isEscalated ? ' [Escalated]' : '';
        $message = "Status for '{$this->activity->title}' marked as {$statusLabel}{$escalatedNotice}.";

        // Dispatch events to notify parent DailyActivityBoard for real-time reactivity
        $this->dispatch('status-updated', message: $message);
        $this->dispatch('statusUpdated', message: $message);

        session()->flash('success', $message);
    }

    /**
     * Render the updater component view.
     *
     * @return View Blade view
     */
    public function render(): View
    {
        return view('livewire.activity-status-updater');
    }
}
