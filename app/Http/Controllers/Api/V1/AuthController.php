<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\LoginApiRequest;
use App\Http\Requests\Api\V1\UpdateProfileApiRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\OperationalNotification;
use App\Models\User;
use App\Notifications\SecurityLoginNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthController extends ApiController
{
    /**
     * Authenticate operator credentials and issue a Sanctum Bearer token.
     */
    public function login(LoginApiRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User|null $user */
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->respondWithError('Invalid email or password provided.', 401);
        }

        $deviceName = $validated['device_name'] ?? $request->userAgent() ?? 'Npontu Mobile Client';
        $token = $user->createToken($deviceName)->plainTextToken;

        logger()->channel('state_changes')->info('api.auth.login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'device' => $deviceName,
            'ip' => $request->ip(),
        ]);

        // Dispatch security notification email for new operator session
        try {
            $user->notify(new SecurityLoginNotification(
                $request->ip() ?? '127.0.0.1',
                now()->toDateTimeString()
            ));
        } catch (\Throwable $e) {
            logger()->warning('Failed to dispatch login email notification', ['error' => $e->getMessage()]);
        }

        // Create in-app operational notification record for audit trail
        try {
            OperationalNotification::create([
                'user_id' => $user->id,
                'title' => 'New Mobile Session Authenticated',
                'message' => "New session initiated on {$deviceName} (IP: {$request->ip()}).",
                'type' => 'system',
                'priority' => 'info',
                'action_route' => '/settings',
                'metadata' => [
                    'device' => $deviceName,
                    'ip' => $request->ip(),
                    'time' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Throwable $e) {
            logger()->warning('Failed to create login operational notification', ['error' => $e->getMessage()]);
        }

        return $this->respondWithSuccess([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Authentication successful.');
    }

    /**
     * Return authenticated user profile and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->respondWithSuccess(
            new UserResource($user),
            'Profile retrieved successfully.'
        );
    }

    /**
     * Update authenticated user profile, operational department/team, and contact info.
     */
    public function updateProfile(UpdateProfileApiRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->update($validated);

        logger()->channel('state_changes')->info('api.auth.profile_updated', [
            'user_id' => $user->id,
            'changes' => array_keys($validated),
            'ip' => $request->ip(),
        ]);

        return $this->respondWithSuccess(
            new UserResource($user->fresh()),
            'Profile updated successfully.'
        );
    }

    /**
     * Revoke current mobile access token (Logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()?->delete();

            logger()->channel('state_changes')->info('api.auth.logout', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
        }

        return $this->respondWithSuccess(
            null,
            'Successfully logged out. Session revoked.'
        );
    }

    /**
     * Revoke all personal access tokens across all devices.
     */
    public function revokeSessions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->tokens()->delete();

        logger()->channel('state_changes')->info('api.auth.revoke_all', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return $this->respondWithSuccess(
            null,
            'All active sessions and tokens have been revoked.'
        );
    }
}
