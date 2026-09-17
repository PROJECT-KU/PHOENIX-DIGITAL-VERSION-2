{{-- Gaya khusus layar Pemesanan RSC.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     memang tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Saringan ===== */
    .rsc-saring { display: flex; flex-wrap: wrap; gap: 12px; }
    .rsc-saring > .dsb-medan { flex: 1 1 180px; }
    .rsc-medan-cari { flex: 2 1 320px !important; }
    @media (max-width: 575.98px) {
        .rsc-saring > .dsb-medan, .rsc-medan-cari { flex: 1 1 100% !important; }
    }
    .rsc-saring-kaki {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
        margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
    }
    .rsc-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }
    @media (max-width: 575.98px) {
        .rsc-saring-kaki { flex-direction: column; align-items: stretch; }
    }

    /* ===== Tabel ===== */
    /* Kolom pertama adalah tautan ke detail, tetapi tidak tampil seperti
       tautan biru bergaris: seluruh sel menjadi sasaran klik, dan warnanya
       tetap warna judul. */
    .rsc-tautan-baris { text-decoration: none; color: inherit; }
    @media (hover: hover) and (pointer: fine) {
        .rsc-tautan-baris:hover .dsb-tabel-judul { color: #6d28d9; }
    }
    .rsc-akun { display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .rsc-total { font-weight: 800; color: var(--dsb-tinta); }
    .rsc-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
    .rsc-halaman .pagination { margin: 0; justify-content: center; flex-wrap: wrap; }

    /* ===== Jendela unduh: daftar batch yang bisa dicentang ===== */
    .rsc-pilih-batch {
        display: flex; flex-direction: column; gap: 6px;
        max-height: 44vh; overflow-y: auto; padding-right: 2px;
    }
    .rsc-pilih-baris {
        display: flex; align-items: center; gap: 11px;
        padding: 9px 12px; border-radius: 12px; cursor: pointer;
        border: 1px solid #eef2f7; background: #fff;
        transition: border-color .15s ease, background .15s ease;
    }
    /* Kotak centang asli disembunyikan dari mata, tetap bisa dipakai papan
       ketik: ubin ikonnya yang menunjukkan keadaan tercentang. */
    .rsc-pilih-baris input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .rsc-pilih-baris:focus-within { box-shadow: 0 0 0 3px rgba(124, 58, 237, .18); }
    .rsc-pilih-baris.is-dipilih { border-color: #bbf7d0; background: #f0fdf4; }
    .rsc-pilih-nama { flex: 1 1 auto; min-width: 0; font-size: .86rem; font-weight: 700; color: var(--dsb-tinta); overflow-wrap: anywhere; }
    @media (hover: hover) and (pointer: fine) {
        .rsc-pilih-baris:hover { border-color: #ddd6fe; }
    }

    .rsc-lencana-kepala { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }

    /* ===== Detail: kredensial ===== */
    .rsc-rahasia { display: inline-flex; align-items: center; gap: 6px; justify-content: flex-end; }
    .rsc-rahasia-nilai { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .02em; }
    .rsc-akun-kartu {
        border: 1px solid #eef2f7; border-radius: 14px; padding: 12px 14px; background: #fff;
    }
    .rsc-akun-kartu-judul {
        display: flex; align-items: center; gap: 9px; margin-bottom: 4px;
        font-weight: 800; color: var(--dsb-tinta); font-size: .9rem;
    }
    .rsc-uraian {
        color: #475569; font-size: .88rem; line-height: 1.7; margin: 0; white-space: pre-line;
    }

    /* ===== Rincian harga per akun ===== */
    .rsc-harga-akun { display: grid; gap: 8px; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); }
    .rsc-harga-akun-item {
        display: flex; align-items: center; justify-content: space-between; gap: 10px;
        padding: 10px 13px; border-radius: 12px; border: 1px solid #eef2f7; background: #fff;
        font-size: .84rem;
    }
    .rsc-harga-akun-nama { display: inline-flex; align-items: center; gap: 7px; min-width: 0; font-weight: 700; color: var(--dsb-tinta); }
    /* Nominal tidak boleh patah jadi "Rp" di satu baris dan angkanya di
       baris berikut; yang menyusut adalah namanya. */
    .rsc-harga-akun-item > b { white-space: nowrap; flex-shrink: 0; }
    .rsc-harga-akun-nama > span:not(.dsb-ikon):not(.dsb-lencana) { min-width: 0; overflow-wrap: anywhere; }
    .rsc-harga-akun-nama .dsb-lencana { flex-shrink: 0; }
    .rsc-harga-akun-rumus {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;
        margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9;
        color: var(--dsb-redup); font-size: .82rem;
    }
    .rsc-harga-akun-rumus b { color: #15803d; font-size: 1rem; }
</style>
@endonce
