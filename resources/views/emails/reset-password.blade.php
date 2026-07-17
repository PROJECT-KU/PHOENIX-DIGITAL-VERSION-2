<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Kata Sandi</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:520px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 12px 30px rgba(76,66,20,.12);">

                    {{-- Header lemon --}}
                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#fef9c3,#fde68a); padding:32px 24px 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center"><tr>
                                {{-- Logo lemon (PNG di-embed via CID: tampil di Gmail/klien email) --}}
                                <td valign="middle" style="padding-right:12px;">
                                    <img src="@isset($message){{ $message->embed(public_path('assets/img/lemon-logo.png')) }}@else{{ asset('assets/img/lemon-logo.png') }}@endisset"
                                        width="58" height="58" alt="lemon" style="display:block; border:0;">
                                </td>
                                <td valign="middle" align="left">
                                    <div style="font-size:30px; font-weight:800; letter-spacing:-.5px; color:#854d0e; line-height:1;">lemon</div>
                                    <div style="font-size:11px; letter-spacing:3px; text-transform:uppercase; font-weight:700; color:#a16207; margin-top:4px;">by acm</div>
                                </td>
                            </tr></table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px 36px 8px;">
                            <h1 style="margin:0 0 12px; font-size:20px; color:#1f2937;">Atur Ulang Kata Sandi</h1>
                            <p style="margin:0 0 16px; font-size:14.5px; line-height:1.6; color:#4b5563;">
                                Halo{{ isset($user->name) ? ' '.$user->name : '' }}, kami menerima permintaan untuk mereset kata sandi akun Anda.
                                Klik tombol di bawah untuk membuat kata sandi baru.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:22px 0;"><tr><td align="center">
                                <a href="{{ $url }}"
                                    style="display:inline-block; background:linear-gradient(135deg,#facc15,#eab308); color:#3f2d00; text-decoration:none; font-weight:800; font-size:15px; padding:14px 30px; border-radius:12px; box-shadow:0 8px 18px rgba(202,138,4,.35);">
                                    Buat Kata Sandi Baru
                                </a>
                            </td></tr></table>

                            <p style="margin:0 0 6px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Tautan ini berlaku selama <strong>{{ $expire }} menit</strong>. Jika Anda tidak meminta reset, abaikan email ini — kata sandi Anda tidak berubah.
                            </p>
                        </td>
                    </tr>

                    {{-- Fallback link --}}
                    <tr>
                        <td style="padding:12px 36px 28px;">
                            <p style="margin:0 0 6px; font-size:12px; color:#9ca3af;">Jika tombol tidak berfungsi, salin & tempel tautan berikut di browser:</p>
                            <p style="margin:0; font-size:12px; word-break:break-all;">
                                <a href="{{ $url }}" style="color:#65a30d;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="background:#fafaf9; padding:18px; border-top:1px solid #f0efe9;">
                            <p style="margin:0; font-size:11.5px; color:#9ca3af;">© {{ date('Y') }} lemon by ACM · PT. Asthana Cipta Mandiri</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
