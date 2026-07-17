<style>
    body#app {
        background:
            radial-gradient(900px 480px at 15% -10%, rgba(250,204,21,.20), transparent 60%),
            radial-gradient(900px 520px at 100% 110%, rgba(132,204,22,.18), transparent 55%),
            linear-gradient(135deg, #fefce8 0%, #f6faf0 45%, #f3f4f6 100%);
    }
    html[data-bs-theme=dark] body#app {
        background:
            radial-gradient(900px 480px at 15% -10%, rgba(202,138,4,.22), transparent 60%),
            linear-gradient(135deg, #14130c 0%, #101410 50%, #0e0e12 100%);
    }

    .lemon-card {
        width: 30rem; max-width: 92vw;
        background: rgba(255,255,255,.72);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255,255,255,.65);
        border-radius: 26px;
        box-shadow: 0 30px 60px -18px rgba(76, 66, 20, .28), inset 0 1px 0 rgba(255,255,255,.6);
        padding: 38px 40px 34px;
    }
    html[data-bs-theme=dark] .lemon-card {
        background: rgba(28,27,20,.75); border-color: rgba(255,255,255,.08);
        box-shadow: 0 30px 60px -18px rgba(0,0,0,.6);
    }

    /* ===== Logo lemon terpotong + animasi ===== */
    .lemon-logo { width: 92px; height: 92px; margin: 0 auto 14px; filter: drop-shadow(0 12px 20px rgba(202,138,4,.35)); }
    .lemon-spin { transform-box: fill-box; transform-origin: center; animation: lemonBob 4s ease-in-out infinite; }
    .lemon-pulse { transform-box: fill-box; transform-origin: center; animation: lemonJuice 4s ease-in-out infinite; }
    @keyframes lemonBob { 0%,100% { transform: rotate(-8deg) translateY(0); } 50% { transform: rotate(8deg) translateY(-6px); } }
    @keyframes lemonJuice { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

    .lemon-brand {
        font-size: 2.3rem; font-weight: 800; letter-spacing: -1px; line-height: 1; margin: 0;
        background: linear-gradient(135deg, #ca8a04, #4d7c0f);
        -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    }
    .lemon-by { font-size: .74rem; letter-spacing: 3px; text-transform: uppercase; font-weight: 700; color: #a3a3a3; margin: 3px 0 0; }
    html[data-bs-theme=dark] .lemon-by { color: #8b8b8b; }
    .lemon-sub { color: #6b7280; font-size: .92rem; margin-top: 6px; }
    html[data-bs-theme=dark] .lemon-sub { color: #9aa4b2; }

    /* ===== Form ===== */
    .lf-label { font-weight: 600; font-size: .86rem; color: #374151; margin-bottom: 6px; display: block; }
    html[data-bs-theme=dark] .lf-label { color: #d1d5db; }
    .lf-field {
        display: flex; align-items: center; gap: 10px;
        background: #fff; border: 1.5px solid #ece9e0; border-radius: 14px;
        padding: 12px 15px; transition: border-color .15s, box-shadow .15s;
    }
    html[data-bs-theme=dark] .lf-field { background: #201f18; border-color: #33322a; }
    .lf-field:focus-within { border-color: #a3e635; box-shadow: 0 0 0 .2rem rgba(132,204,22,.18); }
    .lf-field i.lead-ico { color: #9ca3af; font-size: 1.05rem; line-height: 1; }
    .lf-input { border: 0; outline: 0; background: transparent; width: 100%; font-size: .95rem; color: #111827; }
    html[data-bs-theme=dark] .lf-input { color: #e5e7eb; }
    .lf-input::placeholder { color: #b6bcc6; }
    .lf-eye { cursor: pointer; color: #9ca3af; line-height: 1; }
    .lf-eye:hover { color: #65a30d; }
    .lf-error { color: #e11d48; font-size: .82rem; margin-top: 6px; display: block; }
    .lf-hint { color: #9ca3af; font-size: .78rem; margin-top: 6px; display: block; }

    .lf-check { display: flex; align-items: center; gap: 7px; font-size: .88rem; color: #4b5563; cursor: pointer; }
    html[data-bs-theme=dark] .lf-check { color: #cbd5e1; }
    .lf-check input { width: 16px; height: 16px; accent-color: #65a30d; }
    .lf-forgot { font-size: .86rem; color: #65a30d; text-decoration: none; font-weight: 600; }
    .lf-forgot:hover { text-decoration: none; color: #4d7c0f; }
    .lf-back { font-size: .88rem; color: #65a30d; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
    .lf-back:hover { text-decoration: none; color: #4d7c0f; }
    .lf-back i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }

    .lf-btn {
        width: 100%; border: 0; border-radius: 14px; padding: 13px; font-weight: 800; font-size: 1rem;
        color: #3f2d00;
        background: linear-gradient(135deg, #fde047, #facc15 45%, #eab308);
        box-shadow: 0 12px 24px rgba(202,138,4,.35), inset 0 1px 0 rgba(255,255,255,.55);
        cursor: pointer; transition: transform .15s, box-shadow .15s, filter .15s;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    }
    .lf-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 18px 32px rgba(202,138,4,.45); filter: brightness(1.02); }
    .lf-btn:disabled { opacity: .75; cursor: default; }
    .lf-btn i.bi { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }

    @media (prefers-reduced-motion: reduce) {
        .lemon-spin, .lemon-pulse { animation: none !important; }
    }

    /* ===== SweetAlert glossy (seragam dengan fitur lain) ===== */
    .swal-glossy-popup { border-radius: 28px !important; backdrop-filter: blur(20px) !important; border: 1px solid rgba(255,255,255,.5) !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25) !important; }
    .btn-glossy-confirm { background: linear-gradient(135deg, #7c3aed, #4f46e5) !important; color: #fff !important; padding: 12px 24px !important; border-radius: 12px !important; margin: 0 5px !important; border: none !important; font-weight: 600 !important; }
    .swal-glossy-popup .swal2-icon.swal2-error { border-color: #f43f5e !important; }
    .swal-glossy-popup .swal2-icon.swal2-error [class^='swal2-x-mark-line'] { background-color: #f43f5e !important; }
    .swal-glossy-popup .swal2-icon.swal2-warning { border-color: #f59e0b !important; color: #f59e0b !important; }
    .swal-glossy-popup .swal2-icon.swal2-success { border-color: #10b981 !important; }
    .swal-glossy-popup .swal2-icon.swal2-success [class^='swal2-success-line'] { background-color: #10b981 !important; }
    .swal-glossy-popup .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(16,185,129,.3) !important; }
</style>
