@extends('layouts.app')

@section('title', 'Global Audit Trail — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Compliance</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Global Platform Audit Trail
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Immutable record of all privileged administrative state changes, tenant mutations, and operational check updates across the SaaS fleet.
            </p>
        </div>
    </div>

    {{-- Filter Controls --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <form method="GET" action="{{ route('admin.platform.audit.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Filter by actor name, event type, or IP address..."
                       class="w-full px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
            </div>

            <div>
                <select name="event"
                        class="w-full px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Audit Events</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ ($filters['event'] ?? '') === $ev ? 'selected' : '' }}>
                            {{ $ev }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <input type="date"
                       name="from"
                       value="{{ $filters['from'] ?? '' }}"
                       class="w-full px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono">
            </div>

            <div class="flex items-center gap-2">
                <input type="date"
                       name="to"
                       value="{{ $filters['to'] ?? '' }}"
                       class="w-full px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono">
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold shadow-sm transition-colors cursor-pointer shrink-0">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Audit Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Timestamp (UTC)</th>
                        <th class="px-5 py-3.5">Actor / Operator</th>
                        <th class="px-5 py-3.5">Action / Event</th>
                        <th class="px-5 py-3.5">Subject Entity</th>
                        <th class="px-5 py-3.5">State Diff / Payload</th>
                        <th class="px-5 py-3.5 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 font-mono text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5 text-gray-500 shrink-0">
                                {{ $log->created_at?->format('Y-m-d H:i:s') }}
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $log->actor_name ?? 'System' }}</span>
                                @if($log->actor)
                                    <span class="text-[10px] text-gray-400 block">{{ $log->actor->email }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ $log->event }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-300">
                                <span>{{ class_basename($log->subject_type ?? 'Record') }}</span>
                                @if($log->subject_id)
                                    <span class="text-gray-400">#{{ $log->subject_id }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-[10px] text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                @if($log->new_values)
                                    {{ json_encode($log->new_values) }}
                                @else
                                    <span class="text-gray-400">No delta</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 text-right text-gray-400 shrink-0">
                                {{ $log->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-sans font-medium">
                                No audit log records match the search filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
