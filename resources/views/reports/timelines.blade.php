@extends('layouts.app')
@section('title', 'Operator Work Timelines & Hours')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Operator Work Timelines & Active Hours</h1>
            <p class="text-sm text-gray-500 mt-1">Audited operational timelines and active duty hours derived from activity logs, checkoffs, and handover events.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.timelines', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="px-4 py-2 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Timelines CSV</span>
            </a>
        </div>
    </div>

    {{-- Report Tabs Navigation --}}
    <div class="flex border-b border-gray-200 mt-6 gap-2">
        <a href="{{ route('reports.index') }}"
           class="pb-3 px-4 text-xs font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-colors">
            Activity Check History
        </a>
        <a href="{{ route('reports.handovers') }}"
           class="pb-3 px-4 text-xs font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-colors">
            🤝 Shift Handovers Audit
        </a>
        <a href="{{ route('reports.timelines') }}"
           class="pb-3 px-4 text-xs font-bold text-[#1B6B3A] border-b-2 border-[#1B6B3A] transition-colors flex items-center gap-1.5">
            <span>⏱ Operator Work Timelines & Hours</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-[#1B6B3A] font-mono">{{ $timelines->count() }}</span>
        </a>
    </div>
</div>

{{-- KPI Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Active Hours</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalHours }} <span class="text-xs font-normal text-gray-500">hrs</span></p>
        <p class="text-xs text-gray-500 mt-0.5">Across {{ $timelines->count() }} operator duty shifts</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-[#1B6B3A] uppercase tracking-wider">Avg Shift Length</p>
        <p class="text-2xl font-bold text-[#1B6B3A] mt-1">{{ $avgShift ?? 0 }} <span class="text-xs font-normal text-emerald-700">hrs</span></p>
        <p class="text-xs text-emerald-700 mt-0.5">Per operator active day</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Checks Executed</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $totalChecks }}</p>
        <p class="text-xs text-blue-700 mt-0.5">Daily & recurring verifications</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-red-600 uppercase tracking-wider">Escalations Flagged</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $totalEscalations }}</p>
        <p class="text-xs text-red-700 mt-0.5">Incidents & warnings flagged</p>
    </div>
</div>

{{-- Filters Form --}}
<div class="bg-white rounded-xl shadow-xs border border-gray-200 p-4 mb-6">
    <form action="{{ route('reports.timelines') }}" method="GET" class="flex flex-wrap items-end gap-3 text-xs">
        <div>
            <label class="block font-semibold text-gray-700 mb-1">From Date</label>
            <input type="date" name="from" value="{{ $from }}"
                   class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-2.5">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">To Date</label>
            <input type="date" name="to" value="{{ $to }}"
                   class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-2.5">
        </div>
        <div>
            <label class="block font-semibold text-gray-700 mb-1">SRE Operator</label>
            <select name="user_id" class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-2.5 min-w-[180px]">
                <option value="">All Operators</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ (string)($userId ?? '') === (string)$user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->grade ?? 'L2' }} - {{ $user->department ?? 'Operations' }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-[#1B6B3A] hover:bg-[#15532D] text-white font-bold rounded-lg transition-colors shadow-xs">
                Filter Timelines
            </button>
            <a href="{{ route('reports.timelines') }}"
               class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Operator Timelines Table --}}
<div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-900">Audited Operator Duty Timelines</h2>
        <span class="text-xs text-gray-500 font-mono">Date range: {{ $from }} → {{ $to }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase tracking-wider font-semibold">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">SRE Operator</th>
                    <th class="px-4 py-3">First Duty Action</th>
                    <th class="px-4 py-3">Last Duty Action</th>
                    <th class="px-4 py-3 text-center">Active Hours</th>
                    <th class="px-4 py-3 text-center">Checks Done</th>
                    <th class="px-4 py-3 text-center">Escalations</th>
                    <th class="px-4 py-3 text-center">Total Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($timelines as $timeline)
                <tr class="hover:bg-gray-50/60 transition-colors">
                    <td class="px-4 py-3.5 whitespace-nowrap font-mono font-medium text-gray-900">
                        {{ $timeline['date'] }}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-[#1B6B3A]/10 text-[#1B6B3A] font-bold text-xs flex items-center justify-center shrink-0 border border-[#1B6B3A]/20">
                                {{ strtoupper(substr($timeline['user']->name ?? 'OP', 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $timeline['user']->name }}</div>
                                <div class="text-[11px] text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">
                                        {{ $timeline['user']->grade ?? 'L2' }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $timeline['user']->department ?? 'Operations' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $timeline['first_action_at'] ? $timeline['first_action_at']->format('H:i:s') : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $timeline['last_action_at'] ? $timeline['last_action_at']->format('H:i:s') : '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold font-mono
                            {{ $timeline['hours_worked'] >= 8 ? 'bg-emerald-100 text-emerald-800' : ($timeline['hours_worked'] >= 4 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            ⏱ {{ $timeline['hours_worked'] }} hrs
                        </span>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                        <span class="font-bold text-gray-800 font-mono">{{ $timeline['checks_done'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                        @if($timeline['escalations'] > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-700">
                                ⚠ {{ $timeline['escalations'] }}
                            </span>
                        @else
                            <span class="text-gray-400 font-mono">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-center">
                        <span class="font-mono text-gray-700 font-semibold">{{ $timeline['total_actions'] }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-medium text-gray-600">No operator duty records found</p>
                        <p class="text-xs text-gray-400 mt-1">Try broadening the date range or selecting a different operator filter.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
