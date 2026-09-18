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
 * ApplyOrganizationAction — Handles Self-Service Registration & Configurable Auto-Approval.
 */
final class ApplyOrganizationAction
{
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Submit an organization application with automated risk scoring and auto-approval evaluation.
     *
     * @param  array<string, mixed>  $validated
     * @return array{application: OrganizationApplication, auto_approved: bool, organization: ?Organization}
     */
    public function execute(array $validated, User $applicant): array
    {
        $orgSlug = Str::slug($validated['organization_slug'] ?? $validated['organization_name']);

        // 1. Calculate risk score based on parameters
        $riskScore = 0;
        $contactEmail = strtolower(trim((string) $validated['contact_email']));

        // Higher risk for disposable email domains
        if (Str::endsWith($contactEmail, ['@mailinator.com', '@trashmail.com', '@tempmail.com', '@sharklasers.com'])) {
            $riskScore += 80;
        }

        // Requested enterprise or customer-hosted models require manual architectural review
        if (in_array($validated['deployment_model'] ?? 'shared_saas', ['customer_hosted', 'dedicated_managed'], true)) {
            $riskScore += 40;
        }

        // 2. Determine auto-approval status (auto-approve if low risk and tier is free/team)
        $autoApprove = $riskScore < 30 && in_array($validated['tier'] ?? 'team', ['free', 'team'], true);
        $status = $autoApprove ? 'approved' : 'pending_review';

        $application = OrganizationApplication::create([
            'organization_name' => $validated['organization_name'],
            'organization_slug' => $orgSlug,
            'applicant_user_id' => $applicant->id,
            'contact_email' => $contactEmail,
            'status' => $status,
            'deployment_model' => $validated['deployment_model'] ?? 'shared_saas',
            'preferred_region' => $validated['preferred_region'] ?? 'af-south',
            'tier' => $validated['tier'] ?? 'team',
            'notes' => $validated['notes'] ?? null,
            'risk_score' => $riskScore,
            'reviewed_at' => $autoApprove ? now() : null,
            'review_notes' => $autoApprove ? 'Automatically approved by system rule engine.' : null,
        ]);

        $organization = null;

        // 3. Provision organization and primary workspace if auto-approved
        if ($autoApprove) {
            $organization = Organization::create([
                'uuid' => (string) Str::uuid(),
                'name' => $application->organization_name,
                'slug' => $application->organization_slug,
                'company_code' => 'OPS-'.strtoupper(Str::random(6)),
                'status' => 'active',
                'tier' => $application->tier,
                'deployment_model' => $application->deployment_model,
                'preferred_region' => $application->preferred_region,
                'settings' => ['auto_approved' => true],
            ]);

            OrganizationMembership::create([
                'organization_id' => $organization->id,
                'user_id' => $applicant->id,
                'role' => 'owner',
                'status' => 'active',
            ]);

            $workspace = Workspace::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $organization->id,
                'owner_user_id' => $applicant->id,
                'name' => "{$organization->name} Operations",
                'slug' => "{$organization->slug}-primary",
                'is_personal' => false,
                'status' => 'active',
                'retention_days' => 90,
            ]);

            WorkspaceMembership::create([
                'workspace_id' => $workspace->id,
                'user_id' => $applicant->id,
                'role' => 'admin',
                'status' => 'active',
            ]);
        }

        $this->auditService->log(
            subject: $application,
            event: 'created',
            newValues: [
                'organization_name' => $application->organization_name,
                'status' => $application->status,
                'risk_score' => $application->risk_score,
                'auto_approved' => $autoApprove,
            ],
        );

        return [
            'application' => $application,
            'auto_approved' => $autoApprove,
            'organization' => $organization,
        ];
    }
}
