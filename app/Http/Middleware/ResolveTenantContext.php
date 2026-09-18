<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResolveTenantContext Middleware.
 *
 * Resolves the active Workspace and Organization tenant boundary for incoming requests.
 * Validates membership, status, and sets the request-scoped TenantContext.
 */
class ResolveTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Identify requested workspace from headers, route/query parameters, or session
        $workspaceIdentifier = $request->header('X-Workspace-Id')
            ?? $request->header('X-Workspace-Uuid')
            ?? $request->route('workspace')
            ?? $request->query('workspace_id')
            ?? $request->query('workspace')
            ?? ($request->hasSession() ? $request->session()->get('opsora_workspace_id') : null);

        $workspace = null;

        if ($workspaceIdentifier) {
            $workspace = Workspace::withoutGlobalScopes()
                ->where('id', $workspaceIdentifier)
                ->orWhere('uuid', $workspaceIdentifier)
                ->orWhere('slug', $workspaceIdentifier)
                ->first();

            if (! $workspace) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'Workspace not found',
                        'message' => 'The requested operational workspace could not be identified.',
                    ], 404);
                }
                abort(404, 'The requested operational workspace could not be identified.');
            }
        } elseif ($user) {
            // Default to user's current or primary active workspace
            $workspace = $user->currentWorkspace();
        }

        // 2. If a workspace is resolved, enforce tenant boundary validations
        if ($workspace) {
            // Verify workspace active status
            if ($workspace->status !== 'active') {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'Workspace Suspended',
                        'message' => 'This workspace is currently suspended or archived.',
                    ], 403);
                }
                abort(403, 'This workspace is currently suspended or archived.');
            }

            // Verify parent organization status
            if ($workspace->organization && $workspace->organization->status === 'suspended') {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'Organization Suspended',
                        'message' => 'The parent organization has been suspended by platform administration.',
                    ], 403);
                }
                abort(403, 'The parent organization has been suspended by platform administration.');
            }

            // If user is authenticated, verify workspace membership
            if ($user && ! $workspace->hasUser($user) && ! $user->isAdmin()) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'error' => 'Unauthorized Workspace Access',
                        'message' => 'You do not hold an active membership in this workspace.',
                    ], 403);
                }
                abort(403, 'You do not hold an active membership in this workspace.');
            }

            // Bind to TenantContext singleton
            TenantContext::setWorkspace($workspace);
            TenantContext::setOrganization($workspace->organization);

            // Persist to session for subsequent browser interactions
            if ($request->hasSession()) {
                $request->session()->put('opsora_workspace_id', $workspace->id);
            }
        }

        try {
            return $next($request);
        } finally {
            // Ensure clean context between requests
            TenantContext::clear();
        }
    }
}
