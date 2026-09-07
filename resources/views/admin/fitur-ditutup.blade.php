<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $judul }} sedang diperbaiki — lemon</title>
    <style>
        /* Berdiri sendiri, tidak menumpang layout panel: layouts.app adalah
           layout komponen Livewire yang menuntut $slot, sehingga tidak bisa
           dipakai untuk view biasa yang dikembalikan middleware. */
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            min-height:100vh;display:flex;align-items:center;justify-content:center;
            padding:28px;background:#f8fafc;color:#334155;
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;line-height:1.6;
        }
        .kotak{
            max-width:520px;width:100%;text-align:center;background:#fff;
            border:1px solid #f6dcae;border-radius:20px;padding:42px 34px;
            box-shadow:0 1px 2px rgba(15,23,42,.05),0 12px 32px rgba(15,23,42,.06);
        }
        .ikon{
            width:74px;height:74px;margin:0 auto 22px;border-radius:22px;
            display:flex;align-items:center;justify-content:center;
            background:linear-gradient(135deg,#fbbf24,#f59e0b);
            box-shadow:0 10px 22px rgba(217,119,6,.26),0 0 0 6px rgba(245,158,11,.12);
        }
        .ikon svg{width:34px;height:34px;fill:#fff;
            transform-origin:50% 50%;animation:goyang 2.6s ease-in-out infinite}
        h1{font-size:1.24rem;font-weight:700;margin-bottom:11px;color:#1e293b}
        p{font-size:.94rem;color:#64748b;margin-bottom:26px}
        a{
            display:inline-flex;align-items:center;gap:8px;text-decoration:none;
            padding:11px 22px;border-radius:12px;font-weight:600;font-size:.9rem;
            background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;
            box-shadow:0 8px 15px rgba(124,58,237,.25);
        }
        @keyframes goyang{
            0%,100%{transform:rotate(-12deg)}
            50%{transform:rotate(12deg)}
        }
        @media (prefers-reduced-motion:reduce){.ikon svg{animation:none}}
    </style>
</head>
<body>
    <div class="kotak">
        <div class="ikon">
            {{-- Kunci pas: lambang perbaikan, digambar langsung agar tak
                 bergantung pada berkas ikon mana pun. --}}
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21.7 18.6l-7.1-7.1c.6-1.6.3-3.5-1-4.8-1.4-1.4-3.4-1.7-5-.9l2.9 2.9-2 2-3-2.9c-.9 1.7-.5 3.7.9 5.1 1.3 1.3 3.2 1.6 4.8 1l7.1 7.1c.3.3.7.3 1 0l1.4-1.4c.3-.3.3-.7 0-1z"/>
            </svg>
        </div>
        <h1>{{ $judul }} sedang diperbaiki</h1>
        <p>{{ $pesan }}</p>
        <a href="{{ route('admin.dashboard') }}">Kembali ke Dashboard</a>
    </div>
</body>
</html>
