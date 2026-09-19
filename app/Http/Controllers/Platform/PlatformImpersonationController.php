<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PlatformImpersonationController extends Controller
{
    /**
     * Start a secure support impersonation session for a tenant operator.
     */
    public function impersonateUser(Request $request, int $userId): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin || ! $admin->isPlatformAdmin()) {
            abort(403, 'Unauthorized. Platform administration privilege required for tenant impersonation.');
        }

        $targetUser = User::findOrFail($userId);

        if ($targetUser->isPlatformAdmin()) {
            return redirect()->back()->with('error', 'Security Violation: Cannot impersonate another Platform Administrator.');
        }

        $reason = (string) $request->input('reason', 'Customer support diagnosis and troubleshooting.');

        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'event' => 'tenant_impersonation_started',
            'new_values' => [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'reason' => $reason,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        $request->session()->put('opsora_impersonator_id', $admin->id);
        $request->session()->put('opsora_impersonator_name', $admin->name);

        Auth::login($targetUser);

        return redirect()->route('activities.daily')
            ->with('warning', "Support session active: Now operating as {$targetUser->name} ({$targetUser->email}). All actions are audited.");
    }

    /**
     * Start a support impersonation session assuming the context of a tenant organization.
     */
    public function impersonateOrganization(Request $request, int $organizationId): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin || ! $admin->isPlatformAdmin()) {
            abort(403, 'Unauthorized. Platform administration privilege required for tenant impersonation.');
        }

        $organization = Organization::with(['members', 'workspaces.owner'])->findOrFail($organizationId);

        // Resolve primary tenant operator (owner or active member)
        $targetUser = $organization->members->first()
            ?? $organization->workspaces->first()?->owner
            ?? User::where('id', $organization->workspaces->first()?->owner_id)->first();

        if (! $targetUser) {
            return redirect()->back()->with('error', "No member account found in organization '{$organization->name}' to impersonate.");
        }

        return $this->impersonateUser($request, $targetUser->id);
    }

    /**
     * Conclude an active support impersonation session and restore administrator context.
     */
    public function exit(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('opsora_impersonator_id');

        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::findOrFail($adminId);
        $currentUser = $request->user();

        AuditLog::create([
            'actor_id' => $admin->id,
            'actor_name' => $admin->name,
            'subject_type' => User::class,
            'subject_id' => $currentUser?->id ?? 0,
            'event' => 'tenant_impersonation_ended',
            'old_values' => [
                'admin_id' => $admin->id,
                'impersonated_user_id' => $currentUser?->id,
                'impersonated_user_email' => $currentUser?->email,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        $request->session()->forget(['opsora_impersonator_id', 'opsora_impersonator_name']);

        Auth::login($admin);

        return redirect()->route('admin.platform.organizations.index')
            ->with('success', 'Support impersonation session concluded. Restored to Platform Administration Control Plane.');
    }
}
