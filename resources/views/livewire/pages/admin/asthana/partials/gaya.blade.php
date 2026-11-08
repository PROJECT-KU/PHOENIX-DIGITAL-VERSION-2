<style>
    /* Layar isi situs Asthana. Menumpang bahasa rupa dsb-*; yang ada di sini
       hanya yang khusus layar ini. Inline karena public/build tidak ikut deploy. */
    .ast-nav {
        display: flex; gap: 8px; overflow-x: auto; padding: 2px 2px 6px;
        margin-bottom: clamp(16px, 2.4vw, 22px); scrollbar-width: thin;
    }
    .ast-nav-item {
        display: inline-flex; align-items: center; gap: 8px; flex-shrink: 0;
        padding: 9px 16px; border-radius: 12px; border: 1px solid var(--dsb-tepi);
        background: #fff; color: #475569; font-weight: 700; font-size: .86rem; text-decoration: none;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    .ast-nav-item i { color: var(--c); }
    .ast-nav-item:hover { border-color: var(--c); color: var(--dsb-tinta); }
    .ast-nav-item.is-aktif { background: var(--c); border-color: var(--c); color: #fff; }
    .ast-nav-item.is-aktif i { color: #fff; }

    /* Kartu daftar pendek */
    .ast-kisi { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
    .ast-kartu { padding: 0; }
    .ast-kartu.is-sembunyi { opacity: .6; }
    .ast-kartu-rupa {
        position: relative; height: 132px; display: grid; place-items: center;
        background: linear-gradient(135deg, #f1f5ff, #eef7fb); border-bottom: 1px solid var(--dsb-tepi);
    }
    /* Ukuran dipatok piksel: batas persen tidak berlaku pada gambar di dalam
       kisi ini, dan logo persegi meluber menimpa nama di bawahnya. */
    .ast-kartu-rupa { overflow: hidden; }
    .ast-kartu-rupa.is-logo img { object-fit: contain; max-width: 75%; max-height: 92px; }
    .ast-kartu-rupa.is-foto img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ast-kartu-rupa .ast-kosong { color: #a9b8cc; font-size: 1.8rem; }
    .ast-kartu-rupa .ast-angka { font-size: 2.2rem; font-weight: 800; color: var(--c, #2455e6); letter-spacing: -.03em; }
    .ast-kartu-rupa .ast-angka-ikon { position: absolute; left: 14px; top: 12px; color: var(--c, #2455e6); font-size: 1.1rem; }
    .ast-kartu-tanda { position: absolute; top: 10px; right: 10px; }
    .ast-kartu-badan { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 6px; flex: 1 1 auto; }
    .ast-kartu-nama { font-weight: 800; color: var(--dsb-tinta); font-size: .95rem; margin: 0; }
    .ast-kartu-ket {
        color: var(--dsb-redup); font-size: .8rem; line-height: 1.5; margin: 0;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ast-kartu-aksi { display: flex; gap: 6px; margin-top: auto; padding-top: 10px; flex-wrap: wrap; }
    .ast-kartu-aksi .dsb-tombol { flex: 1 1 auto; justify-content: center; }
    .ast-ikon-btn {
        width: 34px; height: 34px; display: inline-grid; place-items: center; flex-shrink: 0;
        border-radius: 10px; border: 1px solid var(--dsb-tepi); background: #fff; color: #64748b; padding: 0;
    }
    .ast-ikon-btn:hover:not(:disabled) { border-color: #cbd5e1; color: var(--dsb-tinta); }
    .ast-ikon-btn:disabled { opacity: .35; cursor: not-allowed; }

    /* Isian jendela & formulir */
    .ast-isian-deret { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .ast-isian-deret.is-tiga { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    @media (max-width: 575.98px) {
        .ast-isian-deret, .ast-isian-deret.is-tiga { grid-template-columns: 1fr; }
    }
    .ast-tumpuk { display: grid; gap: 14px; }
    .ast-saklar { display: inline-flex; align-items: center; gap: 10px; font-weight: 700; color: var(--dsb-tinta); font-size: .86rem; cursor: pointer; }
    .ast-saklar input { width: 18px; height: 18px; accent-color: #7c3aed; }

    .ast-unggah { display: flex; gap: 14px; align-items: center; padding: 12px; border: 1px dashed #cbd5e1; border-radius: 14px; background: #fbfcfe; }
    .ast-unggah-rupa {
        position: relative; width: 84px; height: 84px; flex-shrink: 0; border-radius: 12px; overflow: hidden;
        background: #eef2f7; display: grid; place-items: center; color: #a9b8cc; font-size: 1.4rem;
    }
    .ast-unggah-rupa img { width: 100%; height: 100%; object-fit: cover; }
    .ast-unggah-rupa.is-logo img { object-fit: contain; padding: 6px; background: #fff; }
    .ast-naik { position: absolute; inset: 0; background: rgba(255,255,255,.75); }
    .ast-naik-isi { position: absolute; inset: 0; display: grid; place-items: center; }
    .ast-unggah-isi { min-width: 0; flex: 1 1 auto; display: grid; gap: 4px; }
    .ast-unggah-isi input[type=file] { font-size: .8rem; max-width: 100%; }
    .ast-catatan { color: var(--dsb-redup); font-size: .76rem; }

    /* Tabel tulisan */
    .ast-sampul-mini { width: 64px; height: 44px; border-radius: 9px; object-fit: cover; background: #eef2f7; flex-shrink: 0; display: grid; place-items: center; color: #a9b8cc; }

    /* Penyunting Quill dalam bahasa rupa dsb */
    .ast-penyunting .ql-toolbar.ql-snow { border: 1px solid var(--dsb-tepi); border-radius: 12px 12px 0 0; background: #fbfcfe; }
    .ast-penyunting .ql-container.ql-snow { border: 1px solid var(--dsb-tepi); border-top: 0; border-radius: 0 0 12px 12px; min-height: 340px; font-size: .95rem; font-family: inherit; }
    .ast-penyunting .ql-editor { min-height: 340px; line-height: 1.7; }

    .ast-galeri { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px; }
    .ast-galeri-butir { position: relative; aspect-ratio: 4 / 3; border-radius: 12px; overflow: hidden; background: #eef2f7; }
    .ast-galeri-butir img { width: 100%; height: 100%; object-fit: cover; }
    .ast-galeri-butir button {
        position: absolute; top: 6px; right: 6px; width: 28px; height: 28px; border-radius: 8px;
        border: 0; background: rgba(15, 23, 42, .65); color: #fff; display: grid; place-items: center; padding: 0;
    }
    .ast-galeri-butir.is-baru { outline: 2px solid #16a34a; outline-offset: -2px; }
</style>
