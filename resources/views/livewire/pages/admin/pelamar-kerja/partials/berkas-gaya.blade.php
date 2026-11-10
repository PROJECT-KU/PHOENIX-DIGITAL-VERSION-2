{{-- Gaya pembaca berkas pelamar. Dipakai bersama layar daftar (jendela
     sembul) dan layar detail, jadi definisinya hidup di satu tempat: disalin
     ke dua berkas, yang satu pasti tertinggal begitu yang lain disentuh.

     Inline, bukan lewat Vite: public/build masuk .gitignore dan tidak ikut
     ter-deploy. --}}
@once
<style>
    .pbk { display: flex; flex-direction: column; min-height: 0; }

    /* ===== Tab berkas ===== */
    .pbk-tab { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px; }
    .pbk-tombol {
        display: inline-flex; align-items: center; gap: 7px; cursor: pointer;
        padding: 8px 14px; border-radius: 999px; font-size: .82rem; font-weight: 700;
        background: #fff; color: #475569; border: 1px solid var(--dsb-tepi, #e9edf3);
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .pbk-tombol i.bi { font-size: .9rem; line-height: 1; }
    .pbk-tombol:hover:not(:disabled) { border-color: #c4b5fd; color: #6d28d9; }
    .pbk-tombol.is-aktif { background: #7c3aed; color: #fff; border-color: transparent; }
    /* Berkas yang tidak dilampirkan tetap TERLIHAT, hanya tidak bisa ditekan:
       tab yang hilang membuat admin mengira berkasnya belum dimuat. */
    .pbk-tombol.is-kosong { opacity: .5; cursor: default; }
    .pbk-tanda { font-weight: 800; color: #94a3b8; }

    /* ===== Bingkai pembaca =====
       Tingginya mengikuti layar, bukan dipatok piksel: pada laptop 13 inci
       bingkai setinggi A4 penuh mendorong tombol unduh keluar jangkauan. */
    .pbk-bingkai {
        border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
        overflow: hidden; background: #f8fafc; flex: 1 1 auto; min-height: 0;
    }
    .pbk-bingkai iframe { display: block; width: 100%; height: min(72vh, 860px); border: 0; }

    .pbk-kaki {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 10px; margin-top: 11px;
    }
    .pbk-kaki-ket { color: var(--dsb-redup, #6b7280); font-size: .76rem; }
    .pbk-kaki-aksi { display: inline-flex; flex-wrap: wrap; gap: 8px; }

    .pbk-kabar {
        display: flex; gap: 10px; padding: 12px 14px; border-radius: 12px;
        background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
        color: var(--dsb-redup, #6b7280); font-size: .82rem; line-height: 1.6;
    }
    .pbk-kabar > i.bi { line-height: 1.5; flex-shrink: 0; }
    .pbk-kabar strong { color: var(--dsb-tinta, #1c1f26); }

    /* ===== Jendela sembul pembaca (dipakai layar daftar) =====
       Supaya CV bisa dibaca TANPA meninggalkan daftarnya: admin yang sedang
       menyaring sepuluh pelamar kehilangan posisi gulung & saringannya tiap
       kali membuka halaman detail, lalu kembali ke daftar yang sudah
       ter-reset. */
    .pbk-latar {
        position: fixed; inset: 0; z-index: 1050;
        background: rgba(15, 23, 42, .5); backdrop-filter: blur(3px);
    }
    .pbk-jendela {
        position: fixed; inset: 0; z-index: 1051;
        display: flex; align-items: center; justify-content: center;
        padding: clamp(10px, 3vw, 32px); pointer-events: none;
    }
    .pbk-kotak {
        pointer-events: auto; width: min(960px, 100%); max-height: 100%;
        display: flex; flex-direction: column; min-height: 0;
        background: #fff; border-radius: 18px; border: 1px solid var(--dsb-tepi, #e9edf3);
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    }
    .pbk-kotak-kepala {
        display: flex; align-items: center; gap: 12px;
        padding: 15px 18px; border-bottom: 1px solid #f1f5f9; flex: 0 0 auto;
    }
    .pbk-kotak-teks { flex: 1 1 auto; min-width: 0; }
    .pbk-kotak-judul {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        font-size: 1rem; color: var(--dsb-tinta, #1c1f26); margin: 0;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .pbk-kotak-sub { display: block; color: var(--dsb-redup, #6b7280); font-size: .78rem; margin-top: 2px; }
    .pbk-tutup {
        flex: 0 0 auto; width: 34px; height: 34px; border-radius: 10px; cursor: pointer;
        background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); color: #64748b;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .pbk-tutup:hover { background: #f8fafc; color: #1c1f26; }
    .pbk-kotak-isi { padding: 16px 18px 18px; overflow-y: auto; min-height: 0; }
    .pbk-kotak .pbk-bingkai iframe { height: min(62vh, 720px); }
</style>
@endonce
