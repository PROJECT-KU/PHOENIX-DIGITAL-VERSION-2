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
        /* ===== Adegan animasi =====
           Digambar sebagai SVG + CSS, bukan berkas GIF: tak ada aset tambahan
           yang harus ikut terdeploy, tajam di layar rapat, dan bisa dihentikan
           untuk yang menyetel prefers-reduced-motion. */
        .adegan{width:172px;height:172px;margin:0 auto 6px}
        .adegan svg{width:100%;height:100%;overflow:visible}

        /* Gerigi: lingkaran bergaris putus-putus, berputar pelan. */
        .gerigi{
            fill:none;stroke:#f3d9a4;stroke-width:13;stroke-dasharray:9 15;stroke-linecap:round;
            transform-origin:80px 80px;animation:putar 9s linear infinite;
        }
        .gerigi-dalam{fill:none;stroke:#f8e7c4;stroke-width:3}

        /* Halo berdenyut, menandai ada yang sedang berjalan. */
        .halo{
            fill:none;stroke:#f59e0b;stroke-width:2;opacity:.5;
            transform-origin:80px 80px;animation:denyut 2.8s ease-in-out infinite;
        }

        /* Buah lemon yang mengambang naik-turun perlahan. */
        .lemon{transform-origin:80px 80px;animation:apung 3.4s ease-in-out infinite}
        .lemon-badan{fill:url(#kulit)}
        .lemon-kilau{fill:#fff;opacity:.42}
        .daun{fill:#7fae4b}

        /* Tiga titik: penanda "sedang dikerjakan". */
        .titik{display:flex;gap:7px;justify-content:center;margin-bottom:20px}
        .titik span{
            width:7px;height:7px;border-radius:50%;background:#e0b567;
            animation:lompat 1.3s ease-in-out infinite;
        }
        .titik span:nth-child(2){animation-delay:.16s}
        .titik span:nth-child(3){animation-delay:.32s}

        @keyframes putar{to{transform:rotate(360deg)}}
        @keyframes denyut{
            0%,100%{transform:scale(1);opacity:.5}
            50%{transform:scale(1.09);opacity:.12}
        }
        @keyframes apung{
            0%,100%{transform:translateY(0) rotate(-3deg)}
            50%{transform:translateY(-7px) rotate(3deg)}
        }
        @keyframes lompat{
            0%,100%{transform:translateY(0);opacity:.45}
            40%{transform:translateY(-6px);opacity:1}
        }

        /* Yang menyetel kurangi-gerak tetap melihat gambarnya, tanpa geraknya. */
        @media (prefers-reduced-motion:reduce){
            .gerigi,.halo,.lemon,.titik span{animation:none}
            .titik span{opacity:.75}
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
        <div class="adegan" aria-hidden="true">
            <svg viewBox="0 0 160 160" role="img">
                <defs>
                    <linearGradient id="kulit" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#fde047"/>
                        <stop offset="1" stop-color="#f0a91b"/>
                    </linearGradient>
                </defs>

                <circle class="halo" cx="80" cy="80" r="66"/>
                <circle class="gerigi" cx="80" cy="80" r="58"/>
                <circle class="gerigi-dalam" cx="80" cy="80" r="47"/>

                <g class="lemon">
                    <ellipse class="lemon-badan" cx="80" cy="82" rx="27" ry="34"
                             transform="rotate(-24 80 82)"/>
                    <ellipse class="lemon-kilau" cx="70" cy="70" rx="7" ry="12"
                             transform="rotate(-24 70 70)"/>
                    <path class="daun" d="M92 50c9-7 19-6 19-6s-2 10-10 14c-6 3-11 1-11 1s-2-6 2-9z"/>
                </g>
            </svg>
        </div>

        <h1>{{ $judul }} sedang kami perbaiki</h1>

        <div class="titik" aria-hidden="true"><span></span><span></span><span></span></div>

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
