<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PrivilegeController extends Controller
{
    /**
     * Retrieve the complete catalog of granular user privileges.
     */
    public function index(): JsonResponse
    {
        $catalog = User::ALL_PRIVILEGES;

        $categories = [];
        foreach ($catalog as $key => $details) {
            $cat = $details['category'] ?? 'General';
            $categories[$cat][] = [
                'key' => $key,
                'label' => $details['label'],
                'description' => $details['description'],
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Privileges catalog retrieved successfully',
            'data' => [
                'catalog' => $catalog,
                'categories' => $categories,
                'total' => count($catalog),
            ],
        ]);
    }

    /**
     * Update a user's granular operational privileges.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        // Target user can be looked up across tenants if platform admin, or within current tenant
        $targetUser = TenantContext::withoutTenancy(fn () => User::findOrFail($id));

        // Authorization check: Must be Platform Admin or Workspace/Organization Admin
        $isAuthorized = $currentUser->isPlatformAdmin() || ($currentUser->isAdmin() && $currentUser->id !== $targetUser->id);

        if (! $isAuthorized) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage granular privileges for this user.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'privileges' => ['present', 'array'],
            'privileges.*' => ['string', 'in:'.implode(',', array_keys(User::ALL_PRIVILEGES))],
        ]);

        $oldPrivileges = $targetUser->privileges ?? [];
        $newPrivileges = array_values(array_unique($validated['privileges']));

        $targetUser->privileges = $newPrivileges;
        $targetUser->save();

        // Record immutable audit trail
        AuditLog::create([
            'actor_id' => $currentUser->id,
            'actor_name' => $currentUser->name,
            'actor_role' => $currentUser->role,
            'actor_ip' => $request->ip() ?? '127.0.0.1',
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'event' => 'user_privileges_updated',
            'old_values' => ['privileges' => $oldPrivileges],
            'new_values' => ['privileges' => $newPrivileges],
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Privileges for '{$targetUser->name}' updated successfully",
            'data' => [
                'user' => new UserResource($targetUser),
                'privileges' => $newPrivileges,
            ],
        ]);
    }
}
