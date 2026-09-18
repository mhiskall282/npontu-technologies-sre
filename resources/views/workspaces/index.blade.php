@extends('layouts.app')

@section('title', 'Workspaces & Organizations — Opsora SRE')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-400 mb-1">
                <span>Opsora Multi-Tenant</span>
                <span>/</span>
                <span class="text-[#F5C518]">Workspaces</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Operational Workspaces
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Switch between your active production workspaces, personal development sandboxes, or join another engineering team.
            </p>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('organizations.apply') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-900 to-[#1B6B3A] text-white font-bold text-xs shadow hover:brightness-110 transition-all border border-emerald-700/40">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Register Organization</span>
            </a>
        </div>
    </div>

    {{-- Active Workspace Banner --}}
    @if($activeWorkspace)
    <div class="p-5 rounded-2xl bg-[#0F1A14] border border-emerald-900/60 shadow-xl relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-40 h-40 bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#1B6B3A] to-emerald-950 border border-[#F5C518]/50 flex items-center justify-center text-white shadow-md">
                    @if($activeWorkspace->isPersonal())
                        <svg class="w-6 h-6 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @else
                        <svg class="w-6 h-6 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-950 text-emerald-300 border border-emerald-700/50 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>ACTIVE TENANT CONTEXT</span>
                        </span>
                        @if($activeWorkspace->isPersonal())
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-950 text-indigo-300 border border-indigo-700/50">Personal</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-yellow-950 text-[#F5C518] border border-yellow-700/50">{{ $activeWorkspace->organization?->name ?? 'Organization' }}</span>
                        @endif
                    </div>
                    <h2 class="text-lg sm:text-xl font-black text-white mt-1">
                        {{ $activeWorkspace->name }}
                    </h2>
                    <p class="text-xs text-gray-400 font-mono mt-0.5">
                        Slug: {{ $activeWorkspace->slug }} &bull; Subdomain: {{ $activeWorkspace->subdomain ?? 'none' }}
                    </p>
                </div>
            </div>

            <a href="{{ route('activities.daily') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition-colors self-start sm:self-auto">
                <span>Enter Operations Board &rarr;</span>
            </a>
        </div>
    </div>
    @endif

    {{-- Grid: My Workspaces & Join Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Column 1 & 2: Available Workspaces --}}
        <div class="lg:col-span-2 space-y-4">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider font-mono flex items-center gap-2">
                <span>Accessible Workspaces</span>
                <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-xs font-semibold">{{ $workspaces->count() }}</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($workspaces as $ws)
                    @php
                        $isActive = $activeWorkspace && $activeWorkspace->id === $ws->id;
                    @endphp
                    <div class="p-4 rounded-xl border transition-all relative flex flex-col justify-between
                                {{ $isActive ? 'bg-emerald-950/20 dark:bg-emerald-950/30 border-emerald-600 shadow-md ring-1 ring-emerald-500/30' : 'bg-white dark:bg-[#16241B] border-gray-200 dark:border-white/10 hover:border-emerald-700/60' }}">

                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold
                                             {{ $ws->isPersonal() ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                                    {{ $ws->isPersonal() ? 'Personal Space' : ($ws->organization?->name ?? 'Organization') }}
                                </span>

                                @if($isActive)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 font-mono">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Current</span>
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-base font-black text-gray-900 dark:text-white mt-2">
                                {{ $ws->name }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-mono">
                                Role: <span class="font-bold text-gray-700 dark:text-gray-300 capitalize">{{ $ws->pivot->role ?? 'agent' }}</span>
                            </p>

                            @if($ws->organization)
                                <div class="mt-2 text-[11px] font-mono text-gray-500 dark:text-gray-400">
                                    Company Code: <code class="px-1 py-0.5 rounded bg-black/10 dark:bg-black/30 font-bold text-[#1B6B3A] dark:text-[#F5C518]">{{ $ws->organization->company_code }}</code>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                            <span class="text-[10px] font-mono text-gray-400 capitalize">{{ $ws->status }}</span>

                            @if(! $isActive)
                                <form method="POST" action="{{ route('workspaces.switch') }}">
                                    @csrf
                                    <input type="hidden" name="workspace_id" value="{{ $ws->id }}">
                                    <button type="submit"
                                            class="px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 hover:bg-[#1B6B3A] text-gray-700 hover:text-white dark:bg-white/5 dark:hover:bg-[#1B6B3A] dark:text-gray-300 transition-colors cursor-pointer">
                                        Switch &rarr;
                                    </button>
                                </form>
                            @else
                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Active</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 rounded-xl border border-dashed border-gray-300 dark:border-white/10 text-center col-span-2">
                        <p class="text-sm text-gray-500">No workspaces found.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Column 3: Join & Fast Actions --}}
        <div class="space-y-6">

            {{-- Join Organization by Company Code --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-lg bg-[#F5C518]/20 border border-[#F5C518]/40 text-[#F5C518] flex items-center justify-center text-xs font-bold font-mono">
                        #
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        Join by Company Code
                    </h3>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Enter the organization company code provided by your lead or onboarding email to automatically join their operations workspace.
                </p>

                <form method="POST" action="{{ route('workspaces.join') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="company_code" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Company Code</label>
                        <input type="text"
                               id="company_code"
                               name="company_code"
                               placeholder="e.g. NPT-OPS-01"
                               class="w-full px-3 py-2 rounded-xl text-xs font-mono uppercase bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none"
                               required>
                    </div>
                    <button type="submit"
                            class="w-full py-2 px-4 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs transition-colors shadow-sm cursor-pointer">
                        Verify Code &amp; Join Workspace
                    </button>
                </form>
            </div>

            {{-- Provision Personal Sandbox --}}
            @if(! $personalWorkspace)
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">
                    Personal SRE Sandbox
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-3">
                    Provision your private isolated workspace for testing runbooks, simulated incidents, and custom scripts.
                </p>
                <form method="POST" action="{{ route('workspaces.store') }}">
                    @csrf
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}'s Sandbox">
                    <input type="hidden" name="is_personal" value="1">
                    <button type="submit"
                            class="w-full py-2 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-800 dark:text-gray-200 font-bold text-xs transition-colors border border-gray-300 dark:border-white/10 cursor-pointer">
                        Provision My Personal Sandbox
                    </button>
                </form>
            </div>
            @endif

        </div>

    </div>

</div>
@endsection
