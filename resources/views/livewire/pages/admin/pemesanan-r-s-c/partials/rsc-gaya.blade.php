{{-- Gaya khusus layar Pemesanan RSC.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     memang tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Saringan ===== */
    /* Kisi, bukan flex: tiap saringan selebar sama walau jumlahnya ganjil. */
    .rsc-saring { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .rsc-medan-cari { grid-column: span 2; }
    @media (max-width: 991.98px) { .rsc-saring { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) {
        .rsc-saring { grid-template-columns: minmax(0, 1fr); }
        .rsc-medan-cari { grid-column: auto; }
    }
    /* Buka-tutup saringan KHUSUS ponsel (pola yang sama dengan Task Saya).
       Di layar lebar pembungkusnya display: contents — medan-medannya tetap
       anak kisi .rsc-saring, jadi susunan desktop & tablet tidak berubah,
       dan tombolnya tidak pernah tampil. */
    .rsc-saring-lanjut { display: contents; }
    .rsc-saring-tombol { display: none; }
    @media (max-width: 575.98px) {
        .rsc-saring-tombol {
            display: flex; align-items: center; gap: 8px;
            min-height: 44px; padding: 0 14px; border-radius: 12px;
            border: 1px solid var(--dsb-tepi, #e9edf3); background: #fff;
            color: #334155; font-size: .88rem; font-weight: 700; text-align: left; cursor: pointer;
        }
        .rsc-saring-tombol > .bi-sliders { color: #7c3aed; }
        .rsc-saring-jumlah {
            padding: 2px 9px; border-radius: 999px; background: #f5f3ff; color: #6d28d9;
            font-size: .74rem; font-weight: 700;
        }
        .rsc-saring-panah { margin-left: auto; color: #94a3b8; transition: transform .2s ease; }
        .rsc-saring-kartu.is-buka .rsc-saring-panah { transform: rotate(180deg); }

        /* Tertutup: enam medan & kaki saringan disembunyikan. */
        .rsc-saring-kartu:not(.is-buka) .rsc-saring-lanjut,
        .rsc-saring-kartu:not(.is-buka) .rsc-saring-kaki { display: none; }
        .rsc-saring-kartu.is-buka .rsc-saring-lanjut { display: grid; gap: 12px; }
    }
    @media (max-width: 575.98px) and (prefers-reduced-motion: reduce) {
        .rsc-saring-panah { transition: none; }
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

    /* ===== Kartu ringkasan yang bisa diklik ===== */
    .rsc-stat-tombol { font: inherit; text-align: left; cursor: pointer; width: 100%; }
    .rsc-stat-tombol > span { display: block; }
    .rsc-stat-tombol > .dsb-ikon { display: inline-flex; }
    .rsc-stat-tombol > .dsb-stat-ket { display: flex; }
    .rsc-stat-tombol.is-dipilih { border-color: color-mix(in srgb, var(--c) 45%, #fff); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 14%, transparent); }
    .rsc-stat-tombol:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }

    /* ===== Tabel ===== */
    /* Lencana masa akun di kolom Batch hanya untuk layar yang menyembunyikan
       kolom Masa Akun (k-sedang: tersembunyi 768–991px; di bawahnya
       tabel menjadi kartu dan kolom itu tampil lagi). */
    .rsc-masa-sempit { display: none; }
    @media (min-width: 768px) and (max-width: 991.98px) { .rsc-masa-sempit { display: inline-flex; } }
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

    /* ===== Detail: salin, WhatsApp, cari peserta ===== */
    .rsc-salin.is-tersalin { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .rsc-telp { display: inline-flex; align-items: center; gap: 8px; }
    .rsc-wa { color: #16a34a; }
    .rsc-wa.is-habis { color: #e11d48; }
    @media (hover: hover) and (pointer: fine) {
        .rsc-wa:hover { background: #16a34a; color: #fff; border-color: transparent; }
    }
    .rsc-cari-peserta {
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px clamp(14px, 2vw, 20px); border-bottom: 1px solid #f1f5f9;
    }
    .rsc-cari-peserta .dsb-cari { flex: 1 1 260px; max-width: 420px; }

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
