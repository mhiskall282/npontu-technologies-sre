<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Workspace;
use App\Scopes\TenantScope;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BelongsToWorkspace — Enforces Workspace Tenant Isolation on Eloquent Models.
 */
trait BelongsToWorkspace
{
    /**
     * Boot the trait: attach global tenant scope and auto-assign workspace_id on creation.
     */
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (empty($model->workspace_id) && TenantContext::hasWorkspace()) {
                $model->workspace_id = TenantContext::getWorkspaceId();
            }
        });
    }

    /**
     * The workspace this record belongs to.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
