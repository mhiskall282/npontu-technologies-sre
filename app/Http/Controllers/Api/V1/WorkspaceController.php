<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Saas\CreateWorkspaceAction;
use App\Http\Requests\Api\V1\CreateWorkspaceRequest;
use App\Http\Requests\Api\V1\SwitchWorkspaceRequest;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WorkspaceController extends ApiController
{
    /**
     * List all operational workspaces accessible to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->with('organization:id,name,slug,company_code')
            ->get()
            ->map(function (Workspace $ws) {
                return [
                    'id' => $ws->id,
                    'uuid' => $ws->uuid,
                    'name' => $ws->name,
                    'slug' => $ws->slug,
                    'subdomain' => $ws->subdomain,
                    'custom_domain' => $ws->custom_domain,
                    'is_personal' => $ws->is_personal,
                    'status' => $ws->status,
                    'my_role' => $ws->pivot->role ?? 'agent',
                    'organization' => $ws->organization ? [
                        'id' => $ws->organization->id,
                        'name' => $ws->organization->name,
                        'slug' => $ws->organization->slug,
                        'company_code' => $ws->organization->company_code,
                    ] : null,
                ];
            });

        return $this->respondWithSuccess(
            data: $workspaces,
            message: 'User workspaces retrieved successfully',
            meta: [
                'count' => $workspaces->count(),
                'active_workspace_id' => TenantContext::getWorkspaceId() ?? $user->currentWorkspace()?->id,
            ]
        );
    }

    /**
     * Provision a new operational workspace.
     */
    public function store(CreateWorkspaceRequest $request, CreateWorkspaceAction $action): JsonResponse
    {
        $workspace = $action->execute($request->validated(), $request->user());

        return $this->respondCreated(
            data: [
                'id' => $workspace->id,
                'uuid' => $workspace->uuid,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'subdomain' => $workspace->subdomain,
                'is_personal' => $workspace->is_personal,
                'status' => $workspace->status,
            ],
            message: 'Operational workspace provisioned successfully'
        );
    }

    /**
     * Switch active operational workspace context.
     */
    public function switch(SwitchWorkspaceRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $targetIdentifier = $request->validated('workspace_id');

        $workspace = Workspace::where('id', $targetIdentifier)
            ->orWhere('uuid', $targetIdentifier)
            ->orWhere('slug', $targetIdentifier)
            ->first();

        if (! $workspace) {
            return $this->respondWithError(
                message: 'Target workspace not found',
                statusCode: 404
            );
        }

        if (! $workspace->isActive()) {
            return $this->respondWithError(
                message: 'Target workspace is suspended or inactive',
                statusCode: 403
            );
        }

        if (! $workspace->hasUser($user) && ! $user->isAdmin()) {
            return $this->respondWithError(
                message: 'You are not an authorized member of this workspace',
                statusCode: 403
            );
        }

        TenantContext::setWorkspace($workspace);

        if ($request->hasSession()) {
            $request->session()->put('opsora_workspace_id', $workspace->id);
        }

        return $this->respondWithSuccess(
            data: [
                'id' => $workspace->id,
                'uuid' => $workspace->uuid,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'is_personal' => $workspace->is_personal,
                'organization' => $workspace->organization?->name,
            ],
            message: "Switched active workspace to {$workspace->name}"
        );
    }
}
