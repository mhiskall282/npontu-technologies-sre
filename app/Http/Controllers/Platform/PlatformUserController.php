<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\ReactivateUserAction;
use App\Actions\Platform\SuspendUserAction;
use App\Enums\PlatformRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformUserController extends Controller
{
    /**
     * Display a listing of all platform operators and users.
     */
    public function index(Request $request): View
    {
        return TenantContext::withoutTenancy(function () use ($request): View {
            $query = User::with(['organizations', 'workspaces'])->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%");
                });
            }

            if ($role = $request->input('role')) {
                $query->where('role', $role);
            }

            if ($platformRole = $request->input('platform_role')) {
                if ($platformRole === 'none') {
                    $query->whereNull('platform_role');
                } else {
                    $query->where('platform_role', $platformRole);
                }
            }

            if ($status = $request->input('status')) {
                if ($status === 'suspended') {
                    $query->whereNotNull('suspended_at');
                } elseif ($status === 'active') {
                    $query->whereNull('suspended_at');
                }
            }

            $users = $query->paginate(20)->withQueryString();
            $platformRoles = PlatformRole::cases();

            return view('admin.platform.users.index', [
                'users' => $users,
                'platformRoles' => $platformRoles,
                'filters' => $request->only(['search', 'role', 'platform_role', 'status']),
            ]);
        });
    }

    /**
     * Display detailed profile and security status of a user.
     */
    public function show(int $id): View
    {
        return TenantContext::withoutTenancy(function () use ($id): View {
            $user = User::with([
                'organizations',
                'workspaces',
                'tokens',
                'securityEvents' => fn ($q) => $q->latest('created_at')->limit(20),
            ])->findOrFail($id);

            $platformRoles = PlatformRole::cases();

            return view('admin.platform.users.show', [
                'user' => $user,
                'platformRoles' => $platformRoles,
                'allPrivileges' => User::ALL_PRIVILEGES,
            ]);
        });
    }

    /**
     * Update user platform administration role.
     */
    public function updateRole(Request $request, int $id): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): RedirectResponse {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'platform_role' => 'nullable|string|in:'.implode(',', array_column(PlatformRole::cases(), 'value')),
            ]);

            $oldRole = $user->platform_role;
            $user->platform_role = $validated['platform_role'] ?: null;
            $user->save();

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'actor_name' => $request->user()->name,
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'event' => 'platform_role_changed',
                'old_values' => ['platform_role' => $oldRole],
                'new_values' => ['platform_role' => $user->platform_role],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            SecurityEvent::record(
                eventType: 'role_changed',
                severity: 'warning',
                actor: $request->user(),
                details: [
                    'target_user_id' => $user->id,
                    'old_platform_role' => $oldRole,
                    'new_platform_role' => $user->platform_role,
                ]
            );

            return redirect()->back()->with('success', "Platform role for '{$user->name}' updated successfully.");
        });
    }

    /**
     * Administratively suspend a user account.
     */
    public function suspend(Request $request, int $id, SuspendUserAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): RedirectResponse {
            $user = User::findOrFail($id);

            // Prevent self-suspension
            if ($user->id === $request->user()->id) {
                return redirect()->back()->with('error', 'You cannot suspend your own platform administrator account.');
            }

            $validated = $request->validate([
                'reason' => 'required|string|min:5|max:500',
            ]);

            $action->execute($user, $validated['reason'], $request->user());

            return redirect()->back()->with('warning', "User '{$user->name}' has been suspended and all active sessions revoked.");
        });
    }

    /**
     * Administratively reactivate a suspended user account.
     */
    public function reactivate(Request $request, int $id, ReactivateUserAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): RedirectResponse {
            $user = User::findOrFail($id);

            $action->execute($user, $request->user());

            return redirect()->back()->with('success', "User '{$user->name}' has been reactivated.");
        });
    }

    /**
     * Revoke all active API and personal access tokens for a user.
     */
    public function revokeTokens(Request $request, int $id): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): RedirectResponse {
            $user = User::findOrFail($id);
            $tokenCount = $user->tokens()->count();
            $user->tokens()->delete();

            SecurityEvent::record(
                eventType: 'token_revoked',
                severity: 'warning',
                actor: $request->user(),
                details: [
                    'target_user_id' => $user->id,
                    'revoked_count' => $tokenCount,
                ]
            );

            return redirect()->back()->with('success', "Revoked {$tokenCount} active token(s) for user '{$user->name}'.");
        });
    }

    /**
     * Update granular operational privileges for a user.
     */
    public function updatePrivileges(Request $request, int $id): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): RedirectResponse {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'privileges' => 'nullable|array',
                'privileges.*' => 'string|in:'.implode(',', array_keys(User::ALL_PRIVILEGES)),
            ]);

            $oldPrivileges = $user->privileges ?? [];
            $newPrivileges = array_values(array_unique($validated['privileges'] ?? []));

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

            SecurityEvent::record(
                eventType: 'privileges_updated',
                severity: 'info',
                actor: $request->user(),
                details: [
                    'target_user_id' => $user->id,
                    'target_user_email' => $user->email,
                    'privileges_count' => count($newPrivileges),
                ]
            );

            return redirect()->back()->with('success', "Granular privileges for '{$user->name}' updated successfully.");
        });
    }
}
