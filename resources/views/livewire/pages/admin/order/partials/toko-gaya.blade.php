{{-- Gaya khusus layar Pesanan Toko.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* Lencana angka kecil (dipakai tombol "Saringan lanjutan"). */
    .pt-tab-jumlah {
        min-width: 24px; height: 22px; padding: 0 7px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 800;
    }

    /* ===== Tab sebagai kartu status (dua kelompok) ===== */
    .pt-grup-deret { display: grid; gap: 12px; grid-template-columns: minmax(0, 7fr) minmax(0, 3fr); }
    @media (max-width: 1199.98px) { .pt-grup-deret { grid-template-columns: minmax(0, 1fr); } }
    .pt-grup {
        min-width: 0; padding: 12px; background: #fff;
        border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 18px;
    }
    .pt-grup-kepala { display: flex; align-items: baseline; gap: 8px; padding: 0 4px 10px; min-width: 0; }
    .pt-grup-judul { flex: 0 0 auto; font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #f26522; }
    .pt-grup-ket { min-width: 0; font-size: .74rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pt-kartu-deret { display: grid; gap: 8px; grid-template-columns: repeat(var(--n, 4), minmax(0, 1fr)); }
    .pt-kartu-tab {
        --c: #7c3aed;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        min-height: 104px; padding: 12px 8px; border-radius: 14px; cursor: pointer; text-align: center;
        background: #f8fafc; border: 1px solid transparent; color: #475569;
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }
    .pt-kartu-ikon {
        width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.05rem; color: var(--c); background: color-mix(in srgb, var(--c) 13%, #fff);
        transition: background .15s ease, color .15s ease;
    }
    .pt-kartu-teks { display: flex; flex-direction: column; align-items: center; gap: 1px; min-width: 0; }
    .pt-kartu-angka { font-size: 1.2rem; font-weight: 800; line-height: 1.1; color: #1c1f26; font-variant-numeric: tabular-nums; }
    .pt-kartu-label { font-size: .76rem; font-weight: 700; line-height: 1.25; color: #64748b; white-space: nowrap; }
    .pt-kartu-tab.is-nol .pt-kartu-angka { color: #cbd5e1; }
    .pt-kartu-tab.is-aktif {
        background: color-mix(in srgb, var(--c) 8%, #fff); border-color: color-mix(in srgb, var(--c) 45%, #fff);
        box-shadow: 0 6px 16px -8px color-mix(in srgb, var(--c) 60%, transparent);
    }
    .pt-kartu-tab.is-aktif .pt-kartu-ikon { background: var(--c); color: #fff; }
    .pt-kartu-tab.is-aktif .pt-kartu-angka, .pt-kartu-tab.is-aktif .pt-kartu-label { color: var(--c); }
    .pt-kartu-tab:focus-visible { outline: 2px solid var(--c); outline-offset: 2px; }
    @media (hover: hover) and (pointer: fine) {
        .pt-kartu-tab:not(.is-aktif):hover { background: #fff; border-color: color-mix(in srgb, var(--c) 30%, #fff); transform: translateY(-1px); }
    }
    /* Layar sempit: kartu mendatar yang bisa digulir, bukan tumpukan panjang. */
    @media (max-width: 767.98px) {
        .pt-kartu-deret {
            display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none;
            margin: 0 -12px; padding: 0 12px 2px; -webkit-overflow-scrolling: touch;
        }
        .pt-kartu-deret::-webkit-scrollbar { display: none; }
        .pt-kartu-tab { flex: 0 0 104px; min-height: 96px; scroll-snap-align: start; }
    }

    /* ===== Judul kecil di dalam kartu tabel ===== */
    .pt-daftar-kepala {
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px clamp(14px, 2vw, 18px); border-bottom: 1px solid #f1f5f9;
    }
    .pt-daftar-judul { flex: 1 1 auto; min-width: 0; }
    .pt-daftar-judul b { display: block; font-size: 1rem; font-weight: 800; color: #1c1f26; }
    .pt-daftar-judul span { display: block; font-size: .76rem; color: #94a3b8; }

    /* ===== Tabel pesanan di HP: kartu ringkas 3 baris, bukan 7 baris berlabel =====
       Urutan sel: 1 pesanan, 2 produk, 3 total, 4 pembayaran, 5 status, 6 tanggal, 7 aksi. */
    @media (max-width: 767.98px) {
        .pt-tabel-pesanan tbody tr {
            display: grid; grid-template-columns: auto auto minmax(0, 1fr) auto;
            column-gap: 8px; row-gap: 8px; align-items: center;
        }
        .pt-tabel-pesanan tbody td { display: block; padding: 0; min-width: 0; }
        .pt-tabel-pesanan tbody td::before { display: none !important; }
        .pt-tabel-pesanan tbody td:nth-child(1) { grid-column: 1 / 4; grid-row: 1; padding: 0; }
        .pt-tabel-pesanan tbody td:nth-child(3) { grid-column: 4; grid-row: 1; text-align: right; align-self: start; }
        .pt-tabel-pesanan tbody td:nth-child(2) { grid-column: 1 / 4; grid-row: 2; font-size: .8rem; color: #64748b; }
        /* Tanggal pindah ke baris meta di bawah nama (salinan .dsb-tabel-samar). */
        .pt-tabel-pesanan tbody td:nth-child(6) { display: none !important; }
        .pt-tabel-pesanan tbody td:nth-child(4) { grid-column: 1; grid-row: 3; }
        .pt-tabel-pesanan tbody td:nth-child(5) { grid-column: 2; grid-row: 3; }
        /* Lencana pembayaran & status tidak boleh lebih lebar dari kolomnya.
           min-width: 0 di atas membuat kolom auto boleh menyusut sampai nol
           saat baris sesak (total besar, huruf ponsel diperbesar) — lencana
           lalu meluber ke celah 8px dan "Dibatalkan" menempel ke lencana
           pembayaran. min-content = lebar lencana terlebar, tetap bisa turun
           baris untuk lencana kedua ("N item batal"). */
        .pt-tabel-pesanan tbody td:nth-child(4),
        .pt-tabel-pesanan tbody td:nth-child(5) { min-width: min-content; }
        /* Layar sangat sempit (< 360px): dua lencana + tombol aksi + total tidak
           muat sebaris, jadi status turun ke bawah lencana pembayaran. */
        @media (max-width: 359.98px) {
            .pt-tabel-pesanan tbody td:nth-child(5) { grid-column: 1 / 3; grid-row: 4; }
        }
        .pt-tabel-pesanan tbody td:nth-child(7) { grid-column: 3 / 5; grid-row: 3; text-align: right; }
        .pt-tabel-pesanan tbody td.is-kosong { display: none; }
        .pt-tabel-pesanan .dsb-tabel-samar { display: inline-flex !important; font-size: .74rem; }
    }

    /* HP: dua tombol kepala daftar berjajar satu baris, bukan bertumpuk. */
    @media (max-width: 575.98px) {
        .pt-hero-daftar .dsb-hero-aksi { flex-wrap: nowrap; gap: 8px; }
        /* Padding 14px (bukan 10px): dengan label yang dipendekkan, tombol
           punya ruang bernapas di kiri-kanan dan teks tidak lagi menempel
           atau meluber keluar tepinya. overflow: hidden sebagai jaring kalau
           huruf di perangkat diperbesar lewat pengaturan aksesibilitas. */
        .pt-hero-daftar .dsb-hero-aksi .dsb-tombol { min-width: 0; padding-left: 14px; padding-right: 14px; overflow: hidden; }
        .pt-hp-sembunyi { display: none; }
    }

    /* ===== Saringan ===== */
    .pt-saring { display: grid; gap: 12px; grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr)); }
    /* HP: pencarian selebar penuh, bulan & tahun berdampingan. */
    @media (max-width: 767.98px) {
        .pt-saring { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pt-saring:not(.pt-saring-lanjut) > .dsb-medan:first-child { grid-column: 1 / -1; }
    }
    .pt-saring-kaki {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9;
    }
    .pt-saring-kaki .dsb-kartu-sub { display: inline-flex; align-items: center; gap: 7px; }
    /* Buka-tutup saringan KHUSUS ponsel (pola Task Saya). Di layar lebar
       pembungkus bulan & tahun memakai display: contents — keduanya tetap
       anak grid .pt-saring, jadi susunan desktop tidak berubah, dan tombolnya
       tidak pernah tampil. */
    .pt-saring-hp { display: contents; }
    .pt-saring-tombol { display: none; }
    @media (max-width: 575.98px) {
        .pt-saring-tombol {
            grid-column: 1 / -1; display: flex; align-items: center; gap: 8px;
            min-height: 44px; padding: 0 14px; border-radius: 12px;
            border: 1px solid var(--dsb-tepi, #e9edf3); background: #fff;
            color: #334155; font-size: .88rem; font-weight: 700; text-align: left; cursor: pointer;
        }
        .pt-saring-tombol > .bi-sliders { color: #7c3aed; }
        .pt-saring-jumlah {
            padding: 2px 9px; border-radius: 999px; background: #f5f3ff; color: #6d28d9;
            font-size: .74rem; font-weight: 700;
        }
        .pt-saring-panah { margin-left: auto; color: #94a3b8; transition: transform .2s ease; }
        .pt-saring-kartu.is-buka .pt-saring-panah { transform: rotate(180deg); }
        /* Tertutup: bulan, tahun, saringan lanjutan + urutan, dan kaki disembunyikan. */
        .pt-saring-kartu:not(.is-buka) .pt-saring-hp,
        .pt-saring-kartu:not(.is-buka) .pt-lanjutan,
        .pt-saring-kartu:not(.is-buka) .pt-saring-kaki { display: none; }
    }
    @media (max-width: 575.98px) and (prefers-reduced-motion: reduce) {
        .pt-saring-panah { transition: none; }
    }

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
    .pt-lencana-kecil { display: inline-flex; margin-top: 4px; font-size: .66rem; }

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
