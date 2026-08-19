<main class="main">
    @include('partials.media-produk-style')
    <style>
        /* Gaya disalin dari halaman detail produk satuan supaya kedua halaman
           benar-benar seragam. Sengaja inline di blade, bukan di
           public-custom-styles.css: berkas di public/build/ tidak ikut git pull
           sehingga gaya bisa tertinggal di server. */
        .rel-thumb { background: var(--ph-grad-soft); }
        .rel-thumb img { object-fit: contain !important; padding: 12px; mix-blend-mode: multiply; }

        /* ===== Daftar poin deskripsi (pecahan dari deskripsi ber-"✅") ===== */
        .pd-feat { list-style:none; margin:0 0 22px; padding:0;
            display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:9px 18px; }
        .pd-feat li { display:flex; align-items:flex-start; gap:9px;
            font-size:.92rem; line-height:1.55; color:var(--ph-ink); }
        .pd-feat li i { color:#16a34a; font-size:1rem; line-height:1.45; flex:0 0 auto; }
        .pd-feat li span { min-width:0; }
        @media (max-width: 767.98px) { .pd-feat { grid-template-columns:1fr; gap:8px; } }

        .pd-feat li i {
            transform-origin: center;
            animation: pdCheckPulse 2.4s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * 200ms);
            will-change: transform;
        }
        @keyframes pdCheckPulse {
            0%, 100% { transform:scale(1);    filter:drop-shadow(0 0 0 rgba(22, 163, 74, 0)); }
            50%      { transform:scale(1.16); filter:drop-shadow(0 0 5px rgba(22, 163, 74, .45)); }
        }
        @media (prefers-reduced-motion: reduce) {
            .pd-feat li i { animation:none; transform:none; filter:none; }
        }

        /* ===== Kartu deskripsi ===== */
        .pd-desc-card { border:1px solid var(--ph-line); border-radius:18px; padding:20px 22px;
            background:linear-gradient(180deg, #fffdfa 0%, #fff 60%); }
        .pd-desc-head { display:flex; align-items:center; gap:9px;
            font-family:'Poppins', sans-serif; font-weight:800; font-size:1rem;
            color:var(--ph-ink); margin:0 0 12px; }
        .pd-desc-head i { color:var(--ph-orange); font-size:1.05rem; }
        .pd-desc-card .pd-desc { margin-bottom:12px; }
        .pd-desc-card .pd-desc.is-lead { color:var(--ph-ink); font-weight:600; }
        .pd-desc-card .pd-desc:last-child { margin-bottom:0; }
        .pd-desc-card .pd-feat { margin-bottom:0; padding-top:4px; }
        .pd-desc-notes { margin-top:14px; padding-top:12px; border-top:1px dashed var(--ph-line); display:grid; gap:8px; }
        .pd-desc-note { display:flex; gap:9px; align-items:flex-start; margin:0; font-size:.88rem; line-height:1.55; color:var(--ph-muted); }
        .pd-desc-note span:first-child { flex:0 0 auto; font-size:1rem; line-height:1.4; }
        @media (max-width: 575.98px) { .pd-desc-card { padding:16px 16px; border-radius:15px; } }

        /* ===== Tata letak kolom, sama persis dengan detail produk satuan:
           gambar di ATAS, lalu deskripsi, lalu kartu jaminan; kolom kanan
           (harga s.d. Wishlist) mengisi kolom kedua penuh. ===== */
        @media (min-width: 992px) {
            .pd-row {
                display:grid;
                grid-template-columns:1fr 1fr;
                grid-template-areas:"media info" "desc info" "trust info";
                grid-template-rows:minmax(260px, 1fr) auto auto;
                align-items:stretch;
                column-gap:3rem; row-gap:22px;
                margin-left:0; margin-right:0;
            }
            .pd-row > .pd-col-media { grid-area:media; display:flex; min-height:0; }
            .pd-row > .pd-col-desc  { grid-area:desc; }
            .pd-row > .pd-col-trust { grid-area:trust; align-self:end; }
            .pd-row > .pd-col-info  { grid-area:info; }
            .pd-row > [class*="col-"] { padding-left:0; padding-right:0; width:auto; max-width:none; margin-top:0; }

            .pd-col-media .pd-media { flex:1; min-height:0; display:flex; }
            .pd-col-media .pd-media img { width:100%; height:100%; aspect-ratio:auto; object-fit:contain; }
        }
        @media (max-width: 991.98px) {
            .pd-row > .pd-col-media { order:1; }
            .pd-row > .pd-col-trust { order:2; }
            .pd-row > .pd-col-info  { order:3; }
            .pd-row > .pd-col-desc  { order:4; }
        }

        /* ===== Isi paket: satu-satunya bagian yang tidak ada di produk satuan,
           menggantikan pilihan durasi/paket harga. ===== */
        .pkd-isi { list-style:none; margin:0 0 22px; padding:0; display:grid; gap:10px; }
        .pkd-isi li { display:flex; align-items:center; justify-content:space-between; gap:12px;
            padding:11px 14px; border:1px solid var(--ph-line); border-radius:12px;
            background:linear-gradient(180deg, #fffdfa 0%, #fff 60%); }
        .pkd-isi-nama { display:flex; align-items:center; gap:9px; font-size:.94rem; min-width:0; }
        .pkd-isi-nama i { color:#16a34a; flex:0 0 auto; }
        .pkd-dur { flex:0 0 auto; font-size:.8rem; font-weight:700; padding:3px 10px;
            border-radius:999px; color:var(--ph-orange); background:var(--ph-grad-soft); }
    </style>

    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box2-heart"></i> Detail Paket</span>
                <h1>{{ $paket->nama_paket }}</h1>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li><a href="{{ route('bundling.product-bundlings') }}">Paket Bundling</a></li>
                    <li class="current">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="pd-section">
        <div class="container">
            <div class="row g-4 g-lg-5 pd-row">
                {{-- Media --}}
                <div class="col-lg-6 pd-col-media">
                    <div class="pd-media">
                        @if ($hp['potongan'] > 0)
                            <span class="pd-badge is-flash">
                                <i class="bi bi-lightning-charge-fill"></i>
                                Hemat Rp{{ number_format($hp['potongan'], 0, ',', '.') }}
                            </span>
                        @else
                            <span class="pd-badge">Paket Hemat</span>
                        @endif

                        @if ($paket->gambar)
                            <img src="{{ asset('storage/img/ProductBundlings/' . basename($paket->gambar)) }}"
                                alt="{{ $paket->nama_paket }}">
                        @else
                            <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="{{ $paket->nama_paket }}">
                        @endif
                    </div>
                </div>

                {{-- Kartu jaminan: dikeluarkan dari kolom media agar bisa
                     ditempatkan SETELAH deskripsi pada layar lebar. --}}
                <div class="col-lg-6 pd-col-trust">
                    <div class="pd-features">
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Selama masa aktif paket</small>
                            </span>
                        </div>
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="pd-feature-txt">
                                <b>Dukungan Cepat</b>
                                <small>Bantuan &amp; respons via WhatsApp</small>
                            </span>
                        </div>
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-lock-fill"></i></span>
                            <span class="pd-feature-txt">
                                <b>Pembayaran Aman</b>
                                <small>Transfer Bank &amp; QRIS</small>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @php $desk = \App\Support\DeskripsiProduk::pisah($paket->deskripsi); @endphp
                @if ($desk['paragraf'] || $desk['poin'] || $desk['ekstra'])
                    <div class="col-lg-6 pd-col-desc">
                        <div class="pd-desc-card">
                            <h3 class="pd-desc-head"><i class="bi bi-card-text"></i> Deskripsi Paket</h3>

                            @foreach ($desk['paragraf'] as $i => $par)
                                <p class="pd-desc {{ $i === 0 ? 'is-lead' : '' }}">{{ $par }}</p>
                            @endforeach

                            @if ($desk['poin'])
                                <ul class="pd-feat">
                                    @foreach ($desk['poin'] as $poin)
                                        <li style="--i: {{ $loop->index }}">
                                            <i class="bi bi-check-circle-fill"></i><span>{{ $poin }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($desk['ekstra'])
                                <div class="pd-desc-notes">
                                    @foreach ($desk['ekstra'] as $e)
                                        <p class="pd-desc-note"><span>{{ $e['ikon'] }}</span><span>{{ $e['teks'] }}</span></p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Info --}}
                <div class="col-lg-6 pd-col-info">
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Paket Bundling</span>
                    <h2 class="pd-title">{{ $paket->nama_paket }}</h2>

                    <div class="pd-price">
                        <span class="pd-price-now">Rp {{ number_format($hp['bayar'], 0, ',', '.') }}</span>
                        {{-- Yang dicoret harga awal, yaitu harga bila produknya
                             dibeli satuan (lihat App\Support\HargaPaket). --}}
                        @if ($hp['coret'] > $hp['bayar'])
                            <span class="pd-price-old">Rp {{ number_format($hp['coret'], 0, ',', '.') }}</span>
                        @endif
                        <span class="pd-price-unit">/ paket</span>
                        @if ($hp['potongan'] > 0)
                            <span class="pd-price-save">Hemat Rp {{ number_format($hp['potongan'], 0, ',', '.') }}</span>
                        @endif
                    </div>

                    @if ($hp['butuh_kode'])
                        <p class="jd-hint">Harga promo di atas aktif setelah kode promo dipakai di keranjang.</p>
                    @endif

                    {{-- Isi paket menggantikan pilihan durasi di produk satuan:
                         paket sudah tetap, durasinya ditentukan admin. --}}
                    @if ($isi)
                        <h4 class="pd-sub"><i class="bi bi-box-seam"></i> Termasuk dalam paket</h4>
                        <ul class="pkd-isi">
                            @foreach ($isi as $baris)
                                <li>
                                    <span class="pkd-isi-nama">
                                        <i class="bi bi-check-circle-fill"></i><span>{{ $baris['nama'] }}</span>
                                    </span>
                                    <span class="pkd-dur">{{ $baris['dur_value'] }} {{ $baris['dur_type'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Beli --}}
                    <div class="pd-buy">
                        <button type="button" class="pd-add" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart">
                            <span wire:loading.remove wire:target="addToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                            <span wire:loading wire:target="addToCart"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                        </button>

                        <button type="button" class="pd-wish"
                            x-data="{ saved: false }"
                            x-init="saved = (JSON.parse(localStorage.getItem('ph_wishlist')||'[]')).includes('{{ $paket->id }}')"
                            @click="
                                let w = JSON.parse(localStorage.getItem('ph_wishlist')||'[]');
                                if (w.includes('{{ $paket->id }}')) { w = w.filter(i => i !== '{{ $paket->id }}'); saved = false; }
                                else { w.push('{{ $paket->id }}'); saved = true; }
                                localStorage.setItem('ph_wishlist', JSON.stringify(w));
                                window.dispatchEvent(new Event('ph-wishlist-changed'));
                                if (window.phToast) phToast(saved ? 'Ditambahkan ke wishlist' : 'Dihapus dari wishlist', 'Wishlist', saved ? 'bi-heart-fill' : 'bi-heart');
                            ">
                            <i class="bi" :class="saved ? 'bi-heart-fill' : 'bi-heart'"></i>
                            <span x-text="saved ? 'Tersimpan di Wishlist' : 'Simpan ke Wishlist'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Paket terkait / rekomendasi --}}
    @if ($lainnya->isNotEmpty())
        <section class="rel-section">
            <div class="container">
                <div class="ph-sec-head" style="text-align:center;">
                    <span class="ph-sec-eyebrow"><i class="bi bi-grid-3x3-gap"></i> Paket Lainnya</span>
                    <h2 class="ph-sec-title">Mungkin Anda juga suka</h2>
                </div>
                <div class="rel-grid rel-scroll">
                    @foreach ($lainnya as $rp)
                        @php $hrp = \App\Support\HargaPaket::untuk($rp); @endphp
                        <a href="{{ route('bundling.detail', $rp->id) }}" class="rel-card">
                            <div class="rel-thumb">
                                @if ($rp->gambar)
                                    <img src="{{ asset('storage/img/ProductBundlings/'.basename($rp->gambar)) }}" alt="{{ $rp->nama_paket }}" loading="lazy">
                                @else
                                    <span class="rel-noimg"><i class="bi bi-box2-heart"></i></span>
                                @endif
                            </div>
                            <div class="rel-body">
                                <h3 class="rel-name">{{ $rp->nama_paket }}</h3>
                                <div class="rel-price">Rp {{ number_format($hrp['bayar'], 0, ',', '.') }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
