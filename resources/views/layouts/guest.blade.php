<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo')
    @include('partials.meta-pixel')
    @include('partials.google-analytics')
    <!-- Favicons -->
    <link href="{{ asset('niceshop/assets/img/faviconphoenix.png') }}" rel="icon">
    <link href="{{ asset('niceshop/assets/img/faviconphoenix.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
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
    @stack('styles')
    @livewireStyles
</head>

<body class="index-page">
    <!-- Garis animasi latar: membentang dari banner s/d footer, berjalan saat scroll -->
    <canvas id="ph-page-lines" aria-hidden="true"></canvas>

    <header id="header" class="header sticky-top">
        <!-- Top Bar -->
        <div class="py-2 top-bar">
            <div class="container-fluid container-xl">
                <div class="row align-items-center">
                    <div class="text-center col-lg-4 col-lg-12">
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
                                        Hemat sampai Rp {{ number_format($maxVal, 0, ',', '.') }}
                                        @else
                                        Diskon sampai {{ $maxVal }}%
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

                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="main-header">
            <div class="container-fluid container-xl">
                <div class="main-header-row py-2 d-flex align-items-center justify-content-between flex-wrap flex-xl-nowrap gap-2">

                    <!-- Logo: flame (gambar, tanpa teks) + wordmark via kode -->
                    <a href="/" class="logo phoenix-logo d-flex align-items-center">
                        <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital" class="phoenix-mark">
                        <span class="phoenix-wordmark">
                            <span class="pw-top">Phoenix</span>
                            <span class="pw-sub">Digital</span>
                        </span>
                    </a>

                    <!-- Menu utama — desktop: inline di bar; mobile: off-canvas (hamburger) -->
                    <nav id="navmenu" class="navmenu">
                        <ul>
                            <li><a href="/" class="{{request()->routeIs('homepage') ? 'active' : ''}}">Home</a></li>
                            <li><a class="{{request()->routeIs('shop.*') ? 'active' : ''}}" href="{{ route('shop.index') }}">Shop</a></li>
                            <li><a class="{{request()->routeIs('bundling.*') ? 'active' : ''}}" href="{{ route('bundling.product-bundlings') }}">Bundling</a></li>
                            <li><a class="{{request()->routeIs('services') ? 'active' : ''}}" href="{{ route('services') }}">Layanan</a></li>
                            <li><a class="{{request()->routeIs('about') ? 'active' : ''}}" href="/about">About</a></li>
                            <li><a class="{{request()->routeIs('contact') ? 'active' : ''}}" href="{{route('contact')}}">Contact</a></li>
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
                        <div class="footer-widget footer-about">
                            <a href="/" class="logo phoenix-logo phoenix-logo--light d-inline-flex align-items-center">
                                <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital" class="phoenix-mark">
                                <span class="phoenix-wordmark">
                                    <span class="pw-top">Phoenix</span>
                                    <span class="pw-sub">Digital</span>
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
                        <div class="footer-widget">
                            <h4>Menu</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('homepage') }}">Beranda</a></li>
                                <li><a href="{{ route('shop.index') }}">Shop</a></li>
                                <li><a href="{{ route('bundling.product-bundlings') }}">Paket Bundling</a></li>
                                <li><a href="{{ route('services') }}">Layanan Teknologi</a></li>
                                <li><a href="{{ route('order.history') }}">Riwayat Pesanan</a></li>
                                <li><a href="{{ route('track-order') }}">Lacak Pesanan</a></li>
                                <li><a href="/about">Tentang Kami</a></li>
                                <li><a href="{{ route('contact') }}">Kontak</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="footer-widget">
                            <h4>Bantuan &amp; Legal</h4>
                            <ul class="footer-links">
                                <li><a href="{{ route('faq') }}">FAQ — Pertanyaan Umum</a></li>
                                <li><a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a></li>
                                <li><a href="{{ route('privacy') }}">Kebijakan Privasi</a></li>
                                <li><a href="{{ route('terms') }}">Kebijakan Pengembalian</a></li>
                                <li><a href="{{ route('contact') }}">Hubungi Kami</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <div class="footer-widget">
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
                                <a class="contact-item" href="mailto:halo@phoenixdigital.id" style="text-decoration:none">
                                    <i class="bi bi-envelope"></i>
                                    <span>halo@phoenixdigital.id</span>
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
                            <span class="fsv-chip"><i class="bi bi-code-slash"></i> Pengembangan Website</span>
                            <span class="fsv-chip"><i class="bi bi-phone"></i> Aplikasi Mobile</span>
                            <span class="fsv-chip"><i class="bi bi-camera-reels"></i> Konten Sosial Media</span>
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
                        pagination: { el: el.querySelector('.swiper-pagination'), type: 'bullets', clickable: true }
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

    <!-- Garis animasi latar (banner → footer): mengalir + "berjalan" saat scroll -->
    <script>
        (function () {
            const canvas = document.getElementById('ph-page-lines');
            if (!canvas || !canvas.getContext) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const ctx = canvas.getContext('2d');
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            let w = 0, h = 0;

            function resize() {
                w = window.innerWidth; h = window.innerHeight;
                canvas.width = w * dpr; canvas.height = h * dpr;
                canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            }
            resize();
            window.addEventListener('resize', resize);

            // Beberapa lengkung yang MEMUSAT & MEREKAH seperti bilah SAYAP (mirip logo),
            // meliuk vertikal turun sepanjang dokumen (di-anchor docY - scrollY → "berjalan"
            // saat scroll). Tiap bilah beda amplitudo & fase → merekah/menyatu seperti sayap.
            const AMP_BASE = () => Math.min(w * 0.32, 420);
            // amp = pengali ayunan, ph = geser fase, wd = tebal, col = warna
            const blades = [
                { amp: 0.78, ph: 0.00, wd: 5.5, col: 'rgba(251,169,25,0.55)' },  // amber (dalam, tebal)
                { amp: 0.92, ph: 0.12, wd: 4.5, col: 'rgba(244,120,45,0.50)' },
                { amp: 1.06, ph: 0.26, wd: 3.5, col: 'rgba(242,101,34,0.42)' },  // oranye
                { amp: 1.22, ph: 0.42, wd: 2.5, col: 'rgba(132,204,22,0.36)' },  // lime (luar, tipis)
            ];
            let t = 0;
            function draw() {
                t += 0.004;
                const sy = window.scrollY || window.pageYOffset || 0;
                ctx.clearRect(0, 0, w, h);

                const cx = w * 0.5;
                const A = AMP_BASE();
                const yStart = sy - 140, yEnd = sy + h + 140;

                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.shadowColor = 'rgba(242,101,34,0.16)';
                ctx.shadowBlur = 8;

                for (const b of blades) {
                    ctx.beginPath();
                    for (let docY = yStart; docY <= yEnd; docY += 6) {
                        const x = cx
                            + Math.sin(docY * 0.0042 + t + b.ph) * (A * b.amp)
                            + Math.sin(docY * 0.011 + t * 0.6 + b.ph) * (A * b.amp * 0.22);
                        const vy = docY - sy;
                        docY === yStart ? ctx.moveTo(x, vy) : ctx.lineTo(x, vy);
                    }
                    ctx.strokeStyle = b.col;
                    ctx.lineWidth = b.wd;
                    ctx.stroke();
                }
                ctx.shadowBlur = 0;

                requestAnimationFrame(draw);
            }
            draw();
        })();
    </script>


    <!-- Main JS File -->
    {{-- <script src="{{ 'niceshop/assets/js/main.js' }}"></script> --}}
    @livewireScripts
    @stack('scripts')
</body>

</html>