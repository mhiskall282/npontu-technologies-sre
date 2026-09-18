<?php

declare(strict_types=1);

namespace App\Actions\Saas;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Services\AuditService;
use Illuminate\Support\Str;

/**
 * CreateWorkspaceAction — Provisions a New Operational Workspace.
 */
final class CreateWorkspaceAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Provision a new operational workspace and grant administrative membership to creator.
     *
     * @param  array<string, mixed>  $validated
     */
    public function execute(array $validated, User $creator): Workspace
    {
        $isPersonal = (bool) ($validated['is_personal'] ?? false);

        $workspace = Workspace::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $isPersonal ? null : ($validated['organization_id'] ?? null),
            'owner_user_id' => $creator->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug'] ?? $validated['name']),
            'subdomain' => $validated['subdomain'] ?? null,
            'custom_domain' => $validated['custom_domain'] ?? null,
            'is_personal' => $isPersonal,
            'status' => 'active',
            'retention_days' => (int) ($validated['retention_days'] ?? 90),
            'settings' => $validated['settings'] ?? [],
        ]);

        WorkspaceMembership::create([
            'workspace_id' => $workspace->id,
            'user_id' => $creator->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->auditService->log(
            subject: $workspace,
            event: 'created',
            newValues: [
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'organization_id' => $workspace->organization_id,
                'is_personal' => $workspace->is_personal,
            ],
        );

        return $workspace;
    }
}
