<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ebook tidak tersedia · Phoenix Digital</title>
    <link rel="icon" href="{{ asset('icons/phoenix-192.png') }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; background: #eef0f6; color: #1c1f26; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
        .kotak { width: 100%; max-width: 420px; padding: 30px 24px; text-align: center; background: #fff; border-radius: 22px; border: 1px solid #e5e7f0; box-shadow: 0 18px 40px -24px rgba(15, 23, 42, .35); }
        .ikon { width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; background: #fff7ed; border: 1px solid #fed7aa; }
        h1 { margin: 0 0 8px; font-size: 1.15rem; }
        p { margin: 0; font-size: .9rem; color: #64748b; line-height: 1.6; }
        .deret { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 18px; }
        a { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border-radius: 12px; font-size: .88rem; font-weight: 700; text-decoration: none; }
        .wa { background: #25d366; color: #fff; }
        .toko { background: #f1f5f9; color: #334155; }
    </style>
</head>

<body>
    <div class="kotak">
        <div class="ikon"><img src="{{ asset('icons/phoenix-192.png') }}" alt="Phoenix Digital" width="36" height="36"></div>
        <h1>Ebook ini sudah tidak tersedia</h1>
        <p>Tautan ebook ini sudah dinonaktifkan atau diganti. Bila Anda pelanggan kami dan masih membutuhkannya, hubungi kami — kami kirimkan tautan terbaru.</p>
        <div class="deret">
            <a class="wa" target="_blank" rel="noopener" href="https://wa.me/6289505967995?text={{ rawurlencode('Halo Phoenix Digital, tautan ebook bonus saya tidak bisa dibuka.') }}">Hubungi kami</a>
            <a class="toko" href="{{ url('/') }}">Ke toko</a>
        </div>
    </div>
</body>

</html>
