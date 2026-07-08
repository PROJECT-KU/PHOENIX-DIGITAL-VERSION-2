<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses Ditolak</title>
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
        .e403 { text-align: center; max-width: 520px; width: 100%; }

        /* ===== Ilustrasi ===== */
        .e403-art { width: 260px; height: 260px; margin: 0 auto 6px; position: relative; }
        .lock-group { animation: floaty 3.4s ease-in-out infinite; transform-origin: center; }
        .shackle { animation: shake 2.6s ease-in-out infinite; transform-box: fill-box; transform-origin: 120px 92px; }
        .eye { transform-box: fill-box; transform-origin: center; animation: blink 3.8s infinite; }
        .spark { transform-box: fill-box; transform-origin: center; animation: twinkle 2.4s ease-in-out infinite; }
        .spark.s2 { animation-delay: .6s; }
        .spark.s3 { animation-delay: 1.2s; }
        .spark.s4 { animation-delay: 1.8s; }
        .shadow { animation: shadowPulse 3.4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }

        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
        @keyframes shake {
            0%,88%,100% { transform: rotate(0deg); }
            91% { transform: rotate(-9deg); }
            94% { transform: rotate(9deg); }
            97% { transform: rotate(-5deg); }
        }
        @keyframes blink {
            0%,92%,100% { transform: scaleY(1); }
            95% { transform: scaleY(.12); }
        }
        @keyframes twinkle { 0%,100% { opacity: .2; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @keyframes shadowPulse { 0%,100% { transform: scaleX(1); opacity: .28; } 50% { transform: scaleX(.78); opacity: .16; } }

        /* ===== Teks ===== */
        .e403-code {
            font-size: 84px; font-weight: 800; line-height: 1;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            letter-spacing: 2px;
        }
        .e403-title { font-size: 1.5rem; font-weight: 800; margin: 10px 0 6px; }
        .e403-text { color: #64748b; font-size: .98rem; line-height: 1.55; margin-bottom: 22px; }
        .e403-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
            padding: 12px 22px; border-radius: 999px; font-weight: 700; font-size: .95rem;
            border: 1px solid transparent; cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, background .15s;
        }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; box-shadow: 0 10px 24px rgba(124,58,237,.32); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(124,58,237,.42); }
        .btn-ghost { background: #fff; color: #6d28d9; border-color: #e5e0f7; box-shadow: 0 6px 14px rgba(124,58,237,.10); }
        .btn-ghost:hover { transform: translateY(-2px); border-color: #c7bdf2; }

        @media (prefers-color-scheme: dark) {
            body { background: radial-gradient(1200px 600px at 50% -10%, #241b45 0%, #14131c 55%, #0e0d14 100%); color: #e5e7eb; }
            .e403-text { color: #9aa4b2; }
            .btn-ghost { background: #1b1a24; color: #c4b5fd; border-color: #2e2b3d; }
        }
        @media (max-width: 480px) {
            .e403-art { width: 210px; height: 210px; }
            .e403-code { font-size: 66px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .lock-group, .shackle, .eye, .spark, .shadow { animation: none !important; }
        }
    </style>
</head>
<body>
    <div class="e403">
        <div class="e403-art">
            <svg viewBox="0 0 240 240" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Gembok terkunci">
                <defs>
                    <linearGradient id="bodyGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#8b5cf6"/>
                        <stop offset="1" stop-color="#5b52e6"/>
                    </linearGradient>
                    <linearGradient id="shackleGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#c4b5fd"/>
                        <stop offset="1" stop-color="#a78bfa"/>
                    </linearGradient>
                </defs>

                <!-- bayangan -->
                <ellipse class="shadow" cx="120" cy="214" rx="58" ry="12" fill="#7c3aed"/>

                <!-- sparkles -->
                <path class="spark"    d="M42 70 l4 10 10 4 -10 4 -4 10 -4 -10 -10 -4 10 -4 z" fill="#a78bfa"/>
                <path class="spark s2" d="M204 54 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3 z" fill="#c4b5fd"/>
                <circle class="spark s3" cx="196" cy="150" r="5" fill="#8b5cf6"/>
                <circle class="spark s4" cx="46"  cy="150" r="4" fill="#c4b5fd"/>

                <g class="lock-group">
                    <!-- shackle (gagang gembok) -->
                    <path class="shackle" d="M92 112 V88 a28 28 0 0 1 56 0 V112" fill="none" stroke="url(#shackleGrad)" stroke-width="15" stroke-linecap="round"/>

                    <!-- badan gembok -->
                    <rect x="70" y="108" width="100" height="86" rx="22" fill="url(#bodyGrad)"/>
                    <rect x="70" y="108" width="100" height="86" rx="22" fill="#ffffff" opacity="0.06"/>

                    <!-- mata -->
                    <g class="eye">
                        <circle cx="102" cy="142" r="10" fill="#fff"/>
                        <circle cx="104" cy="144" r="4.5" fill="#312e57"/>
                    </g>
                    <g class="eye" style="animation-delay:.15s">
                        <circle cx="138" cy="142" r="10" fill="#fff"/>
                        <circle cx="140" cy="144" r="4.5" fill="#312e57"/>
                    </g>

                    <!-- mulut cemberut lucu -->
                    <path d="M108 170 q12 -10 24 0" fill="none" stroke="#312e57" stroke-width="4" stroke-linecap="round"/>

                    <!-- pipi -->
                    <circle cx="88" cy="162" r="5" fill="#fda4af" opacity=".65"/>
                    <circle cx="152" cy="162" r="5" fill="#fda4af" opacity=".65"/>
                </g>
            </svg>
        </div>

        <div class="e403-code">403</div>
        <h1 class="e403-title">Ups, Akses Terkunci!</h1>
        <p class="e403-text">
            Anda tidak memiliki izin untuk membuka halaman ini.<br>
            Hubungi admin bila Anda merasa seharusnya punya akses.
        </p>
        <div class="e403-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">← Kembali ke Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-ghost">Halaman Sebelumnya</a>
        </div>
    </div>
</body>
</html>
