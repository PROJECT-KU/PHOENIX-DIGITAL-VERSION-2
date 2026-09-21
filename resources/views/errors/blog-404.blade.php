<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Artikel tidak ditemukan | Blog Phoenix Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: radial-gradient(1100px 520px at 50% -10%, #fff3e6 0%, #fff8f1 45%, #fffdfb 100%);
            color: #23272f;
        }
        .kotak { width: 100%; max-width: 620px; }
        .ubin {
            width: 66px; height: 66px; margin: 0 auto 18px; border-radius: 20px;
            display: flex; align-items: center; justify-content: center; font-size: 1.7rem; color: #fff;
            background: linear-gradient(135deg, #fba919, #f26522);
            box-shadow: 0 14px 30px -14px rgba(242, 101, 34, .7);
        }
        h1 { font-size: clamp(1.35rem, 4vw, 1.75rem); text-align: center; margin-bottom: 8px; }
        .ket { text-align: center; color: #6b7280; font-size: .95rem; line-height: 1.6; margin-bottom: 6px; }
        .alamat {
            display: block; text-align: center; margin: 0 auto 24px; padding: 7px 14px; max-width: 100%;
            border-radius: 999px; background: #fff; border: 1px solid #f1e6d8;
            font-size: .82rem; color: #9ca3af; overflow-wrap: anywhere;
        }
        .judul-kecil {
            font-size: .78rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            color: #9ca3af; margin: 0 0 10px;
        }
        .daftar { list-style: none; display: grid; gap: 8px; margin-bottom: 24px; }
        .daftar a {
            display: flex; align-items: center; gap: 11px; padding: 13px 15px; border-radius: 14px;
            background: #fff; border: 1px solid #f1e6d8; color: #23272f; text-decoration: none;
            font-weight: 600; font-size: .94rem; line-height: 1.45;
            transition: border-color .15s ease, transform .15s ease;
        }
        .daftar a:hover { border-color: #f26522; transform: translateY(-1px); }
        .daftar i { color: #f26522; flex: 0 0 auto; }
        .aksi { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
        .tombol {
            display: inline-flex; align-items: center; gap: 8px; padding: 11px 20px; border-radius: 12px;
            font-size: .9rem; font-weight: 700; text-decoration: none;
        }
        .tombol.is-utama { background: linear-gradient(135deg, #fba919, #f26522); color: #fff; }
        .tombol.is-lembut { background: #fff; border: 1px solid #f1e6d8; color: #6b7280; }
        @media (max-width: 575.98px) { .tombol { flex: 1 1 100%; justify-content: center; } }
    </style>
</head>
<body>
    <main class="kotak">
        <div class="ubin"><i class="bi bi-signpost-split"></i></div>
        <h1>Artikel ini tidak ada</h1>
        <p class="ket">Mungkin alamatnya salah ketik, terpotong saat disalin, atau tulisannya sudah tidak ditayangkan.</p>
        @if ($slug)
            <span class="alamat">/blog/{{ $slug }}</span>
        @endif

        @if ($saran->isNotEmpty())
            <p class="judul-kecil">Mungkin yang Anda cari</p>
            <ul class="daftar">
                @foreach ($saran as $a)
                    <li>
                        <a href="{{ route('blog.show', $a->slug) }}">
                            <i class="bi bi-journal-text"></i>
                            <span>{{ $a->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @elseif ($terbaru->isNotEmpty())
            <p class="judul-kecil">Tulisan terbaru</p>
            <ul class="daftar">
                @foreach ($terbaru as $a)
                    <li>
                        <a href="{{ route('blog.show', $a->slug) }}">
                            <i class="bi bi-journal-text"></i>
                            <span>{{ $a->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="aksi">
            <a class="tombol is-utama" href="{{ route('blog.index') }}"><i class="bi bi-arrow-left"></i> Semua Artikel</a>
            <a class="tombol is-lembut" href="{{ route('homepage') }}"><i class="bi bi-house"></i> Beranda</a>
        </div>
    </main>
</body>
</html>
