@extends('layouts.app')

@section('title', 'Platform Enterprise Settings — Opsora SRE')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400 mb-1">
                <a href="{{ route('admin.platform.dashboard') }}" class="hover:underline">Platform Control Plane</a>
                <span>/</span>
                <span class="text-[#F5C518]">Settings</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Enterprise Platform Policies</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-mono font-bold bg-[#1B6B3A]/20 text-[#1B6B3A] dark:text-emerald-300 border border-[#1B6B3A]/30">
                    Configuration
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                Global SaaS governance, sovereignty data residency policies, onboarding self-service controls, and enterprise white-labeling.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.platform.audit.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white dark:bg-[#16241B] border border-gray-300 dark:border-white/10 text-gray-800 dark:text-gray-200 text-xs font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Config Audit Logs</span>
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

    {{-- Main Settings Form --}}
    <form action="{{ route('admin.platform.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- General Platform Identity --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Platform Branding & Support Identity</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Global metadata displayed across communications, email templates, and system banners.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="platform_name" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Platform Name</label>
                    <input type="text" id="platform_name" name="platform_name" value="{{ old('platform_name', $settings['platform_name'] ?? 'Opsora SRE') }}"
                           class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                    @error('platform_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="support_email" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Support & Escalation Email</label>
                    <input type="email" id="support_email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? 'hello@johnokyere.xyz') }}"
                           class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                    @error('support_email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Sovereign Data Residency & Cloud Topology --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Sovereign Data Residency & Compliance</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Configure regulatory tenant database boundaries and geographic cloud regions.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="default_region" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Primary Sovereign Cloud Region</label>
                    <select id="default_region" name="default_region" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                        <option value="af-south" {{ ($settings['default_region'] ?? '') === 'af-south' ? 'selected' : '' }}>Africa South (Cape Town / Primary)</option>
                        <option value="eu-west" {{ ($settings['default_region'] ?? '') === 'eu-west' ? 'selected' : '' }}>Europe West (Frankfurt / GDPR Compliant)</option>
                        <option value="us-east" {{ ($settings['default_region'] ?? '') === 'us-east' ? 'selected' : '' }}>US East (N. Virginia / SOC2)</option>
                    </select>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Strict Regional Isolation</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Enforce cross-region tenant data replication boundaries</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="enforce_data_residency" value="1" class="sr-only peer" {{ !empty($settings['enforce_data_residency']) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1B6B3A]"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Customer Onboarding & Self-Service Registration --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Public Onboarding & Registration Gate</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Controls whether external engineers can create accounts and provision organizations.</p>
            </div>

            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Allow Public Self-Service Sign-Up</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Permits open registration at /register with personal workspace generation</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="allow_self_service_registration" value="1" class="sr-only peer" {{ !empty($settings['allow_self_service_registration']) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1B6B3A]"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Require Manual Approval for New Organizations</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">When enabled, newly submitted organizations enter the review queue before activation</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="require_org_application_review" value="1" class="sr-only peer" {{ !empty($settings['require_org_application_review']) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1B6B3A]"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/5">
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">Enable Multi-Tenant White-Labeling</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Allows Enterprise tenants to configure custom logos and custom domain headers</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="white_label_enabled" value="1" class="sr-only peer" {{ !empty($settings['white_label_enabled']) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1B6B3A]"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- System Maintenance Mode & Emergency Lockout --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Platform Maintenance Mode &amp; Emergency Lockout</span>
                        @if(!empty($settings['maintenance_mode']))
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-red-100 dark:bg-red-950/60 text-[#E63946] border border-red-300 dark:border-red-900/60">
                                ACTIVE LOCKOUT
                            </span>
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Put the platform into maintenance mode. Platform Admins remain exempt and can access the Control Plane.</p>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" {{ !empty($settings['maintenance_mode']) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#E63946]"></div>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="maintenance_ends_at" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Estimated Restoration / Downtime</label>
                    <input type="text" id="maintenance_ends_at" name="maintenance_ends_at" value="{{ old('maintenance_ends_at', $settings['maintenance_ends_at'] ?? '') }}"
                           placeholder="e.g. Sunday 03:00 UTC (approx. 45 mins)"
                           class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                </div>

                <div>
                    <label for="maintenance_bypass_key" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Secret Bypass Key (?bypass_key=...)</label>
                    <input type="text" id="maintenance_bypass_key" name="maintenance_bypass_key" value="{{ old('maintenance_bypass_key', $settings['maintenance_bypass_key'] ?? 'sre-opsora-emergency-bypass') }}"
                           class="w-full px-3 py-2 text-sm font-mono rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">
                </div>
            </div>

            <div>
                <label for="maintenance_message" class="block text-xs font-mono font-bold text-gray-700 dark:text-gray-300 mb-1">Custom Maintenance Broadcast Notice</label>
                <textarea id="maintenance_message" name="maintenance_message" rows="3"
                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-black/30 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#1B6B3A]">{{ old('maintenance_message', $settings['maintenance_message'] ?? 'Opsora SRE is currently undergoing scheduled platform maintenance. Services will resume shortly.') }}</textarea>
                <p class="text-[11px] text-gray-500 mt-1">Rendered on the 503 Maintenance Page and returned to API / mobile clients.</p>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white text-sm font-bold shadow-sm transition-colors flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Save Platform Policies &amp; Maintenance State</span>
            </button>
        </div>
    </form>

</div>
@endsection
