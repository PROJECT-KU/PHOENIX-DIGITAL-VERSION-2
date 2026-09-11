<main class="main">
    <style>
        /* Ditulis INLINE: public/build masuk .gitignore dan tidak ikut terdeploy.
           Bahasa visual yang sama dengan halaman /shop — papan saring sf-* dan
           kartu berwarna kategori — supaya paket dan produk satuan terbaca
           sebagai satu toko. Kartu paket memakai kelas pb-*, bukan partial
           kartu-paket (partial itu tetap dipakai beranda & etalase flash sale). */

        /* ===== Papan saring (salinan dari /shop) ===== */
        .sf-papan {
            display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px;
            padding: 16px 18px; margin-bottom: 24px;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        }
        .sf-kategori { display: flex; gap: 8px; flex-wrap: wrap; min-width: 0; }
        .sf-chip {
            --c: #f26522;
            flex: 0 0 auto; display: inline-flex; align-items: center; gap: 8px;
            height: 40px; padding: 0 15px 0 5px; border-radius: 99px;
            border: 1px solid #eceff4; background: #fff; color: #4b5563;
            font-family: inherit; font-weight: 700; font-size: .84rem; white-space: nowrap; cursor: pointer;
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
        .sf-urut {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; margin: 0; padding: 0 8px 0 12px;
            border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; color: #6b7280; font-size: .82rem;
        }
        .sf-urut select { border: 0; background: transparent; font: inherit; font-weight: 700; color: #1c1f26; padding: 0 2px; outline: none; cursor: pointer; }
        .sf-urut:focus-within { border-color: #f26522; box-shadow: 0 0 0 3px rgba(242, 101, 34, .14); }
        .sf-info { margin-left: auto; display: inline-flex; align-items: center; gap: 12px; font-size: .85rem; font-weight: 500; color: #6b7280; }
        .sf-info b { color: #1c1f26; font-weight: 800; font-variant-numeric: tabular-nums; }
        .sf-reset {
            display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px;
            border-radius: 99px; border: 1px solid #fecdd3; background: #fff1f2; color: #e11d48;
            font-family: inherit; font-weight: 700; font-size: .78rem; cursor: pointer; transition: background .15s ease, color .15s ease;
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

        /* ===== Kartu paket ===== */
        .pb-kartu {
            position: relative; display: flex; flex-direction: column; height: 100%; min-width: 0;
            background: #fff; border: 1px solid #eceff4; border-radius: 18px; overflow: hidden;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .pb-kartu:hover {
            transform: translateY(-4px);
            border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .pb-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 11; overflow: hidden; text-decoration: none;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .pb-media::before {
            content: ""; position: absolute; top: -44px; right: -44px;
            width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .35s ease;
        }
        .pb-kartu:hover .pb-media::before { transform: scale(1.3); }
        .pb-media img {
            position: relative; max-width: 70%; max-height: 66%; object-fit: contain;
            mix-blend-mode: multiply; transition: transform .35s ease;
        }
        .pb-kartu:hover .pb-media img { transform: scale(1.05); }

        /* Tanpa gambar: tumpukan ubin produk isi paket, masing-masing berwarna
           kategorinya — isi paket langsung terbaca dari "gambarnya". */
        .pb-tumpuk { display: none; position: relative; align-items: center; justify-content: center; padding-left: 12px; }
        .pb-media.is-kosong .pb-tumpuk { display: flex; }
        .pb-tumpuk > span {
            width: 54px; height: 54px; margin-left: -12px; border-radius: 17px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(140deg, var(--p), color-mix(in srgb, var(--p) 60%, #fff));
            color: #fff; font-size: 1.35rem;
            box-shadow: 0 0 0 3px #fff, 0 12px 22px -12px color-mix(in srgb, var(--p) 85%, transparent);
            transform: rotate(var(--r, 0deg)); transition: transform .3s ease;
        }
        .pb-kartu:hover .pb-tumpuk > span { transform: rotate(0deg) translateY(-3px); }

        .pb-jumlah {
            position: absolute; top: 10px; right: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px 0 8px; border-radius: 99px;
            background: rgba(255, 255, 255, .92); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-size: .7rem; font-weight: 700; white-space: nowrap;
        }
        .pb-diskon {
            position: absolute; top: 10px; left: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 4px; height: 26px; padding: 0 9px; border-radius: 99px;
            background: rgba(255, 255, 255, .95); border: 1px solid #fecdd3; color: #e11d48;
            font-size: .72rem; font-weight: 800; white-space: nowrap;
        }

        .pb-isi { display: flex; flex-direction: column; flex: 1 1 auto; gap: 4px; padding: 14px 16px 16px; }
        .pb-label { font-size: .64rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--c); }
        .pb-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-height: 2.7em; text-decoration: none;
            font-weight: 700; font-size: 1.02rem; line-height: 1.35; letter-spacing: -.015em; color: #1c1f26;
        }
        .pb-nama:hover { color: var(--c); }

        /* Isi paket: baris ringkas dengan ubin ikon kecil berwarna kategori. */
        .pb-daftar { list-style: none; padding: 0; margin: 6px 0 0; display: grid; gap: 6px; }
        .pb-daftar li { display: flex; align-items: center; gap: 8px; min-width: 0; font-size: .8rem; color: #374151; }
        .pb-daftar-ic {
            flex: 0 0 auto; width: 22px; height: 22px; border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--p) 13%, #fff); color: var(--p); font-size: .7rem;
        }
        .pb-daftar-nama { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
        .pb-daftar-dur { flex: 0 0 auto; font-size: .72rem; font-weight: 600; color: #9aa2ae; }
        .pb-daftar-lain { padding-left: 30px; font-size: .74rem; font-weight: 600; color: #9aa2ae; }

        .pb-harga { display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 6px; margin-top: 10px; line-height: 1.3; }
        .pb-harga b { font-weight: 800; font-size: 1.16rem; letter-spacing: -.02em; color: #1c1f26; font-variant-numeric: tabular-nums; }
        .pb-rp { font-size: .68em; font-weight: 700; letter-spacing: 0; margin-right: 2px; color: #4b5563; }
        .pb-harga small { font-size: .74rem; font-weight: 600; color: #9aa2ae; }
        .pb-harga s { font-size: .78rem; font-weight: 500; color: #9aa2ae; font-variant-numeric: tabular-nums; }
        .pb-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 6px 8px; margin-top: 4px; font-size: .72rem; }
        .pb-hemat { display: inline-flex; align-items: center; height: 20px; padding: 0 8px; border-radius: 6px; background: #ecfdf5; color: #047857; font-weight: 700; }
        .pb-jadwal { display: inline-flex; align-items: center; gap: 4px; color: #6b7280; font-weight: 600; }
        .pb-kode {
            display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; align-self: flex-start;
            padding: 3px 9px; border-radius: 8px; background: #fff1f2; color: #e11d48; font-size: .74rem; font-weight: 600;
        }
        .pb-kode b { letter-spacing: .04em; }

        .pb-aksi { display: flex; gap: 8px; margin-top: auto; padding-top: 14px; }
        .pb-beli {
            flex: 1 1 auto; min-width: 0; height: 42px; padding: 0 12px; border: 0; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-family: inherit; font-weight: 700; font-size: .86rem; cursor: pointer;
            box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .7);
            transition: filter .16s ease, transform .16s ease;
        }
        .pb-beli > span { display: inline-flex; align-items: center; gap: 7px; min-width: 0; white-space: nowrap; overflow: hidden; }
        .pb-beli:hover:not(:disabled) { filter: brightness(1.05); transform: translateY(-1px); }
        .pb-lihat {
            flex: 0 0 auto; width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); font-size: .95rem;
            text-decoration: none; transition: background .2s ease, color .2s ease;
        }
        .pb-lihat:hover { background: var(--c); color: #fff; }

        /* Glif sendirian di wadah penengah → block; glif sebaris teks → cukup line-height. */
        .sf-chip-ic i.bi, .sf-cari-chip button i.bi, .pb-tumpuk i.bi, .pb-daftar-ic i.bi, .pb-lihat i.bi { display: block; line-height: 1; }
        .sf-chip-ic i.bi::before, .sf-cari-chip button i.bi::before, .pb-tumpuk i.bi::before,
        .pb-daftar-ic i.bi::before, .pb-lihat i.bi::before { display: block; line-height: 1; }
        .sf-urut i.bi, .sf-reset i.bi, .pb-jumlah i.bi, .pb-diskon i.bi, .pb-jadwal i.bi, .pb-kode i.bi, .pb-beli i.bi { line-height: 1; }
        .sf-urut i.bi::before, .sf-reset i.bi::before, .pb-jumlah i.bi::before, .pb-diskon i.bi::before,
        .pb-jadwal i.bi::before, .pb-kode i.bi::before, .pb-beli i.bi::before { display: block; line-height: 1; }

        @media (max-width: 767.98px) {
            .sf-papan { padding: 14px; border-radius: 16px; }
            /* Chip menggulir mendatar selebar papan (grid kolom eksplisit di atas
               mencegahnya mendorong papan keluar layar). */
            .sf-kategori { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; margin: 0 -14px; padding: 0 14px 2px; }
            .sf-kategori::-webkit-scrollbar { display: none; }
            .sf-urut { flex: 1 1 auto; min-width: 0; }
            .sf-urut select { flex: 1 1 auto; min-width: 0; }
        }
        @media (max-width: 575.98px) {
            .pb-kartu { border-radius: 16px; }
            .pb-media { aspect-ratio: 4 / 3; }
            .pb-tumpuk > span { width: 42px; height: 42px; border-radius: 13px; font-size: 1.05rem; margin-left: -10px; }
            .pb-isi { padding: 12px 12px 13px; }
            .pb-nama { font-size: .9rem; }
            .pb-daftar-dur { display: none; }
            .pb-harga b { font-size: 1rem; }
            .pb-aksi { gap: 6px; padding-top: 12px; }
            /* Kartu paket di HP ~159px: jarak & huruf tombol dirapatkan supaya
               "Keranjang" tidak terpotong jadi "Keranjan". */
            .pb-beli { height: 38px; padding: 0 6px; border-radius: 10px; font-size: .76rem; }
            .pb-beli > span { gap: 5px; }
            .pb-lihat { width: 38px; height: 38px; border-radius: 10px; }
            .pb-jumlah { top: 8px; right: 8px; height: 23px; padding: 0 7px; font-size: .64rem; }
            .pb-diskon { top: 8px; left: 8px; height: 23px; padding: 0 7px; font-size: .66rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pb-kartu, .pb-media::before, .pb-media img, .pb-tumpuk > span, .pb-lihat, .pb-beli, .sf-chip, .sf-chip-ic { transition: none; }
        }

        /* ===== Empty state: vector + animasi ===== */
        .bdl-empty { text-align: center; padding: 30px 16px 20px; max-width: 480px; margin: 0 auto; }
        .bdl-empty-art { margin-bottom: 6px; }
        .bdl-empty-art svg { width: 260px; max-width: 82%; height: auto; overflow: visible; }
        .bdl-empty-title { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; color: #23272f; font-size: 1.35rem; margin: 4px 0 6px; }
        .bdl-empty-sub { color: #6b7280; font-size: .95rem; line-height: 1.6; margin: 0 auto 18px; max-width: 400px; }
        .bdl-empty-btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #fba919, #f26522); color: #fff; font-weight: 700; padding: .7rem 1.4rem; border-radius: 12px; box-shadow: 0 8px 20px rgba(242, 101, 34, .28); text-decoration: none; border: 0; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
        .bdl-empty-btn:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.04); box-shadow: 0 10px 24px rgba(242, 101, 34, .36); }

        .be-box { animation: be-bob 3.4s ease-in-out infinite; }
        .be-lid { animation: be-lidfloat 3.4s ease-in-out infinite; }
        .be-glow, .be-shadow, .be-spark { transform-box: fill-box; transform-origin: center; }
        .be-glow { animation: be-glowpulse 3.4s ease-in-out infinite; }
        .be-shadow { animation: be-shadowpulse 3.4s ease-in-out infinite; }
        .be-spark { animation: be-twinkle 2s ease-in-out infinite; }
        .be-spark.s2 { animation-delay: .5s; }
        .be-spark.s3 { animation-delay: 1s; }
        .be-spark.s4 { animation-delay: 1.4s; }

        @keyframes be-bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes be-lidfloat { 0%, 100% { transform: translateY(-8px); } 50% { transform: translateY(-16px); } }
        @keyframes be-glowpulse { 0%, 100% { opacity: .45; transform: scale(1); } 50% { opacity: .75; transform: scale(1.08); } }
        @keyframes be-shadowpulse { 0%, 100% { opacity: .16; transform: scaleX(1); } 50% { opacity: .09; transform: scaleX(.82); } }
        @keyframes be-twinkle { 0%, 100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }

        @media (prefers-reduced-motion: reduce) {
            .be-box, .be-lid, .be-glow, .be-shadow, .be-spark { animation: none !important; }
            .be-lid { transform: translateY(-10px); }
        }
    </style>
    @include('partials.bundling-deskripsi-style')

    <!-- Page Title -->
    {{-- Kartu judul bersama — rata kiri dengan remah roti di kanan, sama dengan
         halaman /shop dan detail produk. --}}
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box2-heart-fill"></i> Hemat Lebih</span>
                <h1>Paket Bundling</h1>
                <p>Gabungan beberapa akun premium dalam satu paket — lebih lengkap &amp; lebih hemat.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Beranda</a></li>
                    <li class="current">Paket Bundling</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section style="padding-top: 20px;">
        <div class="container">
            {{-- Jarak disamakan dengan daftar produk di shop: .best-sellers bawaan tema
                 memberi 60px di atas dan 30px di bawah, sedangkan shop 0 di atas dan
                 60px di bawah. Ditulis inline supaya menimpa aturan tema. --}}
            <section id="best-sellers" class="best-sellers section" style="padding-top: 0; padding-bottom: 60px;">
                <div class="container" wire:ignore.self>
                    {{-- Papan saring, bahasa visual yang sama dengan /shop. "Isi paket"
                         menyaring berdasarkan produk yang ADA DI DALAM paket — padanan
                         chip kategori di shop, karena paket sendiri tidak punya tipe. --}}
                    <div class="sf-papan">
                        @if (count($chipIsi))
                            <div class="sf-kategori" role="group" aria-label="Isi paket">
                                <button type="button" class="sf-chip {{ $isi === '' ? 'is-aktif' : '' }}"
                                    wire:click="$set('isi', '')" data-isi="" aria-pressed="{{ $isi === '' ? 'true' : 'false' }}">
                                    <span class="sf-chip-ic"><i class="bi bi-grid-fill"></i></span>Semua Paket
                                </button>
                                @foreach ($chipIsi as $c)
                                    <button type="button" class="sf-chip {{ $isi === $c['id'] ? 'is-aktif' : '' }}" style="--c: {{ $c['warna'] }}"
                                        wire:click="$set('isi', '{{ $c['id'] }}')"
                                        data-isi="{{ $c['id'] }}" aria-pressed="{{ $isi === $c['id'] ? 'true' : 'false' }}">
                                        <span class="sf-chip-ic"><i class="bi {{ $c['ikon'] }}"></i></span>{{ $c['nama'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="sf-bawah">
                            <label class="sf-urut">
                                <i class="bi bi-sort-down"></i>
                                <select wire:model.live="sortBy" aria-label="Urutkan paket">
                                    <option value="">Terbaru</option>
                                    <option value="termurah">Harga termurah</option>
                                    <option value="termahal">Harga termahal</option>
                                    <option value="nama">Nama A–Z</option>
                                    <option value="terlama">Terlama</option>
                                </select>
                            </label>
                            <div class="sf-info">
                                <span><b>{{ $bundlings->total() }}</b> paket</span>
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
                                    <button type="button" wire:click="$set('search', '')" aria-label="Hapus pencarian"><i class="bi bi-x"></i></button>
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="row g-3 g-lg-4">
                        @forelse ($kartu as $k)
                            <div class="col-6 col-md-4 col-lg-3" wire:key="bundling-{{ $k['id'] }}">
                                <article class="pb-kartu" style="--c: {{ $k['warna'] }}">
                                    <a href="{{ $k['url'] }}" class="pb-media {{ $k['gambar'] ? '' : 'is-kosong' }}" tabindex="-1" aria-hidden="true">
                                        @if ($k['gambar'])
                                            <img loading="lazy" src="{{ $k['gambar'] }}" alt=""
                                                onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                                        @endif
                                        <span class="pb-tumpuk">
                                            @foreach ($k['tumpuk'] as $t)
                                                <span style="--p: {{ $t['warna'] }}; --r: {{ $t['putar'] }}deg"><i class="bi {{ $t['ikon'] }}"></i></span>
                                            @endforeach
                                        </span>
                                        <span class="pb-jumlah"><i class="bi bi-box-seam"></i>{{ $k['jumlahIsi'] }} produk</span>
                                        @if ($k['diskon'])
                                            <span class="pb-diskon"><i class="bi bi-tag-fill"></i>{{ $k['diskon'] }}</span>
                                        @endif
                                    </a>

                                    <div class="pb-isi">
                                        <span class="pb-label">Paket Bundling</span>
                                        <a href="{{ $k['url'] }}" class="pb-nama">{{ $k['nama'] }}</a>

                                        @if (count($k['isiTampil']))
                                            <ul class="pb-daftar">
                                                @foreach ($k['isiTampil'] as $p)
                                                    <li style="--p: {{ $p['warna'] }}">
                                                        <span class="pb-daftar-ic"><i class="bi {{ $p['ikon'] }}"></i></span>
                                                        <span class="pb-daftar-nama">{{ $p['nama'] }}</span>
                                                        @if ($p['durasi'])
                                                            <span class="pb-daftar-dur">{{ $p['durasi'] }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                                @if ($k['isiLain'])
                                                    <li class="pb-daftar-lain">+{{ $k['isiLain'] }} produk lain</li>
                                                @endif
                                            </ul>
                                        @endif

                                        <div class="pb-harga">
                                            <b><span class="pb-rp">Rp</span>{{ number_format($k['harga'], 0, ',', '.') }}</b>
                                            <small>/paket</small>
                                            @if ($k['hargaAsli'])
                                                <s>Rp{{ number_format($k['hargaAsli'], 0, ',', '.') }}</s>
                                            @endif
                                        </div>
                                        @if ($k['hemat'] || $k['jadwal'])
                                            <div class="pb-meta">
                                                @if ($k['hemat'])
                                                    <span class="pb-hemat">{{ $k['hemat'] }}</span>
                                                @endif
                                                @if ($k['jadwal'])
                                                    <span class="pb-jadwal"><i class="bi bi-clock"></i>{{ $k['jadwal'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($k['kode'])
                                            {{-- Promo berkode tidak berlaku sendiri. Kodenya WAJIB terlihat,
                                                 kalau tidak pembeli mengira harga ini otomatis lalu kecewa
                                                 saat checkout menagih harga penuh. --}}
                                            <span class="pb-kode"><i class="bi bi-ticket-perforated-fill"></i> Pakai kode <b>{{ $k['kode'] }}</b></span>
                                        @endif

                                        <div class="pb-aksi">
                                            <button type="button" class="pb-beli" wire:click="addToCart('{{ $k['id'] }}')"
                                                wire:loading.attr="disabled" wire:target="addToCart('{{ $k['id'] }}')">
                                                <span wire:loading.remove wire:target="addToCart('{{ $k['id'] }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                                <span wire:loading wire:target="addToCart('{{ $k['id'] }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                            </button>
                                            <a href="{{ $k['url'] }}" class="pb-lihat" aria-label="Lihat detail {{ $k['nama'] }}" title="Lihat detail"><i class="bi bi-arrow-up-right"></i></a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="bdl-empty">
                                    <div class="bdl-empty-art">
                                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                                            aria-label="Belum ada paket bundling">
                                            <defs>
                                                <radialGradient id="beGlow" cx="50%" cy="50%" r="50%">
                                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                                </radialGradient>
                                                <linearGradient id="beLid" x1="0" y1="0" x2="1" y2="1">
                                                    <stop offset="0%" stop-color="#fbc25a" />
                                                    <stop offset="100%" stop-color="#f26522" />
                                                </linearGradient>
                                                <linearGradient id="beLeft" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#fdc069" />
                                                    <stop offset="100%" stop-color="#f7a23e" />
                                                </linearGradient>
                                                <linearGradient id="beRight" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#f4772b" />
                                                    <stop offset="100%" stop-color="#e15a18" />
                                                </linearGradient>
                                            </defs>

                                            <ellipse class="be-glow" cx="120" cy="112" rx="80" ry="80" fill="url(#beGlow)" />
                                            <ellipse class="be-shadow" cx="120" cy="182" rx="60" ry="8" fill="#e15a18" />

                                            <g transform="translate(46,74)"><path class="be-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                                            <g transform="translate(198,92)"><path class="be-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                            <g transform="translate(190,142)"><path class="be-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                                            <g transform="translate(52,146)"><path class="be-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                                            <g class="be-box">
                                                <path d="M66,100 L120,122 L120,176 L66,150 Z" fill="url(#beLeft)" />
                                                <path d="M174,100 L120,122 L120,176 L174,150 Z" fill="url(#beRight)" />
                                                <path d="M120,78 L174,100 L120,122 L66,100 Z" fill="#ffe9d0" />
                                                <path d="M120,86 L162,103 L120,120 L78,103 Z" fill="#f6d3ac" />
                                                <path d="M120,78 L174,100 L120,122 L66,100 Z" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="1.5" />
                                            </g>

                                            <g class="be-lid">
                                                <path d="M120,30 L166,50 L120,70 L74,50 Z" fill="url(#beLid)" />
                                                <path d="M74,50 L74,58 L120,78 L120,70 Z" fill="#e15a18" />
                                                <path d="M166,50 L166,58 L120,78 L120,70 Z" fill="#f4772b" />
                                                <circle cx="120" cy="42" r="6" fill="#fff3e0" />
                                                <circle cx="120" cy="42" r="6" fill="none" stroke="#f26522" stroke-opacity=".4" stroke-width="1.5" />
                                            </g>
                                        </svg>
                                    </div>
                                    @if ($search)
                                        <h3 class="bdl-empty-title">Paket tidak ditemukan</h3>
                                        <p class="bdl-empty-sub">Tidak ada paket bundling yang cocok dengan pencarian
                                            <b>"{{ $search }}"</b>. Coba kata kunci lain, ya.</p>
                                        <button type="button" class="bdl-empty-btn" wire:click="$set('search', '')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                                        </button>
                                    @elseif ($adaFilter)
                                        {{-- Filter aktif: pesannya tidak boleh bilang
                                             "belum ada paket", karena paketnya ada. --}}
                                        <h3 class="bdl-empty-title">Tidak ada yang cocok</h3>
                                        <p class="bdl-empty-sub">Filter yang dipilih belum menemukan paket apa pun.
                                            Coba longgarkan filternya, ya.</p>
                                        <button type="button" class="bdl-empty-btn" wire:click="resetFilters">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                                        </button>
                                    @else
                                        <h3 class="bdl-empty-title">Belum ada paket bundling</h3>
                                        <p class="bdl-empty-sub">Saat ini belum ada paket bundling yang aktif. Sementara itu,
                                            cek koleksi produk satuan kami, yuk!</p>
                                        <a href="{{ url('/shop') }}" class="bdl-empty-btn">
                                            <i class="bi bi-bag"></i> Lihat Produk Satuan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforelse

                        {{-- Paginasi seragam dengan halaman shop. --}}
                        @if ($bundlings->hasPages())
                            <div class="col-12 mt-5 ph-pagination">
                                {{ $bundlings->links('pagination.ph') }}
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </section>
</main>
