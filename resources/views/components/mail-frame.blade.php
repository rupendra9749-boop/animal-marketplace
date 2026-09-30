{{-- Shared look of every email: a plain, phone-friendly card. $body is the slot. --}}
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#f5f5f4;font-family:Arial,Helvetica,sans-serif;color:#292524;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f4;padding:24px 12px;">
    <tr><td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;border:1px solid #e7e5e4;">
            <tr><td style="padding:22px 28px;border-bottom:1px solid #f5f5f4;">
                <span style="font-size:20px;font-weight:bold;color:#d97706;">&#128062; {{ config('app.name') }}</span>
            </td></tr>
            <tr><td style="padding:24px 28px;font-size:15px;line-height:1.55;">
                {{ $slot }}
            </td></tr>
            <tr><td style="padding:16px 28px;background:#fafaf9;border-radius:0 0 16px 16px;font-size:12px;color:#78716c;">
                {{ __('You are getting this email because of an order on :app.', ['app' => config('app.name')]) }}
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
