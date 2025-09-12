<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>
    <!-- Favicons -->
    <link href="{{ asset('global/assets/img/faviconphoenix.png') }}" rel="icon">
    <link href="{{ asset('global/assets/img/faviconphoenix.png') }}" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- custom styling -->
    <link rel="stylesheet" href="{{ asset('global/assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('global/assets/css/landing-page.css') }}">
    <!-- global vendor -->
    <link href="{{ asset('global/assets/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('global/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('global/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1127420529069594&ev=PageView&noscript=1" /></noscript>
    <noscript>
        <img height="1" width="1"
            src="https://www.facebook.com/tr?id=1127420529069594&ev=PageView
&noscript=1" />
    </noscript>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="index-page">
    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="{{ route('home') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('global/assets/img/logophoenix.png') }}" style="height: 60px; max-height: 100%;">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Beranda</a></li>
                    <li style="background-color: #DAA520;"><a href="#promo">Promo Hari Ini</a></li>
                    <li><a href="#tentang">Tentang Kami</a></li>
                    <li><a href="#statistik">Pembeli</a></li>
                    <li><a href="#produk">Produk Kami</a></li>
                    <li><a href="#faq">Bantuan</a></li>
                    <li><a href="#kontak">Hubungi Kami Sekarang!</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

        </div>
    </header>
    {{ $slot }}
    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer dark-background">
        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.html" class="logo d-flex align-items-center">
                        <img src="{{ asset('global/assets/img/logophoenix.png') }}"
                            style="height: 60px; max-height: 100%;">
                    </a>
                    <div class="footer-contact pt-3">
                        <p class="mt-3"><strong>Phone:</strong> <span><a href="https://wa.me/6289505967995"
                                    target="_blank">+62 895-0596-7995</a></span></p>
                        <p><strong>Email:</strong> <span><a
                                    href="mailto:phoenixdigitalwarehouse@gmail.com">phoenixdigitalwarehouse@gmail.com</a></span>
                        </p>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Tautan Berguna</h4>
                    <ul>
                        <li><a href="#hero">Home</a></li>
                        <li><a href="#promo">Promo Hari ini!</a></li>
                        <li><a href="#tentang">Tentang Kami</a></li>
                        <li><a href="#produk">Produk Kami</a></li>
                        <li><a href="#faq">Bantuan</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Layanan Kami</h4>
                    <ul>
                        <li><a href="#">Akun Scopus Lisensi + Scopus Ai</a></li>
                        <li><a href="#">Grammarly Premium</a></li>
                        <li><a href="#">Scispace</a></li>
                        <li><a href="#">Quillbot</a></li>
                        <li><a href="#">Deepl Pro</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-12 footer-newsletter">
                    <h4>Ikuti Perjalanan Kami</h4>
                    <p>Jangan lewatkan promo spesial & info akun premium terbaru! Langganan sekarang, gratis!</p>
                    <form action="forms/newsletter.php" method="post" class="php-email-form">
                        <div class="newsletter-form"><input type="email" name="email"><input type="submit"
                                value="Subscribe"></div>
                        <div class="loading">Loading</div>
                        <div class="error-message"></div>
                        <div class="sent-message">Your subscription request has been sent. Thank you!</div>
                    </form>
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">Phoenix Digital Warehouse</strong> <span
                    id="current-year"></span> - All Rights Reserved</p>
        </div>

    </footer>

    <!-- Tombol WhatsApp -->
    <a href="https://wa.me/6289505967995" target="_blank" id="whatsapp-button"
        class="whatsapp-float d-flex align-items-center justify-content-center">
        <i class="bi bi-whatsapp"></i>
    </a>
    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- vendor js files -->
    <script src="{{ asset('global/assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('global/assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('global/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('global/assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('global/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ asset('global/assets/js/landing-page.js') }}"></script>
</body>

</html>
