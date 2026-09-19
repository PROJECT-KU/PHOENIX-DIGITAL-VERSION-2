{{-- Gaya khusus layar Data Testimoni (prefiks tm-; ts- sudah dipakai modal).

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Kartu status (sekaligus tab moderasi) ===== */
    .tm-status { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: clamp(18px, 2.4vw, 26px); }
    .tm-status-btn {
        --c: #7c3aed;
        flex: 1 1 170px;
        display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; text-align: left; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .tm-status-btn.is-info { cursor: default; }
    .tm-status-ikon { flex: 0 0 42px; width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); }
    .tm-status-teks { min-width: 0; }
    .tm-status-teks b { display: block; font-size: 1.35rem; line-height: 1.1; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .tm-status-teks span { display: block; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
    .tm-status-btn.is-aktif { border-color: color-mix(in srgb, var(--c) 50%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); box-shadow: 0 8px 20px -14px color-mix(in srgb, var(--c) 70%, transparent); }
    .tm-status-btn.is-aktif .tm-status-ikon { background: var(--c); color: #fff; }
    .tm-status-btn.is-aktif .tm-status-teks span { color: var(--c); }
    .tm-status-btn.is-perlu .tm-status-teks b { color: #d97706; }
    @media (hover: hover) and (pointer: fine) { .tm-status-btn:not(.is-aktif):not(.is-info):hover { border-color: color-mix(in srgb, var(--c) 30%, #fff); } }

    @media (max-width: 767.98px) {
        .tm-status { display: flex; overflow-x: auto; gap: 8px; scrollbar-width: none; margin-left: -4px; margin-right: -4px; padding: 0 4px 2px; }
        .tm-status::-webkit-scrollbar { display: none; }
        .tm-status-btn { flex: 0 0 150px; padding: 12px; }
        .tm-status-ikon { flex-basis: 36px; width: 36px; height: 36px; }
    }

    [x-cloak] { display: none !important; }

    /* ===== Saklar sorot di form ===== */
    .tm-sorot-saklar { display: flex; align-items: center; gap: 10px; margin-top: 12px; padding: 11px 13px; border: 1px solid #e5e7eb; border-radius: 14px; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .tm-sorot-saklar:hover { border-color: #fcd34d; }
    .tm-sorot-saklar.is-nyala { border-color: #fcd34d; background: #fffbeb; }
    .tm-sorot-saklar input { width: 17px; height: 17px; accent-color: #f59e0b; cursor: pointer; }
    .tm-sorot-ikon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 10px; background: #fef3c7; color: #b45309; }
    .tm-sorot-teks b { display: block; font-size: .86rem; color: #1c1f26; }
    .tm-sorot-teks small { display: block; font-size: .75rem; color: #64748b; }
    .tm-unggah.is-ringkas { padding: 12px; }
    .tm-unggah.is-ringkas .tm-avatar { flex-basis: 40px; width: 40px; height: 40px; font-size: .95rem; }

    /* ===== Sebaran bintang ===== */
    .tm-sebaran { margin-bottom: clamp(18px, 2.4vw, 26px); }
    .tm-sebaran-isi { display: flex; align-items: center; gap: clamp(16px, 3vw, 34px); flex-wrap: wrap; }
    .tm-sebaran-nilai { flex: 0 0 auto; text-align: center; }
    .tm-sebaran-nilai b { display: block; font-size: 2.4rem; line-height: 1; font-weight: 800; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .tm-sebaran-nilai .tm-bintang { display: block; margin: 6px 0 4px; font-size: .95rem; }
    .tm-sebaran-nilai small { display: block; font-size: .72rem; color: #94a3b8; }
    .tm-sebaran-bar { flex: 2 1 260px; min-width: 0; display: grid; gap: 5px; }
    .tm-bar-baris { display: flex; align-items: center; gap: 10px; }
    .tm-bar-label { flex: 0 0 34px; display: inline-flex; align-items: center; gap: 3px; justify-content: flex-end; font-size: .76rem; font-weight: 700; color: #64748b; }
    .tm-bar-label i { color: #f59e0b; font-size: .68rem; }
    .tm-bar-alur { flex: 1 1 auto; height: 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
    .tm-bar-isi { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #fbbf24, #f59e0b); }
    .tm-bar-nilai { flex: 0 0 28px; font-size: .76rem; font-weight: 700; color: #475569; text-align: right; font-variant-numeric: tabular-nums; }
    .tm-sebaran-catatan { flex: 1 1 250px; min-width: 0; display: flex; align-items: flex-start; gap: 11px; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; }
    .tm-sebaran-ikon { flex: 0 0 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; background: #ede9fe; color: #7c3aed; font-size: .95rem; }
    .tm-sebaran-catatan > div { min-width: 0; }
    .tm-sebaran-catatan p { margin: 0; font-size: .85rem; color: #334155; }
    .tm-sebaran-catatan p b { font-size: 1.05rem; color: #1c1f26; }
    .tm-catatan-kecil { margin: 6px 0 0 !important; font-size: .74rem !important; line-height: 1.5; color: #94a3b8 !important; }
    .tm-catatan-kecil i { color: #f59e0b; }
    @media (max-width: 767.98px) {
        .tm-sebaran-isi { gap: 14px; }
        .tm-sebaran-nilai { flex: 1 1 100%; text-align: left; }
        .tm-sebaran-catatan { padding-left: 0; border-left: 0; border-top: 1px solid #f1f5f9; padding-top: 12px; }
    }

    /* ===== Saringan lanjutan ===== */
    .tm-pilih { flex: 0 0 auto; width: auto; min-width: 150px; }
    .tm-pilih.is-sempit { min-width: 124px; }
    .tm-saring-titik { width: 7px; height: 7px; border-radius: 50%; background: #7c3aed; }
    .tm-saring-lanjut { padding: 0 clamp(14px, 2vw, 20px) clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
    .tm-saring-baris { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); padding-top: 14px; }
    .tm-saring-medan { display: block; min-width: 0; }
    .tm-saring-medan > span { display: block; margin-bottom: 5px; font-size: .74rem; font-weight: 700; color: #64748b; }
    .tm-saring-medan .dsb-isian { width: 100%; }
    .tm-saring-reset { margin-top: 12px; }

    /* ===== Bilah aksi massal ===== */
    .tm-massal {
        position: sticky; top: 10px; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        margin-bottom: 14px; padding: 11px 14px; border-radius: 16px;
        background: linear-gradient(135deg, #faf7ff, #fff 55%); border: 1px solid #ddd6fe;
        box-shadow: 0 14px 30px -24px rgba(124, 58, 237, .55);
    }
    .tm-massal-jumlah { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px 5px 6px; border-radius: 99px; background: #fff; border: 1px solid #ede9fe; font-size: .8rem; font-weight: 700; color: #5b21b6; }
    .tm-massal-jumlah b { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; padding: 0 7px; border-radius: 99px; background: #7c3aed; color: #fff; font-size: .82rem; }
    .tm-massal-tombol { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

    /* ===== Centang ===== */
    .tm-centang { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: .8rem; font-weight: 700; color: #475569; cursor: pointer; }
    .tm-centang input { width: 17px; height: 17px; accent-color: #7c3aed; cursor: pointer; }
    .tm-centang.is-kartu { flex: 0 0 auto; padding-top: 2px; }
    .tm-pilih-semua { margin-bottom: 10px; }

    /* ===== Penanda di kartu ===== */
    .tm-penanda { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .tm-tanda { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 99px; font-size: .7rem; font-weight: 700; }
    .tm-tanda i { font-size: .72rem; }
    .tm-tanda.is-sorot { background: #fef3c7; color: #b45309; }
    .tm-tanda.is-tayang { background: #dcfce7; color: #15803d; }
    .tm-tanda.is-diam { background: #f1f5f9; color: #64748b; }
    .tm-tanda.is-tunggu { background: #eff6ff; color: #1d4ed8; }
    .tm-tanda.is-lama { background: #ffedd5; color: #c2410c; }
    .tm-tanda.is-curiga { background: #fef2f2; color: #b91c1c; }
    .tm-kartu.is-sorot { box-shadow: 0 0 0 1px #fcd34d inset, 0 10px 24px -20px rgba(180, 83, 9, .8); }
    .tm-kartu.is-dipilih { box-shadow: 0 0 0 2px #7c3aed inset; }
    .tm-geser { display: inline-flex; gap: 4px; }

    /* ===== Peringatan & alasan di jendela ===== */
    .tm-peringatan { display: flex; gap: 10px; padding: 12px 14px; border-radius: 14px; background: #fef2f2; border: 1px solid #fecaca; }
    .tm-peringatan > i { color: #dc2626; font-size: 1rem; }
    .tm-peringatan b { display: block; font-size: .84rem; color: #b91c1c; }
    .tm-peringatan span { display: block; font-size: .78rem; color: #7f1d1d; }
    .tm-alasan p { margin: 0; padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .85rem; color: #334155; }
    .tm-alasan-pilihan { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .tm-alasan-chip { padding: 6px 11px; border-radius: 99px; border: 1px solid #e5e7eb; background: #fff; font-size: .78rem; font-weight: 700; color: #475569; cursor: pointer; }
    .tm-alasan-chip:hover { border-color: #cbd5e1; background: #f8fafc; }
    .tm-alasan-chip.is-aktif { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
    .tm-jendela.is-ramping { max-width: 460px; }
    .tm-jendela-nav { display: inline-flex; gap: 4px; margin-left: auto; }
    @media (max-width: 575.98px) { .tm-jendela-nav { display: none; } }

    .tm-saring { margin-bottom: clamp(18px, 2.4vw, 26px); }
    .tm-saring-isi { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .tm-saring-isi .dsb-cari { flex: 1 1 280px; min-width: 0; }

    /* ===== Kartu testimoni ===== */
    .tm-rak { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); align-items: start; }
    @media (max-width: 575.98px) { .tm-rak { grid-template-columns: minmax(0, 1fr); gap: 12px; } }
    .tm-kartu {
        --c: #d97706;
        position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease;
    }
    .tm-kartu::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c); }
    @media (hover: hover) and (pointer: fine) { .tm-kartu:hover { box-shadow: 0 16px 32px -24px rgba(15, 23, 42, .45); } }
    .tm-kepala { display: flex; align-items: flex-start; gap: 12px; padding: 16px 16px 0 20px; }
    .tm-avatar { flex: 0 0 46px; width: 46px; height: 46px; border-radius: 50%; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 800; color: #fff; background: var(--av, #7c3aed); border: 0; padding: 0; cursor: pointer; }
    .tm-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .tm-kepala-teks { flex: 1 1 auto; min-width: 0; }
    .tm-nama { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin: 0; font-size: .95rem; font-weight: 800; color: #1c1f26; }
    .tm-peran { display: block; font-size: .76rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .tm-bintang { color: #f59e0b; font-size: .8rem; letter-spacing: 1px; white-space: nowrap; }
    .tm-bintang .is-kosong { color: #e2e8f0; }
    .tm-isi { flex: 1 1 auto; padding: 12px 16px 0 20px; min-width: 0; }
    /* Dipotong di sisi server (Str::limit) — pemotongan CSS menyisakan setengah baris. */
    .tm-pesan { margin: 0; padding: 12px 14px; border-radius: 14px; background: #f8fafc; font-size: .86rem; line-height: 1.6; color: #334155; white-space: pre-line; overflow-wrap: anywhere; }
    .tm-baca { margin-top: 6px; padding: 0; border: 0; background: none; font-size: .76rem; font-weight: 700; color: #6d28d9; cursor: pointer; }
    .tm-chip-deret { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .tm-pemilik { margin-top: 6px; font-size: .72rem; color: #64748b; }
    .tm-pemilik b { color: #334155; }
    .tm-waktu { margin-top: 8px; font-size: .72rem; color: #94a3b8; }
    .tm-aksi { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 16px 14px 20px; margin-top: 12px; border-top: 1px solid #f1f5f9; }
    .tm-aksi:empty { display: none; }
    .tm-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px;
        border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; color: #334155; font-size: .8rem; font-weight: 700;
        text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .tm-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1c1f26; }
    .tm-btn-ikon { width: 36px; padding: 0; flex: 0 0 36px; }
    .tm-btn.is-setuju { background: #16a34a; border-color: #16a34a; color: #fff; }
    .tm-btn.is-setuju:hover { background: #15803d; border-color: #15803d; color: #fff; }
    .tm-btn.is-tolak { color: #dc2626; border-color: #fecaca; }
    .tm-btn.is-tolak:hover { background: #fef2f2; color: #b91c1c; }
    .tm-btn.is-bahaya { color: #dc2626; }
    .tm-btn.is-bahaya:hover { background: #fef2f2; border-color: #fecaca; }
    .tm-aksi-moderasi { display: flex; gap: 8px; flex: 1 1 auto; }
    .tm-aksi-moderasi .tm-btn { flex: 1 1 0; }
    .tm-aksi-lain { display: flex; gap: 8px; margin-left: auto; }
    .tm-halaman { margin-top: 18px; padding: 12px 16px; border-radius: 16px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .tm-halaman .pagination-wrap { gap: 12px; }
    .tm-halaman .small { font-size: .8rem !important; color: #64748b !important; }
    .tm-halaman .small .fw-semibold { color: #1c1f26; }
    .tm-halaman .pagination { margin: 0; gap: 6px; flex-wrap: wrap; justify-content: center; }
    .tm-halaman .page-item { margin: 0; }
    .tm-halaman .page-link {
        display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px;
        border: 1px solid #e9edf3 !important; border-radius: 11px !important; background: #fff !important;
        color: #475569 !important; font-size: .82rem; font-weight: 700; box-shadow: none !important; transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .tm-halaman .page-link:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; color: #1c1f26 !important; }
    .tm-halaman .page-item.active .page-link { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; border-color: transparent !important; color: #fff !important; box-shadow: 0 8px 18px -12px rgba(109, 40, 217, .9) !important; }
    .tm-halaman .page-item.disabled .page-link { background: #f8fafc !important; color: #b0b7c3 !important; border-color: #f1f5f9 !important; }
    @media (max-width: 575.98px) { .tm-halaman .page-link { min-width: 33px; height: 33px; padding: 0 9px; } }

    /* ===== Jendela detail ===== */
    .tm-jendela { max-width: 620px; }
    .tm-detail { display: flex; flex-direction: column; gap: 16px; }
    .tm-detail > * { flex-shrink: 0; }
    .tm-detail-profil { display: flex; align-items: center; gap: 14px; }
    .tm-detail-profil .tm-avatar { flex-basis: 64px; width: 64px; height: 64px; font-size: 1.3rem; cursor: default; }
    /* Hanya teks di kolom kanan — avatar juga sebuah <span>, jangan ikut kena. */
    .tm-detail-profil > div > b { display: block; font-size: 1.02rem; color: #1c1f26; }
    .tm-detail-profil > div > span { display: block; font-size: .8rem; color: #64748b; }
    .tm-kutip { margin: 0; padding: 16px 18px; border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a; font-size: .92rem; line-height: 1.7; color: #334155; white-space: pre-line; overflow-wrap: anywhere; }
    .tm-detail-label { display: block; margin-bottom: 6px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .tm-info { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .tm-info > div { padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; min-width: 0; }
    .tm-info small { display: block; font-size: .7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
    .tm-info b { display: block; font-size: .84rem; color: #1c1f26; overflow-wrap: anywhere; }
    @media (max-width: 420px) { .tm-info { grid-template-columns: minmax(0, 1fr); } }
    .tm-detail-kaki { justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 575.98px) { .tm-detail-kaki .tm-btn, .tm-detail-kaki .dsb-tombol { flex: 1 1 auto; } }

    /* ===== Form tambah/ubah ===== */
    .tm-form-tata { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 340px; align-items: start; }
    .tm-form-utama { display: grid; gap: 20px; min-width: 0; }
    .tm-form-samping { position: sticky; top: 16px; display: grid; gap: 16px; }
    @media (max-width: 991.98px) { .tm-form-tata { grid-template-columns: minmax(0, 1fr); } .tm-form-samping { position: static; } }
    .tm-form-kepala { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
    .tm-form-kepala > div > b { display: block; font-size: .98rem; color: #1c1f26; }
    .tm-form-kepala > div > span { display: block; font-size: .8rem; color: #64748b; }
    .tm-medan + .tm-medan { margin-top: 16px; }
    .tm-dua { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 575.98px) { .tm-dua { grid-template-columns: minmax(0, 1fr); } }
    .tm-galat { display: block; margin-top: 5px; font-size: .78rem; color: #dc2626; }
    .tm-bantu { display: block; margin-top: 5px; font-size: .74rem; color: #94a3b8; }
    .tm-pilih-pelanggan { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; text-align: left; cursor: pointer; }
    .tm-pilih-pelanggan i { color: #94a3b8; }
    .tm-tautan-info { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: #f0fdf4; border: 1px solid #bbf7d0; font-size: .78rem; color: #166534; }
    .tm-tautan-info .tm-lepas { margin-left: auto; }
    .tm-rating-pilih { display: inline-flex; gap: 4px; }
    .tm-rating-pilih button { width: 40px; height: 40px; border-radius: 11px; border: 1px solid #e5e7eb; background: #fff; color: #e2e8f0; font-size: 1.2rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: color .12s ease, background .12s ease; }
    .tm-rating-pilih button.is-isi { color: #f59e0b; background: #fffbeb; border-color: #fde68a; }
    .tm-pesan-isian { min-height: 130px; resize: vertical; }
    .tm-hitung { display: flex; justify-content: space-between; gap: 10px; }
    .tm-status-pilih { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .tm-status-opsi { position: relative; display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 14px; border: 1.5px solid #e9edf3; cursor: pointer; background: #fff; }
    .tm-status-opsi input { position: absolute; opacity: 0; pointer-events: none; }
    .tm-status-opsi > span:first-of-type { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .tm-status-opsi b { display: block; font-size: .88rem; color: #1c1f26; }
    .tm-status-opsi small { display: block; font-size: .74rem; color: #64748b; line-height: 1.4; }
    .tm-status-opsi.is-pilih { border-color: var(--c); background: color-mix(in srgb, var(--c) 6%, #fff); }
    .tm-status-opsi.is-pilih > span:first-of-type { background: var(--c); color: #fff; }
    @media (max-width: 420px) { .tm-status-pilih { grid-template-columns: minmax(0, 1fr); } }
    .tm-unggah { position: relative; display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 16px; border: 1.5px dashed #d8dcf0; background: #fbfbff; }
    .tm-unggah input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 2; }
    .tm-unggah .tm-avatar { flex-basis: 64px; width: 64px; height: 64px; cursor: default; }
    .tm-unggah b { display: block; font-size: .86rem; color: #1c1f26; }
    .tm-unggah small { display: block; font-size: .74rem; color: #64748b; }
    .tm-unggah-muat { align-items: center; gap: 8px; font-size: .8rem; font-weight: 700; color: #6d28d9; margin-top: 10px; }
    /* Pratinjau kartu publik */
    .tm-pratinjau { border-radius: 18px; border: 1px solid #fde68a; background: linear-gradient(180deg, #fffbeb, #fff); padding: 16px; }
    .tm-pratinjau-label { display: flex; align-items: center; gap: 6px; margin-bottom: 12px; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b45309; }
    .tm-pratinjau p { margin: 10px 0 0; font-size: .82rem; line-height: 1.6; color: #334155; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; white-space: pre-line; }
    .tm-simpan { width: 100%; justify-content: center; min-height: 48px; }
    .tm-isi-tombol { display: inline-flex; align-items: center; gap: 8px; }
    .tm-simpan-catatan { margin: 6px 0 12px; font-size: .78rem; line-height: 1.5; color: #64748b; }

    .tm-kicker { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #f26522; margin-bottom: 10px; }
    /* Pemilih pelanggan (Swal) */
    .tf-pick-list { max-height: 320px; overflow-y: auto; text-align: left; }
    .tf-pick-item { display: block; width: 100%; text-align: left; border: 1px solid #eef0f7; background: #fff; border-radius: 12px; padding: 10px 12px; margin-bottom: 6px; cursor: pointer; }
    .tf-pick-item:hover { border-color: #c7d2fe; background: #f7f8ff; }
    .tf-pick-name { display: block; font-weight: 700; color: #1e293b; font-size: .92rem; }
    .tf-pick-sub { display: block; color: #64748b; font-size: .76rem; }
    .tf-pick-empty { color: #94a3b8; font-size: .88rem; padding: 18px; text-align: center; }

    /* ===== Ragam tombol (WAJIB sesudah aturan dasar .tm-btn di atas) ===== */
    .tm-btn.is-aktif { border-color: #c4b5fd; background: #f5f3ff; color: #6d28d9; }
    .tm-btn.tm-btn-ikon.is-sorot { color: #b45309; border-color: #fcd34d; background: #fffbeb; }
    .tm-btn.is-wa { color: #15803d; border-color: #bbf7d0; }
    .tm-btn.is-wa:hover { background: #f0fdf4; color: #166534; }
    .tm-btn.is-tegas { background: #dc2626; border-color: #dc2626; color: #fff; }
    .tm-btn.is-tegas:hover { background: #b91c1c; border-color: #b91c1c; color: #fff; }
</style>
@endonce
