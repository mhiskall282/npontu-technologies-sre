<?php

declare(strict_types=1);

namespace App\Actions\Saas;

use App\Models\Organization;
use App\Models\OrganizationApplication;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMembership;
use App\Services\AuditService;
use Illuminate\Support\Str;

/**
 * ReviewOrganizationApplicationAction — Administrator Review & Manual Provisioning.
 */
final class ReviewOrganizationApplicationAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Process review decision (approve, reject, or request more information).
     *
     * @param  array<string, mixed>  $validated
     */
    public function execute(
        OrganizationApplication $application,
        array $validated,
        User $reviewer,
    ): OrganizationApplication {
        $decision = $validated['decision']; // 'approved', 'rejected', 'additional_info_required'
        $reviewNotes = $validated['review_notes'] ?? null;
        $rejectionReason = $validated['rejection_reason'] ?? null;

        $oldValues = [
            'status' => $application->status,
            'reviewed_by' => $application->reviewed_by,
        ];

        $application->update([
            'status' => $decision,
            'review_notes' => $reviewNotes,
            'rejection_reason' => $decision === 'rejected' ? $rejectionReason : null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        if ($decision === 'approved') {
            // Provision organization
            $organization = Organization::create([
                'uuid' => (string) Str::uuid(),
                'name' => $application->organization_name,
                'slug' => $application->organization_slug,
                'company_code' => 'OPS-'.strtoupper(Str::random(6)),
                'status' => 'active',
                'tier' => $application->tier,
                'deployment_model' => $application->deployment_model,
                'preferred_region' => $application->preferred_region,
                'settings' => ['approved_by_admin_id' => $reviewer->id],
            ]);

            // Assign applicant as owner
            OrganizationMembership::create([
                'organization_id' => $organization->id,
                'user_id' => $application->applicant_user_id,
                'role' => 'owner',
                'status' => 'active',
            ]);

            // Provision default workspace
            $workspace = Workspace::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $organization->id,
                'owner_user_id' => $application->applicant_user_id,
                'name' => "{$organization->name} Operations",
                'slug' => "{$organization->slug}-primary",
                'is_personal' => false,
                'status' => 'active',
                'retention_days' => 90,
            ]);

            WorkspaceMembership::create([
                'workspace_id' => $workspace->id,
                'user_id' => $application->applicant_user_id,
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        $this->auditService->log(
            subject: $application,
            event: 'status_changed',
            oldValues: $oldValues,
            newValues: [
                'status' => $application->status,
                'reviewed_by' => $reviewer->id,
                'decision' => $decision,
            ],
        );

        return $application;
    }
}
