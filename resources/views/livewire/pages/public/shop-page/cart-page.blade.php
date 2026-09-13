<div class="kr-page">
    <style>
        /* ===== Keranjang =====
           Bahasa visual sama dengan kartu produk di Shop, Bundling, dan
           Wishlist: warna mengikuti KATEGORI produk (--c), media bersapuan
           warna, ubin ikon cadangan bila gambarnya tidak ada, sudut 18px.

           Kelas kr-*: aturan .cart-* yang lama ada di public-custom-styles.css,
           dan berkas itu lewat Vite ke public/build yang masuk .gitignore —
           beku di server sampai ada rsync. Ditulis inline supaya tampilan ini
           ikut `git pull`, sama seperti yang sudah dilakukan Wishlist. */
        .kr-page { --kr-ink: #1c1f26; --kr-muted: #6b7280; --kr-line: #eceff3; --kr-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .kr-sec { padding: 22px 0 64px; }

        /* ===== Bilah ringkas di atas daftar ===== */
        .kr-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px 18px; margin-bottom: 16px; }
        .kr-jumlah { display: flex; align-items: center; gap: 9px; font-size: .88rem; color: var(--kr-muted); }
        .kr-jumlah b { font-family: var(--kr-font); font-weight: 800; color: var(--kr-ink); }
        .kr-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 34px; height: 34px; border-radius: 11px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 13%, #fff); color: color-mix(in srgb, var(--c) 85%, #0f172a);
        }
        .kr-ubin i.bi, .kr-ubin i.bi::before { display: block; line-height: 1; }
        .kr-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 18px;
            border-radius: 13px; border: 1.5px solid #e5e0d8; background: #fff; color: var(--kr-ink);
            font-weight: 700; font-size: .86rem; text-decoration: none; white-space: nowrap;
            transition: border-color .18s ease, color .18s ease, transform .18s ease;
        }
        .kr-btn:hover { border-color: #f26522; color: #c2410c; transform: translateY(-1px); }
        .kr-btn i.bi, .kr-btn i.bi::before { display: block; line-height: 1; font-size: 1rem; }

        /* ===== Tata letak dua kolom ===== */
        .kr-tata { display: grid; grid-template-columns: minmax(0, 1fr) 366px; gap: 20px; align-items: start; }

        .kr-panel { background: #fff; border: 1px solid var(--kr-line); border-radius: 18px; overflow: hidden; }
        .kr-panel-kepala {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 16px 18px; border-bottom: 1px solid var(--kr-line);
        }
        .kr-panel-judul {
            display: flex; align-items: center; gap: 9px; margin: 0;
            font-family: var(--kr-font); font-weight: 800; font-size: 1.02rem; color: var(--kr-ink); letter-spacing: -.015em;
        }
        .kr-panel-judul i.bi, .kr-panel-judul i.bi::before { display: block; line-height: 1; font-size: 1rem; color: #f26522; }
        .kr-lencana {
            display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 24px; padding: 0 7px;
            border-radius: 99px; background: #fff1e7; color: #c2410c;
            font-family: var(--kr-font); font-weight: 800; font-size: .76rem; font-variant-numeric: tabular-nums;
        }
        /* Kosongkan itu tindakan merusak: merahnya terlihat tanpa perlu disentuh
           dulu, bukan abu-abu yang baru memerah saat kursor lewat. */
        .kr-kosongkan {
            display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 13px;
            border: 1.5px solid #fecaca; border-radius: 11px; background: #fff5f5; color: #dc2626;
            font-weight: 700; font-size: .8rem; white-space: nowrap; cursor: pointer;
            transition: background .18s ease, border-color .18s ease, transform .18s ease;
        }
        .kr-kosongkan:hover { background: #fee2e2; border-color: #f87171; transform: translateY(-1px); }
        .kr-kosongkan i.bi, .kr-kosongkan i.bi::before { display: block; line-height: 1; font-size: .85rem; }

        /* ===== Satu baris produk ===== */
        .kr-baris {
            position: relative; display: grid; grid-template-columns: 96px minmax(0, 1fr) auto auto;
            align-items: center; gap: 16px; padding: 16px 18px;
            transition: background .2s ease;
        }
        .kr-baris + .kr-baris { border-top: 1px solid var(--kr-line); }
        .kr-baris:hover { background: color-mix(in srgb, var(--c) 4%, #fff); }

        /* Media: sapuan warna kategori, sama dengan kartu di Shop & Wishlist. */
        .kr-media {
            position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 96px; height: 82px; border-radius: 14px; overflow: hidden; text-decoration: none;
            border: 1px solid color-mix(in srgb, var(--c) 16%, #fff);
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 12%, #fff) 0%, #fff 80%);
        }
        .kr-media::before {
            content: ""; position: absolute; top: -28px; right: -28px; width: 78px; height: 78px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent); transition: transform .35s ease;
        }
        .kr-baris:hover .kr-media::before { transform: scale(1.3); }
        .kr-media img { position: relative; max-width: 74%; max-height: 70%; object-fit: contain; mix-blend-mode: multiply; }
        /* Ubin cadangan: dipakai bila berkas gambarnya tidak ada. Dulu <img>
           SELALU dipasang, sehingga yang tampil justru teks alt-nya — tiga
           baris di halaman ini menampilkan "NotebookLM", "Research Rabbit",
           dan "Paket Riset Sultan" sebagai gambar rusak. */
        .kr-cadangan {
            position: relative; display: flex; align-items: center; justify-content: center;
            width: 46px; height: 46px; border-radius: 15px; font-size: 1.25rem; color: #fff;
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff));
            box-shadow: 0 10px 20px -12px color-mix(in srgb, var(--c) 85%, transparent);
            transition: transform .35s ease;
        }
        .kr-baris:hover .kr-cadangan { transform: scale(1.07) rotate(-4deg); }
        .kr-cadangan i.bi, .kr-cadangan i.bi::before { display: block; line-height: 1; }

        .kr-isi { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
        .kr-nama {
            font-family: var(--kr-font); font-weight: 700; font-size: .98rem; color: var(--kr-ink);
            margin: 0; line-height: 1.35; letter-spacing: -.01em; text-decoration: none;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        a.kr-nama:hover { color: #c2410c; }
        .kr-tanda { display: flex; flex-wrap: wrap; gap: 6px; }
        /* Lencana kategori memakai warna kategorinya; lencana durasi netral.
           Dua hal berbeda, jadi tidak diberi rupa yang sama. */
        .kr-kat {
            display: inline-flex; align-items: center; gap: 5px; height: 24px; padding: 0 9px;
            border-radius: 99px; background: color-mix(in srgb, var(--c) 10%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: color-mix(in srgb, var(--c) 80%, #0f172a); font-size: .72rem; font-weight: 700; white-space: nowrap;
        }
        .kr-dur {
            display: inline-flex; align-items: center; gap: 5px; height: 24px; padding: 0 9px;
            border-radius: 99px; background: #f8fafc; border: 1px solid #e8edf3;
            color: #475569; font-size: .72rem; font-weight: 700; white-space: nowrap;
        }
        .kr-dur small { color: #94a3b8; font-weight: 600; }
        .kr-kat i.bi, .kr-kat i.bi::before, .kr-dur i.bi, .kr-dur i.bi::before { display: block; line-height: 1; font-size: .74rem; }
        .kr-satuan { font-size: .8rem; color: var(--kr-muted); }
        .kr-satuan small { color: #9aa3af; }

        /* Rincian item JASA (halaman dilewati & add-on) */
        .kr-jasa { display: flex; flex-wrap: wrap; gap: 5px; }
        .kr-jasa-chip {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 9px; border-radius: 99px; font-size: .72rem;
            font-weight: 600; line-height: 1.4; border: 1px solid transparent;
        }
        .kr-jasa-chip i.bi { display: flex; align-items: center; line-height: 1; font-size: .62rem; flex-shrink: 0; }
        .kr-jasa-chip i.bi::before { display: block; line-height: 1; }
        .kr-jasa-chip b { font-weight: 800; }
        .kr-jasa-chip.is-addon { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .kr-jasa-chip.is-skip { background: #f1f5f9; border-color: #e2e8f0; color: #64748b; }

        .kr-sub { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; text-align: right; white-space: nowrap; }
        .kr-sub-label { font-size: .7rem; color: #9aa3af; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
        .kr-sub b { font-family: var(--kr-font); font-weight: 800; font-size: 1.05rem; color: var(--kr-ink); }

        .kr-hapus {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 36px; height: 36px; border: 1.5px solid #f1e7e7; border-radius: 50%;
            background: #fff; color: #94a3b8; cursor: pointer;
            transition: background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease;
        }
        .kr-hapus:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; transform: scale(1.06); }
        .kr-hapus i.bi, .kr-hapus i.bi::before { display: block; line-height: 1; font-size: .85rem; }

        /* ===== Ringkasan ===== */
        .kr-sisi { position: sticky; top: 92px; display: flex; flex-direction: column; gap: 14px; }
        .kr-ringkas { background: #fff; border: 1px solid var(--kr-line); border-radius: 18px; padding: 18px; }
        .kr-ringkas-judul {
            display: flex; align-items: center; gap: 9px; margin: 0 0 14px;
            font-family: var(--kr-font); font-weight: 800; font-size: 1.02rem; color: var(--kr-ink); letter-spacing: -.015em;
        }
        .kr-ringkas-judul i.bi, .kr-ringkas-judul i.bi::before { display: block; line-height: 1; font-size: 1rem; color: #f26522; }
        .kr-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: .88rem; color: var(--kr-muted); }
        .kr-row strong { font-family: var(--kr-font); font-weight: 700; color: var(--kr-ink); }
        .kr-garis { height: 1px; margin: 10px 0; background: repeating-linear-gradient(to right, var(--kr-line) 0 6px, transparent 6px 12px); }
        .kr-total { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 2px 0 14px; }
        .kr-total span { font-family: var(--kr-font); font-weight: 700; font-size: .95rem; color: var(--kr-ink); }
        .kr-total strong {
            font-family: var(--kr-font); font-weight: 800; font-size: 1.5rem; letter-spacing: -.02em;
            background: linear-gradient(135deg, #fba919, #f26522); -webkit-background-clip: text; background-clip: text; color: transparent;
        }
        .kr-checkout {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            height: 52px; padding: 0 18px; border-radius: 14px; text-decoration: none;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-family: var(--kr-font); font-weight: 800; font-size: .95rem;
            box-shadow: 0 14px 26px -14px rgba(242, 101, 34, .8);
            transition: filter .16s ease, transform .16s ease;
        }
        .kr-checkout:hover { color: #fff; filter: brightness(1.05); transform: translateY(-1px); }
        .kr-checkout span { display: inline-flex; align-items: center; gap: 8px; }
        .kr-checkout i.bi, .kr-checkout i.bi::before { display: block; line-height: 1; font-size: .95rem; }

        .kr-nota { display: flex; align-items: flex-start; gap: 8px; margin: 12px 0 0; font-size: .78rem; color: var(--kr-muted); line-height: 1.6; }
        .kr-nota i.bi { flex-shrink: 0; margin-top: 2px; font-size: .82rem; }
        .kr-nota i.bi::before { display: block; line-height: 1; }
        .kr-nota.is-promo i.bi { color: #d97706; }
        .kr-nota.is-aman i.bi { color: #16a34a; }
        .kr-nota b { color: var(--kr-ink); font-weight: 700; }

        /* ===== Tiga langkah sesudah checkout =====
           Bentuknya diambil dari "Cara Pesan" di beranda: satu JALUR berisi
           beberapa perhentian, bukan kartu-kartu setara. Di sini pertanyaannya
           lebih sempit — "sesudah saya tekan Checkout, apa yang terjadi?" —
           jadi perhentiannya tiga, mendatar, dan ringkas. */
        .kr-langkah { background: #fff; border: 1px solid var(--kr-line); border-radius: 18px; padding: 16px 18px; }
        .kr-langkah-judul {
            display: flex; align-items: center; gap: 9px; margin: 0 0 14px;
            font-family: var(--kr-font); font-weight: 800; font-size: .92rem; color: var(--kr-ink); letter-spacing: -.015em;
        }
        .kr-langkah-judul i.bi, .kr-langkah-judul i.bi::before { display: block; line-height: 1; font-size: .95rem; color: #f26522; }
        .kr-deret { position: relative; display: flex; flex-direction: column; gap: 14px; }
        /* Garis jalur menembus celah antar perhentian; ubin ikonnya berlatar
           pekat sehingga tampak duduk DI ATAS garis, bukan tertimpa. */
        .kr-deret::before { content: ""; position: absolute; z-index: 0; left: 17px; top: 30px; bottom: 22px; border-left: 2px dashed #f8d8bf; }
        .kr-henti { position: relative; z-index: 1; display: flex; align-items: flex-start; gap: 11px; }
        .kr-henti-ikon {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 36px; height: 36px; border-radius: 12px; font-size: .95rem;
            background: color-mix(in srgb, var(--c) 11%, #fff);
            border: 1.5px solid color-mix(in srgb, var(--c) 28%, #fff);
            color: var(--c);
        }
        .kr-henti-ikon i.bi, .kr-henti-ikon i.bi::before { display: block; line-height: 1; }
        .kr-henti-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; padding-top: 1px; }
        .kr-henti-judul { font-family: var(--kr-font); font-weight: 700; font-size: .86rem; color: var(--kr-ink); line-height: 1.35; }
        .kr-henti-ket { font-size: .78rem; color: var(--kr-muted); line-height: 1.55; }

        @media (max-width: 991.98px) {
            .kr-tata { grid-template-columns: minmax(0, 1fr); }
            /* Ringkasan berhenti menempel: di satu kolom ia sudah berada di
               bawah daftar, dan yang menempel justru menutupi isinya. */
            .kr-sisi { position: static; }
        }
        @media (max-width: 575.98px) {
            /* Baris empat kolom tidak muat di lebar telepon. Gambar tetap di
               kiri, subtotal turun ke bawah namanya, dan tombol hapus DIANGKAT
               keluar dari alur grid ke pojok kanan atas — dibiarkan sebagai
               kolom keempat, ia jatuh ke baris berikutnya sendirian dan
               meninggalkan satu baris kosong di bawah tiap produk. */
            .kr-baris { position: relative; grid-template-columns: 72px minmax(0, 1fr); gap: 12px; padding: 14px 52px 14px 14px; }
            .kr-media { width: 72px; height: 66px; border-radius: 12px; }
            .kr-cadangan { width: 38px; height: 38px; border-radius: 13px; font-size: 1.05rem; }
            .kr-sub { grid-column: 2; flex-direction: row; align-items: baseline; justify-content: space-between; width: 100%; }
            .kr-sub b { font-size: 1rem; }
            .kr-hapus { position: absolute; top: 12px; right: 12px; width: 32px; height: 32px; }
            .kr-nama { font-size: .92rem; }
            .kr-bar .kr-btn { width: 100%; }
            .kr-total strong { font-size: 1.35rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .kr-baris:hover .kr-media::before, .kr-baris:hover .kr-cadangan,
            .kr-btn:hover, .kr-checkout:hover, .kr-hapus:hover, .kr-kosongkan:hover { transform: none; }
            .kr-media::before, .kr-cadangan, .kr-btn, .kr-checkout, .kr-hapus, .kr-kosongkan, .kr-baris { transition: none; }
        }
    </style>

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-cart-fill"></i> Keranjang</span>
                <h1>Keranjang Belanja</h1>
                <p>Cek kembali produk pilihan Anda sebelum melanjutkan ke pembayaran.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Keranjang</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="kr-sec">
        <div class="container">
            @if (empty($cart))
                <div class="ph-empty my-4">
                    <div class="ph-empty-art">
                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Keranjang kosong">
                            <defs>
                                <radialGradient id="peGlowC" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="peCart" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fbc25a" />
                                    <stop offset="100%" stop-color="#f26522" />
                                </linearGradient>
                            </defs>
                            <ellipse class="pe-glow" cx="120" cy="108" rx="78" ry="78" fill="url(#peGlowC)" />
                            <ellipse class="pe-shadow" cx="120" cy="184" rx="58" ry="8" fill="#e15a18" />

                            <g transform="translate(48,66)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                            <g transform="translate(196,86)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <g transform="translate(190,140)"><path class="pe-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                            <g transform="translate(58,150)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                            <g class="pe-float">
                                <path d="M40 60 H60 L86 92" fill="none" stroke="url(#peCart)" stroke-width="6"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M80 86 L184 86 L168 140 L96 140 Z" fill="url(#peCart)" />
                                <path d="M80 86 L184 86" stroke="#ffffff" stroke-opacity=".55" stroke-width="3"
                                    stroke-linecap="round" />
                                <path d="M110 92 L104 134 M134 92 L134 134 M158 92 L164 134" stroke="#ffffff"
                                    stroke-opacity=".35" stroke-width="2.5" stroke-linecap="round" />
                                <path d="M112 140 V150 M156 140 V150" stroke="#e15a18" stroke-width="3"
                                    stroke-linecap="round" />
                                <circle cx="112" cy="158" r="9" fill="#e15a18" />
                                <circle cx="156" cy="158" r="9" fill="#e15a18" />
                                <circle cx="112" cy="158" r="3.4" fill="#fff3e0" />
                                <circle cx="156" cy="158" r="3.4" fill="#fff3e0" />
                            </g>
                        </svg>
                    </div>
                    <h3 class="ph-empty-title">Keranjang Anda kosong</h3>
                    <p class="ph-empty-sub">Belum ada produk di keranjang. Yuk pilih akun premium &amp; tools AI favoritmu!</p>
                    <div class="ph-empty-actions">
                        <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                    </div>
                </div>
            @else
                <div class="kr-bar">
                    <span class="kr-jumlah" style="--c: #f26522">
                        <span class="kr-ubin"><i class="bi bi-cart-check-fill"></i></span>
                        <span><b>{{ $totalQuantity }} item</b> siap dibayar</span>
                    </span>
                    <a href="{{ route('shop.index') }}" class="kr-btn"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
                </div>

                <div class="kr-tata">
                    {{-- Daftar produk --}}
                    <div class="kr-panel">
                        <div class="kr-panel-kepala">
                            <h3 class="kr-panel-judul">
                                <i class="bi bi-bag-check-fill"></i> Produk Pilihanmu
                                <span class="kr-lencana">{{ $totalQuantity }}</span>
                            </h3>
                            <button type="button" wire:click="$dispatch('confirm-empty-cart')" class="kr-kosongkan">
                                <i class="bi bi-trash"></i> Kosongkan
                            </button>
                        </div>

                        @foreach ($cart as $key => $item)
                            @php
                                $isBundle = ($item['type'] ?? 'product') === 'bundling';

                                // Warna & ikon dari KATEGORI produk — taksonomi yang sama
                                // dengan kartu di beranda, /shop, dan wishlist. Paket
                                // bundling memakai jingga rumah seperti di halaman lain.
                                $kat = $isBundle ? null : \App\Support\KategoriBeranda::untukProduk($item['product_name'] ?? '');
                                $warna = $isBundle ? '#f26522' : ($kat['warna'] ?? '#f26522');
                                $ikonKat = $isBundle ? 'bi-box2-heart-fill' : ($kat['ikon'] ?? 'bi-box-seam');
                                $labelKat = $isBundle ? 'Paket Bundling' : ($kat['label'] ?? 'Akun Premium');

                                // Gambar dipakai HANYA bila berkasnya ada; dulu selalu
                                // dipasang, sehingga yang tampil teks alt-nya.
                                $folderGbr = $isBundle ? 'ProductBundlings' : 'Product';
                                $namaGbr = ! empty($item['product_image']) ? basename($item['product_image']) : null;
                                $gambar = $namaGbr && is_file(public_path('storage/img/'.$folderGbr.'/'.$namaGbr))
                                    ? asset('storage/img/'.$folderGbr.'/'.$namaGbr)
                                    : null;

                                $durVal = (int) ($item['duration_value'] ?? 1);
                                $perBulan = (! $isBundle && ($item['duration_type'] ?? '') === 'bulan' && $durVal > 1)
                                    ? intdiv((int) $item['price'], max(1, $durVal)) : null;
                            @endphp
                            <div class="kr-baris" style="--c: {{ $warna }}" wire:key="cart-{{ $key }}">
                                <span class="kr-media">
                                    @if ($gambar)
                                        <img src="{{ $gambar }}" alt="{{ $item['product_name'] }}" loading="lazy"
                                            onerror="this.remove();">
                                    @else
                                        <span class="kr-cadangan"><i class="bi {{ $ikonKat }}"></i></span>
                                    @endif
                                </span>

                                <div class="kr-isi">
                                    <h6 class="kr-nama">{{ $item['product_name'] }}</h6>

                                    <div class="kr-tanda">
                                        <span class="kr-kat"><i class="bi {{ $ikonKat }}"></i>{{ $labelKat }}</span>

                                        @if (! $isBundle)
                                            <span class="kr-dur">
                                                @if (($item['duration_type'] ?? '') === 'halaman')
                                                    {{-- Jasa per halaman: tampilkan halaman yang dikerjakan --}}
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    {{ $item['halaman_dihitung'] ?? $item['jumlah_halaman'] ?? 0 }} halaman
                                                    @if (! empty($item['jumlah_halaman']) && ($item['halaman_dihitung'] ?? null) !== null && $item['halaman_dihitung'] < $item['jumlah_halaman'])
                                                        <small>dari {{ $item['jumlah_halaman'] }}</small>
                                                    @endif
                                                @elseif (($item['duration_type'] ?? '') === 'kali')
                                                    <i class="bi bi-collection"></i> {{ $item['duration_value'] }}× pengecekan
                                                @else
                                                    <i class="bi bi-calendar2-week"></i> {{ $item['duration_value'] }} {{ ucfirst($item['duration_type']) }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Rincian JASA: halaman dilewati & add-on yang dipilih --}}
                                    @if (! empty($item['halaman_dikecualikan']) || ! empty($item['addons']))
                                        <div class="kr-jasa">
                                            @if (! empty($item['halaman_dikecualikan']))
                                                <span class="kr-jasa-chip is-skip" title="Halaman ini tidak dikerjakan & tidak ditagih">
                                                    <i class="bi bi-slash-circle"></i> Lewati hal. {{ $item['halaman_dikecualikan'] }}
                                                </span>
                                            @endif
                                            @foreach (($item['addons'] ?? []) as $ad)
                                                <span class="kr-jasa-chip is-addon">
                                                    <i class="bi bi-plus-lg"></i>
                                                    {{ $ad['nama'] ?? '-' }}
                                                    <b>+Rp&nbsp;{{ number_format((int) ($ad['harga'] ?? 0), 0, ',', '.') }}</b>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <span class="kr-satuan">
                                        @if ($perBulan)
                                            Rp {{ number_format($perBulan, 0, ',', '.') }} <small>/ bulan × {{ $durVal }}</small>
                                        @else
                                            Rp {{ number_format($item['price'], 0, ',', '.') }} <small>/ {{ $isBundle ? 'paket' : 'item' }}</small>
                                        @endif
                                    </span>
                                </div>

                                <div class="kr-sub">
                                    <span class="kr-sub-label">Subtotal</span>
                                    <b>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</b>
                                </div>

                                <button type="button" class="kr-hapus" title="Hapus dari keranjang"
                                    wire:click="$dispatch('confirm-delete-product-cart', '{{ $key }}')"
                                    aria-label="Hapus {{ $item['product_name'] }} dari keranjang">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Ringkasan --}}
                    <aside class="kr-sisi">
                        <div class="kr-ringkas">
                            <h3 class="kr-ringkas-judul"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h3>

                            <div class="kr-row">
                                <span>Jumlah Produk</span>
                                <strong>{{ $totalQuantity }} item</strong>
                            </div>
                            <div class="kr-row">
                                <span>Subtotal</span>
                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>

                            <div class="kr-garis"></div>

                            <div class="kr-total">
                                <span>Total</span>
                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>

                            <a href="{{ route('checkout') }}" class="kr-checkout">
                                <span><i class="bi bi-lock-fill"></i> Checkout</span>
                                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </a>

                            <p class="kr-nota is-promo"><i class="bi bi-tag-fill"></i> <span>Harga <b>belum termasuk promo</b> — diterapkan saat checkout.</span></p>
                            <p class="kr-nota is-aman"><i class="bi bi-shield-check"></i> <span>Pembayaran aman — Transfer Bank &amp; QRIS.</span></p>
                        </div>

                        {{-- Warnanya berurutan seperti di "Cara Pesan": biru (membayar),
                             kuning (dikerjakan), hijau (selesai). Hijau di perhentian
                             terakhir bukan pilihan selera — di mana pun hijau berarti
                             beres, dan itu tepat kabar yang ingin disampaikannya. --}}
                        <div class="kr-langkah">
                            <h3 class="kr-langkah-judul"><i class="bi bi-signpost-split-fill"></i> Setelah Checkout</h3>
                            <div class="kr-deret">
                                @foreach ([
                                    ['#2563eb', 'bi-credit-card-2-front-fill', 'Bayar', 'Transfer atau QRIS, terverifikasi otomatis.'],
                                    ['#d97706', 'bi-gear-fill', 'Kami proses', 'Akun disiapkan seketika; naskah mulai dikerjakan.'],
                                    ['#16a34a', 'bi-inbox-fill', 'Terima hasilnya', 'Dikirim lewat email & WhatsApp, atau diunduh di halaman Anda.'],
                                ] as [$warnaL, $ikonL, $judulL, $ketL])
                                    <div class="kr-henti" style="--c: {{ $warnaL }}">
                                        <span class="kr-henti-ikon"><i class="bi {{ $ikonL }}"></i></span>
                                        <span class="kr-henti-teks">
                                            <span class="kr-henti-judul">{{ $judulL }}</span>
                                            <span class="kr-henti-ket">{{ $ketL }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</div>
