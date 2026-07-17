{{-- ============================================================
     Phoenix Digital — Loader
     Sayap (feather) tersusun satu per satu hingga logo utuh.
     Self-contained: style + script inline, prefix .phx-*.
     ============================================================ --}}
<div id="phoenix-loader" class="phx-loader" role="status" aria-live="polite" aria-label="Memuat Phoenix Digital">
    <div class="phx-stage">
        <div class="phx-glow" aria-hidden="true"></div>

        {{-- Embers / bara yang naik --}}
        <div class="phx-embers" aria-hidden="true">
            <span class="phx-ember e1"></span>
            <span class="phx-ember e2"></span>
            <span class="phx-ember e3"></span>
            <span class="phx-ember e4"></span>
            <span class="phx-ember e5"></span>
            <span class="phx-ember e6"></span>
        </div>

        <div class="phx-mark-wrap">
            {{-- Logo ASLI (PNG) diungkap per irisan dari pivot bawah-kiri, satu per satu,
                 dari sayap bawah ke atas — hingga logo utuh yang persis sama. --}}
            <div class="phx-mark-img">
                <img class="phx-slice s1" src="{{ asset('storage/img/phoenix-mark.png') }}" alt="" aria-hidden="true">
                <img class="phx-slice s2" src="{{ asset('storage/img/phoenix-mark.png') }}" alt="" aria-hidden="true">
                <img class="phx-slice s3" src="{{ asset('storage/img/phoenix-mark.png') }}" alt="" aria-hidden="true">
                <img class="phx-slice s4" src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital">
                <span class="phx-sheen" aria-hidden="true"></span>
            </div>
        </div>

        <div class="phx-word">
            <span class="phx-word-top">Phoenix</span>
            <span class="phx-word-bot">Digital</span>
        </div>

        <div class="phx-bar" aria-hidden="true"><span></span></div>
    </div>
</div>

