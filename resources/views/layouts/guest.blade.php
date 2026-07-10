<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
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
                    <div class="col-lg-6 col-md-8">
                        <div class="footer-widget footer-about">
                            <a href="/" class="logo phoenix-logo phoenix-logo--light d-inline-flex align-items-center">
                                <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="Phoenix Digital" class="phoenix-mark">
                                <span class="phoenix-wordmark">
                                    <span class="pw-top">Phoenix</span>
                                    <span class="pw-sub">Digital</span>
                                </span>
                            </a>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in nibh vehicula,
                                facilisis magna ut, consectetur lorem. Proin eget tortor risus.</p>

                            <div class="mt-4 social-links">
                                <h5>Connect With Us</h5>
                                <div class="social-icons">
                                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                                    <a href="#" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-6 col-sm-6">
                        <div class="footer-widget">
                            <h4>Product</h4>
                            <ul class="footer-links">
                                <li><a href="category.html">New Arrivals</a></li>
                                <li><a href="category.html">Bestsellers</a></li>
                                <li><a href="category.html">Women's Clothing</a></li>
                                <li><a href="category.html">Men's Clothing</a></li>
                                <li><a href="category.html">Accessories</a></li>
                                <li><a href="category.html">Sale</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="footer-widget">
                            <h4>Contact Information</h4>
                            <div class="footer-contact">
                                <div class="contact-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>123 Fashion Street, New York, NY 10001</span>
                                </div>
                                <div class="contact-item">
                                    <i class="bi bi-telephone"></i>
                                    <span>+1 (555) 123-4567</span>
                                </div>
                                <div class="contact-item">
                                    <i class="bi bi-envelope"></i>
                                    <span>hello@example.com</span>
                                </div>
                                <div class="contact-item">
                                    <i class="bi bi-clock"></i>
                                    <span>Monday-Friday: 9am-6pm<br>Saturday: 10am-4pm<br>Sunday: Closed</span>
                                </div>
                            </div>

                            <div class="mt-4 app-buttons">
                                <a href="#" class="app-btn">
                                    <i class="bi bi-apple"></i>
                                    <span>App Store</span>
                                </a>
                                <a href="#" class="app-btn">
                                    <i class="bi bi-google-play"></i>
                                    <span>Google Play</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="row gy-3 align-items-center">
                    <div class="col-lg-6 col-md-12">
                        <div class="copyright">
                            <p>© <span>Copyright</span> <strong class="sitename">Phoenix</strong>. All Rights Reserved.
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
                                    <i class="bi bi-credit-card" aria-label="Credit Card"></i>
                                    <i class="bi bi-paypal" aria-label="PayPal"></i>
                                    <i class="bi bi-apple" aria-label="Apple Pay"></i>
                                    <i class="bi bi-google" aria-label="Google Pay"></i>
                                    <i class="bi bi-shop" aria-label="Shop Pay"></i>
                                    <i class="bi bi-cash" aria-label="Cash on Delivery"></i>
                                </div>
                            </div>

                            <div class="legal-links">
                                <a href="tos.html">Terms</a>
                                <a href="privacy.html">Privacy</a>
                                <a href="tos.html">Cookies</a>
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

    <a href="https://wa.me/6281234567890" target="_blank" id="wa-button"
        class="wa-button d-flex align-items-center justify-content-center">
        <i class="bi bi-whatsapp"></i>
    </a>

    <!-- Preloader -->
    <div id="preloader"></div>

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


    <!-- Main JS File -->
    {{-- <script src="{{ 'niceshop/assets/js/main.js' }}"></script> --}}
    @livewireScripts
    @stack('scripts')
</body>

</html>