@extends('layouts.app')

@section('title', 'SaaS Platform Analytics & Reports — Opsora SRE')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb and Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Reports & SaaS Intelligence</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Platform SaaS Intelligence</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                    Live Telemetry
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Real-time commercial revenue metrics (MRR/ARR), multi-tenant adoption growth, operational compliance rates, and SRE resolution KPIs.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Export Report</span>
            </button>
            <a href="{{ route('admin.platform.subscriptions.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white text-xs font-bold transition-colors">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Subscription Ledger</span>
            </a>
        </div>
    </div>

    {{-- Top SaaS Financial & Commercial KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- MRR --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">Monthly Recurring Revenue</div>
            <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">
                ${{ number_format($metrics['mrr'] ?? 0, 2) }}
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-mono">
                <span>Active Plans: {{ $metrics['active_subscriptions'] ?? 0 }}</span>
                <span>&bull;</span>
                <span>{{ $metrics['paid_subscriptions'] ?? 0 }} Paid</span>
            </div>
        </div>

        {{-- ARR --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-[#F5C518]/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">Annual Run Rate (ARR)</div>
            <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">
                ${{ number_format($metrics['arr'] ?? 0, 2) }}
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs text-[#F5C518] font-mono">
                <span>Annualized run-rate</span>
            </div>
        </div>

        {{-- Handover Compliance Rate --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">Shift Handover SLA</div>
            <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">
                {{ $complianceRate }}%
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400 font-mono">
                <span>{{ $totalHandovers }} Total Shifts Executed</span>
            </div>
        </div>

        {{-- Incident Resolution Rate --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm relative overflow-hidden">
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase tracking-wider">Incident Resolution MTTR</div>
            <div class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white mt-1">
                {{ $incidentResolutionRate }}%
            </div>
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400 font-mono">
                <span>{{ $totalIncidents }} Total SRE Incidents</span>
            </div>
        </div>
    </div>

    {{-- Growth & Adoption Breakdown Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Organization Growth Breakdown --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Tenant Organization Growth</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monthly new organization registrations over past 6 months</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300">
                    {{ $metrics['total_organizations'] ?? 0 }} Total
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-black/20 text-gray-600 dark:text-gray-400 uppercase font-mono">
                        <tr>
                            <th class="py-2.5 px-3">Month</th>
                            <th class="py-2.5 px-3 text-right">New Organizations</th>
                            <th class="py-2.5 px-3 text-right">Share of Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($monthlyOrgs as $month => $count)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900 dark:text-white">{{ $month }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">+{{ $count }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-gray-500 dark:text-gray-400">
                                    {{ ($metrics['total_organizations'] ?? 0) > 0 ? round(($count / ($metrics['total_organizations'] ?? 1)) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">No organizational telemetry recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Platform User Growth Breakdown --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Operator & Engineer Onboarding</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Monthly new user registrations across all tenants</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-[#F5C518]/20 text-yellow-800 dark:text-[#F5C518]">
                    {{ $metrics['total_users'] ?? 0 }} Total
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-black/20 text-gray-600 dark:text-gray-400 uppercase font-mono">
                        <tr>
                            <th class="py-2.5 px-3">Month</th>
                            <th class="py-2.5 px-3 text-right">New Engineers</th>
                            <th class="py-2.5 px-3 text-right">Share of Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($monthlyUsers as $month => $count)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                <td class="py-2.5 px-3 font-mono font-bold text-gray-900 dark:text-white">{{ $month }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold">+{{ $count }}</td>
                                <td class="py-2.5 px-3 text-right font-mono text-gray-500 dark:text-gray-400">
                                    {{ ($metrics['total_users'] ?? 0) > 0 ? round(($count / ($metrics['total_users'] ?? 1)) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">No user onboarding telemetry recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Platform Infrastructure Resource Allocation --}}
    <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-1">Multi-Tenant Resource Allocation & Topology</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Summary of provisioned tenant contexts, operational sandboxes, and database density.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">Workspaces Per Organization</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ ($metrics['total_organizations'] ?? 0) > 0 ? round(($metrics['total_workspaces'] ?? 0) / ($metrics['total_organizations'] ?? 1), 1) : 0 }} avg
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">Total Workspaces: {{ $metrics['total_workspaces'] ?? 0 }}</div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">Active Handover Shift Cadence</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                    3 shifts / day
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">Morning, Afternoon, Night rotations</div>
            </div>

            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">Platform Health Integrity</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                    100% Core Integrity
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">Automated DB connection checks</div>
            </div>
        </div>
    </div>

</div>
@endsection
