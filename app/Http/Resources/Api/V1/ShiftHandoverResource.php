<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\ShiftHandover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShiftHandover
 */
class ShiftHandoverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'shift' => $this->shift,
            'shift_label' => ucfirst($this->shift).' Shift',
            'outgoing_lead_id' => $this->outgoing_lead_id,
            'outgoing_lead' => new UserResource($this->whenLoaded('outgoingLead')),
            'incoming_lead_id' => $this->incoming_lead_id,
            'incoming_lead' => new UserResource($this->whenLoaded('incomingLead')),
            'summary' => $this->summary,
            'incidents' => $this->incidents,
            'pending_tasks_count' => $this->pending_tasks_count,
            'completed_tasks_count' => $this->completed_tasks_count,
            'signed_at' => $this->signed_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'accepted_by_id' => $this->accepted_by_id,
            'accepted_by' => new UserResource($this->whenLoaded('acceptedBy')),
            'acceptance_remarks' => $this->acceptance_remarks,
            'is_accepted' => $this->isAccepted(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
