@extends('layouts.app')

@section('title', 'SaaS Commercial Plans — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Commercial</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Plans &amp; Entitlements Catalog
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Configure commercial subscription tiers, feature entitlement limits, pricing models, and evaluation trial periods.
            </p>
        </div>
    </div>

    {{-- Plan Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                     {{ $plan->tier === 'enterprise' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518]' : ($plan->tier === 'team' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300') }}">
                            {{ $plan->tier }}
                        </span>
                        <span class="text-xs font-mono text-gray-400">
                            {{ $plan->subscriptions_count }} Active Subscriber(s)
                        </span>
                    </div>

                    <h3 class="text-xl font-black text-gray-900 dark:text-white">
                        {{ $plan->name }}
                    </h3>
                    <div class="text-2xl font-black text-[#1B6B3A] dark:text-emerald-400 font-mono mt-1">
                        {{ $plan->priceFormatted() }}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        {{ $plan->description ?? 'Standard operational capabilities and SRE tools.' }}
                    </p>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 space-y-2 text-xs font-mono">
                        <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span>Max Workspaces:</span>
                            <strong>{{ $plan->getLimit('max_workspaces', 1) }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span>Max Users:</span>
                            <strong>{{ $plan->getLimit('max_users', 5) }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span>Trial Period:</span>
                            <strong>{{ $plan->trial_days }} Days</strong>
                        </div>
                        <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span>Custom SLA:</span>
                            <strong>{{ $plan->hasFeature('custom_sla') ? 'Included' : 'Standard (99.9%)' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/5">
                    <form method="POST" action="{{ route('admin.platform.plans.update', $plan->id) }}" class="space-y-3 text-xs">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $plan->name }}">
                        <input type="hidden" name="is_active" value="{{ $plan->is_active ? 1 : 0 }}">

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-mono">Price ($)</label>
                                <input type="number" step="0.01" name="price_dollars" value="{{ $plan->price_cents / 100 }}"
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-mono">Trial Days</label>
                                <input type="number" name="trial_days" value="{{ $plan->trial_days }}"
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-mono">Max Workspaces</label>
                                <input type="number" name="max_workspaces" value="{{ $plan->getLimit('max_workspaces', 1) }}"
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-400 uppercase font-mono">Max Users</label>
                                <input type="number" name="max_users" value="{{ $plan->getLimit('max_users', 5) }}"
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono text-xs">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2 rounded-xl bg-gray-100 dark:bg-white/10 hover:bg-[#1B6B3A] hover:text-white text-gray-800 dark:text-gray-200 font-bold transition-colors cursor-pointer">
                            Update Entitlements
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 p-8 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 text-center text-gray-400">
                No commercial plans seeded yet.
            </div>
        @endforelse
    </div>

    {{-- Create New Plan Form --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase">
            Create New Commercial Plan
        </h3>

        <form method="POST" action="{{ route('admin.platform.plans.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Plan Name</label>
                <input type="text" name="name" placeholder="e.g. Enterprise Dedicated" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A]">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Tier Specification</label>
                <select name="tier" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
                    <option value="free">Free</option>
                    <option value="team">Team</option>
                    <option value="enterprise">Enterprise</option>
                    <option value="customer_hosted">Customer Hosted</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Price (USD)</label>
                <input type="number" step="0.01" name="price_dollars" value="49.00" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Billing Interval</label>
                <select name="billing_interval" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
                    <option value="monthly">Monthly</option>
                    <option value="annual">Annual</option>
                    <option value="perpetual">Perpetual License</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Trial Days</label>
                <input type="number" name="trial_days" value="14" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Max Workspaces</label>
                <input type="number" name="max_workspaces" value="10" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Max Users</label>
                <input type="number" name="max_users" value="50" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold transition-colors cursor-pointer shadow-sm">
                    Create Plan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
