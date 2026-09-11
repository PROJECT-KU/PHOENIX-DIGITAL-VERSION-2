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

        /* ===== Papan saring =====
           Kategori sebagai chip berwarna (bahasa yang sama dengan Kategori
           Populer di beranda), tipe akun sebagai tombol segmen, urutan sebagai
           satu kotak pilih berikon. Sebelumnya dua kotak pilih polos yang di HP
           terpotong jadi "Semua Tipe Akı" dan "Urutkan: Terbar". */
        .sf-papan {
            display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px;
            padding: 16px 18px; margin-bottom: 24px;
        }
        .sf-kategori { display: flex; gap: 8px; flex-wrap: wrap; min-width: 0; }
        .sf-chip {
            --c: #f26522;
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px;
            height: 40px; padding: 0 15px 0 5px; border-radius: 99px;
            border: 1px solid #eceff4; background: #fff; color: #4b5563;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: .84rem;
            white-space: nowrap; cursor: pointer;
            transition: border-color .18s ease, background .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .sf-chip-ic {
            flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); font-size: .85rem;
            transition: background .18s ease, color .18s ease;
        }
        .sf-chip:hover { border-color: color-mix(in srgb, var(--c) 40%, #eceff4); color: #1c1f26; }
        .sf-chip.is-aktif {
            background: color-mix(in srgb, var(--c) 9%, #fff);
            border-color: color-mix(in srgb, var(--c) 45%, #fff);
            color: color-mix(in srgb, var(--c) 75%, #111827);
            box-shadow: 0 8px 18px -12px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .sf-chip.is-aktif .sf-chip-ic { background: var(--c); color: #fff; }

        .sf-bawah {
            display: flex; align-items: center; gap: 10px 14px; flex-wrap: wrap;
            padding-top: 14px; border-top: 1px dashed #eceff4;
        }
        .sf-tipe { display: inline-flex; gap: 2px; padding: 3px; border-radius: 12px; background: #f4f5f8; }
        .sf-seg {
            height: 34px; padding: 0 16px; border: 0; border-radius: 9px; background: transparent;
            color: #6b7280; font-weight: 700; font-size: .82rem; cursor: pointer;
            transition: background .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .sf-seg:hover { color: #1c1f26; }
        .sf-seg.is-aktif { background: #fff; color: #1c1f26; box-shadow: 0 1px 3px rgba(15, 23, 42, .14); }
        .sf-urut {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; margin: 0; padding: 0 8px 0 12px;
            border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; color: #6b7280; font-size: .82rem;
        }
        .sf-urut select {
            border: 0; background: transparent; font: inherit; font-weight: 700; color: #1c1f26;
            padding: 0 2px; outline: none; cursor: pointer;
        }
        .sf-urut:focus-within { border-color: #f26522; box-shadow: 0 0 0 3px rgba(242, 101, 34, .14); }
        .sf-info { margin-left: auto; display: inline-flex; align-items: center; gap: 12px; font-size: .85rem; color: #6b7280; }
        .sf-info b { color: #1c1f26; font-weight: 800; }
        .sf-reset {
            display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px;
            border-radius: 99px; border: 1px solid #fecdd3; background: #fff1f2; color: #e11d48;
            font-weight: 700; font-size: .78rem; cursor: pointer; transition: background .15s ease, color .15s ease;
        }
        .sf-reset:hover { background: #e11d48; border-color: #e11d48; color: #fff; }
        .sf-cari { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: .84rem; color: #6b7280; }
        .sf-cari-chip {
            display: inline-flex; align-items: center; gap: 8px; max-width: 100%; height: 32px; padding: 0 5px 0 12px;
            border-radius: 99px; background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; font-weight: 700;
        }
        .sf-cari-chip span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sf-cari-chip button {
            flex: 0 0 auto; width: 22px; height: 22px; border-radius: 50%; border: 0; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            background: rgba(194, 65, 12, .12); color: #c2410c; font-size: .8rem;
        }
        .sf-cari-chip button:hover { background: #c2410c; color: #fff; }

        /* ===== Kartu produk =====
           Kelas sk-*, bukan fs-* bawaan: aturan .fs-card di
           public-custom-styles.css server beku, dan dipakai bersama kartu flash
           sale di beranda. Bahasanya sama dengan kartu "Produk Lainnya" di
           halaman produk: warna kategori, sapuan pojok seperti Cara Pesan. */
        .sk-kartu {
            position: relative; display: flex; flex-direction: column; height: 100%; min-width: 0;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; overflow: hidden;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .sk-kartu:hover {
            transform: translateY(-4px);
            border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .sk-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 11; overflow: hidden; text-decoration: none;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .sk-media::before {
            content: ""; position: absolute; top: -44px; right: -44px;
            width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .35s ease;
        }
        .sk-kartu:hover .sk-media::before { transform: scale(1.3); }
        .sk-media img {
            position: relative; max-width: 70%; max-height: 66%; object-fit: contain;
            mix-blend-mode: multiply; transition: transform .35s ease;
        }
        .sk-kartu:hover .sk-media img { transform: scale(1.05); }
        .sk-cadangan {
            display: none; position: relative; align-items: center; justify-content: center;
            width: 64px; height: 64px; border-radius: 20px;
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff));
            color: #fff; font-size: 1.65rem;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
            transition: transform .35s ease;
        }
        .sk-media.is-kosong .sk-cadangan { display: flex; }
        .sk-kartu:hover .sk-cadangan { transform: scale(1.06) rotate(-4deg); }

        /* Kategori di pojok KANAN atas, diskon di pojok KIRI atas: berseberangan,
           jadi tak mungkin bertemu di lebar berapa pun. */
        .shop-kat {
            position: absolute; top: 10px; right: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px;
            max-width: calc(100% - 20px); height: 26px; padding: 0 10px 0 8px; border-radius: 99px;
            background: rgba(255, 255, 255, .92); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 700; font-size: .7rem; white-space: nowrap;
        }
        .shop-kat-teks { overflow: hidden; text-overflow: ellipsis; }
        .sk-media:has(.sk-diskon) .shop-kat { max-width: calc(100% - 108px); }
        .sk-diskon {
            position: absolute; top: 10px; left: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 4px; height: 26px; padding: 0 9px;
            border-radius: 99px; background: rgba(255, 255, 255, .95); border: 1px solid #fecdd3;
            color: #e11d48; font-size: .72rem; font-weight: 800; white-space: nowrap;
        }
        .sk-diskon.is-flash { background: #fff1f2; }

        .sk-isi { display: flex; flex-direction: column; flex: 1 1 auto; gap: 4px; padding: 14px 16px 16px; }
        .sk-jenis { font-size: .66rem; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--c); }
        .sk-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-height: 2.7em; text-decoration: none;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
            font-size: 1rem; line-height: 1.35; letter-spacing: -.01em; color: #1c1f26;
        }
        .sk-nama:hover { color: var(--c); }
        .sk-harga { display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 6px; margin-top: 6px; line-height: 1.3; }
        .sk-harga b { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; font-size: 1.12rem; color: #1c1f26; }
        .sk-harga small { font-size: .74rem; font-weight: 600; color: #9aa2ae; }
        .sk-harga s { font-size: .78rem; color: #9aa2ae; }

        /* Keranjang + Lihat dalam SATU baris, juga di HP. Sebelumnya di kartu
           selebar 170px keduanya bertumpuk dan setiap kartu jadi setinggi layar. */
        .sk-aksi { display: flex; gap: 8px; margin-top: auto; padding-top: 14px; }
        .sk-beli {
            flex: 1 1 auto; min-width: 0; height: 42px; padding: 0 12px; border: 0; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .86rem; cursor: pointer;
            box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .7);
            transition: filter .16s ease, transform .16s ease;
        }
        .sk-beli > span { display: inline-flex; align-items: center; gap: 7px; min-width: 0; white-space: nowrap; overflow: hidden; }
        .sk-beli:hover:not(:disabled) { filter: brightness(1.05); transform: translateY(-1px); }
        .sk-beli:disabled { background: #f1f3f6; color: #9aa2ae; box-shadow: none; cursor: not-allowed; }
        .sk-lihat {
            flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); font-size: .95rem;
            text-decoration: none; transition: background .2s ease, color .2s ease;
        }
        .sk-lihat:hover { background: var(--c); color: #fff; }
        .sk-pendek { display: none; }
        .sk-kartu.is-jeda .sk-media > img, .sk-kartu.is-jeda .sk-cadangan { filter: grayscale(.6); opacity: .75; }

        /* Glif sendirian di wadah penengah → block; glif sebaris teks → cukup line-height. */
        .sf-chip-ic i.bi, .sk-lihat i.bi, .sk-cadangan i.bi, .sf-cari-chip button i.bi { display: block; line-height: 1; }
        .sf-chip-ic i.bi::before, .sk-lihat i.bi::before, .sk-cadangan i.bi::before, .sf-cari-chip button i.bi::before { display: block; line-height: 1; }
        .shop-kat i.bi, .sk-diskon i.bi, .sk-beli i.bi, .sf-urut i.bi, .sf-reset i.bi { line-height: 1; }
        .shop-kat i.bi::before, .sk-diskon i.bi::before, .sk-beli i.bi::before, .sf-urut i.bi::before, .sf-reset i.bi::before { display: block; line-height: 1; }

        @media (max-width: 767.98px) {
            .sf-papan { padding: 14px; border-radius: 16px; }
            /* Chip kategori menggulir mendatar selebar papan (grid kolom eksplisit
               di atas mencegahnya mendorong papan keluar layar). */
            .sf-kategori {
                flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none;
                margin: 0 -14px; padding: 0 14px 2px;
            }
            .sf-kategori::-webkit-scrollbar { display: none; }
            .sf-tipe { flex: 1 1 100%; }
            .sf-seg { flex: 1 1 0; }
            .sf-urut { flex: 1 1 auto; min-width: 0; }
            .sf-urut select { flex: 1 1 auto; min-width: 0; }
            .sf-info { margin-left: 0; }
        }
        @media (max-width: 575.98px) {
            .sk-kartu { border-radius: 16px; }
            .sk-isi { padding: 12px 12px 13px; }
            .sk-nama { font-size: .9rem; }
            .sk-harga b { font-size: 1rem; }
            .sk-aksi { gap: 6px; padding-top: 12px; }
            .sk-beli { height: 38px; padding: 0 8px; border-radius: 10px; font-size: .78rem; }
            .sk-lihat { width: 38px; height: 38px; border-radius: 10px; }
            .shop-kat { width: 26px; height: 26px; padding: 0; justify-content: center; border-radius: 50%; top: 8px; right: 8px; }
            .shop-kat-teks { display: none; }
            .sk-diskon { height: 23px; padding: 0 7px; font-size: .66rem; top: 8px; left: 8px; }
            .sk-cadangan { width: 54px; height: 54px; border-radius: 17px; font-size: 1.4rem; }
            .sk-media { aspect-ratio: 4 / 3; }
            /* Tombol selebar ~95px: label panjang terpotong jadi "Tidak Ters". */
            .sk-panjang { display: none; }
            .sk-pendek { display: inline; }
        }
        @media (prefers-reduced-motion: reduce) {
            .sk-kartu, .sk-media::before, .sk-media img, .sk-cadangan, .sk-lihat, .sk-beli, .sf-chip, .sf-chip-ic { transition: none; }
        }

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
                    {{-- Pencarian aktif kini tampil sebagai chip di papan saring. --}}
                </div>

                <!-- Category Product List Section -->
                <section style="padding-top: 0;" id="category-product-list" class="category-product-list section">
                    <div class="container">
                        {{-- Papan saring. Kategori yang datang dari beranda tampil sebagai
                             chip AKTIF dan bisa diganti atau dilepas di sini — penyaring
                             yang bekerja diam-diam sama saja dengan halaman yang
                             kehilangan barang. --}}
                        <div class="sf-papan">
                            @if (count($daftarKategori))
                                <div class="sf-kategori" role="group" aria-label="Kategori">
                                    <button type="button" class="sf-chip {{ $kategoriAktif === '' ? 'is-aktif' : '' }}"
                                        wire:click="pilihKategori('')" data-kategori="" aria-pressed="{{ $kategoriAktif === '' ? 'true' : 'false' }}">
                                        <span class="sf-chip-ic"><i class="bi bi-grid-fill"></i></span>Semua
                                    </button>
                                    @foreach ($daftarKategori as $kk)
                                        <button type="button" class="sf-chip {{ $kategoriAktif === $kk['kunci'] ? 'is-aktif' : '' }}" style="--c: {{ $kk['warna'] }}"
                                            wire:click="pilihKategori('{{ $kk['kunci'] }}')"
                                            data-kategori="{{ $kk['kunci'] }}" aria-pressed="{{ $kategoriAktif === $kk['kunci'] ? 'true' : 'false' }}">
                                            <span class="sf-chip-ic"><i class="bi {{ $kk['ikon'] }}"></i></span>{{ $kk['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="sf-bawah">
                                @if (count($categories))
                                    {{-- Isinya sharing/private — TIPE AKUN, bukan kategori. --}}
                                    <div class="sf-tipe" role="group" aria-label="Tipe akun">
                                        <button type="button" class="sf-seg {{ $tipe ? '' : 'is-aktif' }}" wire:click="$set('tipe', '')">Semua</button>
                                        @foreach ($categories as $c)
                                            <button type="button" class="sf-seg {{ $tipe === $c ? 'is-aktif' : '' }}" wire:click="$set('tipe', '{{ $c }}')">{{ ucfirst($c) }}</button>
                                        @endforeach
                                    </div>
                                @endif
                                <label class="sf-urut">
                                    <i class="bi bi-sort-down"></i>
                                    <select wire:model.live="sortBy" aria-label="Urutkan produk">
                                        <option value="">Terbaru</option>
                                        <option value="termurah">Harga termurah</option>
                                        <option value="termahal">Harga termahal</option>
                                        <option value="nama">Nama A–Z</option>
                                        <option value="terlama">Terlama</option>
                                    </select>
                                </label>
                                <div class="sf-info">
                                    <span><b>{{ $products->total() }}</b> produk</span>
                                    @if ($adaFilter)
                                        <button type="button" wire:click="resetFilters" class="sf-reset"><i class="bi bi-x-circle"></i> Reset</button>
                                    @endif
                                </div>
                            </div>

                            @if ($search)
                                <div class="sf-cari">
                                    Hasil pencarian
                                    <span class="sf-cari-chip">
                                        <span>{{ $search }}</span>
                                        <button type="button" wire:click="clearSearch" aria-label="Hapus pencarian"><i class="bi bi-x"></i></button>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="row g-3 g-lg-4">
                            @forelse ($kartu as $k)
                                <div class="col-6 col-md-4 col-lg-3" wire:key="product-{{ $k['id'] }}">
                                    {{-- Warna kartu = warna KATEGORI produknya (taksonomi yang sama
                                         dengan beranda). Produk tanpa kategori memakai warna merek,
                                         bukan abu-abu yang terbaca seperti produk nonaktif. --}}
                                    <article class="sk-kartu {{ $k['dijeda'] ? 'is-jeda' : '' }}" style="--c: {{ $k['warna'] }}">
                                        <a href="{{ $k['url'] }}" class="sk-media {{ $k['gambar'] ? '' : 'is-kosong' }}" tabindex="-1" aria-hidden="true">
                                            @if ($k['gambar'])
                                                <img loading="lazy" src="{{ $k['gambar'] }}" alt=""
                                                    onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                            @endif
                                            <span class="sk-cadangan"><i class="bi {{ $k['ikon'] }}"></i></span>
                                            @if ($k['kategori'])
                                                <span class="shop-kat" title="{{ $k['kategori'] }}"><i class="bi {{ $k['ikon'] }}"></i><span class="shop-kat-teks">{{ $k['kategori'] }}</span></span>
                                            @endif
                                            @if ($k['diskon'])
                                                <span class="sk-diskon {{ $k['flash'] ? 'is-flash' : '' }}"><i class="bi {{ $k['flash'] ? 'bi-lightning-charge-fill' : 'bi-tag-fill' }}"></i>{{ $k['diskon'] }}</span>
                                            @endif
                                        </a>

                                        <div class="sk-isi">
                                            <span class="sk-jenis">{{ $k['jenis'] }}</span>
                                            <a href="{{ $k['url'] }}" class="sk-nama">{{ $k['nama'] }}</a>
                                            <div class="sk-harga">
                                                @if ($k['mulai'])
                                                    <small>Mulai</small>
                                                @endif
                                                <b>Rp{{ number_format($k['harga'], 0, ',', '.') }}</b>
                                                <small>{{ $k['satuan'] }}</small>
                                                @if ($k['hargaAsli'])
                                                    <s>Rp{{ number_format($k['hargaAsli'], 0, ',', '.') }}</s>
                                                @endif
                                            </div>

                                            <div class="sk-aksi">
                                                <button type="button" class="sk-beli" wire:click="openDuration('{{ $k['id'] }}')"
                                                    wire:loading.attr="disabled" wire:target="openDuration('{{ $k['id'] }}')" @disabled($k['dijeda'])>
                                                    <span wire:loading.remove wire:target="openDuration('{{ $k['id'] }}')">
                                                        @if ($k['dijeda'])
                                                            {{-- Dikatakan di kartu supaya pembeli tak mengklik sia-sia --}}
                                                            <i class="bi bi-pause-circle"></i><span class="sk-panjang">Tidak Tersedia</span><span class="sk-pendek">Tutup</span>
                                                        @elseif ($k['jasa'])
                                                            {{-- Jasa: harga ditentukan di halaman produk (unggah file / add-on) --}}
                                                            <i class="bi bi-sliders"></i><span class="sk-panjang">Atur Pesanan</span><span class="sk-pendek">Atur</span>
                                                        @else
                                                            <i class="bi bi-cart-plus"></i> Keranjang
                                                        @endif
                                                    </span>
                                                    <span wire:loading wire:target="openDuration('{{ $k['id'] }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                                </button>
                                                <a href="{{ $k['url'] }}" class="sk-lihat" aria-label="Lihat detail {{ $k['nama'] }}" title="Lihat detail"><i class="bi bi-arrow-up-right"></i></a>
                                            </div>
                                        </div>
                                    </article>
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
                                        @elseif ($adaFilter)
                                            <h3 class="shp-empty-title">Tidak ada yang cocok</h3>
                                            <p class="shp-empty-sub">Filter yang dipilih belum menemukan produk apa pun.
                                                Coba longgarkan filternya, ya.</p>
                                            <button type="button" class="shp-empty-btn" wire:click="resetFilters">
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
