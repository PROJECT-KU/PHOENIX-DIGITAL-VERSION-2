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

    /* ===== Banner: poster tampil di dalam layar laptop =====

       Gambar banner di toko ini adalah POSTER LENGKAP (1254x1254): judul,
       badge, dan ilustrasinya sudah tercetak di dalam gambar. Menempelkannya
       apa adanya di samping judul hero membuat satu pesan tampil dua kali
       bersebelahan dan saling berebut — itu sumber kesan monoton sebelumnya.

       Dibingkai sebagai layar laptop, posternya berubah peran: ia bukan lagi
       pesaing judul di sebelahnya, melainkan "yang sedang tampil di layar".
       Dua hal yang tadinya bertabrakan jadi satu adegan.

       Laptopnya DILUKIS dengan CSS, bukan berkas gambar: mockup sebagai berkas
       berarti satu unduhan besar lagi dan akan ikut buram di layar retina.

       Isi layar dan seluruh teks di kiri tetap datang dari data banner yang
       diunggah admin — tidak ada satu pun yang dipatok di sini. */

    .ph-hero .ph-hero-media {
        background: none; border: 0; border-radius: 0; margin: 0;
        display: flex; align-items: center; justify-content: center;
        padding: 26px 34px 26px 10px;
    }
    .ph-hero .ph-hero-media::after { display: none; }

    .ph-laptop { width: 100%; max-width: 520px; }

    /* --- Tutup layar --- */
    .ph-laptop-layar {
        position: relative;
        background: linear-gradient(160deg, #2b313d 0%, #1b2029 60%, #232937 100%);
        border-radius: 14px 14px 6px 6px;
        padding: 12px 12px 14px;
        box-shadow:
            0 26px 50px rgba(28, 31, 38, .28),
            0 2px 0 rgba(255, 255, 255, .12) inset;
    }
    /* Kamera kecil. Tanpa ini bidang gelap di atas layar terbaca sebagai
       kesalahan jarak, bukan sebagai bingkai. */
    .ph-laptop-layar::before {
        content: ""; position: absolute; top: 5px; left: 50%; transform: translateX(-50%);
        width: 5px; height: 5px; border-radius: 50%; background: #4b5364;
    }

    .ph-laptop-isi {
        position: relative; overflow: hidden; border-radius: 5px;
        /* 16:10 — bentuk layar laptop yang paling dikenali. */
        aspect-ratio: 16 / 10;
        background: linear-gradient(135deg, #ffeeda, #fff7ef);
    }
    .ph-laptop-isi img {
        width: 100%; height: 100%; display: block;
        /* contain, BUKAN cover. Poster persegi yang dipaksa memenuhi layar 16:10
           kehilangan sekitar sepertiga tingginya — dan pada poster ini yang
           terpotong justru judul di atas dan ajakan di bawah. Lebih baik
           tersisa bidang kosong di kiri-kanan daripada memotong isinya.
           (Banner mendatar — misalnya 1600x1000 — akan memenuhi layar penuh.) */
        object-fit: contain; object-position: center;
        -webkit-mask-image: none; mask-image: none;
        padding: 0;
    }
    /* Pantulan cahaya tipis melintang layar. */
    .ph-laptop-isi::after {
        content: ""; position: absolute; inset: 0; pointer-events: none;
        background: linear-gradient(112deg, rgba(255, 255, 255, .22) 0%, rgba(255, 255, 255, 0) 34%);
    }

    /* --- Alas --- */
    .ph-laptop-alas {
        position: relative; height: 13px; margin: 0 auto;
        /* Julurannya dipatok piksel, bukan persen. Dengan 116% alas ikut
           membesar seiring layar dan di ponsel ia melebihi lebar kartunya
           sendiri — ujungnya lalu terpotong tepi kartu dan terlihat rusak. */
        width: calc(100% + 24px); max-width: none; left: -12px;
        background: linear-gradient(180deg, #d8dde6 0%, #aeb6c4 55%, #8f97a6 100%);
        border-radius: 0 0 12px 12px;
        box-shadow: 0 16px 26px rgba(28, 31, 38, .22);
    }
    /* Takik pembuka layar. */
    .ph-laptop-alas span {
        position: absolute; top: 0; left: 50%; transform: translateX(-50%);
        width: 86px; height: 5px; border-radius: 0 0 6px 6px; background: #9aa2b1;
    }

    /* --- Chip mengambang: dua, di tepi, tidak menimpa layar --- */
    .ph-hero .ph-chip.c3, .ph-hero .ph-chip.c4 { display: none; }
    .ph-hero .ph-chip.c1 { top: 8%; right: 2%; }
    .ph-hero .ph-chip.c2 { top: auto; bottom: 10%; right: 1%; }

    /* --- Sisi teks --- */
    .ph-hero .ph-hero-text { gap: 16px; }
    /* Deskripsi dua baris: isinya mengulang kalimat yang sudah tercetak
       besar-besar di posternya sendiri. */
    .ph-hero .ph-hero-desc { -webkit-line-clamp: 2; line-clamp: 2; }

    /* --- Pita aksen di puncak kartu: bergerak, bukan diam --- */
    .phoenix-hero-swiper::before {
        background: linear-gradient(90deg, #f26522, #fba919, #f26522, #fba919);
        background-size: 300% 100%;
        animation: phPitaGeser 9s linear infinite;
    }
    @keyframes phPitaGeser {
        0% { background-position: 0% 0; }
        100% { background-position: 300% 0; }
    }

    @media (prefers-reduced-motion: reduce) {
        .phoenix-hero-swiper::before { animation: none; }
        .ph-hero .ph-chip { animation: none; }
    }

    @media (max-width: 991.98px) {
        .ph-hero .ph-hero-media { padding: 24px 22px 18px; }
        .ph-laptop { max-width: 420px; }
    }
    @media (max-width: 575.98px) {
        .ph-hero .ph-hero-media { padding: 18px 16px 14px; }
        .ph-laptop-layar { padding: 8px 8px 10px; border-radius: 11px 11px 5px 5px; }
        .ph-laptop-alas { height: 10px; width: calc(100% + 16px); left: -8px; }
        .ph-laptop-alas span { width: 62px; height: 4px; }
    }

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

<section id="hero" class="ph-hero section">
    <div class="container">
        {{-- Chip mengambang (ala flip.id) untuk mengisi area kosong --}}
        <div class="ph-hero-deco" aria-hidden="true">
            <span class="ph-chip c1"><span class="ph-chip-ic" style="--c:#f59e0b"><i class="bi bi-star-fill"></i></span> <b>4.9</b>&nbsp;Rating</span>
            <span class="ph-chip c2"><span class="ph-chip-ic" style="--c:#16a34a"><i class="bi bi-shield-check"></i></span> Akun Resmi &amp; Aman</span>
            <span class="ph-chip c3"><span class="ph-chip-ic" style="--c:#f26522"><i class="bi bi-lightning-charge-fill"></i></span> Proses Instan</span>
            <span class="ph-chip c4"><span class="ph-chip-ic" style="--c:#7c3aed"><i class="bi bi-emoji-smile-fill"></i></span> 5.000+ Pelanggan</span>
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
                            <div class="ph-laptop">
                                <div class="ph-laptop-layar">
                                    <div class="ph-laptop-isi">
                                        <img src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                            alt="{{ $banner->judul ?? 'Banner' }}"
                                            @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                                    </div>
                                </div>
                                <div class="ph-laptop-alas" aria-hidden="true"><span></span></div>
                            </div>
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
