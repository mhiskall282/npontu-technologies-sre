@extends('layouts.app')

@section('title', 'Platform Subscriptions — Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Billing</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Tenant Subscriptions &amp; Billing
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Active commercial associations, billing cycles, trial tracking, and manual status adjustments.
            </p>
        </div>
    </div>

    {{-- Subscriptions Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Organization</th>
                        <th class="px-5 py-3.5">Commercial Plan</th>
                        <th class="px-5 py-3.5">Billing Status</th>
                        <th class="px-5 py-3.5">Current Period / Trial</th>
                        <th class="px-5 py-3.5 text-right">Update Subscription</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.platform.organizations.show', $sub->organization->id) }}" class="font-bold text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 block text-sm">
                                    {{ $sub->organization?->name ?? 'Unknown Org' }}
                                </a>
                                <span class="font-mono text-[11px] text-gray-400 block mt-0.5">
                                    Provider: {{ strtoupper($sub->billing_provider) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $sub->plan?->name }}</span>
                                <span class="font-mono text-[11px] text-[#1B6B3A] dark:text-[#F5C518]">
                                    {{ $sub->plan?->priceFormatted() }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if($sub->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        Active
                                    </span>
                                @elseif($sub->status === 'trialing')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518] border border-yellow-300 dark:border-yellow-800">
                                        Trialing
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 font-mono text-gray-500 dark:text-gray-400">
                                @if($sub->onTrial())
                                    <span class="text-yellow-600 dark:text-yellow-400">Trial ends {{ $sub->trial_ends_at?->diffForHumans() }}</span>
                                @else
                                    <span>Period: {{ $sub->current_period_end?->format('Y-m-d') ?? 'N/A' }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('admin.platform.subscriptions.update', $sub->id) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="plan_id" class="px-2 py-1 rounded-lg text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200">
                                        @foreach($plans as $p)
                                            <option value="{{ $p->id }}" {{ $sub->plan_id === $p->id ? 'selected' : '' }}>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="status" class="px-2 py-1 rounded-lg text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200">
                                        <option value="active" {{ $sub->status === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="trialing" {{ $sub->status === 'trialing' ? 'selected' : '' }}>Trialing</option>
                                        <option value="past_due" {{ $sub->status === 'past_due' ? 'selected' : '' }}>Past Due</option>
                                        <option value="canceled" {{ $sub->status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                                    </select>

                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#1B6B3A] hover:bg-emerald-600 text-white transition-colors cursor-pointer">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No subscriptions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
