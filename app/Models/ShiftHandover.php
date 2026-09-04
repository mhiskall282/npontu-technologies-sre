<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ShiftHandover — Formal SRE Operational Handover Record
 *
 * Implements FR-4 enterprise extension:
 * Captures outgoing team lead handover briefings, incident summaries, and statistical snapshots
 * when transferring operational responsibility between shifts (Morning, Afternoon, Night).
 *
 * @property int $id
 * @property Carbon $date
 * @property string $shift
 * @property int $outgoing_lead_id
 * @property int|null $incoming_lead_id
 * @property string $summary
 * @property string|null $incidents
 * @property int $pending_tasks_count
 * @property int $completed_tasks_count
 * @property Carbon|null $signed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ShiftHandover extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'date',
        'shift',
        'outgoing_lead_id',
        'incoming_lead_id',
        'summary',
        'incidents',
        'pending_tasks_count',
        'completed_tasks_count',
        'signed_at',
        'accepted_at',
        'accepted_by_id',
        'acceptance_remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'signed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'pending_tasks_count' => 'integer',
            'completed_tasks_count' => 'integer',
        ];
    }

    /**
     * Outgoing Lead who drafted and signed off this shift handover.
     *
     * @return BelongsTo<User, $this>
     */
    public function outgoingLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'outgoing_lead_id');
    }

    /**
     * Incoming Lead designated to receive operational responsibility.
     *
     * @return BelongsTo<User, $this>
     */
    public function incomingLead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incoming_lead_id');
    }

    /**
     * Lead or supervisor who acknowledged and accepted (signed on) the handover.
     *
     * @return BelongsTo<User, $this>
     */
    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_id');
    }

    /**
     * Check if this handover has been formally accepted by the incoming shift lead.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Scope query to a specific calendar date.
     *
     * @param  Builder<ShiftHandover>  $query
     */
    public function scopeForDate(Builder $query, string $date): void
    {
        $query->where('date', $date);
    }
}
