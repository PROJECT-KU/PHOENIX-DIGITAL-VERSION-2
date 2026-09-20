<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bagaimana penanganan kami?</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:560px; background:#ffffff; border-radius:20px; overflow:hidden; box-shadow:0 12px 30px rgba(76,66,20,.12);">

                    <tr>
                        <td align="center" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe); padding:28px 24px 22px;">
                            <div style="font-size:22px; font-weight:800; color:#4c1d95; line-height:1.2;">Bagaimana penanganan kami?</div>
                            <div style="font-size:12px; color:#6d28d9; margin-top:6px;">Tiket {{ $pesan->ticket }} sudah kami tandai selesai</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:26px 26px 10px;">
                            <p style="margin:0 0 14px; font-size:15px; color:#1f2937;">Halo {{ $pesan->name }},</p>
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.65; color:#374151;">
                                Terima kasih sudah menghubungi Phoenix Digital. Kalau berkenan, beri tahu kami bagaimana
                                penanganannya — cukup satu klik, dan boleh ditambahi catatan kalau ada yang perlu kami perbaiki.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:0 26px 24px;">
                            <a href="{{ $tautan }}"
                                style="display:inline-block; padding:13px 26px; border-radius:12px; background:#7c3aed; color:#ffffff; font-size:14.5px; font-weight:700; text-decoration:none;">
                                Beri penilaian
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 26px 26px;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#64748b;">
                                Masih ada yang mengganjal? Di halaman yang sama Anda bisa menambahkan keterangan, dan
                                tiket ini akan kami buka lagi.
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
