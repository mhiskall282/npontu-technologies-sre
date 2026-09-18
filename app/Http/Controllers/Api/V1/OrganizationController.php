<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Saas\ApplyOrganizationAction;
use App\Actions\Saas\JoinOrganizationByCodeAction;
use App\Actions\Saas\ReviewOrganizationApplicationAction;
use App\Http\Requests\Api\V1\ApplyOrganizationRequest;
use App\Http\Requests\Api\V1\JoinOrganizationByCodeRequest;
use App\Http\Requests\Api\V1\ReviewOrganizationApplicationRequest;
use App\Models\OrganizationApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrganizationController extends ApiController
{
    /**
     * Join an organization via its company code or QR code token.
     */
    public function joinByCode(
        JoinOrganizationByCodeRequest $request,
        JoinOrganizationByCodeAction $action
    ): JsonResponse {
        $result = $action->execute(
            $request->validated('company_code'),
            $request->user()
        );

        return $this->respondWithSuccess(
            data: [
                'organization' => [
                    'id' => $result['organization']->id,
                    'name' => $result['organization']->name,
                    'slug' => $result['organization']->slug,
                    'company_code' => $result['organization']->company_code,
                    'tier' => $result['organization']->tier,
                ],
                'primary_workspace' => $result['workspace'] ? [
                    'id' => $result['workspace']->id,
                    'uuid' => $result['workspace']->uuid,
                    'name' => $result['workspace']->name,
                    'slug' => $result['workspace']->slug,
                ] : null,
            ],
            message: "Successfully joined {$result['organization']->name}"
        );
    }

    /**
     * Submit an organization registration application.
     */
    public function apply(
        ApplyOrganizationRequest $request,
        ApplyOrganizationAction $action
    ): JsonResponse {
        $result = $action->execute(
            $request->validated(),
            $request->user()
        );

        return $this->respondCreated(
            data: [
                'application_id' => $result['application']->id,
                'organization_name' => $result['application']->organization_name,
                'status' => $result['application']->status,
                'auto_approved' => $result['auto_approved'],
                'organization' => $result['organization'] ? [
                    'id' => $result['organization']->id,
                    'slug' => $result['organization']->slug,
                    'company_code' => $result['organization']->company_code,
                ] : null,
            ],
            message: $result['auto_approved']
                ? 'Organization registered and provisioned immediately.'
                : 'Application submitted successfully and is pending administrator review.'
        );
    }

    /**
     * List organization applications (Platform Admin Queue).
     */
    public function applications(Request $request): JsonResponse
    {
        if (! $request->user()?->isAdmin()) {
            return $this->respondWithError('Unauthorized. Administrator access required.', 403);
        }

        $applications = OrganizationApplication::with('applicant:id,name,email')
            ->latest()
            ->paginate(15);

        return $this->respondWithSuccess(
            data: $applications->items(),
            message: 'Organization applications retrieved successfully',
            meta: [
                'current_page' => $applications->currentPage(),
                'total' => $applications->total(),
                'per_page' => $applications->perPage(),
            ]
        );
    }

    /**
     * Review and decide on an organization application.
     */
    public function reviewApplication(
        OrganizationApplication $application,
        ReviewOrganizationApplicationRequest $request,
        ReviewOrganizationApplicationAction $action
    ): JsonResponse {
        $updatedApplication = $action->execute(
            $application,
            $request->validated(),
            $request->user()
        );

        return $this->respondWithSuccess(
            data: [
                'id' => $updatedApplication->id,
                'organization_name' => $updatedApplication->organization_name,
                'status' => $updatedApplication->status,
                'reviewed_at' => $updatedApplication->reviewed_at?->toIso8601String(),
                'review_notes' => $updatedApplication->review_notes,
            ],
            message: "Application marked as {$updatedApplication->status}"
        );
    }
}
