{{-- Gaya latar gambar produk, dipakai bersama oleh halaman Shop, detail
     produk, dan kartu flash sale di beranda.

     Ditaruh di blade (bukan resources/css/public-custom-styles.css) supaya ikut
     terkirim lewat git pull — public/build/ ada di .gitignore dan hanya sampai
     ke server lewat rsync terpisah. Lihat catatan aset di README. --}}
<style>
    /* ===== Latar hangat yang seragam untuk semua gambar produk =====
       Gambar yang diunggah tidak selalu sama: sebagian PNG transparan, sebagian
       membawa latar putih bawaannya sendiri (bahkan ada yang ikut membawa pola
       papan catur transparansi dari editor). mix-blend-mode: multiply membuat
       area putih pada gambar menyatu dengan latar hangat ini, sehingga semuanya
       tampil seragam tanpa perlu memperbaiki tiap berkas satu per satu. */
    .fs-card-media,
    .pd-media {
        background: radial-gradient(120% 120% at 30% 18%, #fffaf3 0%, #fff4e7 48%, #ffeeda 100%);
    }

    /* Kilau lembut di belakang logo — berdenyut pelan supaya terasa hidup.
       Dibuat sebagai pseudo-element agar tidak bentrok dengan efek perbesar
       saat kursor menyentuh gambar. */
    .fs-card-media::after,
    .pd-media::after {
        content: "";
        position: absolute;
        inset: 8%;
        border-radius: 50%;
        pointer-events: none;
        background: radial-gradient(closest-side, rgba(255, 255, 255, .9), rgba(255, 255, 255, 0) 72%);
        animation: mediaGlow 5.5s ease-in-out infinite;
        z-index: 0;
    }

    @keyframes mediaGlow {
        0%, 100% { opacity: .55; transform: scale(1); }
        50%      { opacity: .95; transform: scale(1.07); }
    }

    /* Logo: utuh (tidak terpotong), diberi napas, dan menyatu dengan latar. */
    .fs-card-media img,
    .pd-media img {
        position: relative;
        z-index: 1;
        object-fit: contain;
        mix-blend-mode: multiply;
    }

    .fs-card-media img { padding: 16px; }
    .pd-media img { padding: 26px; }

    /* Badge diskon & tombol lihat-cepat harus tetap di atas kilau. */
    .fs-card-media .fs-badge,
    .fs-card-media .fs-quickview,
    .pd-media .pd-badge { z-index: 2; }

    @media (max-width: 575.98px) {
        .fs-card-media img { padding: 11px; }
        .pd-media img { padding: 18px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .fs-card-media::after,
        .pd-media::after { animation: none; opacity: .7; transform: none; }
    }
</style>
