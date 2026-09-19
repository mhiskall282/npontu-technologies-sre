<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PlatformPlanController extends Controller
{
    /**
     * Display SaaS Commercial Plans & Entitlements Catalog.
     */
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->get();

        return view('admin.platform.plans.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a new commercial plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tier' => 'required|string|in:free,team,enterprise,customer_hosted',
            'price_dollars' => 'required|numeric|min:0',
            'billing_interval' => 'required|string|in:monthly,annual,perpetual',
            'trial_days' => 'required|integer|min:0|max:365',
            'max_workspaces' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $features = [
            'max_workspaces' => (int) $validated['max_workspaces'],
            'max_users' => (int) $validated['max_users'],
            'custom_sla' => $validated['tier'] === 'enterprise',
            'dedicated_deployment' => in_array($validated['tier'], ['enterprise', 'customer_hosted'], true),
            'audit_retention_days' => $validated['tier'] === 'enterprise' ? 365 : 30,
        ];

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'tier' => $validated['tier'],
            'price_cents' => (int) round($validated['price_dollars'] * 100),
            'billing_interval' => $validated['billing_interval'],
            'trial_days' => (int) $validated['trial_days'],
            'features' => $features,
            'is_active' => true,
        ]);

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => Plan::class,
            'subject_id' => $plan->id,
            'event' => 'plan_created',
            'new_values' => $plan->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.platform.plans.index')
            ->with('success', "Plan '{$plan->name}' created successfully.");
    }

    /**
     * Update an existing plan's price and entitlements.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price_dollars' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0|max:365',
            'max_workspaces' => 'required|integer|min:1',
            'max_users' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $features = $plan->features ?? [];
        $features['max_workspaces'] = (int) $validated['max_workspaces'];
        $features['max_users'] = (int) $validated['max_users'];

        $plan->update([
            'name' => $validated['name'],
            'price_cents' => (int) round($validated['price_dollars'] * 100),
            'trial_days' => (int) $validated['trial_days'],
            'is_active' => (bool) $validated['is_active'],
            'features' => $features,
        ]);

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => Plan::class,
            'subject_id' => $plan->id,
            'event' => 'plan_updated',
            'new_values' => $plan->toArray(),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.platform.plans.index')
            ->with('success', "Plan '{$plan->name}' updated successfully.");
    }
}
