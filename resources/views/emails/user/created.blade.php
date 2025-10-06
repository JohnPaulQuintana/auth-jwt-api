<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }} - Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f6f6; margin:0; padding:0;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff; margin:20px 0; border-radius:6px; overflow:hidden;">
          {{-- Banner --}}
          @if(!empty($bannerUrl))
            <tr>
              <td style="padding:0; text-align:center;">
                <img src="{{ $bannerUrl }}" alt="Banner" style="width:100%; max-height:180px; object-fit:cover; display:block;">
              </td>
            </tr>
          @endif

          <tr>
            <td style="padding:24px;">
              <h2 style="margin:0 0 10px 0;">Welcome, {{ $user->name }} 👋</h2>
              <p style="color:#555; margin:0 0 16px 0;">
                You have been added to <strong>{{ config('app.name') }}</strong> as a <strong>{{ ucfirst($user->role) }}</strong>.
              </p>

              @if(!empty($profilePictureUrl))
                <p style="margin:0 0 16px;">
                  <img src="{{ $profilePictureUrl }}" alt="Profile" style="width:120px; height:120px; object-fit:cover; border-radius:60px; display:block;">
                </p>
              @endif

              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
                <tr>
                  <td align="center">
                    <a href="{{ $resetUrl }}" target="_blank" style="background:#1f6feb; color:#fff; padding:12px 20px; text-decoration:none; border-radius:6px; display:inline-block;">
                      Set your password &amp; login
                    </a>
                  </td>
                </tr>
              </table>

              <p style="color:#777; font-size:13px;">
                If that button doesn't work, copy and paste this link into your browser:
                <br/>
                <a href="{{ $resetUrl }}" target="_blank" style="color:#1f6feb; word-break:break-all;">{{ $resetUrl }}</a>
              </p>

              <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">

              <p style="color:#555; font-size:13px; margin:0;">
                If you didn't expect this email, please ignore it.
                Thanks,<br>{{ config('app.name') }} Team
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
