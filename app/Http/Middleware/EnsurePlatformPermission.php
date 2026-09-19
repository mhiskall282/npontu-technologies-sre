<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SecurityEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  $permission  Required platform permission (e.g. 'platform.organizations.suspend')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPlatformPermission($permission)) {
            if ($user) {
                SecurityEvent::record(
                    eventType: 'privilege_escalation_attempt',
                    severity: 'critical',
                    actor: $user,
                    details: [
                        'required_permission' => $permission,
                        'user_platform_role' => $user->platform_role,
                        'requested_path' => $request->path(),
                        'method' => $request->method(),
                    ]
                );
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => "Forbidden. You do not hold the required platform permission: {$permission}.",
                    'error' => 'insufficient_platform_privilege',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, "Forbidden. You do not hold the required platform permission: {$permission}.");
        }

        return $next($request);
    }
}
