{{-- Tab berbentuk KARTU STATUS, dikelompokkan.

     Dipakai bersama Pesanan Toko dan Master Harga. Dipisah ke sini supaya
     satu rupa punya satu definisi: disalin ke dua berkas, yang satu pasti
     tertinggal begitu yang lain disentuh, dan dua layar yang seharusnya
     kembar mulai berbeda tanpa ada yang memutuskannya.

     Dasarnya bahasa rupa dasbor (partials/dasbor-gaya) — di sini hanya yang
     tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
     .gitignore dan tidak ikut ter-deploy. --}}
@once
<style>
    /* ===== Judul kecil di dalam kartu tabel =====
       Hidup di sini, bukan di toko-gaya: ia dipakai layar mana pun yang
       memakai kartu tab — Pendaftaran Trip dan Lowongan ikut memakainya,
       dan keduanya sempat menggambarnya TANPA GAYA karena aturannya cuma
       ada di berkas gaya Pesanan Toko. Halamannya tetap 200, jadi tidak ada
       satu pun tanda bahwa rupanya hilang. */
    .pt-daftar-kepala {
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px clamp(14px, 2vw, 18px); border-bottom: 1px solid #f1f5f9;
    }
    .pt-daftar-judul { flex: 1 1 auto; min-width: 0; }
    .pt-daftar-judul b { display: block; font-size: 1rem; font-weight: 800; color: #1c1f26; }
    .pt-daftar-judul span { display: block; font-size: .76rem; color: #94a3b8; }

    /* Lencana angka kecil — dipakai tombol "Saringan lainnya" di kedua layar. */
    .pt-tab-jumlah {
        min-width: 24px; height: 22px; padding: 0 7px; border-radius: 999px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #f1f5f9; color: #475569; font-size: .72rem; font-weight: 800;
    }
    .pt-tab-jumlah.is-isi { background: #7c3aed; color: #fff; min-width: 20px; height: 20px; }

    /* ===== Tab sebagai kartu status (dua kelompok) ===== */
    /* Lebar kedua kelompok bisa disetel per layar lewat --grup-kolom.
       Pesanan Toko punya 7 tab lawan 3, jadi bawaannya 7fr/3fr; layar yang
       kelompoknya sama besar menyetelnya jadi 1fr/1fr. Dikunci di sini, satu
       kelompok yang isinya tiga kartu akan melar jadi dua kali lebar isinya. */
    .pt-grup-deret {
        display: grid; gap: 12px;
        grid-template-columns: var(--grup-kolom, minmax(0, 7fr) minmax(0, 3fr));
    }
    /* SATU kelompok mengisi lebar penuh.

       Bawaannya dua kolom (7fr + 3fr) karena layar Pesanan Toko memang punya
       dua kelompok. Layar yang cuma punya satu — Pelamar — meninggalkan
       kolom kedua menganggur: petak putih selebar sepertiga kartu di sebelah
       kanan deret tabnya, tanpa galat apa pun.

       Dipasang di sini supaya layar berikutnya tidak perlu mengingat
       menimpa --grup-kolom sendiri. */
    .pt-grup-deret:has(> .pt-grup:only-child) { grid-template-columns: minmax(0, 1fr); }
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

        /* Tanda bahwa deretnya masih berlanjut ke kanan.

           Bilah gulungnya sengaja disembunyikan, dan tanpa penggantinya kartu
           terakhir terpotong rata di tepi layar — terbaca sebagai "memang
           cuma segitu", bukan sebagai "masih ada lagi, geser". Kabut tipis
           ini satu-satunya petunjuknya. Dipasang di sistem desain, bukan di
           satu layar: tiap layar yang memakai deret ini punya cacat yang sama.

           pointer-events: none — kalau tidak, kabutnya menelan sentuhan pada
           kartu di bawahnya dan tab paling kanan tidak bisa ditekan. */
        .pt-grup { position: relative; }
        .pt-grup::after {
            content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 26px;
            pointer-events: none; border-radius: 0 18px 18px 0;
            background: linear-gradient(to right, rgba(255, 255, 255, 0), #fff 78%);
        }
    }
</style>
@endonce
