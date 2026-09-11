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

    {{-- ===== Judul halaman (dipakai 12 halaman: shop, keranjang, checkout,
         pembayaran, riwayat, lacak, wishlist, detail produk & paket, about,
         kontak, blog) =====

         Ditulis sekali di sini, bukan di halaman shop saja: kedua belas halaman
         memakai markup yang sama persis, dan satu halaman yang judulnya tampil
         berbeda dari sebelas lainnya akan terbaca sebagai halaman dari situs
         lain. Gaya lamanya ada di public-custom-styles.css yang tidak ikut
         terdeploy, jadi pembenahannya harus inline. --}}
    <style>
        /* PITA JADI KARTU.

           Sebelumnya judul halaman berupa pita selebar layar setinggi ~200px:
           pada layar lebar, bidang persiknya membentang dari tepi ke tepi dan
           isinya — tiga baris teks dan satu remah roti — mengambang kecil di
           tengah bidang yang terlalu besar untuknya.

           Kini pitanya bening dan KONTAINERNYA yang jadi kartu: selebar isi
           halaman di bawahnya, sejajar dengan kartu-kartu lain, dengan tinggi
           yang mengikuti isinya sendiri. */
        .ph-page-title {
            --c: #f26522;
            background: transparent !important; border: 0 !important;
            padding: 20px 0 4px !important;
        }
        /* KARTUNYA DIGAMBAR DI DALAM CELAH KONTAINER, bukan pada kontainernya.

           Versi sebelumnya menjadikan .container itu sendiri kartu — termasuk
           celah 12px di kiri-kanannya yang mestinya kosong. Hasilnya kartu
           24px lebih lebar daripada isi halaman di bawahnya (1320px vs 1296px
           di layar lebar), dan tepi-tepinya tidak pernah sejajar dengan bilah
           saring atau kisi produk: persis yang membuatnya terasa terlalu lebar.

           Bidang kartunya kini ::before yang menjorok 12px dari kiri-kanan —
           tepat selebar celahnya — jadi kartu otomatis sejajar dengan isi
           halaman di SETIAP ukuran kontainer Bootstrap (540, 720, 960, 1140,
           1320), tanpa satu angka lebar pun yang harus dipatok. */
        .ph-page-title > .container {
            position: relative; gap: 24px;
            padding: 22px 40px;  /* 12px celah + 28px ruang dalam kartu */
        }
        .ph-page-title > .container::before {
            content: ""; position: absolute; top: 0; bottom: 0; left: 12px; right: 12px; z-index: 0;
            border-radius: 22px; border: 1px solid #f1e6da;
            box-shadow: 0 14px 34px rgba(150, 100, 60, .07);
            background:
                /* Bola cahaya berwarna di pojok kanan atas — perlakuan kartu
                   Cara Pesan dalam skala yang lebih besar. Digambar sebagai
                   lapisan latar supaya ikut terpotong sudut kartu. */
                radial-gradient(38% 120% at 100% 0%, color-mix(in srgb, var(--c) 15%, transparent) 0%, transparent 70%),
                radial-gradient(50% 120% at 0% 100%, rgba(251, 169, 25, .09) 0%, transparent 60%),
                #fff;
            pointer-events: none;
        }
        /* Titik-titik samar di sisi kanan kartu, memudar ke kiri supaya tidak
           pernah menyentuh judul. Sudut kanannya ikut membulat supaya titiknya
           tidak menyembul keluar dari lengkung kartu. */
        .ph-page-title > .container::after {
            content: ""; position: absolute; top: 1px; bottom: 1px; right: 13px; width: 40%; z-index: 0;
            border-radius: 0 21px 21px 0;
            background-image: radial-gradient(color-mix(in srgb, var(--c) 24%, transparent) 1px, transparent 1px);
            background-size: 18px 18px;
            -webkit-mask-image: linear-gradient(270deg, #000 0%, transparent 100%);
            mask-image: linear-gradient(270deg, #000 0%, transparent 100%);
            pointer-events: none;
        }
        .ph-page-title > .container > * { position: relative; z-index: 1; }

        /* Label pembuka: ubin ikon berwarna + teks berjarak lebar, bahasa yang
           sama dengan kartu Cara Pesan dan Kategori Populer. Pil lamanya
           (bidang persik + bingkai) bertumpuk di atas latar yang juga persik,
           jadi nyaris tidak terlihat sebagai bentuk. */
        .ph-page-head .ph-sec-eyebrow {
            display: inline-flex !important; align-items: center; gap: 10px;
            background: none !important; border: 0 !important; padding: 0 !important;
            margin-bottom: 9px !important;
            font-size: .74rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase;
            color: var(--c) !important;
        }
        .ph-page-head .ph-sec-eyebrow i.bi {
            width: 30px; height: 30px; border-radius: 9px; flex: 0 0 auto;
            display: flex !important; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--c) 13%, #fff);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-size: .9rem; line-height: 1;
        }
        /* Ikon di tengah: glif Bootstrap Icons membawa tinggi baris bawaannya
           sendiri, jadi di dalam ubin ia duduk sedikit di bawah pusat. */
        .ph-page-head .ph-sec-eyebrow i.bi::before { display: block; line-height: 1; }

        /* Kekhususan .ph-page-head h1 dipakai supaya menang atas aturan lama
           yang masih memaksa Poppins pada selektor yang sama. */
        .ph-page-head h1 {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif !important;
            font-weight: 800; color: #1c1f26;
            font-size: clamp(1.65rem, 2.6vw, 2.2rem); line-height: 1.08;
            letter-spacing: -.03em; margin: 0;
        }
        .ph-page-head p {
            color: #6b7280; font-size: .96rem; line-height: 1.55;
            margin: 6px 0 0; max-width: 54ch;
        }

        /* Remah roti jadi satu pil, bukan teks lepas di pojok. Sebagai teks
           lepas ia terbaca sebagai sisa yang lupa dirapikan; sebagai pil ia
           terbaca sebagai alat navigasi yang disengaja. Pemisahnya panah tipis,
           bukan garis miring: garis miring terbaca sebagai alamat berkas. */
        .ph-page-title .breadcrumbs { flex: 0 0 auto; }
        .ph-page-title .breadcrumbs ol {
            display: inline-flex !important; align-items: center; flex-wrap: wrap;
            background: #fff; border: 1px solid #eceff4; border-radius: 999px;
            padding: 8px 16px; gap: 0;
            box-shadow: 0 6px 18px rgba(120, 90, 60, .06);
            font-size: .85rem;
        }
        .ph-page-title .breadcrumbs ol li { display: inline-flex; align-items: center; }
        .ph-page-title .breadcrumbs ol li + li { padding-left: 10px; }
        .ph-page-title .breadcrumbs ol li + li::before {
            content: "\203A"; padding-right: 10px;
            color: #c3c9d2; font-size: 1.1rem; line-height: 1;
        }
        .ph-page-title .breadcrumbs a {
            color: #6b7280 !important; text-decoration: none; font-weight: 600;
            transition: color .2s ease;
        }
        .ph-page-title .breadcrumbs a:hover { color: var(--c) !important; }
        .ph-page-title .breadcrumbs .current { color: #1c1f26 !important; font-weight: 700; }

        @media (max-width: 991.98px) {
            .ph-page-title .breadcrumbs { margin-top: 16px; }
        }
        @media (max-width: 575.98px) {
            .ph-page-title > .container { padding: 20px 30px; }
            .ph-page-title > .container::before { border-radius: 18px; }
            .ph-page-title > .container::after { width: 55%; opacity: .6; border-radius: 0 17px 17px 0; }
            .ph-page-head p { font-size: .94rem; }
            .ph-page-title .breadcrumbs ol { font-size: .8rem; padding: 7px 14px; }
        }
    </style>

    {{-- ===== Kaki halaman =====

         Gayanya berasal dari public-custom-styles.css yang tidak ikut
         terdeploy (salinannya di server beku sejak 19 Agustus), jadi
         pembenahannya harus ditulis inline di sini. --}}
    <style>
        /* --- LATAR KAKI HALAMAN ---

           Sebelumnya satu bidang cokelat arang (#201b18) — dan sejak hero
           dijadikan biru-gelap, dua nada gelap dari keluarga warna yang berbeda
           membuat halaman berhenti terasa satu bahasa. Kaki halaman disamakan
           ke keluarga hero.

           Bidangnya juga tidak lagi rata: dua bola cahaya hangat sangat samar
           memberi kedalaman, dan satu lapis butiran menghapus pita warna yang
           selalu muncul pada gradien selebar ini di layar 8-bit. Persis
           perlakuan yang dipakai hero dan kartu promo, jadi ketiganya satu
           bahasa. */
        #footer.footer.dark-background {
            background:
                radial-gradient(52% 62% at 14% 0%, rgba(242, 101, 34, .16) 0%, rgba(242, 101, 34, 0) 60%),
                radial-gradient(46% 58% at 88% 96%, rgba(251, 169, 25, .12) 0%, rgba(251, 169, 25, 0) 62%),
                linear-gradient(160deg, #232937 0%, #1b2029 52%, #171b23 100%);
        }
        #footer.footer.dark-background::after {
            content: ""; position: absolute; inset: 0; pointer-events: none; z-index: 0;
            opacity: .22; mix-blend-mode: overlay;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)' opacity='.5'/%3E%3C/svg%3E");
        }
        /* Isinya harus berada DI ATAS lapisan butiran, kalau tidak seluruh
           tulisan ikut teredam olehnya. */
        #footer .footer-main, #footer .footer-services, #footer .footer-bottom { position: relative; z-index: 1; }

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

        /* --- Pita layanan lain ---

           Ini AJAKAN, bukan kolom navigasi. Begitu keempat kolom di atasnya
           jadi kartu netral, panel ini harus terlihat berbeda dari mereka —
           kalau tidak, ia terbaca sebagai kolom kelima dan ajakan di dalamnya
           hilang di antara daftar tautan.

           Bedanya dibuat dari kehangatan: latarnya bernada jingga dan
           bingkainya amber, sementara kartu kolom netral putih transparan. */
        #footer .footer-services { padding: 22px 0 6px; }
        #footer .footer-services .fsv-inner {
            position: relative; overflow: hidden;
            background:
                radial-gradient(70% 120% at 100% 0%, rgba(251, 169, 25, .16) 0%, rgba(251, 169, 25, 0) 62%),
                linear-gradient(120deg, rgba(242, 101, 34, .12), rgba(251, 169, 25, .06));
            border: 1px solid rgba(251, 175, 69, .26);
            padding: 28px 32px;
        }
        #footer .footer-services .fsv-inner > * { position: relative; z-index: 1; }

        /* Label pembuka: teks polos berjarak lebar, bukan pil. Pil di dalam
           panel yang sudah berbingkai membuat dua bingkai bertumpuk. */
        #footer .fsv-text .ph-sec-eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: none !important; border: 0 !important; padding: 0 !important;
            font-size: .74rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase;
            color: #fbaf45 !important;
        }
        #footer .fsv-text h3 {
            font-size: 1.5rem; letter-spacing: -.025em; line-height: 1.2; margin: 10px 0 6px;
        }
        #footer .fsv-text p { max-width: 52ch; }

        /* Chip diberi latar berwarna, bukan hanya glif berwarna. Glif setinggi
           14px yang berwarna sendirian di dalam pil abu nyaris tidak terbaca
           sebagai warna — yang tertangkap mata bidangnya, bukan gorenya. */
        #footer .fsv-chip {
            background: color-mix(in srgb, var(--c) 14%, transparent) !important;
            border-color: color-mix(in srgb, var(--c) 30%, transparent) !important;
            padding: 7px 14px;
        }
        #footer .fsv-chip i.bi { font-size: .9rem; color: var(--c) !important; }

        #footer .fsv-cta { align-self: center; }

        /* --- Kaki paling bawah ---

           Empat hal berbeda berdesakan di satu baris tanpa hierarki: hak
           cipta, kredit perancang, metode pembayaran, dan tautan legal.
           Dipisah jadi dua kelompok yang jelas — siapa kami di kiri, apa yang
           perlu diketahui di kanan — dan yang di kanan diberi pemisah titik
           tengah supaya kelompoknya terbaca, bukan sekadar berjajar. */
        #footer .footer-bottom {
            margin-top: 26px; padding-top: 22px; padding-bottom: 6px;
            border-top: 1px solid rgba(255, 255, 255, .09);
        }
        #footer .copyright p {
            color: rgba(255, 255, 255, .62); font-size: .86rem; margin: 0; line-height: 1.5;
        }
        #footer .credits {
            color: rgba(255, 255, 255, .38); font-size: .78rem; line-height: 1.5;
        }
        #footer .credits strong { color: rgba(255, 255, 255, .55); font-weight: 700; }

        /* Label kecil sebelum chip: tanpa itu "Transfer" dan "QRIS" berdiri
           sendiri tanpa keterangan, dan pengunjung harus menebak keduanya
           metode pembayaran. */
        #footer .fb-label {
            font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase;
            color: rgba(255, 255, 255, .38);
        }
        #footer .payment-icons { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        #footer .pay-chip {
            display: inline-flex !important; align-items: center; gap: 7px;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 10px; padding: 7px 13px;
            color: rgba(255, 255, 255, .82); font-size: .8rem; font-weight: 600;
        }
        #footer .pay-chip i.bi { display: block; line-height: 1; color: #fbaf45; font-size: .95rem; }
        #footer .pay-chip i.bi::before { display: block; line-height: 1; }

        /* Tautan legal dipisah titik tengah, bukan jarak kosong: dua tautan
           yang hanya berjarak terbaca sebagai satu kalimat terputus. */
        #footer .legal-links { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        #footer .legal-links a {
            color: rgba(255, 255, 255, .62); font-size: .82rem; text-decoration: none;
            transition: color .2s ease;
        }
        #footer .legal-links a + a { position: relative; padding-left: 14px; }
        #footer .legal-links a + a::before {
            content: "·"; position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            color: rgba(255, 255, 255, .25);
        }

        @media (max-width: 991.98px) {
            #footer .footer-bottom .row > div { text-align: center; }
            #footer .payment-icons, #footer .legal-links { justify-content: center; }
        }

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

                                    {{-- Jenis promo TIDAK lagi dicetak dalam kurung.
                                         Ikon di sebelah kiri sudah mengatakannya
                                         (petir untuk flash sale, tiket untuk kode
                                         promo), dan badge di kanan sudah mengatakan
                                         besarannya. "(Flash Sale)" di antara
                                         keduanya hanya mengulang, dan pengulangan
                                         di pita setinggi 34px memakan ruang yang
                                         justru dibutuhkan nama promonya. --}}
                                    {!! $iconPromo !!}

                                    <span class="tb-nama">{{ $promo->nama_promo }}</span>

                                    @if ($promo->tipe_promo === 'kode_promo' && $promo->kode_promo)
                                    <span class="promo-code-chip"><i class="bi bi-tag-fill"></i>{{ strtoupper($promo->kode_promo) }}</span>
                                    @endif

                                    <span class="tb-nilai">
                                        @if ($promo->tipe_diskon === 'nominal' || $isNominal)
                                        Hemat{{ $pakaiSampai ? ' s/d' : '' }} <b>Rp{{ number_format($maxVal, 0, ',', '.') }}</b>
                                        @else
                                        Diskon{{ $pakaiSampai ? ' s/d' : '' }} <b>{{ $maxVal }}%</b>
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

            .top-bar .tb-kanan { display: flex; align-items: center; gap: 20px; flex: 0 0 auto; }

            /* Tiga janji layanan.

               Sebelumnya tiga potong teks berikon yang mengambang tanpa
               struktur: tidak ada yang menandai di mana satu janji berakhir
               dan janji berikutnya dimulai, jadi ketiganya terbaca sebagai satu
               kalimat panjang yang aneh — "Proses Instan Garansi Aman Bantuan
               24/7".

               Dua penambahan kecil menyelesaikannya: garis pemisah setipis
               rambut di antara ketiganya, dan ikon yang diberi bulatan sendiri
               supaya tiap janji punya titik awal yang jelas. */
            .top-bar .tb-janji {
                position: relative;
                display: inline-flex; align-items: center; gap: 7px;
                font-size: .78rem; font-weight: 600; white-space: nowrap;
                color: #2b1d12;
            }
            /* Ditulis dengan #header supaya menang atas aturan lama
               `#header .top-bar i { font-size: 1.05rem }` — selektor ber-ID
               selalu mengalahkan berapa pun kelas yang ditumpuk, jadi tanpa ini
               glifnya tetap 16,8px dan berdesakan di dalam bulatan 22px.
               Bayangan jatuh bawaannya juga dimatikan: pada glif sekecil ini ia
               hanya membuatnya tampak kabur. */
            #header .top-bar .tb-janji i.bi {
                width: 22px; height: 22px; border-radius: 50%; flex: 0 0 auto;
                display: flex !important; align-items: center; justify-content: center;
                background: rgba(255, 255, 255, .58);
                color: #b3490f !important; font-size: .72rem; filter: none;
            }
            /* Pemisah diletakkan di TENGAH celah (setengah dari gap 20px), jadi
               ia berjarak sama ke kiri dan ke kanan — pemisah yang menempel
               pada salah satu sisi terbaca sebagai milik janji itu, bukan
               sebagai batas di antara keduanya. */
            .top-bar .tb-janji + .tb-janji::before {
                content: ""; position: absolute; left: -10px; top: 50%;
                transform: translateY(-50%);
                width: 1px; height: 15px; background: rgba(43, 29, 18, .20);
            }
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

            /* Nama promo dipotong dengan elipsis, bukan dibiarkan mendorong
               badge keluar. Nama sepanjang "Merdeka Sale! Diskon 17% All Item
               Spesial HUT RI ke-81" akan mendesak nilai diskonnya keluar dari
               pita — dan nilai diskon justru satu-satunya hal yang harus
               selamat di baris ini. */
            .top-bar .tb-nama {
                font-weight: 700; min-width: 0;
                overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
            }

            /* Nilai diskon: pil pekat, angkanya lebih tebal daripada
               kalimatnya. Badge kuning bawaan Bootstrap terlalu terang di atas
               pita jingga — keduanya sama-sama hangat, jadi pilnya tidak
               terbaca sebagai sesuatu yang terpisah. */
            .top-bar .tb-nilai {
                display: inline-flex; align-items: center; gap: 5px; flex: 0 0 auto;
                background: rgba(36, 26, 18, .82); color: #ffe6c9;
                border-radius: 999px; padding: 4px 13px;
                font-size: .78rem; font-weight: 600; line-height: 1.5; white-space: nowrap;
            }
            .top-bar .tb-nilai b { color: #fff; font-weight: 800; }

            /* Ikon di tengah: glif Bootstrap Icons membawa tinggi baris
               bawaannya sendiri, jadi di dalam pita setipis ini ia duduk
               sedikit di bawah garis teks di sebelahnya. */
            .top-bar i.bi { display: block; line-height: 1; }
            .top-bar i.bi::before { display: block; line-height: 1; }
            .top-bar .announcement-slider .swiper-slide { gap: 10px; }

            @media (max-width: 991.98px) {
                .top-bar .tb-kanan { display: none; }
                .top-bar .announcement-slider .swiper-slide { justify-content: center !important; text-align: center !important; }
            }
            @media (max-width: 575.98px) {
                .top-bar .tb-nilai { font-size: .72rem; padding: 3px 10px; }
                .top-bar .tb-nama { font-size: .82rem; }
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
                        {{-- Tautan "Designed by Phoenix" sebelumnya menuju
                             https://phoenix.com/ — perusahaan lain yang sama
                             sekali tidak berhubungan dengan toko ini. Baris
                             kredit yang mengirim pengunjung ke situs asing
                             adalah kebocoran, bukan kredit. Dijadikan teks
                             biasa; tidak ada tempat yang perlu dituju. --}}
                        <div class="mt-1 credits">
                            Dirancang &amp; dikembangkan oleh <strong>Phoenix Digital</strong>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12">
                        <div
                            class="flex-wrap gap-4 d-flex justify-content-lg-end justify-content-center align-items-center">
                            <div class="payment-methods">
                                <div class="payment-icons">
                                    <span class="fb-label">Pembayaran</span>
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