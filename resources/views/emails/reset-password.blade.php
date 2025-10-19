<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} - Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f6f7; margin:0; padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border-radius:8px; padding:32px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <tr>
                        <td style="text-align:center;">
                            <h2 style="margin:0 0 16px 0; color:#111; font-size:22px;">Reset Your Password</h2>
                            <p style="color:#444; font-size:15px; line-height:1.6; margin:0 0 24px 0;">
                                You requested a password reset for your <strong>{{ config('app.name') }}</strong> account.
                            </p>

                            <a href="{{ $url }}"
                               style="display:inline-block; padding:14px 28px; background-color:#34C759; color:#ffffff; border-radius:6px; text-decoration:none; font-weight:bold; font-size:15px; margin-bottom:24px;">
                               Reset Password
                            </a>

                            <p style="color:#444; font-size:14px; line-height:1.6; margin:0 0 16px 0;">
                                If the button above does not work, copy and paste the following link into your web browser:
                            </p>

                            <p style="word-break:break-all; color:#1B5E20; font-size:14px; margin:0 0 24px 0;">
                                {{ $url }}
                            </p>

                            <hr style="border:none; border-top:1px solid #e5e5e5; margin:24px 0;">

                            <p style="color:#888; font-size:13px; line-height:1.6; margin:0; text-align:center;">
                                If you did not request this password reset, please ignore this email.
                                This is an automated message from <strong>{{ config('app.name') }}</strong>.
                            </p>

                            <p style="color:#888; font-size:13px; margin-top:8px; text-align:center;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
