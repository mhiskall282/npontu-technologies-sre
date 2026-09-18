<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Saas\ApplyOrganizationAction;
use App\Actions\Saas\ReviewOrganizationApplicationAction;
use App\Http\Requests\Api\V1\ApplyOrganizationRequest;
use App\Http\Requests\Api\V1\ReviewOrganizationApplicationRequest;
use App\Models\OrganizationApplication;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class OrganizationApplicationController extends Controller
{
    /**
     * Show the self-service organization registration form.
     */
    public function create(): View
    {
        return view('organizations.apply');
    }

    /**
     * Handle organization registration application submission.
     */
    public function store(ApplyOrganizationRequest $request, ApplyOrganizationAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $action->execute($request->validated(), $user);

        if ($result['auto_approved'] && $result['organization']) {
            $primaryWs = $result['organization']->workspaces()->first();
            if ($primaryWs) {
                $request->session()->put('opsora_workspace_id', $primaryWs->id);
                TenantContext::setWorkspace($primaryWs);
            }

            return redirect()->route('activities.daily')
                ->with('status', "Organization '{$result['organization']->name}' registered and provisioned immediately.");
        }

        return redirect()->route('workspaces.index')
            ->with('status', 'Your organization registration has been submitted and placed in the review queue.');
    }

    /**
     * Admin Queue: View all organization applications.
     */
    public function index(Request $request): View
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Unauthorized. Platform Administrator privilege required.');
        }

        $applications = OrganizationApplication::with('applicant', 'reviewer')
            ->latest()
            ->paginate(15);

        return view('admin.organizations.applications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Admin Action: Review and decide on an application.
     */
    public function review(
        OrganizationApplication $application,
        ReviewOrganizationApplicationRequest $request,
        ReviewOrganizationApplicationAction $action
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $action->execute($application, $request->validated(), $user);

        return back()->with('status', "Application for '{$application->organization_name}' has been processed.");
    }
}
