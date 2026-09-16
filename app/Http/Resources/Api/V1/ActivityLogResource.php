<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ActivityLog
 */
class ActivityLogResource extends JsonResource
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
            'activity_id' => $this->activity_id,
            'date' => $this->date->format('Y-m-d'),
            'status' => $this->status,
            'remark' => $this->remark,
            'incident_ticket' => $this->incident_ticket,
            'is_escalated' => (bool) $this->is_escalated,
            'updated_by' => $this->updated_by,
            'actor_name' => $this->actor_name,
            'actor_role' => $this->actor_role,
            'actor_designation' => $this->actor_designation,
            'updater' => new UserResource($this->whenLoaded('updater')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
