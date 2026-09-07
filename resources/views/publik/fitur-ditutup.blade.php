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
           Dua roda gigi bertaut — lambang perbaikan yang langsung terbaca, dan
           sengaja BUKAN buah lemon: lemon itu identitas merek, bukan tanda
           sedang ada gangguan.

           Digambar sebagai SVG + CSS, bukan berkas GIF: tak ada aset tambahan
           yang harus ikut terdeploy (public/build memang tidak ikut), tajam di
           layar rapat, dan geraknya bisa dihentikan. */
        .adegan{width:176px;height:176px;margin:0 auto 4px}
        .adegan svg{width:100%;height:100%;overflow:visible}

        .gigi-besar,.gigi-kecil{fill:url(#kulit)}
        .poros{fill:#fdfaf4}
        .bayang{fill:#f3e3c6;opacity:.55}

        /* Arah putaran berlawanan, dan yang kecil lebih cepat sesuai
           perbandingan jumlah giginya (12 : 8) — kalau sama cepat, mata
           langsung merasa giginya saling menembus. */
        .gigi-besar,.poros-besar{transform-origin:62px 66px;animation:putar-kanan 8s linear infinite}
        .gigi-kecil,.poros-kecil{transform-origin:113px 105px;animation:putar-kiri 5.33s linear infinite}

        .halo{
            fill:none;stroke:#f59e0b;stroke-width:2;opacity:.35;
            transform-origin:88px 86px;animation:denyut 2.8s ease-in-out infinite;
        }

        .titik{display:flex;gap:7px;justify-content:center;margin-bottom:20px}
        .titik span{
            width:7px;height:7px;border-radius:50%;background:#e0b567;
            animation:lompat 1.3s ease-in-out infinite;
        }
        .titik span:nth-child(2){animation-delay:.16s}
        .titik span:nth-child(3){animation-delay:.32s}

        @keyframes putar-kanan{to{transform:rotate(360deg)}}
        @keyframes putar-kiri{to{transform:rotate(-360deg)}}
        @keyframes denyut{
            0%,100%{transform:scale(1);opacity:.35}
            50%{transform:scale(1.08);opacity:.08}
        }
        @keyframes lompat{
            0%,100%{transform:translateY(0);opacity:.45}
            40%{transform:translateY(-6px);opacity:1}
        }

        /* Yang menyetel kurangi-gerak tetap melihat gambarnya, tanpa geraknya. */
        @media (prefers-reduced-motion:reduce){
            .gigi-besar,.gigi-kecil,.poros-besar,.poros-kecil,.halo,.titik span{animation:none}
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
            <svg viewBox="0 0 176 176" role="img">
                <defs>
                    <linearGradient id="kulit" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#fcd34d"/>
                        <stop offset="1" stop-color="#e78c0a"/>
                    </linearGradient>
                </defs>

                <circle class="halo" cx="88" cy="86" r="80"/>

                <g class="bayang"><path d="M93.0 66.0 L101.5 72.1 L99.3 80.5 L88.8 81.5 L93.2 91.0 L87.0 97.2 L77.5 92.8 L76.5 103.3 L68.1 105.5 L62.0 97.0 L55.9 105.5 L47.5 103.3 L46.5 92.8 L37.0 97.2 L30.8 91.0 L35.2 81.5 L24.7 80.5 L22.5 72.1 L31.0 66.0 L22.5 59.9 L24.7 51.5 L35.2 50.5 L30.8 41.0 L37.0 34.8 L46.5 39.2 L47.5 28.7 L55.9 26.5 L62.0 35.0 L68.1 26.5 L76.5 28.7 L77.5 39.2 L87.0 34.8 L93.2 41.0 L88.8 50.5 L99.3 51.5 L101.5 59.9 L93.0 66.0 Z" transform="translate(3,4)"/></g>
                <path class="gigi-besar" d="M93.0 66.0 L101.5 72.1 L99.3 80.5 L88.8 81.5 L93.2 91.0 L87.0 97.2 L77.5 92.8 L76.5 103.3 L68.1 105.5 L62.0 97.0 L55.9 105.5 L47.5 103.3 L46.5 92.8 L37.0 97.2 L30.8 91.0 L35.2 81.5 L24.7 80.5 L22.5 72.1 L31.0 66.0 L22.5 59.9 L24.7 51.5 L35.2 50.5 L30.8 41.0 L37.0 34.8 L46.5 39.2 L47.5 28.7 L55.9 26.5 L62.0 35.0 L68.1 26.5 L76.5 28.7 L77.5 39.2 L87.0 34.8 L93.2 41.0 L88.8 50.5 L99.3 51.5 L101.5 59.9 L93.0 66.0 Z"/>
                <circle class="poros poros-besar" cx="62" cy="66" r="13"/>

                <g class="bayang"><path d="M133.5 105.0 L139.3 111.1 L135.9 119.3 L127.5 119.5 L127.3 127.9 L119.1 131.3 L113.0 125.5 L106.9 131.3 L98.7 127.9 L98.5 119.5 L90.1 119.3 L86.7 111.1 L92.5 105.0 L86.7 98.9 L90.1 90.7 L98.5 90.5 L98.7 82.1 L106.9 78.7 L113.0 84.5 L119.1 78.7 L127.3 82.1 L127.5 90.5 L135.9 90.7 L139.3 98.9 L133.5 105.0 Z" transform="translate(3,4)"/></g>
                <path class="gigi-kecil" d="M133.5 105.0 L139.3 111.1 L135.9 119.3 L127.5 119.5 L127.3 127.9 L119.1 131.3 L113.0 125.5 L106.9 131.3 L98.7 127.9 L98.5 119.5 L90.1 119.3 L86.7 111.1 L92.5 105.0 L86.7 98.9 L90.1 90.7 L98.5 90.5 L98.7 82.1 L106.9 78.7 L113.0 84.5 L119.1 78.7 L127.3 82.1 L127.5 90.5 L135.9 90.7 L139.3 98.9 L133.5 105.0 Z"/>
                <circle class="poros poros-kecil" cx="113" cy="105" r="9"/>
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
