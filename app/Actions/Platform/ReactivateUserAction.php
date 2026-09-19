<?php

declare(strict_types=1);

namespace App\Actions\Platform;

use App\Models\AuditLog;
use App\Models\SecurityEvent;
use App\Models\User;

final class ReactivateUserAction
{
    /**
     * Administratively restore an active status to a suspended user account.
     */
    public function execute(User $user, User $admin): User
    {
        $oldSuspended = $user->suspended_at;
        $user->suspended_at = null;
        $user->save();

        // Audit Trail
        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'event' => 'user_reactivated',
            'old_values' => ['suspended_at' => $oldSuspended?->toIso8601String()],
            'new_values' => ['suspended_at' => null],
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        // Security Event
        SecurityEvent::record(
            eventType: 'user_reactivated',
            severity: 'info',
            actor: $admin,
            details: [
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
            ]
        );

        return $user;
    }
}
