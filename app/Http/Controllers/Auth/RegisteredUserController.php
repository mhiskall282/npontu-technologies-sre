<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OrganizationWelcomeMail;
use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\SecurityEvent;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Workspace;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming self-service registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_code' => ['nullable', 'string', 'max:50'],
            'organization_name' => ['nullable', 'string', 'max:100'],
        ]);

        return TenantContext::withoutTenancy(function () use ($request): RedirectResponse {
            $companyCode = $request->filled('company_code') ? trim((string) $request->input('company_code')) : null;
            $orgName = $request->filled('organization_name') ? trim((string) $request->input('organization_name')) : null;

            if ($companyCode) {
                $existingOrg = Organization::where('company_code', $companyCode)
                    ->where('status', 'active')
                    ->first();

                if (! $existingOrg) {
                    return back()->withInput()->withErrors([
                        'company_code' => 'The provided company code is invalid or the organization is not active.',
                    ]);
                }
            }

            return DB::transaction(function () use ($request, $companyCode, $orgName): RedirectResponse {
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'role' => 'engineer',
                    'department' => 'SRE Operations',
                ]);

                $organization = null;
                $workspace = null;

                if ($companyCode) {
                    $organization = Organization::where('company_code', $companyCode)
                        ->where('status', 'active')
                        ->first();

                    if ($organization) {
                        $organization->users()->attach($user->id, ['role' => 'member']);

                        // Attach to organization's primary workspace
                        $workspace = $organization->workspaces()->where('is_personal', false)->first();
                        if ($workspace) {
                            $workspace->members()->attach($user->id, ['role' => 'member']);
                        }
                    }
                } elseif ($orgName) {
                    // Create new Tenant Organization
                    $slug = Str::slug($orgName);
                    $count = Organization::where('slug', 'like', "{$slug}%")->count();
                    if ($count > 0) {
                        $slug .= '-'.($count + 1);
                    }

                    $generatedCode = 'NPT-'.strtoupper(Str::random(3)).'-'.rand(100, 999);

                    $organization = Organization::create([
                        'uuid' => (string) Str::uuid(),
                        'name' => $orgName,
                        'slug' => $slug,
                        'company_code' => $generatedCode,
                        'status' => 'active',
                        'tier' => 'free',
                        'deployment_model' => 'shared_saas',
                        'preferred_region' => 'af-south',
                    ]);

                    $organization->users()->attach($user->id, ['role' => 'admin']);

                    // Create primary workspace
                    $wsSlug = $organization->slug.'-primary';
                    $wsCount = Workspace::where('slug', 'like', "{$wsSlug}%")->count();
                    if ($wsCount > 0) {
                        $wsSlug .= '-'.($wsCount + 1);
                    }

                    $workspace = Workspace::create([
                        'uuid' => (string) Str::uuid(),
                        'organization_id' => $organization->id,
                        'owner_user_id' => $user->id,
                        'name' => "{$organization->name} Primary SRE",
                        'slug' => $wsSlug,
                        'is_personal' => false,
                        'status' => 'active',
                        'retention_days' => 90,
                    ]);

                    $workspace->members()->attach($user->id, ['role' => 'admin']);

                    // Automatically provision default Trial / Free Subscription
                    $plan = Plan::where('tier', 'free')->first() ?? Plan::first();
                    if ($plan) {
                        Subscription::create([
                            'organization_id' => $organization->id,
                            'plan_id' => $plan->id,
                            'status' => 'active',
                            'current_period_start' => now(),
                            'current_period_end' => now()->addMonth(),
                            'billing_provider' => 'ready',
                        ]);
                    }
                } else {
                    // Create personal sandbox workspace for standalone engineer
                    $wsSlug = Str::slug($user->name).'-sandbox-'.rand(10, 99);
                    $workspace = Workspace::create([
                        'uuid' => (string) Str::uuid(),
                        'organization_id' => null,
                        'owner_user_id' => $user->id,
                        'name' => "{$user->name}'s Sandbox",
                        'slug' => $wsSlug,
                        'is_personal' => true,
                        'status' => 'active',
                        'retention_days' => 30,
                    ]);

                    $workspace->members()->attach($user->id, ['role' => 'admin']);
                }

                if ($workspace) {
                    session(['active_workspace_id' => $workspace->id]);
                    TenantContext::setWorkspace($workspace);
                }

                // Security & SIEM Event
                SecurityEvent::record(
                    eventType: 'user_registered',
                    severity: 'info',
                    actor: $user,
                    details: [
                        'name' => $user->name,
                        'email' => $user->email,
                        'joined_org' => $organization?->name,
                        'workspace_id' => $workspace?->id,
                    ],
                    ip: $request->ip() ?? '127.0.0.1'
                );

                AuditLog::create([
                    'actor_id' => $user->id,
                    'actor_name' => $user->name,
                    'subject_type' => User::class,
                    'subject_id' => $user->id,
                    'event' => 'user_registered',
                    'new_values' => [
                        'email' => $user->email,
                        'organization_id' => $organization?->id,
                        'workspace_id' => $workspace?->id,
                    ],
                    'ip_address' => $request->ip() ?? '127.0.0.1',
                    'created_at' => now(),
                ]);

                // Dispatch customized welcome email
                try {
                    Mail::to($user->email)->queue(new OrganizationWelcomeMail($user, $organization));
                } catch (\Throwable $e) {
                    report($e);
                }

                Auth::login($user);

                return redirect()->route('dashboard');
            });
        });
    }
}
