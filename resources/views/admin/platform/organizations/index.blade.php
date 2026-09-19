@extends('layouts.app')

@section('title', 'Organizations Directory — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Tenants</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Tenant Organizations Directory
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manage commercial tenant organizations, review security statuses, and configure deployment topologies.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.organizations.applications') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold shadow-xs hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Applications Queue</span>
            </a>
        </div>
    </div>

    {{-- Search & Filter Controls --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <form method="GET" action="{{ route('admin.platform.organizations.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            {{-- Search input --}}
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Search organizations by name, slug, or company code..."
                       class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
            </div>

            {{-- Status Filter --}}
            <div>
                <select name="status"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            {{-- Tier Filter & Submit --}}
            <div class="flex items-center gap-2">
                <select name="tier"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Tiers</option>
                    <option value="free" {{ ($filters['tier'] ?? '') === 'free' ? 'selected' : '' }}>Free</option>
                    <option value="team" {{ ($filters['tier'] ?? '') === 'team' ? 'selected' : '' }}>Team</option>
                    <option value="enterprise" {{ ($filters['tier'] ?? '') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                    <option value="customer_hosted" {{ ($filters['tier'] ?? '') === 'customer_hosted' ? 'selected' : '' }}>Customer Hosted</option>
                </select>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer shrink-0">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Organizations Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Organization</th>
                        <th class="px-5 py-3.5">Company Code</th>
                        <th class="px-5 py-3.5">Tier &amp; Deployment</th>
                        <th class="px-5 py-3.5">Workspaces &amp; Members</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.platform.organizations.show', $org->id) }}"
                                   class="font-bold text-gray-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 block text-sm">
                                    {{ $org->name }}
                                </a>
                                <span class="font-mono text-[11px] text-gray-400 block mt-0.5">
                                    UUID: {{ substr($org->uuid, 0, 8) }}... &bull; Region: {{ $org->preferred_region }}
                                </span>
                            </td>

                            <td class="px-5 py-4 font-mono font-bold text-[#1B6B3A] dark:text-[#F5C518]">
                                {{ $org->company_code }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                             {{ $org->tier === 'enterprise' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518]' : ($org->tier === 'team' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300') }}">
                                    {{ $org->tier }}
                                </span>
                                <span class="block text-[11px] text-gray-400 font-mono mt-0.5 capitalize">
                                    {{ str_replace('_', ' ', $org->deployment_model) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-gray-600 dark:text-gray-300 font-mono">
                                <span>{{ $org->workspaces_count }} Workspaces</span>
                                &bull;
                                <span>{{ $org->members_count }} Members</span>
                            </td>

                            <td class="px-5 py-4">
                                @if($org->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                        <span>Active</span>
                                    </span>
                                @elseif($org->status === 'suspended')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                                        <span>Suspended</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518] border border-yellow-300 dark:border-yellow-800">
                                        <span class="capitalize">{{ $org->status }}</span>
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.platform.organizations.show', $org->id) }}"
                                       class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 text-gray-800 dark:text-gray-200 transition-colors">
                                        Details
                                    </a>

                                    @if($org->status === 'active')
                                        <form method="POST" action="{{ route('admin.platform.organizations.suspend', $org->id) }}">
                                            @csrf
                                            <input type="hidden" name="reason" value="Suspended via Platform Control Plane.">
                                            <button type="submit"
                                                    onclick="return confirm('Suspend organization {{ $org->name }}? Its operators will be blocked from accessing workspaces.')"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 hover:bg-red-600 hover:text-white transition-colors cursor-pointer">
                                                Suspend
                                            </button>
                                        </form>
                                    @elseif($org->status === 'suspended')
                                        <form method="POST" action="{{ route('admin.platform.organizations.reactivate', $org->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Reactivate organization {{ $org->name }}?')"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-600 hover:text-white transition-colors cursor-pointer">
                                                Reactivate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No organizations match the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($organizations->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $organizations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
