<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\ReactivateOrganizationAction;
use App\Actions\Platform\SuspendOrganizationAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Plan;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformOrganizationController extends Controller
{
    /**
     * Display a paginated listing of all tenant organizations.
     */
    public function index(Request $request): View
    {
        return TenantContext::withoutTenancy(function () use ($request): View {
            $query = Organization::withCount(['workspaces', 'members'])->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('company_code', 'like', "%{$search}%");
                });
            }

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($tier = $request->input('tier')) {
                $query->where('tier', $tier);
            }

            $organizations = $query->paginate(15)->withQueryString();

            return view('admin.platform.organizations.index', [
                'organizations' => $organizations,
                'filters' => $request->only(['search', 'status', 'tier']),
            ]);
        });
    }

    /**
     * Display comprehensive tabbed profile of a tenant organization.
     */
    public function show(int $id): View
    {
        return TenantContext::withoutTenancy(function () use ($id): View {
            $organization = Organization::with([
                'workspaces.owner',
                'members',
                'subscriptions.plan',
                'memberships.user',
            ])->findOrFail($id);

            $auditLogs = AuditLog::where('subject_type', Organization::class)
                ->where('subject_id', $organization->id)
                ->latest('created_at')
                ->limit(25)
                ->get();

            $availablePlans = Plan::where('is_active', true)->get();

            return view('admin.platform.organizations.show', [
                'organization' => $organization,
                'auditLogs' => $auditLogs,
                'availablePlans' => $availablePlans,
            ]);
        });
    }

    /**
     * Update organization platform attributes (tier, deployment model, region).
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): RedirectResponse {
            $organization = Organization::findOrFail($id);

            $validated = $request->validate([
                'tier' => 'required|string|in:free,team,enterprise,customer_hosted',
                'deployment_model' => 'required|string|in:shared_saas,dedicated_managed,customer_funded,customer_hosted',
                'preferred_region' => 'required|string|in:af-south,us-east,eu-west',
            ]);

            $organization->update($validated);

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'actor_name' => $request->user()->name,
                'subject_type' => Organization::class,
                'subject_id' => $organization->id,
                'event' => 'organization_profile_updated',
                'new_values' => $validated,
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return redirect()->route('admin.platform.organizations.show', $organization)
                ->with('success', "Organization '{$organization->name}' configuration updated successfully.");
        });
    }

    /**
     * Administratively suspend an organization.
     */
    public function suspend(Request $request, int $id, SuspendOrganizationAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): RedirectResponse {
            $organization = Organization::findOrFail($id);

            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            $action->execute($organization, $validated['reason'], $request->user());

            return redirect()->back()->with('warning', "Organization '{$organization->name}' has been suspended.");
        });
    }

    /**
     * Administratively reactivate a suspended organization.
     */
    public function reactivate(Request $request, int $id, ReactivateOrganizationAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): RedirectResponse {
            $organization = Organization::findOrFail($id);

            $action->execute($organization, $request->user());

            return redirect()->back()->with('success', "Organization '{$organization->name}' has been reactivated to active status.");
        });
    }
}
