<div>
    {{-- ── Date picker header & Handover Trigger ───────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Daily Activity Board</h1>
                @if($date === today()->format('Y-m-d'))
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#F5C518] text-gray-900 shadow-xs">
                        LIVE SHIFT
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-0.5">SRE Shift Handover & Operational Checklist — {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span wire:loading.flex wire:target="refreshBoard,date,search,category,assigneeFilter,bulkAssign" class="items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-[#1B6B3A]">
                <svg class="animate-spin h-3 w-3 text-[#1B6B3A]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Syncing...
            </span>

            @if(auth()->user()->canManageActivities())
            <button type="button"
                    wire:click="openHandoverModal"
                    class="px-3.5 py-1.5 text-xs font-bold bg-[#1B6B3A] text-white rounded-lg hover:bg-[#15532D] transition-colors shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Shift Handover Sign-Off</span>
            </button>
            @endif

            <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-gray-200 shadow-xs">
                <label for="board-date" class="text-xs font-semibold text-gray-600">Shift Date:</label>
                <input type="date"
                       id="board-date"
                       wire:model.live="date"
                       max="{{ today()->format('Y-m-d') }}"
                       class="rounded border-gray-200 text-xs text-gray-800 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] py-1 px-2">
            </div>
        </div>
    </div>

    {{-- ── Flash messages ──────────────────────────────────────── --}}
    @if(session('success'))
    <div class="mb-6 transition-all duration-300">
        <x-alert type="success" :message="session('success')" />
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 transition-all duration-300">
        <x-alert type="error" :message="session('error')" />
    </div>
    @endif

    {{-- ── SRE Shift Handover Briefing Banner ──────────────────── --}}
    @if($shiftHandovers->isNotEmpty())
    <div class="mb-6 bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden">
        <div class="bg-gradient-to-r from-[#12492A] to-[#1B6B3A] p-4 text-white flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-lg bg-white/10 text-[#F5C518]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-bold tracking-wide uppercase">Operational Shift Handover Briefing</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-[#F5C518] text-gray-900">
                            {{ ucfirst($shiftHandovers->first()->shift) }} Shift
                        </span>
                    </div>
                    <p class="text-xs text-emerald-100 mt-0.5">
                        Signed off by <span class="font-semibold text-white">{{ $shiftHandovers->first()->outgoingLead?->name }}</span>
                        @if($shiftHandovers->first()->incomingLead)
                            → Handed over to <span class="font-semibold text-white">{{ $shiftHandovers->first()->incomingLead?->name }}</span>
                        @endif
                        at {{ $shiftHandovers->first()->signed_at?->format('H:i \G\M\T') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono">
                <span class="px-2.5 py-1 rounded bg-white/10 border border-white/20">
                    Pending at sign-off: <strong class="text-amber-300">{{ $shiftHandovers->first()->pending_tasks_count }}</strong>
                </span>
                <span class="px-2.5 py-1 rounded bg-white/10 border border-white/20">
                    Done: <strong class="text-emerald-300">{{ $shiftHandovers->first()->completed_tasks_count }}</strong>
                </span>
            </div>
        </div>
        <div class="p-4 bg-emerald-50/30 text-gray-800 space-y-3">
            <div>
                <h3 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Handover Summary / Operational Notes</h3>
                <p class="text-sm text-gray-800 mt-1 whitespace-pre-line leading-relaxed">{{ $shiftHandovers->first()->summary }}</p>
            </div>
            @if($shiftHandovers->first()->incidents)
            <div class="pt-2 border-t border-emerald-100">
                <h3 class="text-[11px] font-bold text-red-700 uppercase tracking-wider flex items-center gap-1">
                    <span>🚨</span> Open Incidents & Discrepancies Noted
                </h3>
                <p class="text-xs text-red-900 mt-1 whitespace-pre-line font-medium leading-relaxed bg-red-50 p-2.5 rounded-lg border border-red-200">
                    {{ $shiftHandovers->first()->incidents }}
                </p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Role-based Welcome & Console ────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 mb-6">
        @if(auth()->user()->isAdmin())
        <div class="bg-gradient-to-br from-[#12492A] to-[#1B6B3A] rounded-xl shadow-sm p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="bg-[#F5C518] text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Administrator Console</span>
                <h2 class="text-xl font-bold mt-2">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-green-100 text-sm mt-1">Manage system configurations, user access, and oversee SRE shift operations.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 bg-white text-[#1B6B3A] hover:bg-green-50 text-sm font-bold rounded-lg transition-colors shadow-sm">
                    Manage Activities
                </a>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-[#F5C518] text-gray-900 hover:bg-[#E0B310] text-sm font-bold rounded-lg transition-colors shadow-sm">
                    Manage Users
                </a>
            </div>
        </div>
        @elseif(auth()->user()->isLead())
        <div class="bg-gradient-to-br from-[#12492A] to-[#1B6B3A] rounded-xl shadow-sm p-6 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="bg-emerald-400 text-gray-900 text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Team Lead Dashboard</span>
                <h2 class="text-xl font-bold mt-2">Welcome back, {{ auth()->user()->name }}</h2>
                <p class="text-green-100 text-sm mt-1">Review shift handovers, delegate checks, and sign off operational handovers.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.activities.index') }}" class="px-4 py-2 bg-white text-[#1B6B3A] hover:bg-green-50 text-sm font-bold rounded-lg transition-colors shadow-sm">
                    Create/Edit Checks
                </a>
                <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-[#F5C518] text-gray-900 hover:bg-[#E0B310] text-sm font-bold rounded-lg transition-colors shadow-sm">
                    System Reports
                </a>
            </div>
        </div>
        @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="bg-slate-100 text-gray-700 text-xs font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider">Operator Portal</span>
                <h2 class="text-xl font-bold text-gray-900 mt-2">Hello, {{ auth()->user()->name }}</h2>
                <p class="text-gray-500 text-sm mt-1">You are logged in as a Support Operator. Review and complete shift checkoff duties below.</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Current Designation</p>
                <p class="text-sm font-bold text-gray-800">{{ auth()->user()->designation ?? 'Support Operator' }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── SRE Stats Bar ───────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-3.5 border border-gray-100 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $totalActivitiesCount }}</p>
            <p class="text-[11px] text-gray-500 mt-1 uppercase tracking-wider font-semibold">Total Checks</p>
        </div>
        <div class="bg-amber-50 rounded-lg shadow-sm p-3.5 border border-[#F5C518] border-opacity-50 text-center">
            <p class="text-2xl font-bold text-[#E63946]">{{ $totalPendingCount }}</p>
            <p class="text-[11px] text-gray-500 mt-1 uppercase tracking-wider font-semibold">Needs Attention</p>
        </div>
        <div class="bg-green-50 rounded-lg shadow-sm p-3.5 border border-[#1B6B3A] border-opacity-30 text-center">
            <p class="text-2xl font-bold text-[#1B6B3A]">{{ $totalDoneCount }}</p>
            <p class="text-[11px] text-gray-500 mt-1 uppercase tracking-wider font-semibold">Completed</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow-sm p-3.5 border border-red-200 text-center {{ $criticalPendingCount > 0 ? 'ring-2 ring-red-300' : '' }}">
            <div class="flex items-center justify-center gap-1">
                <p class="text-2xl font-bold text-red-600">{{ $criticalPendingCount }}</p>
                @if($criticalPendingCount > 0)
                <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                @endif
            </div>
            <p class="text-[11px] text-red-700 mt-1 uppercase tracking-wider font-bold">P1/P2 Critical</p>
        </div>
        <div class="bg-purple-50 rounded-lg shadow-sm p-3.5 border border-purple-200 text-center">
            <p class="text-2xl font-bold text-purple-700">{{ $escalatedCount }}</p>
            <p class="text-[11px] text-purple-800 mt-1 uppercase tracking-wider font-semibold">Escalations</p>
        </div>
        <div wire:click="setAssigneeFilter('me')"
             class="cursor-pointer rounded-lg shadow-sm p-3.5 border transition-all text-center {{ $assigneeFilter === 'me' ? 'bg-[#F5C518] border-[#E0B310] text-gray-900 ring-2 ring-[#F5C518]/50' : 'bg-white border-gray-100 hover:border-[#F5C518]' }}">
            <div class="flex items-center justify-center gap-1.5">
                <p class="text-2xl font-bold {{ $assigneeFilter === 'me' ? 'text-gray-900' : 'text-[#1B6B3A]' }}">{{ $myTasksCount }}</p>
                @if($myPendingTasksCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#E63946] text-white animate-pulse">
                    {{ $myPendingTasksCount }}
                </span>
                @endif
            </div>
            <p class="text-[11px] mt-1 uppercase tracking-wider font-semibold {{ $assigneeFilter === 'me' ? 'text-gray-900 font-bold' : 'text-gray-500' }}">
                My Tasks {{ $assigneeFilter === 'me' ? '✓' : '' }}
            </p>
        </div>
    </div>

    @php
        $completionRate = $totalActivitiesCount > 0 ? round(($totalDoneCount / $totalActivitiesCount) * 100) : 0;
    @endphp
    @if($totalActivitiesCount > 0)
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
        <div class="flex items-center justify-between text-xs font-semibold text-gray-700 mb-2">
            <span class="uppercase tracking-wider">Shift Completion Progress</span>
            <span class="text-[#1B6B3A] font-bold">{{ $completionRate }}% ({{ $totalDoneCount }} of {{ $totalActivitiesCount }} checks completed)</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="bg-gradient-to-r from-[#1B6B3A] to-[#2A8F52] h-full rounded-full transition-all duration-500 ease-out"
                 style="width: {{ $completionRate }}%"></div>
        </div>
    </div>
    @endif

    {{-- ── Search & Filter Controls ────────────────────────────── --}}
    @if($totalActivitiesCount > 0)
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100 space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.250ms="search"
                       placeholder="Filter checks by title, description, category, or assigned engineer..."
                       class="w-full pl-9 pr-8 py-2 text-xs md:text-sm rounded-lg border border-gray-200 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A] placeholder-gray-400">
                @if($search !== '')
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                @endif
            </div>

            {{-- Assignee Delegation Filters --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs flex-wrap">
                <span class="text-gray-400 font-medium mr-1 text-[11px] uppercase tracking-wider">Assignee:</span>
                <button type="button"
                        wire:click="setAssigneeFilter('')"
                        class="px-2.5 py-1.5 rounded-lg font-medium transition-colors whitespace-nowrap {{ $assigneeFilter === '' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    All
                </button>
                <button type="button"
                        wire:click="setAssigneeFilter('me')"
                        class="px-2.5 py-1.5 rounded-lg font-semibold transition-colors whitespace-nowrap flex items-center gap-1 {{ $assigneeFilter === 'me' ? 'bg-[#F5C518] text-gray-900 shadow-xs font-bold' : 'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100' }}">
                    <span>Assigned to Me</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $assigneeFilter === 'me' ? 'bg-gray-900 text-white' : 'bg-amber-200 text-amber-900' }}">{{ $myTasksCount }}</span>
                </button>
                <button type="button"
                        wire:click="setAssigneeFilter('unassigned')"
                        class="px-2.5 py-1.5 rounded-lg font-medium transition-colors whitespace-nowrap {{ $assigneeFilter === 'unassigned' ? 'bg-[#1B6B3A] text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Shift Pool ({{ $unassignedCount }})
                </button>
                @if(isset($teamMembers) && $teamMembers->isNotEmpty())
                <select wire:model.live="assigneeFilter"
                        class="px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-white text-gray-700 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                    <option value="">Specific Team Member...</option>
                    @foreach($teamMembers as $tm)
                    <option value="{{ $tm->id }}">{{ $tm->name }} ({{ ucfirst($tm->role) }})</option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>

        {{-- Categories and Reset Bar --}}
        <div class="flex items-center justify-between border-t border-gray-50 pt-2.5 flex-wrap gap-2 text-xs">
            @if($categories->isNotEmpty())
            <div class="flex items-center gap-1.5 overflow-x-auto flex-wrap">
                <span class="text-gray-400 font-medium mr-1 text-[11px] uppercase tracking-wider">Category:</span>
                <button type="button"
                        wire:click="$set('category', '')"
                        class="px-2 py-1 rounded-md font-medium transition-colors {{ $category === '' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    All
                </button>
                @foreach($categories as $cat)
                <button type="button"
                        wire:click="$set('category', '{{ $cat }}')"
                        class="px-2 py-1 rounded-md font-medium transition-colors {{ $category === $cat ? 'bg-[#1B6B3A] text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    {{ $cat }}
                </button>
                @endforeach
            </div>
            @endif

            @if($search !== '' || $category !== '' || $assigneeFilter !== '')
            <button type="button"
                    wire:click="clearFilters"
                    class="px-2.5 py-1 text-xs text-red-600 hover:text-red-700 font-semibold flex items-center gap-1 ml-auto">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Reset All Filters
            </button>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Bulk Reassignment Action Bar (Admins & Leads) ───────── --}}
    @if(auth()->user()->canManageActivities() && count($selectedActivities) > 0)
    <div class="fixed bottom-6 inset-x-0 z-40 max-w-2xl mx-auto px-4">
        <div class="bg-gray-900 text-white rounded-xl shadow-2xl p-4 border border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-3 animate-slide-up">
            <div class="flex items-center gap-2">
                <span class="bg-[#F5C518] text-gray-900 text-xs font-black px-2 py-0.5 rounded-full">
                    {{ count($selectedActivities) }}
                </span>
                <span class="text-xs font-semibold">check(s) selected for delegation</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select id="bulk-assignee-select"
                        class="text-xs py-1.5 px-2.5 rounded-lg bg-gray-800 border border-gray-700 text-white focus:ring-1 focus:ring-[#F5C518]">
                    <option value="">— Select Assignee —</option>
                    <option value="unassign">Return to Shift Pool</option>
                    @foreach($teamMembers as $tm)
                    <option value="{{ $tm->id }}">{{ $tm->name }} ({{ ucfirst($tm->role) }})</option>
                    @endforeach
                </select>

                <button type="button"
                        onclick="const val = document.getElementById('bulk-assignee-select').value; if(val === 'unassign') { @this.bulkAssign(null); } else if(val) { @this.bulkAssign(parseInt(val)); }"
                        class="px-3 py-1.5 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-xs font-bold rounded-lg transition-colors shadow-xs">
                    Delegate
                </button>

                <button type="button"
                        wire:click="$set('selectedActivities', [])"
                        class="px-2.5 py-1.5 text-xs text-gray-400 hover:text-white transition-colors">
                    Clear
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($pending->isEmpty() && $done->isEmpty())
        <div class="bg-white rounded-lg shadow-sm p-12 text-center border border-gray-100">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            @if($search !== '' || $category !== '' || $assigneeFilter !== '')
                <p class="text-gray-700 font-medium">No operational checks match your current filters.</p>
                <p class="text-gray-400 text-sm mt-1">Try resetting the assignee filter or broadening your search keywords.</p>
                <button wire:click="clearFilters" class="mt-4 px-4 py-1.5 text-xs font-semibold text-[#1B6B3A] border border-[#1B6B3A] rounded-lg hover:bg-green-50 transition-colors">
                    Clear All Filters
                </button>
            @else
                <p class="text-gray-500 font-medium">No activities found for this date.</p>
                <p class="text-gray-400 text-sm mt-1">Activities created in Admin will appear here.</p>
            @endif
        </div>
    @else

    {{-- ── PENDING SECTION ─────────────────────────────────────── --}}
    @if($pending->isNotEmpty())
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#E63946]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <h2 class="text-sm font-bold text-[#E63946] uppercase tracking-widest">Needs Attention</h2>
                </div>
                <span class="bg-[#E63946] text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $pending->count() }}</span>
            </div>

            @if(auth()->user()->canManageActivities() && $pending->isNotEmpty())
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <label class="flex items-center gap-1.5 cursor-pointer font-medium hover:text-gray-800">
                    <input type="checkbox"
                           wire:model.live="selectAll"
                           class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                    <span>Select all pending ({{ count($visiblePendingIds) }})</span>
                </label>
            </div>
            @endif
        </div>

        <div class="space-y-3">
            @foreach($pending as $activity)
            <div class="bg-white rounded-lg shadow-sm border-l-4 {{ $activity->priority === 'critical' ? 'border-red-600 ring-1 ring-red-300' : ($activity->assigned_to === auth()->id() ? 'border-[#F5C518] ring-1 ring-[#F5C518]/60 bg-amber-50/15' : 'border-[#F5C518]') }} overflow-hidden"
                 wire:key="pending-{{ $activity->id }}">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            {{-- Multi-select checkbox for supervisor delegation --}}
                            @if(auth()->user()->canManageActivities())
                            <div class="pt-0.5">
                                <input type="checkbox"
                                       wire:model.live="selectedActivities"
                                       value="{{ $activity->id }}"
                                       class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A] h-4 w-4">
                            </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    {{-- Pinned indicator --}}
                                    @if($activity->is_pinned)
                                    <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        📌 PINNED
                                    </span>
                                    @endif

                                    <h3 class="font-semibold text-gray-900 truncate">{{ $activity->title }}</h3>

                                    {{-- Priority Badge --}}
                                    @if($activity->priority === 'critical')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-600 text-white uppercase tracking-wider shadow-xs animate-pulse">
                                        P1 CRITICAL
                                    </span>
                                    @elseif($activity->priority === 'high')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white uppercase tracking-wider shadow-xs">
                                        P2 HIGH
                                    </span>
                                    @elseif($activity->priority === 'low')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">
                                        P4 LOW
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800">
                                        P3 MEDIUM
                                    </span>
                                    @endif

                                    {{-- SLA Target Time Badge --}}
                                    @if($activity->sla_time)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-mono bg-purple-50 text-purple-700 border border-purple-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        SLA: {{ $activity->sla_time }} GMT
                                    </span>
                                    @endif

                                    @if($activity->category)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">{{ $activity->category }}</span>
                                    @endif
                                    <x-status-badge status="pending" />

                                    {{-- Assignee Badging --}}
                                    @if($activity->assigned_to === auth()->id())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#F5C518] text-gray-900 shadow-xs">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                        Assigned to You
                                    </span>
                                    @elseif($activity->assignee)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-[#1B6B3A] border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1B6B3A]"></span>
                                        {{ $activity->assignee->name }}
                                        <span class="text-gray-400 text-[10px]">({{ ucfirst($activity->assignee->role) }})</span>
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500 italic">
                                        Shift Pool (Unassigned)
                                    </span>
                                    @endif
                                </div>

                                @if($activity->description)
                                <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $activity->description }}</p>
                                @endif

                                {{-- Active Incident Alert on Card --}}
                                @if($activity->latest_log && ($activity->latest_log->is_escalated || $activity->latest_log->incident_ticket))
                                <div class="mt-2 p-2 bg-red-50 border-l-4 border-red-500 rounded-r text-xs text-red-900 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 font-bold">
                                        <span>🚨</span>
                                        <span>ACTIVE ESCALATION:</span>
                                        <span>{{ $activity->latest_log->incident_ticket ? 'Ticket #' . $activity->latest_log->incident_ticket : 'Incident Flagged' }}</span>
                                    </div>
                                    @if($activity->latest_log->remark)
                                    <span class="text-[11px] italic font-normal text-red-700 truncate max-w-md">"{{ $activity->latest_log->remark }}"</span>
                                    @endif
                                </div>
                                @endif

                                {{-- Supervisor Inline Quick-Assign Menu (Admins & Leads) --}}
                                @if(auth()->user()->canManageActivities() && isset($teamMembers))
                                <div class="mt-3 pt-2 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                                    <span class="font-medium text-gray-600">Reassign:</span>
                                    <select wire:change="assignActivity({{ $activity->id }}, $event.target.value ? parseInt($event.target.value) : null)"
                                            class="py-1 px-2 text-xs rounded-md border-gray-200 bg-gray-50 text-gray-800 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                                        <option value="" {{ empty($activity->assigned_to) ? 'selected' : '' }}>— Shift Pool (Unassigned) —</option>
                                        @foreach($teamMembers as $tm)
                                        <option value="{{ $tm->id }}" {{ $activity->assigned_to == $tm->id ? 'selected' : '' }}>
                                            {{ $tm->name }} ({{ ucfirst($tm->role) }}{{ $tm->designation ? ' - ' . $tm->designation : '' }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            @livewire('activity-status-updater', [
                                'activity'      => $activity,
                                'date'          => $date,
                                'currentStatus' => 'pending',
                            ], key("updater-pending-{$activity->id}-{$date}"))
                        </div>
                    </div>

                    {{-- Timeline of today's updates --}}
                    @if($activity->day_logs->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Update History</p>
                        @foreach($activity->day_logs as $log)
                            <x-activity-timeline-item :log="$log" />
                        @endforeach
                    </div>
                    @else
                    <p class="mt-3 text-xs text-gray-400 italic">No updates yet today.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── DONE SECTION (COMPLETED) ─────────────────────────────── --}}
    @if($done->isNotEmpty())
    <section>
        <div class="flex items-center gap-3 mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-[#1B6B3A]" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-sm font-bold text-[#1B6B3A] uppercase tracking-widest">Completed</h2>
            </div>
            <span class="bg-[#1B6B3A] text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $done->count() }}</span>
            <div class="flex-1 h-px bg-green-200"></div>
        </div>

        <div class="space-y-3">
            @foreach($done as $activity)
            <div class="bg-white rounded-lg shadow-sm border-l-4 border-[#1B6B3A] border-r border-t border-b border-gray-100 overflow-hidden"
                 wire:key="done-{{ $activity->id }}"
                 x-data="{ showHistory: false }">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($activity->is_pinned)
                                <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    📌 PINNED
                                </span>
                                @endif

                                <h3 class="font-semibold text-gray-900 truncate">{{ $activity->title }}</h3>

                                @if($activity->category)
                                <span class="text-xs bg-green-50 text-[#1B6B3A] border border-green-200 px-2 py-0.5 rounded-full font-medium">{{ $activity->category }}</span>
                                @endif
                                <x-status-badge status="done" />

                                @if($activity->assigned_to === auth()->id())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#F5C518] text-gray-900 shadow-xs">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                    Assigned to You
                                </span>
                                @elseif($activity->assignee)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-[#1B6B3A] border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#1B6B3A]"></span>
                                    {{ $activity->assignee->name }}
                                </span>
                                @endif
                            </div>

                            @if($activity->description)
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $activity->description }}</p>
                            @endif

                            @if($activity->latest_log && $activity->latest_log->actor_name)
                            <div class="mt-2.5 flex items-start gap-2 text-xs text-gray-600 bg-gray-50 rounded-md p-2 border border-gray-100">
                                <svg class="w-4 h-4 text-[#1B6B3A] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div class="flex-1">
                                    <p>
                                        Completed by <span class="font-semibold text-gray-900">{{ $activity->latest_log->actor_name }}</span>
                                        <span class="text-gray-400">({{ $activity->latest_log->actor_role }}{{ $activity->latest_log->actor_designation ? ' · ' . $activity->latest_log->actor_designation : '' }})</span>
                                        at <span class="font-mono text-gray-700 font-medium">{{ $activity->latest_log->created_at->format('H:i') }}</span>
                                    </p>
                                    @if($activity->latest_log->remark)
                                    <p class="text-gray-700 mt-1 italic leading-relaxed">
                                        "{{ $activity->latest_log->remark }}"
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Supervisor Reassignment for Completed tasks --}}
                            @if(auth()->user()->canManageActivities() && isset($teamMembers))
                            <div class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                                <span class="font-medium text-gray-600">Reassign:</span>
                                <select wire:change="assignActivity({{ $activity->id }}, $event.target.value ? parseInt($event.target.value) : null)"
                                        class="py-0.5 px-2 text-xs rounded-md border-gray-200 bg-gray-50 text-gray-800 focus:ring-1 focus:ring-[#1B6B3A]">
                                    <option value="" {{ empty($activity->assigned_to) ? 'selected' : '' }}>— Shift Pool —</option>
                                    @foreach($teamMembers as $tm)
                                    <option value="{{ $tm->id }}" {{ $activity->assigned_to == $tm->id ? 'selected' : '' }}>
                                        {{ $tm->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            @if($activity->day_logs->count() > 1)
                            <button type="button"
                                    @click="showHistory = !showHistory"
                                    class="mt-2 text-xs text-[#1B6B3A] hover:underline font-medium flex items-center gap-1">
                                <span x-text="showHistory ? 'Hide earlier updates' : 'View all {{ $activity->day_logs->count() }} updates today'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform" :class="showHistory ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            @endif
                        </div>

                        <div class="flex-shrink-0">
                            @livewire('activity-status-updater', [
                                'activity'      => $activity,
                                'date'          => $date,
                                'currentStatus' => 'done',
                            ], key("updater-done-{$activity->id}-{$date}"))
                        </div>
                    </div>

                    {{-- Collapsible timeline of all updates today if multiple --}}
                    @if($activity->day_logs->isNotEmpty())
                    <div x-show="showHistory" x-cloak class="mt-4 pt-4 border-t border-gray-100 space-y-3">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Full Update History Today</p>
                        @foreach($activity->day_logs as $log)
                            <x-activity-timeline-item :log="$log" />
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    @endif

    {{-- ── Shift Handover Sign-Off Modal ───────────────────────── --}}
    @if($showHandoverModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 text-left border border-gray-100 animate-scale-in"
             x-data x-trap="true">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-lg bg-green-50 text-[#1B6B3A]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">SRE Shift Handover Sign-Off</h3>
                        <p class="text-xs text-gray-500">Record formal operational briefing for incoming shift lead</p>
                    </div>
                </div>
                <button type="button" wire:click="closeHandoverModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit="saveHandover" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Shift</label>
                        <select wire:model="handoverShift"
                                class="w-full text-xs rounded-lg border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                            <option value="morning">Morning (06:00 - 14:00)</option>
                            <option value="afternoon">Afternoon (14:00 - 22:00)</option>
                            <option value="night">Night (22:00 - 06:00)</option>
                        </select>
                        @error('handoverShift') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Incoming Lead</label>
                        <select wire:model="handoverIncomingLeadId"
                                class="w-full text-xs rounded-lg border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                            <option value="">— Select Incoming Lead —</option>
                            @foreach($teamMembers as $tm)
                            <option value="{{ $tm->id }}">{{ $tm->name }} ({{ ucfirst($tm->role) }})</option>
                            @endforeach
                        </select>
                        @error('handoverIncomingLeadId') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Operational Summary & Traffic Health <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="handoverSummary"
                              rows="3"
                              placeholder="Describe SMS delivery rates, PaySwitch gateways, backup completions, database health..."
                              class="w-full text-xs rounded-lg border-gray-300 focus:ring-1 focus:ring-[#1B6B3A] focus:border-[#1B6B3A]"></textarea>
                    @error('handoverSummary') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-red-800 uppercase mb-1 flex items-center gap-1">
                        <span>🚨</span> Incidents & Blockers
                    </label>
                    <textarea wire:model="handoverIncidents"
                              rows="2"
                              placeholder="Any open incidents, ticket IDs, or uncompleted checks needing immediate attention by incoming team..."
                              class="w-full text-xs rounded-lg border-red-200 bg-red-50/30 text-gray-800 focus:ring-1 focus:ring-red-500 focus:border-red-500"></textarea>
                    @error('handoverIncidents') <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p> @enderror
                </div>

                <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 flex items-center justify-between text-xs text-gray-600">
                    <span>Current Snapshot: <strong>{{ $totalPendingCount }} Pending</strong>, <strong>{{ $totalDoneCount }} Completed</strong></span>
                    <span class="font-mono text-gray-400">Date: {{ $date }}</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button"
                            wire:click="closeHandoverModal"
                            class="px-4 py-2 text-xs font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-bold bg-[#1B6B3A] text-white rounded-lg hover:bg-[#15532D] transition-colors shadow-sm flex items-center gap-1.5">
                        <svg wire:loading class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Sign & Log Shift Handover</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── Audit Log Timeline (Admin & Lead only) ──────────────── --}}
    @if(auth()->user()->canManageActivities() && isset($recentAudits) && $recentAudits->isNotEmpty())
    <section class="mt-10 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-3">
            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#1B6B3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Recent Audit Trail (Supervisor Console)
            </h2>
            <span class="text-xs text-gray-400">Live operational events</span>
        </div>
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($recentAudits as $audit)
                <li>
                    <div class="relative pb-8">
                        @if(!$loop->last)
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full bg-green-50 flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-4 h-4 text-[#1B6B3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-800">
                                        <span class="font-semibold text-gray-900">{{ $audit->actor_name }}</span>
                                        <span class="capitalize text-gray-500 font-medium ml-1">({{ $audit->event }})</span>
                                        <span class="text-gray-600 ml-1">
                                            {{ class_basename($audit->subject_type) }} #{{ $audit->subject_id }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-right text-xs whitespace-nowrap text-gray-400 font-mono">
                                    {{ $audit->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    {{-- wire:poll auto-refreshes the board every 30s for live shift updates --}}
    <div wire:poll.30000ms></div>
</div>