<style>
    .phx-loader {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(120% 90% at 50% 38%, #fffaf3 0%, #fff3e6 55%, #ffe9d6 100%);
        opacity: 1;
        visibility: visible;
        transition: opacity .55s ease, visibility 0s linear .55s;
    }
    .phx-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .55s ease, visibility 0s linear .55s;
    }

    .phx-stage {
        position: relative;
        width: 240px;
        max-width: 78vw;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Cahaya hangat di belakang logo */
    .phx-glow {
        position: absolute;
        top: -14px;
        left: 50%;
        width: 240px;
        height: 240px;
        transform: translateX(-50%);
        border-radius: 50%;
        background: radial-gradient(circle, rgba(251,169,25,.42) 0%, rgba(242,101,34,.16) 42%, rgba(242,101,34,0) 70%);
        filter: blur(4px);
        animation: phxGlow 2.6s ease-in-out infinite;
        pointer-events: none;
    }
    @keyframes phxGlow {
        0%, 100% { opacity: .55; transform: translateX(-50%) scale(.86); }
        45%      { opacity: 1;   transform: translateX(-50%) scale(1.06); }
    }

    .phx-mark-wrap { position: relative; z-index: 2; }
    /* Bingkai logo — rasio asli 179:154 */
    .phx-mark-img {
        position: relative;
        width: 152px;
        height: 131px;
        filter: drop-shadow(0 12px 26px rgba(240,90,24,.28));
    }

    /* Tiap irisan = salinan logo asli, di-clip jadi satu sayap, tumbuh dari pivot */
    .phx-slice {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        opacity: 0;
        transform-origin: 8% 96%;              /* pivot bawah-kiri, tempat sayap bertemu */
        animation: phxSlice calc(2.6s * var(--phx-mul, 1)) cubic-bezier(.22,.9,.24,1) infinite;
        will-change: transform, opacity;
    }
    /* Irisan kipas dari pivot: bawah (paling datar) → atas (paling tegak).
       Sedikit tumpang-tindih agar tak ada celah saat utuh. */
    .phx-slice.s1 { clip-path: polygon(8% 96%, 106% 101%, 106% 43%);            animation-delay: 0s;   }
    .phx-slice.s2 { clip-path: polygon(8% 96%, 106% 52%, 93% 7%);              animation-delay: .13s; }
    .phx-slice.s3 { clip-path: polygon(8% 96%, 96% 10%, 51% -6%);             animation-delay: .26s; }
    .phx-slice.s4 { clip-path: polygon(8% 96%, 57% -4%, 10% -2%, -4% 42%);    animation-delay: .39s; }
    @keyframes phxSlice {
        0%   { opacity: 0; transform: scale(.34); }
        12%  { opacity: 1; }
        22%  { transform: scale(1.04); }        /* sedikit overshoot */
        30%  { transform: scale(1); }
        68%  { opacity: 1; transform: none; }
        84%  { opacity: 0; transform: scale(.34); }
        100% { opacity: 0; transform: scale(.34); }
    }

    /* Sapuan cahaya melintasi logo saat utuh */
    .phx-sheen {
        position: absolute;
        top: -12%;
        left: 0;
        width: 26px;
        height: 124%;
        background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.6), rgba(255,255,255,0));
        transform: translateX(-40px) rotate(20deg);
        opacity: 0;
        mix-blend-mode: overlay;
        pointer-events: none;
        animation: phxSheen calc(2.6s * var(--phx-mul, 1)) ease-in-out infinite;
    }
    @keyframes phxSheen {
        0%, 32%   { opacity: 0; transform: translateX(-40px) rotate(20deg); }
        44%       { opacity: .85; }
        60%       { opacity: 0; transform: translateX(150px) rotate(20deg); }
        100%      { opacity: 0; transform: translateX(150px) rotate(20deg); }
    }

    /* Bara/ember naik */
    .phx-embers {
        position: absolute;
        top: 8px;
        left: 50%;
        width: 150px;
        height: 150px;
        transform: translateX(-50%);
        z-index: 1;
        pointer-events: none;
    }
    .phx-ember {
        position: absolute;
        bottom: 34px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: radial-gradient(circle, #ffd873 0%, #f7931a 55%, rgba(247,147,26,0) 72%);
        opacity: 0;
        animation: phxEmber 2.8s ease-in infinite;
    }
    .phx-ember.e1 { left: 40%; animation-delay: .1s; }
    .phx-ember.e2 { left: 54%; width: 4px; height: 4px; animation-delay: .7s; }
    .phx-ember.e3 { left: 62%; animation-delay: 1.3s; }
    .phx-ember.e4 { left: 48%; width: 5px; height: 5px; animation-delay: 1.9s; }
    .phx-ember.e5 { left: 58%; width: 3px; height: 3px; animation-delay: 1s; }
    .phx-ember.e6 { left: 44%; width: 4px; height: 4px; animation-delay: 2.3s; }
    @keyframes phxEmber {
        0%   { opacity: 0; transform: translate(0, 0) scale(.6); }
        14%  { opacity: 1; }
        70%  { opacity: .9; }
        100% { opacity: 0; transform: translate(10px, -68px) scale(.3); }
    }

    /* Wordmark */
    .phx-word {
        margin-top: 18px;
        text-align: center;
        line-height: 1;
        opacity: 0;
        transform: translateY(8px);
        animation: phxWord 2.6s ease-in-out infinite;
    }
    .phx-word-top {
        display: block;
        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: 1.5rem;
        letter-spacing: .5px;
        background: linear-gradient(90deg, #f7931a, #f0531e);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .phx-word-bot {
        display: block;
        margin-top: 3px;
        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', sans-serif;
        font-weight: 600;
        font-size: .72rem;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #b56412;
    }
    @keyframes phxWord {
        0%, 22%  { opacity: 0; transform: translateY(8px); }
        40%, 74% { opacity: 1; transform: translateY(0); }
        92%,100% { opacity: 0; transform: translateY(8px); }
    }

    /* Progress bar indeterminate */
    .phx-bar {
        margin-top: 20px;
        width: 148px;
        height: 4px;
        border-radius: 99px;
        background: rgba(240,90,24,.14);
        overflow: hidden;
    }
    .phx-bar span {
        display: block;
        width: 42%;
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, #fba919, #f0531e);
        animation: phxBar 1.25s cubic-bezier(.65,.05,.35,1) infinite;
    }
    @keyframes phxBar {
        0%   { transform: translateX(-120%); }
        100% { transform: translateX(360%); }
    }

    /* Mode gelap (jika perangkat/tema gelap) */
    @media (prefers-color-scheme: dark) {
        .phx-loader {
            background: radial-gradient(120% 90% at 50% 38%, #1c130b 0%, #140d07 60%, #0d0805 100%);
        }
        .phx-word-bot { color: #f2a24b; }
        .phx-bar { background: rgba(251,169,25,.18); }
    }

    /* Hormati pengguna yang mengurangi animasi: tampilkan logo utuh, denyut lembut */
    @media (prefers-reduced-motion: reduce) {
        .phx-slice { opacity: 1 !important; transform: none !important; clip-path: none !important; animation: phxSoftPulse 2s ease-in-out infinite; }
        .phx-word { opacity: 1 !important; transform: none !important; animation: none; }
        .phx-glow, .phx-embers { animation: none; }
        .phx-sheen { display: none; }
        @keyframes phxSoftPulse { 0%,100% { opacity: .85; } 50% { opacity: 1; } }
    }
</style>

<script>
    (function () {
        var el = document.getElementById('phoenix-loader');
        if (!el) return;

        var MIN_MS = 650;           // tampil minimal supaya tidak berkedip
        var shownAt = Date.now();
        var hideTimer = null;

        function hide() {
            var wait = Math.max(0, MIN_MS - (Date.now() - shownAt));
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () { el.classList.add('is-hidden'); }, wait);
        }
        function show() {
            clearTimeout(hideTimer);
            shownAt = Date.now();
            el.classList.remove('is-hidden');
        }

        // Muat awal
        if (document.readyState === 'complete') hide();
        else window.addEventListener('load', hide);

        // Navigasi SPA Livewire (wire:navigate)
        document.addEventListener('livewire:navigate', show);
        document.addEventListener('livewire:navigated', hide);

        // Jaring pengaman: jangan sampai loader tersangkut
        setTimeout(function () { el.classList.add('is-hidden'); }, 8000);
    })();
</script>
