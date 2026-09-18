{{-- Gaya khusus layar Pesanan Toko.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Tab status (segmen yang bisa digulir mendatar) ===== */
    .pt-tab-deret {
        display: flex; gap: 6px; padding: 6px; overflow-x: auto; scrollbar-width: thin;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        -webkit-overflow-scrolling: touch;
    }
    .pt-tab {
        --c: #7c3aed;
        flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 42px; padding: 0 14px; border: 0; border-radius: 11px; cursor: pointer;
        background: transparent; color: #64748b; font-size: .84rem; font-weight: 700; white-space: nowrap;
        transition: background .15s ease, color .15s ease;
    }
    .pt-tab i.bi { color: var(--c); font-size: .95rem; line-height: 1; }
    .pt-tab-jumlah {
        min-width: 24px; height: 22px; padding: 0 7px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 800;
    }
    .pt-tab.is-aktif { background: color-mix(in srgb, var(--c) 10%, #fff); color: var(--c); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--c) 30%, #fff); }
    .pt-tab.is-aktif .pt-tab-jumlah { background: var(--c); color: #fff; }
    .pt-tab:focus-visible { outline: 2px solid #7c3aed; outline-offset: 1px; }
    /* Layar lebar: tab membungkus ke baris kedua supaya semuanya terlihat
       (10 tab tidak muat satu baris; yang di ujung kanan jadi tak terlihat). */
    @media (min-width: 992px) {
        .pt-tab-deret { flex-wrap: wrap; overflow: visible; }
        .pt-tab { flex: 0 1 auto; }
    }
    @media (hover: hover) and (pointer: fine) {
        .pt-tab:not(.is-aktif):hover { background: #f8fafc; color: #1c1f26; }
    }

    /* ===== Saringan ===== */
    .pt-saring { display: grid; gap: 12px; grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr)); }
    @media (max-width: 767.98px) { .pt-saring { grid-template-columns: minmax(0, 1fr); } }
    .pt-saring-kaki {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
    }
    .pt-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }

    /* ===== Tabel ===== */
    .pt-tautan { text-decoration: none; color: inherit; }
    @media (hover: hover) and (pointer: fine) {
        .pt-tautan:hover .dsb-tabel-judul { color: #6d28d9; }
    }
    /* Tombol & jendela catatan (daftar) */
    .pt-catatan-btn.is-admin { color: #b45309; background: #fffbeb; border-color: #fde68a; }
    .pt-catatan-btn.is-pelanggan { color: #15803d; background: #f0fdf4; border-color: #bbf7d0; }
    .pt-catatan-btn.is-selesai { color: #94a3b8; }
    #pt-catatan-jendela .dsb-jendela-kaki > .dsb-tombol { margin-left: auto; }
    .pt-cj-blok { --c: #d97706; padding: 10px 12px; border-radius: 12px; border: 1px solid color-mix(in srgb, var(--c) 25%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); }
    .pt-cj-blok + .pt-cj-blok { margin-top: 10px; }
    .pt-cj-judul { display: flex; align-items: center; gap: 6px; font-size: .74rem; font-weight: 800; color: var(--c); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
    .pt-cj-isi { font-size: .88rem; color: #1c1f26; line-height: 1.55; white-space: normal; overflow-wrap: anywhere; }
    .pt-total { font-weight: 800; color: var(--dsb-tinta, #1c1f26); white-space: nowrap; }
    .pt-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
    .pt-halaman .pagination { margin: 0; justify-content: center; flex-wrap: wrap; }
    .pt-lanjut {
        display: inline-flex; align-items: center; gap: 5px; min-height: 32px; padding: 0 11px;
        border-radius: 9px; background: #16a34a; color: #fff; font-size: .76rem; font-weight: 700;
        text-decoration: none; white-space: nowrap;
    }
    .pt-lanjut:hover { background: #15803d; color: #fff; }
    .pt-lanjut.is-wa { background: #25d366; }
    .pt-lanjut.is-wa:hover { background: #1ebe5a; }
    .pt-lanjut.is-ungu { background: #f5f3ff; color: #6d28d9; box-shadow: inset 0 0 0 1px #ddd6fe; }
    .pt-lanjut.is-ungu:hover { background: #7c3aed; color: #fff; }

    /* ===== Saringan lanjutan, urutan, baris ===== */
    .pt-lanjutan { margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9; }
    .pt-lanjutan-kepala { display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
    .pt-lanjutan-panah { transition: transform .15s ease; font-size: .75rem; }
    .pt-lanjutan-panah.is-buka { transform: rotate(180deg); }
    .pt-tab-jumlah.is-isi { background: #7c3aed; color: #fff; min-width: 20px; height: 20px; }
    .pt-urut { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .pt-urut .dsb-label { margin: 0; }
    .pt-urut .dsb-isian { width: auto; min-width: 150px; }
    .pt-urut .dsb-isian.pt-baris { min-width: 76px; }
    .pt-urut .dsb-isian:disabled { opacity: .55; }
    .pt-saring.pt-saring-lanjut { margin-top: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .pt-saring-lanjut .pt-medan-produk { grid-column: 1 / -1; }
    @media (max-width: 991.98px) { .pt-saring.pt-saring-lanjut { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) {
        .pt-saring.pt-saring-lanjut { grid-template-columns: minmax(0, 1fr); }
        .pt-urut { width: 100%; }
        .pt-urut .dsb-isian { flex: 1 1 0; min-width: 0; }
    }
    .pt-isi-tombol { display: inline-flex; align-items: center; gap: 8px; }

    /* ===== Linimasa riwayat pesanan ===== */
    .pt-riwayat { list-style: none; margin: 0; padding: 0; position: relative; }
    .pt-riwayat::before { content: ''; position: absolute; left: 17px; top: 8px; bottom: 8px; width: 2px; background: #eef2f7; }
    .pt-riwayat-baris { position: relative; display: flex; gap: 12px; align-items: flex-start; padding: 7px 0; }
    .pt-riwayat-ikon {
        flex: 0 0 36px; width: 36px; height: 36px; border-radius: 11px; position: relative; z-index: 1;
        display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: .95rem;
        background: var(--c); box-shadow: 0 0 0 4px #fff;
    }
    .pt-riwayat-teks { min-width: 0; padding-top: 2px; font-size: .8rem; color: #64748b; line-height: 1.45; }
    .pt-riwayat-teks b { display: block; font-size: .88rem; color: #1e293b; font-weight: 700; }
    .pt-riwayat-teks span { display: block; }
    .pt-riwayat-catatan { margin: 12px 0 0; padding: 9px 12px; border-radius: 11px; background: #f8fafc; color: #64748b; font-size: .76rem; }

    /* ===== Aksi massal (tab Segera Habis / Akun Habis) ===== */
    .pt-massal {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
        padding: 10px 16px; border-bottom: 1px solid #f1f5f9; font-size: .82rem; color: #475569;
    }
    .pt-massal.is-aktif { background: #f5f3ff; }
    .pt-massal-aksi { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .pt-massal-aksi b { color: #6d28d9; }
    .pt-centang { display: inline-flex; align-items: center; gap: 8px; margin: 0; cursor: pointer; font-weight: 600; }
    .pt-centang input, .pt-centang-kotak { width: 18px; height: 18px; accent-color: #7c3aed; cursor: pointer; }
    .dsb-tabel th.pt-kol-centang, .dsb-tabel td.pt-kol-centang { width: 44px; padding-right: 0; }
    @media (max-width: 767.98px) {
        .dsb-tabel td.pt-kol-centang { width: auto; }
    }

    /* Kartu ringkasan yang bisa diklik (pindah tab) */
    .pt-stat-tombol { font: inherit; text-align: left; cursor: pointer; width: 100%; }
    .pt-stat-tombol > span { display: block; }
    .pt-stat-tombol > .dsb-ikon { display: inline-flex; }
    .pt-stat-tombol > .dsb-stat-ket { display: flex; }
    .pt-stat-tombol:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
</style>
@endonce
