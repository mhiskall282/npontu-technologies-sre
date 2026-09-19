@extends('layouts.app')

@section('title', 'Platform Operations & Infrastructure Diagnostics — Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Infrastructure &amp; Maintenance</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Platform Operations Hub &amp; Diagnostics</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                    SRE Console
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Execute live platform maintenance routines, purge caches, prune authentication tokens, retry worker queues, and probe node latencies.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.platform.health.diagnostics') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Run Deep Diagnostics Probe</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Feedback Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Platform Operations & Maintenance Hub --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/10 pb-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Administrative System Operations &amp; Maintenance</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">One-click operational triggers for routine maintenance and platform hygiene. All actions record immutable audit entries.</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300">
                Authorized Admin Actions
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
            {{-- Purge Caches --}}
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Purge System Caches</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Clears application cache, compiled Blade templates, and routing lookup tables.</p>
                </div>
                <form method="POST" action="{{ route('admin.platform.health.clear-cache') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Purge application and view compilation caches?')"
                            class="w-full py-1.5 px-3 bg-[#1B6B3A] hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                        Clear Caches Now
                    </button>
                </form>
            </div>

            {{-- Prune API Tokens --}}
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Prune Stale Tokens</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Purges expired Sanctum bearer tokens from the database to ensure credential hygiene.</p>
                </div>
                <form method="POST" action="{{ route('admin.platform.health.prune-tokens') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Prune all expired Sanctum API tokens from database?')"
                            class="w-full py-1.5 px-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                        Prune Stale Tokens
                    </button>
                </form>
            </div>

            {{-- Retry Failed Jobs --}}
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Retry Queue Jobs</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Re-queues all jobs from the failed_jobs ledger for automatic retry by background workers.</p>
                </div>
                <form method="POST" action="{{ route('admin.platform.health.retryJobs') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Retry all failed queue jobs?')"
                            class="w-full py-1.5 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                        Retry Failed Jobs
                    </button>
                </form>
            </div>

            {{-- Dispatch SRE Reports --}}
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Dispatch Reports</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Manually triggers scheduled daily handover and compliance report compilation and email dispatches.</p>
                </div>
                <form method="POST" action="{{ route('admin.platform.health.dispatch-reports') }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Trigger immediate dispatch of automated compliance reports?')"
                            class="w-full py-1.5 px-3 bg-[#1B6B3A] hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition cursor-pointer">
                        Dispatch Reports
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Diagnostic Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($health as $component => $info)
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ ucfirst($component) }}
                        </span>
                        @if($info['status'] === 'healthy')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                Healthy
                            </span>
                        @elseif($info['status'] === 'warning')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518] border border-yellow-300 dark:border-yellow-800">
                                Warning
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                                {{ ucfirst($info['status']) }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-base font-bold text-gray-900 dark:text-white capitalize">
                        {{ $component }} Engine
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">
                        {{ $info['message'] }}
                    </p>
                </div>

                @if(isset($info['latency_ms']))
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs font-mono">
                        <span class="text-gray-400">Probe Latency:</span>
                        <span class="font-bold text-[#1B6B3A] dark:text-emerald-400">{{ $info['latency_ms'] }} ms</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- System Telemetry Specifications --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase">
            Platform Runtime Environment &amp; Worker Telemetry
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10">
                <span class="text-gray-400 block text-[10px]">PHP RUNTIME</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ PHP_VERSION }}</span>
            </div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10">
                <span class="text-gray-400 block text-[10px]">FRAMEWORK</span>
                <span class="font-bold text-gray-900 dark:text-white">Laravel {{ app()->version() }}</span>
            </div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10">
                <span class="text-gray-400 block text-[10px]">APP ENVIRONMENT</span>
                <span class="font-bold text-gray-900 dark:text-white uppercase">{{ app()->environment() }}</span>
            </div>
            <div class="p-3 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-200 dark:border-white/10">
                <span class="text-gray-400 block text-[10px]">CACHE STORE</span>
                <span class="font-bold text-gray-900 dark:text-white uppercase">{{ config('cache.default') }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
