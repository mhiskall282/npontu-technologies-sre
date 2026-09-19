@extends('layouts.app')

@section('title', "Workspace: {$workspace->name} — Platform Control Plane")

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb and Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <a href="{{ route('admin.platform.workspaces.index') }}" class="hover:underline">Workspaces</a>
                <span>/</span>
                <span class="text-[#F5C518]">{{ $workspace->slug }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>{{ $workspace->name }}</span>
                @if($workspace->is_personal)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800">
                        Personal Sandbox
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                        Team Workspace
                    </span>
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Organization: <a href="{{ route('admin.platform.organizations.show', $workspace->organization->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline font-bold">{{ $workspace->organization->name }}</a> &bull; Owner: {{ $workspace->owner->name ?? 'System' }} &bull; Created {{ $workspace->created_at->format('M d, Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.platform.workspaces.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                &larr; Back to Workspaces
            </a>
        </div>
    </div>

    {{-- Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase">Assigned Members</div>
            <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $workspace->members->count() }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">Engineers & Operators</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase">Total SRE Activities</div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $workspace->activities->count() }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">Operations log entries</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 uppercase">Tenant Security Boundary</div>
            <div class="text-2xl font-black text-gray-900 dark:text-white mt-1">Strict Isolated</div>
            <div class="text-xs text-emerald-600 dark:text-emerald-400 mt-1 font-mono">Scoped to Org #{{ $workspace->organization_id }}</div>
        </div>
    </div>

    {{-- Members & Recent Activities --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Members List --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Assigned Engineers</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Users who have access to this operational workspace.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-black/20 text-gray-600 dark:text-gray-400 uppercase font-mono">
                        <tr>
                            <th class="py-2.5 px-3">Name</th>
                            <th class="py-2.5 px-3">Role</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($workspace->members as $member)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                <td class="py-2.5 px-3">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $member->name }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $member->email }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                        {{ $member->pivot->role ?? 'member' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <a href="{{ route('admin.platform.users.show', $member->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline font-bold">
                                        View Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">No members assigned directly to this workspace.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Recent Operational Activities</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Latest activities logged within this workspace boundary.</p>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-black/20 text-gray-600 dark:text-gray-400 uppercase font-mono">
                        <tr>
                            <th class="py-2.5 px-3">Description</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse($workspace->activities as $activity)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                <td class="py-2.5 px-3 font-semibold text-gray-900 dark:text-white max-w-xs truncate">
                                    {{ $activity->description }}
                                </td>
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                        @if($activity->status === 'done') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                        @elseif($activity->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                                        @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                        {{ strtoupper(str_replace('_', ' ', $activity->status)) }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $activity->activity_date?->format('M d, Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500 dark:text-gray-400">No activities recorded in this workspace.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
