<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\OperationalNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OperationalNotification
 */
class NotificationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'title' => $this->title,
            'message' => $this->message,
            'body' => $this->message,
            'type' => $this->type,
            'priority' => $this->priority,
            'action_route' => $this->action_route,
            'metadata' => $this->metadata,
            'data' => $this->metadata,
            'related_type' => $this->metadata['related_type'] ?? null,
            'related_id' => isset($this->metadata['related_id']) ? (int) $this->metadata['related_id'] : null,
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'time_ago' => $this->created_at?->diffForHumans(),
        ];
    }
}
