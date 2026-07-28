@extends('layouts.app')
@section('title', 'Activity Reports')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Activity Reports</h1>
    <p class="text-sm text-gray-500 mt-1">Query activity history across any date range.</p>
</div>

{{-- Filter form --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="from" class="block text-xs font-medium text-gray-700 mb-1">From</label>
            <input type="date" id="from" name="from" value="{{ request('from') }}"
                   class="rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
        </div>
        <div>
            <label for="to" class="block text-xs font-medium text-gray-700 mb-1">To</label>
            <input type="date" id="to" name="to" value="{{ request('to') }}"
                   class="rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
        </div>
        <div>
            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
            <select id="status" name="status" class="rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
                <option value="">All</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
            </select>
        </div>
        <div>
            <label for="activity_id" class="block text-xs font-medium text-gray-700 mb-1">Activity</label>
            <select id="activity_id" name="activity_id" class="rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1.5 px-3">
                <option value="">All activities</option>
                @foreach($activities as $activity)
                <option value="{{ $activity->id }}" {{ request('activity_id') == $activity->id ? 'selected' : '' }}>
                    {{ $activity->title }}
                </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-5 py-2 bg-[#1B6B3A] hover:bg-[#2A8F52] text-white text-sm font-semibold rounded-lg transition-colors duration-150 shadow-sm">
            Run Report
        </button>
        @if(request()->hasAny(['from','to','status','activity_id']))
        <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">Clear</a>
        @endif
    </form>
</div>

{{-- Results --}}
@if($logs !== null)
    @if($logs->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-500">
        <p class="font-medium">No records found for the selected criteria.</p>
        <p class="text-sm mt-1">Try expanding the date range or clearing filters.</p>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Showing <span class="font-semibold">{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</span>
                of <span class="font-semibold">{{ $logs->total() }}</span> entries
            </p>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Activity</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Updated by</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Remark</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @foreach($logs as $log)
                <tr class="hover:bg-[#F4F7F5] transition-colors duration-100">
                    <td class="px-6 py-3 font-mono text-xs text-gray-600">{{ $log->date->format('d M Y') }}</td>
                    <td class="px-6 py-3">
                        <a href="{{ route('activities.show', $log->activity) }}"
                           class="text-[#1B6B3A] hover:underline font-medium">
                            {{ $log->activity?->title ?? '—' }}
                        </a>
                    </td>
                    <td class="px-6 py-3"><x-status-badge :status="$log->status" /></td>
                    <td class="px-6 py-3">
                        <span class="font-medium text-gray-800">{{ $log->actor_name }}</span>
                        <span class="text-gray-400 text-xs ml-1 capitalize">({{ $log->actor_role }})</span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 max-w-xs truncate">{{ $log->remark ?? '—' }}</td>
                    <td class="px-6 py-3 font-mono text-xs text-gray-400">{{ $log->created_at->format('H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
    @endif
@else
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
    <svg class="w-10 h-10 mx-auto mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    <p class="text-sm font-medium">Select a date range and run a report to see activity history.</p>
</div>
@endif
@endsection
