<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * SettingsController — Self-Service Operator Profile & Security Settings
 *
 * Allows authenticated personnel to:
 *   - edit(): View account details, designation, and current access permissions
 *   - update(): Update contact details and professional designation
 *   - updatePassword(): Verify current password and securely hash a new credential
 *
 * SECURITY & AUDITING:
 * Profile mutations and credential changes are recorded as compliance audit trails via AuditService.
 */
class SettingsController extends Controller
{
    /**
     * Inject AuditService compliance dependency.
     *
     * @param  AuditService  $auditService  Service managing write-only audit trail
     */
    public function __construct(private readonly AuditService $auditService) {}

    /**
     * Show self-service settings dashboard.
     *
     * @param  Request  $request  HTTP request
     * @return View Settings view
     */
    public function edit(Request $request): View
    {
        $this->authorize('update', $request->user());
        $user = $request->user();

        return view('settings.edit', compact('user'));
    }

    /**
     * Update authenticated user's contact information and designation.
     *
     * @param  Request  $request  HTTP request with validated inputs
     * @return RedirectResponse Redirect with confirmation
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'designation' => ['nullable', 'string', 'max:150'],
        ]);

        $old = $user->only(['name', 'email', 'phone', 'designation']);
        $user->update($validated);

        // Record profile update compliance trail
        $this->auditService->log($user, 'profile_updated', $old, $validated);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Profile settings updated successfully.');
    }

    /**
     * Update authenticated user's login credential.
     *
     * Validates existing password against current hash prior to bcrypt encryption of new credential.
     *
     * @param  Request  $request  HTTP request
     * @return RedirectResponse Redirect with confirmation
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('update', $user);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Record security audit trail (sensitive credentials excluded)
        $this->auditService->log($user, 'password_changed');

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Password updated successfully.');
    }
}
