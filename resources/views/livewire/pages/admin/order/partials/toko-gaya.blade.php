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

    /* Kartu ringkasan yang bisa diklik (pindah tab) */
    .pt-stat-tombol { font: inherit; text-align: left; cursor: pointer; width: 100%; }
    .pt-stat-tombol > span { display: block; }
    .pt-stat-tombol > .dsb-ikon { display: inline-flex; }
    .pt-stat-tombol > .dsb-stat-ket { display: flex; }
    .pt-stat-tombol:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
</style>
@endonce
