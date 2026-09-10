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

    /* ===== Banner: dua blok padat, bukan satu wash =====

       Gambar banner di toko ini berukuran 1254x1254 — POSTER PERSEGI yang sudah
       lengkap sendiri: judul, badge, dan ilustrasinya sudah tercetak di dalam
       gambar. Sementara itu hero menampilkan judul dan deskripsi yang sama
       persis di sebelahnya. Satu pesan tampil dua kali bersebelahan dan saling
       berebut perhatian; itulah sumber kesan monoton, bukan kurangnya hiasan.

       Karena itu pembagiannya dipertegas, bukan diperhalus:
       - poster mengisi PENUH separuh kartu sampai ke tepi, tanpa jarak dan
         tanpa pudar, sehingga ia terbaca sebagai satu blok padat;
       - sisi teks dibersihkan dari hiasan dan hanya menyisakan tiga hal:
         label, judul, tombol.

       Percobaan sebelumnya menambahkan panel melengkung, garis, dan lapisan
       titik-titik. Semuanya dicabut: menambah elemen pada bidang yang sudah
       berebut hanya menambah keramaian, dan yang diminta justru clean. */

    /* --- Poster mengisi penuh, tepinya tegas --- */
    .ph-hero .ph-hero-media {
        background: linear-gradient(135deg, #ffe9d2, #fff6ec);
        border: 0; border-radius: 0; margin: 0; padding: 0;
        /* Poster nyaris persegi dan kolomnya pun nyaris persegi, jadi cover
           hampir tidak memotong apa pun — tapi ia menghapus bingkai kosong di
           sekeliling gambar yang selama ini membuat poster tampak mengambang. */
        overflow: hidden;
    }
    .ph-hero .ph-hero-media img {
        -webkit-mask-image: none; mask-image: none;
        object-fit: cover; object-position: center;
        width: 100%; height: 100%; padding: 0;
    }

    /* Peralihan ke sisi teks dikerjakan oleh satu lapis gradien tipis DI ATAS
       poster, bukan dengan memudarkan posternya sendiri. Bedanya: poster tetap
       punya bentuk, dan hanya 14% tepinya yang melunak. */
    .ph-hero .ph-hero-media::before {
        content: ""; position: absolute; inset: 0; z-index: 2;
        background: linear-gradient(90deg, #fff6ec 0%, rgba(255, 246, 236, .55) 7%, rgba(255, 246, 236, 0) 14%);
        pointer-events: none;
    }
    /* Bola cahaya bawaan di pojok kanan atas dimatikan: di atas poster yang
       sudah penuh warna, ia hanya membuat sudutnya tampak pudar. */
    .ph-hero .ph-hero-media::after { display: none; }

    /* --- Sisi teks: tiga hal saja --- */
    .ph-hero .ph-hero-text { gap: 16px; }
    /* Deskripsi dipendekkan jadi dua baris. Isinya mengulang kalimat yang sudah
       tercetak besar-besar di posternya; membiarkannya tiga baris berarti
       memakai ruang untuk mengatakan hal yang sama tiga kali. */
    .ph-hero .ph-hero-desc { -webkit-line-clamp: 2; line-clamp: 2; }

    /* --- Chip mengambang: dua, bukan empat --- */
    /* Empat pil putih melayang di atas poster adalah unsur paling tidak rapi di
       halaman ini — mereka menutupi gambar yang justru ingin ditunjukkan.
       Disisakan dua, dan keduanya digeser ke tepi supaya tidak menimpa isi
       poster. */
    .ph-hero .ph-chip.c3, .ph-hero .ph-chip.c4 { display: none; }
    .ph-hero .ph-chip.c1 { top: 6%; right: 3%; }
    .ph-hero .ph-chip.c2 { top: auto; bottom: 8%; right: 3%; }

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
       terus-menerus bukan hiasan melainkan gangguan. */
    @media (prefers-reduced-motion: reduce) {
        .phoenix-hero-swiper::before { animation: none; }
        .ph-hero .ph-chip { animation: none; }
    }

    @media (max-width: 991.98px) {
        /* Susunan menumpuk: peralihan lembutnya pindah ke tepi BAWAH poster,
           karena di sinilah teks berada — di sisi kiri sudah tidak ada apa pun. */
        .ph-hero .ph-hero-media::before {
            background: linear-gradient(180deg, rgba(255, 246, 236, 0) 78%, #fff6ec 100%);
        }
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
