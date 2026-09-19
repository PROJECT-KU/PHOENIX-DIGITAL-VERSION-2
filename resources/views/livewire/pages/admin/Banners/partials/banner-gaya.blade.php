{{-- Gaya khusus layar Data Banner.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Kartu keadaan (sekaligus saringan) ===== */
    .bn-keadaan { display: grid; gap: 12px; grid-template-columns: repeat(5, minmax(0, 1fr)); margin-bottom: clamp(18px, 2.4vw, 26px); }
    .bn-keadaan-btn {
        --c: #7c3aed;
        display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; text-align: left; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .bn-keadaan-ikon { flex: 0 0 42px; width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff); }
    .bn-keadaan-teks { min-width: 0; }
    .bn-keadaan-teks b { display: block; font-size: 1.35rem; line-height: 1.1; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .bn-keadaan-teks span { display: block; font-size: .76rem; font-weight: 700; color: #64748b; white-space: nowrap; }
    .bn-keadaan-btn.is-aktif { border-color: color-mix(in srgb, var(--c) 50%, #fff); background: color-mix(in srgb, var(--c) 6%, #fff); box-shadow: 0 8px 20px -14px color-mix(in srgb, var(--c) 70%, transparent); }
    .bn-keadaan-btn.is-aktif .bn-keadaan-ikon { background: var(--c); color: #fff; }
    .bn-keadaan-btn.is-aktif .bn-keadaan-teks span { color: var(--c); }
    @media (hover: hover) and (pointer: fine) { .bn-keadaan-btn:not(.is-aktif):hover { border-color: color-mix(in srgb, var(--c) 30%, #fff); } }
    @media (max-width: 1199.98px) { .bn-keadaan { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    /* Ponsel: kartu mendatar yang bisa digulir. */
    @media (max-width: 767.98px) {
        .bn-keadaan { display: flex; overflow-x: auto; gap: 8px; scrollbar-width: none; margin-left: -4px; margin-right: -4px; padding: 0 4px 2px; }
        .bn-keadaan::-webkit-scrollbar { display: none; }
        .bn-keadaan-btn { flex: 0 0 150px; padding: 12px; }
        .bn-keadaan-ikon { flex-basis: 36px; width: 36px; height: 36px; }
    }

    /* ===== Saringan cari ===== */
    .bn-saring { margin-bottom: clamp(18px, 2.4vw, 26px); }
    .bn-saring-isi { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .bn-saring-isi .dsb-cari { flex: 1 1 280px; min-width: 0; }

    /* ===== Galeri banner ===== */
    .bn-galeri { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .bn-kartu {
        --c: #16a34a;
        display: flex; flex-direction: column; min-width: 0; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    @media (hover: hover) and (pointer: fine) {
        .bn-kartu:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -22px rgba(15, 23, 42, .45); }
        .bn-kartu:hover .bn-gambar img { transform: scale(1.03); }
    }
    /* Persegi — sama dengan potongan banner di beranda (aspect-ratio 1). */
    .bn-gambar { position: relative; display: block; width: 100%; aspect-ratio: 1; padding: 0; border: 0; cursor: pointer; overflow: hidden; background: #f1f5f9; }
    .bn-gambar img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .35s ease; }
    .bn-gambar-kosong { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #94a3b8; font-size: .78rem; font-weight: 700; }
    .bn-gambar-kosong span { width: 52px; height: 52px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; background: #fff; color: #cbd5e1; font-size: 1.4rem; }
    .bn-lencana-keadaan { position: absolute; left: 12px; top: 12px; box-shadow: 0 2px 8px rgba(15, 23, 42, .18); }
    .bn-kartu.is-mati .bn-gambar img { filter: grayscale(.8); opacity: .7; }
    .bn-kartu-isi { flex: 1 1 auto; min-width: 0; padding: 14px 16px 6px; }
    .bn-judul { margin: 0 0 6px; font-size: .95rem; font-weight: 800; line-height: 1.35; color: #1c1f26; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .bn-waktu { display: flex; align-items: center; gap: 6px; font-size: .76rem; color: #64748b; }
    .bn-waktu i { color: var(--c); }
    .bn-aksi { display: flex; align-items: center; gap: 6px; padding: 12px 16px 14px; margin-top: 8px; border-top: 1px dashed #eef2f7; }
    .bn-aksi-utama { margin-right: auto; }
    .bn-saklar.is-hidup { color: #15803d; border-color: #bbf7d0; background: #f0fdf4; }
    .bn-halaman { margin-top: 18px; display: flex; justify-content: center; }
    .bn-halaman .pagination { margin: 0; flex-wrap: wrap; justify-content: center; }
    /* Lencana keadaan di badan kartu: hanya ponsel (di layar lebar ada di atas gambar).
       HARUS sebelum media query di bawah supaya bisa ditimpa. */
    .bn-lencana-hp { display: none !important; }
    /* Ponsel: kartu mendatar — gambar persegi kecil di kiri. */
    @media (max-width: 575.98px) {
        .bn-galeri { grid-template-columns: minmax(0, 1fr); gap: 10px; }
        .bn-kartu { display: grid; grid-template-columns: 104px minmax(0, 1fr); grid-template-rows: 1fr auto; }
        .bn-gambar { grid-row: 1 / 3; aspect-ratio: auto; height: 100%; min-height: 128px; }
        .bn-lencana-keadaan { display: none; }
        .bn-kartu-isi { padding: 12px 12px 4px; }
        .bn-aksi { padding: 8px 12px 12px; margin-top: 0; border-top: 0; }
        .bn-lencana-hp { display: inline-flex !important; margin-bottom: 6px; }
    }

    /* ===== Jendela detail ===== */
    .bn-jendela { max-width: 760px; }
    .bn-detail { display: grid; gap: 18px; grid-template-columns: minmax(0, 300px) minmax(0, 1fr); align-items: start; }
    /* Tinggi ditentukan gambarnya sendiri (bukan aspect-ratio wadah): di dalam
       jendela yang bisa digulir, baris grid sempat menyusut dan gambar menimpa
       teks di bawahnya. */
    .bn-detail-gambar { position: relative; min-height: 180px; border-radius: 16px; overflow: hidden; background: #f1f5f9; border: 1px solid #eef2f7; }
    .bn-detail-gambar img { width: 100%; height: auto; aspect-ratio: 1; object-fit: cover; display: block; }
    .bn-detail-info { display: grid; gap: 14px; min-width: 0; }
    .bn-detail-label { display: block; margin-bottom: 5px; font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .bn-detail-info p { margin: 0; font-size: .88rem; color: #334155; line-height: 1.55; white-space: pre-line; }
    .bn-jadwal { display: grid; gap: 8px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bn-jadwal > div { padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; }
    .bn-jadwal small { display: block; font-size: .7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
    .bn-jadwal b { display: block; font-size: .84rem; color: #1c1f26; }
    .bn-detail-waktu { margin: 0; font-size: .74rem; color: #94a3b8; }
    .bn-detail-waktu span { display: block; }
    .bn-detail-kaki { justify-content: flex-end; gap: 8px; flex-wrap: wrap; }
    @media (max-width: 767.98px) {
        /* Flex kolom + flex-shrink 0: wadah jendela yang tingginya dibatasi
           membuat baris grid menyusut (terukur 248px untuk gambar 320px) dan
           gambar menimpa teks. Dengan ini kelebihannya digulir. */
        .bn-detail { display: flex; flex-direction: column; }
        .bn-detail > * { flex-shrink: 0; }
        .bn-detail-gambar { max-width: 320px; width: 100%; margin: 0 auto; }
    }
    @media (max-width: 575.98px) {
        .bn-detail-kaki .dsb-tombol { flex: 1 1 auto; justify-content: center; min-height: 44px; }
    }

    /* ===== Form tambah/ubah ===== */
    .bn-form-tata { display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 360px; align-items: start; }
    .bn-form-utama { display: grid; gap: 20px; min-width: 0; }
    .bn-form-samping { position: sticky; top: 16px; display: grid; gap: 16px; }
    @media (max-width: 991.98px) { .bn-form-tata { grid-template-columns: minmax(0, 1fr); } .bn-form-samping { position: static; } }
    .bn-form-kepala { display: flex; align-items: center; gap: 12px; padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
    .bn-form-kepala > div > b { display: block; font-size: .98rem; color: #1c1f26; }
    .bn-form-kepala > div > span { display: block; font-size: .8rem; color: #64748b; }
    .bn-medan + .bn-medan { margin-top: 16px; }
    .bn-galat { display: block; margin-top: 5px; font-size: .78rem; color: #dc2626; }
    .bn-bantu { display: block; margin-top: 5px; font-size: .74rem; color: #94a3b8; }
    .bn-desk-isian { min-height: 110px; resize: vertical; }
    .bn-status-pilih { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .bn-status-opsi { position: relative; display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 14px; border: 1.5px solid #e9edf3; cursor: pointer; background: #fff; transition: border-color .15s ease, background .15s ease; }
    .bn-status-opsi input { position: absolute; opacity: 0; pointer-events: none; }
    .bn-status-opsi > span:first-of-type { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .bn-status-opsi b { display: block; font-size: .88rem; color: #1c1f26; }
    .bn-status-opsi small { display: block; font-size: .74rem; color: #64748b; line-height: 1.4; }
    .bn-status-opsi.is-pilih { border-color: var(--c); background: color-mix(in srgb, var(--c) 6%, #fff); }
    .bn-status-opsi.is-pilih > span:first-of-type { background: var(--c); color: #fff; }
    @media (max-width: 420px) { .bn-status-pilih { grid-template-columns: minmax(0, 1fr); } }
    .bn-jadwal-isian { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (max-width: 575.98px) { .bn-jadwal-isian { grid-template-columns: minmax(0, 1fr); } }
    .bn-cepat { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 12px; }
    .bn-cepat > span { font-size: .74rem; font-weight: 700; color: #94a3b8; }
    .bn-cepat button { padding: 5px 11px; border-radius: 999px; border: 1px solid #ddd6fe; background: #f5f3ff; color: #6d28d9; font-size: .74rem; font-weight: 700; cursor: pointer; }
    .bn-cepat button:hover { background: #7c3aed; color: #fff; }
    .bn-catatan { display: flex; gap: 8px; margin-top: 12px; padding: 9px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #eef2f7; font-size: .76rem; color: #64748b; line-height: 1.5; }
    .bn-catatan i { color: #7c3aed; }

    /* Zona unggah gambar — persegi seperti di beranda */
    .bn-unggah { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; aspect-ratio: 1; padding: 18px; border-radius: 16px; border: 1.5px dashed #d8dcf0; background: linear-gradient(180deg, #fbfbff, #f5f6ff); text-align: center; overflow: hidden; transition: border-color .2s ease; }
    .bn-unggah:hover { border-color: #a78bfa; }
    .bn-unggah input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 2; }
    .bn-unggah.is-isi { border-style: solid; border-color: #e2e8f0; padding: 0; }
    .bn-unggah.is-isi img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .bn-unggah-ganti { position: absolute; left: 50%; bottom: 12px; transform: translateX(-50%); z-index: 1; display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 999px; background: rgba(15, 23, 42, .72); color: #fff; font-size: .76rem; font-weight: 700; white-space: nowrap; }
    .bn-unggah.is-galat { border-color: #fca5a5; background: #fef2f2; }
    .bn-unggah-ikon { width: 56px; height: 56px; border-radius: 17px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.55rem; background: #fff; color: #7c3aed; box-shadow: 0 8px 18px -10px rgba(76, 29, 149, .5); }
    .bn-unggah b { font-size: .9rem; color: #1c1f26; }
    .bn-unggah small { font-size: .76rem; color: #64748b; }
    .bn-unggah-muat { align-items: center; gap: 8px; font-size: .8rem; font-weight: 700; color: #6d28d9; margin-top: 10px; }
    .bn-simpan { width: 100%; justify-content: center; min-height: 48px; }
    .bn-isi-tombol { display: inline-flex; align-items: center; gap: 8px; }
    .bn-kicker { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #f26522; margin-bottom: 10px; }
    .bn-tips { margin: 12px 0 0; padding-left: 18px; font-size: .78rem; color: #64748b; line-height: 1.6; }
</style>
@endonce
