<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Conversation
 */
class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $unreadCount = $viewer ? $this->unreadCountFor($viewer->id) : 0;
        $title = $viewer ? $this->displayTitleFor($viewer) : ($this->title ?? 'Channel');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $title,
            'raw_title' => $this->title,
            'description' => $this->description,
            'is_private' => (bool) $this->is_private,
            'is_direct' => $this->isDirect(),
            'created_by' => $this->created_by,
            'unread_count' => $unreadCount,
            'latest_message' => new MessageResource($this->whenLoaded('latestMessage')),
            'participants' => UserResource::collection($this->whenLoaded('participants')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
