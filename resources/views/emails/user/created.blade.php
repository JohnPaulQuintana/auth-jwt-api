<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }} - Account Registered</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f6f7; margin:0; padding:0;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center" style="padding: 30px 0;">
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

          @if(!empty($bannerUrl))
          <tr>
            <td style="padding:0;">
              <img src="{{ $bannerUrl }}" alt="Banner" style="width:100%; max-height:180px; object-fit:cover; display:block;">
            </td>
          </tr>
          @endif

          <tr>
            <td style="padding: 36px 28px;">
              <h2 style="margin:0 0 12px 0; color:#111; font-size:22px;">
                Hello {{ $user->name }},
              </h2>

              <p style="color:#444; margin:0 0 20px 0; line-height:1.6; font-size:15px;">
                We’re pleased to inform you that your account has been successfully registered in
                <strong>{{ config('app.name') }}</strong>.
              </p>

              <p style="color:#444; margin:0 0 20px 0; line-height:1.6; font-size:15px;">
                Your role in the system is <strong>{{ ucfirst($user->role) }}</strong>. You’ll be able to access the platform once your login credentials are provided by your administrator.
              </p>

              <p style="color:#444; margin:0 0 20px 0; line-height:1.6; font-size:15px;">
                Please keep an eye on your inbox for further instructions or system updates.
              </p>

              <hr style="border:none; border-top:1px solid #e5e5e5; margin:28px 0;">

              <p style="color:#888; font-size:13px; line-height:1.6; margin:0;">
                This is an automated message from <strong>{{ config('app.name') }}</strong>.
                No action is required on your part.
              </p>

              <p style="color:#888; font-size:13px; margin-top:8px;">
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
