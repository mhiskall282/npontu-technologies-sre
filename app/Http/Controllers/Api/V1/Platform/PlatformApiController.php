<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Platform;

use App\Actions\Platform\ReactivateOrganizationAction;
use App\Actions\Platform\ReactivateUserAction;
use App\Actions\Platform\SuspendOrganizationAction;
use App\Actions\Platform\SuspendUserAction;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\SecurityEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use App\Services\PlatformMetricsService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PlatformApiController extends ApiController
{
    /**
     * Get platform overview metrics and health telemetry.
     */
    public function dashboard(PlatformMetricsService $metricsService): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($metricsService): JsonResponse {
            $metrics = $metricsService->getDashboardMetrics();
            $health = $metricsService->getSystemHealth();

            return $this->respondWithSuccess([
                'metrics' => $metrics,
                'health' => $health,
            ], 'Platform control plane telemetry retrieved');
        });
    }

    /**
     * List all multi-tenant organizations.
     */
    public function organizations(Request $request): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request): JsonResponse {
            $query = Organization::withCount(['workspaces', 'users'])->latest();

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

            $orgs = $query->paginate(25);

            return $this->respondWithSuccess($orgs, 'Organizations retrieved successfully');
        });
    }

    /**
     * Show single organization profile.
     */
    public function organizationShow(int $id): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($id): JsonResponse {
            $org = Organization::with([
                'workspaces',
                'users',
                'subscriptions.plan',
            ])->findOrFail($id);

            return $this->respondWithSuccess($org, 'Organization retrieved successfully');
        });
    }

    /**
     * Suspend an organization.
     */
    public function suspendOrganization(Request $request, int $id, SuspendOrganizationAction $action): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): JsonResponse {
            $org = Organization::findOrFail($id);

            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            $action->execute($org, $validated['reason'], $request->user());

            return $this->respondWithSuccess([
                'id' => $org->id,
                'status' => 'suspended',
            ], "Organization '{$org->name}' suspended successfully");
        });
    }

    /**
     * Reactivate a suspended organization.
     */
    public function reactivateOrganization(Request $request, int $id, ReactivateOrganizationAction $action): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): JsonResponse {
            $org = Organization::findOrFail($id);

            $action->execute($org, $request->user());

            return $this->respondWithSuccess([
                'id' => $org->id,
                'status' => 'active',
            ], "Organization '{$org->name}' reactivated successfully");
        });
    }

    /**
     * List all platform operators and users.
     */
    public function users(Request $request): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request): JsonResponse {
            $query = User::with(['organizations'])->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($status = $request->input('status')) {
                if ($status === 'suspended') {
                    $query->whereNotNull('suspended_at');
                } elseif ($status === 'active') {
                    $query->whereNull('suspended_at');
                }
            }

            $users = $query->paginate(25);

            return $this->respondWithSuccess($users, 'Users retrieved successfully');
        });
    }

    /**
     * Show detailed user profile.
     */
    public function userShow(int $id): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($id): JsonResponse {
            $user = User::with(['organizations', 'workspaces'])->findOrFail($id);

            return $this->respondWithSuccess($user, 'User retrieved successfully');
        });
    }

    /**
     * Suspend a user account.
     */
    public function suspendUser(Request $request, int $id, SuspendUserAction $action): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): JsonResponse {
            $user = User::findOrFail($id);

            if ($user->id === $request->user()->id) {
                return $this->respondWithError('Cannot suspend your own administrator account', 422);
            }

            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            $action->execute($user, $validated['reason'], $request->user());

            return $this->respondWithSuccess([
                'id' => $user->id,
                'suspended_at' => $user->suspended_at,
            ], "User '{$user->name}' suspended successfully");
        });
    }

    /**
     * Reactivate a suspended user account.
     */
    public function reactivateUser(Request $request, int $id, ReactivateUserAction $action): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): JsonResponse {
            $user = User::findOrFail($id);

            $action->execute($user, $request->user());

            return $this->respondWithSuccess([
                'id' => $user->id,
                'status' => 'active',
            ], "User '{$user->name}' reactivated successfully");
        });
    }

    /**
     * List all operational workspaces.
     */
    public function workspaces(Request $request): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request): JsonResponse {
            $query = Workspace::with(['organization', 'owner'])->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            $workspaces = $query->paginate(25);

            return $this->respondWithSuccess($workspaces, 'Workspaces retrieved successfully');
        });
    }

    /**
     * List all commercial SaaS plans.
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::withCount('subscriptions')->get();

        return $this->respondWithSuccess($plans, 'Plans retrieved successfully');
    }

    /**
     * List all active subscriptions.
     */
    public function subscriptions(): JsonResponse
    {
        return TenantContext::withoutTenancy(function (): JsonResponse {
            $subs = Subscription::with(['organization', 'plan'])->latest()->paginate(25);

            return $this->respondWithSuccess($subs, 'Subscriptions retrieved successfully');
        });
    }

    /**
     * List all platform feature flags.
     */
    public function featureFlags(): JsonResponse
    {
        $flags = FeatureFlag::latest()->get();

        return $this->respondWithSuccess($flags, 'Feature flags retrieved successfully');
    }

    /**
     * Get system health diagnostics.
     */
    public function health(PlatformMetricsService $metricsService): JsonResponse
    {
        $health = $metricsService->getSystemHealth();

        return $this->respondWithSuccess($health, 'Platform health diagnostics retrieved');
    }

    /**
     * List recent security events.
     */
    public function securityEvents(): JsonResponse
    {
        $events = SecurityEvent::latest('created_at')->limit(50)->get();

        return $this->respondWithSuccess($events, 'Security events retrieved');
    }

    /**
     * List immutable audit logs.
     */
    public function auditLogs(): JsonResponse
    {
        return TenantContext::withoutTenancy(function (): JsonResponse {
            $logs = AuditLog::latest('created_at')->paginate(50);

            return $this->respondWithSuccess($logs, 'Audit logs retrieved');
        });
    }

    /**
     * Get the full catalog of available granular user privileges.
     */
    public function privileges(): JsonResponse
    {
        return $this->respondWithSuccess([
            'catalog' => User::ALL_PRIVILEGES,
            'total' => count(User::ALL_PRIVILEGES),
        ], 'Privileges catalog retrieved successfully');
    }

    /**
     * Update a user's granular privileges across the platform fleet.
     */
    public function updateUserPrivileges(Request $request, int $id): JsonResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): JsonResponse {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'privileges' => ['present', 'array'],
                'privileges.*' => ['string', 'in:'.implode(',', array_keys(User::ALL_PRIVILEGES))],
            ]);

            $oldPrivileges = $user->privileges ?? [];
            $newPrivileges = array_values(array_unique($validated['privileges']));

            $user->privileges = $newPrivileges;
            $user->save();

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'actor_name' => $request->user()->name,
                'actor_role' => $request->user()->role,
                'actor_ip' => $request->ip() ?? '127.0.0.1',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'event' => 'user_privileges_updated',
                'old_values' => ['privileges' => $oldPrivileges],
                'new_values' => ['privileges' => $newPrivileges],
                'created_at' => now(),
            ]);

            return $this->respondWithSuccess([
                'id' => $user->id,
                'name' => $user->name,
                'privileges' => $user->privileges,
            ], "Privileges for '{$user->name}' updated successfully");
        });
    }
}
