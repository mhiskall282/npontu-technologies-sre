{{-- Sleek Collapsible Navigation --}}
<div class="space-y-3" x-data="{
    openOps: {{ request()->routeIs('activities.*') ? 'true' : 'true' }},
    openSup: {{ request()->routeIs('admin.*') || request()->routeIs('monitoring.*') || request()->routeIs('reports.*') ? 'true' : 'true' }}
}">
    {{-- Operations Submenu --}}
    <div class="rounded-xl bg-white/[0.02] border border-white/5 overflow-hidden">
        <button type="button"
                @click="openOps = !openOps"
                class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono hover:text-white transition-colors">
            <div class="flex items-center gap-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Operations</span>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': openOps }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="openOps" class="px-1.5 pb-1.5 space-y-0.5">
            {{-- Today's Board --}}
            <a href="{{ route('activities.daily') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('activities.daily') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('activities.daily') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="truncate">Today's Board</span>
                @if(request()->routeIs('activities.daily'))
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#F5C518]"></span>
                @endif
            </a>

            {{-- Activities Registry --}}
            <a href="{{ route('activities.index') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('activities.*') && !request()->routeIs('activities.daily') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('activities.*') && !request()->routeIs('activities.daily') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span class="truncate">All Activities</span>
            </a>

            {{-- Workspaces Directory --}}
            <a href="{{ route('workspaces.index') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('workspaces.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('workspaces.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="truncate">Workspaces</span>
            </a>
        </div>
    </div>

    {{-- Communications --}}
    @php $unreadCount = auth()->check() ? auth()->user()->unreadMessagesCount() : 0; @endphp
    <a href="{{ route('messages.index') }}"
       class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('messages.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <div class="flex items-center gap-2.5 min-w-0">
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

    {{-- Supervisory Submenu (Strictly role-gated) --}}
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isLead() || auth()->user()->canManageActivities() || auth()->user()->hasPrivilege('export_reports')))
    <div class="rounded-xl bg-white/[0.02] border border-white/5 overflow-hidden">
        <button type="button"
                @click="openSup = !openSup"
                class="w-full flex items-center justify-between px-3 py-2 text-[10px] font-bold tracking-wider text-gray-400 uppercase font-mono hover:text-white transition-colors">
            <span>Supervisory</span>
            <svg class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': openSup }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="openSup" class="px-1.5 pb-1.5 space-y-0.5">
            {{-- Reports (Strictly gated by export_reports or lead/admin) --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isLead() || auth()->user()->hasPrivilege('export_reports'))
            <a href="{{ route('reports.index') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('reports.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('reports.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="truncate">Reports</span>
            </a>
            @endif

            {{-- Admin Console --}}
            @if(auth()->user()->isAdmin() || auth()->user()->canManageActivities())
            <a href="{{ route('admin.activities.index') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('admin.activities.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.activities.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="truncate">Admin Console</span>
            </a>

            <a href="{{ route('admin.organizations.applications') }}"
               class="group flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('admin.organizations.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.organizations.*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span class="truncate">Org Applications</span>
            </a>
            @endif

            {{-- Monitoring HUD --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isLead())
            <a href="{{ route('monitoring.index') }}"
               class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-xs font-semibold transition-all duration-150
                      {{ request()->routeIs('monitoring.*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
                <div class="flex items-center gap-2.5 min-w-0">
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
            @endif
        </div>
    </div>
    @endif

    {{-- Docs & Architecture Guide --}}
    <a href="{{ route('docs') }}"
       class="group flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
              {{ request()->routeIs('docs*') ? 'bg-[#1B6B3A] text-white shadow-sm border-l-4 border-[#F5C518]' : 'text-gray-300 hover:text-white hover:bg-[#1A2E22]' }}">
        <div class="flex items-center gap-2.5 min-w-0">
            <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('docs*') ? 'text-[#F5C518]' : 'text-gray-400 group-hover:text-gray-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="truncate">Docs &amp; Guide</span>
        </div>
        <span class="px-1.5 py-0.5 rounded text-[10px] font-mono text-[#F5C518] bg-yellow-900/30 border border-[#F5C518]/30">
            SRE
        </span>
    </a>
</div>

{{-- Subtle System Status Footer in Sidebar (Outsiders & Quick Check) --}}
<div class="pt-3 mt-4 border-t border-white/5">
    <a href="{{ route('health') }}"
       class="group flex items-center justify-between px-3 py-2 rounded-xl text-[11px] font-mono text-gray-400 hover:text-emerald-300 hover:bg-emerald-950/40 border border-transparent hover:border-emerald-800/30 transition-all">
        <span class="flex items-center gap-2 truncate">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Uptime: 99.98%</span>
        </span>
        <span class="text-xs text-gray-500 group-hover:text-emerald-400">&rarr;</span>
    </a>
</div>
