<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SRE Password Reset Authorization</title>
</head>
<body style="margin: 0; padding: 0; background-color: #08100B; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #E2E8F0;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #08100B; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Main Email Card Container -->
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 580px; background-color: #0C1A12; border: 1px solid #1A3826; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #050B07; padding: 24px 30px; border-bottom: 2px solid #F5C518;">
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
                                        <span style="display: inline-block; padding: 4px 10px; background-color: rgba(245, 197, 24, 0.15); border: 1px solid #F5C518; border-radius: 20px; color: #F5C518; font-size: 11px; font-weight: 700; font-family: monospace; text-transform: uppercase;">
                                            SECURITY NOTICE
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
                                SRE Account Password Reset
                            </h2>
                            
                            <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #CBD5E1;">
                                Hello <strong style="color: #FFFFFF;">{{ $user->name }}</strong>,
                            </p>

                            <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #CBD5E1;">
                                We received a formal request to reset the password for your operational account (<code style="color: #A7F3D0; font-family: monospace;">{{ $user->email }}</code>).
                            </p>

                            <!-- Security Alert Box -->
                            <div style="background-color: rgba(0,0,0,0.35); border-left: 4px solid #F5C518; border-radius: 0 8px 8px 0; padding: 14px 16px; margin-bottom: 24px;">
                                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: bold; color: #F5C518; font-family: monospace; text-transform: uppercase;">
                                    Operational Security Safeguard
                                </p>
                                <p style="margin: 0; font-size: 12px; color: #94A3B8; line-height: 1.5;">
                                    This authorization link will expire in <strong>60 minutes</strong>. If you did not initiate this request, notify the SRE Security Lead immediately as your email or credentials may have been targeted.
                                </p>
                            </div>

                            <!-- Reset Button CTA -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #F5C518; color: #0A120E; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(245, 197, 24, 0.4); text-transform: uppercase; letter-spacing: 0.5px;">
                                    Reset Password &rarr;
                                </a>
                            </div>

                            <p style="margin: 24px 0 0 0; font-size: 12px; color: #64748B; line-height: 1.5; word-break: break-all;">
                                If the button above does not work, copy and paste this link into your browser:<br>
                                <a href="{{ $resetUrl }}" style="color: #10B981; text-decoration: underline;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer Disclaimer -->
                    <tr>
                        <td style="background-color: #050B07; padding: 20px 30px; border-top: 1px solid #14261B; text-align: center; font-size: 11px; color: #64748B; font-family: monospace;">
                            <p style="margin: 0 0 4px 0;">
                                Emergency NOC Security Desk: <span style="color: #F5C518;">security@npontu.local</span>
                            </p>
                            <p style="margin: 0;">
                                &copy; {{ date('Y') }} Npontu Technologies Limited &bull; Accra, Ghana.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
