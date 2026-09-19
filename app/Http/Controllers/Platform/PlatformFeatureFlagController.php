<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\ToggleFeatureFlagAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FeatureFlag;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PlatformFeatureFlagController extends Controller
{
    /**
     * Display a listing of all platform feature flags.
     */
    public function index(): View
    {
        $flags = FeatureFlag::latest()->get();
        $organizations = Organization::where('status', 'active')->select('id', 'name')->get();

        return view('admin.platform.features.index', [
            'flags' => $flags,
            'organizations' => $organizations,
        ]);
    }

    /**
     * Create a new platform feature flag.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'key' => 'required|string|max:100|unique:feature_flags,key',
            'description' => 'nullable|string|max:255',
            'is_enabled' => 'required|boolean',
            'target_tiers' => 'nullable|array',
            'target_tiers.*' => 'string|in:free,team,enterprise,customer_hosted',
        ]);

        $flag = FeatureFlag::create([
            'name' => $validated['name'],
            'key' => Str::slug($validated['key'], '_'),
            'description' => $validated['description'] ?? null,
            'is_enabled' => (bool) $validated['is_enabled'],
            'target_tiers' => $validated['target_tiers'] ?? null,
            'target_org_ids' => null,
            'rules' => null,
        ]);

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => FeatureFlag::class,
            'subject_id' => $flag->id,
            'event' => 'feature_flag_created',
            'new_values' => $flag->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.platform.features.index')
            ->with('success', "Feature flag '{$flag->name}' created successfully.");
    }

    /**
     * Toggle or update targeting for a feature flag.
     */
    public function toggle(Request $request, int $id, ToggleFeatureFlagAction $action): RedirectResponse
    {
        $flag = FeatureFlag::findOrFail($id);

        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'target_tiers' => 'nullable|array',
            'target_tiers.*' => 'string|in:free,team,enterprise,customer_hosted',
        ]);

        $newStatus = isset($validated['is_enabled']) ? (bool) $validated['is_enabled'] : ! $flag->is_enabled;

        $action->execute(
            $flag,
            $newStatus,
            $request->user(),
            $validated['target_tiers'] ?? null
        );

        return redirect()->route('admin.platform.features.index')
            ->with('success', "Feature flag '{$flag->name}' updated successfully.");
    }
}
