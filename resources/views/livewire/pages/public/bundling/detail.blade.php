<main class="main">
    @include('partials.media-produk-style')
    <style>
        /* Gaya disalin dari halaman detail produk satuan supaya kedua halaman
           benar-benar seragam. Sengaja inline di blade, bukan di
           public-custom-styles.css: berkas di public/build/ tidak ikut git pull
           sehingga gaya bisa tertinggal di server. */

        /* ===== Kartu deskripsi ===== */
        .pd-desc-card { border:1px solid var(--ph-line); border-radius:18px; padding:20px 22px;
            background:linear-gradient(180deg, #fffdfa 0%, #fff 60%); }
        .pd-desc-head { display:flex; align-items:center; gap:9px;
            font-family:'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight:800; font-size:1rem;
            color:var(--ph-ink); margin:0 0 12px; }
        .pd-desc-head i { color:var(--ph-orange); font-size:1.05rem; }
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


        /* ===================================================================
           Warna halaman = aksen paket (KartuPaket): warna kategori produk
           pertama di dalam paket yang bukan abu netral. Harga dan tombol beli
           tetap jingga merek, sama dengan halaman produk.
           =================================================================== */
        .pd-section .pd-media {
            background:
                radial-gradient(70% 90% at 100% 0%, color-mix(in srgb, var(--c) 16%, transparent) 0%, transparent 62%),
                radial-gradient(60% 80% at 0% 100%, color-mix(in srgb, var(--c) 9%, transparent) 0%, transparent 60%),
                #fff;
            border-color: color-mix(in srgb, var(--c) 18%, #eceff4);
            box-shadow: 0 20px 48px color-mix(in srgb, var(--c) 12%, transparent);
        }
        .pd-section .pd-media img { mix-blend-mode: multiply; }
        .pd-section .pd-badge {
            animation: none; transform: none; background: #f26522;
            box-shadow: 0 6px 16px rgba(242, 101, 34, .28);
        }
        .pd-badge i.bi { display: block; line-height: 1; }
        .pd-badge i.bi::before { display: block; line-height: 1; }

        /* Gutter vertikal Bootstrap .g-lg-5 memberi .pd-row margin atas -48px,
           lebih besar dari jarak atas .pd-section (40px, CSS publik beku):
           isinya naik 4px MENIMPA tepi bawah kartu judul. Dinolkan, lalu jarak
           atas section diatur ulang supaya ada celah rapi ±22px. */
        @media (min-width: 992px) {
            .pd-section { padding-top: 18px; }
            .pd-section .pd-row { margin-top: 0; }
        }

        /* Tanpa gambar: tumpukan ubin besar produk isi paket, masing-masing
           berwarna kategorinya — isi paket langsung terbaca dari "gambarnya",
           bukan teks alt mentah di pojok kotak kosong. */
        .pd-tumpuk { display: none; position: relative; align-items: center; justify-content: center; padding-left: 22px; }
        /* display:flex ditulis di sini, bukan hanya di aturan layar lebar: di HP
           .pd-media bukan kotak lentur, dan tumpukannya menempel ke atas lalu
           menabrak lencana. */
        .pd-media.is-kosong { display: flex; align-items: center; justify-content: center; min-height: 300px; }
        .pd-media.is-kosong .pd-tumpuk { display: flex; }
        .pd-tumpuk > span {
            width: 96px; height: 96px; margin-left: -22px; border-radius: 30px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(140deg, var(--p), color-mix(in srgb, var(--p) 60%, #fff));
            color: #fff; font-size: 2.4rem;
            box-shadow: 0 0 0 5px #fff, 0 22px 40px -18px color-mix(in srgb, var(--p) 85%, transparent);
            transform: rotate(var(--r, 0deg)); transition: transform .35s ease;
        }
        .pd-media:hover .pd-tumpuk > span { transform: rotate(0deg) translateY(-4px); }
        .pd-tumpuk i.bi, .pd-tumpuk i.bi::before { display: block; line-height: 1; }

        /* Label paket di atas nama — bentuk yang sama dengan label kategori
           di halaman produk. */
        .pd-kat {
            display: inline-flex; align-items: center; gap: 8px;
            background: color-mix(in srgb, var(--c) 11%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 24%, #fff);
            color: var(--c); text-decoration: none;
            border-radius: 999px; padding: 6px 14px 6px 8px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .8rem; line-height: 1;
            transition: background .2s ease, border-color .2s ease;
        }
        a.pd-kat:hover { color: var(--c); background: color-mix(in srgb, var(--c) 18%, #fff); }
        .pd-kat i.bi {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--c); color: #fff; font-size: .72rem; line-height: 1;
        }
        .pd-kat i.bi::before { display: block; line-height: 1; }

        /* Nama paket tidak dicetak dua kali di layar lebar (sudah tampil besar
           di kartu judul); di layar sempit tetap ada sebagai pengingat. */
        .pd-title { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif !important; letter-spacing: -.025em; }
        @media (min-width: 992px) {
            .pd-col-info .pd-title { display: none; }
            .pd-col-info .pd-kat { margin-bottom: 14px; }
        }
        @media (max-width: 991.98px) { .pd-col-info .pd-title { margin-top: 12px; } }

        .pd-price-now, .pd-sub { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif !important; }
        .pd-price-now { letter-spacing: -.03em; font-variant-numeric: lining-nums tabular-nums; }
        .pd-sub i.bi { color: var(--c); display: block; line-height: 1; }
        .pd-sub i.bi::before { display: block; line-height: 1; }

        .pd-catatan { display: flex; flex-wrap: wrap; gap: 8px; margin: -4px 0 18px; }
        .pd-catatan span {
            display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 99px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-size: .78rem; font-weight: 600;
        }
        .pd-catatan .is-kode { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }
        .pd-catatan .is-jadwal { background: #f4f5f8; color: #4b5563; }
        .pd-catatan i.bi, .pd-catatan i.bi::before { display: block; line-height: 1; }

        /* ===== Isi paket: satu kartu per produk, berwarna kategorinya =====
           Bahasa kartu Cara Pesan — ubin ikon, sapuan pojok — dan tiap kartu
           membawa ke halaman produknya, supaya pembeli bisa memeriksa isi
           paket satu per satu. */
        .pd-isi-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 22px; }
        .pd-isi-kartu {
            position: relative; overflow: hidden; display: flex; align-items: center; gap: 12px; min-width: 0;
            padding: 14px 14px 14px 14px; border: 1px solid #eceff4; border-radius: 16px; background: #fff;
            text-decoration: none; color: inherit;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .pd-isi-kartu::before {
            content: ""; position: absolute; top: -34px; right: -34px;
            width: 90px; height: 90px; border-radius: 50%;
            background: color-mix(in srgb, var(--p) 11%, transparent);
            transition: transform .3s ease; pointer-events: none;
        }
        .pd-isi-kartu:hover {
            color: inherit; transform: translateY(-2px);
            border-color: color-mix(in srgb, var(--p) 38%, #fff);
            box-shadow: 0 12px 24px -16px color-mix(in srgb, var(--p) 80%, transparent);
        }
        .pd-isi-kartu:hover::before { transform: scale(1.35); }
        .pd-isi-kartu > * { position: relative; }
        .pd-isi-ic {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--p) 12%, #fff); color: var(--p); font-size: 1.15rem;
        }
        .pd-isi-ic i.bi, .pd-isi-ic i.bi::before { display: block; line-height: 1; }
        .pd-isi-teks { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .pd-isi-kat { font-size: .62rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: var(--p); }
        .pd-isi-nama {
            font-weight: 700; font-size: .92rem; line-height: 1.3; color: #1c1f26;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .pd-isi-dur {
            flex: 0 0 auto; height: 26px; padding: 0 10px; border-radius: 99px;
            display: inline-flex; align-items: center;
            background: color-mix(in srgb, var(--p) 10%, #fff); color: color-mix(in srgb, var(--p) 80%, #111827);
            font-size: .74rem; font-weight: 800; white-space: nowrap;
        }

        /* --- Kartu jaminan: warna per butir seperti Cara Pesan --- */
        .pd-feature { position: relative; overflow: hidden; border-color: #eceff4 !important; }
        .pd-feature::before {
            content: ""; position: absolute; top: -34px; right: -34px;
            width: 92px; height: 92px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .3s ease; pointer-events: none;
        }
        .pd-feature:hover::before { transform: scale(1.35); }
        .pd-feature:hover {
            border-color: color-mix(in srgb, var(--c) 35%, #fff) !important;
            box-shadow: 0 12px 26px color-mix(in srgb, var(--c) 16%, transparent) !important;
        }
        .pd-feature > * { position: relative; z-index: 1; }
        .pd-feature-ic { background: color-mix(in srgb, var(--c) 12%, #fff) !important; color: var(--c) !important; box-shadow: none !important; }
        .pd-feature-ic i.bi, .pd-feature-ic i.bi::before { display: block; line-height: 1; }

        /* ===== Paket lainnya — kartu rk-* yang sama dengan "Produk Lainnya" ===== */
        .rk-deret { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 18px; }
        .rk-kartu {
            position: relative; display: flex; flex-direction: column; min-width: 0;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; overflow: hidden;
            text-decoration: none; color: inherit; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .rk-kartu:hover {
            transform: translateY(-4px); color: inherit;
            border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .rk-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 10; overflow: hidden;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .rk-media::before {
            content: ""; position: absolute; top: -42px; right: -42px;
            width: 124px; height: 124px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .35s ease;
        }
        .rk-kartu:hover .rk-media::before { transform: scale(1.3); }
        .rk-media img { position: relative; max-width: 64%; max-height: 72%; object-fit: contain; mix-blend-mode: multiply; }
        .rk-tumpuk { display: none; position: relative; align-items: center; justify-content: center; padding-left: 10px; }
        .rk-media.is-kosong .rk-tumpuk { display: flex; }
        .rk-tumpuk > span {
            width: 46px; height: 46px; margin-left: -10px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(140deg, var(--p), color-mix(in srgb, var(--p) 60%, #fff));
            color: #fff; font-size: 1.15rem;
            box-shadow: 0 0 0 3px #fff, 0 10px 20px -10px color-mix(in srgb, var(--p) 85%, transparent);
            transform: rotate(var(--r, 0deg)); transition: transform .3s ease;
        }
        .rk-kartu:hover .rk-tumpuk > span { transform: rotate(0deg) translateY(-3px); }
        .rk-kat {
            position: absolute; top: 10px; right: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px 0 8px; border-radius: 99px;
            background: rgba(255, 255, 255, .92); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-size: .7rem; font-weight: 700; white-space: nowrap;
        }
        .rk-diskon {
            position: absolute; top: 10px; left: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 4px; height: 26px; padding: 0 9px; border-radius: 99px;
            background: rgba(255, 255, 255, .95); border: 1px solid #fecdd3; color: #e11d48; font-size: .72rem; font-weight: 800;
        }
        .rk-isi { display: flex; flex-direction: column; flex: 1 1 auto; gap: 4px; padding: 14px 16px 16px; }
        .rk-jenis { font-size: .64rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--c); }
        .rk-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-height: 2.7em; font-weight: 700; font-size: .98rem; line-height: 1.35; letter-spacing: -.01em; color: #1c1f26;
        }
        .rk-kaki { display: flex; align-items: flex-end; justify-content: space-between; gap: 10px; margin-top: auto; padding-top: 12px; }
        .rk-harga { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
        .rk-harga s { font-size: .74rem; color: #9aa2ae; }
        .rk-harga b { font-weight: 800; font-size: 1.02rem; color: #1c1f26; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .rk-harga small { margin-left: 2px; font-size: .72rem; font-weight: 600; color: #9aa2ae; }
        .rk-panah {
            flex: 0 0 auto; width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); font-size: .9rem;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .rk-kartu:hover .rk-panah { background: var(--c); color: #fff; transform: rotate(45deg); }
        .rk-kat i.bi, .rk-diskon i.bi, .rk-panah i.bi, .rk-tumpuk i.bi { line-height: 1; }
        .rk-kat i.bi::before, .rk-diskon i.bi::before, .rk-panah i.bi::before, .rk-tumpuk i.bi::before { display: block; line-height: 1; }
        @media (max-width: 1199.98px) {
            .rk-deret { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .rk-kartu:nth-child(n+9) { display: none; }
        }
        @media (max-width: 991.98px) {
            .rk-deret { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
            .rk-kartu:nth-child(n+9) { display: flex; }
            .rk-kartu:nth-child(n+10) { display: none; }
        }
        @media (max-width: 767.98px) {
            .rk-deret {
                grid-template-columns: none; grid-auto-flow: column; grid-auto-columns: 72%;
                overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; gap: 14px; padding-bottom: 4px;
            }
            .rk-deret::-webkit-scrollbar { display: none; }
            .rk-kartu, .rk-kartu:nth-child(n+9), .rk-kartu:nth-child(n+10) { display: flex; scroll-snap-align: start; }
            .rk-kartu:hover { transform: none; }
        }
        @media (max-width: 575.98px) {
            .pd-isi-grid { grid-template-columns: minmax(0, 1fr); }
            .pd-tumpuk > span { width: 76px; height: 76px; border-radius: 24px; font-size: 1.9rem; margin-left: -18px; }
            .pd-media.is-kosong { min-height: 240px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pd-feature::before, .pd-kat, .pd-isi-kartu, .pd-isi-kartu::before, .pd-tumpuk > span { transition: none; }
            .rk-kartu, .rk-media::before, .rk-tumpuk > span, .rk-panah { transition: none; }
        }
    </style>

    <div class="page-title ph-page-title" style="--c: {{ $kartu['warna'] }}">
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

    {{-- Data halaman dari App\Support\KartuPaket — aturan yang sama dengan
         kartu di daftar paket, jadi warna, harga, dan isi tidak mungkin berbeda. --}}
    <section class="pd-section" style="--c: {{ $kartu['warna'] }}">
        <div class="container">
            <div class="row g-4 g-lg-5 pd-row">
                {{-- Media --}}
                <div class="col-lg-6 pd-col-media">
                    <div class="pd-media {{ $kartu['gambar'] ? '' : 'is-kosong' }}">
                        @if ($kartu['diskon'])
                            <span class="pd-badge"><i class="bi bi-tag-fill"></i> Hemat {{ ltrim($kartu['diskon'], '-') }}</span>
                        @endif
                        @if ($kartu['gambar'])
                            {{-- alt kosong: nama paket tercetak besar tepat di atasnya. --}}
                            <img src="{{ $kartu['gambar'] }}" alt="" onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                        @endif
                        <span class="pd-tumpuk" aria-hidden="true">
                            @foreach ($kartu['tumpuk'] as $t)
                                <span style="--p: {{ $t['warna'] }}; --r: {{ $t['putar'] }}deg"><i class="bi {{ $t['ikon'] }}"></i></span>
                            @endforeach
                        </span>
                    </div>
                </div>

                {{-- Kartu jaminan: dikeluarkan dari kolom media agar bisa
                     ditempatkan SETELAH deskripsi pada layar lebar. --}}
                <div class="col-lg-6 pd-col-trust">
                    <div class="pd-features">
                        <div class="pd-feature" style="--c: #2563eb">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Selama masa aktif paket</small>
                            </span>
                        </div>
                        <div class="pd-feature" style="--c: #16a34a">
                            <span class="pd-feature-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="pd-feature-txt">
                                <b>Dukungan Cepat</b>
                                <small>Bantuan &amp; respons via WhatsApp</small>
                            </span>
                        </div>
                        <div class="pd-feature" style="--c: #7c3aed">
                            <span class="pd-feature-ic"><i class="bi bi-shield-lock-fill"></i></span>
                            <span class="pd-feature-txt">
                                <b>Pembayaran Aman</b>
                                <small>Transfer Bank &amp; QRIS</small>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @php $desk = \App\Support\DeskripsiProduk::blok($paket->deskripsi); @endphp
                @if ($desk)
                    <div class="col-lg-6 pd-col-desc">
                        <div class="pd-desc-card">
                            <h3 class="pd-desc-head"><i class="bi bi-card-text"></i> Deskripsi Paket</h3>
                            @include('partials.deskripsi-rapi', ['blok' => $desk])
                        </div>
                    </div>
                @endif

                {{-- Info --}}
                <div class="col-lg-6 pd-col-info">
                    <a class="pd-kat" href="{{ route('bundling.product-bundlings') }}">
                        <i class="bi bi-box2-heart-fill"></i> Paket Bundling · {{ $kartu['jumlahIsi'] }} produk
                    </a>
                    <h2 class="pd-title">{{ $paket->nama_paket }}</h2>

                    <div class="pd-price">
                        <span class="pd-price-now">Rp {{ number_format($kartu['harga'], 0, ',', '.') }}</span>
                        {{-- Yang dicoret harga awal, yaitu harga bila produknya
                             dibeli satuan (lihat App\Support\HargaPaket). --}}
                        @if ($kartu['hargaAsli'])
                            <span class="pd-price-old">Rp {{ number_format($kartu['hargaAsli'], 0, ',', '.') }}</span>
                        @endif
                        <span class="pd-price-unit">/ paket</span>
                        @if ($kartu['hemat'])
                            <span class="pd-price-save">{{ $kartu['hemat'] }}</span>
                        @endif
                    </div>

                    @if ($kartu['kode'] || $kartu['jadwal'])
                        <div class="pd-catatan">
                            @if ($kartu['kode'])
                                {{-- Promo berkode tidak berlaku sendiri: kodenya WAJIB terlihat. --}}
                                <span class="is-kode"><i class="bi bi-ticket-perforated-fill"></i> Harga promo aktif setelah kode <b>{{ $kartu['kode'] }}</b> dipakai di keranjang</span>
                            @endif
                            @if ($kartu['jadwal'])
                                <span class="is-jadwal"><i class="bi bi-clock"></i> {{ $kartu['jadwal'] }}</span>
                            @endif
                        </div>
                    @endif

                    {{-- Isi paket menggantikan pilihan durasi di produk satuan:
                         paket sudah tetap, durasinya ditentukan admin. Tiap
                         kartu membawa ke halaman produknya. --}}
                    @if (count($kartu['isi']))
                        <h4 class="pd-sub"><i class="bi bi-box-seam"></i> Termasuk dalam paket</h4>
                        <div class="pd-isi-grid">
                            @foreach ($kartu['isi'] as $p)
                                <a href="{{ $p['url'] }}" class="pd-isi-kartu" style="--p: {{ $p['warna'] }}" title="Lihat {{ $p['nama'] }}">
                                    <span class="pd-isi-ic"><i class="bi {{ $p['ikon'] }}"></i></span>
                                    <span class="pd-isi-teks">
                                        <span class="pd-isi-kat">{{ $p['kategori'] ?? 'Produk' }}</span>
                                        <span class="pd-isi-nama">{{ $p['nama'] }}</span>
                                    </span>
                                    @if ($p['durasi'])
                                        <span class="pd-isi-dur">{{ $p['durasi'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
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

    {{-- Paket lainnya — kartu yang sama dengan "Produk Lainnya" di halaman produk. --}}
    @if (count($lainnya))
        <section class="rel-section">
            <div class="container">
                <x-kepala-bagian
                    ikon="bi-grid-3x3-gap-fill"
                    kicker="Paket Lainnya"
                    judul="Mungkin Anda juga suka"
                    :tautan-url="route('bundling.product-bundlings')"
                    tautan-teks="Lihat Semua Paket" />
                <div class="rk-deret">
                    @foreach ($lainnya as $k)
                        <a href="{{ $k['url'] }}" class="rk-kartu" style="--c: {{ $k['warna'] }}" wire:key="rk-{{ $k['id'] }}">
                            <span class="rk-media {{ $k['gambar'] ? '' : 'is-kosong' }}">
                                @if ($k['gambar'])
                                    <img src="{{ $k['gambar'] }}" alt="" loading="lazy" onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                @endif
                                <span class="rk-tumpuk" aria-hidden="true">
                                    @foreach ($k['tumpuk'] as $t)
                                        <span style="--p: {{ $t['warna'] }}; --r: {{ $t['putar'] }}deg"><i class="bi {{ $t['ikon'] }}"></i></span>
                                    @endforeach
                                </span>
                                <span class="rk-kat"><i class="bi bi-box-seam"></i>{{ $k['jumlahIsi'] }} produk</span>
                                @if ($k['diskon'])
                                    <span class="rk-diskon"><i class="bi bi-tag-fill"></i>{{ $k['diskon'] }}</span>
                                @endif
                            </span>
                            <span class="rk-isi">
                                <span class="rk-jenis">Paket Bundling</span>
                                <span class="rk-nama">{{ $k['nama'] }}</span>
                                <span class="rk-kaki">
                                    <span class="rk-harga">
                                        @if ($k['hargaAsli'])
                                            <s>Rp{{ number_format($k['hargaAsli'], 0, ',', '.') }}</s>
                                        @endif
                                        <b>Rp{{ number_format($k['harga'], 0, ',', '.') }}<small>/paket</small></b>
                                    </span>
                                    <span class="rk-panah" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
