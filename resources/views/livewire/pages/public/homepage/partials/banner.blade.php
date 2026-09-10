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

    /* ===== Aksen jingga pada ekor judul =====
       Judul hero satu-satunya tulisan sebesar itu di halaman; membiarkannya
       satu warna membuat mata membacanya sebagai balok, bukan sebagai kalimat
       dengan penekanan. */
    .ph-hero-title .ph-aksen { color: #f26522; }

    /* ===== Baris kepercayaan =====
       Sebelumnya tiga kartu berbingkai sendiri-sendiri, terpisah dari hero dan
       terbaca sebagai bagian keempat halaman. Padahal isinya bukan bagian —
       melainkan keterangan kaki hero. Dijadikan datar tanpa bingkai supaya
       menempel pada hero di atasnya, dan butirnya digenapkan jadi empat agar
       tidak ada ruang menganga di ujung baris.

       Ditulis dengan kekhususan tinggi karena aturan berkartu yang lama ada di
       public-custom-styles.css, dan salinan berkas itu di server beku sejak 19
       Agustus — tidak bisa disunting lewat git pull. */
    .ph-hero .ph-hero-trust {
        display: flex; flex-wrap: wrap; gap: 14px 38px;
        background: none; border: 0; box-shadow: none; padding: 4px 0 0; margin: 14px 0 0;
    }
    .ph-hero .ph-hero-trust .ph-trust-item {
        flex: 1 1 190px; min-width: 0;
        display: flex; align-items: center; gap: 12px;
        background: none; border: 0; box-shadow: none; padding: 0;
    }
    .ph-hero .ph-hero-trust .ph-trust-ico {
        flex: 0 0 auto; width: 38px; height: 38px; border-radius: 11px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fff2ea; color: #f26522; font-size: 1.05rem;
    }
    /* !important terpaksa dipakai: aturan lama .ph-trust-ico i memakai
       `color: #fff !important` untuk latar gradient jingga. Latarnya kini
       lembut, jadi glif putih di atasnya menghilang sama sekali. */
    .ph-hero .ph-hero-trust .ph-trust-ico i.bi { line-height: 1; color: #f26522 !important; }
    .ph-hero .ph-hero-trust .ph-trust-item > div { display: flex; flex-direction: column; min-width: 0; }
    .ph-hero .ph-hero-trust .ph-trust-item strong {
        font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .88rem;
        color: #1c1f26; line-height: 1.25;
    }
    .ph-hero .ph-hero-trust .ph-trust-item span {
        font-size: .78rem; color: #8b94a3; line-height: 1.35;
    }

    @media (max-width: 575.98px) {
        .ph-hero .ph-hero-trust { gap: 14px 20px; }
        .ph-hero .ph-hero-trust .ph-trust-item { flex: 1 1 44%; }
    }

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

        {{-- Baris kepercayaan (konsisten berapa pun jumlah banner) --}}
        <div class="ph-hero-trust" data-aos="fade-up" data-aos-delay="100">
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-lightning-charge"></i></span>
                <div><strong>Proses Instan</strong><span>Langsung aktif</span></div>
            </div>
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-shield-check"></i></span>
                <div><strong>Aman &amp; Terpercaya</strong><span>Garansi uang kembali</span></div>
            </div>
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-headset"></i></span>
                <div><strong>Bantuan 24/7</strong><span>Siap membantu</span></div>
            </div>
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-people-fill"></i></span>
                <div><strong>5.000+ Pelanggan</strong><span>Telah bergabung</span></div>
            </div>
        </div>
    </div>
</section>
