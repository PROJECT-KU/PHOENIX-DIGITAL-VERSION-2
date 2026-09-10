<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Di halaman bertoken, batasi referrer ke origin saja. Tanpa ini, saat
         pelanggan berpindah dari /cek/{token} ke halaman lain, browser
         mengirim URL bertoken itu sebagai referrer — dan GA/Pixel di halaman
         berikutnya ikut meneruskannya ke Google/Facebook. --}}
    @if (\App\Support\JalurAnalitik::peka())
        <meta name="referrer" content="origin">
    @endif

    @include('partials.seo')
    @include('partials.meta-pixel')
    @include('partials.google-analytics')
    {{-- Favicon.

         Google MENOLAK ikon yang tidak persegi, dan sebelumnya di sini
         dideklarasikan faviconphoenix.png yang berukuran 187x159 — itulah
         sebabnya hasil pencarian Phoenix tampil tanpa ikon. Berkas
         public/favicon.ico juga sempat berukuran 0 byte, padahal alamat itu
         yang dijemput Google lebih dulu sebagai cadangan; ia menemukan
         balasan 200 tanpa gambar apa pun lalu berhenti mencari.

         Sekarang keduanya persegi (192x192 dan 48x48), dibuat dengan MENAMBAH
         ruang kosong di sisi pendek, bukan meregangkan logo supaya tidak
         penyok. 192 dipilih karena kelipatan 48, ukuran yang disarankan
         Google. --}}
    <link href="{{ asset('favicon.ico') }}" rel="icon" sizes="48x48">
    <link href="{{ asset('icons/phoenix-192.png') }}" rel="icon" type="image/png" sizes="192x192">
    <link href="{{ asset('icons/phoenix-192.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('niceshop/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/vendor/drift-zoom/drift-basic.css') }}" rel="stylesheet">

    <link href="{{ asset('niceshop/assets/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('niceshop/assets/css/custom.css') }}" rel="stylesheet">


    <!-- Main CSS File -->
    {{-- <link href="{{ 'niceshop/assets/css/main.css' }}" rel="stylesheet">

    <link href="{{ 'niceshop/assets/css/custom.css' }}" rel="stylesheet"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/public-custom-styles.css', 'resources/js/public-custom-scripts.js'])

    {{-- ===== Huruf tampilan =====

         Judul dan angka besar pindah dari Poppins ke Plus Jakarta Sans.

         Poppins geometris murni: semua hurufnya dibangun dari lingkaran yang
         nyaris sama, dan pada ukuran besar keseragaman itu membuat judul
         terbaca rata tanpa watak. Ia juga huruf bawaan hampir semua templat,
         jadi mata pengunjung sudah terbiasa mengaitkannya dengan halaman yang
         dibuat cepat.

         Plus Jakarta Sans lebih sempit dan sedikit humanis — huruf 'a', 'g',
         dan 'y'-nya punya bentuk yang berbeda satu sama lain, jadi kata-kata
         punya siluet yang bisa dikenali. Pada judul tebal ia terlihat lebih
         padat dan lebih mahal, dan pada angka besar (diskon, penghitung
         mundur) digitnya lebih tegas.

         Ditulis di sini, bukan di berkas CSS: public/build masuk .gitignore
         dan salinannya di server beku sejak 19 Agustus, jadi aturan yang
         ditulis di sana tidak akan pernah sampai ke pengunjung.

         Hanya HURUF TAMPILAN yang diganti. Teks isi dibiarkan seperti semula:
         mengganti huruf paragraf mengubah panjang tiap baris di seluruh situs,
         dan itu risiko yang tidak sebanding dengan hasilnya. --}}
    <style>
        h1, h2, h3, h4, h5, h6,
        .ph-hero-title, .ph-hero-eyebrow,
        .kb-judul, .kb-kicker,
        .fsx-judul, .fsx-sapaan, .fsx-diskon-angka, .fsx-diskon-label,
        .fsx-satuan b, .fsx-titik-dua, .fsx-pita-atas,
        .fs-name, .fs-price-sale, .fsx-hemat-chip,
        .kp-nama, .dp-label, .dp-merek span,
        .jb-butir strong, .jb-sosial strong,
        .ph-jaminan-butir strong, .ph-kartu b, .ph-ubin span,
        .ph-btn-primary, .ph-btn-ghost, .fsx-tombol, .kb-tautan {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        }

        /* Plus Jakarta Sans lebih sempit daripada Poppins pada ukuran yang
           sama, jadi jarak hurufnya tidak perlu serapat sebelumnya — nilai
           negatif yang dulu pas untuk Poppins kini membuat huruf saling
           menempel. Dilonggarkan sedikit di tiga judul terbesar. */
        .ph-hero-title { letter-spacing: -.022em; }
        .kb-judul { letter-spacing: -.024em; }
        .fsx-judul { letter-spacing: -.026em; }
    </style>

    {{-- ===== Kaki halaman =====

         Gayanya berasal dari public-custom-styles.css yang tidak ikut
         terdeploy (salinannya di server beku sejak 19 Agustus), jadi
         pembenahannya harus ditulis inline di sini. --}}
    <style>
        /* --- IKON BENAR-BENAR DI TENGAH ---
           Glif Bootstrap Icons membawa tinggi baris bawaannya sendiri, jadi di
           dalam bulatan sosial dan ubin kontak ia duduk sedikit di bawah pusat.
           Selisihnya beberapa piksel, tapi pada tiga bulatan berjajar mata
           langsung menangkapnya. Keduanya perlu: line-height dinolkan DAN
           ::before dijadikan block. */
        #footer .social-icons a i.bi,
        #footer .contact-item i.bi,
        #footer .fsv-chip i.bi,
        #footer .fsv-cta i.bi { display: block; line-height: 1; }
        #footer .social-icons a i.bi::before,
        #footer .contact-item i.bi::before,
        #footer .fsv-chip i.bi::before,
        #footer .fsv-cta i.bi::before { display: block; line-height: 1; }

        /* Ikon dibiarkan SATU warna. Warnanya dipindah ke kartu kolomnya —
           mewarnai keduanya membuat kartu dan ikonnya saling berebut, dan yang
           menandai kolom jadi tidak jelas yang mana. */
        #footer .social-icons { display: flex; gap: 10px; }
        #footer .social-icons a {
            display: inline-flex !important; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 12px;
            background: rgba(255, 255, 255, .07);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #fff; font-size: 1.05rem;
            transition: background .2s ease, transform .2s ease;
        }
        #footer .social-icons a:hover {
            background: color-mix(in srgb, var(--c) 30%, transparent);
            border-color: color-mix(in srgb, var(--c) 45%, transparent);
            transform: translateY(-2px);
        }

        /* --- Kontak: ikon diberi ubinnya sendiri ---
           Glif telanjang di samping alamat dua baris menggantung di tengah
           kalimat; sebagai ubin yang disejajarkan ke baris pertama, ia jadi
           penanda awal baris — sama seperti ubin ikon di seluruh beranda. */
        #footer .footer-contact { display: flex; flex-direction: column; gap: 12px; }
        #footer .contact-item {
            display: flex; align-items: flex-start; gap: 12px;
            color: rgba(255, 255, 255, .78); font-size: .9rem; line-height: 1.55;
            transition: color .2s ease;
        }
        #footer a.contact-item:hover { color: #fff; }
        #footer .contact-item i.bi {
            flex: 0 0 auto; width: 34px; height: 34px; border-radius: 10px;
            display: flex !important; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 16%, transparent);
            color: var(--c); font-size: .95rem;
            margin-top: 1px;
        }

        /* --- Daftar tautan ---
           Bergeser sedikit ke kanan saat disentuh: penanda paling murah bahwa
           barisnya menanggapi, tanpa menambah warna atau bentuk apa pun. */
        #footer .footer-links li { margin-bottom: 9px; }
        #footer .footer-links a {
            display: inline-block; color: rgba(255, 255, 255, .72);
            font-size: .9rem; line-height: 1.5; text-decoration: none;
            transition: color .2s ease, transform .2s ease;
        }
        #footer .footer-links a:hover { transform: translateX(3px); }

        /* --- KEEMPAT KOLOM JADI KARTU BERWARNA ---
           Perlakuan yang sama dengan kartu Kategori Populer: bidang bergaris
           tipis, sapuan warna samar di pojok kanan atas, bingkai yang menyala
           saat disentuh. Bedanya di sini latarnya gelap, jadi kartunya dibuat
           dari cahaya (putih transparan) dan bukan dari bayangan.

           Sebelumnya keempat kolom hanya teks mengambang di bidang gelap —
           tidak ada yang menandai di mana satu kolom berakhir dan kolom
           berikutnya dimulai, dan di layar lebar keempatnya melebur jadi satu
           bidang teks. */
        #footer .footer-widget {
            position: relative; overflow: hidden; height: 100%;
            background: rgba(255, 255, 255, .035);
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 18px; padding: 24px 22px;
            transition: border-color .25s ease, background .25s ease;
        }
        #footer .footer-widget:hover {
            border-color: color-mix(in srgb, var(--c) 34%, transparent);
            background: color-mix(in srgb, var(--c) 6%, rgba(255, 255, 255, .035));
        }
        #footer .footer-widget::before {
            content: ""; position: absolute; top: -40px; right: -40px;
            width: 118px; height: 118px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 16%, transparent);
            transition: transform .3s ease; pointer-events: none;
        }
        #footer .footer-widget:hover::before { transform: scale(1.3); }
        #footer .footer-widget > * { position: relative; z-index: 1; }

        /* Garis-bawah judul mengikuti warna kolomnya, bukan gradient merek —
           dengan begitu penanda kolom dan warna kartunya satu suara. */
        #footer .footer-widget h4 {
            font-size: 1rem; letter-spacing: -.01em; margin-bottom: 18px;
        }
        #footer .footer-widget h4::after { background: var(--c) !important; width: 32px; }
        #footer .footer-about p {
            color: rgba(255, 255, 255, .68); font-size: .92rem; line-height: 1.7; max-width: 46ch;
        }
        #footer .social-links h5 {
            font-size: .78rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
            color: rgba(255, 255, 255, .5); margin-bottom: 12px;
        }

        /* --- Pita layanan lain --- */
        #footer .footer-services { padding: 18px 0 6px; }
        #footer .footer-services .fsv-inner {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .10);
        }
        #footer .fsv-chip i.bi { font-size: .9rem; color: var(--c) !important; }

        /* --- Kaki paling bawah --- */
        #footer .footer-bottom {
            margin-top: 26px; padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, .09);
        }
        #footer .copyright p { color: rgba(255, 255, 255, .6); font-size: .86rem; margin: 0; }

        @media (max-width: 767.98px) {
            #footer .footer-widget { padding: 20px 18px; border-radius: 16px; }
            #footer .footer-widget h4 { margin-bottom: 14px; }
            #footer .social-icons a { width: 38px; height: 38px; }
        }

        @media (prefers-reduced-motion: reduce) {
            #footer .footer-links a, #footer .contact-item { transition: none; }
        }
    </style>
    @stack('styles')
    @livewireStyles
