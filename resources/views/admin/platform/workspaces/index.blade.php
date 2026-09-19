@extends('layouts.app')

@section('title', 'Platform Workspaces Directory — Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Workspaces</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Global Workspaces Directory
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Cross-tenant oversight of all operational workspaces, personal engineering sandboxes, and member allocations.
            </p>
        </div>
    </div>

    {{-- Workspaces Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Workspace Name</th>
                        <th class="px-5 py-3.5">Organization Tenant</th>
                        <th class="px-5 py-3.5">Owner / Lead</th>
                        <th class="px-5 py-3.5">Members &amp; Activities</th>
                        <th class="px-5 py-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($workspaces as $ws)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-bold text-gray-900 dark:text-white block text-sm">{{ $ws->name }}</span>
                                <span class="font-mono text-[11px] text-gray-400 block mt-0.5">
                                    Slug: {{ $ws->slug }} &bull; {{ $ws->is_personal ? 'Personal Space' : 'Org Space' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                @if($ws->organization)
                                    <a href="{{ route('admin.platform.organizations.show', $ws->organization->id) }}" class="font-bold text-[#1B6B3A] dark:text-emerald-400 hover:underline">
                                        {{ $ws->organization->name }}
                                    </a>
                                    <span class="block text-[11px] font-mono text-gray-400">{{ $ws->organization->company_code }}</span>
                                @else
                                    <span class="text-gray-400 font-mono">Personal Workspace</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-gray-800 dark:text-gray-200">
                                {{ $ws->owner?->name ?? 'Unassigned' }}
                            </td>

                            <td class="px-5 py-4 font-mono text-gray-600 dark:text-gray-300">
                                <span>{{ $ws->members_count }} Members</span>
                                &bull;
                                <span>{{ $ws->activities_count }} Checks</span>
                            </td>

                            <td class="px-5 py-4">
                                @if($ws->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                                        {{ ucfirst($ws->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No workspaces found in database.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workspaces->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $workspaces->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
