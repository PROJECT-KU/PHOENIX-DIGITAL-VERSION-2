<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 — Halaman Kedaluwarsa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { color-scheme: light dark; }
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(1200px 600px at 50% -10%, #ffe9d0 0%, #fff6ec 45%, #ffffff 100%);
            color: #23272f;
            padding: 24px;
            overflow: hidden;
        }
        .e-wrap { text-align: center; max-width: 520px; width: 100%; }
        .e-art { width: 260px; height: 260px; margin: 0 auto 4px; }
        .e-art svg { width: 100%; height: 100%; overflow: visible; }

        .e-hg { animation: floaty 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .e-glow { animation: glowPulse 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .e-shadow { animation: shadowPulse 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .e-stream { stroke-dasharray: 2 5; animation: sand .55s linear infinite; }
        .e-spark { transform-box: fill-box; transform-origin: center; animation: twinkle 2.4s ease-in-out infinite; }
        .e-spark.s2 { animation-delay: .6s; }
        .e-spark.s3 { animation-delay: 1.2s; }
        .e-spark.s4 { animation-delay: 1.8s; }

        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes glowPulse { 0%,100% { opacity: .5; transform: scale(1); } 50% { opacity: .85; transform: scale(1.07); } }
        @keyframes shadowPulse { 0%,100% { transform: scaleX(1); opacity: .16; } 50% { transform: scaleX(.8); opacity: .09; } }
        @keyframes sand { to { stroke-dashoffset: -21; } }
        @keyframes twinkle { 0%,100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }

        .e-eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;
            color: #f26522; background: #fff5ec; border: 1px solid #f1e6d8;
            padding: 6px 14px; border-radius: 999px; margin-bottom: 14px;
        }
        .e-code {
            font-size: 80px; font-weight: 800; line-height: 1; letter-spacing: 2px;
            background: linear-gradient(135deg, #fba919, #f26522);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .e-title { font-size: 1.5rem; font-weight: 800; margin: 8px 0 6px; }
        .e-text { color: #6b7280; font-size: .98rem; line-height: 1.6; margin-bottom: 24px; }
        .e-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            padding: 13px 24px; border-radius: 14px; font-weight: 700; font-size: .95rem;
            border: 1.5px solid transparent; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }
        .btn-primary { background: linear-gradient(135deg, #fba919, #f26522); color: #fff; box-shadow: 0 12px 28px rgba(242,101,34,.32); }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.04); box-shadow: 0 16px 34px rgba(242,101,34,.42); }
        .btn-ghost { background: #fff; color: #f26522; border-color: #f1e6d8; box-shadow: 0 6px 14px rgba(242,101,34,.08); }
        .btn-ghost:hover { transform: translateY(-2px); border-color: #f4772b; }

        @media (prefers-color-scheme: dark) {
            body { background: radial-gradient(1200px 600px at 50% -10%, #3a2412 0%, #1a1512 55%, #100d0b 100%); color: #f3ede6; }
            .e-text { color: #b9afa4; }
            .e-eyebrow { background: #26201b; border-color: #3a3128; }
            .btn-ghost { background: #221c18; color: #fbaf45; border-color: #3a3128; }
        }
        @media (max-width: 480px) { .e-art { width: 210px; height: 210px; } .e-code { font-size: 62px; } }
        @media (prefers-reduced-motion: reduce) {
            .e-hg, .e-glow, .e-shadow, .e-stream, .e-spark { animation: none !important; }
        }
    </style>
</head>
<body>
    <div class="e-wrap">
        <div class="e-art">
            <svg viewBox="0 0 240 240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Jam pasir kedaluwarsa">
                <defs>
                    <linearGradient id="hgO" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#fbc25a" />
                        <stop offset="1" stop-color="#f26522" />
                    </linearGradient>
                    <linearGradient id="hgV" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#fba919" />
                        <stop offset="1" stop-color="#f26522" />
                    </linearGradient>
                    <radialGradient id="hgGlow" cx="50%" cy="50%" r="50%">
                        <stop offset="0" stop-color="#fba919" stop-opacity=".55" />
                        <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                    </radialGradient>
                </defs>

                <circle class="e-glow" cx="120" cy="118" r="82" fill="url(#hgGlow)" />
                <ellipse class="e-shadow" cx="120" cy="214" rx="56" ry="11" fill="#e15a18" />

                <g transform="translate(46,72)"><path class="e-spark s1" d="M0,-8 L2,-2 8,0 2,2 0,8 -2,2 -8,0 -2,-2Z" fill="#fba919" /></g>
                <g transform="translate(198,92)"><path class="e-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                <circle class="e-spark s3" cx="196" cy="158" r="5" fill="#fbaf45" />
                <circle class="e-spark s4" cx="48" cy="156" r="4" fill="#f4772b" />

                <g class="e-hg">
                    {{-- rangka --}}
                    <rect x="66" y="44" width="108" height="12" rx="6" fill="url(#hgV)" />
                    <rect x="66" y="184" width="108" height="12" rx="6" fill="url(#hgV)" />
                    <rect x="70" y="53" width="6" height="132" rx="3" fill="url(#hgV)" opacity=".45" />
                    <rect x="164" y="53" width="6" height="132" rx="3" fill="url(#hgV)" opacity=".45" />

                    {{-- kaca --}}
                    <path d="M80 56 L160 56 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#hgO)" stroke-width="4" stroke-linejoin="round" />
                    <path d="M80 184 L160 184 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#hgO)" stroke-width="4" stroke-linejoin="round" />

                    {{-- pasir atas (tersisa sedikit) --}}
                    <path d="M101 104 L139 104 L120 120 Z" fill="url(#hgV)" />
                    {{-- aliran pasir jatuh --}}
                    <line class="e-stream" x1="120" y1="120" x2="120" y2="150" stroke="url(#hgV)" stroke-width="3.5" stroke-linecap="round" />
                    {{-- gundukan pasir bawah --}}
                    <path d="M84 184 L156 184 L120 150 Z" fill="url(#hgV)" />

                    {{-- kilau kaca --}}
                    <path d="M92 64 L108 64" stroke="#ffffff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round" />
                </g>
            </svg>
        </div>

        <span class="e-eyebrow">⏳ Kedaluwarsa</span>
        <div class="e-code">419</div>
        <h1 class="e-title">Sesi Telah Berakhir</h1>
        <p class="e-text">
            Halaman atau sesi ini sudah tidak berlaku (kedaluwarsa).<br>
            Silakan muat ulang atau kembali ke beranda, lalu coba lagi.
        </p>
        <div class="e-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">← Kembali ke Beranda</a>
            <a href="javascript:location.reload()" class="btn btn-ghost">Muat Ulang</a>
        </div>
    </div>
</body>
</html>
