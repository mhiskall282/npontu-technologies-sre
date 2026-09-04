<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * AuditService — Immutable Compliance Audit Trail
 *
 * ARCHITECTURAL DESIGN & SEPARATION OF CONCERNS:
 * - `activity_logs`: Domain state store used for shift handover views and daily reporting.
 * - `audit_logs`: Security and compliance audit trail tracking all mutations (who changed what, IP, diff).
 *
 * BIO CAPTURE & INTEGRITY:
 * All actor fields (name, role, designation, and IP address) are captured strictly from
 * server-side auth session and Request::ip() — never accepted from client-submitted data.
 * This prevents bio spoofing by malicious actors.
 */
final class AuditService
{
    /**
     * Record an immutable audit log entry for a domain model mutation.
     *
     * @param  Model  $subject  The Eloquent model subject of the mutation (e.g. Activity, User)
     * @param  string  $event  The mutation event type ('created', 'updated', 'status_changed', 'deleted')
     * @param  array|null  $oldValues  Attribute snapshot before the mutation
     * @param  array|null  $newValues  Attribute snapshot after the mutation
     * @return AuditLog The newly persisted immutable audit log record
     */
    public function log(
        Model $subject,
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        /** @var User|null $actor */
        $actor = Auth::user();

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'actor_role' => $actor?->role,
            'actor_ip' => Request::ip(),
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
