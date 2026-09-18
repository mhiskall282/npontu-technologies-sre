<?php

declare(strict_types=1);

namespace App\Actions\Saas;

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Services\AuditService;
use Illuminate\Validation\ValidationException;

/**
 * JoinOrganizationByCodeAction — Fast Onboarding via Company Code or QR Code.
 */
final class JoinOrganizationByCodeAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Join an organization using a company code and gain access to its primary workspace.
     *
     * @return array{organization: Organization, workspace: ?Workspace}
     */
    public function execute(string $companyCode, User $user): array
    {
        $cleanedCode = strtoupper(trim($companyCode));

        $organization = Organization::where('company_code', $cleanedCode)->first();

        if (! $organization) {
            throw ValidationException::withMessages([
                'company_code' => ['No active organization matches this company code.'],
            ]);
        }

        if (! $organization->isActive()) {
            throw ValidationException::withMessages([
                'company_code' => ['The specified organization is currently suspended or inactive.'],
            ]);
        }

        // Check if user is already a member
        $existingOrgMembership = OrganizationMembership::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $existingOrgMembership) {
            $existingOrgMembership = OrganizationMembership::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'role' => 'member',
                'status' => 'active',
            ]);

            $this->auditService->log(
                subject: $organization,
                event: 'user_joined',
                newValues: [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'company_code' => $cleanedCode,
                ],
            );
        }

        // Resolve primary workspace and attach user membership if not already present
        $primaryWorkspace = $organization->workspaces()->where('status', 'active')->first();

        if ($primaryWorkspace && ! $primaryWorkspace->hasUser($user)) {
            WorkspaceMembership::create([
                'workspace_id' => $primaryWorkspace->id,
                'user_id' => $user->id,
                'role' => 'agent',
                'status' => 'active',
            ]);
        }

        return [
            'organization' => $organization,
            'workspace' => $primaryWorkspace,
        ];
    }
}
