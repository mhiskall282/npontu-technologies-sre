<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * UserPolicy — Access Control Policy for User Accounts
 *
 * Enforces role-based permissions:
 *   - Only administrators can view the user list or create/delete user accounts.
 *   - Users can view and update their own profile; administrators can update all profiles.
 *   - Administrators cannot delete their own active account (prevents system lockout).
 */
class UserPolicy
{
    /**
     * Determine whether the user can view the user management index.
     *
     * @param  User  $user  Authenticated user
     * @return bool True if administrator
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific user's details.
     *
     * @param  User  $user  Authenticated user
     * @param  User  $target  Target user model
     * @return bool True if administrator or self
     */
    public function view(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->id === $target->id;
    }

    /**
     * Determine whether the user can provision new accounts.
     *
     * @param  User  $user  Authenticated user
     * @return bool True if administrator
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update account details.
     *
     * @param  User  $user  Authenticated user
     * @param  User  $target  Target user model
     * @return bool True if administrator or self
     */
    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() || $user->id === $target->id;
    }

    /**
     * Determine whether the user can delete an account.
     *
     * @param  User  $user  Authenticated user
     * @param  User  $target  Target user to delete
     * @return bool True if administrator and not self
     */
    public function delete(User $user, User $target): bool
    {
        // Enforce safety invariant: an administrator cannot delete their own account
        return $user->isAdmin() && $user->id !== $target->id;
    }
}
