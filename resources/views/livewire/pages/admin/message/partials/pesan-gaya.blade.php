{{-- Gaya khusus layar Pesan Pelanggan (prefiks pp-).

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    [x-cloak] { display: none !important; }

    /* ===== Kartu status (sekaligus tab) ===== */
    .pp-status { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
    .pp-status-btn {
        --c: #7c3aed;
        flex: 1 1 170px;
        display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; text-align: left; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .pp-status-ikon { flex: 0 0 42px; width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); }
    .pp-status-teks { min-width: 0; }
    .pp-status-teks b { display: block; font-size: 1.5rem; line-height: 1.05; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .pp-status-teks span { display: block; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
    .pp-status-btn.is-aktif { border-color: color-mix(in srgb, var(--c) 50%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); box-shadow: 0 8px 20px -14px color-mix(in srgb, var(--c) 70%, transparent); }
    .pp-status-btn.is-aktif .pp-status-ikon { background: var(--c); color: #fff; }
    .pp-status-btn.is-aktif .pp-status-teks span { color: var(--c); }
    .pp-status-btn.is-perlu .pp-status-teks b { color: #d97706; }
    @media (hover: hover) and (pointer: fine) { .pp-status-btn:not(.is-aktif):hover { border-color: color-mix(in srgb, var(--c) 30%, #fff); } }
    @media (max-width: 575.98px) {
        .pp-status { gap: 8px; }
        .pp-status-btn { flex: 1 1 calc(50% - 4px); padding: 12px; }
        .pp-status-ikon { flex-basis: 36px; width: 36px; height: 36px; font-size: 1rem; }
        .pp-status-teks b { font-size: 1.25rem; }
        .pp-status-teks span { font-size: .72rem; white-space: normal; }
    }

    /* Isi tombol unduhan: ikon & teks sebaris, tidak berhimpitan. */
    .pp-isi-tombol { display: inline-flex; align-items: center; gap: 7px; }
    .pp-isi-tombol i.bi, .pp-isi-tombol i.bi::before { display: block; line-height: 1; }

    /* ===== Kartu ringkasan antrean ===== */
    .pp-ringkas { margin-bottom: 14px; }
    .pp-ringkas-isi { display: flex; align-items: center; gap: clamp(14px, 2.4vw, 26px); flex-wrap: wrap; }
    .pp-ringkas-blok { display: flex; align-items: flex-start; gap: 11px; flex: 1 1 250px; min-width: 0; padding: 13px 15px; border-radius: 14px; background: #f8fafc; border: 1px solid #eef2f7; text-align: left; font: inherit; color: inherit; cursor: pointer; transition: border-color .15s ease, background .15s ease, transform .15s ease; }
    @media (hover: hover) and (pointer: fine) { .pp-ringkas-blok:hover { border-color: #ddd6fe; background: #faf5ff; transform: translateY(-1px); } }
    .pp-ringkas-ikon { flex: 0 0 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; font-size: .95rem; }
    .pp-ringkas-ikon.is-tunggu { background: #fff7ed; color: #c2410c; }
    .pp-ringkas-ikon.is-mendesak { background: #fef2f2; color: #dc2626; }
    .pp-ringkas-ikon.is-aman { background: #ecfdf5; color: #15803d; }
    .pp-ringkas-blok p { margin: 0; font-size: .84rem; color: #334155; }
    .pp-ringkas-blok p b { font-size: 1rem; color: #1c1f26; }
    .pp-catatan-kecil { margin: 5px 0 0 !important; font-size: .74rem !important; line-height: 1.5; color: #94a3b8 !important; }

    /* ===== Bilah cari & saring ===== */
    .pp-saring { margin-bottom: 14px; }
    .pp-saring-isi { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .pp-saring-isi .dsb-cari { flex: 1 1 260px; min-width: 0; max-width: 420px; }
    .pp-saring-isi .pp-pilih:first-of-type { margin-left: auto; }
    .pp-pilih { flex: 0 0 auto; width: auto; min-width: 150px; }
    .pp-pilih.is-sempit { min-width: 124px; }
    .pp-saring-titik { width: 7px; height: 7px; border-radius: 50%; background: #7c3aed; }
    .pp-saring-lanjut { padding: 0 clamp(14px, 2vw, 20px) clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
    .pp-saring-baris { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); padding-top: 14px; }
    .pp-saring-medan { display: block; min-width: 0; }
    .pp-saring-medan > span { display: block; margin-bottom: 5px; font-size: .74rem; font-weight: 700; color: #64748b; }
    .pp-saring-medan .dsb-isian { width: 100%; }
    .pp-chip-saring { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 clamp(14px, 2vw, 20px) 13px; }
    .pp-chip-lepas { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 99px; border: 1px solid #ddd6fe; background: #f5f3ff; color: #5b21b6; font-size: .75rem; font-weight: 700; cursor: pointer; }
    .pp-chip-lepas:hover { background: #ede9fe; }
    .pp-chip-lepas i { font-size: .6rem; }
    .pp-chip-lepas.is-semua { border-color: #e5e7eb; background: #fff; color: #64748b; }
    .pp-ekspor-ket { margin: 0; padding: 0 clamp(14px, 2vw, 20px) 12px; font-size: .78rem; color: #6d28d9; }

    /* ===== Bilah aksi massal ===== */
    .pp-massal {
        position: sticky; top: 10px; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        margin-bottom: 14px; padding: 11px 14px; border-radius: 16px;
        background: linear-gradient(135deg, #faf7ff, #fff 55%); border: 1px solid #ddd6fe;
        box-shadow: 0 14px 30px -24px rgba(124, 58, 237, .55);
    }
    .pp-massal-jumlah { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px 5px 6px; border-radius: 99px; background: #fff; border: 1px solid #ede9fe; font-size: .8rem; font-weight: 700; color: #5b21b6; }
    .pp-massal-jumlah b { display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; padding: 0 7px; border-radius: 99px; background: #7c3aed; color: #fff; font-size: .82rem; }
    .pp-massal-tombol { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; align-items: center; }

    /* ===== Centang ===== */
    .pp-centang { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: .8rem; font-weight: 700; color: #475569; cursor: pointer; }
    .pp-centang input { width: 17px; height: 17px; accent-color: #7c3aed; cursor: pointer; }
    .pp-centang.is-kartu { flex: 0 0 auto; padding-top: 2px; }
    .pp-pilih-semua { margin-bottom: 10px; }

    /* ===== Rak kartu pesan ===== */
    /* Tanpa align-items: start, kartu sebaris ikut setinggi yang tertinggi
       sehingga deretan tombolnya rata — rak yang ragged terbaca berantakan. */
    .pp-rak { display: grid; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); align-items: stretch; }
    @media (max-width: 575.98px) { .pp-rak { grid-template-columns: minmax(0, 1fr); gap: 12px; } }
    .pp-kartu {
        --c: #64748b;
        position: relative; display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease;
    }
    .pp-kartu::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--c); }
    @media (hover: hover) and (pointer: fine) { .pp-kartu:hover { box-shadow: 0 16px 32px -24px rgba(15, 23, 42, .45); } }
    .pp-kartu.is-dipilih { box-shadow: 0 0 0 2px #7c3aed inset; }
    .pp-kartu.is-baru { background: linear-gradient(180deg, #fffdf7, #fff 120px); }

    .pp-kepala { display: flex; align-items: flex-start; gap: 12px; padding: 16px 16px 0 20px; }
    .pp-avatar { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 800; color: #fff; background: var(--av, #7c3aed); border: 0; padding: 0; cursor: pointer; }
    .pp-kepala-teks { flex: 1 1 auto; min-width: 0; }
    .pp-nama { margin: 0; font-size: .95rem; font-weight: 800; color: #1c1f26; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pp-tiket { display: inline-flex; align-items: center; gap: 5px; margin-top: 2px; font-size: .74rem; font-weight: 700; letter-spacing: .02em; color: #6d28d9; }
    .pp-kontak { display: block; margin-top: 2px; font-size: .76rem; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .pp-isi { flex: 1 1 auto; display: flex; flex-direction: column; padding: 12px 16px 0 20px; min-width: 0; }
    .pp-penanda { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .pp-tanda { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 99px; font-size: .7rem; font-weight: 700; }
    .pp-tanda i { font-size: .72rem; }
    .pp-tanda.is-baru { background: #fef3c7; color: #b45309; }
    .pp-tanda.is-lama { background: #ffedd5; color: #c2410c; }
    .pp-tanda.is-mendesak { background: #fef2f2; color: #b91c1c; }
    .pp-tanda.is-tinggi { background: #fff7ed; color: #c2410c; }
    .pp-tanda.is-sedang { background: #fefce8; color: #a16207; }
    .pp-tanda.is-rendah { background: #f1f5f9; color: #64748b; }
    .pp-pesan { flex: 1 1 auto; margin: 0; padding: 12px 14px; border-radius: 14px; background: #f8fafc; font-size: .86rem; line-height: 1.6; color: #334155; white-space: pre-line; overflow-wrap: anywhere; min-height: 74px; }
    .pp-waktu { margin-top: 8px; font-size: .72rem; color: #94a3b8; }

    .pp-aksi { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 16px 14px 20px; margin-top: 12px; border-top: 1px solid #f1f5f9; }
    .pp-aksi:empty { display: none; }
    .pp-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px;
        border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; color: #334155; font-size: .8rem; font-weight: 700;
        text-decoration: none; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .pp-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #1c1f26; }
    .pp-btn i.bi, .pp-btn i.bi::before { display: block; line-height: 1; }
    .pp-btn-ikon { width: 36px; padding: 0; flex: 0 0 36px; }
    .pp-aksi-utama { display: flex; gap: 8px; flex: 1 1 auto; }
    .pp-aksi-utama .pp-btn { flex: 1 1 0; }
    .pp-aksi-lain { display: flex; gap: 8px; flex: 1 1 auto; margin-left: auto; }
    .pp-aksi-lain .pp-btn-ikon { flex: 1 1 0; width: auto; min-width: 36px; }

    /* Ragam tombol WAJIB sesudah aturan dasar .pp-btn di atas. */
    .pp-btn.is-utama { background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-color: transparent; color: #fff; }
    .pp-btn.is-utama:hover { background: linear-gradient(135deg, #7c4ddb, #5b21b6); border-color: transparent; color: #fff; }
    .pp-btn.is-wa { color: #15803d; border-color: #bbf7d0; }
    .pp-btn.is-wa:hover { background: #f0fdf4; color: #166534; }
    .pp-btn.is-bahaya { color: #dc2626; }
    .pp-btn.is-bahaya:hover { background: #fef2f2; border-color: #fecaca; }
    .pp-btn.is-aktif { border-color: #c4b5fd; background: #f5f3ff; color: #6d28d9; }

    /* ===== Paginasi (dibatasi ke layar ini) ===== */
    .pp-halaman { margin-top: 18px; padding: 12px 16px; border-radius: 16px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .pp-halaman .pagination-wrap { gap: 12px; }
    .pp-halaman .small { font-size: .8rem !important; color: #64748b !important; }
    .pp-halaman .pagination { margin: 0; gap: 6px; flex-wrap: wrap; justify-content: center; }
    .pp-halaman .page-link {
        display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px;
        border: 1px solid #e9edf3 !important; border-radius: 11px !important; background: #fff !important;
        color: #475569 !important; font-size: .82rem; font-weight: 700; box-shadow: none !important;
    }
    .pp-halaman .page-link:hover { background: #f8fafc !important; border-color: #cbd5e1 !important; color: #1c1f26 !important; }
    .pp-halaman .page-item.active .page-link { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; border-color: transparent !important; color: #fff !important; }
    .pp-halaman .page-item.disabled .page-link { background: #f8fafc !important; color: #b0b7c3 !important; border-color: #f1f5f9 !important; }

    /* ===== Sebaran topik ===== */
    .pp-topik { margin-bottom: 14px; }
    .pp-topik-kepala { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
    .pp-topik-baris { display: grid; gap: 9px; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .pp-topik-item { display: block; padding: 9px 11px; border-radius: 12px; border: 1px solid #eef2f7; background: #f8fafc; text-align: left; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
    .pp-topik-item:hover { border-color: #ddd6fe; background: #faf5ff; }
    .pp-topik-item.is-aktif { border-color: #c4b5fd; background: #f5f3ff; }
    .pp-topik-label { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; font-size: .8rem; font-weight: 700; color: #475569; }
    .pp-topik-label b { font-size: .95rem; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .pp-topik-bar { display: block; margin-top: 7px; height: 6px; border-radius: 99px; background: #e9edf3; overflow: hidden; }
    .pp-topik-bar > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, #8b5cf6, #6d28d9); }

    /* ===== Kepala kolom yang bisa diurutkan ===== */
    .pp-th { display: inline-flex; align-items: center; gap: 5px; padding: 0; border: 0; background: none; font: inherit; color: inherit; text-transform: inherit; letter-spacing: inherit; cursor: pointer; }
    .pp-th i.bi { font-size: .78rem; color: #cbd5e1; }
    .pp-th:hover { color: #7c3aed; }
    .pp-th.is-aktif { color: #7c3aed; }
    .pp-th.is-aktif i.bi { color: #7c3aed; }

    /* ===== Petunjuk pintasan papan tik ===== */
    .pp-pintasan { margin-left: auto; font-size: .74rem; color: #94a3b8; white-space: nowrap; }
    .pp-pintasan kbd { padding: 1px 6px; border-radius: 6px; border: 1px solid #e2e8f0; border-bottom-width: 2px; background: #f8fafc; font-family: inherit; font-size: .72rem; color: #475569; }
    @media (max-width: 991.98px) { .pp-pintasan { display: none; } }

    /* ===== Pergantian bentuk daftar =====
       Kartunya TIDAK dibongkar saat bentuknya ditukar, jadi tinggal dianimasikan
       perpindahannya: padding & ukuran berubah halus, dan kartu yang masuk dari
       halaman/saringan baru tetap muncul sambil naik tipis. */
    .pp-kartu, .pp-kepala, .pp-isi, .pp-aksi, .pp-avatar, .pp-pesan {
        transition: padding .24s ease, gap .24s ease, flex-basis .24s ease, width .24s ease, height .24s ease, background .24s ease, font-size .24s ease;
    }
    .pp-rak { animation: pp-masuk .3s cubic-bezier(.22, .61, .36, 1) both; }
    @keyframes pp-masuk { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    @media (prefers-reduced-motion: reduce) {
        .pp-kartu, .pp-kepala, .pp-isi, .pp-aksi, .pp-avatar, .pp-pesan { transition: none; }
        .pp-rak { animation: none; }
    }

    /* ===== Kerangka pemuatan ===== */
    .pp-sembunyi { display: none !important; }
    .pp-kerangka { display: none; gap: 14px; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); }
    .pp-kerangka-kartu { padding: 16px; border-radius: 18px; background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); }
    .pp-kerangka-kepala { display: flex; align-items: center; gap: 12px; }
    .pp-tulang { display: block; height: 12px; border-radius: 8px; background: linear-gradient(90deg, #f1f5f9 25%, #e8edf4 37%, #f1f5f9 63%); background-size: 400% 100%; animation: pp-kilau 1.3s ease infinite; }
    .pp-tulang.is-bulat { flex: 0 0 44px; width: 44px; height: 44px; border-radius: 50%; }
    @keyframes pp-kilau { 0% { background-position: 100% 50%; } 100% { background-position: 0 50%; } }
    @media (prefers-reduced-motion: reduce) { .pp-tulang { animation: none; } }

    .sorot-kata { background: #fef08a; color: inherit; padding: 0 2px; border-radius: 3px; }

    /* ===== Halaman detail ===== */
    .pp-detail { display: grid; gap: 14px; grid-template-columns: minmax(0, 1.8fr) minmax(280px, 1fr); align-items: start; }
    @media (max-width: 991.98px) { .pp-detail { grid-template-columns: minmax(0, 1fr); } }
    .pp-detail-utama, .pp-detail-samping { display: grid; gap: 14px; }
    /* Kolom kanan ikut menggulung: linimasa bisa panjang, dan kotak status
       harus tetap terjangkau tanpa menggulung balik ke atas. */
    @media (min-width: 992px) {
        .pp-detail-samping { position: sticky; top: 14px; align-self: start; max-height: calc(100vh - 28px); overflow-y: auto; }
    }
    .pp-pengirim { display: flex; align-items: center; gap: 14px; }
    .pp-pengirim .pp-avatar { flex-basis: 54px; width: 54px; height: 54px; font-size: 1.2rem; }
    .pp-pengirim-teks { min-width: 0; flex: 1 1 auto; }
    .pp-pengirim-teks b { display: block; font-size: 1.05rem; color: #1c1f26; }
    .pp-pengirim-teks span { display: block; font-size: .82rem; color: #64748b; overflow-wrap: anywhere; }
    .pp-kutip { margin: 14px 0 0; padding: 16px 18px; border-radius: 16px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .95rem; line-height: 1.75; color: #334155; white-space: pre-line; overflow-wrap: anywhere; }
    .pp-detail-label { display: block; margin-bottom: 8px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .pp-balas { margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
    .pp-balas-tombol { display: flex; gap: 8px; flex-wrap: wrap; }
    .pp-medan { display: block; margin-bottom: 12px; }
    .pp-medan > span { display: block; margin-bottom: 5px; font-size: .78rem; font-weight: 700; color: #64748b; }
    .pp-medan .dsb-isian { width: 100%; }
    .pp-info { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .pp-info.is-tunggal { grid-template-columns: minmax(0, 1fr); }
    .pp-info > div { padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; min-width: 0; }
    .pp-info small { display: block; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .pp-info b { display: block; font-size: .88rem; color: #1c1f26; overflow-wrap: anywhere; }
    @media (max-width: 575.98px) { .pp-info { grid-template-columns: minmax(0, 1fr); } }
    .pp-teknis-pemicu { display: flex; align-items: center; gap: 12px; width: 100%; padding: 0; border: 0; background: none; text-align: left; cursor: pointer; color: #334155; }
    .pp-teknis-pemicu b { display: block; font-size: .9rem; color: #1c1f26; }
    .pp-teknis-pemicu small { display: block; font-size: .76rem; color: #94a3b8; }
    .pp-teknis-pemicu > i { margin-left: auto; color: #94a3b8; }

    /* ===== Saklar tampilan kartu/tabel ===== */
    .pp-saklar { position: relative; display: inline-flex; padding: 3px; gap: 3px; border-radius: 12px; background: #f1f5f9; border: 1px solid #e9edf3; }
    /* Pil putihnya satu elemen yang BERGESER, bukan latar yang berpindah dari
       satu tombol ke tombol lain — pergantiannya jadi terbaca, tidak berkedip. */
    .pp-saklar-pil { position: absolute; top: 3px; bottom: 3px; left: 3px; width: calc(50% - 4.5px); border-radius: 9px; background: #fff; box-shadow: 0 2px 6px -3px rgba(15, 23, 42, .3); transition: transform .26s cubic-bezier(.22, .61, .36, 1); }
    .pp-saklar.is-tabel .pp-saklar-pil { transform: translateX(calc(100% + 3px)); }
    .pp-saklar button { position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex: 1 1 0; padding: 6px 11px; border: 0; border-radius: 9px; background: none; font-size: .78rem; font-weight: 700; color: #64748b; cursor: pointer; transition: color .2s ease; }
    .pp-saklar button.is-aktif { color: #7c3aed; }
    @media (max-width: 575.98px) { .pp-saklar { display: none; } }
    @media (prefers-reduced-motion: reduce) { .pp-saklar-pil { transition: none; } }

    /* ===== Bentuk TABEL: kartu yang sama, disusun jadi baris =====
       Sengaja BUKAN elemen <table> tersendiri. Menukar rak kartu dengan tabel
       berarti seluruh DOM-nya dibongkar lalu dibangun ulang, dan pergantiannya
       terasa menyentak. Dengan satu markup, kartunya cuma bergeser posisi. */
    .pp-tabel-kepala { display: none; gap: 10px; padding: 0 16px 8px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    /* Lebarnya disamakan dengan .pp-kepala / .pp-isi / .pp-aksi di barisnya. */
    .pp-tabel-kepala > *:nth-child(1) { flex: 1 1 230px; }
    .pp-tabel-kepala > *:nth-child(2) { flex: 2 1 260px; }
    .pp-tabel-kepala > *:nth-child(3) { flex: 0 0 auto; margin-left: auto; }
    .pp-tabel-kepala button { display: inline-flex; align-items: center; gap: 5px; padding: 0; border: 0; background: none; font: inherit; color: inherit; text-align: left; cursor: pointer; }
    .pp-tabel-kepala button:hover { color: #64748b; }
    .pp-tabel-kepala button.is-aktif { color: #6d28d9; }
    .pp-tabel-kepala i { font-size: .68rem; }
    @media (min-width: 768px) { .pp-daftar.is-tabel .pp-tabel-kepala { display: flex; } }

    @media (min-width: 768px) {
        .pp-daftar.is-tabel .pp-rak { grid-template-columns: minmax(0, 1fr); gap: 8px; }
        .pp-daftar.is-tabel .pp-kartu { flex-direction: row; align-items: center; flex-wrap: wrap; gap: 10px; padding: 8px 12px 8px 16px; }
        .pp-daftar.is-tabel .pp-kepala { flex: 1 1 230px; padding: 0; align-items: center; }
        .pp-daftar.is-tabel .pp-avatar { flex-basis: 34px; width: 34px; height: 34px; font-size: .84rem; }
        .pp-daftar.is-tabel .pp-kontak { display: none; }
        .pp-daftar.is-tabel .pp-kepala .dsb-lencana { flex: 0 0 110px; }
        .pp-daftar.is-tabel .pp-isi { flex: 2 1 260px; padding: 0; }
        .pp-daftar.is-tabel .pp-penanda { margin: 0 0 4px; flex-wrap: nowrap; overflow: hidden; }
        .pp-daftar.is-tabel .pp-pesan { flex: 0 0 auto; min-height: 0; padding: 0; background: none; font-size: .82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pp-daftar.is-tabel .pp-waktu { margin-top: 2px; }
        .pp-daftar.is-tabel .pp-aksi { flex: 0 0 auto; margin-top: 0; padding: 0; border-top: 0; }
        .pp-daftar.is-tabel .pp-aksi-utama { flex: 0 0 auto; }
        .pp-daftar.is-tabel .pp-aksi-utama .pp-btn span { display: none; }
        .pp-daftar.is-tabel .pp-aksi-utama .pp-btn { flex: 0 0 36px; padding: 0; }
        .pp-daftar.is-tabel .pp-aksi-lain { flex: 0 0 auto; }
        .pp-daftar.is-tabel .pp-aksi-lain .pp-btn-ikon { flex: 0 0 36px; }
        /* Nomor tiket jangan dipatahkan jadi dua baris — itu yang bikin baris
           tingginya tidak seragam di layar sempit. */
        .pp-daftar.is-tabel .pp-tiket { white-space: nowrap; }
    }
    /* Tablet: ruangnya lebih sempit, jadi pangkalan flex-nya dikecilkan supaya
       barisnya tetap satu baris selama mungkin. */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .pp-daftar.is-tabel .pp-kepala { flex: 1 1 180px; }
        .pp-daftar.is-tabel .pp-isi { flex: 2 1 200px; }
        .pp-daftar.is-tabel .pp-kepala .dsb-lencana { flex: 0 0 auto; }
        .pp-daftar.is-tabel .pp-penanda { flex-wrap: wrap; }
    }

    /* ===== Penanda tambahan ===== */
    .pp-tanda.is-lewat { background: #fef2f2; color: #b91c1c; }
    .pp-tanda.is-dibalas { background: #ecfdf5; color: #15803d; }
    .pp-tanda.is-topik { background: #f5f3ff; color: #6d28d9; }
    .pp-tanda.is-petugas { background: #ecfeff; color: #0e7490; }
    .pp-tanda.is-spam { background: #fef2f2; color: #b91c1c; }

    /* ===== Linimasa tiket ===== */
    .pp-linimasa { list-style: none; margin: 14px 0 0; padding: 0 0 0 26px; position: relative; }
    .pp-linimasa::before { content: ''; position: absolute; left: 10px; top: 6px; bottom: 6px; width: 2px; background: #eef2f7; }
    .pp-baris { position: relative; padding: 0 0 16px; }
    .pp-baris:last-child { padding-bottom: 0; }
    .pp-baris-ikon { --c: #64748b; position: absolute; left: -26px; top: 0; width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .68rem; color: #fff; background: var(--c); box-shadow: 0 0 0 3px #fff; }
    .pp-baris-kepala { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
    .pp-baris-kepala b { font-size: .86rem; color: #1c1f26; }
    .pp-baris-kepala small { font-size: .74rem; color: #94a3b8; }
    .pp-baris-isi { margin: 6px 0 0; padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .85rem; color: #334155; white-space: pre-wrap; overflow-wrap: anywhere; }
    .pp-baris.is-catatan .pp-baris-isi { background: #fffbeb; border-color: #fde68a; }
    .pp-baris.is-balasan .pp-baris-isi { background: #f0fdf4; border-color: #bbf7d0; }

    /* ===== Kotak balasan & catatan ===== */
    .pp-tulis { margin-top: 14px; }
    .pp-tulis textarea.dsb-isian { width: 100%; min-height: 104px; resize: vertical; padding: 11px 13px; line-height: 1.55; }
    .pp-tulis-baris { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
    .pp-tulis-baris .pp-pilih { min-width: 140px; }
    .pp-tulis-baris .pp-centang { margin-right: auto; }
    .pp-galat { display: block; margin-top: 6px; font-size: .78rem; font-weight: 600; color: #dc2626; }
    .pp-template { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 10px; }
    .pp-template-btn { display: inline-flex; align-items: center; gap: 6px; padding: 6px 11px; border-radius: 999px; border: 1px dashed #d8b4fe; background: #faf5ff; color: #6d28d9; font-size: .77rem; font-weight: 700; cursor: pointer; }
    .pp-template-btn:hover { background: #f3e8ff; }
    .pp-template-kelola { display: grid; gap: 9px; margin-top: 12px; padding: 12px; border-radius: 13px; background: #f8fafc; border: 1px solid #eef2f7; }
    .pp-template-daftar { display: grid; gap: 7px; }
    .pp-template-item { display: flex; align-items: center; gap: 9px; padding: 8px 11px; border-radius: 10px; background: #fff; border: 1px solid #eef2f7; font-size: .8rem; }
    .pp-template-item b { flex: 1 1 auto; min-width: 0; color: #1c1f26; }

    /* ===== Lampiran ===== */
    .pp-lampiran { margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
    .pp-lampiran-baris { display: flex; align-items: center; gap: 10px; padding: 9px 11px; margin-bottom: 8px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; }
    .pp-lampiran-baris > i.bi { font-size: 1.05rem; color: #0891b2; }
    .pp-lampiran-baris > span { flex: 1 1 auto; min-width: 0; }
    .pp-lampiran-baris b { display: block; font-size: .85rem; color: #1c1f26; overflow-wrap: anywhere; }
    .pp-lampiran-baris small { display: block; font-size: .73rem; color: #94a3b8; }
    .pp-lampiran-unggah { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; margin-top: 10px; }
    .pp-berkas { display: flex; align-items: center; gap: 9px; flex: 1 1 260px; min-width: 0; padding: 9px 12px; border: 1.5px dashed #e2e8f0; border-radius: 12px; background: #fff; font-size: .82rem; color: #475569; cursor: pointer; }
    .pp-berkas:hover { border-color: #c4b5fd; background: #faf5ff; }
    .pp-berkas i.bi { color: #7c3aed; }
    .pp-berkas span { min-width: 0; overflow-wrap: anywhere; }
    .pp-berkas input[type="file"] { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }

    /* ===== Linimasa yang dilipat ===== */
    .pp-lipat { margin-bottom: 12px; }
    .pp-template-item.is-diedit { border-color: #c4b5fd; background: #faf5ff; }
    .pp-template-aksi { display: flex; gap: 8px; flex-wrap: wrap; }
    .pp-kait-gabung { display: flex; align-items: stretch; gap: 8px; }
    .pp-kait-gabung .pp-kait-baris { flex: 1 1 auto; }
    /* Chip template di ponsel: satu baris yang bisa digeser, bukan menumpuk
       empat baris yang memakan setengah layar. */
    @media (max-width: 575.98px) {
        .pp-template { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none; }
        .pp-template::-webkit-scrollbar { display: none; }
        .pp-template-btn { flex: 0 0 auto; }
    }

    /* ===== Kaitan pelanggan & tiket tetangga ===== */
    .pp-kait { display: grid; gap: 8px; }
    .pp-kait-baris { display: flex; align-items: center; gap: 10px; padding: 9px 11px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .82rem; color: #334155; text-decoration: none; }
    .pp-kait-baris:hover { border-color: #ddd6fe; color: #4c1d95; }
    .pp-kait-baris i { color: #7c3aed; }
    .pp-kait-baris span { min-width: 0; overflow-wrap: anywhere; }
    .pp-kait-baris b { display: block; font-size: .84rem; color: #1c1f26; }
    .pp-kait-baris small { display: block; font-size: .73rem; color: #94a3b8; }
    .pp-tetangga { display: flex; gap: 8px; flex-wrap: wrap; }

    /* ===== Fokus papan tik ===== */
    .pp-btn:focus-visible, .pp-status-btn:focus-visible, .pp-chip-lepas:focus-visible,
    .pp-avatar:focus-visible, .pp-centang input:focus-visible, .pp-halaman .page-link:focus-visible {
        outline: 2px solid #7c3aed; outline-offset: 2px; border-radius: 10px;
    }

    /* ===== Cetak ===== */
    @media print {
        .dsb-hero-aksi, .pp-saring, .pp-massal, .pp-pilih-semua, .pp-aksi, .pp-status,
        .pp-halaman, .pp-kerangka { display: none !important; }
        .dsb, .dsb-kartu, .pp-kartu { box-shadow: none !important; }
        .pp-kartu { break-inside: avoid; border: 1px solid #cbd5e1 !important; }
        .pp-rak { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .pp-pesan { flex: 0 0 auto !important; min-height: 0 !important; background: none !important; padding-left: 0 !important; }
    }
</style>
@endonce
