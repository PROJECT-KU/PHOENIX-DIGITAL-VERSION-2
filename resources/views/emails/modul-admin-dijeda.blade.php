<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $judul }}</title></head>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#334155;line-height:1.6;">
    <div style="max-width:540px;margin:0 auto;background:#ffffff;border-radius:14px;padding:30px 28px;border:1px solid {{ $ditutup ? '#f6dcae' : '#cfe9db' }};">

        {{-- Ikon digambar dengan kotak berwarna, bukan entitas HTML atau emoji.
             Entitas di dalam {{ }} ikut di-escape dan tampil mentah sebagai
             "&#92..." di Gmail, sedangkan emoji tidak bisa diandalkan lintas
             klien surel. Kotak dan segitiga CSS selalu tampil. --}}
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:18px;">
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

        <h1 style="margin:0 0 12px;font-size:19px;color:#1e293b;">{{ $judul }}</h1>

        @if ($ditutup)
        <p style="margin:0 0 16px;font-size:14px;">
            Modul <strong>{{ $namaModul }}</strong> untuk sementara tidak bisa dipakai di panel lemon.
            Izin Anda tidak dicabut — begitu dibuka kembali, semuanya kembali seperti semula.
        </p>

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:16px;border-collapse:collapse;">
            <tr>
                <td style="padding:10px 14px;background:#fffdf7;border:1px solid #f6dcae;border-radius:8px 8px 0 0;font-size:13px;">
                    <span style="color:#a08048;">Ditutup sejak</span><br>
                    <strong style="color:#92400e;font-size:14px;">
                        {{ $mulai ? $mulai->translatedFormat('l, d F Y · H:i').' WIB' : 'baru saja' }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td style="padding:10px 14px;background:#fffdf7;border:1px solid #f6dcae;border-top:0;border-radius:0 0 8px 8px;font-size:13px;">
                    <span style="color:#a08048;">Perkiraan dibuka kembali</span><br>
                    <strong style="color:#92400e;font-size:14px;">
                        @if ($sampai)
                            {{ $sampai->translatedFormat('l, d F Y · H:i') }} WIB
                        @else
                            Belum ditentukan — kami kabari lagi begitu dibuka
                        @endif
                    </strong>
                </td>
            </tr>
        </table>

        <div style="background:#f8fafc;border-left:3px solid #cbd5e1;border-radius:0 8px 8px 0;padding:12px 14px;margin-bottom:18px;">
            <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#94a3b8;margin-bottom:4px;">Keterangan</div>
            <div style="font-size:14px;color:#475569;">{{ $pesan }}</div>
        </div>
        @else
        <p style="margin:0 0 18px;font-size:14px;">
            Modul <strong>{{ $namaModul }}</strong> sudah bisa dipakai lagi seperti biasa.
            Terima kasih sudah menunggu.
        </p>
        @endif

        <p style="margin:0;font-size:12px;color:#94a3b8;">
            Diubah oleh {{ $olehSiapa }} &middot; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </p>
    </div>

    <p style="max-width:540px;margin:14px auto 0;font-size:11px;color:#94a3b8;text-align:center;">
        Pesan otomatis dari lemon by ACM. Tidak perlu dibalas.
    </p>
</body>
</html>
