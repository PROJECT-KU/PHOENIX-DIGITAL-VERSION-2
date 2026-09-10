@php
    $multiBanner = $banners->count() > 1;

    /**
     * Menyorot EKOR judul dengan warna jingga.
     *
     * Judul hero disimpan admin sebagai satu kalimat utuh, jadi tidak ada
     * medan terpisah untuk "bagian yang diwarnai". Aturannya dibuat dapat
     * ditebak: yang disorot adalah bagian setelah koma terakhir — itu memang
     * bagian yang menjanjikan sesuatu ("…, Gratis & Langsung Untung!").
     * Bila tidak ada koma, dua kata terakhir yang disorot.
     *
     * Selalu mengembalikan HTML yang sudah di-escape: judulnya diketik manusia
     * lewat panel admin, dan tidak ada alasan mempercayainya mentah-mentah.
     */
    $aksenJudul = function (?string $judul) {
        $judul = trim((string) $judul);

        if ($judul === '') {
            return '';
        }

        if (str_contains($judul, ',')) {
            $depan = Str::beforeLast($judul, ',').',';
            $ekor = trim(Str::afterLast($judul, ','));
        } else {
            $kata = preg_split('/\s+/', $judul) ?: [];

            // Judul sangat pendek tidak dipotong: menyorot dua dari tiga kata
            // membuat warnanya terlihat asal, bukan disengaja.
            if (count($kata) < 4) {
                return e($judul);
            }

            $depan = implode(' ', array_slice($kata, 0, -2));
            $ekor = implode(' ', array_slice($kata, -2));
        }

        return e($depan).' <span class="ph-aksen">'.e($ekor).'</span>';
    };
@endphp

