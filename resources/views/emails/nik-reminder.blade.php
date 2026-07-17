<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nomor Induk Karyawan</title>
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
                            <h1 style="margin:0 0 12px; font-size:20px; color:#1f2937;">Nomor Induk Karyawan Anda</h1>
                            <p style="margin:0 0 16px; font-size:14.5px; line-height:1.6; color:#4b5563;">
                                Halo{{ isset($user->name) ? ' '.$user->name : '' }}, kami menerima permintaan untuk mengirim ulang
                                Nomor Induk Karyawan (NIK) akun Anda. Berikut NIK Anda untuk masuk ke sistem:
                            </p>

                            {{-- Kotak NIK --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:22px 0;"><tr><td align="center">
                                <div style="display:inline-block; background:linear-gradient(135deg,#fffbeb,#fef3c7); border:1px solid #fde68a; border-radius:14px; padding:18px 34px;">
                                    <div style="font-size:11px; letter-spacing:2px; text-transform:uppercase; font-weight:700; color:#a16207; margin-bottom:6px;">Nomor Induk Karyawan</div>
                                    <div style="font-family:'Courier New',monospace; font-size:26px; font-weight:800; letter-spacing:3px; color:#854d0e;">{{ $nik }}</div>
                                </div>
                            </td></tr></table>

                            <p style="margin:0 0 6px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Gunakan NIK ini beserta kata sandi Anda untuk masuk. Jika Anda tidak meminta pengiriman ini,
                                abaikan email ini — akun Anda tetap aman.
                            </p>
                            <p style="margin:8px 0 6px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Lupa kata sandi? Gunakan menu <strong>Lupa sandi?</strong> di halaman login.
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
