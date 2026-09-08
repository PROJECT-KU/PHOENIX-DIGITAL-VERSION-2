<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $judul }}</title></head>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#334155;line-height:1.6;">
    <div style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:14px;padding:30px 28px;border:1px solid {{ $ditutup ? '#f6dcae' : '#cfe9db' }};">

        <div style="width:56px;height:56px;border-radius:16px;margin-bottom:18px;text-align:center;line-height:56px;font-size:24px;color:#ffffff;background:{{ $ditutup ? '#f59e0b' : '#16a34a' }};">
            {{ $ditutup ? '&#9208;' : '&#9654;' }}
        </div>

        <h1 style="margin:0 0 12px;font-size:19px;color:#1e293b;">{{ $judul }}</h1>

        @if ($ditutup)
        <p style="margin:0 0 16px;font-size:14px;">
            Modul <strong>{{ $namaModul }}</strong> untuk sementara tidak bisa dipakai di panel lemon.
            Izin Anda tidak dicabut — begitu dibuka kembali, semuanya kembali seperti semula.
        </p>
        <div style="background:#fffdf7;border-left:3px solid #f59e0b;border-radius:0 8px 8px 0;padding:12px 14px;margin-bottom:18px;">
            <div style="font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#a08048;margin-bottom:4px;">Keterangan</div>
            <div style="font-size:14px;color:#92400e;">{{ $pesan }}</div>
        </div>
        @else
        <p style="margin:0 0 18px;font-size:14px;">
            Modul <strong>{{ $namaModul }}</strong> sudah bisa dipakai lagi seperti biasa.
        </p>
        @endif

        <p style="margin:0;font-size:12px;color:#94a3b8;">
            Diubah oleh {{ $olehSiapa }} &middot; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        </p>
    </div>

    <p style="max-width:520px;margin:14px auto 0;font-size:11px;color:#94a3b8;text-align:center;">
        Pesan otomatis dari lemon by ACM. Tidak perlu dibalas.
    </p>
</body>
</html>
