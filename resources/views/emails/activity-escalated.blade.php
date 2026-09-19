<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SRE Incident Escalation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F7F5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F4F7F5; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    {{-- Alert Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #7F1D1D 0%, #DC2626 100%); padding: 30px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 800; color: #FEE2E2; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 4px;">
                                            CRITICAL SRE ALERT &bull; ESCALATION
                                        </div>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">
                                            🚨 Incident Escalated [{{ $severity }}]
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 30px;">
                            <p style="font-size: 15px; margin-top: 0; margin-bottom: 16px;">
                                Attention <strong>{{ $recipient->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-top: 0; margin-bottom: 20px;">
                                An operational activity has been flagged as an active <strong>SRE Incident</strong> requiring immediate investigation or war room coordination.
                            </p>

                            {{-- Incident Box --}}
                            <div style="background-color: #FEF2F2; border-left: 4px solid #DC2626; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 14px; font-weight: 700; color: #991B1B; margin-bottom: 8px; line-height: 1.5;">
                                    {{ $activity->description }}
                                </div>
                                <div style="font-size: 12px; color: #7F1D1D; font-family: monospace;">
                                    Status: <strong>{{ strtoupper(str_replace('_', ' ', $activity->status)) }}</strong>
                                    &bull; Escalated by: {{ $escalatedBy?->name ?? 'System Monitor' }}
                                    @if($activity->workspace)
                                        &bull; Workspace: {{ $activity->workspace->name }}
                                    @endif
                                </div>
                            </div>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top: 24px; margin-bottom: 24px;">
                                <tr>
                                    <td style="border-radius: 8px; background-color: #DC2626;">
                                        <a href="{{ route('activities.daily') }}" style="font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 12px 24px; display: inline-block; border-radius: 8px;">
                                            Enter Incident War Room &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #9ca3af; margin: 0; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                Opsora SRE Operations Platform &bull; Automated Telemetry &bull; Confidential
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
