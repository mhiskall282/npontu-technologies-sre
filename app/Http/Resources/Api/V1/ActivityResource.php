<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Activity
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $targetDate = $request->query('date', today()->toDateString());
        $currentStatus = $this->current_status ?? $this->currentStatusForDate($targetDate);
        $latestLog = $this->latest_log ?? $this->latestLogForDate($targetDate);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'recurrence' => $this->recurrence,
            'priority' => $this->priority,
            'sla_time' => $this->sla_time,
            'is_pinned' => (bool) $this->is_pinned,
            'is_active' => (bool) $this->is_active,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'assigned_to' => $this->assigned_to,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'current_status' => $currentStatus,
            'latest_log' => $latestLog ? new ActivityLogResource($latestLog) : null,
            'logs' => ActivityLogResource::collection($this->whenLoaded('logs')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
