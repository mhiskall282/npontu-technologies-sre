<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SRE Comms Notification</title>
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
                                            NPONTU TECHNOLOGIES — SRE OPS
                                        </div>
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #ffffff;">
                                            @if($isDirectMessage ?? false)
                                                💬 Direct Operational Message
                                            @elseif($isBroadcast)
                                                📢 Team-Wide SRE Broadcast
                                            @else
                                                💬 Operational Mention Notification
                                            @endif
                                        </h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body Content --}}
                    <tr>
                        <td style="padding: 30px;">
                            <p style="font-size: 15px; margin-top: 0; margin-bottom: 16px;">
                                Hello <strong>{{ $recipient->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; line-height: 1.6; color: #4b5563; margin-top: 0; margin-bottom: 20px;">
                                @if($isDirectMessage ?? false)
                                    <strong>{{ $chatMessage->sender?->name }}</strong> sent you a direct operational message in SRE Operations Comms:
                                @elseif($isBroadcast)
                                    <strong>{{ $chatMessage->sender?->name }}</strong> sent an <strong>@all</strong> broadcast in
                                    <strong style="color: #1B6B3A;">#{{ $conversation->title ?? 'General Shift' }}</strong>:
                                @else
                                    <strong>{{ $chatMessage->sender?->name }}</strong> mentioned you in
                                    <strong style="color: #1B6B3A;">{{ $conversation->title ?? 'Shift Operations' }}</strong>:
                                @endif
                            </p>

                            {{-- Quoted Message Bubble --}}
                            <div style="background-color: #f9fafb; border-left: 4px solid #1B6B3A; border-radius: 8px; padding: 18px 20px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; color: #1B6B3A; margin-bottom: 6px;">
                                    {{ $chatMessage->sender?->name }}
                                    @if($chatMessage->sender?->grade)
                                        <span style="font-size: 10px; background: #e5e7eb; color: #374151; padding: 2px 6px; border-radius: 4px; font-family: monospace; margin-left: 4px;">
                                            {{ $chatMessage->sender->grade }}
                                        </span>
                                    @endif
                                    <span style="font-size: 11px; font-weight: 400; color: #9ca3af; margin-left: 8px;">
                                        {{ $chatMessage->created_at->format('H:i \G\M\T') }}
                                    </span>
                                </div>
                                <div style="font-size: 14px; line-height: 1.6; color: #111827; white-space: pre-wrap;">{{ $chatMessage->body }}</div>

                                @if($chatMessage->hasAttachment())
                                    <div style="margin-top: 12px; padding: 8px 12px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; display: inline-block;">
                                        📎 <strong>Attached:</strong> {{ $chatMessage->attachment_name }} ({{ $chatMessage->formattedAttachmentSize() }})
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons (Instant Web Reply + Open App) --}}
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 24px 0;">
                                <tr>
                                    @if(!empty($replyUrl))
                                    <td align="center" style="background-color: #F5C518; border-radius: 8px; padding-right: 12px;">
                                        <a href="{{ $replyUrl }}"
                                           target="_blank"
                                           style="display: inline-block; padding: 12px 22px; font-size: 13px; font-weight: 800; color: #0F1A14; text-decoration: none; border-radius: 8px;">
                                            ⚡ Quick Reply via Web &rarr;
                                        </a>
                                    </td>
                                    @endif
                                    <td align="center" style="background-color: #1B6B3A; border-radius: 8px;">
                                        <a href="{{ url('/messages?c=' . $conversation->id) }}"
                                           target="_blank"
                                           style="display: inline-block; padding: 12px 22px; font-size: 13px; font-weight: 700; color: #ffffff; text-decoration: none; border-radius: 8px;">
                                            Open SRE Comms &rarr;
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
                                <p style="font-size: 12px; color: #166534; line-height: 1.5; margin: 0;">
                                    💡 <strong>Email Reply Supported:</strong> You can also reply directly to this email in your email client (Gmail, Outlook, Apple Mail), and your message will automatically be posted to <strong>#{{ $conversation->title ?? 'General Shift' }}</strong>.
                                </p>
                            </div>

                            <p style="font-size: 12px; color: #9ca3af; line-height: 1.5; margin-bottom: 0;">
                                You are receiving this operational receipt because you were mentioned or tagged in an active SRE shift channel.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
                            Support Activity Tracker &bull; Npontu Technologies SRE Operations
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
