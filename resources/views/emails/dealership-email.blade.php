{{-- Default template for emails reps send from a dealership. Styled like a personal email, not a newsletter: no background, card or fixed width. Inline styles for email clients. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subject ?? '' }}</title>
</head>
<body style="margin:0;padding:0;-webkit-text-size-adjust:100%;">
<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.5;color:#222222;">
    @if ($note)
        <p style="margin:0 0 16px 0;color:#666666;font-size:13px;">
            {{ $note['text'] }}
            @if (! empty($note['url']))
                <a href="{{ $note['url'] }}" style="color:#1a73e8;">{{ $note['link'] }}</a>
            @endif
        </p>
    @endif

    @if ($bodyHtml)
        {{-- Sanitized by SanitizeEmailHtml when the email was saved. --}}
        {{ $bodyHtml }}
    @else
        @foreach ($paragraphs as $paragraph)
            <p style="margin:0 0 14px 0;">{{ $paragraph }}</p>
        @endforeach
    @endif

    @if ($signature)
        @php($logoPath = resource_path('images/email-logo.png'))
        <div style="margin-top:24px;">
            {{-- Embedded rather than linked, so it shows without loading remote images. $message only exists while sending. --}}
            <img
                src="{{ isset($message) ? $message->embed($logoPath) : 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath)) }}"
                width="150"
                height="66"
                alt="ARMP - Automotive Risk Management Partners"
                style="display:block;width:150px;height:66px;margin:0 0 10px 0;border:0;outline:none;text-decoration:none;"
            >
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="border-left:3px solid #f97316;padding:0 0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#555555;">
                        <div style="font-size:14px;font-weight:bold;color:#222222;">{{ $signature['name'] }}</div>
                        @if (! empty($signature['title']))
                            <div>{{ $signature['title'] }}</div>
                        @endif
                        <div>{{ $signature['company'] }}</div>
                        @if ($signature['phone'])
                            <div>{{ $signature['phone'] }}</div>
                        @endif
                        <div><a href="mailto:{{ $signature['email'] }}" style="color:#555555;text-decoration:none;">{{ $signature['email'] }}</a></div>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</div>
</body>
</html>
