<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $judul }}</title></head>
<body style="margin:0;padding:24px;background:#faf7f2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#3a2f1c;line-height:1.6;">
    <div style="max-width:540px;margin:0 auto;background:#ffffff;border-radius:16px;padding:32px 28px;border:1px solid {{ $ditutup ? '#f6dcae' : '#cfe9db' }};">

        {{-- Bentuk digambar dengan kotak & segitiga CSS, bukan entitas HTML
             (ikut di-escape di dalam {{ }}) atau emoji (tidak bisa diandalkan
             lintas klien surel). --}}
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
            <tr>
                <td style="width:56px;height:56px;border-radius:16px;text-align:center;vertical-align:middle;background:{{ $ditutup ? '#f59e0b' : '#16a34a' }};">
                    @if ($ditutup)
                        <span style="display:inline-block;width:6px;height:22px;background:#ffffff;border-radius:2px;margin:0 3px;"></span><span style="display:inline-block;width:6px;height:22px;background:#ffffff;border-radius:2px;margin:0 3px;"></span>
                    @else
                        <span style="display:inline-block;width:0;height:0;border-top:11px solid transparent;border-bottom:11px solid transparent;border-left:17px solid #ffffff;"></span>
                    @endif
                </td>
            </tr>
        </table>

        <h1 style="margin:0 0 12px;font-size:20px;color:#2a2113;">{{ $judul }}</h1>

        @if ($ditutup)
        <p style="margin:0 0 18px;font-size:14px;color:#6b5c40;">
            Halaman <strong>{{ $namaHalaman }}</strong> sedang kami perbaiki, jadi untuk sementara
            belum bisa diakses. Bagian lain di Phoenix Digital tetap berjalan seperti biasa.
        </p>

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:18px;border-collapse:separate;border-spacing:0 6px;">
            <tr>
                <td style="padding:11px 14px;background:#fffdf7;border:1px solid #f6dcae;border-radius:10px;font-size:13px;">
                    <span style="color:#a08048;">Sejak</span><br>
                    <strong style="color:#92400e;font-size:14px;">
                        {{ $mulai ? $mulai->translatedFormat('l, d F Y · H:i').' WIB' : 'baru saja' }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td style="padding:11px 14px;background:#fffdf7;border:1px solid #f6dcae;border-radius:10px;font-size:13px;">
                    <span style="color:#a08048;">Perkiraan selesai</span><br>
                    <strong style="color:#92400e;font-size:14px;">
                        {{ $sampai ? $sampai->translatedFormat('l, d F Y · H:i').' WIB' : 'Belum bisa kami pastikan' }}
                    </strong>
                </td>
            </tr>
        </table>

        <div style="background:#f8fafc;border-left:3px solid #cbd5e1;border-radius:0 8px 8px 0;padding:12px 14px;margin-bottom:22px;">
            <div style="font-size:14px;color:#475569;">{{ $pesan }}</div>
        </div>
        @else
        <p style="margin:0 0 22px;font-size:14px;color:#6b5c40;">
            Kabar baik — halaman <strong>{{ $namaHalaman }}</strong> sudah bisa diakses lagi
            seperti biasa. Terima kasih sudah menunggu.
        </p>
        @endif

        <a href="https://wa.me/6289505967995" style="display:inline-block;padding:11px 22px;border-radius:12px;background:#f59e0b;color:#ffffff;text-decoration:none;font-weight:600;font-size:14px;">
            Hubungi kami lewat WhatsApp
        </a>
    </div>

    <p style="max-width:540px;margin:14px auto 0;font-size:11px;color:#a89b83;text-align:center;">
        Anda menerima kabar ini karena pernah berbelanja di Phoenix Digital.
    </p>
</body>
</html>
