@extends('layouts.app')

@section('title', 'Platform Operational Announcements & Broadcasts — Control Plane')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Communications</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Operational Announcements &amp; Broadcasts</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                    Live Engine
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Broadcast system-wide notices, maintenance advisories, and critical incident bulletins across all tenant web views and mobile applications.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button"
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#1B6B3A] hover:bg-[#15532d] text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span>Broadcast New Announcement</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Active Broadcasts</p>
                <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $activeCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Critical Incident Advisories</p>
                <p class="text-2xl font-black text-[#E63946] mt-1">{{ $criticalCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-[#E63946]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-mono uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">Broadcast Reach</p>
                <p class="text-2xl font-black text-[#F5C518] mt-1">Web &amp; Mobile</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-[#F5C518]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
            </div>
        </div>
    </div>

    {{-- Announcements Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-[#1B6B3A] dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                <span>Broadcasts &amp; Operational Bulletins Ledger</span>
            </h3>
            <span class="text-xs font-mono text-gray-500 dark:text-gray-400">Total: {{ $announcements->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Severity / Type</th>
                        <th class="px-5 py-3.5">Title &amp; Notice Body</th>
                        <th class="px-5 py-3.5">Broadcast Schedule</th>
                        <th class="px-5 py-3.5">Author</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($announcements as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4 align-top">
                                @if($item->type === 'critical')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold font-mono uppercase bg-red-100 dark:bg-red-950/60 text-[#E63946] border border-red-300 dark:border-red-900/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#E63946] animate-ping"></span>
                                        Critical
                                    </span>
                                @elseif($item->type === 'warning')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold font-mono uppercase bg-amber-100 dark:bg-amber-950/60 text-[#F5C518] border border-amber-300 dark:border-amber-800">
                                        Warning
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold font-mono uppercase bg-blue-100 dark:bg-blue-950/60 text-blue-500 border border-blue-300 dark:border-blue-800">
                                        Information
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-top max-w-md">
                                <span class="font-bold text-gray-900 dark:text-white block text-sm mb-1">{{ $item->title }}</span>
                                <p class="text-gray-600 dark:text-gray-300 text-xs leading-relaxed whitespace-pre-line">{{ $item->message }}</p>
                                @if(!$item->dismissible)
                                    <span class="inline-block mt-2 px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/5 text-[10px] font-mono text-gray-500">
                                        Lockout / Non-Dismissible
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-top font-mono text-[11px] text-gray-600 dark:text-gray-400 space-y-1">
                                <div>
                                    <span class="text-gray-400">Created:</span> {{ $item->created_at->format('M d, Y H:i') }} UTC
                                </div>
                                @if($item->expires_at)
                                    <div class="{{ $item->isExpired() ? 'text-red-400' : 'text-emerald-400' }}">
                                        <span class="text-gray-400">Expires:</span> {{ $item->expires_at->format('M d, Y H:i') }} UTC
                                        @if($item->isExpired())
                                            <span class="text-[10px] uppercase font-bold">(Expired)</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-gray-400">Indefinite duration</div>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-top text-xs text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">{{ $item->creator?->name ?? 'System Admin' }}</span>
                                <span class="block text-[10px] text-gray-500 font-mono">{{ $item->creator?->email ?? 'platform@npontu.com' }}</span>
                            </td>

                            <td class="px-5 py-4 align-top">
                                @if($item->is_active && !$item->isExpired())
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Broadcasting
                                    </span>
                                @elseif($item->isExpired())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-400">
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-400">
                                        Deactivated
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 align-top text-right space-x-2 whitespace-nowrap">
                                {{-- Quick Toggle Form --}}
                                <form action="{{ route('admin.platform.announcements.toggle', $item->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold border transition cursor-pointer
                                                   {{ $item->is_active ? 'bg-amber-500/10 text-[#F5C518] border-amber-500/30 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20' }}">
                                        {{ $item->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                {{-- Delete Form --}}
                                <form action="{{ route('admin.platform.announcements.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Permanently remove this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-red-500/10 text-[#E63946] border border-red-500/30 hover:bg-red-500/20 transition cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                                <p class="text-sm font-semibold">No operational announcements published.</p>
                                <p class="text-xs text-gray-400 mt-1">Broadcast high-priority notices, planned maintenance, and incident updates to all teams.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div class="px-5 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>

    {{-- Create Announcement Modal --}}
    <div x-show="showCreateModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
         x-cloak>
        <div class="bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4"
             @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#F5C518]"></span>
                    <h3 class="font-bold text-base text-gray-900 dark:text-white">Broadcast Operational Announcement</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-white text-lg font-mono">&times;</button>
            </div>

            <form action="{{ route('admin.platform.announcements.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase font-mono tracking-wider mb-1">Notice Headline</label>
                    <input type="text" name="title" required placeholder="e.g. Scheduled Core Database Maintenance Window"
                           class="w-full px-3 py-2 text-xs rounded-xl bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase font-mono tracking-wider mb-1">Severity / Urgency</label>
                        <select name="type" required
                                class="w-full px-3 py-2 text-xs rounded-xl bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                            <option value="info">Info (Standard Operational Bulletin)</option>
                            <option value="warning">Warning (Planned Maintenance Window)</option>
                            <option value="critical">Critical (Incident / Emergency Advisory)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase font-mono tracking-wider mb-1">Expiration Date (Optional)</label>
                        <input type="datetime-local" name="expires_at"
                               class="w-full px-3 py-2 text-xs rounded-xl bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase font-mono tracking-wider mb-1">Notice Message Body</label>
                    <textarea name="message" rows="4" required placeholder="Detailed message explaining the situation, affected services, and expected resolution..."
                              class="w-full px-3 py-2 text-xs rounded-xl bg-gray-50 dark:bg-black/40 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                        <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">Activate immediately upon broadcast</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="dismissible" value="1" checked class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]">
                        <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">Allow operators to dismiss</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-[#1B6B3A] hover:bg-[#15532d] text-white font-bold text-xs rounded-xl shadow-sm">Publish Broadcast</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
