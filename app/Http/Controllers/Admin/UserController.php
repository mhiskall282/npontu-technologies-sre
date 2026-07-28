<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AuditService $auditService) {}

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $this->auditService->log(subject: $user, event: 'created', newValues: collect($data)->except('password')->toArray());

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $old = $user->only(['name', 'email', 'role', 'designation', 'phone']);
        $user->update($request->validated());
        $this->auditService->log(subject: $user, event: 'updated', oldValues: $old, newValues: $request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->auditService->log(subject: $user, event: 'deleted', oldValues: $user->toArray());
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User removed.');
    }
}
