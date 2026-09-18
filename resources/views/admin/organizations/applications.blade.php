@extends('layouts.app')

@section('title', 'Organization Applications Queue — Platform Administration')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-400 mb-1">
                <span>Platform Admin</span>
                <span>/</span>
                <span class="text-[#F5C518]">Organization Queue</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Organization Registration Queue
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Review self-service organization registration requests, evaluate risk indicators, and provision enterprise tenant environments.
            </p>
        </div>
    </div>

    {{-- Applications Table Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-black/30 text-gray-500 dark:text-gray-400 font-mono uppercase text-[10px] tracking-wider border-b border-gray-200 dark:border-white/10">
                    <tr>
                        <th class="px-5 py-3.5">Organization</th>
                        <th class="px-5 py-3.5">Applicant</th>
                        <th class="px-5 py-3.5">Topology &amp; Tier</th>
                        <th class="px-5 py-3.5">Risk Score</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($applications as $app)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $app->organization_name }}</span>
                                <span class="font-mono text-[11px] text-gray-400">Slug: {{ $app->organization_slug }}</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-800 dark:text-gray-200 block">{{ $app->applicant?->name ?? 'Unknown' }}</span>
                                <span class="font-mono text-[11px] text-gray-400">{{ $app->contact_email }}</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 capitalize">
                                    {{ str_replace('_', ' ', $app->deployment_model) }}
                                </span>
                                <span class="block text-[11px] text-gray-400 font-mono mt-0.5 capitalize">Tier: {{ $app->tier }} &bull; Region: {{ $app->preferred_region }}</span>
                            </td>

                            <td class="px-5 py-4">
                                @if($app->risk_score < 30)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-500/30">
                                        Low ({{ $app->risk_score }})
                                    </span>
                                @elseif($app->risk_score < 70)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-500/30">
                                        Med ({{ $app->risk_score }})
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-500/30">
                                        High ({{ $app->risk_score }})
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if($app->status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        Approved
                                    </span>
                                @elseif($app->status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        Rejected
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-950/60 dark:text-[#F5C518]">
                                        Pending Review
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-right">
                                @if($app->isPending())
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Approve Form --}}
                                        <form method="POST" action="{{ route('admin.organizations.applications.review', $app) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="approved">
                                            <input type="hidden" name="review_notes" value="Approved via Platform Administrator Console.">
                                            <button type="submit"
                                                    onclick="return confirm('Approve organization {{ $app->organization_name }} and provision tenant workspace?')"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-colors cursor-pointer">
                                                Approve
                                            </button>
                                        </form>

                                        {{-- Reject Form --}}
                                        <form method="POST" action="{{ route('admin.organizations.applications.review', $app) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="rejected">
                                            <input type="hidden" name="rejection_reason" value="Application did not meet security or qualification baseline.">
                                            <button type="submit"
                                                    onclick="return confirm('Reject organization application {{ $app->organization_name }}?')"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-600/20 hover:bg-red-600 text-red-700 dark:text-red-300 hover:text-white transition-colors cursor-pointer">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-[11px] font-mono text-gray-400">Decided</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                No organization applications pending in queue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/5">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
