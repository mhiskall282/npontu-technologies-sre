@extends('layouts.app')
@section('title', 'Account Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Account Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your profile information and update security credentials.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Profile Navigation Info --}}
        <div class="md:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center text-[#1B6B3A] font-bold text-lg">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900 leading-tight">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 capitalize mt-1">
                            {{ $user->role }}
                        </span>
                    </div>
                </div>
                <hr class="border-gray-100 my-4">
                <dl class="space-y-3 text-xs text-gray-600">
                    <div>
                        <dt class="font-medium text-gray-400 uppercase tracking-wider">Email Address</dt>
                        <dd class="mt-0.5 font-semibold text-gray-800">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-400 uppercase tracking-wider">Designation</dt>
                        <dd class="mt-0.5 font-semibold text-gray-800">{{ $user->designation ?? 'Support Operator' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Privilege Info Card --}}
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                <h3 class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2">Privilege Access</h3>
                <p class="text-xs text-amber-700 leading-relaxed">
                    @if($user->isAdmin())
                        You have **Administrator** privileges. You can manage system activities, view full audit logs, and manage user accounts.
                    @elseif($user->isLead())
                        You have **Team Lead** privileges. You can create/edit activities, oversee daily checklists, and review shift logs.
                    @else
                        You have **Support Operator (Agent)** privileges. You can check off activities and log update remarks. Administrative settings are locked.
                    @endif
                </p>
            </div>
        </div>

        {{-- Forms Area --}}
        <div class="md:col-span-2 space-y-6">
            {{-- Profile Details Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">Profile Information</h2>
                <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="name" class="block text-xs font-semibold text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="designation" class="block text-xs font-semibold text-gray-700 mb-1">Designation</label>
                            <input type="text" id="designation" name="designation" value="{{ old('designation', $user->designation) }}"
                                   {{ !$user->canManageActivities() ? 'readonly' : '' }}
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2 {{ !$user->canManageActivities() ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}">
                            @if(!$user->canManageActivities())
                                <p class="text-[10px] text-gray-400 mt-1">Only leads or admins can change designations.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="submit"
                                class="px-5 py-2 bg-[#1B6B3A] hover:bg-[#2A8F52] text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Password Change Form --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-5">Change Password</h2>
                <form action="{{ route('settings.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-gray-700 mb-1">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">New Password</label>
                            <input type="password" id="password" name="password" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] px-3 py-2">
                        </div>
                    </div>

                    <div class="flex justify-end pt-3">
                        <button type="submit"
                                class="px-5 py-2 bg-[#1B6B3A] hover:bg-[#2A8F52] text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
