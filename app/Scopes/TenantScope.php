<?php

declare(strict_types=1);

namespace App\Scopes;

use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * TenantScope — Global Eloquent Scope Enforcing Workspace Isolation Boundaries.
 *
 * When an active workspace is resolved in TenantContext, all queries are automatically
 * restricted to records matching the workspace_id foreign key.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (TenantContext::hasWorkspace()) {
            $builder->where($model->getTable().'.workspace_id', '=', TenantContext::getWorkspaceId());
        }
    }
}
