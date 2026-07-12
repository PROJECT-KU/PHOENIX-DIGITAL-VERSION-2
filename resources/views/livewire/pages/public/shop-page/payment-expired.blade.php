<div>
    <style>
        #ph-page-lines { display: none !important; }
        .exp-hg { animation: expFloat 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-glow { animation: expGlow 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-shadow { animation: expShadow 4s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .exp-stream { stroke-dasharray: 2 5; animation: expSand .55s linear infinite; }
        .exp-spark { transform-box: fill-box; transform-origin: center; animation: expTwinkle 2.4s ease-in-out infinite; }
        .exp-spark.s2 { animation-delay: .6s; }
        .exp-spark.s3 { animation-delay: 1.2s; }
        .exp-spark.s4 { animation-delay: 1.8s; }
        @keyframes expFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes expGlow { 0%,100% { opacity: .5; transform: scale(1); } 50% { opacity: .85; transform: scale(1.07); } }
        @keyframes expShadow { 0%,100% { transform: scaleX(1); opacity: .16; } 50% { transform: scaleX(.8); opacity: .09; } }
        @keyframes expSand { to { stroke-dashoffset: -21; } }
        @keyframes expTwinkle { 0%,100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @media (prefers-reduced-motion: reduce) {
            .exp-hg, .exp-glow, .exp-shadow, .exp-stream, .exp-spark { animation: none !important; }
        }
        .exp-order { display: inline-block; margin-top: 4px; font-family: 'Courier New', monospace; font-weight: 700; color: var(--ph-orange); background: var(--ph-soft); border: 1px solid var(--ph-line); border-radius: 8px; padding: 3px 12px; }
    </style>

    <section class="cart-section">
        <div class="container">
            <div class="ph-empty" style="max-width: 560px;">
                <div class="ph-empty-art" style="max-width: 300px; margin-left: auto; margin-right: auto;">
                    <svg viewBox="0 0 240 240" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Jam pasir kedaluwarsa">
                        <defs>
                            <linearGradient id="ehgO" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#fbc25a" />
                                <stop offset="1" stop-color="#f26522" />
                            </linearGradient>
                            <linearGradient id="ehgV" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#fba919" />
                                <stop offset="1" stop-color="#f26522" />
                            </linearGradient>
                            <radialGradient id="ehgGlow" cx="50%" cy="50%" r="50%">
                                <stop offset="0" stop-color="#fba919" stop-opacity=".55" />
                                <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                            </radialGradient>
                        </defs>

                        <circle class="exp-glow" cx="120" cy="118" r="82" fill="url(#ehgGlow)" />
                        <ellipse class="exp-shadow" cx="120" cy="214" rx="56" ry="11" fill="#e15a18" />

                        <g transform="translate(46,72)"><path class="exp-spark s1" d="M0,-8 L2,-2 8,0 2,2 0,8 -2,2 -8,0 -2,-2Z" fill="#fba919" /></g>
                        <g transform="translate(198,92)"><path class="exp-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                        <circle class="exp-spark s3" cx="196" cy="158" r="5" fill="#fbaf45" />
                        <circle class="exp-spark s4" cx="48" cy="156" r="4" fill="#f4772b" />

                        <g class="exp-hg">
                            <rect x="66" y="44" width="108" height="12" rx="6" fill="url(#ehgV)" />
                            <rect x="66" y="184" width="108" height="12" rx="6" fill="url(#ehgV)" />
                            <rect x="70" y="53" width="6" height="132" rx="3" fill="url(#ehgV)" opacity=".45" />
                            <rect x="164" y="53" width="6" height="132" rx="3" fill="url(#ehgV)" opacity=".45" />
                            <path d="M80 56 L160 56 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#ehgO)" stroke-width="4" stroke-linejoin="round" />
                            <path d="M80 184 L160 184 L120 120 Z" fill="rgba(251,169,25,.13)" stroke="url(#ehgO)" stroke-width="4" stroke-linejoin="round" />
                            <path d="M101 104 L139 104 L120 120 Z" fill="url(#ehgV)" />
                            <line class="exp-stream" x1="120" y1="120" x2="120" y2="150" stroke="url(#ehgV)" stroke-width="3.5" stroke-linecap="round" />
                            <path d="M84 184 L156 184 L120 150 Z" fill="url(#ehgV)" />
                            <path d="M92 64 L108 64" stroke="#ffffff" stroke-opacity=".5" stroke-width="3" stroke-linecap="round" />
                        </g>
                    </svg>
                </div>

                <span class="ph-sec-eyebrow" style="margin-bottom:10px;"><i class="bi bi-hourglass-bottom"></i> Kedaluwarsa</span>
                <h3 class="ph-empty-title">Waktu Pembayaran Habis</h3>
                <p class="ph-empty-sub">
                    Pesanan <span class="exp-order">{{ $order->order_number }}</span> telah <b>dibatalkan</b> karena melewati
                    batas waktu pembayaran. Tidak masalah — Anda bisa memesan kembali kapan saja.
                </p>
                <div class="ph-empty-actions">
                    <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Belanja Lagi</a>
                    <a href="{{ route('homepage') }}" class="ph-empty-btn ghost"><i class="bi bi-house"></i> Ke Beranda</a>
                </div>
            </div>
        </div>
    </section>
</div>
