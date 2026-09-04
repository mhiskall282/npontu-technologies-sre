{{-- Core Operations --}}
<div class="space-y-1">
    <div class="px-3 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono flex items-center justify-between">
        <span>Operations</span>
        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" title="Shift active"></span>
    </div>

    {{-- Today's Board --}}
    <a href="{{ route('activities.daily') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('activities.daily') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('activities.daily') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="truncate">Today's Board</span>
        @if(request()->routeIs('activities.daily'))
            <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#F5C518]"></span>
        @endif
    </a>

    {{-- Activities --}}
    <a href="{{ route('activities.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('activities.*') && !request()->routeIs('activities.daily') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('activities.*') && !request()->routeIs('activities.daily') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <span class="truncate">Activities</span>
    </a>

    {{-- Reports --}}
    <a href="{{ route('reports.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('reports.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('reports.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <span class="truncate">Reports</span>
    </a>
</div>

{{-- Communications --}}
<div class="space-y-1 pt-3">
    <div class="px-3 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono">
        Comms & Dispatch
    </div>

    {{-- Ops Comms --}}
    @php $unreadCount = auth()->check() ? auth()->user()->unreadMessagesCount() : 0; @endphp
    <a href="{{ route('messages.index') }}"
       class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('messages.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('messages.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="truncate">Ops Comms</span>
        </div>
        @if($unreadCount > 0)
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F5C518] opacity-90"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F5C518]"></span>
            </span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-extrabold bg-[#E63946] text-white shadow ring-1 ring-[#F5C518]">
                {{ $unreadCount }}
            </span>
        </div>
        @endif
    </a>
</div>

{{-- SRE Management & Supervisory --}}
@if(auth()->check() && auth()->user()->canManageActivities())
<div class="space-y-1 pt-3">
    <div class="px-3 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono">
        Supervisory
    </div>

    {{-- Admin Console --}}
    <a href="{{ route('admin.activities.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('admin.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <span class="truncate">Admin</span>
    </a>

    {{-- Monitoring --}}
    <a href="{{ route('monitoring.index') }}"
       class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('monitoring.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('monitoring.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="truncate">Monitoring</span>
        </div>
        <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
        </span>
    </a>
</div>
@endif

{{-- System Telemetry --}}
<div class="space-y-1 pt-3">
    <div class="px-3 pb-1 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono">
        Telemetry
    </div>

    {{-- System Health --}}
    <a href="{{ route('health') }}"
       class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('health*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <div class="flex items-center gap-3 min-w-0">
            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('health*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
            </svg>
            <span class="truncate">System Health</span>
        </div>
        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono text-emerald-300 bg-emerald-900/40 border border-emerald-500/20">
            Live
        </span>
    </a>
</div>
