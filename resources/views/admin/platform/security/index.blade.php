@extends('layouts.app')

@section('title', 'Security Center & SIEM — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">SIEM Telemetry</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Platform Security Center
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Security Information &amp; Event Management (SIEM) feed tracking failed logins, privilege changes, and security events.
            </p>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <form method="GET" action="{{ route('admin.platform.security.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Filter by actor name, IP address, or event type..."
                       class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
            </div>

            <div>
                <select name="severity"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Severities</option>
                    <option value="info" {{ ($filters['severity'] ?? '') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ ($filters['severity'] ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="critical" {{ ($filters['severity'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="event_type"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Event Types</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ ($filters['event_type'] ?? '') === $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer shrink-0">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Security Events Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Severity</th>
                        <th class="px-5 py-3.5">Event Type</th>
                        <th class="px-5 py-3.5">Actor</th>
                        <th class="px-5 py-3.5">IP Address &amp; User Agent</th>
                        <th class="px-5 py-3.5">Details (JSON)</th>
                        <th class="px-5 py-3.5 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($events as $ev)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase
                                             {{ $ev->severity === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' : ($ev->severity === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518]' : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300') }}">
                                    {{ $ev->severity }}
                                </span>
                            </td>

                            <td class="px-5 py-4 font-mono font-bold text-gray-900 dark:text-white">
                                {{ $ev->event_type }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $ev->actor_name ?? 'System/Anonymous' }}</span>
                                @if($ev->actor)
                                    <span class="font-mono text-[11px] text-gray-400 block">{{ $ev->actor->email }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 font-mono text-[11px] text-gray-600 dark:text-gray-300">
                                <span>{{ $ev->ip_address ?? 'unknown' }}</span>
                                <span class="block text-[10px] text-gray-400 truncate max-w-xs">{{ $ev->user_agent }}</span>
                            </td>

                            <td class="px-5 py-4 font-mono text-[10px] text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $ev->details ? json_encode($ev->details) : 'None' }}
                            </td>

                            <td class="px-5 py-4 font-mono text-gray-500 text-right shrink-0">
                                {{ $ev->created_at?->format('Y-m-d H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No security events match the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $events->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
