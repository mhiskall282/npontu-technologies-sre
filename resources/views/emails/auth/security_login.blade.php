<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRE Security Alert — New Session Authenticated</title>
</head>
<body style="margin: 0; padding: 0; background-color: #08100B; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #E2E8F0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #08100B; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Main Email Card Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #0C1A12; border: 1px solid #1A3826; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #050B07; padding: 24px 30px; border-bottom: 2px solid #10B981;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td>
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align: middle; padding-right: 12px;">
                                                    <div style="width: 38px; height: 38px; background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; text-align: center; line-height: 38px;">
                                                        <span style="color: #F5C518; font-size: 20px; font-weight: bold;">&#9650;</span>
                                                    </div>
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <span style="font-size: 16px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.3px; display: block;">Support Tracker</span>
                                                    <span style="font-size: 9px; font-family: monospace; color: #F5C518; letter-spacing: 1.5px; text-transform: uppercase; font-weight: bold; display: block; margin-top: 2px;">NPONTU TECHNOLOGIES</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="display: inline-block; padding: 4px 10px; background-color: rgba(16, 185, 129, 0.2); border: 1px solid #10B981; border-radius: 20px; color: #34D399; font-size: 11px; font-weight: 700; font-family: monospace; text-transform: uppercase;">
                                            SESSION SIGN-IN
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="margin: 0 0 12px 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">
                                New SRE Session Initiated
                            </h2>
                            
                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #CBD5E1;">
                                Hello <strong style="color: #FFFFFF;">{{ $user->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #CBD5E1;">
                                An operator session was successfully authenticated for your account on the Support Activity Tracker platform.
                            </p>

                            <!-- Session Details Card -->
                            <div style="background-color: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="font-size: 12px; font-family: monospace;">
                                    <tr>
                                        <td style="padding: 4px 0; color: #94A3B8;">Timestamp (UTC):</td>
                                        <td style="padding: 4px 0; color: #FFFFFF; font-weight: bold; text-align: right;">{{ $loginTime }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #94A3B8;">IP Address:</td>
                                        <td style="padding: 4px 0; color: #F5C518; font-weight: bold; text-align: right;">{{ $ipAddress }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #94A3B8;">Operator Grade:</td>
                                        <td style="padding: 4px 0; color: #34D399; font-weight: bold; text-align: right;">{{ $user->grade ?? 'L2' }} &bull; {{ $user->role }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #94A3B8;">Node Cluster:</td>
                                        <td style="padding: 4px 0; color: #CBD5E1; text-align: right;">Accra-Cluster-01</td>
                                    </tr>
                                </table>
                            </div>

                            <p style="margin: 0 0 24px 0; font-size: 13px; color: #94A3B8; line-height: 1.5;">
                                If this was you assuming your scheduled operational shift, no further action is required. If you did not sign in, click below to lock your session and escalate to the SRE Security Lead.
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 25px 0;">
                                <a href="{{ route('activities.daily') }}" style="display: inline-block; padding: 12px 28px; background-color: #1B6B3A; color: #FFFFFF; font-weight: 700; font-size: 13px; text-decoration: none; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Access Shift Cockpit &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #050B07; padding: 20px 30px; border-top: 1px solid #14261B; text-align: center; font-size: 11px; color: #64748B; font-family: monospace;">
                            <p style="margin: 0 0 4px 0;">
                                Emergency Incident Desk: <span style="color: #F5C518;">sre-emergency@npontu.local</span>
                            </p>
                            <p style="margin: 0;">
                                &copy; {{ date('Y') }} Npontu Technologies Limited &bull; Greater Accra, Ghana.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
