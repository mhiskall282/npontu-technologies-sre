<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Actions\Platform\UpdateSubscriptionAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformSubscriptionController extends Controller
{
    /**
     * Display a listing of all tenant commercial subscriptions.
     */
    public function index(Request $request): View
    {
        return TenantContext::withoutTenancy(function () use ($request): View {
            $query = Subscription::with(['organization', 'plan'])->latest();

            if ($status = $request->input('status')) {
                $query->where('status', $status);
            }

            if ($planId = $request->input('plan_id')) {
                $query->where('plan_id', $planId);
            }

            $subscriptions = $query->paginate(20)->withQueryString();
            $plans = Plan::where('is_active', true)->get();

            return view('admin.platform.subscriptions.index', [
                'subscriptions' => $subscriptions,
                'plans' => $plans,
                'filters' => $request->only(['status', 'plan_id']),
            ]);
        });
    }

    /**
     * Update an organization's subscription plan and billing status.
     */
    public function update(Request $request, int $id, UpdateSubscriptionAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id, $action): RedirectResponse {
            $subscription = Subscription::findOrFail($id);
            $organization = $subscription->organization;

            $validated = $request->validate([
                'plan_id' => 'required|exists:saas_plans,id',
                'status' => 'required|string|in:active,trialing,past_due,canceled,paused',
            ]);

            $plan = Plan::findOrFail($validated['plan_id']);
            $action->execute($organization, $plan, $validated['status'], $request->user());

            return redirect()->back()->with('success', "Subscription for '{$organization->name}' updated to {$plan->name} ({$validated['status']}).");
        });
    }

    /**
     * Attach a new subscription to an organization.
     */
    public function store(Request $request, UpdateSubscriptionAction $action): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $action): RedirectResponse {
            $validated = $request->validate([
                'organization_id' => 'required|exists:organizations,id',
                'plan_id' => 'required|exists:saas_plans,id',
                'status' => 'required|string|in:active,trialing,past_due,canceled,paused',
            ]);

            $organization = Organization::findOrFail($validated['organization_id']);
            $plan = Plan::findOrFail($validated['plan_id']);

            $action->execute($organization, $plan, $validated['status'], $request->user());

            return redirect()->back()->with('success', "Provisioned {$plan->name} subscription for {$organization->name}.");
        });
    }

    /**
     * Update an individual subscription's status directly.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        return TenantContext::withoutTenancy(function () use ($request, $id): RedirectResponse {
            $subscription = Subscription::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|string|in:active,trialing,past_due,canceled,paused',
            ]);

            $oldStatus = $subscription->status;
            $subscription->status = $validated['status'];
            $subscription->save();

            AuditLog::create([
                'actor_id' => $request->user()->id,
                'actor_name' => $request->user()->name,
                'subject_type' => Subscription::class,
                'subject_id' => $subscription->id,
                'event' => 'subscription_status_updated',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $subscription->status],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'created_at' => now(),
            ]);

            return redirect()->back()->with('success', "Subscription status updated to {$subscription->status}.");
        });
    }
}