</head>

<body class="index-page">
    <header id="header" class="header sticky-top">
        <!-- Top Bar -->
        <div class="py-2 top-bar">
            <div class="container-fluid container-xl">
                <div class="tb-baris">
                    <div class="tb-promo">
                        <div class="announcement-slider swiper init-swiper">
                            <script type="application/json" class="swiper-config">
                                {
                                    "loop": true,
                                    "speed": 600,
                                    "autoplay": {
                                        "delay": 5000
                                    },
                                    "slidesPerView": 1,
                                    "direction": "vertical",
                                    "effect": "slide"
                                }
                            </script>
                            <div class="swiper-wrapper">
                                @forelse ($headerPromos as $promo)
                                <div class="swiper-slide text-center d-flex align-items-center justify-content-center">

                                    @php
                                    // Menentukan ikon dan warna berdasarkan tipe promo
                                    $iconPromo = match($promo->tipe_promo) {
                                    'flash_sale' => '<i class="bi bi-lightning-charge-fill text-warning me-2 fs-5" style="vertical-align: middle;"></i>',
                                    'kode_promo' => '<i class="bi bi-ticket-perforated-fill text-info me-2 fs-5" style="vertical-align: middle;"></i>',
                                    'referral_bonus' => '<i class="bi bi-gift-fill text-danger me-2 fs-5" style="vertical-align: middle;"></i>',
                                    default => '<i class="bi bi-tags-fill text-success me-2 fs-5" style="vertical-align: middle;"></i>'
                                    };

                                    // Ambil 4 nilai yang mungkin ada
                                    $vals = [
                                    $promo->diskon_member_persen,
                                    $promo->diskon_member_nominal,
                                    $promo->diskon_non_member_persen,
                                    $promo->diskon_non_member_nominal
                                    ];

                                    // Ambil nilai terbesar yang bukan 0
                                    $maxVal = max($vals);

                                    // Tentukan apakah yang terbesar itu persen atau nominal
                                    $isNominal = ($maxVal == $promo->diskon_member_nominal || $maxVal == $promo->diskon_non_member_nominal) && $maxVal > 0;

                                    // "Sampai" hanya dipakai bila potongan member dan non-member
                                    // MEMANG berbeda. Kalau keduanya sama, kata itu pagar tanpa
                                    // isi: ia membuat tawaran terdengar lebih ragu daripada
                                    // kenyataannya, padahal setiap pembeli pasti mendapat angka
                                    // yang tertulis. Aturannya disamakan dengan etalase flash
                                    // sale di beranda supaya kedua tempat tidak berbeda kata.
                                    $nMember = $isNominal ? (float) $promo->diskon_member_nominal : (float) $promo->diskon_member_persen;
                                    $nUmum = $isNominal ? (float) $promo->diskon_non_member_nominal : (float) $promo->diskon_non_member_persen;
                                    $pakaiSampai = $nUmum > 0 && abs($nMember - $nUmum) > 0.001;
                                    @endphp

                                    {!! $iconPromo !!}

                                    <span class="fw-bold">{{ $promo->nama_promo }}</span>
                                    <small class="opacity-75 mx-2 text-capitalize">
                                        ({{ $promo->tipe_promo === 'flash_sale' ? 'Flash Sale' : str_replace('_', ' ', $promo->tipe_promo) }})
                                    </small>

                                    @if ($promo->tipe_promo === 'kode_promo' && $promo->kode_promo)
                                    <span class="promo-code-chip"><i class="bi bi-tag-fill"></i>{{ strtoupper($promo->kode_promo) }}</span>
                                    @endif

                                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-2 ms-1">
                                        @if ($promo->tipe_diskon === 'nominal' || $isNominal)
                                        Hemat{{ $pakaiSampai ? ' sampai' : '' }} Rp{{ number_format($maxVal, 0, ',', '.') }}
                                        @else
                                        Diskon{{ $pakaiSampai ? ' sampai' : '' }} {{ $maxVal }}%
                                        @endif
                                    </span>

                                </div>
                                @empty
                                <div class="swiper-slide text-center">
                                    <i class="bi bi-box-seam text-primary me-2"></i> 🚚 Dapatkan promo menarik kami hari ini!
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Sisi kanan: janji layanan + satu ajakan.

                         Pita atas sebelumnya hanya berisi promo yang berputar di
                         tengah, menyisakan dua pertiga lebar layar kosong. Ruang
                         itu kini dipakai untuk tiga janji yang paling sering
                         ditanyakan sebelum orang berani memesan, dan satu tombol
                         menuju promonya.

                         Disembunyikan di bawah 992px: di ponsel pita ini harus
                         menyisakan tempat untuk promonya sendiri, dan tiga janji
                         yang mengecil jadi tak terbaca sama saja dengan sampah. --}}
                    <div class="tb-kanan">
                        <span class="tb-janji"><i class="bi bi-lightning-charge-fill"></i> Proses Instan</span>
                        <span class="tb-janji"><i class="bi bi-shield-check"></i> Garansi Aman</span>
                        <span class="tb-janji"><i class="bi bi-headset"></i> Bantuan 24/7</span>
                        <a href="{{ route('shop.index') }}" class="tb-cta"><i class="bi bi-fire"></i> Klaim Promo</a>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
            .top-bar .tb-baris {
                display: flex; align-items: center; justify-content: space-between; gap: 18px;
            }
            .top-bar .tb-promo { min-width: 0; flex: 1 1 auto; }
            /* Promonya rata kiri sekarang, bukan rata tengah: begitu ada isi di
               sisi kanan, teks yang tetap rata tengah terbaca seperti salah
               tempat — tidak sejajar dengan apa pun di sekitarnya. */
            .top-bar .announcement-slider .swiper-slide { justify-content: flex-start !important; text-align: left !important; }

            .top-bar .tb-kanan { display: flex; align-items: center; gap: 18px; flex: 0 0 auto; }
            .top-bar .tb-janji {
                display: inline-flex; align-items: center; gap: 6px;
                font-size: .78rem; font-weight: 600; white-space: nowrap; opacity: .92;
            }
            .top-bar .tb-janji i.bi { font-size: .85rem; line-height: 1; }
            .top-bar .tb-cta {
                display: inline-flex; align-items: center; gap: 6px;
                background: #f26522; color: #fff; text-decoration: none;
                font-size: .78rem; font-weight: 700; white-space: nowrap;
                padding: 5px 14px; border-radius: 999px; line-height: 1.4;
                box-shadow: 0 4px 12px rgba(242, 101, 34, .28);
                transition: background .2s ease, transform .2s ease;
            }
            .top-bar .tb-cta:hover { background: #d9531a; color: #fff; transform: translateY(-1px); }
            .top-bar .tb-cta i.bi { font-size: .85rem; line-height: 1; }

            @media (max-width: 991.98px) {
                .top-bar .tb-kanan { display: none; }
                .top-bar .announcement-slider .swiper-slide { justify-content: center !important; text-align: center !important; }
            }
        </style>

        <!-- Main Header -->
        <div class="main-header">
            <div class="container-fluid container-xl">
                <div class="main-header-row py-2 d-flex align-items-center justify-content-between flex-wrap flex-xl-nowrap gap-2">

                    <!-- Logo: flame (gambar, tanpa teks) + wordmark via kode -->
                    <a href="/" class="logo phoenix-logo d-flex align-items-center">
                        <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital" class="phoenix-mark">
                        <span class="phoenix-wordmark">
                            <span class="pw-top">Phoenix</span>
                            <span class="pw-sub">Digital Warehouse</span>
                        </span>
                    </a>

                    <!-- Menu utama — desktop: inline di bar; mobile: off-canvas (hamburger) -->
                    <nav id="navmenu" class="navmenu">
                        <ul>
                            <li><a href="/" class="{{request()->routeIs('homepage') ? 'active' : ''}}">Beranda</a></li>
                                                        <li><a class="{{request()->routeIs('shop.*') ? 'active' : ''}}" href="{{ route('shop.index') }}">Shop</a></li>
                                                        <li><a class="{{request()->routeIs('bundling.*') ? 'active' : ''}}" href="{{ route('bundling.product-bundlings') }}">Bundling</a></li>
                                                        <li><a class="{{request()->routeIs('services') ? 'active' : ''}}" href="{{ route('services') }}">Layanan</a></li>
                            <li><a class="{{request()->routeIs('about') ? 'active' : ''}}" href="/about">About</a></li>
                                                        <li><a class="{{request()->routeIs('contact') ? 'active' : ''}}" href="{{route('contact')}}">Kontak</a></li>
                        </ul>
                    </nav>

                    <!-- Live Search (responsive: inline di desktop, baris penuh di mobile) -->
                    <livewire:components.global-search />

                    <!-- Cart & aksi -->
                    <div class="header-actions-group d-flex align-items-center gap-3 gap-md-4">
                        <a href="{{route('order.history')}}" class="ha-item ha-riwayat" title="Riwayat Pesanan">
                            <i class="bi bi-clock-history"></i>
                            <span class="ha-label">Riwayat</span>
                        </a>
                        <a href="{{ route('wishlist') }}" class="ha-item ha-wishlist" title="Wishlist"
                            x-data="{ n: 0 }"
                            x-init="n = (JSON.parse(localStorage.getItem('ph_wishlist')||'[]')).length"
                            @ph-wishlist-changed.window="n = (JSON.parse(localStorage.getItem('ph_wishlist')||'[]')).length">
                            <span class="ha-ic-wrap">
                                <i class="bi bi-heart"></i>
                                <span class="ha-badge" x-show="n > 0" x-cloak x-text="n"></span>
                            </span>
                            <span class="ha-label">Wishlist</span>
                        </a>
                        <livewire:components.cart-badge />
                        <!-- Mobile Navigation Toggle -->
                        <i class="mobile-nav-toggle d-xl-none bi bi-list me-0"></i>
                    </div>
                </div>
            </div>
        </div>
        </div>

    </header>
    {{ $slot }}
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer dark-background">
        <div class="footer-main">
            <div class="container">
                <div class="row gy-4">
                    <div class="col-lg-4 col-md-12">
                        <div class="footer-widget footer-about" style="--c: #fbaf45">
                            <a href="/" class="logo phoenix-logo phoenix-logo--light d-inline-flex align-items-center">
                                <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital" class="phoenix-mark">
                                <span class="phoenix-wordmark">
                                    <span class="pw-top">Phoenix</span>
                                    <span class="pw-sub">Digital Warehouse</span>
                                </span>
                            </a>
                            <p>Toko akun premium, lisensi, &amp; tools AI untuk riset dan produktivitas.
                                Proses cepat, aman, dan bergaransi — teman hemat kebutuhan digital Anda.</p>

                            <div class="mt-4 social-links">
                                <h5>Ikuti Kami</h5>
                                <div class="social-icons">
                                    <a href="https://web.facebook.com/profile.php?id=61586376808425" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                                    <a href="https://www.instagram.com/phoenixdigital.id/" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                                    <a href="https://www.tiktok.com/@phoenix_digitalwarehouse" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <div class="footer-widget" style="--c: #7fb3ff">
                            <h4>Menu</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('homepage') }}">Beranda</a></li>
                                <li><a href="{{ route('shop.index') }}">Shop</a></li>
                                <li><a href="{{ route('bundling.product-bundlings') }}">Paket Bundling</a></li>
                                <li><a href="{{ route('services') }}">Layanan Teknologi</a></li>
                                                                <li><a href="{{ route('blog.index') }}">Blog</a></li>
                                <li><a href="{{ route('order.history') }}">Riwayat Pesanan</a></li>
                                <li><a href="{{ route('track-order') }}">Lacak Pesanan</a></li>
                                <li><a href="/about">Tentang Kami</a></li>
                                <li><a href="{{ route('contact') }}">Kontak</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="footer-widget" style="--c: #a78bfa">
                            <h4>Bantuan &amp; Legal</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('faq') }}">FAQ — Pertanyaan Umum</a></li>
                                <li><a href="{{ route('member.info') }}">Syarat Jadi Member</a></li>
                                <li><a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a></li>
                                <li><a href="{{ route('privacy') }}">Kebijakan Privasi</a></li>
                                <li><a href="{{ route('terms') }}">Kebijakan Pengembalian</a></li>
                                <li><a href="{{ route('contact') }}">Hubungi Kami</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <div class="footer-widget" style="--c: #4ade80">
                            <h4>Kontak</h4>
                            <div class="footer-contact">
                                <div class="contact-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>Jl. Durmo, Ngemplak, Mlati, Sleman, Yogyakarta</span>
                                </div>
                                <a class="contact-item" href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya%20tentang%20produk." target="_blank" rel="noopener" style="text-decoration:none">
                                    <i class="bi bi-whatsapp"></i>
                                    <span>0895-0596-7995</span>
                                </a>
                                <a class="contact-item" href="mailto:halo@phoenixdigitalwarehouse.com" style="text-decoration:none">
                                    <i class="bi bi-envelope"></i>
                                    <span>halo@phoenixdigitalwarehouse.com</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-services">
            <div class="container">
                <div class="fsv-inner">
                    <div class="fsv-text">
                        <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Layanan Lainnya</span>
                        <h3>Butuh lebih dari sekadar akun digital?</h3>
                        <p>Kami juga melayani pembuatan solusi digital untuk kebutuhan bisnis &amp; instansi Anda.</p>
                        <div class="fsv-chips">
                            <span class="fsv-chip" style="--c: #a78bfa"><i class="bi bi-code-slash"></i> Pengembangan Website</span>
                            <span class="fsv-chip" style="--c: #7fb3ff"><i class="bi bi-phone"></i> Aplikasi Mobile</span>
                            <span class="fsv-chip" style="--c: #f76ea8"><i class="bi bi-camera-reels"></i> Konten Sosial Media</span>
                        </div>
                    </div>
                    <a class="fsv-cta" href="{{ route('services') }}">
                        <i class="bi bi-arrow-right-circle"></i> Lihat Layanan
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row gy-3 align-items-center">
                    <div class="col-lg-6 col-md-12">
                        <div class="copyright">
                            <p>© {{ date('Y') }} <strong class="sitename">Phoenix Digital</strong>. Semua hak dilindungi.
                            </p>
                        </div>
                        <div class="mt-1 credits">
                            <!-- All the links in the footer should remain intact. -->
                            <!-- You can delete the links only if you've purchased the pro version. -->
                            <!-- Licensing information: https://bootstrapmade.com/license/ -->
                            <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
                            Designed by <a href="https://phoenix.com/">Phoenix</a>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div
                            class="flex-wrap gap-4 d-flex justify-content-lg-end justify-content-center align-items-center">
                            <div class="payment-methods">
                                <div class="payment-icons">
                                    <span class="pay-chip"><i class="bi bi-bank"></i> Transfer</span>
                                    <span class="pay-chip"><i class="bi bi-qr-code"></i> QRIS</span>
                                </div>
                            </div>

                            <div class="legal-links">
                                <a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a>
                                <a href="{{ route('privacy') }}">Kebijakan Privasi</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </footer>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya%20tentang%20produk." target="_blank" rel="noopener" id="wa-button"
        class="wa-button d-flex align-items-center justify-content-center">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Preloader: animasi logo Phoenix menyusun sayap -->
    @include('partials.phoenix-loader')

    <!-- Vendor JS Files -->
    <script src="{{ asset('niceshop/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/drift-zoom/Drift.min.js') }}"></script>
    <script src="{{ asset('niceshop/assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>

    <script src="{{ asset('niceshop/assets/js/main.js') }}"></script>
    <script src="{{ asset('niceshop/assets/js/custom.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Init hero banner carousel (andal saat load & navigasi) -->
    <script>
        (function () {
            function initPhoenixHero() {
                if (!window.Swiper) return;
                document.querySelectorAll('.phoenix-hero-swiper').forEach(function (el) {
                    if (el.swiper) return; // sudah diinit
                    var multi = el.dataset.multi === '1';
                    new Swiper(el, {
                        loop: multi,
                        speed: 700,
                        grabCursor: true,
                        slidesPerView: 1,
                        autoplay: multi ? { delay: 5500, disableOnInteraction: false } : false,
                        // Bilah kemajuan, bukan titik. Titik kecil di dasar kartu
                        // praktis tak terlihat, jadi sebagian besar pengunjung
                        // tidak pernah tahu ada banner kedua. Bilah yang mengisi
                        // seiring waktu tayang memberi tahu keduanya sekaligus:
                        // ada yang menyusul, dan kapan gantinya.
                        pagination: { el: el.querySelector('.swiper-pagination'), type: 'progressbar' }
                    });
                });
            }
            window.addEventListener('load', initPhoenixHero);
            document.addEventListener('livewire:navigated', initPhoenixHero);
        })();
    </script>

    <!-- Init testimoni slider (center slide membesar sendiri) -->
    <script>
        (function () {
            function initPhoenixTestimonials() {
                if (!window.Swiper) return;
                document.querySelectorAll('.phoenix-tm-swiper').forEach(function (el) {
                    if (el.swiper) { try { el.swiper.destroy(true, true); } catch (e) {} }
                    var slides = el.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)').length;
                    new Swiper(el, {
                        loop: slides > 2,
                        centeredSlides: true,
                        slidesPerView: 'auto',
                        spaceBetween: 24,
                        grabCursor: true,
                        speed: 600,
                        autoplay: slides > 1 ? { delay: 4000, disableOnInteraction: false } : false,
                        pagination: { el: el.querySelector('.swiper-pagination'), clickable: true }
                    });
                });
            }
            window.addEventListener('load', initPhoenixTestimonials);
            document.addEventListener('livewire:navigated', initPhoenixTestimonials);
            // Re-init setelah pelanggan mengirim testimoni (jaga-jaga Swiper ter-morph)
            window.addEventListener('tm-reinit', function () { setTimeout(initPhoenixTestimonials, 60); });
        })();
    </script>



    <!-- Main JS File -->
    {{-- <script src="{{ 'niceshop/assets/js/main.js' }}"></script> --}}
    @livewireScripts
    @stack('scripts')
</body>

</html>