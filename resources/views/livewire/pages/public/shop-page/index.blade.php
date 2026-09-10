<main class="main">
    @include('partials.media-produk-style')
    {{-- Gaya empty state sengaja ditaruh di blade, bukan di
         resources/css/public-custom-styles.css, meniru halaman Bundling.
         Alasannya: public/build/ masuk .gitignore, jadi CSS di stylesheet Vite
         hanya sampai ke server lewat rsync terpisah. Ketika markup terkirim
         (git pull) tapi CSS-nya tidak, SVG kehilangan aturan width dan
         memenuhi layar. Menaruhnya di sini membuat markup & gaya selalu
         terkirim bersama. --}}
    <style>
        /* Ditulis inline: public/build tidak ikut terdeploy ke server. */
        .fs-btn-cart:disabled { opacity: .55; cursor: not-allowed; filter: grayscale(.4); }

        /* --- IKON DI TENGAH ---
           Glif Bootstrap Icons membawa tinggi baris bawaannya sendiri, jadi di
           dalam tombol ia duduk sedikit di bawah pusat. Pada tombol Reset
           selisihnya mencapai 10 piksel — cukup untuk membuat tombolnya
           terlihat salah susun. */
        .shop-reset i.bi, .fs-btn-cart i.bi, .shp-empty-btn i.bi,
        .shop-aktif-chip i.bi, .fs-btn-view i.bi { display: block; line-height: 1; }
        .shop-reset i.bi::before, .fs-btn-cart i.bi::before, .shp-empty-btn i.bi::before,
        .shop-aktif-chip i.bi::before, .fs-btn-view i.bi::before { display: block; line-height: 1; }
        .shop-reset, .fs-btn-cart, .shp-empty-btn {
            display: inline-flex !important; align-items: center; justify-content: center;
        }
        /* Pembungkus isi tombol ikut dijadikan baris lentur.
           
           display:block pada glif hanya benar bila glif itu SENDIRIAN di dalam
           wadah yang menengahkan. Di tombol ini glif duduk sebaris dengan
           teksnya di dalam <span> biasa — dijadikan block, ia turun ke barisnya
           sendiri dan tombolnya jadi dua baris dengan ikon menggantung di atas.
           Pembungkusnya dijadikan inline-flex supaya glif tetap sebaris DAN
           tetap tertengahkan. */
        .fs-btn-cart > span, .shop-reset > span, .shp-empty-btn > span {
            display: inline-flex; align-items: center; gap: 7px;
        }

        /* --- Penyaring kategori yang sedang berlaku --- */
        .shop-aktif {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .shop-aktif-label {
            font-size: .74rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; color: #9aa2ae;
        }
        .shop-aktif-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: #fff3ea; border: 1px solid #f8d9c2; color: #d9531a;
            border-radius: 999px; padding: 6px 8px 6px 15px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .86rem;
        }
        .shop-aktif-chip button {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50%; border: 0; cursor: pointer;
            background: rgba(217, 83, 26, .12); color: #d9531a; font-size: .8rem;
            transition: background .18s ease;
        }
        .shop-aktif-chip button:hover { background: #d9531a; color: #fff; }

        /* --- Kartu produk berwarna menurut kategorinya ---
           Perlakuan yang sama dengan kartu Cara Pesan dan Kategori Populer:
           sapuan warna samar di pojok, bingkai dan bayangan senada saat
           disentuh. Satu bahasa untuk seluruh situs. */
        .shop-kartu { position: relative; overflow: hidden; }
        .shop-kartu::before {
            content: ""; position: absolute; top: -40px; right: -40px; z-index: 0;
            width: 110px; height: 110px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 11%, transparent);
            transition: transform .3s ease; pointer-events: none;
        }
        .shop-kartu:hover::before { transform: scale(1.3); }
        .shop-kartu:hover {
            border-color: color-mix(in srgb, var(--c) 34%, #fff) !important;
            box-shadow: 0 12px 28px color-mix(in srgb, var(--c) 16%, transparent) !important;
        }
        .shop-kartu > * { position: relative; z-index: 1; }

        /* Label kategori di pojok kartu. Kecil dan tenang: ia keterangan, dan
           keterangan yang menyaingi nama produk membuat keduanya sulit dibaca. */
        .fs-card-media { position: relative; }
        .shop-kat {
            position: absolute; top: 10px; right: 10px; z-index: 2;
            display: inline-flex; align-items: center; gap: 5px;
            background: color-mix(in srgb, var(--c) 12%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 24%, #fff);
            color: var(--c); border-radius: 999px; padding: 4px 10px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .68rem; line-height: 1.3; white-space: nowrap;
            /* 46%, bukan separuh: lencana promo di seberangnya memakan sekitar
               separuh lebar kartu, jadi tepat setengah-setengah membuat
               keduanya bersentuhan persis di tengah. */
            max-width: 46%; overflow: hidden; text-overflow: ellipsis;
        }
        .shop-kat i.bi { display: block; line-height: 1; font-size: .72rem; }
        .shop-kat i.bi::before { display: block; line-height: 1; }

        /* Di kartu yang menyempit, label kategori menyusut jadi IKON SAJA.

           Lencana promo memakai white-space: nowrap, jadi "Diskon s.d. 30%"
           memanjang melintasi kartu selebar 200px dan menyentuh label di
           seberangnya — berseberangan saja tidak cukup. Diperiksa: di 900px dan
           390px keduanya bertabrakan pada DUA BELAS dari dua belas kartu.

           Sebagai ikon bulat ia tetap mengerjakan tugasnya — warnanya memberi
           tahu kelompok produknya — tanpa memakan lebar. Nama kategorinya tetap
           terbaca lewat atribut title dan pembaca layar. */
        @media (max-width: 1319.98px) {
            .shop-kat-teks { display: none; }
            .shop-kat {
                max-width: none; padding: 0; border-radius: 50%;
                width: 28px; height: 28px; justify-content: center;
            }
            .shop-kat i.bi { font-size: .8rem; }
        }
        @media (max-width: 575.98px) {
            .shop-kat { width: 24px; height: 24px; top: 8px; right: 8px; }
            .shop-kat i.bi { font-size: .7rem; }
        }

        /* --- Bilah saring jadi satu papan --- */
        /* Sebelumnya dua kotak pilih dan satu angka mengambang di atas kisi
           produk tanpa bidang sendiri, jadi ia terbaca sebagai baris pertama
           kisi — bukan sebagai alat. */
        .shop-filter {
            background: #fff; border: 1px solid #eceff4; border-radius: 14px;
            padding: 12px 16px; margin-bottom: 22px;
        }
        .shop-filter-count { color: #6b7280; }
        .shop-filter-count b { color: #1c1f26; font-weight: 800; }

        .shp-empty { text-align: center; padding: 30px 16px 20px; max-width: 480px; margin: 0 auto; }
        .shp-empty-art { margin-bottom: 6px; }
        .shp-empty-art svg { width: 260px; max-width: 82%; height: auto; overflow: visible; }
        .shp-empty-title { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; color: #23272f; font-size: 1.35rem; margin: 4px 0 6px; }
        .shp-empty-sub { color: #6b7280; font-size: .95rem; line-height: 1.6; margin: 0 auto 18px; max-width: 400px; }
        .shp-empty-btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #fba919, #f26522); color: #fff; font-weight: 700; padding: .7rem 1.4rem; border-radius: 12px; box-shadow: 0 8px 20px rgba(242, 101, 34, .28); text-decoration: none; border: 0; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
        .shp-empty-btn:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.04); box-shadow: 0 10px 24px rgba(242, 101, 34, .36); }

        .se-bag { animation: se-bob 3.4s ease-in-out infinite; }
        .se-lens { animation: se-search 4.2s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
        .se-glow, .se-shadow, .se-spark { transform-box: fill-box; transform-origin: center; }
        .se-glow { animation: se-glowpulse 3.4s ease-in-out infinite; }
        .se-shadow { animation: se-shadowpulse 3.4s ease-in-out infinite; }
        .se-spark { animation: se-twinkle 2s ease-in-out infinite; }
        .se-spark.s2 { animation-delay: .5s; }
        .se-spark.s3 { animation-delay: 1s; }
        .se-spark.s4 { animation-delay: 1.4s; }

        @keyframes se-bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes se-search { 0%, 100% { transform: translate(0, 0) rotate(0deg); } 30% { transform: translate(-14px, -8px) rotate(-8deg); } 65% { transform: translate(8px, 4px) rotate(5deg); } }
        @keyframes se-glowpulse { 0%, 100% { opacity: .45; transform: scale(1); } 50% { opacity: .75; transform: scale(1.08); } }
        @keyframes se-shadowpulse { 0%, 100% { opacity: .16; transform: scaleX(1); } 50% { opacity: .09; transform: scaleX(.82); } }
        @keyframes se-twinkle { 0%, 100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }

        @media (prefers-reduced-motion: reduce) {
            .se-bag, .se-lens, .se-glow, .se-shadow, .se-spark { animation: none !important; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-bag-fill"></i> Katalog</span>
                <h1>Shop</h1>
                <p>Pilihan akun premium &amp; tools AI untuk riset dan produktivitas Anda.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Beranda</a></li>
                    <li class="current">Shop</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <!-- list product -->
    <section style="padding-top: 20px;">
        <div class="container">
            <section style="padding-top: 0;" id="category-header" class="category-header section">
                <div class="container">
                    @if ($search)
                        <div class="mb-4 alert alert-info" role="alert">
                            Menampilkan hasil pencarian untuk: <strong>{{ $search }}</strong>
                            <button wire:click="$set('search', '')" class="btn-close float-end"
                                aria-label="Clear search"></button>
                        </div>
                    @endif
                </div>

                <!-- Category Product List Section -->
                <section style="padding-top: 0;" id="category-product-list" class="category-product-list section">
                    <div class="container">
                        {{-- Filter & urutkan (opsional) --}}
                        {{-- Kategori yang datang dari beranda DITAMPILKAN dan
                             bisa dilepas.

                             Sebelumnya penyaringnya bekerja diam-diam: daftar
                             produk menyusut tanpa satu pun keterangan kenapa,
                             dan satu-satunya jalan keluar adalah menyunting
                             alamat sendiri. Penyaring yang tidak terlihat sama
                             saja dengan halaman yang kehilangan barang. --}}
                        @if ($kategori)
                            <div class="shop-aktif">
                                <span class="shop-aktif-label">Kategori</span>
                                <span class="shop-aktif-chip">
                                    {{ App\Support\KategoriBeranda::label($kategori) }}
                                    <button type="button" wire:click="clearKategori" aria-label="Lepas penyaring kategori">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </span>
                            </div>
                        @endif

                        <div class="shop-filter">
                            <div class="shop-filter-controls">
                                @if (count($categories))
                                    {{-- Isinya sharing/private — TIPE AKUN, bukan
                                         kategori. Menamainya "Semua Kategori"
                                         membuat pengunjung mengira ini penyaring
                                         yang sama dengan kartu kategori di
                                         beranda, padahal keduanya menyaring hal
                                         yang berbeda. --}}
                                    <select wire:model.live="tipe" class="shop-select">
                                        <option value="">Semua Tipe Akun</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c }}">{{ $c }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <select wire:model.live="sortBy" class="shop-select">
                                    <option value="">Urutkan: Terbaru</option>
                                    <option value="termurah">Harga: Termurah</option>
                                    <option value="termahal">Harga: Termahal</option>
                                    <option value="nama">Nama: A–Z</option>
                                    <option value="terlama">Terlama</option>
                                </select>
                                @if ($tipe || $sortBy)
                                    <button type="button" wire:click="resetFilters" class="shop-reset"><i class="bi bi-x-circle"></i> Reset</button>
                                @endif
                            </div>
                            <div class="shop-filter-count"><b>{{ $products->total() }}</b> produk</div>
                        </div>

                        <div class="row g-3 g-lg-4">
                            @forelse ($products as $item)
                                @php
                                    $bestDiscount = $this->getBestDiscount($item->id);
                                    $isFlash = $bestDiscount && ($bestDiscount['promo']->tipe_promo ?? null) === 'flash_sale';
                                    // Produk JASA harga per bulannya 0. Ada DUA cara tagihnya:
                                    // per pengecekan (paket 'kali') dan per halaman. Sebelumnya
                                    // hanya yang per pengecekan diambil, sehingga jasa per halaman
                                    // tampil "Rp 0".
                                    $isJasa = (bool) $item->butuh_file;
                                    $perHalaman = $isJasa && $item->jasaPerHalaman();
                                    $satuanHarga = $perHalaman ? '/halaman' : ($isJasa ? '/cek' : '/bln');
                                    $originalPrice = $perHalaman
                                        ? (int) $item->hargaPerHalaman()
                                        : ($isJasa ? (int) ($item->hargaSekali() ?? 0) : (int) $item->harga_perbulan);
                                    if ($bestDiscount) {
                                        if ($bestDiscount['type'] === 'persen') {
                                            $discountedPrice = (int) round(
                                                $originalPrice - ($originalPrice * $bestDiscount['value']) / 100,
                                            );
                                        } else {
                                            $discountedPrice = (int) max(0, $originalPrice - $bestDiscount['value']);
                                        }
                                    } else {
                                        $discountedPrice = $originalPrice;
                                    }

                                    // Kategori produk, untuk mewarnai kartunya.
                                    $kat = App\Support\KategoriBeranda::untukProduk($item->nama_akun);
                                @endphp
                                <div class="col-6 col-md-4 col-lg-3" wire:key="product-{{ $item->id }}">
                                    {{-- Warna kartu mengikuti KATEGORI produknya,
                                         memakai taksonomi yang sama dengan kartu
                                         kategori di beranda. Warna di sini bukan
                                         hiasan: ia memberi tahu produk ini masuk
                                         kelompok apa tanpa menambah satu baris
                                         teks pun, dan membuat katalog panjang
                                         bisa disusuri lewat bentuk warna saja.

                                         Produk yang tidak cocok kategori mana pun
                                         memakai warna merek — bukan abu-abu, yang
                                         akan terbaca seperti produk nonaktif. --}}
                                    <div class="fs-card shop-kartu" style="--c: {{ $kat['warna'] ?? '#f26522' }}">
                                        <div class="fs-card-media">
                                            @if ($item->image)
                                                <img loading="lazy" src="{{ asset('storage/img/Product/' . $item->image) }}"
                                                    alt="{{ $item->nama_akun }}">
                                            @else
                                                <img loading="lazy" src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                    alt="{{ $item->nama_akun }}">
                                            @endif

                                            {{-- Label kategori di pojok KANAN ATAS.

                                                 Lencana promo menempati pojok KIRI atas
                                                 (top:12 left:12 di public-custom-styles.css), jadi
                                                 keduanya berseberangan dan tidak mungkin bertemu.

                                                 Sempat ditaruh di tepi bawah area gambar; di
                                                 ponsel area itu menyusut sampai 100-an piksel dan
                                                 keduanya kembali bertabrakan — dua belas dari dua
                                                 belas kartu. Berseberangan aman di lebar berapa
                                                 pun, dan pojok atas juga yang pertama dilihat. --}}
                                            @if ($kat)
                                                <span class="shop-kat" title="{{ $kat['label'] }}">
                                                    <i class="bi {{ $kat['ikon'] }}"></i>
                                                    <span class="shop-kat-teks">{{ $kat['label'] }}</span>
                                                </span>
                                            @endif

                                            @if ($bestDiscount)
                                                @if ($isFlash)
                                                    <span class="fs-badge fs-badge-flash"><i
                                                            class="bi bi-lightning-charge-fill"></i>
                                                        @if ($bestDiscount['type'] === 'persen')
                                                            Diskon s.d. {{ number_format($bestDiscount['value'], 0) }}%
                                                        @else
                                                            Diskon s.d. Rp{{ number_format($bestDiscount['value'], 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="fs-badge">
                                                        @if ($bestDiscount['type'] === 'persen')
                                                            @if ($bestDiscount['member_value'] != $bestDiscount['non_member_value'])
                                                                Diskon {{ number_format($bestDiscount['non_member_value'], 0) }}–{{ number_format($bestDiscount['member_value'], 0) }}%
                                                            @else
                                                                Diskon {{ number_format($bestDiscount['value'], 0) }}%
                                                            @endif
                                                        @else
                                                            Diskon Rp{{ number_format($bestDiscount['value'], 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="fs-card-body">
                                            <a href="{{ route('shop.detail-product', $item->id) }}"
                                                class="fs-name">{{ $item->nama_akun }}</a>

                                            <div class="fs-price">
                                                @if ($isJasa)
                                                    <small class="text-muted me-1">Mulai</small>
                                                @endif
                                                <span class="fs-price-sale">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                                @if ($discountedPrice < $originalPrice)
                                                    <span class="fs-price-orig">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                                @endif
                                                <small>{{ $satuanHarga }}</small>
                                            </div>

                                            @php $dijeda = \App\Support\JedaLayanan::produkDijeda($item); @endphp
                                            <div class="fs-actions">
                                                <button type="button" wire:click="openDuration('{{ $item->id }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openDuration('{{ $item->id }}')" class="fs-btn-cart"
                                                    @disabled($dijeda)>
                                                    <span wire:loading.remove
                                                        wire:target="openDuration('{{ $item->id }}')">
                                                        @if ($dijeda)
                                                            {{-- Dikatakan di kartu supaya pembeli tak mengklik sia-sia --}}
                                                            <i class="bi bi-pause-circle"></i> Tidak Tersedia
                                                        @elseif ($isJasa)
                                                            {{-- Jasa: harga ditentukan di halaman produk (unggah file / add-on) --}}
                                                            <i class="bi bi-sliders"></i> Atur Pesanan
                                                        @else
                                                            <i class="bi bi-cart-plus"></i> Keranjang
                                                        @endif
                                                    </span>
                                                    <span wire:loading wire:target="openDuration('{{ $item->id }}')"><span
                                                            class="spinner-border spinner-border-sm"></span></span>
                                                </button>
                                                <a href="{{ route('shop.detail-product', $item->id) }}"
                                                    class="fs-btn-view">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    {{-- Empty state beranimasi — seragam dengan halaman Bundling,
                                         menggantikan kotak peringatan datar yang terasa seperti error. --}}
                                    <div class="shp-empty">
                                        <div class="shp-empty-art">
                                            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                                                aria-label="Produk tidak ditemukan">
                                                <defs>
                                                    <radialGradient id="seGlow" cx="50%" cy="50%" r="50%">
                                                        <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                                        <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                                    </radialGradient>
                                                    <linearGradient id="seBag" x1="0" y1="0" x2="0" y2="1">
                                                        <stop offset="0%" stop-color="#fdc069" />
                                                        <stop offset="100%" stop-color="#f4772b" />
                                                    </linearGradient>
                                                    <linearGradient id="seBagFold" x1="0" y1="0" x2="1" y2="0">
                                                        <stop offset="0%" stop-color="#f7a23e" />
                                                        <stop offset="100%" stop-color="#e15a18" />
                                                    </linearGradient>
                                                </defs>

                                                {{-- Cahaya, bayangan & kilau memakai koordinat yang sama persis
                                                     dengan halaman Bundling agar skala & iramanya seragam. --}}
                                                <ellipse class="se-glow" cx="120" cy="112" rx="80" ry="80" fill="url(#seGlow)" />
                                                <ellipse class="se-shadow" cx="120" cy="182" rx="60" ry="8" fill="#e15a18" />

                                                <g transform="translate(46,74)"><path class="se-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                                                <g transform="translate(198,92)"><path class="se-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                                <g transform="translate(190,142)"><path class="se-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                                                <g transform="translate(52,146)"><path class="se-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                                                {{-- Kantong belanja: dilebarkan & ditinggikan supaya mengisi kanvas
                                                     setara kotak hadiah di Bundling (x 66–174, y 30–176). --}}
                                                <g class="se-bag">
                                                    <path d="M72,78 L168,78 L158,170 Q157,176 151,176 L89,176 Q83,176 82,170 Z" fill="url(#seBag)" />
                                                    <path d="M72,78 L168,78 L166,98 L74,98 Z" fill="url(#seBagFold)" opacity=".55" />
                                                    <path d="M97,78 L97,60 Q97,40 120,40 Q143,40 143,60 L143,78" fill="none"
                                                        stroke="#ffe9d0" stroke-width="8" stroke-linecap="round" />
                                                    <path d="M72,78 L168,78 L158,170 Q157,176 151,176 L89,176 Q83,176 82,170 Z" fill="none"
                                                        stroke="#ffffff" stroke-opacity=".45" stroke-width="1.5" />
                                                </g>

                                                {{-- Kaca pembesar digeser ke pojok kanan bawah supaya tidak
                                                     menindih badan kantong (sebelumnya terlihat berdesakan). --}}
                                                <g class="se-lens">
                                                    <circle cx="150" cy="140" r="25" fill="#fff8ef" fill-opacity=".92" stroke="#f26522" stroke-width="5" />
                                                    <path d="M168,158 L182,172" stroke="#e15a18" stroke-width="8" stroke-linecap="round" />
                                                    <path class="se-shine" d="M140,130 Q146,124 154,126" stroke="#ffffff" stroke-width="4"
                                                        stroke-linecap="round" fill="none" opacity=".85" />
                                                </g>
                                            </svg>
                                        </div>

                                        @if ($search)
                                            <h3 class="shp-empty-title">Produk tidak ditemukan</h3>
                                            <p class="shp-empty-sub">Tidak ada produk yang cocok dengan pencarian
                                                <b>"{{ $search }}"</b>. Coba kata kunci lain, ya.</p>
                                            <button type="button" class="shp-empty-btn" wire:click="$set('search', '')">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                                            </button>
                                        @elseif ($tipe || $sortBy)
                                            <h3 class="shp-empty-title">Tidak ada yang cocok</h3>
                                            <p class="shp-empty-sub">Filter yang dipilih belum menemukan produk apa pun.
                                                Coba longgarkan filternya, ya.</p>
                                            <button type="button" class="shp-empty-btn"
                                                wire:click="$set('tipe', ''); $set('sortBy', '')">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                                            </button>
                                        @else
                                            <h3 class="shp-empty-title">Belum ada produk</h3>
                                            <p class="shp-empty-sub">Koleksi produk sedang disiapkan. Sementara itu,
                                                lihat paket bundling kami, yuk!</p>
                                            <a href="{{ url('/bundling/product') }}" class="shp-empty-btn">
                                                <i class="bi bi-box2-heart"></i> Lihat Paket Bundling
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        @if ($products->hasPages())
                            <div class="mt-5 ph-pagination">
                                {{ $products->links('pagination.ph') }}
                            </div>
                        @endif
                    </div>
                </section>
            </section>
        </div>
    </section>
    <!-- end list product -->

    {{-- ===== Modal Pilih Durasi (seragam dengan Flash Sale) ===== --}}
    @if ($showDurationModal)
        <div class="fs-modal-overlay" wire:key="shop-dur-modal" wire:click.self="closeDuration">
            <div class="fs-modal">
                <button type="button" class="fs-modal-close" wire:click="closeDuration" aria-label="Tutup"><i
                        class="bi bi-x-lg"></i></button>

                <div class="fs-modal-head">
                    <div class="fs-modal-thumb">
                        @if ($pickProductImage)
                            <img src="{{ asset('storage/img/Product/' . $pickProductImage) }}"
                                alt="{{ $pickProductName }}">
                        @else
                            <i class="bi bi-box-seam"></i>
                        @endif
                    </div>
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow">
                            @if ($pickIsFlash)
                                <i class="bi bi-lightning-charge-fill"></i> Flash Sale
                            @else
                                <i class="bi bi-box-seam"></i> Pilih Paket
                            @endif
                        </span>
                        <h4>{{ $pickProductName }}</h4>
                        <p>Pilih durasi langganan</p>
                    </div>
                </div>

                <div class="fs-modal-options">
                    @foreach ($pickPackages as $p)
                        @php $active = ($pickType === $p['duration_type'] && (int) $pickValue === (int) $p['duration_value']); @endphp
                        <button type="button" class="fs-opt {{ $active ? 'is-active' : '' }}"
                            wire:click="selectPackage('{{ $p['duration_type'] }}', {{ $p['duration_value'] }})">
                            <span class="fs-opt-radio"></span>
                            <span class="fs-opt-info">
                                <span class="fs-opt-label">{{ $p['label'] }}</span>
                                @if (!empty($p['savings']) && $p['savings'] > 0)
                                    <span class="fs-opt-save">Hemat Rp{{ number_format($p['savings'], 0, ',', '.') }}</span>
                                @endif
                            </span>
                            <span class="fs-opt-price">
                                @if (($p['discounted'] ?? $p['price']) < $p['price'])
                                    <span class="fs-opt-orig">Rp{{ number_format($p['price'], 0, ',', '.') }}</span>
                                @endif
                                <span class="fs-opt-now">Rp{{ number_format($p['discounted'] ?? $p['price'], 0, ',', '.') }}</span>
                            </span>
                        </button>
                    @endforeach

                    {{-- Durasi custom (bila produk punya harga per bulan) --}}
                    @if ($pickPerBulan > 0)
                        @php
                            $cp = $this->customPricing();
                            $customBase = $cp['base'];
                            $customDisc = $cp['discounted'];
                            $customSave = $cp['savings'];
                        @endphp
                        <div class="fs-opt fs-opt-custom {{ $pickIsCustom ? 'is-active' : '' }}">
                            <span class="fs-opt-radio" wire:click="chooseCustom"></span>
                            <span class="fs-opt-info" wire:click="chooseCustom">
                                <span class="fs-opt-label">Durasi lain</span>
                                <span class="fs-opt-sub">
                                    @if ($cp['matched'])
                                        Sesuai paket {{ $pickCustomMonths }} bulan
                                    @else
                                        Rp{{ number_format($pickPerBulan, 0, ',', '.') }}/bulan
                                    @endif
                                </span>
                            </span>
                            <div class="fs-stepper">
                                <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                <span class="fs-stepper-val">{{ $pickCustomMonths }} bln</span>
                                <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                            </div>
                        </div>
                        @if ($pickIsCustom)
                            <div class="fs-custom-total">
                                <span class="fs-custom-total-left">
                                    Total {{ $pickCustomMonths }} bulan
                                    @if ($customSave > 0)
                                        <span class="fs-opt-save">Hemat Rp{{ number_format($customSave, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="fs-custom-total-price">
                                    @if ($customDisc < $customBase)
                                        <span class="fs-opt-orig">Rp{{ number_format($customBase, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="fs-opt-now">Rp{{ number_format($customDisc, 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

                <button type="button" class="fs-modal-add" wire:click="confirmAddToCart" wire:loading.attr="disabled"
                    wire:target="confirmAddToCart">
                    <span wire:loading.remove wire:target="confirmAddToCart"><i class="bi bi-cart-plus"></i> Tambah ke
                        Keranjang</span>
                    <span wire:loading wire:target="confirmAddToCart"><span
                            class="spinner-border spinner-border-sm"></span> Memproses…</span>
                </button>
            </div>
        </div>
    @endif
</main>
