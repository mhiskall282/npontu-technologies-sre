<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Opsora SRE</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F7F5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F4F7F5; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e5e7eb;">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #12492A 0%, #1B6B3A 100%); padding: 30px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 11px; font-weight: 800; color: #F5C518; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 4px;">
                                            OPSORA SRE PLATFORM &bull; ONBOARDING
                                        </div>
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff;">
                                            🚀 Welcome to Opsora SRE
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
                                Welcome <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-top: 0; margin-bottom: 20px;">
                                Your account has been provisioned on the Opsora SRE Operations Platform. You are ready to log activities, manage multi-tenant workspaces, and track uptime SLAs.
                            </p>

                            @if($organization)
                            {{-- Org Info Box --}}
                            <div style="background-color: #f9fafb; border-left: 4px solid #1B6B3A; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; color: #1B6B3A; margin-bottom: 6px; text-transform: uppercase;">
                                    Tenant Organization Details
                                </div>
                                <div style="font-size: 15px; font-weight: 700; color: #111827; margin-bottom: 4px;">
                                    {{ $organization->name }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280; font-family: monospace;">
                                    Company Code: <strong style="color: #1B6B3A;">{{ $organization->company_code }}</strong> &bull; Tier: {{ ucfirst($organization->tier) }}
                                </div>
                                <p style="font-size: 12px; color: #6b7280; margin-top: 8px; margin-bottom: 0;">
                                    Share your Company Code with teammates so they can join your operational workspace.
                                </p>
                            </div>
                            @endif

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top: 24px; margin-bottom: 24px;">
                                <tr>
                                    <td style="border-radius: 8px; background-color: #1B6B3A;">
                                        <a href="{{ route('dashboard') }}" style="font-size: 14px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 12px 24px; display: inline-block; border-radius: 8px;">
                                            Access Your SRE Dashboard &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 12px; color: #9ca3af; margin: 0; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                Need assistance? Reach out to support at hello@johnokyere.xyz &bull; Confidential
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
