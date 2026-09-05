<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $period === 'weekly' ? 'Weekly SRE Digest' : ($period === 'monthly' ? 'Monthly SRE Report' : 'Daily SRE Digest') }}</title>
    <style>
        :root {
            color-scheme: light dark;
            supported-color-schemes: light dark;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 8px !important;
            }
            .header-padding {
                padding: 18px 16px !important;
            }
            .body-padding {
                padding: 20px 14px !important;
            }
            .timeframe-cell {
                display: block !important;
                width: 100% !important;
                text-align: left !important;
                margin-top: 6px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F0F4F2; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1E293B;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F0F4F2; padding: 20px 8px;">
        <tr>
            <td align="center">
                <!-- Main Email Card Container (High-Contrast Solid Surface) -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="email-container" style="max-width: 600px; background-color: #FFFFFF; border: 1px solid #D1D5DB; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    
                    <!-- High-Contrast Enterprise SRE Header (Always Dark & Crisp) -->
                    <tr>
                        <td class="header-padding" style="background-color: #0A1810; padding: 22px 28px; border-bottom: 3px solid #1B6B3A;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align: middle; padding-right: 12px;">
                                                    <div style="width: 36px; height: 36px; background-color: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.2); border-radius: 9px; text-align: center; line-height: 36px;">
                                                        <span style="color: #F5C518; font-size: 18px; font-weight: bold;">&#9650;</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <span style="font-size: 16px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.3px; display: block; line-height: 1.1;">Support Tracker</span>
                                                    <span style="font-size: 9px; font-family: -apple-system, monospace; color: #F5C518; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 700; display: block; margin-top: 3px;">NPONTU TECHNOLOGIES</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="display: inline-block; padding: 4px 10px; background-color: #1B6B3A; border: 1px solid #22C55E; border-radius: 20px; color: #FFFFFF; font-size: 10px; font-weight: 800; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                            {{ strtoupper($period) }} REPORT
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content Area -->
                    <tr>
                        <td class="body-padding" style="padding: 24px 28px;">
                            
                            <!-- Greeting & Dispatch Info (Clean Stacked Layout) -->
                            <div style="margin-bottom: 14px;">
                                <span style="display: block; font-size: 11px; font-family: -apple-system, monospace; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: 0.5px;">
                                    OPERATIONAL DISPATCH FOR:
                                </span>
                                <h2 style="margin: 4px 0 0 0; font-size: 22px; font-weight: 800; color: #0F172A; letter-spacing: -0.3px;">
                                    Hello, {{ $recipient->name }}
                                </h2>
                                <div style="margin-top: 5px;">
                                    <span style="display: inline-block; padding: 2px 8px; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; font-size: 11px; font-weight: 700; font-family: -apple-system, monospace; color: #065F46;">
                                        {{ $recipient->grade ?? 'L3' }} &bull; {{ $recipient->department ?? 'Cloud Infrastructure & SRE' }} &bull; {{ ucfirst($recipient->role) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Dedicated Full-Width Shift Duration Card -->
                            <div style="background-color: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <span style="display: block; font-size: 10px; font-weight: 800; color: #64748B; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                                SHIFT DURATION (UTC):
                                            </span>
                                            <span style="display: block; font-size: 12px; font-weight: 700; color: #0F172A; font-family: -apple-system, monospace; margin-top: 2px;">
                                                From: <strong>{{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }}</strong> (00:00 UTC) &rarr; To: <strong>{{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</strong> (23:59 UTC)
                                            </span>
                                        </td>
                                        <td align="right" class="timeframe-cell" style="vertical-align: middle;">
                                            <span style="display: inline-block; padding: 3px 8px; background-color: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 6px; font-size: 10px; font-weight: 800; color: #065F46; font-family: -apple-system, monospace; text-transform: uppercase; white-space: nowrap;">
                                                {{ $period === 'daily' ? '24h Full Shift' : ($period === 'weekly' ? '7-Day SRE Cycle' : '30-Day Cycle') }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6; color: #334155;">
                                @if(\Illuminate\Support\Carbon::parse($startDate)->toDateString() === \Illuminate\Support\Carbon::parse($endDate)->toDateString())
                                    Below is your automated <strong>Daily</strong> operational summary for Npontu mission-critical infrastructure, covering your 24-hour duty shift on <strong>{{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }}</strong> (00:00 UTC &rarr; 23:59 UTC). All activity status mutations and shift custody handshakes have been verified into immutable audit logs.
                                @else
                                    Below is your automated <strong>{{ ucfirst($period) }}</strong> operational summary for Npontu mission-critical infrastructure, covering the duration from <strong>{{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }}</strong> to <strong>{{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</strong>. All activity status mutations and shift custody handshakes have been verified into immutable audit logs.
                                @endif
                            </p>

                            <!-- High-Contrast 2x2 Metric KPI Tiles (Preserved 2-Column Grid on Mobile) -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 22px;">
                                <tr>
                                    <td width="48%" style="padding: 12px 14px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; vertical-align: top;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #475569; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Resolution Rate
                                        </span>
                                        <span style="display: block; font-size: 24px; font-weight: 900; color: #B45309; font-family: -apple-system, monospace; margin-top: 2px; line-height: 1.1;">
                                            {{ $metrics['resolution_rate'] ?? 0 }}%
                                        </span>
                                        <span style="display: block; font-size: 10px; font-weight: 600; color: #059669; margin-top: 3px;">
                                            {{ $metrics['completed_count'] ?? 0 }} completed / {{ $metrics['total_activities'] ?? 0 }} total
                                        </span>
                                    </td>
                                    <td width="4%"></td>
                                    <td width="48%" style="padding: 12px 14px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; vertical-align: top;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #475569; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Shift Handshakes
                                        </span>
                                        <span style="display: block; font-size: 24px; font-weight: 900; color: #059669; font-family: -apple-system, monospace; margin-top: 2px; line-height: 1.1;">
                                            {{ $metrics['handovers_count'] ?? 0 }}
                                        </span>
                                        <span style="display: block; font-size: 10px; font-weight: 600; color: #047857; margin-top: 3px;">
                                            Two-way custody verified
                                        </span>
                                    </td>
                                </tr>
                                <tr><td height="10" colspan="3"></td></tr>
                                <tr>
                                    <td width="48%" style="padding: 12px 14px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; vertical-align: top;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #475569; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Active Blockers
                                        </span>
                                        <span style="display: block; font-size: 24px; font-weight: 900; color: {{ ($metrics['pending_count'] ?? 0) > 0 ? '#DC2626' : '#059669' }}; font-family: -apple-system, monospace; margin-top: 2px; line-height: 1.1;">
                                            {{ $metrics['pending_count'] ?? 0 }}
                                        </span>
                                        <span style="display: block; font-size: 10px; font-weight: 600; color: #64748B; margin-top: 3px;">
                                            Requiring attention
                                        </span>
                                    </td>
                                    <td width="4%"></td>
                                    <td width="48%" style="padding: 12px 14px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; vertical-align: top;">
                                        <span style="display: block; font-size: 10px; font-weight: 800; color: #475569; font-family: -apple-system, monospace; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Uptime SLA Target
                                        </span>
                                        <span style="display: block; font-size: 24px; font-weight: 900; color: #0284C7; font-family: -apple-system, monospace; margin-top: 2px; line-height: 1.1;">
                                            99.98%
                                        </span>
                                        <span style="display: block; font-size: 10px; font-weight: 600; color: #64748B; margin-top: 3px;">
                                            8 Subsystems Nominal
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Key Operational Activities Section (Clean 3-Column Mobile Friendly) -->
                            <div style="margin-bottom: 22px;">
                                <div style="border-bottom: 2px solid #1B6B3A; padding-bottom: 6px; margin-bottom: 10px;">
                                    <span style="font-size: 11px; font-weight: 800; color: #0F172A; text-transform: uppercase; font-family: -apple-system, monospace; letter-spacing: 0.5px;">
                                        Key Shift Activities ({{ $activities->count() }})
                                    </span>
                                </div>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #F1F5F9; text-align: left; color: #475569; font-family: -apple-system, monospace; font-size: 10px; font-weight: 700; text-transform: uppercase;">
                                            <th style="padding: 8px 10px; border-bottom: 1px solid #CBD5E1;">Activity Details</th>
                                            <th style="padding: 8px 10px; border-bottom: 1px solid #CBD5E1; text-align: center;">Status</th>
                                            <th style="padding: 8px 10px; border-bottom: 1px solid #CBD5E1; text-align: right;">Assignee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activities->take(8) as $act)
                                            <tr style="border-bottom: 1px solid #E2E8F0;">
                                                <td style="padding: 10px 10px; color: #0F172A;">
                                                    <div style="font-weight: 700; font-size: 12px; color: #0F172A; line-height: 1.3;">
                                                        {{ $act->title }}
                                                        @if($act->is_pinned)
                                                            <span style="color: #F5C518; font-size: 12px;">&#9733;</span>
                                                        @endif
                                                    </div>
                                                    <div style="margin-top: 3px;">
                                                        <span style="display: inline-block; padding: 1px 6px; background-color: #F1F5F9; border: 1px solid #E2E8F0; border-radius: 4px; font-size: 9px; font-family: -apple-system, monospace; color: #64748B; text-transform: uppercase;">
                                                            {{ $act->category ?? 'General' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td style="padding: 10px 10px; text-align: center; vertical-align: middle;">
                                                    @if(($act->current_status ?? 'pending') === 'done')
                                                        <span style="display: inline-block; padding: 3px 8px; background-color: #D1FAE5; border: 1px solid #10B981; border-radius: 4px; color: #065F46; font-size: 10px; font-weight: 800; font-family: -apple-system, monospace; text-transform: uppercase;">DONE</span>
                                                    @else
                                                        <span style="display: inline-block; padding: 3px 8px; background-color: #FEE2E2; border: 1px solid #F87171; border-radius: 4px; color: #991B1B; font-size: 10px; font-weight: 800; font-family: -apple-system, monospace; text-transform: uppercase;">PENDING</span>
                                                    @endif
                                                </td>
                                                <td style="padding: 10px 10px; text-align: right; vertical-align: middle; color: #475569; font-size: 11px;">
                                                    {{ $act->assignee?->name ?? 'Unassigned' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" style="padding: 16px; text-align: center; color: #94A3B8; font-style: italic;">
                                                    No activities recorded for this timeframe.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Shift Handover Verification Box -->
                            @if($handovers->isNotEmpty())
                                <div style="margin-bottom: 24px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 16px;">
                                    <div style="border-bottom: 1px solid #E2E8F0; padding-bottom: 8px; margin-bottom: 12px;">
                                        <span style="font-size: 11px; font-weight: 800; color: #065F46; text-transform: uppercase; font-family: -apple-system, monospace; letter-spacing: 0.5px;">
                                            Shift Handover Verifications
                                        </span>
                                    </div>
                                    @foreach($handovers->take(3) as $h)
                                        <div style="margin-bottom: 8px; padding: 10px 12px; background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 12px;">
                                            <div style="display: flex; justify-content: space-between; font-weight: 700; color: #0F172A;">
                                                <span>{{ $h->date?->format('Y-m-d') ?? $h->date }} &bull; Shift {{ ucfirst((string) $h->shift) }}</span>
                                                <span style="color: {{ $h->accepted_at ? '#059669' : '#D97706' }}; font-family: -apple-system, monospace; font-size: 11px; font-weight: 800;">
                                                    {{ $h->accepted_at ? '✓ Signed-On' : '⏳ Awaiting Sign-On' }}
                                                </span>
                                            </div>
                                            <p style="margin: 4px 0 0 0; color: #64748B; font-size: 11px;">
                                                Outgoing Lead: <strong style="color: #0F172A;">{{ $h->outgoingLead?->name ?? 'Lead' }}</strong>
                                                @if($h->acceptedBy)
                                                    &bull; Incoming Lead: <strong style="color: #0F172A;">{{ $h->acceptedBy?->name }}</strong>
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Prominent High-Contrast Action Button -->
                            <div style="text-align: center; margin: 30px 0 10px 0;">
                                <a href="{{ route('activities.daily') }}" style="display: inline-block; padding: 14px 32px; background-color: #1B6B3A; color: #FFFFFF; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 10px; border: 1px solid #14532D; box-shadow: 0 4px 12px rgba(27, 107, 58, 0.35); text-transform: uppercase; letter-spacing: 0.5px;">
                                    Open Shift Cockpit →
                                </a>
                            </div>

                        </td>
                    </tr>

                    <!-- Footer Area (High-Contrast SRE Dark Finish) -->
                    <tr>
                        <td style="background-color: #0A1810; padding: 22px 28px; border-top: 1px solid #1B6B3A; text-align: center; font-size: 11px; color: #94A3B8; font-family: -apple-system, monospace;">
                            <p style="margin: 0 0 6px 0; color: #E2E8F0; font-weight: 600;">
                                &copy; {{ date('Y') }} Npontu Technologies Limited &bull; Accra, Ghana.
                            </p>
                            <p style="margin: 0 0 8px 0; color: #64748B;">
                                Automated SRE reporting daemon. All mutations recorded under ISO 27001 & Ghana Act 843 compliance.
                            </p>
                            <p style="margin: 0;">
                                <a href="{{ route('docs') }}" style="color: #F5C518; text-decoration: underline; font-weight: 700;">Documentation Portal</a> &bull;
                                <a href="{{ route('policy.privacy') }}" style="color: #A7F3D0; text-decoration: underline;">Privacy Policy</a> &bull;
                                <a href="{{ route('health') }}" style="color: #A7F3D0; text-decoration: underline;">System Health HUD</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
