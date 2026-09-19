@extends('layouts.app')

@section('title', 'Platform Users & Operators — Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Access Control</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Platform Operators &amp; User Accounts
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Administer global users, configure platform administrative roles, revoke active sessions, and enforce security suspensions.
            </p>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <form method="GET" action="{{ route('admin.platform.users.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text"
                       name="search"
                       value="{{ $filters['search'] ?? '' }}"
                       placeholder="Search by operator name, email address, or department..."
                       class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
            </div>

            <div>
                <select name="platform_role"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Platform Roles</option>
                    @foreach($platformRoles as $pr)
                        <option value="{{ $pr->value }}" {{ ($filters['platform_role'] ?? '') === $pr->value ? 'selected' : '' }}>
                            {{ $pr->label() }}
                        </option>
                    @endforeach
                    <option value="none" {{ ($filters['platform_role'] ?? '') === 'none' ? 'selected' : '' }}>No Platform Role (Tenant Only)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status"
                        class="w-full px-3.5 py-2 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    <option value="">All Account Statuses</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit"
                        class="px-4 py-2 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-sm transition-colors cursor-pointer shrink-0">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Operator</th>
                        <th class="px-5 py-3.5">Platform Role</th>
                        <th class="px-5 py-3.5">Organizations &amp; Workspaces</th>
                        <th class="px-5 py-3.5">Account Status</th>
                        <th class="px-5 py-3.5 text-right">Administrative Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-[#1B6B3A] border border-[#F5C518]/40 flex items-center justify-center text-white font-bold text-xs font-mono shadow-inner shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-gray-900 dark:text-white block text-sm">{{ $user->name }}</span>
                                        <span class="font-mono text-[11px] text-gray-400 block">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if($user->platformRoleEnum())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>{{ $user->platformRoleEnum()->label() }}</span>
                                    </span>
                                @else
                                    <span class="text-[11px] font-mono text-gray-400">Tenant Operator ({{ $user->role }})</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-gray-600 dark:text-gray-300 font-mono">
                                <span>{{ $user->organizations->count() }} Orgs</span>
                                &bull;
                                <span>{{ $user->workspaces->count() }} Workspaces</span>
                            </td>

                            <td class="px-5 py-4">
                                @if($user->isSuspended())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300 border border-red-300 dark:border-red-800">
                                        Suspended
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Quick Role Change Dropdown --}}
                                    <form method="POST" action="{{ route('admin.platform.users.update-role', $user->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="platform_role" onchange="this.form.submit()"
                                                class="px-2 py-1 rounded-lg text-xs bg-gray-50 dark:bg-black/40 border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 cursor-pointer">
                                            <option value="">No Platform Role</option>
                                            @foreach($platformRoles as $pr)
                                                <option value="{{ $pr->value }}" {{ $user->platform_role === $pr->value ? 'selected' : '' }}>
                                                    {{ $pr->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>

                                    {{-- Revoke Tokens --}}
                                    <form method="POST" action="{{ route('admin.platform.users.revoke-tokens', $user->id) }}">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Revoke all active Sanctum tokens for {{ $user->name }}?')"
                                                class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors"
                                                title="Revoke all active tokens/sessions">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        </button>
                                    </form>

                                    {{-- Suspend / Reactivate --}}
                                    @if(! $user->isSuspended() && $user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.platform.users.suspend', $user->id) }}">
                                            @csrf
                                            <input type="hidden" name="reason" value="Suspended via Platform Control Plane.">
                                            <button type="submit"
                                                    onclick="return confirm('Suspend user {{ $user->name }}? Active sessions will be terminated immediately.')"
                                                    class="px-2 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 hover:bg-red-600 hover:text-white transition-colors cursor-pointer">
                                                Suspend
                                            </button>
                                        </form>
                                    @elseif($user->isSuspended())
                                        <form method="POST" action="{{ route('admin.platform.users.reactivate', $user->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    onclick="return confirm('Reactivate account for {{ $user->name }}?')"
                                                    class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 hover:bg-emerald-600 hover:text-white transition-colors cursor-pointer">
                                                Reactivate
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No users found matching search parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
