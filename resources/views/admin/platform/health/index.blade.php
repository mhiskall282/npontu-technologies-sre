@extends('layouts.app')

@section('title', 'System Diagnostics — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Infrastructure</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                System Health &amp; Operational Probes
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Live diagnostics for database latency, cache drivers, queue workers, storage mounts, and background worker state.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.platform.health.retryJobs') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Retry Failed Queue Jobs</span>
                </button>
            </form>
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
            Platform Runtime Environment
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
