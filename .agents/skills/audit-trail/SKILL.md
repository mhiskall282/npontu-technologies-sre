---
name: audit-trail
description: >
  Standardised pattern for recording immutable audit log entries in the
  Support Activity Tracker. Triggered whenever implementation of a state-change
  action is needed. Covers migration schema, AuditService API, and controller
  integration.
---

# Audit Trail Pattern -- Support Activity Tracker

## Why This Exists

The grading rubric explicitly scores **Non-Functional Requirements**, and the brief calls out
*capture bio details of the personnel + timestamp for each status update* as a core requirement
(FR-3). Every mutable action -- create, update, status change, delete -- must produce an immutable
log entry. This skill defines the canonical implementation so all agent turns stay consistent.

---

## 1. Database Schema

### Migration: create_audit_logs_table

`php
Schema::create('audit_logs', function (Blueprint \) {
    \->id();
    \->unsignedBigInteger('actor_id')->nullable();
    \->string('actor_name');
    \->string('actor_role')->nullable();
    \->string('actor_ip', 45)->nullable();
    \->morphs('subject');
    \->string('event');
    \->json('old_values')->nullable();
    \->json('new_values')->nullable();
    \->timestamps();

    \->index(['subject_type', 'subject_id']);
    \->index('actor_id');
    \->index('created_at');

    \->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
});
`

---

## 2. AuditLog Model

Location: pp/Models/AuditLog.php

`php
<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected \ = [
        'actor_id', 'actor_name', 'actor_role', 'actor_ip',
        'subject_type', 'subject_id', 'event', 'old_values', 'new_values',
    ];

    protected \ = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function subject(): MorphTo
    {
        return \->morphTo();
    }

    public function actor(): BelongsTo
    {
        return \->belongsTo(User::class, 'actor_id');
    }
}
`

---

## 3. AuditService

Location: pp/Services/AuditService.php

`php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

final class AuditService
{
    /**
     * Record an audit event for any Eloquent model.
     *
     * @param  Model       \    The model being changed.
     * @param  string      \      One of: created, updated, status_changed, deleted.
     * @param  array|null  \  Snapshot before the change.
     * @param  array|null  \  Snapshot after the change.
     */
    public function log(
        Model \,
        string \,
        ?array \ = null,
        ?array \ = null,
    ): AuditLog {
        \ = Auth::user();

        return AuditLog::create([
            'actor_id'     => \->id,
            'actor_name'   => \->name ?? 'System',
            'actor_role'   => \->role ?? null,
            'actor_ip'     => Request::ip(),
            'subject_type' => get_class(\),
            'subject_id'   => \->getKey(),
            'event'        => \,
            'old_values'   => \,
            'new_values'   => \,
        ]);
    }
}
`

---

## 4. Usage in Action Classes

**Always call AuditService inside the Action, not the controller.**

`php
<?php
declare(strict_types=1);

namespace App\Actions\Activities;

use App\Models\Activity;
use App\Models\ActivityUpdate;
use App\Services\AuditService;

final class UpdateActivityStatusAction
{
    public function __construct(private readonly AuditService \) {}

    public function execute(Activity \, string \, ?string \): ActivityUpdate
    {
        \ = \->status;

        \ = \->updates()->create([
            'status'  => \,
            'remark'  => \,
            'user_id' => auth()->id(),
        ]);

        \->update(['status' => \]);

        \->auditService->log(
            subject: \,
            event: 'status_changed',
            oldValues: ['status' => \],
            newValues: ['status' => \, 'remark' => \],
        );

        return \;
    }
}
`

---

## 5. Mandatory Audit Points

| User action | Event string | Subject |
|---|---|---|
| Create new activity | created | Activity |
| Edit activity fields | updated | Activity |
| Update status/remark | status_changed | Activity |
| Soft-delete activity | deleted | Activity |
| Restore activity | restored | Activity |
| Admin creates user | created | User |
| Admin updates user | updated | User |

---

## 6. Displaying Audit Logs in Views

`php
\ = AuditLog::where('subject_type', Activity::class)
    ->where('subject_id', \->id)
    ->orderByDesc('created_at')
    ->get();
`

Render as a timeline: timestamp left, actor name + role centre, event badge right.
Show before/after JSON in a collapsed details element.

---

## 7. Test Pattern

`php
it('writes an audit log entry when activity status is updated', function () {
    \     = User::factory()->create(['role' => 'support']);
    \ = Activity::factory()->create(['status' => 'pending']);

    \->actingAs(\)
        ->patch(route('activities.update-status', \), [
            'status' => 'done',
            'remark' => 'Verified against logs.',
        ])
        ->assertRedirect();

    expect(AuditLog::where('subject_id', \->id)
        ->where('event', 'status_changed')
        ->exists()
    )->toBeTrue();
});
`
