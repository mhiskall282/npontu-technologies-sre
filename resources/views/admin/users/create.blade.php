@extends('layouts.app')
@section('title', 'Add Team Member')

@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-[#1B6B3A] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Provision Team Member</h1>
            <p class="text-xs text-gray-500 mt-0.5">Configure profile, SRE engineering grade, department, and granular operational privileges</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
         x-data="{
            role: '{{ old('role', 'agent') }}',
            selectedPrivileges: {{ json_encode(old('privileges', ['escalate_incidents', 'create_channels'])) }},
            applyRolePresets() {
                if (this.role === 'admin') {
                    this.selectedPrivileges = {{ json_encode(array_keys($allPrivileges)) }};
                } else if (this.role === 'lead') {
                    this.selectedPrivileges = ['manage_activities', 'assign_tasks', 'sign_handovers', 'accept_handovers', 'escalate_incidents', 'export_reports', 'view_audit_logs', 'create_channels'];
                } else {
                    this.selectedPrivileges = ['escalate_incidents', 'create_channels'];
                }
            }
         }">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- ── Basic Identification ───────────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Full Name <span class="text-[#E63946]">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="e.g. Kwesi Mensah"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] @error('name') border-[#E63946] @enderror">
                    @error('name')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address <span class="text-[#E63946]">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="e.g. kwesi.mensah@npontu.com"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] @error('email') border-[#E63946] @enderror">
                    @error('email')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ── Role, Grade & Department ──────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Access Role <span class="text-[#E63946]">*</span></label>
                    <select name="role" x-model="role" x-on:change="applyRolePresets()"
                            class="block w-full rounded-lg border-gray-300 text-sm font-semibold focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        <option value="agent">Support Operator (Agent)</option>
                        <option value="lead">Team Lead (Shift Supervisor)</option>
                        <option value="admin">System Administrator</option>
                    </select>
                    @error('role')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">SRE Grade / Tier <span class="text-[#E63946]">*</span></label>
                    <select name="grade" class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        @foreach($grades as $gradeKey => $gradeLabel)
                        <option value="{{ $gradeKey }}" {{ old('grade', 'L2') === $gradeKey ? 'selected' : '' }}>
                            {{ $gradeLabel }}
                        </option>
                        @endforeach
                    </select>
                    @error('grade')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Department / Pod <span class="text-[#E63946]">*</span></label>
                    <select name="department" class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                    @error('department')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Job Designation</label>
                    <input type="text" name="designation" value="{{ old('designation') }}"
                           placeholder="e.g. NOC Engineer / Infrastructure Specialist"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Emergency On-Call Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="e.g. +233 24 123 4567"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                </div>
            </div>

            {{-- ── Initial Password ───────────────────────────────── --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Initial Password <span class="text-[#E63946]">*</span></label>
                    <input type="password" name="password"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] @error('password') border-[#E63946] @enderror">
                    @error('password')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Confirm Initial Password <span class="text-[#E63946]">*</span></label>
                    <input type="password" name="password_confirmation"
                           class="block w-full rounded-lg border-gray-300 text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                </div>
            </div>

            {{-- ── Granular Privileges & Permissions Checkboxes ──── --}}
            <div class="pt-3 border-t border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Granular Operational Privileges</h3>
                        <p class="text-xs text-gray-500">Tick custom permissions for this user. Select an access role above to auto-apply defaults, or customize individually.</p>
                    </div>
                    <button type="button"
                            x-on:click="applyRolePresets()"
                            class="text-xs text-[#1B6B3A] font-semibold hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Reset to Role Defaults
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($allPrivileges as $privKey => $privData)
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:border-green-300 transition-colors cursor-pointer bg-white"
                           :class="{ 'bg-green-50/40 border-green-300 ring-1 ring-green-200': selectedPrivileges.includes('{{ $privKey }}') }">
                        <input type="checkbox"
                               name="privileges[]"
                               value="{{ $privKey }}"
                               x-model="selectedPrivileges"
                               class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A] mt-0.5 h-4 w-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-gray-900">{{ $privData['label'] }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 font-semibold">{{ $privData['category'] }}</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed">{{ $privData['description'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-3 border-t border-gray-100">
                <button type="submit"
                        class="px-6 py-2.5 bg-[#1B6B3A] hover:bg-[#15532D] text-white text-sm font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Provision Member</span>
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
