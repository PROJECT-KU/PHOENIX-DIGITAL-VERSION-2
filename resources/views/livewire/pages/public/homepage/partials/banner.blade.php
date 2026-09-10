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

    /* ===== Banner diberi STRUKTUR =====

       Keluhannya: banner terlihat monoton. Sebabnya bukan warnanya kurang,
       melainkan tidak ada satu pun BENTUK yang tegas di dalamnya. Seluruh kartu
       memakai satu wash krem yang sama, dan tepi kiri gambar sengaja dibuat
       memudar hilang ke latar — jadi mata tidak menemukan batas apa pun untuk
       dipegang, dan yang tersisa hanya bidang lebar berwarna sama.

       Yang diperbaiki: sisi gambar diberi panelnya sendiri dengan tepi yang
       terlihat, sisi teks diberi kedalaman tipis, dan pita aksen di puncak
       kartu dibuat bergerak. Bukan menambah warna — menambah bentuk. */

    /* --- Sisi gambar: panel tersendiri, bukan bidang yang memudar --- */
    .ph-hero .ph-hero-media {
        /* Lebih pekat daripada sisi teks. Perbedaan inilah yang membelah kartu
           jadi dua bagian yang jelas; sebelumnya keduanya nyaris sewarna. */
        background:
            radial-gradient(90% 90% at 78% 18%, rgba(255, 255, 255, .92) 0%, rgba(255, 255, 255, 0) 58%),
            linear-gradient(135deg, #ffdfc0 0%, #ffeed9 52%, #fff6ec 100%);
        border-radius: 34px 0 0 34px;
        margin: 14px 0 14px 0;
        border: 1px solid rgba(242, 101, 34, .10);
        border-right: 0;
    }

    /* Topeng pemudar dilepas. Ia dulu dipakai supaya gambar menyatu dengan latar
       — dan itu persis yang membuat kartunya kehilangan bentuk. Sekarang gambar
       duduk DI ATAS panel, jadi batasnya memang harus terlihat. */
    .ph-hero .ph-hero-media img {
        -webkit-mask-image: none; mask-image: none;
        padding: 10px 14px;
    }

    /* --- Sisi teks: kedalaman tipis, bukan bidang kosong --- */
    .ph-hero .ph-hero-slide { position: relative; }
    .ph-hero .ph-hero-text { position: relative; z-index: 1; }
    .ph-hero .ph-hero-slide::before {
        content: "";
        position: absolute; left: 0; top: 0; bottom: 0; width: 52%;
        /* Titik-titik sangat samar. Cukup untuk membuat bidang krem terasa
           punya permukaan, tidak cukup untuk terbaca sebagai pola. */
        background-image: radial-gradient(rgba(242, 101, 34, .12) 1px, transparent 1px);
        background-size: 22px 22px;
        -webkit-mask-image: radial-gradient(120% 90% at 6% 12%, #000 0%, transparent 62%);
        mask-image: radial-gradient(120% 90% at 6% 12%, #000 0%, transparent 62%);
        pointer-events: none;
    }

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

    /* Gerakan selalu bisa dimatikan. Bagi sebagian orang animasi yang berjalan
       terus-menerus bukan hiasan, melainkan gangguan yang membuat halaman sulit
       dibaca — dan itu berlaku untuk chip mengambang juga. */
    @media (prefers-reduced-motion: reduce) {
        .phoenix-hero-swiper::before { animation: none; }
        .ph-hero .ph-chip { animation: none; }
    }

    @media (max-width: 991.98px) {
        /* Tata letaknya menumpuk: gambar pindah ke atas, jadi lengkungannya
           ikut pindah ke sisi atas. Lengkung di kiri pada susunan bertumpuk
           hanya terlihat seperti salah pasang. */
        .ph-hero .ph-hero-media {
            border-radius: 26px 26px 0 0;
            margin: 12px 12px 0;
            border-right: 1px solid rgba(242, 101, 34, .10);
            border-bottom: 0;
        }
        .ph-hero .ph-hero-slide::before { display: none; }
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
                            <img src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                alt="{{ $banner->judul ?? 'Banner' }}"
                                @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
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
