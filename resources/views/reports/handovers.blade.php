@extends('layouts.app')
@section('title', 'Shift Handover Reports')

@section('content')
<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">SRE Shift Handover Reporting</h1>
            <p class="text-sm text-gray-500 mt-1">Audit trail and compliance tracking for shift transfers, sign-offs, and incoming lead acceptances.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.handovers', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="px-4 py-2 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Handover CSV</span>
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
           class="pb-3 px-4 text-xs font-bold text-[#1B6B3A] border-b-2 border-[#1B6B3A] transition-colors flex items-center gap-1.5">
            <span>🤝 Shift Handovers Audit</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-[#1B6B3A] font-mono">{{ $metrics['total'] }}</span>
        </a>
        <a href="{{ route('reports.timelines') }}"
           class="pb-3 px-4 text-xs font-semibold text-gray-500 hover:text-gray-700 border-b-2 border-transparent transition-colors">
            ⏱ Operator Work Timelines & Hours
        </a>
    </div>
</div>

{{-- KPI Stat Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Shift Handovers</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $metrics['total'] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Recorded shift transitions</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Formally Accepted</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $metrics['accepted'] }}</p>
        <p class="text-xs text-emerald-700 mt-0.5">{{ $metrics['acceptance_rate'] }}% compliance rate</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Awaiting Acceptance</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $metrics['pending'] }}</p>
        <p class="text-xs text-amber-700 mt-0.5">Pending incoming lead sign-on</p>
    </div>
    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
        <p class="text-[11px] font-bold text-red-600 uppercase tracking-wider">Handovers with Incidents</p>
        <p class="text-2xl font-bold text-red-600 mt-1">{{ $metrics['incidents_count'] }}</p>
        <p class="text-xs text-red-700 mt-0.5">Active blockers transferred</p>
    </div>
</div>

{{-- Filter form --}}
<div class="bg-white rounded-xl shadow-xs border border-gray-200 p-5 mb-6 no-print">
    <form action="{{ route('reports.handovers') }}" method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label for="from" class="block text-xs font-semibold text-gray-700 uppercase mb-1">From</label>
            <input type="date" id="from" name="from" value="{{ $from }}"
                   class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
        </div>
        <div>
            <label for="to" class="block text-xs font-semibold text-gray-700 uppercase mb-1">To</label>
            <input type="date" id="to" name="to" value="{{ $to }}"
                   class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
        </div>
        <div>
            <label for="shift" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Shift</label>
            <select id="shift" name="shift" class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
                <option value="">All Shifts</option>
                <option value="morning" {{ $shift === 'morning' ? 'selected' : '' }}>Morning (06:00 - 14:00)</option>
                <option value="afternoon" {{ $shift === 'afternoon' ? 'selected' : '' }}>Afternoon (14:00 - 22:00)</option>
                <option value="night" {{ $shift === 'night' ? 'selected' : '' }}>Night (22:00 - 06:00)</option>
            </select>
        </div>
        <div>
            <label for="lead_id" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Shift Lead</label>
            <select id="lead_id" name="lead_id" class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
                <option value="">All Leads</option>
                @foreach($leads as $lead)
                <option value="{{ $lead->id }}" {{ $leadId == $lead->id ? 'selected' : '' }}>
                    {{ $lead->name }} ({{ ucfirst($lead->role) }})
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Acceptance</label>
            <select id="status" name="status" class="rounded-lg border-gray-300 text-xs focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
                <option value="">All Statuses</option>
                <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>✓ Formally Accepted</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>⏳ Awaiting Sign-On</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-xs font-bold rounded-lg transition-colors shadow-xs">
                Filter Handovers
            </button>
            <a href="{{ route('reports.handovers') }}"
               class="px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Handovers Data Table --}}
<div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-600">Shift Handover Audit Records</h2>
        <span class="text-xs text-gray-400 font-mono">Showing {{ $handovers->total() }} records</span>
    </div>

    @if($handovers->isEmpty())
    <div class="p-12 text-center text-gray-400">
        <span class="text-4xl mb-2 block">🤝</span>
        <p class="text-sm font-semibold text-gray-600">No shift handovers found for the selected criteria.</p>
        <p class="text-xs text-gray-400 mt-1">Try adjusting your date range or shift filter parameters.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
            <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider text-[11px]">
                <tr>
                    <th class="py-3 px-4">Shift & Date</th>
                    <th class="py-3 px-4">Outgoing Lead</th>
                    <th class="py-3 px-4">Incoming Lead / Acceptance</th>
                    <th class="py-3 px-4">Frozen Task Snapshot</th>
                    <th class="py-3 px-4">Summary & Verification</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($handovers as $h)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <div class="font-bold text-gray-900">{{ $h->date->format('d M Y') }}</div>
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-slate-100 text-slate-800">
                            {{ ucfirst($h->shift) }} Shift
                        </span>
                        <div class="text-[10px] text-gray-400 font-mono mt-0.5">
                            Signed: {{ $h->signed_at?->format('H:i \G\M\T') }}
                        </div>
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <div class="font-bold text-gray-800">{{ $h->outgoingLead?->name ?? '—' }}</div>
                        <div class="text-[10px] text-gray-400">{{ $h->outgoingLead?->grade ?? 'L4' }} &bull; {{ $h->outgoingLead?->role }}</div>
                    </td>

                    <td class="py-3.5 px-4">
                        @if($h->isAccepted())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                <svg class="w-3 h-3 text-emerald-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Signed On
                            </span>
                            <div class="text-xs font-semibold text-gray-800 mt-1">
                                {{ $h->acceptedBy?->name ?? 'Incoming Lead' }}
                            </div>
                            <div class="text-[10px] text-gray-400 font-mono">
                                {{ $h->accepted_at?->format('d M Y, H:i \G\M\T') }}
                            </div>
                            @if($h->acceptance_remarks)
                            <p class="text-[11px] text-gray-600 italic mt-1 bg-gray-50 p-1.5 rounded border border-gray-100">
                                "{{ $h->acceptance_remarks }}"
                            </p>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                <span>⏳</span> Awaiting Sign-On
                            </span>
                            @if($h->incomingLead)
                            <div class="text-xs text-gray-500 mt-1">
                                Designated: <strong class="text-gray-700">{{ $h->incomingLead->name }}</strong>
                            </div>
                            @endif
                        @endif
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs">
                        <div class="text-amber-700">Pending: <strong>{{ $h->pending_tasks_count }}</strong></div>
                        <div class="text-emerald-700">Done: <strong>{{ $h->completed_tasks_count }}</strong></div>
                        @if($h->incidents)
                        <div class="mt-1">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-red-100 text-red-800">
                                🚨 Incidents Noted
                            </span>
                        </div>
                        @endif
                    </td>

                    <td class="py-3.5 px-4 max-w-sm">
                        <p class="text-xs text-gray-800 whitespace-pre-line leading-relaxed">{{ Str::limit($h->summary, 160) }}</p>
                        @if($h->incidents)
                        <div class="mt-1 text-[11px] text-red-800 bg-red-50/70 p-1.5 rounded border border-red-200">
                            <strong>Open Incident:</strong> {{ Str::limit($h->incidents, 100) }}
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($handovers->hasPages())
    <div class="p-4 border-t border-gray-200 bg-gray-50/50">
        {{ $handovers->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
