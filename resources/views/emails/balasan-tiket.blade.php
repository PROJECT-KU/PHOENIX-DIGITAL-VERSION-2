<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Balasan {{ $pesan->ticket }}</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 12px 30px rgba(76,66,20,.12);">

                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe); padding:28px 24px 22px;">
                            <div style="font-size:22px; font-weight:800; color:#4c1d95; line-height:1.2;">Phoenix Digital</div>
                            <div style="font-size:12px; color:#6d28d9; margin-top:6px;">Balasan untuk tiket {{ $pesan->ticket }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:26px 26px 6px;">
                            <p style="margin:0 0 14px; font-size:15px; color:#1f2937;">Halo {{ $pesan->name }},</p>
                            <div style="font-size:15px; line-height:1.65; color:#374151; white-space:pre-wrap;">{{ $isi }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 26px 26px;">
                            <div style="padding:14px 16px; background:#f8fafc; border:1px solid #e9edf3; border-radius:14px;">
                                <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em;">Pesan Anda sebelumnya</div>
                                <div style="margin-top:8px; font-size:13.5px; line-height:1.6; color:#64748b; white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($pesan->message, 600) }}</div>
                                <div style="margin-top:10px; font-size:12px; color:#94a3b8;">Dikirim {{ $pesan->created_at?->locale('id')->translatedFormat('d F Y, H:i') }} WIB</div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 26px 28px;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#64748b;">
                                Balas surel ini kalau masih ada yang ingin ditanyakan — balasannya masuk ke tiket yang sama.
                                Jam operasional kami setiap hari 08.00–21.00 WIB.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background:#faf5ff; padding:16px 24px; font-size:11.5px; color:#7c3aed;">
                            Phoenix Digital · phoenixdigitalwarehouse.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
