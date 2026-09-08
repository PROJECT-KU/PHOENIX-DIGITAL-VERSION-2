<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $judul }}</title></head>
<body style="margin:0;padding:26px 16px;background:#faf7f2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#3a2f1c;line-height:1.6;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td align="center">
<table role="presentation" width="540" cellpadding="0" cellspacing="0" border="0" style="max-width:540px;background:#ffffff;border-radius:16px;border:1px solid {{ $ditutup ? '#f6dcae' : '#cfe9db' }};overflow:hidden;">

    {{-- Kepala: DI TENGAH. Lambang dan judul adalah kabarnya sendiri, dan
         kabar yang dipusatkan terbaca lebih dulu daripada paragraf. --}}
    <tr>
        <td align="center" style="padding:34px 30px 22px;background:{{ $ditutup ? 'linear-gradient(180deg,#fffdf7,#ffffff)' : 'linear-gradient(180deg,#f6fbf8,#ffffff)' }};">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 16px;">
                <tr><td style="width:58px;height:58px;border-radius:17px;text-align:center;vertical-align:middle;background:{{ $ditutup ? '#f59e0b' : '#16a34a' }};">
                    @if ($ditutup)
                        <span style="display:inline-block;width:6px;height:22px;background:#ffffff;border-radius:2px;margin:0 3px;"></span><span style="display:inline-block;width:6px;height:22px;background:#ffffff;border-radius:2px;margin:0 3px;"></span>
                    @else
                        <span style="display:inline-block;width:0;height:0;border-top:11px solid transparent;border-bottom:11px solid transparent;border-left:17px solid #ffffff;"></span>
                    @endif
                </td></tr>
            </table>
            <h1 style="margin:0;font-size:21px;line-height:1.3;color:#2a2113;">{{ $judul }}</h1>
        </td>
    </tr>

    {{-- Isi: rata kiri, karena kalimat panjang yang dipusatkan justru sulit dibaca. --}}
    <tr>
        <td style="padding:4px 30px 0;">
            @if ($ditutup)
            <p style="margin:0 0 20px;font-size:14.5px;color:#6b5c40;">
                Halaman <strong>{{ $namaHalaman }}</strong> sedang kami perbaiki, jadi untuk sementara
                belum bisa diakses. Bagian lain di Phoenix Digital tetap berjalan seperti biasa.
            </p>

            {{-- Label kiri, nilai KANAN: mata menyusuri satu kolom angka,
                 bukan mencari-cari di tengah kalimat. --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #f2e3c4;border-radius:12px;background:#fffdf7;margin-bottom:20px;">
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#a08048;border-bottom:1px solid #f6ecd8;">Sejak</td>
                    <td align="right" style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#92400e;border-bottom:1px solid #f6ecd8;">
                        {{ $mulai ? $mulai->translatedFormat('d M Y · H:i').' WIB' : 'Baru saja' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#a08048;">Perkiraan selesai</td>
                    <td align="right" style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#92400e;">
                        {{ $sampai ? $sampai->translatedFormat('d M Y · H:i').' WIB' : 'Belum bisa kami pastikan' }}
                    </td>
                </tr>
            </table>

            <p style="margin:0 0 24px;font-size:14px;color:#475569;background:#f8fafc;border-left:3px solid #cbd5e1;border-radius:0 8px 8px 0;padding:13px 15px;">
                {{ $pesan }}
            </p>
            @else
            <p style="margin:0 0 24px;font-size:14.5px;color:#6b5c40;">
                Kabar baik — halaman <strong>{{ $namaHalaman }}</strong> sudah bisa diakses lagi
                seperti biasa. Terima kasih sudah menunggu.
            </p>
            @endif
        </td>
    </tr>

    {{-- Ajakan: DI TENGAH, karena satu tindakan tunggal. --}}
    <tr>
        <td align="center" style="padding:0 30px 32px;">
            <a href="https://wa.me/6289505967995" style="display:inline-block;padding:13px 26px;border-radius:12px;background:#f59e0b;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;">
                Hubungi kami lewat WhatsApp
            </a>
        </td>
    </tr>
</table>

<p style="max-width:540px;margin:16px auto 0;font-size:11.5px;color:#a89b83;text-align:center;line-height:1.6;">
    Anda menerima kabar ini karena pernah berbelanja di Phoenix Digital.
</p>
</td></tr>
</table>
</body>
</html>
