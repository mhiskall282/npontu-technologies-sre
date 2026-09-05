@extends('errors.layout')

@section('title', '403 — Access Forbidden')

@section('content')
<div class="space-y-6">
    {{-- Status Code Badge --}}
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-xs font-mono font-bold tracking-wider uppercase">
        <span class="w-2 h-2 rounded-full bg-[#E63946]"></span>
        <span>403 &bull; ACCESS FORBIDDEN</span>
    </div>

    {{-- Icon & Heading --}}
    <div>
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-red-500/20 to-red-600/10 border border-red-500/30 flex items-center justify-center mx-auto mb-4 shadow-inner">
            <svg class="w-8 h-8 text-[#E63946]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Privileged Action Restricted</h1>
        <p class="text-sm text-gray-400 mt-2 max-w-md mx-auto leading-relaxed">
            Your current operational role or SRE technical grade does not possess authorization to view or mutate this resource.
        </p>
    </div>

    <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-left max-w-md mx-auto text-xs text-gray-300">
        <p class="font-bold text-white mb-1">Need higher privilege?</p>
        <p class="text-gray-400 leading-relaxed">
            Contact your Lead or System Administrator (<span class="font-mono text-emerald-400">admin@npontu.local</span>) to grant the required capability flag (e.g. channel creation, task reassignment, or handover sign-off).
        </p>
    </div>

    {{-- Primary CTA --}}
    <div class="pt-2">
        <a href="{{ route('activities.daily') }}"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-[#2A8F52] text-white font-bold text-xs transition-colors shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Return to Today's Board</span>
        </a>
    </div>
</div>
@endsection
