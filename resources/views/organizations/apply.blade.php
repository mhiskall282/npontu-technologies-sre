@extends('layouts.app')

@section('title', 'Register Organization — Opsora SaaS')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <div class="flex items-center gap-2 text-xs font-mono text-emerald-400 mb-1">
            <a href="{{ route('workspaces.index') }}" class="hover:underline">Workspaces</a>
            <span>/</span>
            <span class="text-[#F5C518]">Organization Registration</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">
            Register New Organization
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
            Create an enterprise tenant on the Opsora operations cloud. Standard team deployments are evaluated automatically; dedicated and customer-hosted topologies enter platform review.
        </p>
    </div>

    {{-- Registration Form Card --}}
    <div class="p-6 sm:p-8 rounded-2xl bg-white dark:bg-[#16241B] border border-gray-200 dark:border-white/10 shadow-sm">
        <form method="POST" action="{{ route('organizations.apply.store') }}" class="space-y-6">
            @csrf

            {{-- Row 1: Org Name & Desired Slug --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="organization_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Organization / Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="organization_name"
                           name="organization_name"
                           value="{{ old('organization_name') }}"
                           placeholder="e.g. Apex Global Logistics"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none"
                           required>
                    @error('organization_name')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="organization_slug" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Desired Workspace Slug
                    </label>
                    <input type="text"
                           id="organization_slug"
                           name="organization_slug"
                           value="{{ old('organization_slug') }}"
                           placeholder="e.g. apex-global"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                    @error('organization_slug')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Contact Email & Plan Tier --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_email" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Primary Administrative Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="contact_email"
                           name="contact_email"
                           value="{{ old('contact_email', auth()->user()->email) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none"
                           required>
                    @error('contact_email')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tier" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Subscription Tier
                    </label>
                    <select id="tier"
                            name="tier"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                        <option value="team" {{ old('tier') === 'team' ? 'selected' : '' }}>Team (Up to 25 Operators)</option>
                        <option value="free" {{ old('tier') === 'free' ? 'selected' : '' }}>Free Tier (Starter Community)</option>
                        <option value="enterprise" {{ old('tier') === 'enterprise' ? 'selected' : '' }}>Enterprise (Unlimited &amp; Custom SLA)</option>
                    </select>
                </div>
            </div>

            {{-- Row 3: Deployment Model & Data Residency --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="deployment_model" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Deployment Topology
                    </label>
                    <select id="deployment_model"
                            name="deployment_model"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                        <option value="shared_saas" {{ old('deployment_model') === 'shared_saas' ? 'selected' : '' }}>Shared Multi-Tenant SaaS (Instant)</option>
                        <option value="dedicated_managed" {{ old('deployment_model') === 'dedicated_managed' ? 'selected' : '' }}>Dedicated Managed Cloud (Review Required)</option>
                        <option value="customer_hosted" {{ old('deployment_model') === 'customer_hosted' ? 'selected' : '' }}>Customer-Hosted / On-Prem (Review Required)</option>
                        <option value="customer_funded" {{ old('deployment_model') === 'customer_funded' ? 'selected' : '' }}>Customer-Funded AWS/Azure VPC (Review Required)</option>
                    </select>
                </div>

                <div>
                    <label for="preferred_region" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Nominated Data Residency Zone
                    </label>
                    <select id="preferred_region"
                            name="preferred_region"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">
                        <option value="af-south" {{ old('preferred_region') === 'af-south' ? 'selected' : '' }}>Africa Primary (Johannesburg / Accra Gateway)</option>
                        <option value="eu-west" {{ old('preferred_region') === 'eu-west' ? 'selected' : '' }}>Europe West (Frankfurt / Dublin)</option>
                        <option value="us-east" {{ old('preferred_region') === 'us-east' ? 'selected' : '' }}>North America East (Virginia)</option>
                    </select>
                </div>
            </div>

            {{-- Row 4: Notes --}}
            <div>
                <label for="notes" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                    Operational Requirements / Implementation Notes (Optional)
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          placeholder="Describe target workloads, custom SSO/SAML integrations, or regulatory data-sovereignty mandates..."
                          class="w-full px-3.5 py-2.5 rounded-xl text-xs bg-gray-50 dark:bg-black/30 border border-gray-300 dark:border-white/10 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#1B6B3A] focus:outline-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit Strip --}}
            <div class="pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between gap-3">
                <a href="{{ route('workspaces.index') }}"
                   class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    &larr; Cancel
                </a>

                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-[#1B6B3A] hover:bg-emerald-600 text-white font-bold text-xs shadow-md transition-colors cursor-pointer flex items-center gap-2">
                    <span>Submit Application</span>
                    <svg class="w-4 h-4 text-[#F5C518]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
