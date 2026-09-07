<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $judul }} sedang diperbaiki — {{ config('app.name') }}</title>
    <style>
        /* Ditulis inline: halaman ini harus tetap tampil benar meski aset
           build tidak ikut terdeploy, dan justru dipakai saat ada perbaikan. */
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            padding:28px;background:#fdfaf4;color:#3a2f1c;
            font-family:system-ui,-apple-system,'Segoe UI',sans-serif;line-height:1.6;
        }
        .kotak{max-width:520px;width:100%;text-align:center}
        .lambang{
            width:76px;height:76px;margin:0 auto 22px;border-radius:24px;
            display:flex;align-items:center;justify-content:center;font-size:34px;
            background:linear-gradient(135deg,#fbbf24,#f59e0b);
            box-shadow:0 10px 24px rgba(217,119,6,.26),0 0 0 6px rgba(245,158,11,.12);
        }
        h1{font-size:clamp(20px,4vw,27px);line-height:1.25;margin-bottom:12px;color:#2a2113}
        p{font-size:clamp(14px,2.4vw,16px);color:#6b5c40;margin-bottom:26px}
        .aksi{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
        a{
            display:inline-flex;align-items:center;gap:8px;text-decoration:none;
            padding:11px 20px;border-radius:12px;font-weight:600;font-size:.92rem;
        }
        .utama{background:linear-gradient(135deg,#f59e0b,#ea8104);color:#fff}
        .biasa{background:#fff;color:#6b5c40;border:1px solid #ecdcc0}
    </style>
</head>
<body>
    <div class="kotak">
        <div class="lambang">🍋</div>
        <h1>{{ $judul }} sedang kami perbaiki</h1>
        <p>{{ $pesan }}</p>
        <div class="aksi">
            <a class="utama" href="{{ route('homepage') }}">Kembali ke Beranda</a>
            {{-- Nomor yang sama dengan tombol WhatsApp di layout publik. --}}
            <a class="biasa" target="_blank" rel="noopener"
               href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya.">
                Hubungi via WhatsApp
            </a>
        </div>
    </div>
</body>
</html>
