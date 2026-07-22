<div>
    <style>
        #ph-page-lines { display: none !important; }
        .cke-wrap { max-width: 560px; margin: 0 auto; text-align: center; padding: 8px 0 4px; }
        .cke-ic { width: 78px; height: 78px; border-radius: 50%; margin: 0 auto 16px;
            display: grid; place-items: center; font-size: 2.1rem; color: #b45309;
            background: linear-gradient(135deg, #fff7ed, #ffedd5); border: 1px solid #fde68a; }
        .cke-title { font-family: 'Poppins', sans-serif; font-weight: 800; color: #23272f;
            font-size: 1.5rem; margin: 0 0 8px; }
        .cke-sub { color: #6b7280; font-size: .95rem; line-height: 1.65; margin: 0 auto 6px; max-width: 460px; }
        .cke-order { font-family: 'Courier New', monospace; font-weight: 700; color: #f26522; }
        .cke-info { background: #fff8f1; border: 1px solid #f1e6d8; border-radius: 14px;
            padding: 14px 16px; margin: 18px auto 0; max-width: 460px; text-align: left;
            font-size: .86rem; color: #6b5f52; line-height: 1.6; }
        .cke-info b { color: #92400e; }
        .cke-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 22px; }
        .cke-btn { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: .92rem;
            padding: .72rem 1.4rem; border-radius: 12px; text-decoration: none; border: 0; cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
        .cke-btn.primary { background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            box-shadow: 0 8px 20px rgba(242, 101, 34, .28); }
        .cke-btn.primary:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.04); }
        .cke-btn.ghost { background: #fff; color: #b45309; border: 1px solid #f1e6d8; }
        .cke-btn.ghost:hover { color: #b45309; border-color: #f26522; }
    </style>

    <section class="cart-section">
        <div class="container">
            <div class="cke-wrap">
                <div class="cke-ic"><i class="bi bi-clock-history"></i></div>
                <h3 class="cke-title">Link Pengecekan Sudah Berakhir</h3>
                <p class="cke-sub">
                    Kuota pengecekan untuk pesanan
                    <span class="cke-order">{{ $order->order_number }}</span> sudah habis,
                    dan masa akses link ini telah berakhir.
                </p>

                <div class="cke-info">
                    <b><i class="bi bi-info-circle"></i> Kenapa link ini tidak bisa dibuka lagi?</b><br>
                    Setiap link pengecekan hanya aktif <b>24 jam setelah seluruh kuota terpakai</b>, demi
                    menjaga keamanan &amp; kerahasiaan dokumen Anda.
                    @isset($kadaluarsaAt)
                        Masa akses berakhir pada
                        <b>{{ $kadaluarsaAt->translatedFormat('l, d F Y • H:i') }} WIB</b>.
                    @endisset
                    Jika Anda masih membutuhkan hasilnya atau ingin memesan pengecekan lagi, silakan hubungi kami.
                </div>

                <div class="cke-actions">
                    <a href="{{ url('/') }}" class="cke-btn primary"><i class="bi bi-house-door"></i> Kembali ke Beranda</a>
                    <a href="{{ url('/shop') }}" class="cke-btn ghost"><i class="bi bi-bag"></i> Pesan Pengecekan Lagi</a>
                </div>
            </div>
        </div>
    </section>
</div>
