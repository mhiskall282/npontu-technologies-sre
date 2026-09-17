<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use App\Models\OperationalNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    /**
     * List notifications for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OperationalNotification::class);

        $user = $request->user();

        $query = OperationalNotification::where('user_id', $user->id)
            ->latest('created_at');

        if ($request->query('unread_only') === 'true' || $request->query('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $unreadCount = OperationalNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $notifications = $query->paginate($perPage);

        $meta = [
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
            'per_page' => $notifications->perPage(),
            'total' => $notifications->total(),
            'unread_count' => $unreadCount,
        ];

        return $this->respondWithSuccess(
            NotificationResource::collection($notifications->items()),
            'Operational notifications retrieved successfully.',
            200,
            $meta
        );
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, OperationalNotification $notification): JsonResponse
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return $this->respondWithSuccess(
            new NotificationResource($notification->fresh()),
            'Notification marked as read.'
        );
    }

    /**
     * Mark all notifications as read for authenticated user.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->authorize('viewAny', OperationalNotification::class);

        $count = OperationalNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->respondWithSuccess(
            ['updated_count' => $count],
            "All ({$count}) notifications marked as read."
        );
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, OperationalNotification $notification): JsonResponse
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return $this->respondNoContent();
    }
}
