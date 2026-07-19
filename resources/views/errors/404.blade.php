<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Halaman Tidak Ditemukan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { color-scheme: light dark; }
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(1200px 600px at 50% -10%, #efe9ff 0%, #f7f7fb 45%, #f2f4f9 100%);
            color: #1e293b;
            padding: 24px;
            overflow: hidden;
        }
        .e-wrap { text-align: center; max-width: 520px; width: 100%; }
        .e-art { width: 260px; height: 260px; margin: 0 auto 6px; position: relative; }

        .float-group { animation: floaty 3.4s ease-in-out infinite; transform-origin: center; }
        .eye { transform-box: fill-box; transform-origin: center; animation: blink 3.8s infinite; }
        .spark { transform-box: fill-box; transform-origin: center; animation: twinkle 2.4s ease-in-out infinite; }
        .spark.s2 { animation-delay: .6s; }
        .spark.s3 { animation-delay: 1.2s; }
        .spark.s4 { animation-delay: 1.8s; }
        .shadow { animation: shadowPulse 3.4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .qmark { transform-box: fill-box; transform-origin: center; animation: bobTilt 3s ease-in-out infinite; }

        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
        @keyframes blink { 0%,92%,100% { transform: scaleY(1); } 95% { transform: scaleY(.12); } }
        @keyframes twinkle { 0%,100% { opacity: .2; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @keyframes shadowPulse { 0%,100% { transform: scaleX(1); opacity: .28; } 50% { transform: scaleX(.78); opacity: .16; } }
        @keyframes bobTilt { 0%,100% { transform: rotate(-8deg) translateY(0); } 50% { transform: rotate(8deg) translateY(-6px); } }

        .e-code {
            font-size: 84px; font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
        }
        .e-title { font-size: 1.5rem; font-weight: 800; margin: 10px 0 6px; }
        .e-text { color: #64748b; font-size: .98rem; line-height: 1.55; margin-bottom: 22px; }
        .e-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            padding: 12px 22px; border-radius: 999px; font-weight: 700; font-size: .95rem;
            border: 1px solid transparent; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; box-shadow: 0 10px 24px rgba(124,58,237,.32); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(124,58,237,.42); }
        .btn-ghost { background: #fff; color: #6d28d9; border-color: #e5e0f7; box-shadow: 0 6px 14px rgba(124,58,237,.10); }
        .btn-ghost:hover { transform: translateY(-2px); border-color: #c7bdf2; }

        @media (prefers-color-scheme: dark) {
            body { background: radial-gradient(1200px 600px at 50% -10%, #241b45 0%, #14131c 55%, #0e0d14 100%); color: #e5e7eb; }
            .e-text { color: #9aa4b2; }
            .btn-ghost { background: #1b1a24; color: #c4b5fd; border-color: #2e2b3d; }
        }
        @media (max-width: 480px) { .e-art { width: 210px; height: 210px; } .e-code { font-size: 66px; } }
        @media (prefers-reduced-motion: reduce) {
            .float-group, .eye, .spark, .shadow, .qmark { animation: none !important; }
        }
    </style>
</head>
<body>
    <div class="e-wrap">
        <div class="e-art">
            <svg viewBox="0 0 240 240" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Hantu tersesat">
                <defs>
                    <linearGradient id="ghostGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#8b5cf6"/>
                        <stop offset="1" stop-color="#5b52e6"/>
                    </linearGradient>
                </defs>

                <ellipse class="shadow" cx="120" cy="214" rx="56" ry="12" fill="#7c3aed"/>

                <path class="spark"    d="M44 78 l4 10 10 4 -10 4 -4 10 -4 -10 -10 -4 10 -4 z" fill="#a78bfa"/>
                <path class="spark s2" d="M202 60 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3 z" fill="#c4b5fd"/>
                <circle class="spark s3" cx="198" cy="150" r="5" fill="#8b5cf6"/>
                <circle class="spark s4" cx="44"  cy="150" r="4" fill="#c4b5fd"/>

                <!-- tanda tanya melayang -->
                <text class="qmark" x="176" y="86" font-family="system-ui, sans-serif" font-size="34" font-weight="800" fill="#a78bfa">?</text>

                <g class="float-group">
                    <!-- badan hantu -->
                    <path d="M78 120 a42 42 0 0 1 84 0 V196 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 Z"
                          fill="url(#ghostGrad)"/>
                    <path d="M78 120 a42 42 0 0 1 84 0 V196 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 c-3 12 -18 12 -21 0 Z"
                          fill="#ffffff" opacity="0.06"/>

                    <!-- mata -->
                    <g class="eye">
                        <circle cx="104" cy="120" r="11" fill="#fff"/>
                        <circle cx="106" cy="122" r="4.8" fill="#312e57"/>
                    </g>
                    <g class="eye" style="animation-delay:.15s">
                        <circle cx="138" cy="120" r="11" fill="#fff"/>
                        <circle cx="140" cy="122" r="4.8" fill="#312e57"/>
                    </g>

                    <!-- mulut kaget -->
                    <ellipse cx="121" cy="150" rx="7" ry="9" fill="#312e57"/>

                    <!-- pipi -->
                    <circle cx="90"  cy="140" r="5" fill="#fda4af" opacity=".65"/>
                    <circle cx="152" cy="140" r="5" fill="#fda4af" opacity=".65"/>
                </g>
            </svg>
        </div>

        <div class="e-code">404</div>
        <h1 class="e-title">Halaman Menghilang!</h1>
        <p class="e-text">
            Halaman yang Anda cari tidak ditemukan atau sudah dipindahkan.<br>
            Yuk kembali sebelum tersesat lebih jauh.
        </p>
        <div class="e-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">← Kembali ke Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-ghost">Halaman Sebelumnya</a>
        </div>
    </div>
</body>
</html>
