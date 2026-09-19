{{-- Gaya khusus layar Moderasi Ulasan Produk (prefiks ul-).

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    [x-cloak] { display: none !important; }

    /* ===== Kartu status (sekaligus tab moderasi) ===== */
    .ul-status { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
    .ul-status-btn {
        --c: #7c3aed;
        flex: 1 1 170px;
        display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; text-align: left; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .ul-status-btn.is-info { cursor: default; }
    .ul-status-ikon { flex: 0 0 42px; width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); }
    .ul-status-teks { min-width: 0; }
    .ul-status-teks b { display: block; font-size: 1.5rem; line-height: 1.05; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .ul-status-teks span { display: block; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
    .ul-status-btn.is-aktif { border-color: color-mix(in srgb, var(--c) 50%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); box-shadow: 0 8px 20px -14px color-mix(in srgb, var(--c) 70%, transparent); }
    .ul-status-btn.is-aktif .ul-status-ikon { background: var(--c); color: #fff; }
    .ul-status-btn.is-aktif .ul-status-teks span { color: var(--c); }
    .ul-status-btn.is-perlu .ul-status-teks b { color: #d97706; }
    @media (hover: hover) and (pointer: fine) { .ul-status-btn:not(.is-aktif):not(.is-info):hover { border-color: color-mix(in srgb, var(--c) 30%, #fff); } }
    /* Di ponsel kartunya 2x2, bukan empat baris penuh yang memaksa menggulir
       jauh sebelum sampai ke daftar ulasannya. */
    @media (max-width: 575.98px) {
        .ul-status { gap: 8px; }
        .ul-status-btn { flex: 1 1 calc(50% - 4px); min-width: 0; padding: 12px; }
        .ul-status-ikon { flex-basis: 36px; width: 36px; height: 36px; font-size: 1rem; }
        .ul-status-teks b { font-size: 1.25rem; }
        .ul-status-teks span { font-size: .72rem; white-space: normal; }
    }

    /* ===== Sebaran bintang ===== */
    .ul-sebaran { margin-bottom: 14px; }
    .ul-sebaran-isi { display: flex; align-items: center; gap: clamp(16px, 3vw, 34px); flex-wrap: wrap; }
    .ul-sebaran-nilai { flex: 0 0 auto; text-align: center; }
    .ul-sebaran-nilai b { display: block; font-size: 2.3rem; line-height: 1; font-weight: 800; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .ul-sebaran-nilai small { display: block; margin-top: 4px; font-size: .72rem; color: #94a3b8; }
    .ul-bintang { color: #f59e0b; font-size: .84rem; letter-spacing: 1px; white-space: nowrap; }
    .ul-bintang .is-kosong { color: #e2e8f0; }
    .ul-sebaran-bar { flex: 2 1 260px; min-width: 0; display: grid; gap: 5px; }
    .ul-bar-baris { display: flex; align-items: center; gap: 10px; }
    .ul-bar-label { flex: 0 0 34px; display: inline-flex; align-items: center; gap: 3px; justify-content: flex-end; font-size: .76rem; font-weight: 700; color: #64748b; }
    .ul-bar-label i { color: #f59e0b; font-size: .68rem; }
    .ul-bar-alur { flex: 1 1 auto; height: 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .ul-bar-isi { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #fbbf24, #f59e0b); }
    .ul-bar-nilai { flex: 0 0 28px; font-size: .76rem; font-weight: 700; color: #475569; text-align: right; font-variant-numeric: tabular-nums; }
    .ul-sebaran-catatan { flex: 1 1 250px; min-width: 0; display: flex; align-items: flex-start; gap: 11px; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; }
    .ul-sebaran-ikon { flex: 0 0 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; background: #ede9fe; color: #7c3aed; font-size: .95rem; }
    .ul-sebaran-catatan p { margin: 0; font-size: .82rem; color: #334155; }
    .ul-sebaran-catatan p b { font-size: 1rem; color: #1c1f26; }
    .ul-catatan-kecil { margin: 6px 0 0 !important; font-size: .74rem !important; line-height: 1.5; color: #94a3b8 !important; }

    /* ===== Bilah cari & saring ===== */
    .ul-saring { margin-bottom: 14px; }
    .ul-saring-isi { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ul-saring-isi .dsb-cari { flex: 1 1 260px; min-width: 0; max-width: 420px; }
    .ul-saring-isi .ul-pilih:first-of-type { margin-left: auto; }
    .ul-pilih { flex: 0 0 auto; width: auto; min-width: 150px; }
    .ul-pilih.is-sempit { min-width: 124px; }
    .ul-saring-titik { width: 7px; height: 7px; border-radius: 50%; background: #7c3aed; }
    .ul-saring-lanjut { padding: 0 clamp(14px, 2vw, 20px) clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
    .ul-saring-baris { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); padding-top: 14px; }
    .ul-saring-medan { display: block; min-width: 0; }
    .ul-saring-medan > span { display: block; margin-bottom: 5px; font-size: .74rem; font-weight: 700; color: #64748b; }
    .ul-saring-medan .dsb-isian { width: 100%; }
    .ul-chip-saring { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 clamp(14px, 2vw, 20px) 13px; }
    .ul-chip-lepas { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 99px; border: 1px solid #ddd6fe; background: #f5f3ff; color: #5b21b6; font-size: .75rem; font-weight: 700; cursor: pointer; }
    .ul-chip-lepas:hover { background: #ede9fe; }
    .ul-chip-lepas i { font-size: .6rem; }
    .ul-chip-lepas.is-semua { border-color: #e5e7eb; background: #fff; color: #64748b; }
    .ul-ekspor-ket { margin: 0; padding: 0 clamp(14px, 2vw, 20px) 12px; font-size: .78rem; color: #6d28d9; }

    /* ===== Bilah aksi massal ===== */
    .ul-massal {
        position: sticky; top: 10px; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        margin-bottom: 14px; padding: 11px 14px; border-radius: 16px;
        background: linear-gradient(135deg, #faf7ff, #fff 55%); border: 1px solid #ddd6fe;
        box-shadow: 0 14px 30px -24px rgba(124, 58, 237, .55);
    }
    .ul-massal-jumlah { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px 5px 6px; border-radius: 99px; background: #fff; border: 1px solid #ede9fe; font-size: .8rem; font-weight: 700; color: #5b21b6; }
    .ul-massal-jumlah b { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; padding: 0 7px; border-radius: 99px; background: #7c3aed; color: #fff; font-size: .82rem; }
    .ul-massal-tombol { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

    /* ===== Centang ===== */
    .ul-centang { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: .8rem; font-weight: 700; color: #475569; cursor: pointer; }
    .ul-centang input { width: 17px; height: 17px; accent-color: #7c3aed; cursor: pointer; }
    .ul-centang.is-kartu { flex: 0 0 auto; padding-top: 2px; }
    .ul-pilih-semua { margin-bottom: 10px; }

    /* ===== Rak kartu ulasan ===== */
    .ul-rak { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); align-items: start; }
    @media (max-width: 575.98px) { .ul-rak { grid-template-columns: minmax(0, 1fr); gap: 12px; } }
    .ul-kartu {
        --c: #d97706;
        position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease;
    }
    .ul-kartu::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c); }
    @media (hover: hover) and (pointer: fine) { .ul-kartu:hover { box-shadow: 0 16px 32px -24px rgba(15, 23, 42, .45); } }
    .ul-kartu.is-dipilih { box-shadow: 0 0 0 2px #7c3aed inset; }

    .ul-kepala { display: flex; align-items: flex-start; gap: 12px; padding: 16px 16px 0 20px; }
    .ul-gambar { flex: 0 0 46px; width: 46px; height: 46px; border-radius: 13px; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 1.1rem; border: 0; padding: 0; cursor: pointer; }
    .ul-gambar img { width: 100%; height: 100%; object-fit: cover; }
    .ul-kepala-teks { flex: 1 1 auto; min-width: 0; }
    /* Isi tombol unduhan: ikon & teks sebaris, tidak berhimpitan. */
    .ul-isi-tombol { display: inline-flex; align-items: center; gap: 7px; }
    .ul-isi-tombol i.bi, .ul-isi-tombol i.bi::before { display: block; line-height: 1; }

    /* Nama produk boleh dua baris: dipotong satu baris membuat hampir semua
       nama paket berakhir "Combo Sat-Se…" dan tidak terbaca. */
    .ul-produk { margin: 0; font-size: .95rem; font-weight: 800; line-height: 1.3; color: #1c1f26; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ul-pengulas { display: block; font-size: .78rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .ul-isi { flex: 1 1 auto; padding: 12px 16px 0 20px; min-width: 0; }
    .ul-penanda { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .ul-tanda { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 99px; font-size: .7rem; font-weight: 700; }
    .ul-tanda i { font-size: .72rem; }
    .ul-tanda.is-paket { background: #fef3c7; color: #b45309; }
    .ul-tanda.is-produk { background: #eff6ff; color: #1d4ed8; }
    .ul-tanda.is-curiga { background: #fef2f2; color: #b91c1c; }
    .ul-tanda.is-tayang { background: #dcfce7; color: #15803d; }
    .ul-teks { margin: 0; padding: 12px 14px; border-radius: 14px; background: #f8fafc; font-size: .86rem; line-height: 1.6; color: #334155; white-space: pre-line; overflow-wrap: anywhere; min-height: 74px; }
    .ul-baca { margin-top: 6px; padding: 0; border: 0; background: none; font-size: .76rem; font-weight: 700; color: #6d28d9; cursor: pointer; }
    .ul-waktu { margin-top: 8px; font-size: .72rem; color: #94a3b8; }

    .ul-aksi { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 16px 14px 20px; margin-top: 12px; border-top: 1px solid #f1f5f9; }
    .ul-aksi:empty { display: none; }
    .ul-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px;
        border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; color: #334155; font-size: .8rem; font-weight: 700;
        text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .ul-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1c1f26; }
    .ul-btn i.bi, .ul-btn i.bi::before { display: block; line-height: 1; }
    .ul-btn-ikon { width: 36px; padding: 0; flex: 0 0 36px; }
    .ul-aksi-moderasi { display: flex; gap: 8px; flex: 1 1 auto; }
    .ul-aksi-moderasi .ul-btn { flex: 1 1 0; }
    .ul-aksi-lain { display: flex; gap: 8px; flex: 1 1 auto; margin-left: auto; }
    .ul-aksi-lain .ul-btn-ikon { flex: 1 1 0; width: auto; min-width: 36px; }

    /* Ragam tombol WAJIB sesudah aturan dasar .ul-btn di atas. */
    .ul-btn.is-setuju { background: #16a34a; border-color: #16a34a; color: #fff; }
    .ul-btn.is-setuju:hover { background: #15803d; border-color: #15803d; color: #fff; }
    .ul-btn.is-sembunyi { color: #b45309; border-color: #fde68a; }
    .ul-btn.is-sembunyi:hover { background: #fffbeb; color: #92400e; }
    .ul-btn.is-bahaya { color: #dc2626; }
    .ul-btn.is-bahaya:hover { background: #fef2f2; border-color: #fecaca; }
    .ul-btn.is-aktif { border-color: #c4b5fd; background: #f5f3ff; color: #6d28d9; }

    /* ===== Paginasi (dibatasi ke layar ini) ===== */
    .ul-halaman { margin-top: 18px; padding: 12px 16px; border-radius: 16px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .ul-halaman .pagination-wrap { gap: 12px; }
    .ul-halaman .small { font-size: .8rem !important; color: #64748b !important; }
    .ul-halaman .pagination { margin: 0; gap: 6px; flex-wrap: wrap; justify-content: center; }
    .ul-halaman .page-link {
        display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px;
        border: 1px solid #e9edf3 !important; border-radius: 11px !important; background: #fff !important;
        color: #475569 !important; font-size: .82rem; font-weight: 700; box-shadow: none !important;
    }
    .ul-halaman .page-link:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; color: #1c1f26 !important; }
    .ul-halaman .page-item.active .page-link { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; border-color: transparent !important; color: #fff !important; }
    .ul-halaman .page-item.disabled .page-link { background: #f8fafc !important; color: #b0b7c3 !important; border-color: #f1f5f9 !important; }

    /* ===== Jendela detail ===== */
    .ul-jendela { max-width: 620px; }
    .ul-detail { display: flex; flex-direction: column; gap: 16px; }
    .ul-detail > * { flex-shrink: 0; }
    .ul-detail-profil { display: flex; align-items: center; gap: 14px; }
    .ul-detail-profil .ul-gambar { flex-basis: 64px; width: 64px; height: 64px; font-size: 1.3rem; cursor: default; }
    .ul-detail-profil > div > b { display: block; font-size: 1.02rem; color: #1c1f26; }
    .ul-detail-profil > div > span { display: block; font-size: .8rem; color: #64748b; }
    .ul-kutip { margin: 0; padding: 16px 18px; border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a; font-size: .92rem; line-height: 1.7; color: #334155; white-space: pre-line; overflow-wrap: anywhere; }
    .ul-detail-label { display: block; margin-bottom: 6px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .ul-info { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ul-info > div { padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; min-width: 0; }
    .ul-info small { display: block; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .ul-info b { display: block; font-size: .88rem; color: #1c1f26; overflow-wrap: anywhere; }
    .ul-detail-kaki { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .ul-jendela-nav { display: inline-flex; gap: 4px; margin-left: auto; }
    .ul-pintasan { margin-left: auto; font-size: .72rem; color: #94a3b8; white-space: nowrap; }
    .ul-pintasan b { color: #64748b; }
    @media (max-width: 991.98px) { .ul-pintasan { display: none; } }
    @media (max-width: 575.98px) { .ul-jendela-nav { display: none; } .ul-info { grid-template-columns: minmax(0, 1fr); } }
    .ul-peringatan { display: flex; gap: 10px; padding: 12px 14px; border-radius: 14px; background: #fef2f2; border: 1px solid #fecaca; }
    .ul-peringatan > i { color: #dc2626; font-size: 1rem; }
    .ul-peringatan b { display: block; font-size: .84rem; color: #b91c1c; }
    .ul-peringatan span { display: block; font-size: .78rem; color: #7f1d1d; }

    /* ===== Kerangka pemuatan ===== */
    .ul-sembunyi { display: none !important; }
    .ul-kerangka { display: none; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); }
    .ul-kerangka-kartu { padding: 16px; border-radius: 18px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .ul-kerangka-kepala { display: flex; align-items: center; gap: 12px; }
    .ul-tulang { display: block; height: 12px; border-radius: 8px; background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 37%, #f1f5f9 63%); background-size: 400% 100%; animation: ul-kilau 1.3s ease infinite; }
    .ul-tulang.is-kotak { flex: 0 0 46px; width: 46px; height: 46px; border-radius: 13px; }
    @keyframes ul-kilau { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
    @media (prefers-reduced-motion: reduce) { .ul-tulang { animation: none; } }

    .sorot-kata { background: #fef08a; color: inherit; padding: 0 2px; border-radius: 3px; }

    /* ===== Fokus papan tik ===== */
    .ul-btn:focus-visible, .ul-status-btn:focus-visible, .ul-chip-lepas:focus-visible,
    .ul-gambar:focus-visible, .ul-centang input:focus-visible, .ul-halaman .page-link:focus-visible {
        outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 10px;
    }

    /* ===== Cetak ===== */
    @media print {
        .dsb-hero-aksi, .ul-saring, .ul-massal, .ul-pilih-semua, .ul-aksi, .ul-status,
        .ul-halaman, .ts-modal, .ts-modal-back, .ul-kerangka { display: none !important; }
        .dsb, .dsb-kartu, .ul-kartu { box-shadow: none !important; }
        .ul-kartu { break-inside: avoid; border: 1px solid #cbd5e1 !important; }
        .ul-rak { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .ul-teks { min-height: 0 !important; background: none !important; padding-left: 0 !important; }
    }
</style>
@endonce
