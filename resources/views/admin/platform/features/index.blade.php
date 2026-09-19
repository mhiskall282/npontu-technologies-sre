@extends('layouts.app')

@section('title', 'Feature Flags & Gates — Platform Control Plane')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Product Gates</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Feature Flags &amp; Capabilities
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Dynamically toggle platform features, canary rollouts, and tier-targeted capabilities across web, API, and mobile without code redeploys.
            </p>
        </div>
    </div>

    {{-- Feature Flags Table --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-300 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Feature Name &amp; Key</th>
                        <th class="px-5 py-3.5">Description</th>
                        <th class="px-5 py-3.5">Target Tiers</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Quick Toggle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($flags as $flag)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-bold text-gray-900 dark:text-white block text-sm">{{ $flag->name }}</span>
                                <code class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-black/40 text-[11px] font-mono text-[#1B6B3A] dark:text-[#F5C518]">
                                    {{ $flag->key }}
                                </code>
                            </td>

                            <td class="px-5 py-4 text-gray-600 dark:text-gray-300 leading-relaxed max-w-xs">
                                {{ $flag->description ?? 'Platform-wide operational capability gate.' }}
                            </td>

                            <td class="px-5 py-4">
                                @if(!empty($flag->target_tiers))
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @foreach($flag->target_tiers as $tier)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono uppercase bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-[#F5C518]">
                                                {{ $tier }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 font-mono text-[11px]">All Tiers (Global)</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($flag->is_enabled)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300">
                                        Disabled
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right space-x-2 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.platform.features.toggle', $flag->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_enabled" value="{{ $flag->is_enabled ? 0 : 1 }}">
                                    <button type="submit"
                                            class="px-3 py-1 rounded-xl text-xs font-bold transition-colors cursor-pointer
                                                   {{ $flag->is_enabled ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300 hover:bg-red-600 hover:text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                        {{ $flag->is_enabled ? 'Turn Off' : 'Turn On' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.platform.features.destroy', $flag->id) }}" class="inline" onsubmit="return confirm('Permanently delete feature flag {{ $flag->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-2.5 py-1 rounded-xl text-xs font-bold bg-red-500/10 text-[#E63946] border border-red-500/30 hover:bg-red-600 hover:text-white transition-colors cursor-pointer"
                                            title="Delete Flag">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400 font-medium">
                                No feature flags defined yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Feature Flag Form --}}
    <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white font-mono uppercase">
            Define New Feature Flag
        </h3>

        <form method="POST" action="{{ route('admin.platform.features.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Display Name</label>
                <input type="text" name="name" placeholder="e.g. AI Automated Incident Diagnosis" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A]">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Key Identifier</label>
                <input type="text" name="key" placeholder="e.g. ai_incident_diagnosis" required
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-[#1B6B3A]">
            </div>

            <div>
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Initial State</label>
                <select name="is_enabled" class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
                    <option value="1">Enabled Immediately</option>
                    <option value="0" selected>Disabled (Staged)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <input type="text" name="description" placeholder="Brief technical summary of this flag's gate behavior"
                       class="w-full px-3 py-2 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold transition-colors cursor-pointer shadow-sm">
                    Create Feature Flag
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
