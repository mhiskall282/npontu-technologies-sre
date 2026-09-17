<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OperationalNotification;
use App\Models\User;

/**
 * OperationalNotificationPolicy — Access Control Policy for In-App Operational Notifications
 *
 * Enforces ownership boundary: users can only view, mark as read, or modify their own notifications.
 */
class OperationalNotificationPolicy
{
    /**
     * Determine whether the user can list their notifications.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view a specific notification.
     */
    public function view(User $user, OperationalNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can mark as read or mutate a notification.
     */
    public function update(User $user, OperationalNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete a notification.
     */
    public function delete(User $user, OperationalNotification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
