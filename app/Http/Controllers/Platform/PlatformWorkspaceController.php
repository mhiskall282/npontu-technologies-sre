<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformWorkspaceController extends Controller
{
    /**
     * Display a paginated listing of all operational workspaces across tenants.
     */
    public function index(Request $request): View
    {
        return TenantContext::withoutTenancy(function () use ($request): View {
            $query = Workspace::with(['organization', 'owner'])
                ->withCount(['members', 'activities'])
                ->latest();

            if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            if ($type = $request->input('type')) {
                if ($type === 'personal') {
                    $query->where('is_personal', true);
                } elseif ($type === 'organization') {
                    $query->where('is_personal', false);
                }
            }

            $workspaces = $query->paginate(20)->withQueryString();

            return view('admin.platform.workspaces.index', [
                'workspaces' => $workspaces,
                'filters' => $request->only(['search', 'type']),
            ]);
        });
    }

    /**
     * Display detailed operational profile of a workspace.
     */
    public function show(int $id): View
    {
        return TenantContext::withoutTenancy(function () use ($id): View {
            $workspace = Workspace::with([
                'organization',
                'owner',
                'members',
                'activities' => fn ($q) => $q->latest('activity_date')->limit(15),
            ])->findOrFail($id);

            return view('admin.platform.workspaces.show', [
                'workspace' => $workspace,
            ]);
        });
    }
}
