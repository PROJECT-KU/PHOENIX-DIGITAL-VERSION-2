<div class="wl-page" x-data="{ ids: JSON.parse(localStorage.getItem('ph_wishlist') || '[]') }" x-init="$wire.load(ids)">
    <style>
        /* ===== Wishlist =====
           Bahasa visual sama dengan kartu produk di Shop: warna mengikuti
           KATEGORI produk (--c), media bersapuan warna, ubin ikon cadangan
           bila gambarnya tidak ada, dan sudut 18px.
           Kelas wl-*: aturan .rel-* ada di public-custom-styles.css yang beku
           di server, jadi tampilan ini tidak bergantung padanya. */
        .wl-page { --wl-ink: #1c1f26; --wl-muted: #6b7280; --wl-line: #eceff3; --wl-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .wl-sec { padding: 22px 0 64px; }

        .wl-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px 18px; margin-bottom: 16px; }
        .wl-jumlah { display: flex; align-items: center; gap: 9px; font-size: .88rem; color: var(--wl-muted); }
        .wl-jumlah b { font-family: var(--wl-font); font-weight: 800; color: var(--wl-ink); }
        .wl-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
        }
        .wl-ubin i.bi, .wl-ubin i.bi::before { display: block; line-height: 1; }
        .wl-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px;
            border-radius: 13px; border: 1.5px solid #e5e0d8; background: #fff; color: var(--wl-ink);
            font-weight: 700; font-size: .86rem; text-decoration: none; white-space: nowrap;
            transition: border-color .18s ease, color .18s ease, transform .18s ease;
        }
        .wl-btn:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }
        .wl-btn i.bi, .wl-btn i.bi::before { display: block; line-height: 1; font-size: 1rem; }

        /* ===== Kartu ===== */
        .wl-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .wl-kartu {
            position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden;
            background: #fff; border: 1px solid var(--wl-line); border-radius: 18px;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .wl-kartu:hover {
            transform: translateY(-4px); border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .wl-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 11; overflow: hidden; text-decoration: none;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .wl-media::before {
            content: ""; position: absolute; top: -44px; right: -44px; width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .35s ease;
        }
        .wl-kartu:hover .wl-media::before { transform: scale(1.3); }
        .wl-media img { position: relative; max-width: 70%; max-height: 66%; object-fit: contain; mix-blend-mode: multiply; transition: transform .35s ease; }
        .wl-kartu:hover .wl-media img { transform: scale(1.05); }
        /* Ubin cadangan: dipakai bila berkas gambarnya tidak ada — dulu gambarnya
           tetap dipasang sehingga yang tampil teks alt-nya. */
        .wl-cadangan {
            position: relative; display: flex; align-items: center; justify-content: center;
            width: 64px; height: 64px; border-radius: 20px; font-size: 1.65rem; color: #fff;
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff));
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
            transition: transform .35s ease;
        }
        .wl-kartu:hover .wl-cadangan { transform: scale(1.06) rotate(-4deg); }
        .wl-cadangan i.bi, .wl-cadangan i.bi::before { display: block; line-height: 1; }
        .wl-kat {
            position: absolute; left: 10px; bottom: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px; max-width: calc(100% - 20px);
            height: 26px; padding: 0 10px 0 8px; border-radius: 99px;
            background: rgba(255, 255, 255, .92); border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 78%, #0f172a); font-size: .7rem; font-weight: 700; white-space: nowrap;
        }
        .wl-kat span { overflow: hidden; text-overflow: ellipsis; }
        .wl-kat i.bi, .wl-kat i.bi::before { display: block; line-height: 1; font-size: .72rem; }
        .wl-hapus {
            position: absolute; top: 10px; right: 10px; z-index: 2;
            display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;
            border: 0; border-radius: 50%; background: rgba(255, 255, 255, .94); color: #64748b;
            box-shadow: 0 6px 16px -8px rgba(15, 23, 42, .5); cursor: pointer;
            transition: background .18s ease, color .18s ease, transform .18s ease;
        }
        .wl-hapus:hover { background: #fee2e2; color: #dc2626; transform: scale(1.06); }
        .wl-hapus i.bi, .wl-hapus i.bi::before { display: block; line-height: 1; font-size: .82rem; }

        .wl-isi { display: flex; flex-direction: column; flex: 1 1 auto; gap: 4px; padding: 14px 16px 16px; }
        .wl-nama {
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
            min-height: 2.7em; text-decoration: none; color: var(--wl-ink);
            font-family: var(--wl-font); font-weight: 700; font-size: 1rem; line-height: 1.35; letter-spacing: -.01em;
        }
        .wl-nama:hover { color: color-mix(in srgb, var(--c) 75%, #0f172a); }
        .wl-harga { display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 6px; margin-top: 6px; }
        .wl-harga small { font-size: .7rem; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; color: #64748b; }
        .wl-harga b { font-family: var(--wl-font); font-weight: 800; font-size: 1.12rem; color: var(--wl-ink); }
        .wl-aksi { display: flex; gap: 8px; margin-top: auto; padding-top: 14px; }
        .wl-lihat {
            flex: 1 1 auto; display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            height: 42px; border-radius: 12px; text-decoration: none; cursor: pointer;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .86rem; box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .7);
            transition: filter .16s ease, transform .16s ease;
        }
        .wl-lihat:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .wl-lihat i.bi, .wl-lihat i.bi::before { display: block; line-height: 1; }

        @media (max-width: 1199.98px) { .wl-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 991.98px) { .wl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575.98px) {
            .wl-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .wl-isi { padding: 12px 12px 14px; }
            .wl-nama { font-size: .9rem; }
            .wl-harga b { font-size: 1rem; }
            .wl-lihat { height: 38px; font-size: .8rem; }
            .wl-bar .wl-btn { width: 100%; }
        }
        @media (prefers-reduced-motion: reduce) {
            .wl-kartu, .wl-kartu:hover, .wl-lihat:hover, .wl-btn:hover { transform: none; transition: none; }
            .wl-media::before, .wl-media img, .wl-cadangan { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-heart-fill"></i> Wishlist</span>
                <h1>Produk Favorit Anda</h1>
                <p>Produk yang Anda simpan tersimpan di perangkat ini.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Wishlist</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="wl-sec">
        <div class="container">
            @if ($products->isEmpty() && $pakets->isEmpty())
                <div class="ph-empty my-4">
                    {{-- Ilustrasi beranimasi — pola & kelas animasi sama dengan
                         keranjang (pe-float / pe-glow / pe-spark), hanya gambarnya
                         yang berbeda, supaya kedua halaman terasa satu keluarga. --}}
                    <div class="ph-empty-art">
                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Wishlist kosong">
                            <defs>
                                <radialGradient id="peGlowW" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="peHeart" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fbc25a" />
                                    <stop offset="100%" stop-color="#f26522" />
                                </linearGradient>
                            </defs>

                            <ellipse class="pe-glow" cx="120" cy="106" rx="78" ry="78" fill="url(#peGlowW)" />
                            <ellipse class="pe-shadow" cx="120" cy="182" rx="52" ry="8" fill="#e15a18" />

                            <g transform="translate(50,64)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                            <g transform="translate(194,84)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <g transform="translate(188,142)"><path class="pe-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                            <g transform="translate(56,148)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                            {{-- Hati besar (garis putus = belum terisi) --}}
                            <g class="pe-float">
                                <path d="M120,158 C86,134 64,116 64,94 C64,78 76,68 90,68 C101,68 112,74 120,86
                                         C128,74 139,68 150,68 C164,68 176,78 176,94 C176,116 154,134 120,158 Z"
                                    fill="none" stroke="url(#peHeart)" stroke-width="7" stroke-linejoin="round"
                                    stroke-dasharray="10 8" />
                                <path d="M96,88 Q104,80 114,84" stroke="#ffffff" stroke-opacity=".7" stroke-width="4"
                                    stroke-linecap="round" fill="none" />
                            </g>

                            {{-- Hati kecil melayang, berbeda irama --}}
                            <g class="pe-float-2">
                                <path d="M172,52 C165,47 160,43 160,38 C160,34 163,32 166,32 C168,32 170,33 172,36
                                         C174,33 176,32 178,32 C181,32 184,34 184,38 C184,43 179,47 172,52 Z"
                                    fill="url(#peHeart)" opacity=".9" />
                            </g>
                        </svg>
                    </div>
                    <h3 class="ph-empty-title">Wishlist masih kosong</h3>
                    <p class="ph-empty-sub">Simpan produk favorit dengan menekan tombol <b>♥ Simpan ke Wishlist</b> di halaman produk.</p>
                    <div class="ph-empty-actions">
                        <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                    </div>
                </div>
            @else
                @php $jumlahWl = $products->count() + $pakets->count(); @endphp
                <div class="wl-bar">
                    <span class="wl-jumlah" style="--c: #e11d48">
                        <span class="wl-ubin"><i class="bi bi-heart-fill"></i></span>
                        <span><b>{{ $jumlahWl }} item</b> tersimpan di perangkat ini</span>
                    </span>
                    <a href="{{ route('shop.index') }}" class="wl-btn"><i class="bi bi-bag"></i> Cari Produk Lain</a>
                </div>

                <div class="wl-grid">
                    @foreach ($products as $p)
                    @php
                        // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                        // dengan kartu di beranda, /shop, dan hasil pencarian.
                        $katWl = \App\Support\KategoriBeranda::untukProduk($p->nama_akun);
                        $warnaWl = $katWl['warna'] ?? '#f26522';
                        // Gambar dipakai HANYA bila berkasnya ada; dulu selalu
                        // dipasang, sehingga yang tampil teks alt-nya.
                        $gambarWl = $p->image && is_file(public_path('storage/img/Product/'.basename($p->image)))
                            ? asset('storage/img/Product/'.basename($p->image))
                            : null;
                    @endphp
                    <article class="wl-kartu" style="--c: {{ $warnaWl }}">
                        <button type="button" class="wl-hapus" title="Hapus dari wishlist" aria-label="Hapus {{ $p->nama_akun }} dari wishlist"
                            @click="ids = ids.filter(i => i !== '{{ $p->id }}'); localStorage.setItem('ph_wishlist', JSON.stringify(ids)); window.dispatchEvent(new Event('ph-wishlist-changed')); if (window.phToast) phToast('Dihapus dari wishlist', 'Wishlist', 'bi-heart'); $wire.load(ids)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <a class="wl-media" href="{{ route('shop.detail-product', $p->id) }}" aria-label="{{ $p->nama_akun }}">
                            @if ($gambarWl)
                                <img src="{{ $gambarWl }}" alt="{{ $p->nama_akun }}" loading="lazy" onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
                            @else
                                <span class="wl-cadangan"><i class="bi {{ $katWl['ikon'] ?? 'bi-box-seam' }}"></i></span>
                            @endif
                            <span class="wl-kat"><i class="bi {{ $katWl['ikon'] ?? 'bi-tag' }}"></i><span>{{ $katWl['label'] ?? ucfirst((string) $p->tipe_akun) }}</span></span>
                        </a>
                        <div class="wl-isi">
                            <a class="wl-nama" href="{{ route('shop.detail-product', $p->id) }}">{{ $p->nama_akun }}</a>
                            @if ($p->harga_perbulan)
                                <span class="wl-harga"><small>Mulai</small> <b>Rp {{ number_format($p->harga_perbulan, 0, ',', '.') }}</b></span>
                            @endif
                            <div class="wl-aksi">
                                <a class="wl-lihat" href="{{ route('shop.detail-product', $p->id) }}"><i class="bi bi-arrow-right"></i> Lihat Produk</a>
                            </div>
                        </div>
                    </article>
                    @endforeach

                    {{-- Paket bundling, kartu sama dengan produk satuan. --}}
                    @foreach ($pakets as $pk)
                    @php
                        $hpw = \App\Support\HargaPaket::untuk($pk);
                        $gambarPk = $pk->gambar && is_file(public_path('storage/img/ProductBundlings/'.basename($pk->gambar)))
                            ? asset('storage/img/ProductBundlings/'.basename($pk->gambar))
                            : null;
                    @endphp
                    <article class="wl-kartu" style="--c: #f26522">
                        <button type="button" class="wl-hapus" title="Hapus dari wishlist" aria-label="Hapus {{ $pk->nama_paket }} dari wishlist"
                            @click="ids = ids.filter(i => i !== '{{ $pk->id }}'); localStorage.setItem('ph_wishlist', JSON.stringify(ids)); window.dispatchEvent(new Event('ph-wishlist-changed')); if (window.phToast) phToast('Dihapus dari wishlist', 'Wishlist', 'bi-heart'); $wire.load(ids)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                        <a class="wl-media" href="{{ route('bundling.detail', $pk->id) }}" aria-label="{{ $pk->nama_paket }}">
                            @if ($gambarPk)
                                <img src="{{ $gambarPk }}" alt="{{ $pk->nama_paket }}" loading="lazy" onerror="this.remove();">
                            @else
                                <span class="wl-cadangan"><i class="bi bi-box2-heart-fill"></i></span>
                            @endif
                            <span class="wl-kat"><i class="bi bi-box-seam"></i><span>Paket Bundling</span></span>
                        </a>
                        <div class="wl-isi">
                            <a class="wl-nama" href="{{ route('bundling.detail', $pk->id) }}">{{ $pk->nama_paket }}</a>
                            <span class="wl-harga"><small>Paket</small> <b>Rp {{ number_format($hpw['bayar'], 0, ',', '.') }}</b></span>
                            <div class="wl-aksi">
                                <a class="wl-lihat" href="{{ route('bundling.detail', $pk->id) }}"><i class="bi bi-arrow-right"></i> Lihat Paket</a>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>

