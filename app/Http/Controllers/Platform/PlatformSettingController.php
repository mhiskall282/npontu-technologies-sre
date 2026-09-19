<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

final class PlatformSettingController extends Controller
{
    /**
     * Display Platform Enterprise Settings & Policy Configuration.
     */
    public function index(): View
    {
        $settings = Cache::get('opsora_platform_settings', [
            'platform_name' => 'Opsora SRE',
            'support_email' => 'hello@johnokyere.xyz',
            'enforce_data_residency' => true,
            'default_region' => 'af-south',
            'allow_self_service_registration' => true,
            'require_org_application_review' => false,
            'white_label_enabled' => true,
            'maintenance_mode' => false,
            'maintenance_message' => 'Opsora SRE is undergoing scheduled platform maintenance. Services will resume shortly.',
            'maintenance_ends_at' => null,
            'maintenance_bypass_key' => 'sre-opsora-emergency-bypass',
        ]);

        return view('admin.platform.settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update platform enterprise configurations.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_name' => 'required|string|max:100',
            'support_email' => 'required|email',
            'default_region' => 'required|string|in:af-south,us-east,eu-west',
            'enforce_data_residency' => 'nullable|boolean',
            'allow_self_service_registration' => 'nullable|boolean',
            'require_org_application_review' => 'nullable|boolean',
            'white_label_enabled' => 'nullable|boolean',
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string|max:1000',
            'maintenance_ends_at' => 'nullable|string|max:100',
            'maintenance_bypass_key' => 'nullable|string|max:100',
        ]);

        $settings = [
            'platform_name' => $validated['platform_name'],
            'support_email' => $validated['support_email'],
            'default_region' => $validated['default_region'],
            'enforce_data_residency' => (bool) ($validated['enforce_data_residency'] ?? false),
            'allow_self_service_registration' => (bool) ($validated['allow_self_service_registration'] ?? false),
            'require_org_application_review' => (bool) ($validated['require_org_application_review'] ?? false),
            'white_label_enabled' => (bool) ($validated['white_label_enabled'] ?? false),
            'maintenance_mode' => (bool) ($validated['maintenance_mode'] ?? false),
            'maintenance_message' => $validated['maintenance_message'] ?: 'Opsora SRE is undergoing scheduled platform maintenance. Services will resume shortly.',
            'maintenance_ends_at' => $validated['maintenance_ends_at'] ?: null,
            'maintenance_bypass_key' => $validated['maintenance_bypass_key'] ?: 'sre-opsora-emergency-bypass',
        ];

        Cache::forever('opsora_platform_settings', $settings);

        AuditLog::create([
            'actor_id' => $request->user()->id,
            'actor_name' => $request->user()->name,
            'subject_type' => 'PlatformSettings',
            'subject_id' => 0,
            'event' => 'platform_settings_updated',
            'new_values' => $settings,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.platform.settings.index')
            ->with('success', 'Platform settings, maintenance state, and enterprise policies updated successfully.');
    }
}
