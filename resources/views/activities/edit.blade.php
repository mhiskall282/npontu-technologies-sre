@extends('layouts.app')
@section('title', 'Edit Activity')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('activities.show', $activity) }}" class="text-gray-400 hover:text-[#1B6B3A] transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Activity</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('activities.update', $activity) }}" method="POST" class="space-y-5">
            @csrf @method('PATCH')

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-[#E63946]">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title', $activity->title) }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A] @error('title') border-[#E63946] @enderror">
                @error('title')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">{{ old('description', $activity->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="category" name="category" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        <option value="">— Select —</option>
                        @foreach(['Application','Infrastructure','Database','Network','Security'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $activity->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="recurrence" class="block text-sm font-medium text-gray-700 mb-1">Recurrence</label>
                    <select id="recurrence" name="recurrence" class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        <option value="daily" {{ old('recurrence', $activity->recurrence) === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="adhoc" {{ old('recurrence', $activity->recurrence) === 'adhoc' ? 'selected' : '' }}>Ad Hoc</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-1">
                    Assign to Team Member <span class="text-xs text-gray-400 font-normal">(Optional — defaults to general shift pool)</span>
                </label>
                <select id="assigned_to" name="assigned_to"
                        class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                    <option value="">— Unassigned (Available to whole shift) —</option>
                    @if(isset($users))
                        @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to', $activity->assigned_to) == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ ucfirst($u->role) }}{{ $u->designation ? ' — ' . $u->designation : '' }})
                        </option>
                        @endforeach
                    @endif
                </select>
                @error('assigned_to')<p class="mt-1 text-xs text-[#E63946]">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority Tier</label>
                    <select id="priority" name="priority"
                            class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                        <option value="low" {{ old('priority', $activity->priority) === 'low' ? 'selected' : '' }}>P4 — Low Priority</option>
                        <option value="medium" {{ old('priority', $activity->priority) === 'medium' ? 'selected' : '' }}>P3 — Medium Priority (Standard)</option>
                        <option value="high" {{ old('priority', $activity->priority) === 'high' ? 'selected' : '' }}>P2 — High Priority</option>
                        <option value="critical" {{ old('priority', $activity->priority) === 'critical' ? 'selected' : '' }}>P1 — Critical (Urgent SRE Check)</option>
                    </select>
                </div>

                <div>
                    <label for="sla_time" class="block text-sm font-medium text-gray-700 mb-1">
                        SLA Target Checkoff Time <span class="text-xs text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input type="text" id="sla_time" name="sla_time" value="{{ old('sla_time', $activity->sla_time) }}"
                           placeholder="e.g. 09:00 or 14:30 GMT"
                           class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-[#1B6B3A] focus:border-[#1B6B3A]">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_pinned" name="is_pinned" value="1"
                           class="rounded border-gray-300 text-amber-500 focus:ring-amber-500"
                           {{ old('is_pinned', $activity->is_pinned) ? 'checked' : '' }}>
                    <label for="is_pinned" class="text-sm font-medium text-gray-700 flex items-center gap-1">
                        <span>📌</span> Pin to Top of Shift Board
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           class="rounded border-gray-300 text-[#1B6B3A] focus:ring-[#1B6B3A]"
                           {{ old('is_active', $activity->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="text-sm text-gray-700">Active</label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-[#1B6B3A] hover:bg-[#2A8F52] text-white text-sm font-semibold rounded-lg transition-colors duration-150 shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('activities.show', $activity) }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors duration-150">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
