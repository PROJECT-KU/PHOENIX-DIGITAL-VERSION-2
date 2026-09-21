{{-- Gaya khusus layar Artikel/Blog (prefiks bl-).

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    [x-cloak] { display: none !important; }

    /* ===== Kartu status (sekaligus tab) ===== */
    .bl-status { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
    .bl-status-btn {
        --c: #7c3aed;
        flex: 1 1 170px;
        display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; text-align: left; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .bl-status-ikon { flex: 0 0 42px; width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); }
    .bl-status-teks { min-width: 0; }
    .bl-status-teks b { display: block; font-size: 1.5rem; line-height: 1.05; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .bl-status-teks span { display: block; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
    .bl-status-btn.is-aktif { border-color: color-mix(in srgb, var(--c) 50%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); box-shadow: 0 8px 20px -14px color-mix(in srgb, var(--c) 70%, transparent); }
    .bl-status-btn.is-aktif .bl-status-ikon { background: var(--c); color: #fff; }
    .bl-status-btn.is-aktif .bl-status-teks span { color: var(--c); }
    @media (hover: hover) and (pointer: fine) { .bl-status-btn:not(.is-aktif):hover { border-color: color-mix(in srgb, var(--c) 30%, #fff); } }
    @media (max-width: 575.98px) {
        .bl-status { gap: 8px; }
        .bl-status-btn { flex: 1 1 calc(50% - 4px); padding: 12px; }
        .bl-status-ikon { flex-basis: 36px; width: 36px; height: 36px; font-size: 1rem; }
        .bl-status-teks b { font-size: 1.25rem; }
        .bl-status-teks span { font-size: .72rem; white-space: normal; }
    }

    .bl-isi-tombol { display: inline-flex; align-items: center; gap: 7px; }
    .bl-isi-tombol i.bi, .bl-isi-tombol i.bi::before { display: block; line-height: 1; }

    /* ===== Ringkasan ===== */
    .bl-ringkas { margin-bottom: 14px; }
    /* stretch, BUKAN center: dengan center tiap kotak setinggi isinya
       sendiri, sehingga blok yang keterangannya cuma satu baris terlihat
       lebih pendek daripada tetangganya. */
    .bl-ringkas-isi { display: flex; align-items: stretch; gap: clamp(14px, 2.4vw, 26px); flex-wrap: wrap; }
    .bl-ringkas-blok { display: flex; align-items: flex-start; gap: 11px; flex: 1 1 250px; min-width: 0; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; text-align: left; font: inherit; color: inherit; cursor: pointer; transition: border-color .15s ease, background .15s ease, transform .15s ease; }
    @media (hover: hover) and (pointer: fine) { .bl-ringkas-blok:hover { border-color: #ddd6fe; background: #faf5ff; transform: translateY(-1px); } }
    .bl-ringkas-ikon { flex: 0 0 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; font-size: .95rem; }
    .bl-ringkas-ikon.is-baca { background: #eef2ff; color: #4338ca; }
    .bl-ringkas-ikon.is-populer { background: #fff7ed; color: #c2410c; }
    .bl-ringkas-ikon.is-terbit { background: #ecfdf5; color: #15803d; }
    .bl-ringkas-blok p { margin: 0; font-size: .84rem; color: #334155; }
    .bl-ringkas-blok p b { font-size: 1rem; color: #1c1f26; }
    .bl-catatan-kecil { margin: 5px 0 0 !important; font-size: .74rem !important; line-height: 1.5; color: #94a3b8 !important; overflow-wrap: anywhere; }

    /* ===== Bilah cari & saring ===== */
    .bl-saring { margin-bottom: 14px; }
    .bl-saring-isi { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .bl-saring-isi .dsb-cari { flex: 1 1 240px; min-width: 0; max-width: 340px; }
    .bl-pilih { flex: 0 0 auto; width: auto; min-width: 150px; }
    .bl-pilih.is-sempit { min-width: 124px; }
    .bl-saklar { position: relative; display: inline-flex; padding: 3px; gap: 3px; border-radius: 12px; background: #f1f5f9; border: 1px solid #e9edf3; }
    /* Pil putihnya BERGESER, bukan latar yang meloncat antar tombol. */
    .bl-saklar-pil { position: absolute; top: 3px; bottom: 3px; left: 3px; width: calc(50% - 4.5px); border-radius: 9px; background: #fff; box-shadow: 0 2px 6px -3px rgba(15, 23, 42, .3); transition: transform .26s cubic-bezier(.22, .61, .36, 1); }
    .bl-saklar.is-tabel .bl-saklar-pil { transform: translateX(calc(100% + 3px)); }
    .bl-saklar button { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex: 1 1 0; padding: 6px 11px; border: 0; border-radius: 9px; background: none; font-size: .78rem; font-weight: 700; color: #64748b; cursor: pointer; transition: color .2s ease; }
    .bl-saklar button.is-aktif { color: #7c3aed; }
    @media (max-width: 575.98px) { .bl-saklar { display: none; } }
    @media (prefers-reduced-motion: reduce) { .bl-saklar-pil { transition: none; } }

    /* Saringan yang jarang dipakai disembunyikan di balik satu tombol,
       supaya bilah utamanya muat satu baris dan tidak jadi deretan kotak
       centang yang seragam dan sulit dibedakan. */
    .bl-saring-lanjut { display: grid; gap: 12px; padding: 0 clamp(14px, 2vw, 20px) clamp(14px, 2vw, 18px); }
    .bl-saring-judul { margin: 0; font-size: .72rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .bl-saring-baris { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .bl-saring-titik { position: absolute; top: 5px; right: 6px; width: 7px; height: 7px; border-radius: 50%; background: #7c3aed; }
    .bl-saring-tombol { position: relative; }

    .bl-chip-saring { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 clamp(14px, 2vw, 20px) clamp(12px, 2vw, 16px); }
    .bl-chip-lepas { display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px; border-radius: 999px; border: 1px solid #e9d5ff; background: #faf5ff; color: #6d28d9; font-size: .78rem; font-weight: 700; cursor: pointer; }
    .bl-chip-lepas:hover { background: #f3e8ff; }
    .bl-chip-lepas.is-semua { border-style: dashed; background: #fff; color: #64748b; }
    .bl-chip-lepas i { font-size: .7rem; }

    /* ===== Rak artikel ===== */
    /* align-items: stretch supaya kartu sebaris sama tinggi dan tombolnya rata;
       rak yang ragged terbaca berantakan. */
    .bl-rak { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); align-items: stretch; }
    @media (max-width: 575.98px) { .bl-rak { grid-template-columns: minmax(0, 1fr); gap: 12px; } }

    .bl-kartu {
        --c: #64748b;
        position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease, padding .24s ease, gap .24s ease;
    }
    @media (hover: hover) and (pointer: fine) { .bl-kartu:hover { box-shadow: 0 16px 32px -24px rgba(15, 23, 42, .45); } }

    /* Dipakai sebagai <button> pada artikel yang punya sampul (agar bisa
       diperbesar), jadi tepi & padding bawaan tombol harus dimatikan —
       tanpa ini gambarnya terbingkai garis gelap bawaan peramban. */
    .bl-sampul {
        position: relative; display: block; width: 100%; aspect-ratio: 16 / 9;
        padding: 0; border: 0; border-radius: 0; appearance: none; -webkit-appearance: none;
        font: inherit; color: inherit; text-align: inherit;
        background: linear-gradient(135deg, #f5f3ff, #ede9fe); overflow: hidden;
    }
    .bl-sampul:focus-visible { outline: 2px solid #7c3aed; outline-offset: -2px; }
    .bl-sampul img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; }
    .bl-sampul-kosong { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; color: #a78bfa; }
    .bl-sampul-kosong i { font-size: 1.6rem; }
    .bl-sampul-kosong span { font-size: .72rem; font-weight: 700; }
    .bl-sampul-lencana { position: absolute; left: 12px; top: 12px; display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; font-size: .72rem; font-weight: 800; color: #fff; background: rgba(15, 23, 42, .62); backdrop-filter: blur(6px); }

    .bl-isi { flex: 1 1 auto; display: flex; flex-direction: column; padding: 14px 16px 0; min-width: 0; }
    .bl-judul { margin: 0 0 6px; font-size: .96rem; font-weight: 800; line-height: 1.45; color: #1c1f26; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .bl-judul a { color: inherit; text-decoration: none; }
    .bl-judul a:hover { color: #6d28d9; }
    .bl-slug { display: block; font-size: .72rem; color: #94a3b8; overflow-wrap: anywhere; margin-bottom: 8px; }
    .bl-cuplikan { flex: 1 1 auto; margin: 0 0 10px; font-size: .84rem; line-height: 1.6; color: #64748b; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

    .bl-penanda { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding: 0 16px; margin-bottom: 2px; }
    .bl-tanda { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 99px; font-size: .7rem; font-weight: 700; }
    .bl-tanda i { font-size: .72rem; }
    .bl-tanda.is-kategori { background: #f5f3ff; color: #6d28d9; cursor: pointer; border: 0; }
    .bl-tanda.is-kategori:hover { background: #ede9fe; }
    .bl-tanda.is-baca { background: #eff6ff; color: #1d4ed8; }
    .bl-tanda.is-waktu { background: #f8fafc; color: #64748b; }

    .bl-aksi { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 16px 14px; margin-top: 12px; border-top: 1px solid #f1f5f9; }
    .bl-aksi-utama { display: flex; gap: 8px; flex: 1 1 auto; }
    .bl-aksi-utama .bl-btn { flex: 1 1 0; }
    .bl-aksi-lain { display: flex; gap: 8px; flex: 1 1 auto; margin-left: auto; }
    .bl-aksi-lain .bl-btn-ikon { flex: 1 1 0; width: auto; min-width: 36px; }

    .bl-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px;
        border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; color: #334155; font-size: .8rem; font-weight: 700;
        text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .bl-btn i.bi, .bl-btn i.bi::before { display: block; line-height: 1; }
    .bl-btn-ikon { width: 36px; padding: 0; }
    .bl-btn:hover { border-color: #cbd5e1; color: #1c1f26; }

    /* Ragam tombol WAJIB sesudah aturan dasar .bl-btn di atas. */
    .bl-btn.is-utama { background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-color: transparent; color: #fff; }
    .bl-btn.is-utama:hover { background: linear-gradient(135deg, #7c4ddb, #5b21b6); border-color: transparent; color: #fff; }
    .bl-btn.is-terbit { color: #15803d; border-color: #bbf7d0; }
    .bl-btn.is-terbit:hover { background: #f0fdf4; color: #166534; }
    .bl-btn.is-draf { color: #b45309; border-color: #fde68a; }
    .bl-btn.is-draf:hover { background: #fffbeb; color: #92400e; }
    .bl-btn.is-bahaya { color: #dc2626; border-color: #fecaca; }
    .bl-btn.is-bahaya:hover { background: #fef2f2; color: #b91c1c; }
    .bl-btn.is-aktif { border-color: #c4b5fd; color: #6d28d9; background: #faf5ff; }

    /* ===== Bentuk TABEL: kartu yang sama, disusun jadi baris =====
       Sengaja BUKAN elemen tabel tersendiri. Menukar rak kartu dengan tabel
       berarti seluruh DOM-nya dibongkar lalu dibangun ulang, dan pergantiannya
       terasa menyentak. Dengan satu markup, kartunya cuma bergeser posisi. */
    .bl-tabel-kepala { display: none; gap: 10px; padding: 0 16px 8px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .bl-tabel-kepala > *:nth-child(1) { flex: 1 1 260px; }
    .bl-tabel-kepala > *:nth-child(2) { flex: 1 1 200px; }
    .bl-tabel-kepala > *:nth-child(3) { flex: 0 0 auto; margin-left: auto; }
    .bl-tabel-kepala button { display: inline-flex; align-items: center; gap: 5px; padding: 0; border: 0; background: none; font: inherit; color: inherit; text-align: left; cursor: pointer; }
    .bl-tabel-kepala button:hover { color: #64748b; }
    .bl-tabel-kepala button.is-aktif { color: #6d28d9; }
    .bl-tabel-kepala i { font-size: .68rem; }

    @media (min-width: 768px) {
        .bl-daftar.is-tabel .bl-tabel-kepala { display: flex; position: sticky; top: 0; z-index: 5; padding-top: 10px; background: linear-gradient(#f6f7fb 78%, rgba(246, 247, 251, 0)); }
        .bl-daftar.is-tabel .bl-rak { grid-template-columns: minmax(0, 1fr); gap: 8px; }
        .bl-daftar.is-tabel .bl-kartu { flex-direction: row; align-items: center; flex-wrap: wrap; gap: 12px; padding: 8px 12px; }
        .bl-daftar.is-tabel .bl-sampul { flex: 0 0 92px; width: 92px; aspect-ratio: 16 / 10; border-radius: 10px; }
        .bl-daftar.is-tabel .bl-sampul-lencana { display: none; }
        .bl-daftar.is-tabel .bl-sampul-kosong span { display: none; }
        .bl-daftar.is-tabel .bl-isi { flex: 1 1 260px; padding: 0; }
        .bl-daftar.is-tabel .bl-judul { -webkit-line-clamp: 1; margin-bottom: 2px; }
        .bl-daftar.is-tabel .bl-cuplikan { display: none; }
        .bl-daftar.is-tabel .bl-slug { margin-bottom: 5px; }
        .bl-daftar.is-tabel .bl-penanda { flex: 1 1 200px; padding: 0; margin: 0; }
        .bl-daftar.is-tabel .bl-aksi { flex: 0 0 auto; margin-top: 0; padding: 0; border-top: 0; }
        .bl-daftar.is-tabel .bl-aksi-utama { flex: 0 0 auto; }
        .bl-daftar.is-tabel .bl-aksi-utama .bl-btn span { display: none; }
        .bl-daftar.is-tabel .bl-aksi-utama .bl-btn { flex: 0 0 36px; padding: 0; }
        .bl-daftar.is-tabel .bl-aksi-lain { flex: 0 0 auto; }
        .bl-daftar.is-tabel .bl-aksi-lain .bl-btn-ikon { flex: 0 0 36px; }
    }
    @media (min-width: 768px) and (max-width: 991.98px) {
        .bl-daftar.is-tabel .bl-isi { flex: 1 1 180px; }
        .bl-daftar.is-tabel .bl-penanda { flex: 0 1 auto; }
    }

    /* ===== Pergantian bentuk ===== */
    .bl-rak { animation: bl-masuk .3s cubic-bezier(.22, .61, .36, 1) both; }
    @keyframes bl-masuk { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) { .bl-rak { animation: none; } }

    /* ===== Kerangka pemuatan ===== */
    .bl-sembunyi { display: none !important; }
    .bl-kerangka { display: none; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
    .bl-kerangka-kartu { border-radius: 18px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); overflow: hidden; }
    .bl-kerangka-sampul { aspect-ratio: 16 / 9; }
    .bl-kerangka-isi { padding: 14px 16px 16px; display: grid; gap: 9px; }
    .bl-tulang { display: block; height: 12px; border-radius: 8px; background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 37%, #f1f5f9 63%); background-size: 400% 100%; animation: bl-kilau 1.3s ease infinite; }
    @keyframes bl-kilau { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
    @media (prefers-reduced-motion: reduce) { .bl-tulang { animation: none; } }

    /* Ikon di tengah keadaan-kosong sengaja BERWARNA di layar ini
       (permintaan pemilik). Abu-abu bawaan dasbor terbaca seperti layar mati. */
    .bl-wadah .dsb-kosong-ikon { background: #f5f3ff; border-color: #ede9fe; color: #7c3aed; }

    .sorot-kata { background: #fef08a; color: inherit; padding: 0 2px; border-radius: 3px; }

    /* ===== Halaman ===== */
    .bl-halaman { margin-top: 16px; }
    .bl-halaman .pagination { justify-content: center; gap: 6px; margin: 0; flex-wrap: wrap; }
    .bl-halaman .page-item .page-link {
        display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px;
        border: 1px solid #e9edf3 !important; border-radius: 11px !important; background: #fff !important;
        color: #475569 !important; font-size: .82rem; font-weight: 700; box-shadow: none !important;
    }
    .bl-halaman .page-link:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; color: #1c1f26 !important; }
    .bl-halaman .page-item.active .page-link { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; border-color: transparent !important; color: #fff !important; }
    .bl-halaman .page-item.disabled .page-link { background: #f8fafc !important; color: #b0b7c3 !important; border-color: #f1f5f9 !important; }

    /* ===== Formulir artikel (create/edit) ===== */
    .bl-form { display: grid; gap: 14px; grid-template-columns: minmax(0, 1.9fr) minmax(300px, 1fr); align-items: start; }
    @media (max-width: 1199.98px) { .bl-form { grid-template-columns: minmax(0, 1fr); } }
    .bl-form-utama, .bl-form-samping { display: grid; gap: 14px; }
    @media (min-width: 1200px) { .bl-form-samping { position: sticky; top: 14px; } }

    .bl-panel-judul { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
    .bl-panel-judul > span:nth-child(2) { flex: 1 1 170px; min-width: 0; }
    .bl-panel-ikon { flex: 0 0 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; font-size: .95rem; }
    .bl-panel-judul b { display: block; font-size: .92rem; color: #1c1f26; }
    .bl-panel-judul small { display: block; font-size: .76rem; color: #94a3b8; }

    .bl-medan { display: block; margin-bottom: 14px; }
    .bl-medan > span:first-child { display: block; margin-bottom: 6px; font-size: .78rem; font-weight: 700; color: #475569; }
    .bl-medan .dsb-isian, .bl-medan .form-control, .bl-medan .form-select { width: 100%; }
    .bl-galat { display: block; margin-top: 6px; font-size: .78rem; font-weight: 600; color: #dc2626; }
    .bl-bantu { display: block; margin-top: 6px; font-size: .75rem; line-height: 1.5; color: #94a3b8; }

    .bl-url { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 8px; padding: 9px 12px; border-radius: 11px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .78rem; color: #64748b; overflow-wrap: anywhere; }
    .bl-url i { color: #7c3aed; }
    .bl-url b { color: #1c1f26; font-weight: 700; }
    .bl-url-auto { margin-left: auto; display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px; background: #f5f3ff; color: #6d28d9; font-size: .7rem; font-weight: 800; }

    .bl-sampul-pratinjau { aspect-ratio: 16 / 9; border-radius: 14px; overflow: hidden; background: linear-gradient(135deg, #f5f3ff, #ede9fe); border: 1px solid #eef2f7; margin-bottom: 12px; }
    .bl-sampul-pratinjau img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; }
    .bl-sampul-kosong.is-form { color: #a78bfa; }
    .bl-unggah { position: relative; display: flex; align-items: center; justify-content: center; gap: 9px; padding: 13px; border: 1.5px dashed #e2e8f0; border-radius: 12px; background: #fff; font-size: .84rem; font-weight: 700; color: #475569; cursor: pointer; }
    .bl-unggah:hover { border-color: #c4b5fd; background: #faf5ff; }
    .bl-unggah i { color: #7c3aed; font-size: 1.05rem; }
    .bl-unggah input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    .bl-wajib { color: #dc2626; font-style: normal; }
    .bl-panel-judul .bl-seo-acak { margin-left: auto; flex: 0 0 auto; height: 32px; padding: 0 12px; font-size: .76rem; }

    /* Medan hanya-baca: isinya dibuat sistem, bukan diketik. Tetap putih agar
       terbaca; yang menandai hanya kursor dan tepinya. */
    .dsb-isian.is-kunci { background: #fff; color: #475569; cursor: default; }

    .bl-pilih-kategori {
        display: flex; align-items: center; gap: 8px; width: 100%; min-height: 42px; padding: 9px 12px;
        border: 1px solid #e2e8f0; border-radius: 11px; background: #fff; font-size: .86rem; font-weight: 600;
        color: #1c1f26; text-align: left; cursor: pointer;
    }
    .bl-pilih-kategori:hover { border-color: #c4b5fd; }
    .bl-pilih-kategori:focus-visible { outline: 2px solid #7c3aed; outline-offset: 2px; }
    .bl-pilih-kosong { color: #94a3b8; font-weight: 500; }
    .bl-pilih-panah { margin-left: auto; color: #94a3b8; font-size: .75rem; }

    .bl-simpan-baris { display: flex; gap: 10px; align-items: stretch; }
    .bl-kembali {
        width: 100%; height: 40px; margin-top: 12px;
        border-style: dashed; color: #64748b; font-weight: 600;
    }
    .bl-kembali:hover { border-style: solid; border-color: #c4b5fd; background: #faf5ff; color: #6d28d9; }
    .bl-kembali i { font-size: .9rem; }
    .bl-simpan { flex: 1 1 auto; justify-content: center; height: 46px; }
    .bl-batal { flex: 0 0 auto; height: 46px; }

    /* ===== Kategori ===== */
    .bl-kat-rak { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); align-items: stretch; }
    .bl-kat { display: flex; align-items: flex-start; flex-wrap: wrap; gap: 12px; padding: 12px 14px; border-radius: 14px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); transition: border-color .15s ease, box-shadow .15s ease; }
    .bl-kat:hover { border-color: #ddd6fe; box-shadow: 0 10px 22px -20px rgba(15, 23, 42, .5); }
    .bl-kat-ikon { flex: 0 0 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; background: #f5f3ff; color: #7c3aed; font-size: 1rem; }
    .bl-kat-teks { flex: 1 1 150px; min-width: 0; }
    .bl-kat-teks b { display: block; font-size: .9rem; color: #1c1f26; overflow-wrap: break-word; }
    .bl-kat-teks small { display: block; font-size: .75rem; color: #94a3b8; }
    .bl-kat-aksi { display: flex; gap: 6px; flex: 0 0 auto; margin-left: auto; align-self: flex-start; }
    .bl-kat .dsb-isian { flex: 1 1 auto; min-width: 0; }


    /* ===== Pilih & aksi massal ===== */
    .bl-pilih-semua { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; padding: 0 2px; }
    .bl-centang { display: inline-flex; align-items: center; gap: 8px; font-size: .82rem; font-weight: 600; color: #475569; cursor: pointer; }
    .bl-centang input { width: 16px; height: 16px; accent-color: #7c3aed; cursor: pointer; }
    .bl-tautan { border: 0; background: none; padding: 0; font-size: .8rem; font-weight: 700; color: #6d28d9; text-decoration: underline; cursor: pointer; }

    .bl-massal { position: sticky; bottom: 12px; z-index: 20; display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin: 12px 0; padding: 11px 14px; border-radius: 15px; background: #1c1f26; color: #fff; box-shadow: 0 18px 38px -18px rgba(15, 23, 42, .8); }
    .bl-massal b { font-size: .86rem; }
    .bl-massal .bl-btn { height: 34px; background: rgba(255, 255, 255, .1); border-color: rgba(255, 255, 255, .18); color: #fff; }
    .bl-massal .bl-btn:hover { background: rgba(255, 255, 255, .2); color: #fff; }
    .bl-massal .bl-btn.is-bahaya { color: #fca5a5; border-color: rgba(248, 113, 113, .4); }
    .bl-massal-pisah { flex: 1 1 auto; }
    @media (max-width: 575.98px) { .bl-massal { flex-direction: column; align-items: stretch; } .bl-massal .bl-btn { width: 100%; } }

    .bl-kartu.is-tercentang { border-color: #c4b5fd; box-shadow: 0 0 0 2px #ede9fe; }
    .bl-kartu-centang { position: absolute; left: 12px; top: 12px; z-index: 3; width: 20px; height: 20px; accent-color: #7c3aed; cursor: pointer; }
    .bl-kartu.is-tercentang .bl-sampul-lencana, .bl-kartu-centang ~ .bl-sampul .bl-sampul-lencana { left: 40px; }
    .bl-daftar.is-tabel .bl-kartu-centang { position: static; flex: 0 0 auto; }

    /* ===== Urungkan ===== */
    .bl-urung { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; padding: 11px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #e9edf3; font-size: .85rem; color: #334155; }
    .bl-urung i { color: #64748b; }
    .bl-urung .bl-btn { margin-left: auto; height: 32px; }

    /* ===== Lencana tambahan di kartu ===== */
    .bl-tanda.is-mandek { background: #fff7ed; color: #c2410c; }
    .bl-tanda.is-semat { background: #fdf4ff; color: #a21caf; }
    .bl-tanda.is-hitung { background: #eff6ff; color: #1d4ed8; }
    .bl-tanda.is-tag { background: #f1f5f9; color: #475569; border: 0; cursor: pointer; }
    .bl-tanda.is-tag:hover { background: #e2e8f0; }
    .bl-sampul-semat { position: absolute; right: 12px; top: 12px; display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 999px; background: rgba(162, 28, 175, .9); color: #fff; font-size: .72rem; }

    /* ===== Saklar kecil ===== */
    .bl-tukar { display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; font-size: .78rem; font-weight: 700; color: #475569; cursor: pointer; }
    .bl-tukar input { width: 15px; height: 15px; accent-color: #7c3aed; cursor: pointer; }
    .bl-tukar.is-nyala { border-color: #c4b5fd; background: #faf5ff; color: #6d28d9; }

    .bl-pintasan { margin-left: auto; font-size: .72rem; color: #94a3b8; white-space: nowrap; }
    .bl-pintasan kbd { display: inline-block; padding: 1px 5px; border-radius: 5px; border: 1px solid #e2e8f0; background: #f8fafc; font-family: inherit; font-size: .7rem; color: #475569; }
    @media (max-width: 991.98px) { .bl-pintasan { display: none; } }

    /* ===== Tag di formulir ===== */
    .bl-tag-rak { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 9px; }
    .bl-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 9px; border-radius: 999px; background: #f5f3ff; color: #6d28d9; font-size: .78rem; font-weight: 700; }
    .bl-tag button { display: inline-flex; border: 0; background: none; padding: 0; color: inherit; opacity: .6; cursor: pointer; font-size: .7rem; }
    .bl-tag button:hover { opacity: 1; }
    .bl-tag-isi { display: flex; gap: 8px; }
    .bl-tag-isi .dsb-isian { flex: 1 1 auto; min-width: 0; }

    /* ===== Daftar periksa kelengkapan ===== */
    .bl-periksa { display: grid; gap: 7px; margin: 0; padding: 0; list-style: none; }
    .bl-periksa li { display: flex; align-items: flex-start; gap: 8px; font-size: .82rem; color: #475569; }
    .bl-periksa i { flex: 0 0 auto; margin-top: 2px; font-size: .85rem; }
    .bl-periksa .is-ok { color: #16a34a; }
    .bl-periksa .is-kurang { color: #cbd5e1; }
    .bl-periksa .is-wajib { color: #dc2626; }
    .bl-periksa li.is-selesai { color: #94a3b8; }

    .bl-kemajuan { height: 6px; border-radius: 999px; background: #f1f5f9; overflow: hidden; margin-bottom: 12px; }
    .bl-kemajuan span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #8b5cf6, #6d28d9); transition: width .3s ease; }

    /* ===== Pratinjau Google ===== */
    .bl-google { display: block; padding: 13px 15px; border-radius: 12px; background: #fff; border: 1px solid #eef2f7; }
    .bl-google-url { font-size: .76rem; color: #202124; margin-bottom: 2px; overflow-wrap: anywhere; }
    .bl-google-judul { font-size: 1.02rem; line-height: 1.35; color: #1a0dab; margin-bottom: 3px; overflow-wrap: anywhere; }
    .bl-google-ket { font-size: .82rem; line-height: 1.5; color: #4d5156; }
    .bl-hitung { display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; font-size: .74rem; font-weight: 700; color: #94a3b8; }
    .bl-hitung.is-pas { color: #16a34a; }
    .bl-hitung.is-lebih { color: #dc2626; }

    /* ===== Hasil periksa tautan ===== */
    .bl-tautan-daftar { display: grid; gap: 7px; margin: 10px 0 0; padding: 0; list-style: none; }
    .bl-tautan-daftar li { display: flex; align-items: flex-start; gap: 8px; font-size: .79rem; color: #475569; overflow-wrap: anywhere; }
    .bl-tautan-daftar i { flex: 0 0 auto; margin-top: 2px; }
    .bl-tautan-daftar .is-baik { color: #16a34a; }
    .bl-tautan-daftar .is-rusak { color: #dc2626; }
    .bl-tautan-daftar .is-ragu, .bl-tautan-daftar .is-lewat { color: #94a3b8; }
    .bl-tautan-daftar small { display: block; color: #94a3b8; }

    /* ===== Catatan simpan otomatis ===== */
    .bl-simpan-ket { display: flex; align-items: center; gap: 7px; margin-top: 9px; font-size: .76rem; color: #94a3b8; }
    .bl-simpan-ket.is-belum { color: #b45309; }

    /* ===== Editor: tinggi bisa diatur & mode fokus ===== */
    .bl-editor-alat { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; }
    .bl-editor-alat .bl-btn { height: 30px; padding: 0 10px; font-size: .74rem; }
    .bl-editor-alat .bl-hitung { margin: 0 0 0 auto; }
    .blog-editor .quill-container { resize: vertical; }
    body.bl-fokus .dsb-hero, body.bl-fokus .bl-form-samping, body.bl-fokus #sidebar, body.bl-fokus .bl-seo-panel { display: none !important; }
    body.bl-fokus .bl-form { grid-template-columns: minmax(0, 1fr) !important; }
    body.bl-fokus .quill-container { height: calc(100vh - 210px) !important; }

    /* ===== Kerangka kategori ===== */
    .bl-kat-kerangka { display: none; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); }
    .bl-kat-kerangka > div { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .bl-kat-kerangka span.bl-tulang.is-ubin { flex: 0 0 38px; height: 38px; border-radius: 12px; }
    .bl-kat-isi { flex: 1 1 auto; display: grid; gap: 7px; }
    .bl-kat-ket { margin: 3px 0 0; font-size: .78rem; line-height: 1.5; color: #94a3b8; }
    .bl-kat-baris { flex: 1 1 100%; display: grid; gap: 8px; }


    /* ===== Pita peringatan formulir (bentrok / sedang dibuka) ===== */
    .bl-pita { display: flex; align-items: flex-start; gap: 10px; flex-wrap: wrap; padding: 12px 15px; border-radius: 14px; font-size: .86rem; line-height: 1.55; margin-bottom: 14px; }
    .bl-pita i { flex: 0 0 auto; margin-top: 2px; font-size: 1rem; }
    .bl-pita-teks { flex: 1 1 220px; min-width: 0; }
    .bl-pita b { display: block; }
    .bl-pita.is-kunci { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .bl-pita.is-bentrok { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .bl-pita .bl-btn { height: 32px; }

    /* ===== Riwayat versi ===== */
    .bl-riwayat { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
    .bl-riwayat li { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; min-width: 0; padding: 9px 11px; border-radius: 11px; background: #f8fafc; border: 1px solid #eef2f7; }
    .bl-riwayat-teks { flex: 1 1 150px; min-width: 0; overflow: hidden; }
    .bl-riwayat li > .bl-btn:first-of-type { margin-left: auto; }
    .bl-riwayat-teks b { display: block; font-size: .82rem; color: #1c1f26; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bl-riwayat-teks small { display: block; font-size: .74rem; color: #94a3b8; }
    .bl-riwayat .bl-btn { flex: 0 0 auto; height: 30px; padding: 0 10px; font-size: .74rem; }

    /* ===== Grafik baca 30 hari ===== */
    .bl-grafik { display: block; width: 100%; height: 56px; }
    .bl-grafik-garis { fill: none; stroke: #7c3aed; stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
    .bl-grafik-isi { fill: rgba(124, 58, 237, .12); stroke: none; }
    .bl-grafik-ket { display: flex; justify-content: space-between; margin-top: 6px; font-size: .72rem; color: #94a3b8; }

    /* ===== Kata kunci fokus ===== */
    .bl-kunci-periksa { display: grid; gap: 6px; margin: 9px 0 0; padding: 0; list-style: none; }
    .bl-kunci-periksa li { display: flex; align-items: center; gap: 7px; font-size: .8rem; color: #475569; }
    .bl-kunci-periksa .is-ok { color: #16a34a; }
    .bl-kunci-periksa .is-kurang { color: #cbd5e1; }

    /* ===== Penanda belum tersimpan ===== */
    .bl-simpan-ket.is-kotor { color: #b45309; font-weight: 700; }
    .bl-titik-kotor { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; }

    /* ===== Impor ===== */
    .bl-impor { position: relative; overflow: hidden; }
    .bl-impor input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    /* ===== Tab bar: satu baris yang bisa digeser di ponsel ===== */
    @media (max-width: 575.98px) {
        .bl-status {
            flex-wrap: nowrap; overflow-x: auto; scroll-snap-type: x mandatory;
            margin-inline: -16px; padding-inline: 16px; padding-bottom: 4px;
            scrollbar-width: none;
        }
        .bl-status::-webkit-scrollbar { display: none; }
        .bl-status-btn { flex: 0 0 auto; min-width: 132px; scroll-snap-align: start; }
        .bl-status-teks span { white-space: nowrap; }
    }

    /* ===== Tautan hasil periksa ===== */
    .bl-tautan-daftar a { color: #4338ca; text-decoration: none; overflow-wrap: anywhere; }
    .bl-tautan-daftar a:hover { text-decoration: underline; }


    /* Penanda sekunder disembunyikan di bentuk tabel pada layar sedang:
       enam chip dalam satu baris membuat tingginya tidak lagi seragam. */
    @media (min-width: 768px) and (max-width: 1399.98px) {
        .bl-daftar.is-tabel .bl-tanda.is-sekunder { display: none; }
    }

    /* ===== Tampilan beda versi ===== */
    .bl-beda-label { margin: 12px 0 5px; font-size: .76rem; font-weight: 700; color: #475569; }
    .bl-beda {
        padding: 11px 13px; border-radius: 11px; background: #f8fafc; border: 1px solid #eef2f7;
        font-size: .84rem; line-height: 1.7; color: #334155; overflow-wrap: anywhere;
    }
    .bl-beda.is-panjang { max-height: 320px; overflow-y: auto; }
    .bl-beda .bd-buang { background: #fee2e2; color: #991b1b; text-decoration: line-through; padding: 0 2px; border-radius: 3px; }
    .bl-beda .bd-tambah { background: #dcfce7; color: #166534; text-decoration: none; padding: 0 2px; border-radius: 3px; }
    .bl-beda .bd-sama { color: #94a3b8; }

    /* ===== Pintasan ke pengaturan di ponsel ===== */
    .bl-lompat { display: none; }
    @media (max-width: 1199.98px) {
        .bl-lompat {
            display: inline-flex; align-items: center; gap: 7px; margin-bottom: 12px;
            padding: 9px 14px; border-radius: 11px; border: 1px dashed #c4b5fd;
            background: #faf5ff; color: #6d28d9; font-size: .82rem; font-weight: 700;
            text-decoration: none;
        }
    }

    /* ===== Tombol aksi di layar sentuh =====
       Tooltip tidak pernah muncul di layar sentuh, jadi lima ikon tanpa teks
       harus ditebak. Di perangkat sentuh labelnya dimunculkan lagi. */
    @media (pointer: coarse) and (min-width: 768px) {
        .bl-daftar.is-tabel .bl-aksi { flex: 1 1 100%; justify-content: flex-end; }
        .bl-daftar.is-tabel .bl-aksi-utama .bl-btn span { display: inline; }
        .bl-daftar.is-tabel .bl-aksi-utama .bl-btn { flex: 0 0 auto; padding: 0 14px; }
        .bl-daftar.is-tabel .bl-btn-ikon { min-width: 40px; }
    }

    /* ===== Petunjuk keadaan kosong ===== */
    .bl-petunjuk {
        display: flex; align-items: flex-start; gap: 9px; margin: 10px 0 0;
        padding: 10px 13px; border-radius: 11px; background: #f8fafc; border: 1px dashed #e2e8f0;
        font-size: .79rem; line-height: 1.55; color: #64748b;
    }
    .bl-petunjuk i { flex: 0 0 auto; margin-top: 2px; color: #94a3b8; }

    /* ===== Fokus papan tik ===== */
    .bl-btn:focus-visible, .bl-status-btn:focus-visible, .bl-chip-lepas:focus-visible,
    .bl-ringkas-blok:focus-visible, .bl-halaman .page-link:focus-visible, .bl-tanda.is-kategori:focus-visible {
        outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 10px;
    }

    /* ===== Cetak ===== */
    @media print {
        .dsb-hero-aksi, .bl-saring, .bl-aksi, .bl-status, .bl-halaman, .bl-kerangka { display: none !important; }
        .dsb, .dsb-kartu, .bl-kartu { box-shadow: none !important; }
        .bl-kartu { break-inside: avoid; border: 1px solid #cbd5e1 !important; }
        .bl-rak { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    }
</style>
@endonce
