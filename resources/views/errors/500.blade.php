<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Terjadi Kesalahan</title>
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

        .float-group { animation: floaty 3.6s ease-in-out infinite; transform-origin: center; }
        .robot { animation: wobble 3.2s ease-in-out infinite; transform-box: fill-box; transform-origin: 120px 184px; }
        .spark { transform-box: fill-box; transform-origin: center; animation: twinkle 2.4s ease-in-out infinite; }
        .spark.s2 { animation-delay: .6s; }
        .spark.s3 { animation-delay: 1.2s; }
        .shadow { animation: shadowPulse 3.6s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .warn { transform-box: fill-box; transform-origin: center; animation: warnBlink 1.1s ease-in-out infinite; }
        .bolt { transform-box: fill-box; transform-origin: center; animation: twinkle 1.5s ease-in-out infinite; }
        .sweat { transform-box: fill-box; transform-origin: center; animation: drip 2.6s ease-in-out infinite; }

        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes wobble { 0%,100% { transform: rotate(-3.5deg); } 50% { transform: rotate(3.5deg); } }
        @keyframes twinkle { 0%,100% { opacity: .2; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @keyframes shadowPulse { 0%,100% { transform: scaleX(1); opacity: .28; } 50% { transform: scaleX(.78); opacity: .16; } }
        @keyframes warnBlink { 0%,100% { opacity: 1; } 50% { opacity: .2; } }
        @keyframes drip { 0% { transform: translateY(0); opacity: 0; } 25% { opacity: 1; } 100% { transform: translateY(20px); opacity: 0; } }

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
            .float-group, .robot, .spark, .shadow, .warn, .bolt, .sweat { animation: none !important; }
        }
    </style>
</head>
<body>
    <div class="e-wrap">
        <div class="e-art">
            <svg viewBox="0 0 240 240" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Robot rusak">
                <defs>
                    <linearGradient id="robotGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#8b5cf6"/>
                        <stop offset="1" stop-color="#5b52e6"/>
                    </linearGradient>
                </defs>

                <ellipse class="shadow" cx="120" cy="216" rx="58" ry="12" fill="#7c3aed"/>

                <!-- percikan / kilat error -->
                <path class="bolt" d="M196 96 l-12 20 8 2 -10 18 22 -24 -8 -2 z" fill="#f59e0b"/>
                <circle class="spark s2" cx="48" cy="96" r="5" fill="#f43f5e"/>
                <circle class="spark s3" cx="52" cy="158" r="4" fill="#c4b5fd"/>

                <g class="float-group">
                    <g class="robot">
                        <!-- antena + lampu peringatan -->
                        <line x1="120" y1="104" x2="120" y2="82" stroke="#a78bfa" stroke-width="5" stroke-linecap="round"/>
                        <circle class="warn" cx="120" cy="76" r="8" fill="#f43f5e"/>

                        <!-- baut telinga -->
                        <rect x="64" y="128" width="12" height="26" rx="5" fill="#a78bfa"/>
                        <rect x="164" y="128" width="12" height="26" rx="5" fill="#a78bfa"/>

                        <!-- kepala -->
                        <rect x="74" y="104" width="92" height="82" rx="22" fill="url(#robotGrad)"/>
                        <rect x="74" y="104" width="92" height="82" rx="22" fill="#ffffff" opacity="0.06"/>

                        <!-- layar wajah -->
                        <rect x="88" y="122" width="64" height="46" rx="12" fill="#ede9fe"/>

                        <!-- mata X (error) -->
                        <g stroke="#4c1d95" stroke-width="4" stroke-linecap="round">
                            <path d="M101 133 L113 145"/><path d="M113 133 L101 145"/>
                            <path d="M127 133 L139 145"/><path d="M139 133 L127 145"/>
                        </g>

                        <!-- mulut zigzag glitch -->
                        <path d="M104 158 l7 -6 7 6 7 -6 7 6" fill="none" stroke="#4c1d95" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <!-- pipi -->
                        <circle cx="92"  cy="150" r="4.5" fill="#fda4af" opacity=".6"/>
                        <circle cx="148" cy="150" r="4.5" fill="#fda4af" opacity=".6"/>
                    </g>
                </g>

                <!-- tetesan keringat -->
                <path class="sweat" d="M168 118 c0 6 -8 10 -8 16 a8 8 0 0 0 16 0 c0 -6 -8 -10 -8 -16 z" fill="#38bdf8"/>
            </svg>
        </div>

        <div class="e-code">500</div>
        <h1 class="e-title">Sistem Sedang Ngambek</h1>
        <p class="e-text">
            Terjadi kesalahan di server kami. Tim sedang memperbaikinya.<br>
            Coba muat ulang beberapa saat lagi.
        </p>
        <div class="e-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">← Kembali ke Dashboard</a>
            <a href="javascript:location.reload()" class="btn btn-ghost">Muat Ulang</a>
        </div>
    </div>
</body>
</html>
