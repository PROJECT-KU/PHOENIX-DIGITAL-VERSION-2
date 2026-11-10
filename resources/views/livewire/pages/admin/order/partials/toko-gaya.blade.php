{{-- Gaya khusus layar Pesanan Toko.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
{{-- Kartu tab dipakai bersama Master Harga, jadi definisinya hidup di
     partial bersama. DI LUAR <style> ini: partial itu membawa <style>
     miliknya sendiri, dan blok style bersarang tidak berlaku. --}}
@include('livewire.pages.admin.partials.kartu-tab-gaya')

@once
<style>
    /* .pt-daftar-kepala & .pt-daftar-judul PINDAH ke partials/kartu-tab-gaya
       (yang sudah di-include di atas): layar lain yang memakai kartu tab —
       Pendaftaran Trip, Lowongan — menggambarnya tanpa gaya selama aturannya
       hanya ada di sini. */

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
    /* Keterangan masa aktif selalu mulai baris baru di bawah lencana status,
       bukan berdempetan di sampingnya. */
    .pt-lencana-bawah { display: flex; width: fit-content; margin-top: 4px; }
    /* Di lencana biru (sudah dibeli ulang) penanda masa aktifnya tetap merah;
       di lencana merah ia mewarisi warna lencananya sendiri. */
    .pt-lencana-bawah .pt-habis-tegas { color: #b91c1c; font-weight: 800; }

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
