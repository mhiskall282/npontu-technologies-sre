<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\SecurityEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isPlatformAdmin()) {
            // Log security warning for unauthorized access attempt into control plane
            if ($user) {
                SecurityEvent::record(
                    eventType: 'unauthorized_platform_access',
                    severity: 'warning',
                    actor: $user,
                    details: [
                        'requested_path' => $request->path(),
                        'method' => $request->method(),
                    ]
                );
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden. Platform Administrator credentials required.',
                    'error' => 'platform_admin_required',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, 'Forbidden. Platform Administrator credentials required.');
        }

        return $next($request);
    }
}
