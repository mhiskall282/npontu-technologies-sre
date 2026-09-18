<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Saas\CreateWorkspaceAction;
use App\Actions\Saas\JoinOrganizationByCodeAction;
use App\Http\Requests\Api\V1\CreateWorkspaceRequest;
use App\Http\Requests\Api\V1\JoinOrganizationByCodeRequest;
use App\Http\Requests\Api\V1\SwitchWorkspaceRequest;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkspaceController extends Controller
{
    /**
     * Display all accessible workspaces and fast company-code onboarding.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->with('organization')
            ->get();

        $activeWorkspace = TenantContext::getWorkspace() ?? $user->currentWorkspace();

        return view('workspaces.index', [
            'workspaces' => $workspaces,
            'activeWorkspace' => $activeWorkspace,
            'personalWorkspace' => $user->personalWorkspace(),
        ]);
    }

    /**
     * Provision a new workspace from the web interface.
     */
    public function store(CreateWorkspaceRequest $request, CreateWorkspaceAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $workspace = $action->execute($request->validated(), $user);

        // Auto-switch to the newly provisioned workspace
        $request->session()->put('opsora_workspace_id', $workspace->id);
        TenantContext::setWorkspace($workspace);

        return redirect()->route('activities.daily')
            ->with('status', "Workspace '{$workspace->name}' provisioned and set as active.");
    }

    /**
     * Switch active workspace in web session.
     */
    public function switch(SwitchWorkspaceRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $targetId = $request->validated('workspace_id');

        $workspace = Workspace::where('id', $targetId)
            ->orWhere('uuid', $targetId)
            ->firstOrFail();

        if (! $workspace->isActive()) {
            return back()->with('error', 'This workspace is currently suspended or archived.');
        }

        if (! $workspace->hasUser($user) && ! $user->isAdmin()) {
            abort(403, 'Unauthorized. You are not a member of this workspace.');
        }

        $request->session()->put('opsora_workspace_id', $workspace->id);
        TenantContext::setWorkspace($workspace);

        return redirect()->route('activities.daily')
            ->with('status', "Active workspace switched to '{$workspace->name}'.");
    }

    /**
     * Join an organization via company code.
     */
    public function join(JoinOrganizationByCodeRequest $request, JoinOrganizationByCodeAction $action): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $action->execute($request->validated('company_code'), $user);

        if ($result['workspace']) {
            $request->session()->put('opsora_workspace_id', $result['workspace']->id);
            TenantContext::setWorkspace($result['workspace']);
        }

        return redirect()->route('activities.daily')
            ->with('status', "Joined {$result['organization']->name} successfully!");
    }
}
