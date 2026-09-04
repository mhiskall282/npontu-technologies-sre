<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

/**
 * ActivityPolicy — Access Control Policy for Operational Activities
 *
 * Implements authorization rules defined in requirements and AGENTS.md:
 *   - Any authenticated user can view activities or the daily board.
 *   - Only supervisors (Admin and Team Lead) can create or update activity definitions.
 *   - Only administrators can permanently or soft-delete activities.
 *   - Any authenticated user can update activity status/remark (essential for shift handovers).
 */
class ActivityPolicy
{
    /**
     * Determine whether the user can view any activities.
     *
     * @param  User  $user  Authenticated user
     * @return bool Always true for authenticated team members
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can inspect a specific activity.
     *
     * @param  User  $user  Authenticated user
     * @param  Activity  $activity  Activity instance
     * @return bool Always true for authenticated team members
     */
    public function view(User $user, Activity $activity): bool
    {
        return true;
    }

    /**
     * Determine whether the user can provision new activity definitions.
     *
     * @param  User  $user  Authenticated user
     * @return bool True if admin or team lead
     */
    public function create(User $user): bool
    {
        return $user->canManageActivities();
    }

    /**
     * Determine whether the user can update activity configuration definitions.
     *
     * @param  User  $user  Authenticated user
     * @param  Activity  $activity  Activity instance
     * @return bool True if admin or team lead
     */
    public function update(User $user, Activity $activity): bool
    {
        return $user->canManageActivities();
    }

    /**
     * Determine whether the user can soft-delete an activity definition.
     *
     * @param  User  $user  Authenticated user
     * @param  Activity  $activity  Activity instance
     * @return bool True only for system administrators
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore a soft-deleted activity.
     *
     * @param  User  $user  Authenticated user
     * @param  Activity  $activity  Activity instance
     * @return bool True only for system administrators
     */
    public function restore(User $user, Activity $activity): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can log a daily status transition and remark.
     *
     * Shift handover context: Any active support operator on shift can record
     * whether a check was completed, regardless of who originally authored the definition.
     *
     * @param  User  $user  Authenticated operator on shift
     * @param  Activity  $activity  Activity check being updated
     * @return bool Always true for authenticated team members
     */
    public function updateStatus(User $user, Activity $activity): bool
    {
        return true;
    }
}
