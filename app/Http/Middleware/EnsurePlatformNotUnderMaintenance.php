<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePlatformNotUnderMaintenance
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Cache::get('opsora_platform_settings', []);
        $isMaintenance = (bool) ($settings['maintenance_mode'] ?? false);

        if (! $isMaintenance) {
            return $next($request);
        }

        // Exempt critical system routes, platform admin control plane, and auth recovery
        if ($request->is('admin/platform*', 'login', 'logout', 'up', 'health*', 'health')) {
            return $next($request);
        }

        // Exempt authenticated Platform Administrators
        if ($request->user() && $request->user()->isPlatformAdmin()) {
            return $next($request);
        }

        // Check for bypass secret key via query string or cookie
        $bypassKey = $settings['maintenance_bypass_key'] ?? 'sre-opsora-emergency-bypass';
        if ($request->query('bypass_key') === $bypassKey || $request->cookie('opsora_maintenance_bypass') === $bypassKey) {
            $response = $next($request);
            if ($request->query('bypass_key') === $bypassKey) {
                $response->withCookie(cookie()->make('opsora_maintenance_bypass', $bypassKey, 120));
            }

            return $response;
        }

        // Return structured JSON for API callers & Flutter mobile app
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Platform Maintenance Active',
                'message' => $settings['maintenance_message'] ?? 'Opsora SRE is currently undergoing scheduled platform maintenance. Services will resume shortly.',
                'estimated_restoration' => $settings['maintenance_ends_at'] ?? null,
                'support_contact' => $settings['support_email'] ?? 'support@npontu.com',
                'status' => 503,
            ], 503);
        }

        // Return branded HTML error view for web clients
        return response()->view('errors.maintenance', [
            'settings' => $settings,
        ], 503);
    }
}
