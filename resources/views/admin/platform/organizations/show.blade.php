@extends('layouts.app')

@section('title', $organization->name . ' — Organization Control Plane')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400">
            <a href="{{ route('admin.platform.organizations.index') }}" class="hover:underline">Organizations</a>
            <span>/</span>
            <span class="text-[#F5C518]">{{ $organization->slug }}</span>
        </div>

        <div class="flex items-center gap-2">
            @if($organization->status === 'active')
                <form method="POST" action="{{ route('admin.platform.organizations.suspend', $organization->id) }}">
                    @csrf
                    <input type="hidden" name="reason" value="Suspended from Organization Detail Console.">
                    <button type="submit"
                            onclick="return confirm('Suspend organization {{ $organization->name }}?')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 hover:bg-red-600 hover:text-white transition-colors cursor-pointer">
                        Suspend Organization
                    </button>
                </form>
            @elseif($organization->status === 'suspended')
                <form method="POST" action="{{ route('admin.platform.organizations.reactivate', $organization->id) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Reactivate organization {{ $organization->name }}?')"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors cursor-pointer">
                        Reactivate Organization
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Organization Profile Header Banner --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#1B6B3A] to-emerald-950 border border-[#F5C518]/50 flex items-center justify-center text-white text-xl font-black font-mono shadow-md">
                {{ strtoupper(substr($organization->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                        {{ $organization->name }}
                    </h1>
                    @if($organization->status === 'active')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                            Active
                        </span>
                    @elseif($organization->status === 'suspended')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                            Suspended
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518] border border-yellow-300 dark:border-yellow-800">
                            {{ ucfirst($organization->status) }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-mono mt-1">
                    <span>Code: <strong class="text-[#1B6B3A] dark:text-[#F5C518]">{{ $organization->company_code }}</strong></span>
                    <span>&bull;</span>
                    <span>Tier: <strong class="uppercase text-gray-700 dark:text-gray-200">{{ $organization->tier }}</strong></span>
                    <span>&bull;</span>
                    <span>Region: <strong class="uppercase text-gray-700 dark:text-gray-200">{{ $organization->preferred_region }}</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6 font-mono text-center shrink-0">
            <div>
                <span class="block text-xl font-black text-gray-900 dark:text-white">{{ $organization->workspaces->count() }}</span>
                <span class="block text-[10px] text-gray-400 uppercase">Workspaces</span>
            </div>
            <div>
                <span class="block text-xl font-black text-gray-900 dark:text-white">{{ $organization->members->count() }}</span>
                <span class="block text-[10px] text-gray-400 uppercase">Members</span>
            </div>
            <div>
                <span class="block text-xl font-black text-[#1B6B3A] dark:text-emerald-400">
                    {{ $organization->activePlan()?->name ?? 'Free Tier' }}
                </span>
                <span class="block text-[10px] text-gray-400 uppercase">Current Plan</span>
            </div>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-white/10 pb-2 text-xs font-bold font-mono overflow-x-auto">
        <button type="button"
                @click="activeTab = 'overview'"
                :class="activeTab === 'overview' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-2 rounded-xl transition-colors cursor-pointer">
            Overview &amp; Topology
        </button>
        <button type="button"
                @click="activeTab = 'workspaces'"
                :class="activeTab === 'workspaces' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-2 rounded-xl transition-colors cursor-pointer">
            Workspaces ({{ $organization->workspaces->count() }})
        </button>
        <button type="button"
                @click="activeTab = 'members'"
                :class="activeTab === 'members' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-2 rounded-xl transition-colors cursor-pointer">
            Members ({{ $organization->members->count() }})
        </button>
        <button type="button"
                @click="activeTab = 'subscription'"
                :class="activeTab === 'subscription' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-2 rounded-xl transition-colors cursor-pointer">
            Commercial Subscription
        </button>
        <button type="button"
                @click="activeTab = 'audit'"
                :class="activeTab === 'audit' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                class="px-4 py-2 rounded-xl transition-colors cursor-pointer">
            Audit Trail ({{ $auditLogs->count() }})
        </button>
    </div>

    {{-- Tab 1: Overview & Topology Configuration --}}
    <div x-show="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase">
                Tenant Metadata &amp; Configuration
            </h3>

            <form method="POST" action="{{ route('admin.platform.organizations.update', $organization->id) }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Commercial Tier</label>
                        <select name="tier" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A]">
                            <option value="free" {{ $organization->tier === 'free' ? 'selected' : '' }}>Free Tier</option>
                            <option value="team" {{ $organization->tier === 'team' ? 'selected' : '' }}>Team Edition</option>
                            <option value="enterprise" {{ $organization->tier === 'enterprise' ? 'selected' : '' }}>Enterprise Tier</option>
                            <option value="customer_hosted" {{ $organization->tier === 'customer_hosted' ? 'selected' : '' }}>Customer Hosted</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Deployment Model</label>
                        <select name="deployment_model" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A]">
                            <option value="shared_saas" {{ $organization->deployment_model === 'shared_saas' ? 'selected' : '' }}>Shared Multi-Tenant SaaS</option>
                            <option value="dedicated_managed" {{ $organization->deployment_model === 'dedicated_managed' ? 'selected' : '' }}>Dedicated Managed Cluster</option>
                            <option value="customer_funded" {{ $organization->deployment_model === 'customer_funded' ? 'selected' : '' }}>Customer Funded VPC</option>
                            <option value="customer_hosted" {{ $organization->deployment_model === 'customer_hosted' ? 'selected' : '' }}>Customer Self-Hosted</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Preferred Data Residency</label>
                        <select name="preferred_region" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A]">
                            <option value="af-south" {{ $organization->preferred_region === 'af-south' ? 'selected' : '' }}>Africa (South) — af-south</option>
                            <option value="us-east" {{ $organization->preferred_region === 'us-east' ? 'selected' : '' }}>US East (N. Virginia) — us-east</option>
                            <option value="eu-west" {{ $organization->preferred_region === 'eu-west' ? 'selected' : '' }}>Europe (Ireland) — eu-west</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Company Code</label>
                        <input type="text" value="{{ $organization->company_code }}" disabled
                               class="w-full px-3 py-2 rounded-xl bg-gray-100 dark:bg-black/50 border border-gray-300 dark:border-white/10 text-gray-500 dark:text-gray-400 font-mono">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer">
                        Save Configuration Changes
                    </button>
                </div>
            </form>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-3 text-xs">
            <h3 class="font-bold text-gray-900 dark:text-white font-mono uppercase">System Identifiers</h3>
            <div class="space-y-2 font-mono">
                <div>
                    <span class="text-gray-400 block text-[10px]">ORGANIZATION UUID</span>
                    <span class="text-gray-800 dark:text-gray-200 break-all select-all">{{ $organization->uuid }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">SLUG IDENTIFIER</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $organization->slug }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[10px]">REGISTERED ON</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $organization->created_at?->format('F d, Y H:i:s T') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab 2: Workspaces --}}
    <div x-show="activeTab === 'workspaces'" class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                <tr>
                    <th class="px-5 py-3.5">Workspace Name</th>
                    <th class="px-5 py-3.5">Slug / Subdomain</th>
                    <th class="px-5 py-3.5">Owner</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse($organization->workspaces as $ws)
                    <tr>
                        <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">{{ $ws->name }}</td>
                        <td class="px-5 py-4 font-mono text-gray-500 dark:text-gray-400">{{ $ws->slug }} &bull; {{ $ws->subdomain ?? 'none' }}</td>
                        <td class="px-5 py-4 text-gray-800 dark:text-gray-200">{{ $ws->owner?->name ?? 'None' }}</td>
                        <td class="px-5 py-4 font-mono capitalize">{{ $ws->status }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.platform.workspaces.show', $ws->id) }}" class="text-xs font-bold text-[#1B6B3A] dark:text-emerald-400 hover:underline">
                                Inspect &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">No workspaces provisioned yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tab 3: Members --}}
    <div x-show="activeTab === 'members'" class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                <tr>
                    <th class="px-5 py-3.5">Member Name</th>
                    <th class="px-5 py-3.5">Email</th>
                    <th class="px-5 py-3.5">Tenant Role</th>
                    <th class="px-5 py-3.5">Department</th>
                    <th class="px-5 py-3.5">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @forelse($organization->memberships as $m)
                    <tr>
                        <td class="px-5 py-4 font-bold text-gray-900 dark:text-white">{{ $m->user?->name ?? 'Unknown' }}</td>
                        <td class="px-5 py-4 font-mono text-gray-500 dark:text-gray-400">{{ $m->user?->email }}</td>
                        <td class="px-5 py-4 font-mono uppercase text-[#1B6B3A] dark:text-[#F5C518] font-bold">{{ $m->role }}</td>
                        <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $m->department ?? 'General Operations' }}</td>
                        <td class="px-5 py-4 font-mono capitalize">{{ $m->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">No members attached to this organization.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tab 4: Commercial Subscription --}}
    <div x-show="activeTab === 'subscription'" class="space-y-6">
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase">Change Commercial Plan</h3>

            <form method="POST" action="{{ route('admin.platform.subscriptions.store') }}" class="flex flex-col sm:flex-row sm:items-end gap-3 text-xs">
                @csrf
                <input type="hidden" name="organization_id" value="{{ $organization->id }}">

                <div class="flex-1">
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Target Commercial Plan</label>
                    <select name="plan_id" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
                        @foreach($availablePlans as $p)
                            <option value="{{ $p->id }}" {{ $organization->activePlan()?->id === $p->id ? 'selected' : '' }}>
                                {{ $p->name }} — {{ $p->priceFormatted() }} ({{ $p->tier }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Billing State</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
                        <option value="active">Active</option>
                        <option value="trialing">Trialing</option>
                        <option value="past_due">Past Due</option>
                        <option value="paused">Paused</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold shadow-sm transition-colors cursor-pointer">
                    Apply Plan &amp; Entitlements
                </button>
            </form>
        </div>
    </div>

    {{-- Tab 5: Audit Trail --}}
    <div x-show="activeTab === 'audit'" class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                <tr>
                    <th class="px-5 py-3.5">Timestamp</th>
                    <th class="px-5 py-3.5">Actor</th>
                    <th class="px-5 py-3.5">Event</th>
                    <th class="px-5 py-3.5">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5 font-mono">
                @forelse($auditLogs as $log)
                    <tr>
                        <td class="px-5 py-3.5 text-gray-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-5 py-3.5 font-bold text-gray-800 dark:text-gray-200">{{ $log->actor_name }}</td>
                        <td class="px-5 py-3.5 text-emerald-600 dark:text-emerald-400 font-semibold">{{ $log->event }}</td>
                        <td class="px-5 py-3.5 text-gray-400">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400">No audit trail entries found for this organization.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
