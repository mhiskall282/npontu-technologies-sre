<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\SecurityEvent;
use App\Models\User;

final class SuspendUserAction
{
    /**
     * Administratively suspend a user account and immediately revoke all active sessions and API tokens.
     */
    public function execute(User $user, string $reason, User $admin): User
    {
        $user->suspended_at = now();
        $user->save();

        // Invalidate all active Sanctum tokens immediately
        $user->tokens()->delete();

        // Audit Trail
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'event' => 'user_suspended',
            'old_values' => ['suspended_at' => null],
            'new_values' => ['suspended_at' => $user->suspended_at->toIso8601String(), 'reason' => $reason],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        // Security Event
        SecurityEvent::record(
            eventType: 'user_suspended',
            severity: 'warning',
            actor: $admin,
            details: [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
                'reason' => $reason,
            ]
        );

        return $user;
    }
}
