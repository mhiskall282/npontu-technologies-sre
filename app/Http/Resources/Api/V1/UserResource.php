<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'grade' => $this->grade,
            'grade_label' => User::GRADES[$this->grade] ?? $this->grade,
            'department' => $this->department,
            'designation' => $this->designation,
            'phone' => $this->phone,
            'privileges' => $this->privileges ?? [],
            'unread_messages_count' => $this->when(
                $request->user()?->id === $this->id,
                fn () => $this->unreadMessagesCount()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