<style>
    /* Ditulis inline, bukan di resources/css/public-custom-styles.css: berkas itu
       dikompilasi Vite ke public/build, yang MASUK .gitignore dan tidak ikut
       terdeploy — salinan di server masih tertanggal 19 Agustus. Aturan yang
       ditulis di sana tidak akan pernah sampai ke pengunjung lewat git pull. */

    /* Chip mengambang hanya masuk akal saat hero berdampingan dengan kartunya:
       chip mengisi ruang kosong di sekelilingnya. Begitu tata letaknya menumpuk
       (di bawah 992px) ruang kosong itu hilang, dan chip hanya bisa mendarat DI
       ATAS tulisan — di tablet, "Proses Instan" menutupi kata "Checkout" pada
       judul hero. Seluruh lapisannya disembunyikan, bukan sebagian. */
    @media (max-width: 991.98px) {
        .ph-hero-deco { display: none !important; }
    }

    /* ===== Banner: poster sebagai kartu, katalog sebagai warna =====

       Sebelumnya poster dikurung dalam bingkai laptop. Poster ini berbentuk
       PERSEGI sementara layar laptop mendatar, jadi selalu tersisa bilah kosong
       di kiri-kanan — dan bingkai di dalam bingkai membuatnya tampil dua
       tingkat lebih kecil daripada seharusnya.

       Sebagai kartu persegi ia memakai seluruh ruang yang ada, dan bingkainya
       hilang. Kedalamannya datang dari bayangan dan satu kartu bayangan di
       belakang, bukan dari benda yang digambar mengelilinginya.

       Warna hero tidak lagi berasal dari hiasan buatan, melainkan dari LOGO
       PRODUK yang benar-benar dijual. "5.000+ pelanggan" adalah klaim yang
       harus dipercaya begitu saja; logo Scopus dan ChatGPT adalah bukti yang
       langsung dikenali. */

    /* --- Latar: dua bola cahaya, bukan satu bidang rata --- */
    .ph-hero .ph-hero-slide {
        position: relative; overflow: hidden;
        background:
            radial-gradient(58% 78% at 88% 18%, rgba(251, 169, 25, .26) 0%, rgba(251, 169, 25, 0) 62%),
            radial-gradient(52% 70% at 60% 96%, rgba(242, 101, 34, .18) 0%, rgba(242, 101, 34, 0) 60%),
            linear-gradient(118deg, #fffaf4 0%, #fff3e6 52%, #fffaf5 100%);
    }

    /* --- Kolom gambar --- */
    .ph-hero .ph-hero-media {
        background: none; border: 0; border-radius: 0; margin: 0;
        flex: 1 1 54%;
        display: flex; align-items: center; justify-content: center;
        padding: 34px 34px 34px 0; position: relative; overflow: visible;
    }
    .ph-hero .ph-hero-media::before, .ph-hero .ph-hero-media::after { display: none; }
    .ph-hero .ph-hero-text { flex: 1 1 46%; padding: 44px 26px 44px 50px; }

    /* --- Poster --- */
    .ph-poster {
        position: relative; z-index: 1; margin: 0;
        width: 100%; max-width: 430px; aspect-ratio: 1;
        border-radius: 22px; overflow: hidden; background: #fff;
        /* Bayangan berlapis: satu rapat untuk tepi, satu lebar dan hangat untuk
           jarak. Satu bayangan saja selalu terbaca sebagai stiker. */
        box-shadow:
            0 2px 6px rgba(120, 60, 20, .08),
            0 26px 60px rgba(120, 60, 20, .20);
        transform: rotate(-2.5deg);
    }
    .ph-poster img {
        width: 100%; height: 100%; object-fit: cover; display: block;
        -webkit-mask-image: none; mask-image: none; padding: 0;
    }
    /* Kartu di belakang berisi banner BERIKUTNYA, bukan bidang putih kosong.
       Bidang kosong hanya hiasan yang berpura-pura jadi tumpukan; berisi promo
       yang sungguh menyusul, ia mulai memberi tahu sesuatu. */
    .ph-poster::after {
        content: ""; position: absolute; inset: 0; z-index: -1;
        border-radius: 22px;
        background: #fff var(--berikut, none) center / cover no-repeat;
        transform: rotate(5deg) translate(14px, 8px);
        box-shadow: 0 18px 40px rgba(120, 60, 20, .14);
        /* Diredupkan supaya jelas ia yang di BELAKANG; tanpa ini dua poster
           berwarna penuh saling berebut dan tumpukannya terbaca berantakan. */
        filter: brightness(1.06) saturate(.35); opacity: .55;
    }

    /* Posternya bisa diklik — ia sedang menawarkan sesuatu, jadi wajar kalau
       menuju ke suatu tempat. Angkatnya halus: poster sebesar ini kalau
       melompat saat disentuh justru terasa murah. */
    .ph-poster-tautan {
        display: block; text-decoration: none;
        transition: transform .35s cubic-bezier(.2, .7, .3, 1);
    }
    .ph-poster-tautan:hover { transform: translateY(-6px); }
    .ph-poster-tautan:hover .ph-poster {
        box-shadow: 0 4px 10px rgba(120, 60, 20, .10), 0 34px 70px rgba(120, 60, 20, .26);
    }
    .ph-poster { transition: box-shadow .35s ease; }

    /* --- Butiran halus di seluruh kartu ---
       Gradien lebar selalu memperlihatkan pita-pita warna di layar 8-bit.
       Satu lapis butiran sangat samar menghapusnya, sekaligus memberi permukaan
       yang terasa "ada" — inilah yang membedakan gradien buatan cepat dari
       gradien yang digarap. Digambar SVG, bukan berkas gambar. */
    .ph-hero .ph-hero-slide::after {
        content: ""; position: absolute; inset: 0; z-index: 1; pointer-events: none;
        opacity: .35; mix-blend-mode: multiply;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)' opacity='.5'/%3E%3C/svg%3E");
    }
    .ph-hero .ph-hero-text, .ph-hero .ph-hero-media { position: relative; z-index: 2; }

    /* --- Teks masuk bertahap ---
       Bukan demi animasi. Urutan munculnya memberi tahu mata urutan membacanya:
       label dulu, judul, keterangan, baru ajakan. Muncul serentak menyerahkan
       urutan itu kepada kebetulan. */
    @keyframes phNaik {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: none; }
    }
    .ph-hero .swiper-slide-active .ph-hero-text > * {
        animation: phNaik .62s cubic-bezier(.2, .7, .3, 1) both;
    }
    .ph-hero .swiper-slide-active .ph-hero-text > *:nth-child(1) { animation-delay: .05s; }
    .ph-hero .swiper-slide-active .ph-hero-text > *:nth-child(2) { animation-delay: .13s; }
    .ph-hero .swiper-slide-active .ph-hero-text > *:nth-child(3) { animation-delay: .21s; }
    .ph-hero .swiper-slide-active .ph-hero-text > *:nth-child(4) { animation-delay: .29s; }
    .ph-hero .swiper-slide-active .ph-hero-text > *:nth-child(5) { animation-delay: .37s; }

    /* --- Penunjuk jalannya promo ---
       Titik-titik kecil di dasar kartu praktis tak terlihat, jadi sebagian
       besar pengunjung tidak pernah tahu ada promo kedua. Diganti bilah tipis
       yang MENGISI seiring waktu tayang: ia memberi tahu ada yang menyusul
       sekaligus kapan gantinya. */
    .phoenix-hero-swiper .swiper-pagination-progressbar {
        top: 0; left: 0; height: 4px; background: rgba(242, 101, 34, .14); z-index: 6;
    }
    .phoenix-hero-swiper .swiper-pagination-progressbar .swiper-pagination-progressbar-fill {
        background: linear-gradient(90deg, #f26522, #fba919);
    }

    @media (prefers-reduced-motion: reduce) {
        .ph-hero .swiper-slide-active .ph-hero-text > * { animation: none; opacity: 1; transform: none; }
        .ph-poster-tautan { transition: none; }
    }

    /* --- Ubin produk sungguhan --- */
    .ph-hero-deco { pointer-events: none; }
    .ph-ubin {
        position: absolute; z-index: 5;
        display: flex; align-items: center; gap: 9px;
        background: rgba(255, 255, 255, .94);
        -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, .9); border-radius: 14px;
        padding: 9px 14px 9px 9px; white-space: nowrap;
        box-shadow: 0 10px 30px rgba(35, 39, 47, .10), 0 2px 6px rgba(35, 39, 47, .05);
        animation: phChipFloat 6s ease-in-out infinite;
    }
    .ph-ubin img { width: 30px; height: 30px; object-fit: contain; border-radius: 8px; }
    .ph-ubin span {
        font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .84rem; color: #1c1f26;
    }
    /* Ditaruh menempel tepi poster, bukan menimpa tengahnya: yang ingin
       ditunjukkan tetap posternya. */
    .ph-ubin-1 { top: 14%; left: 44%; animation-delay: 0s; }
    .ph-ubin-2 { top: 46%; right: 3%; animation-delay: 1.1s; }
    .ph-ubin-3 { bottom: 12%; left: 48%; animation-delay: 2.2s; }

    /* --- Tulisan tangan --- */
    .ph-hero .ph-tulisan {
        position: absolute; z-index: 5; top: 4%; right: 1%; width: 170px;
        color: #f26522; text-align: center; transform: rotate(-6deg);
    }
    .ph-hero .ph-tulisan span {
        display: block;
        font-family: 'Caveat', 'Segoe Script', 'Bradley Hand', cursive;
        font-weight: 700; font-size: 1.5rem; line-height: 1.15;
    }
    .ph-hero .ph-tulisan svg {
        display: block; width: 58px; height: auto; margin: 2px 0 0 auto; transform: rotate(6deg);
    }

    @media (max-width: 1199.98px) {
        .ph-ubin-3 { display: none; }
        .ph-hero .ph-tulisan { display: none; }
    }

    /* Pita pelangi bergeser di puncak kartu dicabut. Ia bergerak terus tanpa
       menyampaikan apa pun — gerakan yang tidak berarti hanya menarik mata
       menjauh dari isinya. Tempatnya diambil bilah kemajuan promo, yang
       bergerak karena memang ada yang sedang berjalan. */
    .phoenix-hero-swiper::before { display: none; }

    @media (prefers-reduced-motion: reduce) {
        .ph-ubin { animation: none; }
    }

    /* --- Sisi teks --- */
    .ph-hero .ph-hero-text { gap: 18px; }
    .ph-hero .ph-hero-title { letter-spacing: -.03em; line-height: 1.04; }
    .ph-hero .ph-hero-desc { font-size: 1rem; line-height: 1.65; color: #7b8493; -webkit-line-clamp: 2; line-clamp: 2; }
    .ph-hero .ph-hero-eyebrow {
        font-size: .72rem; letter-spacing: .14em; padding: 6px 13px;
        background: #fff; border-color: #f3ddcc;
    }
    .ph-hero .ph-hero-actions { gap: 8px; align-items: center; margin-top: 4px; }
    .ph-hero .ph-btn-primary { font-size: 1.02rem; padding: 15px 32px; }
    .ph-hero .ph-btn-ghost {
        background: none; border: 0; padding: 15px 18px;
        font-size: 1rem; color: #6b7280 !important; box-shadow: none;
    }
    .ph-hero .ph-btn-ghost:hover {
        background: none; color: #f26522 !important; text-decoration: underline; text-underline-offset: 4px;
    }

    /* --- Jaminan di kaki hero --- */
    .ph-hero .ph-jaminan {
        display: flex; flex-wrap: wrap; gap: 12px 26px; margin-top: 14px;
        padding-top: 20px; border-top: 1px solid rgba(242, 101, 34, .12);
    }
    .ph-hero .ph-jaminan-butir { flex: 1 1 176px; min-width: 0; display: flex; align-items: center; gap: 9px; }
    .ph-hero .ph-jaminan-ikon {
        flex: 0 0 auto; width: 28px; height: 28px; border-radius: 9px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff2ea; font-size: .82rem;
    }
    .ph-hero .ph-jaminan-ikon i.bi { line-height: 1; color: #f26522 !important; }
    .ph-hero .ph-jaminan-butir > span { display: flex; flex-direction: column; min-width: 0; }
    .ph-hero .ph-jaminan-butir strong {
        font-family: 'Poppins', sans-serif; font-weight: 600; font-size: .8rem;
        color: #3f4652; line-height: 1.25;
    }
    .ph-hero .ph-jaminan-butir small { font-size: .72rem; color: #a0a8b4; line-height: 1.35; }

    @media (max-width: 991.98px) {
        .ph-hero .ph-hero-media { padding: 26px 22px 10px; }
        .ph-hero .ph-hero-text { padding: 28px 24px 32px; }
        .ph-poster { max-width: 340px; transform: rotate(-2deg); }
    }
    @media (max-width: 575.98px) {
        .ph-hero .ph-hero-media { padding: 20px 18px 6px; }
        .ph-hero .ph-hero-text { padding: 24px 20px 28px; }
        .ph-poster { max-width: 280px; }
        .ph-hero .ph-btn-ghost { padding: 12px 4px; }
        .ph-hero .ph-jaminan { gap: 12px 18px; }
        .ph-hero .ph-jaminan-butir { flex: 1 1 44%; }
    }

    /* ===== GAYA PRATINJAU (hanya aktif lewat ?gaya=) =====
       Markupnya sama persis untuk ketiganya; yang berbeda hanya warna. Tanpa
       ?gaya= di alamat, halaman tampil seperti biasa. Sakelar ini SEMENTARA —
       begitu satu gaya dipilih, dua sisanya dibuang. */

    /* --- Gaya 2: gelap & tegas --- */
    .ph-gaya-2 .phoenix-hero-swiper { border-color: #232937; }
    .ph-gaya-2 .ph-hero-slide {
        background:
            radial-gradient(58% 78% at 88% 18%, rgba(251, 169, 25, .20) 0%, rgba(251, 169, 25, 0) 62%),
            radial-gradient(52% 70% at 60% 96%, rgba(242, 101, 34, .22) 0%, rgba(242, 101, 34, 0) 60%),
            linear-gradient(120deg, #1b2029 0%, #232937 58%, #1b2029 100%);
    }
    .ph-gaya-2 .ph-hero-title { color: #fff; }
    .ph-gaya-2 .ph-hero-title .ph-aksen { color: #fba919; }
    .ph-gaya-2 .ph-hero-desc { color: rgba(255, 255, 255, .68); }
    .ph-gaya-2 .ph-hero-eyebrow {
        background: rgba(242, 101, 34, .16); border-color: rgba(242, 101, 34, .38); color: #fba919;
    }
    .ph-gaya-2 .ph-btn-ghost { color: rgba(255, 255, 255, .72) !important; }
    .ph-gaya-2 .ph-btn-ghost:hover { color: #fba919 !important; }
    .ph-gaya-2 .ph-jaminan { border-top-color: rgba(255, 255, 255, .12); }
    .ph-gaya-2 .ph-jaminan-ikon { background: rgba(242, 101, 34, .18); }
    .ph-gaya-2 .ph-jaminan-ikon i.bi { color: #fba919 !important; }
    .ph-gaya-2 .ph-jaminan-butir strong { color: rgba(255, 255, 255, .92); }
    .ph-gaya-2 .ph-jaminan-butir small { color: rgba(255, 255, 255, .48); }
    /* Poster jadi satu-satunya bidang terang di kartu gelap — ia praktis
       menyala sendiri, dan ke situlah mata jatuh lebih dulu. */
    .ph-gaya-2 .ph-poster { box-shadow: 0 2px 6px rgba(0, 0, 0, .3), 0 30px 64px rgba(0, 0, 0, .48); }
    .ph-gaya-2 .ph-poster::after { background: rgba(255, 255, 255, .10); box-shadow: none; }
    .ph-gaya-2 .ph-tulisan { color: #fba919; }

    /* --- Gaya 3: jingga penuh, warna merek --- */
    .ph-gaya-3 .phoenix-hero-swiper { border-color: #e0571b; }
    .ph-gaya-3 .ph-hero-slide {
        background:
            radial-gradient(110% 110% at 92% 8%, rgba(255, 255, 255, .28) 0%, rgba(255, 255, 255, 0) 55%),
            linear-gradient(122deg, #f26522 0%, #fb8b3c 54%, #fba919 100%);
    }
    .ph-gaya-3 .ph-hero-title { color: #fff; }
    .ph-gaya-3 .ph-hero-title .ph-aksen { color: #23272f; }
    .ph-gaya-3 .ph-hero-desc { color: rgba(255, 255, 255, .88); }
    .ph-gaya-3 .ph-hero-eyebrow {
        background: rgba(255, 255, 255, .18); border-color: rgba(255, 255, 255, .42); color: #fff;
    }
    /* Tombol utama dibalik jadi putih. Tombol jingga di atas latar jingga
       hilang sama sekali — kesalahan paling sering pada hero berwarna. */
    .ph-gaya-3 .ph-btn-primary {
        background: #fff; color: #d9531a !important; box-shadow: 0 12px 28px rgba(120, 45, 8, .28);
    }
    .ph-gaya-3 .ph-btn-ghost { color: rgba(255, 255, 255, .85) !important; }
    .ph-gaya-3 .ph-btn-ghost:hover { color: #fff !important; }
    .ph-gaya-3 .ph-jaminan { border-top-color: rgba(255, 255, 255, .28); }
    .ph-gaya-3 .ph-jaminan-ikon { background: rgba(255, 255, 255, .22); }
    .ph-gaya-3 .ph-jaminan-ikon i.bi { color: #fff !important; }
    .ph-gaya-3 .ph-jaminan-butir strong { color: #fff; }
    .ph-gaya-3 .ph-jaminan-butir small { color: rgba(255, 255, 255, .78); }
    .ph-gaya-3 .ph-poster { box-shadow: 0 2px 6px rgba(120, 45, 8, .2), 0 30px 60px rgba(120, 45, 8, .38); }
    .ph-gaya-3 .ph-tulisan { color: #fff; }
    .ph-gaya-3 .phoenix-hero-swiper::before { display: none; }

    /* ===== Aksen jingga pada ekor judul =====
       Judul hero satu-satunya tulisan sebesar itu di halaman; membiarkannya
       satu warna membuat mata membacanya sebagai balok, bukan sebagai kalimat
       dengan penekanan. */
    .ph-hero-title .ph-aksen { color: #f26522; }

    /* Hanya jarak luar bagian hero yang dirapatkan. Padding di dalam slide
       SENGAJA tidak disentuh: slide diatur Swiper dengan lebar tetap, dan
       mengubah paddingnya membuat teks meluber keluar lalu terpotong tepi. */
    @media (max-width: 991.98px) {
        .ph-hero.section { padding: 14px 0 10px; }
    }
</style>

@php
    // Sakelar PRATINJAU, sementara. Tanpa ?gaya= di alamat, halaman tampil
    // persis seperti biasa — jadi pengunjung tidak pernah melihat apa pun yang
    // berubah. Dipakai supaya pemilik toko bisa membandingkan tiga arah desain
    // langsung di halaman aslinya, bukan lewat gambar contoh yang belum tentu
    // sama dengan hasil jadinya. Dihapus begitu satu gaya dipilih.
    $gaya = in_array(request('gaya'), ['2', '3'], true) ? 'ph-gaya-'.request('gaya') : '';
@endphp

<section id="hero" class="ph-hero section {{ $gaya }}">
    <div class="container">
        {{-- Chip mengambang (ala flip.id) untuk mengisi area kosong --}}
        {{-- Lapis mengambang.

             Sebelumnya berisi kartu angka, kartu nilai, dan kartu daftar —
             tiga kotak berisi klaim tentang diri sendiri. Diganti UBIN PRODUK
             SUNGGUHAN: logo yang benar-benar dijual di toko ini.

             Alasannya sederhana. "5.000+ pelanggan" adalah klaim yang harus
             dipercaya begitu saja; logo Scopus dan ChatGPT adalah bukti yang
             langsung dikenali. Dan logo-logo itu memberi warna pada hero tanpa
             satu pun hiasan buatan — warnanya datang dari barang dagangan.

             Dibaca dari katalog lewat MerekDipercaya, jadi produk yang dijeda
             atau dihapus hilang sendiri dari sini. --}}
        @php $ubin = App\Support\MerekDipercaya::ambil(3); @endphp

        <div class="ph-hero-deco" aria-hidden="true">
            @foreach ($ubin as $i => $u)
            <div class="ph-ubin ph-ubin-{{ $i + 1 }}">
                <img loading="lazy" src="{{ asset('storage/img/Product/'.$u['gambar']) }}" alt="">
                <span>{{ $u['nama'] }}</span>
            </div>
            @endforeach

            {{-- Tulisan tangan tetap: satu-satunya unsur bernada manusia di
                 hero yang selebihnya rapi dan geometris. --}}
            <div class="ph-tulisan">
                <span>Upgrade Produktivitasmu Sekarang!</span>
                <svg viewBox="0 0 88 54" fill="none" aria-hidden="true">
                    <path d="M79 5c3 16-4 30-18 37C46 49 28 46 15 36"
                          stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
                    <path d="M8 41c2.6-2.4 5-4 8-5.2M21 28c-3 3-5 5.6-6.6 8.6"
                          stroke="currentColor" stroke-width="3.4" stroke-linecap="round"/>
                </svg>
            </div>
        </div>

        <div class="swiper phoenix-hero-swiper" data-aos="fade-up" data-multi="{{ $multiBanner ? '1' : '0' }}">
            <div class="swiper-wrapper">
                @forelse ($banners as $banner)
                <div class="swiper-slide">
                    <article class="ph-hero-slide">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-award-fill"></i> Akun Premium, Lisensi &amp; Tools AI</span>
                            <h1 class="ph-hero-title">{!! $aksenJudul($banner->judul) !!}</h1>
                            @if ($banner->deskripsi)
                            <p class="ph-hero-desc">{{ $banner->deskripsi }}</p>
                            @endif
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">
                                    Belanja Sekarang <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>

                            <div class="ph-jaminan">
                                @foreach ([
                                    ['bi-lightning-charge-fill', 'Proses Instan', 'Langsung aktif'],
                                    ['bi-shield-check', 'Aman &amp; Terpercaya', 'Garansi uang kembali'],
                                    ['bi-headset', 'Bantuan 24/7', 'Siap membantu'],
                                    ['bi-people-fill', '5.000+ Pelanggan', 'Telah bergabung'],
                                ] as [$ikon, $judulJ, $subJ])
                                <div class="ph-jaminan-butir">
                                    <span class="ph-jaminan-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span>
                                        <strong>{!! $judulJ !!}</strong>
                                        <small>{{ $subJ }}</small>
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="ph-hero-media">
                            {{-- Slide PERTAMA sengaja TIDAK lazy.

                                 Ia adalah gambar terbesar yang langsung terlihat
                                 saat halaman dibuka, jadi menundanya justru
                                 memperlambat kesan halaman terbuka — kebalikan
                                 dari tujuan lazy loading. fetchpriority="high"
                                 menyuruh browser mengambilnya lebih dulu di
                                 antara semua unduhan.

                                 Slide kedua dan seterusnya belum terlihat sampai
                                 pengunjung menggeser, jadi itu yang di-lazy. --}}
                            {{-- Laptop digambar dengan CSS, bukan berkas gambar.
                                 Mockup sebagai berkas berarti satu unduhan besar
                                 lagi, dan ia akan ikut buram di layar retina.
                                 Bentuk sesederhana ini lebih baik dilukis. --}}
                            @php
                                // Banner BERIKUTNYA dipakai sebagai kartu di
                                // belakang. Sebelumnya kartu itu putih kosong —
                                // hiasan yang berpura-pura jadi tumpukan. Diisi
                                // promo yang sungguh menyusul, ia berhenti jadi
                                // hiasan dan mulai memberi tahu sesuatu: masih
                                // ada satu promo lagi di balik yang ini.
                                $berikut = $banners->count() > 1
                                    ? $banners[($loop->index + 1) % $banners->count()]
                                    : null;
                            @endphp

                            {{-- Poster sebagai KARTU, bukan di dalam bingkai.
                                 Poster ini persegi; bingkai apa pun yang
                                 mendatar akan menyisakan bilah kosong di
                                 kiri-kanan dan mengecilkannya dua tingkat. --}}
                            <a class="ph-poster-tautan" href="{{ route('shop.index') }}"
                               aria-label="{{ $banner->judul ?? 'Lihat promo' }}">
                                <figure class="ph-poster"
                                    @if ($berikut) style="--berikut: url('{{ asset('storage/img/banners/'.$berikut->gambar) }}')" @endif>
                                    <img src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                        alt="{{ $banner->judul ?? 'Banner' }}"
                                        @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                </figure>
                            </a>
                        </div>
                    </article>
                </div>
                @empty
                <div class="swiper-slide">
                    <article class="ph-hero-slide ph-hero-slide--empty">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-award-fill"></i> Akun Premium, Lisensi &amp; Tools AI</span>
                            <h1 class="ph-hero-title">Solusi Akun &amp; Lisensi <span class="ph-aksen">Digital Terpercaya</span></h1>
                            <p class="ph-hero-desc">Akun premium, lisensi, dan tools AI untuk riset &amp; produktivitas — proses cepat dan bergaransi.</p>
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">Belanja Sekarang <i class="bi bi-arrow-right"></i></a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>

                            <div class="ph-jaminan">
                                @foreach ([
                                    ['bi-lightning-charge-fill', 'Proses Instan', 'Langsung aktif'],
                                    ['bi-shield-check', 'Aman &amp; Terpercaya', 'Garansi uang kembali'],
                                    ['bi-headset', 'Bantuan 24/7', 'Siap membantu'],
                                    ['bi-people-fill', '5.000+ Pelanggan', 'Telah bergabung'],
                                ] as [$ikon, $judulJ, $subJ])
                                <div class="ph-jaminan-butir">
                                    <span class="ph-jaminan-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span>
                                        <strong>{!! $judulJ !!}</strong>
                                        <small>{{ $subJ }}</small>
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="ph-hero-media ph-hero-media--empty">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </article>
                </div>
                @endforelse
            </div>

            @if ($multiBanner)
            <div class="swiper-pagination"></div>
            @endif
        </div>

    </div>
</section>
