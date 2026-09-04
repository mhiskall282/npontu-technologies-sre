<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use App\Notifications\WelcomeNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * UserController — User Access & Role Administration
 *
 * Implements FR-6 (Authentication Gate & Role Management):
 *   - index(): List all team members with pagination and role indicators
 *   - create() / store(): Provision support operators and team leads with temporary passwords
 *   - edit() / update(): Modify operator designations, roles, contact details, and audit changes
 *   - resetPassword(): Admin-triggered time-limited password reset flow via broker
 *   - destroy(): Revoke user access and log audit trail
 *
 * SECURITY & AUDIT DISCIPLINE:
 * Every mutation (create, update, password reset, delete) invokes AuditService to record
 * an immutable compliance trail containing actor details, client IP, and diffs.
 */
class UserController extends Controller
{
    /**
     * Inject the AuditService compliance coordinator.
     *
     * @param  AuditService  $auditService  Audit logging service for security tracking
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Display a paginated list of all system users.
     *
     * @return View Admin user management index view
     */
    public function index(): View
    {
        // Enforce policy: verify authenticated administrator permissions
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the user provisioning form.
     *
     * @return View User creation form
     */
    public function create(): View
    {
        // Enforce policy: verify user has permission to provision accounts
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Store a new user account and dispatch a welcome notification.
     *
     * Flow:
     *   1. Validate input via StoreUserRequest.
     *   2. Hash temporary password with bcrypt (cost factor 12).
     *   3. Persist user model.
     *   4. Write immutable security audit log (excluding sensitive plaintext credentials).
     *   5. Dispatch WelcomeNotification with login link and temporary credentials.
     *
     * @param  StoreUserRequest  $request  Validated user creation request
     * @return RedirectResponse Redirect to user catalog
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        // Preserve temporary password in memory for one-time inclusion in welcome email
        $temporaryPassword = $data['password'];
        $data['password'] = Hash::make($temporaryPassword);

        $user = User::create($data);

        // Record compliance audit trail (sensitive password field excluded from old/new diff)
        $this->auditService->log(
            subject: $user,
            event: 'created',
            newValues: collect($data)->except('password')->toArray(),
        );

        // ── Dispatch Welcome Email ────────────────────────────────────────
        try {
            $user->notify(new WelcomeNotification(
                temporaryPassword: $temporaryPassword,
                loginUrl: route('login'),
            ));
        } catch (\Throwable) {
            // Mail transport failure must not roll back account creation.
            // Admin receives explicit warning notification to verify mail configuration.
            return redirect()
                ->route('admin.users.index')
                ->with('warning', "User \"{$user->name}\" created, but the welcome email could not be sent. Check your mail configuration.");
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created and a welcome email has been sent to {$user->email}.");
    }

    /**
     * Show the form for editing an existing team member's profile.
     *
     * @param  User  $user  User instance to edit
     * @return View Edit user view
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user profile information, designation, or access role.
     *
     * Captures before/after snapshot diffs for security audit compliance.
     *
     * @param  UpdateUserRequest  $request  Validated update parameters
     * @param  User  $user  Target user model
     * @return RedirectResponse Redirect to user catalog with confirmation
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $old = $user->only(['name', 'email', 'role', 'designation', 'phone']);
        $user->update($request->validated());

        $this->auditService->log(
            subject: $user,
            event: 'updated',
            oldValues: $old,
            newValues: $request->validated(),
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" updated successfully.");
    }

    /**
     * Trigger a secure, admin-initiated password reset link for a team member.
     *
     * SECURITY DESIGN:
     * - The administrator never sets or views the user's password.
     * - Generates an ephemeral, cryptographically secure token via Laravel's password broker.
     * - The user sets their own credential via the signed link (expires in 60 minutes).
     *
     * @param  User  $user  Target user requesting credential reset
     * @return RedirectResponse Redirect with confirmation
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        // Generate password reset token using Laravel password broker
        $token = Password::broker()->createToken($user);

        // Build signed reset URL matching standard password reset route
        $resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ], absolute: false));

        // Dispatch notification email
        try {
            $user->notify(new AdminPasswordResetNotification(
                resetUrl: $resetUrl,
                adminName: auth()->user()->name,
            ));
        } catch (\Throwable) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "Could not send password-reset email to {$user->email}. Check mail configuration.");
        }

        // Write security audit log tracking who initiated the reset
        $this->auditService->log(
            subject: $user,
            event: 'password_reset_requested',
            newValues: ['initiated_by' => auth()->user()->name],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "A password-reset link has been sent to {$user->name} ({$user->email}). The link expires in 60 minutes.");
    }

    /**
     * Remove a user account and revoke access privileges.
     *
     * Writes compliance audit trail before deleting the record.
     *
     * @param  User  $user  User to delete
     * @return RedirectResponse Redirect to user catalog
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->auditService->log(
            subject: $user,
            event: 'deleted',
            oldValues: $user->only(['name', 'email', 'role', 'designation']),
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" has been removed.");
    }
}
