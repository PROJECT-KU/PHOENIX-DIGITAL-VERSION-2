<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanda terima — PT. Asthana Cipta Mandiri</title>
</head>
<body style="margin:0; padding:0; background:#f7f4ee; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f4ee; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; border:1px solid #e5e0d6;">
                    <tr>
                        <td align="center" style="background:#17324f; padding:28px 24px 24px; border-bottom:5px solid #2b679e;">
                            <div style="font-size:12px; font-weight:700; letter-spacing:2px; color:#8fc0dc;">PT.</div>
                            <div style="font-family:Georgia, 'Times New Roman', serif; font-size:23px; font-weight:600; color:#ffffff; line-height:1.2;">Asthana Cipta Mandiri</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 28px 8px;">
                            <p style="margin:0 0 14px; font-size:16px; color:#17324f;">Halo {{ $nama }},</p>
                            @if ($jenis === 'lamaran')
                                <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#4a5160;">
                                    Terima kasih telah melamar posisi <strong style="color:#17324f;">{{ $posisi }}</strong>.
                                    Lamaran Anda sudah kami terima dan akan ditinjau oleh tim kami.
                                </p>
                                @if ($nomor)
                                    <p style="margin:0 0 14px; font-size:15px; color:#4a5160;">
                                        Nomor lamaran: <strong style="color:#2b679e;">{{ $nomor }}</strong>
                                    </p>
                                @endif
                                <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#4a5160;">
                                    Bila profil Anda sesuai, kami akan menghubungi Anda melalui email atau telepon
                                    untuk tahap berikutnya.
                                </p>
                            @else
                                <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#4a5160;">
                                    Terima kasih telah menghubungi kami. Pesan Anda sudah kami terima, dan tim kami
                                    akan segera membalasnya.
                                </p>
                            @endif
                            <p style="margin:0 0 14px; font-size:13px; line-height:1.6; color:#7c86a3;">
                                Surel ini dikirim otomatis. Anda tidak perlu membalasnya.
                            </p>
                        </td>
                    </tr>
                    @if ($situs)
                        <tr>
                            <td align="center" style="padding:6px 28px 30px;">
                                <a href="{{ $situs }}" style="display:inline-block; padding:12px 26px; border-radius:10px; background:#2b679e; color:#ffffff; font-weight:700; font-size:14px; text-decoration:none;">
                                    Kunjungi situs kami
                                </a>
                            </td>
                        </tr>
                    @endif
                </table>
                <p style="font-size:12px; color:#9aa3bd; margin:18px 0 0;">&copy; {{ date('Y') }} PT. Asthana Cipta Mandiri</p>
            </td>
        </tr>
    </table>
</body>
</html>
