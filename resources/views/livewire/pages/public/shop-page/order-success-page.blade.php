<div>
    <style>
        #ph-page-lines { display: none !important; }
        .su-ring { transform-box: fill-box; transform-origin: center; animation: suRing 2.4s ease-out infinite; }
        .su-ring.r2 { animation-delay: 1.2s; }
        .su-check { stroke-dasharray: 90; stroke-dashoffset: 90; animation: suCheck .55s ease forwards .25s; }
        .su-pop { transform-box: fill-box; transform-origin: center; animation: suPop .5s cubic-bezier(.2,1.4,.4,1) forwards; }
        .su-spark { transform-box: fill-box; transform-origin: center; animation: suTwinkle 2s ease-in-out infinite; }
        .su-spark.s2 { animation-delay: .5s; }
        .su-spark.s3 { animation-delay: 1s; }
        .su-spark.s4 { animation-delay: 1.5s; }
        @keyframes suRing { 0% { transform: scale(.7); opacity: .55; } 100% { transform: scale(1.7); opacity: 0; } }
        @keyframes suCheck { to { stroke-dashoffset: 0; } }
        @keyframes suPop { 0% { transform: scale(0); } 100% { transform: scale(1); } }
        @keyframes suTwinkle { 0%,100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }
        @media (prefers-reduced-motion: reduce) {
            .su-ring, .su-check, .su-pop, .su-spark { animation: none !important; stroke-dashoffset: 0 !important; }
        }
        .su-code { font-family: 'Courier New', monospace; font-weight: 700; color: var(--ph-orange); background: var(--ph-soft); border: 1px solid var(--ph-line); border-radius: 8px; padding: 2px 10px; }
    </style>

    <section class="cart-section">
        <div class="container">
            <div style="max-width: 600px; margin: 0 auto;">
                <div class="ph-empty" style="padding-bottom: 6px;">
                    <div class="ph-empty-art" style="max-width: 220px; margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Pembayaran berhasil">
                            <defs>
                                <linearGradient id="suG" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#fbc25a" />
                                    <stop offset="1" stop-color="#f26522" />
                                </linearGradient>
                            </defs>
                            <circle class="su-ring" cx="100" cy="100" r="46" fill="none" stroke="url(#suG)" stroke-width="3" />
                            <circle class="su-ring r2" cx="100" cy="100" r="46" fill="none" stroke="url(#suG)" stroke-width="3" />

                            <g transform="translate(40,52)"><path class="su-spark s1" d="M0,-8 L2,-2 8,0 2,2 0,8 -2,2 -8,0 -2,-2Z" fill="#fba919" /></g>
                            <g transform="translate(162,60)"><path class="su-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <circle class="su-spark s3" cx="164" cy="146" r="4.5" fill="#fbaf45" />
                            <circle class="su-spark s4" cx="40" cy="150" r="4" fill="#f4772b" />

                            <g class="su-pop">
                                <circle cx="100" cy="100" r="46" fill="url(#suG)" />
                                <path class="su-check" d="M78 101 L94 117 L124 83" fill="none" stroke="#fff" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                        </svg>
                    </div>

                    <span class="ph-sec-eyebrow" style="margin-bottom: 10px;"><i class="bi bi-patch-check-fill"></i> Berhasil</span>
                    <h3 class="ph-empty-title">Pembayaran Berhasil! 🎉</h3>
                    <p class="ph-empty-sub">
                        Terima kasih! Pesanan <span class="su-code">{{ $order->order_number }}</span> telah kami terima dan sedang diproses.
                    </p>
                </div>

                {{-- Ringkasan --}}
                <div class="pay-card">
                    <div class="pay-card-head"><i class="bi bi-receipt"></i> Ringkasan Pesanan</div>
                    <div class="pay-card-body">
                        @foreach ($order->items as $item)
                            <div class="pay-item">
                                <div>
                                    <div class="pay-item-name">{{ $item->product_name }}</div>
                                    <div class="pay-item-dur">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                </div>
                            </div>
                        @endforeach

                        <div class="pay-sum">
                            <div class="pay-sum-row"><span>Subtotal</span><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
                            @if ($order->total_discount > 0)
                                <div class="pay-sum-row is-disc"><span>Diskon</span><strong>− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</strong></div>
                            @endif
                            @if ((int) $order->unique_code > 0)
                                <div class="pay-sum-row"><span>Kode Unik</span><strong>+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</strong></div>
                            @endif
                        </div>
                        <div class="pay-total">
                            <span>Total Dibayar</span>
                            <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                {{-- Info pengiriman akun — hanya bila ada item AKUN (produk jasa tak dikirim akun) --}}
                @if ($order->items->contains(fn ($i) => ! optional($i->product)->butuh_file))
                <div class="pay-deadline" style="align-items:flex-start;">
                    <i class="bi bi-envelope-check" style="margin-top:2px;"></i>
                    <div>
                        <span>Pengiriman Akun</span>
                        <b>Detail akun dikirim ke {{ $order->customer->email }}</b>
                        <div style="font-size:.82rem; color:var(--ph-muted); margin-top:4px;">via Email / WhatsApp, maksimal 1×24 jam pada jam operasional.</div>
                    </div>
                </div>
                @endif

                {{-- ===== Pesanan JASA: arahkan ke halaman pengecekan (link permanen) ===== --}}
                @if ($order->butuhUpload())
                <div class="pay-card" style="margin-top:14px; border:1px solid #fde68a; background:linear-gradient(180deg,#fffdf5,#fff);">
                    <div class="pay-card-head" style="color:#b45309;"><i class="bi bi-shield-check"></i> Halaman Cek Plagiasi</div>
                    <div class="pay-card-body">
                        <p style="font-size:.88rem; color:var(--ph-muted); margin-bottom:12px;">
                            Pesanan ini termasuk <b>jasa cek plagiasi</b>. Unggah file &amp; unduh hasil lewat halaman khusus di bawah.
                            <b>Simpan linknya</b> agar bisa dibuka kapan saja — tanpa perlu login.
                        </p>

                        <div style="display:flex; gap:8px; align-items:center; background:#f8fafc; border:1px dashed var(--ph-line); border-radius:10px; padding:8px 10px; margin-bottom:10px;">
                            <code id="su-cek-link" style="flex:1; min-width:0; font-size:.78rem; color:#334155; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ url('/cek/'.$order->share_token) }}</code>
                            <button type="button" onclick="suSalinCek()" style="border:0; background:var(--ph-orange); color:#fff; border-radius:8px; padding:6px 12px; font-size:.78rem; font-weight:700; cursor:pointer; white-space:nowrap;"><i class="bi bi-clipboard"></i> Salin</button>
                        </div>

                        <a href="{{ route('jasa.cek', $order->share_token) }}" class="ph-empty-btn" style="width:100%; justify-content:center;">
                            <i class="bi bi-box-arrow-up-right"></i> Buka Halaman Pengecekan
                        </a>
                    </div>
                </div>
                @endif

                <div class="ph-empty-actions" style="margin-top:8px;">
                    <a href="{{ route('order.history') }}" class="ph-empty-btn"><i class="bi bi-clock-history"></i> Lihat Riwayat</a>
                    <a href="{{ route('shop.index') }}" class="ph-empty-btn ghost"><i class="bi bi-bag"></i> Belanja Lagi</a>
                </div>

                <p class="cart-summary-note" style="justify-content:center; margin-top:16px;">
                    <i class="bi bi-shield-lock"></i> Halaman ini hanya bisa diakses dari perangkat pemesan.
                </p>
            </div>
        </div>
    </section>

    @if ($order->butuhUpload())
    <script>
        function suSalinCek() {
            var el = document.getElementById('su-cek-link');
            var txt = el ? el.textContent.trim() : '';
            var done = function () {
                if (typeof window.phToast === 'function') window.phToast('Simpan link ini untuk unggah file & unduh hasil.', 'Link disalin', 'bi-clipboard-check');
                else if (typeof Swal !== 'undefined') Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:2400, title:'Link disalin' });
            };
            if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done).catch(done);
            else { var r=document.createRange(); r.selectNode(el); window.getSelection().removeAllRanges(); window.getSelection().addRange(r); try{document.execCommand('copy');}catch(e){} done(); }
        }
    </script>
    @endif
</div>
