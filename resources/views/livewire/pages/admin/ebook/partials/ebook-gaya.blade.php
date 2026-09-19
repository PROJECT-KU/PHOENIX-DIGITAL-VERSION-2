{{-- Gaya khusus layar Ebook Bonus.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Ringkasan: 4 kartu → 2×2 di tablet & ponsel ===== */
    .eb-stat-deret { display: grid; gap: 14px; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: clamp(18px, 2.4vw, 26px); }
    @media (max-width: 991.98px) { .eb-stat-deret { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    /* Ponsel: kartu sempit — angka saja, satuan disembunyikan supaya tidak terpotong. */
    @media (max-width: 575.98px) { .eb-stat-deret { gap: 10px; } .eb-stat-deret .dsb-stat-satuan { display: none; } }

    /* ===== Saringan: cari + segmen status dalam satu baris ===== */
    .eb-saring { margin-bottom: clamp(18px, 2.4vw, 26px); }
    .eb-saring-isi { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .eb-cari { flex: 1 1 280px; min-width: 0; }
    .eb-segmen { display: inline-flex; gap: 4px; padding: 4px; border-radius: 13px; background: #f1f5f9; }
    .eb-segmen-btn {
        display: inline-flex; align-items: center; gap: 6px; min-height: 38px; padding: 0 14px; border: 0; border-radius: 10px;
        background: transparent; color: #64748b; font-size: .82rem; font-weight: 700; cursor: pointer; white-space: nowrap;
        transition: background .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .eb-segmen-btn.is-aktif { background: #fff; color: #6d28d9; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
    .eb-segmen-btn:not(.is-aktif):hover { color: #1c1f26; }
    @media (max-width: 575.98px) {
        .eb-segmen { width: 100%; }
        .eb-segmen-btn { flex: 1 1 0; justify-content: center; padding: 0 8px; }
    }

    /* ===== Rak ebook: kartu bersampul ===== */
    .eb-rak { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); }
    /* Status di baris meta hanya untuk ponsel (di layar lebar ada di sampul). */
    .eb-meta-status { display: none !important; }
    .eb-kartu {
        --c: #7c3aed;
        display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
    }
    @media (hover: hover) and (pointer: fine) {
        .eb-kartu:hover { transform: translateY(-2px); border-color: color-mix(in srgb, var(--c) 30%, #fff); box-shadow: 0 14px 30px -18px color-mix(in srgb, var(--c) 55%, transparent); }
    }
    .eb-sampul {
        position: relative; display: flex; align-items: center; justify-content: center; height: 128px; width: 100%;
        border: 0; padding: 0; cursor: pointer; overflow: hidden;
        background:
            radial-gradient(circle at 18% 20%, rgba(255, 255, 255, .35), transparent 42%),
            linear-gradient(135deg, color-mix(in srgb, var(--c) 80%, #fff), var(--c));
    }
    /* Punggung buku di kiri sampul */
    .eb-sampul::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 10px; background: rgba(0, 0, 0, .14); }
    .eb-sampul-ikon {
        width: 58px; height: 58px; border-radius: 17px; display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255, 255, 255, .95); color: var(--c); font-size: 1.6rem; box-shadow: 0 10px 22px -10px rgba(15, 23, 42, .45);
    }
    .eb-sampul-ikon i.bi, .eb-sampul-ikon i.bi::before { display: block; line-height: 1; }
    .eb-sampul-pdf {
        position: absolute; right: 12px; bottom: 10px; padding: 2px 8px; border-radius: 7px;
        background: rgba(255, 255, 255, .22); color: #fff; font-size: .66rem; font-weight: 800; letter-spacing: .08em;
    }
    .eb-sampul-status { position: absolute; left: 20px; top: 12px; box-shadow: 0 2px 6px rgba(15, 23, 42, .12); }
    .eb-kartu.is-nonaktif .eb-sampul { filter: grayscale(.85); opacity: .75; }
    .eb-kartu-isi { flex: 1 1 auto; padding: 14px 16px 6px; min-width: 0; }
    .eb-judul { margin: 0 0 4px; font-size: .98rem; font-weight: 800; color: #1c1f26; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .eb-desk { margin: 0 0 10px; font-size: .8rem; color: #64748b; line-height: 1.5; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; min-height: 2.4em; }
    .eb-meta { display: flex; flex-wrap: wrap; gap: 6px 12px; font-size: .74rem; color: #64748b; }
    .eb-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .eb-meta .is-peringatan { color: #b45309; }
    .eb-meta .eb-meta-status { color: #64748b; font-weight: 700; }
    .eb-meta .eb-meta-status.is-aktif { color: #15803d; }
    .eb-aksi { display: flex; align-items: center; gap: 6px; padding: 12px 16px 14px; border-top: 1px dashed #eef2f7; margin-top: 8px; }
    .eb-aksi-utama { margin-right: auto; }
    .eb-halaman { margin-top: 18px; display: flex; justify-content: center; }
    .eb-halaman .pagination { margin: 0; flex-wrap: wrap; justify-content: center; }
    /* Ponsel: kartu mendatar (SESUDAH aturan dasar kartu, supaya tidak tertimpa) — sampul kecil di kiri, isi di kanan (±150px, bukan ±300px). */
    @media (max-width: 575.98px) {
        .eb-rak { grid-template-columns: minmax(0, 1fr); gap: 10px; }
        .eb-kartu { display: grid; grid-template-columns: 92px minmax(0, 1fr); grid-template-rows: 1fr auto; }
        .eb-sampul { grid-row: 1 / 3; height: auto; min-height: 100%; }
        .eb-sampul::before { width: 6px; }
        .eb-sampul-ikon { width: 44px; height: 44px; border-radius: 13px; font-size: 1.2rem; }
        .eb-sampul-pdf { right: 50%; transform: translateX(50%); bottom: 8px; }
        .eb-sampul-status { display: none; }
        .eb-kartu-isi { padding: 12px 12px 4px; }
        .eb-desk { min-height: 0; margin-bottom: 8px; }
        .eb-aksi { padding: 8px 12px 12px; margin-top: 0; border-top: 0; }
        .eb-meta-status { display: inline-flex !important; }
    }

    /* ===== Jendela detail ===== */
    .eb-jendela { max-width: 560px; }
    /* minmax(0,1fr): tautan panjang (nowrap) tidak boleh melebarkan jendela di ponsel. */
    .eb-detail { display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr); }
    .eb-detail-angka { display: grid; gap: 10px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    @media (max-width: 575.98px) { .eb-detail-angka { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .eb-detail-angka > div { --c: #7c3aed; display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 12px 8px; border-radius: 14px; background: color-mix(in srgb, var(--c) 7%, #fff); border: 1px solid color-mix(in srgb, var(--c) 18%, #fff); text-align: center; }
    .eb-detail-angka span { width: 34px; height: 34px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: var(--c); color: #fff; margin-bottom: 4px; }
    .eb-detail-angka b { font-size: .95rem; color: #1c1f26; }
    .eb-detail-angka small { font-size: .7rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .eb-detail-label { display: block; margin-bottom: 6px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .eb-detail-blok p { margin: 0; font-size: .88rem; color: #334155; line-height: 1.55; white-space: pre-line; }
    .eb-tautan { display: flex; align-items: center; gap: 8px; padding: 8px 8px 8px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; }
    .eb-tautan code { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #4c1d95; font-size: .8rem; background: none; }
    .eb-pesanan { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 11px; text-decoration: none; color: inherit; }
    .eb-pesanan + .eb-pesanan { border-top: 1px dashed #eef2f7; }
    .eb-pesanan:hover { background: #f8fafc; }
    .eb-pesanan-nama { font-weight: 700; font-size: .84rem; color: #1c1f26; white-space: nowrap; }
    .eb-pesanan-produk { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .78rem; color: #64748b; }
    .eb-pesanan > i { color: #cbd5e1; }
    .eb-kosong-kecil { font-size: .82rem; color: #94a3b8; }
    .eb-detail-waktu { margin: 0; font-size: .74rem; color: #94a3b8; }
    .eb-detail-peringatan { display: flex; gap: 10px; padding: 11px 13px; border-radius: 12px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; font-size: .82rem; line-height: 1.5; }
    .eb-detail-ket { display: block; margin-top: 6px; font-size: .74rem; color: #94a3b8; }
    .eb-tautan-baru { border: 0; background: none; padding: 0; color: #dc2626; font-weight: 700; text-decoration: underline; cursor: pointer; font-size: inherit; }
    .eb-detail-produk { display: flex; flex-wrap: wrap; gap: 6px; }
    .eb-meta .is-bawaan { color: #c2410c; font-weight: 700; }
    .eb-detail-kaki { justify-content: flex-end; gap: 8px; }
    @media (max-width: 575.98px) {
        .eb-pesanan { flex-wrap: wrap; row-gap: 2px; }
        .eb-pesanan-produk { flex-basis: 100%; order: 3; }
        .eb-detail-kaki .dsb-tombol { flex: 1 1 auto; justify-content: center; min-height: 44px; }
        .eb-detail-angka { gap: 6px; }
        .eb-detail-angka > div { padding: 10px 4px; }
    }

    /* ===== Form tambah/ubah ===== */
    .eb-form-tata { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 340px; align-items: start; }
    .eb-form-samping { position: sticky; top: 16px; display: grid; gap: 16px; }
    @media (max-width: 991.98px) { .eb-form-tata { grid-template-columns: minmax(0, 1fr); } .eb-form-samping { position: static; } }
    .eb-form-kepala { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
    .eb-form-kepala > div > b { display: block; font-size: .98rem; color: #1c1f26; }
    .eb-form-kepala > div > span { display: block; font-size: .8rem; color: #64748b; }
    .eb-medan + .eb-medan { margin-top: 16px; }
    .eb-galat { display: block; margin-top: 5px; font-size: .78rem; color: #dc2626; }
    .eb-bantu { display: block; margin-top: 5px; font-size: .76rem; color: #94a3b8; }
    .eb-desk-isian { min-height: 120px; resize: vertical; }
    .eb-status-pilih { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .eb-status-opsi { position: relative; display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 14px; border: 1.5px solid #e9edf3; cursor: pointer; background: #fff; transition: border-color .15s ease, background .15s ease; }
    .eb-status-opsi input { position: absolute; opacity: 0; pointer-events: none; }
    .eb-status-opsi > span:first-of-type { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .eb-status-opsi b { display: block; font-size: .88rem; color: #1c1f26; }
    .eb-status-opsi small { display: block; font-size: .74rem; color: #64748b; line-height: 1.4; }
    .eb-status-opsi.is-pilih { border-color: var(--c); background: color-mix(in srgb, var(--c) 6%, #fff); }
    .eb-status-opsi.is-pilih > span:first-of-type { background: var(--c); color: #fff; }
    @media (max-width: 420px) { .eb-status-pilih { grid-template-columns: minmax(0, 1fr); } }

    /* Zona unggah PDF */
    .eb-unggah { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; min-height: 190px; padding: 18px; border-radius: 16px; border: 1.5px dashed #d8dcf0; background: linear-gradient(180deg, #fbfbff, #f5f6ff); text-align: center; transition: border-color .2s ease, background .2s ease; }
    .eb-unggah:hover { border-color: #a78bfa; }
    .eb-unggah input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .eb-unggah.is-siap { border-style: solid; border-color: #86efac; background: linear-gradient(180deg, #f0fdf4, #fff); }
    .eb-unggah.is-galat { border-color: #fca5a5; background: #fef2f2; }
    .eb-unggah-ikon { width: 56px; height: 56px; border-radius: 17px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.55rem; background: #fff; color: #7c3aed; box-shadow: 0 8px 18px -10px rgba(76, 29, 149, .5); }
    .eb-unggah.is-siap .eb-unggah-ikon { color: #16a34a; }
    .eb-unggah b { font-size: .9rem; color: #1c1f26; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .eb-unggah small { font-size: .76rem; color: #64748b; }
    .eb-unggah-muat { align-items: center; gap: 8px; font-size: .8rem; font-weight: 700; color: #6d28d9; margin-top: 10px; }
    .eb-berkas { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; }
    .eb-berkas-ikon { width: 42px; height: 42px; flex: 0 0 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #fee2e2; color: #dc2626; font-size: 1.2rem; }
    .eb-berkas > div { flex: 1 1 auto; min-width: 0; }
    .eb-berkas b { display: block; font-size: .86rem; color: #1c1f26; }
    .eb-berkas small { display: block; font-size: .74rem; color: #64748b; }
    .eb-isi-tombol { display: inline-flex; align-items: center; gap: 8px; }
    .eb-simpan { width: 100%; justify-content: center; min-height: 48px; }
    .eb-kicker { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #f26522; margin-bottom: 10px; }
    .eb-tips { margin: 0; padding-left: 18px; font-size: .78rem; color: #64748b; line-height: 1.6; }
    .eb-form-utama { display: grid; gap: 20px; min-width: 0; }
    .eb-saran-daftar { display: grid; gap: 8px; grid-template-columns: minmax(0, 1fr); }
    .eb-saran-baris { display: flex; align-items: flex-start; gap: 12px; width: 100%; padding: 12px; text-align: left; border-radius: 13px; border: 1.5px solid #e9edf3; background: #fff; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .eb-saran-baris .eb-produk-centang { margin-top: 1px; }
    .eb-saran-baris.is-pilih { border-color: #ea580c; background: #fff7ed; }
    .eb-saran-baris.is-pilih .eb-produk-centang { background: #ea580c; border-color: #ea580c; color: #fff; }
    .eb-saran-teks { min-width: 0; }
    .eb-saran-teks b { display: block; font-size: .88rem; color: #1c1f26; }
    .eb-saran-teks span { display: block; font-size: .8rem; color: #475569; }
    .eb-saran-teks small { display: block; margin-top: 2px; font-size: .72rem; color: #c2410c; font-weight: 700; }
    .eb-hitung { display: inline-flex; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: #fff7ed; color: #c2410c; font-size: .7rem; font-weight: 800; vertical-align: middle; }
    .eb-saran { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 12px; padding: 10px 12px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; }
    .eb-saran-judul { font-size: .76rem; font-weight: 700; color: #92400e; display: inline-flex; align-items: center; gap: 5px; }
    .eb-saran-btn { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; border: 1px solid #fcd34d; background: #fff; color: #92400e; font-size: .76rem; font-weight: 700; cursor: pointer; }
    .eb-saran-btn small { color: #b45309; font-weight: 600; }
    .eb-saran-btn:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .eb-saran-btn:hover small { color: #fff; }
    .eb-produk-cari { margin-bottom: 10px; }
    .eb-produk-daftar { display: grid; gap: 8px; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); max-height: 340px; overflow-y: auto; padding: 2px; }
    .eb-produk { position: relative; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; border: 1.5px solid #e9edf3; background: #fff; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .eb-produk input { position: absolute; opacity: 0; pointer-events: none; }
    .eb-produk-centang { flex: 0 0 22px; width: 22px; height: 22px; border-radius: 7px; border: 1.5px solid #cbd5e1; display: inline-flex; align-items: center; justify-content: center; color: transparent; font-size: .85rem; }
    .eb-produk.is-pilih { border-color: #ea580c; background: #fff7ed; }
    .eb-produk.is-pilih .eb-produk-centang { background: #ea580c; border-color: #ea580c; color: #fff; }
    .eb-produk-teks { min-width: 0; }
    .eb-produk-teks b { display: block; font-size: .84rem; color: #1c1f26; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .eb-produk-teks small { display: block; font-size: .7rem; color: #94a3b8; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .eb-produk-teks small.is-pindah { color: #c2410c; font-weight: 700; }
    .eb-produk:focus-within { outline: 2px solid #fdba74; outline-offset: 1px; }
</style>
@endonce
