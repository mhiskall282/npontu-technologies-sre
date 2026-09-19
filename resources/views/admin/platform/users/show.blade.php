@extends('layouts.app')

@section('title', "User: {$user->name} — Platform Control Plane")

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb and Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <a href="{{ route('admin.platform.users.index') }}" class="hover:underline">Users</a>
                <span>/</span>
                <span class="text-[#F5C518]">{{ $user->email }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>{{ $user->name }}</span>
                @if($user->isSuspended())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-800">
                        Suspended
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        Active Account
                    </span>
                @endif
                @if($user->platformRoleEnum())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                        {{ $user->platformRoleEnum()->label() }}
                    </span>
                @endif
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Department: {{ $user->department ?? 'General Operations' }} &bull; Role: {{ ucfirst($user->role ?? 'engineer') }} &bull; Joined {{ $user->created_at->format('M d, Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.platform.users.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                &larr; Back to Users
            </a>
        </div>
    </div>

    {{-- Feedback Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-300 dark:border-red-800 text-red-800 dark:text-red-300 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- User Profile Overview and Management Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Identity & Access Management --}}
        <div class="space-y-6">

            {{-- Platform Role Assignment --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Platform Administrative Privileges</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Assign cross-tenant control plane permissions.</p>

                <form action="{{ route('admin.platform.users.update-role', $user->id) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="platform_role" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Assigned Role</label>
                        <select id="platform_role" name="platform_role" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                            <option value="">No Platform Privileges (Tenant User)</option>
                            @foreach($platformRoles as $role)
                                <option value="{{ $role->value }}" {{ $user->platform_role === $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white text-xs font-bold transition-colors">
                        Update Platform Role
                    </button>
                </form>
            </div>

            {{-- Account Governance: Suspend / Reactivate --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Account Governance</h2>

                @if($user->isSuspended())
                    <p class="text-xs text-red-600 dark:text-red-400 mb-4">
                        Account was suspended on {{ $user->suspended_at->format('M d, Y H:i') }}. All logins and API access are blocked.
                    </p>
                    <form action="{{ route('admin.platform.users.reactivate', $user->id) }}" method="POST">
                        @csrf
                        @method('POST')
                        <button type="submit" class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors">
                            Reactivate User Account
                        </button>
                    </form>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                        Suspension immediately revokes all web sessions, API tokens, and halts multi-tenant access.
                    </p>
                    <form action="{{ route('admin.platform.users.suspend', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to suspend this user account?');" class="space-y-3">
                        @csrf
                        @method('POST')
                        <div>
                            <label for="reason" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Suspension Reason</label>
                            <input type="text" id="reason" name="reason" placeholder="e.g., Security incident, policy violation" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white">
                        </div>
                        <button type="submit" class="w-full py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-colors">
                            Suspend User Account
                        </button>
                    </form>
                @endif

                {{-- Revoke Tokens --}}
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-white/5">
                    <form action="{{ route('admin.platform.users.revoke-tokens', $user->id) }}" method="POST" onsubmit="return confirm('Revoke all {{ $user->tokens->count() }} active API tokens?');">
                        @csrf
                        @method('POST')
                        <button type="submit" class="w-full py-2 rounded-xl bg-gray-100 dark:bg-white/5 hover:bg-gray-200 dark:hover:bg-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold transition-colors">
                            Revoke Active Tokens ({{ $user->tokens->count() }})
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right 2 Columns: Tenant Memberships & Security Events --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Organization & Workspace Memberships --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Tenant Memberships & Workspaces</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Active multi-tenant organizations and assigned operational workspaces.</p>

                <div class="space-y-4">
                    <div>
                        <div class="text-xs font-mono font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">Organizations ({{ $user->organizations->count() }})</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @forelse($user->organizations as $org)
                                <a href="{{ route('admin.platform.organizations.show', $org->id) }}"
                                   class="p-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 hover:border-[#1B6B3A] transition-colors flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $org->name }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $org->slug }} &bull; {{ $org->company_code }}</div>
                                    </div>
                                    <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400 font-bold">&rarr;</span>
                                </a>
                            @empty
                                <div class="p-3 text-xs text-gray-500 dark:text-gray-400">No organizational memberships.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-mono font-bold uppercase text-gray-500 dark:text-gray-400 mb-2">Workspaces ({{ $user->workspaces->count() }})</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @forelse($user->workspaces as $ws)
                                <a href="{{ route('admin.platform.workspaces.show', $ws->id) }}"
                                   class="p-3 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5 hover:border-[#1B6B3A] transition-colors flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $ws->name }}</div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">{{ $ws->slug }} &bull; {{ $ws->is_personal ? 'Sandbox' : 'Team' }}</div>
                                    </div>
                                    <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400 font-bold">&rarr;</span>
                                </a>
                            @empty
                                <div class="p-3 text-xs text-gray-500 dark:text-gray-400">No workspace memberships.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Security & SIEM Events --}}
            <div class="p-5 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">Security & SIEM Telemetry</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Security events and privileged actions involving this user.</p>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-black/20 text-gray-600 dark:text-gray-400 uppercase font-mono">
                            <tr>
                                <th class="py-2.5 px-3">Event Type</th>
                                <th class="py-2.5 px-3">Severity</th>
                                <th class="py-2.5 px-3">IP Address</th>
                                <th class="py-2.5 px-3">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @forelse($user->securityEvents as $event)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="py-2.5 px-3 font-mono font-bold text-gray-900 dark:text-white">{{ $event->event_type }}</td>
                                    <td class="py-2.5 px-3">
                                        @if($event->severity === 'critical')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300">CRITICAL</span>
                                        @elseif($event->severity === 'warning')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">WARNING</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300">INFO</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-gray-600 dark:text-gray-400">{{ $event->ip_address }}</td>
                                    <td class="py-2.5 px-3 font-mono text-gray-500 dark:text-gray-400">{{ $event->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-400">No security telemetry recorded for this user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
