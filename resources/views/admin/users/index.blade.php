@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-[#1B6B3A] hover:bg-[#2A8F52] text-white text-sm font-semibold rounded-lg transition-colors duration-150 shadow-sm">
        Add Member
    </a>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Designation</th>
                <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">
            @foreach($users as $user)
            <tr class="hover:bg-[#F4F7F5] transition-colors duration-100">
                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                <td class="px-6 py-4">
                    <span class="capitalize text-xs px-2.5 py-0.5 rounded-full font-semibold
                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' :
                           ($user->role === 'lead' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ $user->role }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-500">{{ $user->designation ?? '—' }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('admin.users.edit', $user) }}" class="text-[#1B6B3A] hover:underline font-medium mr-3">Edit</a>
                    @if(auth()->id() !== $user->id)
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                          onsubmit="return confirm('Remove {{ $user->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-[#E63946] hover:underline font-medium">Remove</button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
</div>
@endsection
