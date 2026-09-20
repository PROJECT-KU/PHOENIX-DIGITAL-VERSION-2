{{-- Bahasa rupa dasbor panel (admin & karyawan).

     Disatukan dengan halaman publik (beranda, shop, bundling): kartu putih
     berbingkai tipis, sapuan warna samar di pojok, ubin ikon berwarna, dan
     judul Plus Jakarta Sans. Sebelumnya dasbor memakai kartu kaca ungu bawaan
     template — indah sendiri, tetapi terbaca sebagai aplikasi yang berbeda
     dari tokonya.

     Ditulis inline: public/build masuk .gitignore dan tidak ikut terdeploy,
     jadi CSS yang ditaruh di resources/css TIDAK PERNAH sampai ke server.

     @once: dipakai dua tampilan dasbor dan beberapa partial; tanpa itu blok
     gaya yang sama tercetak berulang di satu halaman. --}}
@once
@push('styles')
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
@endpush

<style>
    /* ===== Ikon huruf vs aturan ikon SVG bawaan template =========
       Template membawa aturan Bootstrap 5.3 ini:

           .bi { width: 1em; height: 1em; ... }

       Aturan itu ditujukan untuk ikon SVG (<svg class="bi">), bukan ikon HURUF
       (<i class="bi bi-…">). Pada ikon huruf, ia MENGUNCI kotak elemennya di
       1em milik font induk (16px) sementara glifnya sendiri digambar sebesar
       font-size ubinnya (24px). Glif lalu meluber ke kanan dan ke bawah dari
       kotaknya, dan karena yang dipusatkan flex adalah KOTAK itu — bukan
       tintanya — seluruh ikon di dasbor tampak turun ~3px dan bergeser ~5px ke
       kanan. Diukur piksel per piksel, bukan ditaksir.

       Kotaknya dikembalikan mengikuti isi, sehingga yang dipusatkan flex sama
       dengan yang dilihat mata. */
    .dsb i.bi,
    .bt-panel i.bi {
        width: auto; height: auto;
        /* Satu model untuk SEMUA ikon di dasbor, di mana pun ia dipakai:
           kotaknya sebesar glifnya, glifnya dipusatkan di dalam kotak itu. */
        display: inline-flex; align-items: center; justify-content: center;
        line-height: 1;
        /* Hanya berlaku saat ikon berdiri di tengah kalimat (konteks inline);
           pada anak wadah flex, vertical-align memang diabaikan.

           Template menyetel `.bi:before { vertical-align: sub }` — 'sub'
           menurunkan glif setinggi posisi subskrip, jadi ikon di tengah
           kalimat duduk ~2px di bawah garis alas dan terbaca melorot.
           -.125em adalah angka Bootstrap Icons sendiri: glif jatuh pas pada
           pita huruf, sejajar dengan teks di sebelahnya. */
        vertical-align: -.125em;
    }
    /* Glifnya dijadikan blok di dalam kotak ikon, sekaligus MEMBATALKAN
       vertical-align: sub milik template — kalau tidak, ikon di dalam ubin
       pun ikut terdorong turun. */
    .dsb i.bi::before,
    .bt-panel i.bi::before { display: block; line-height: 1; vertical-align: baseline; }

    /* ===== Kerangka halaman ===================================== */
    /* Kotak dihitung termasuk tepi & padding — TIDAK menumpang setelan
       Bootstrap milik kerangka. Kartu angka memakai height:100% di dalam
       kisi; dengan box-sizing bawaan (content-box), tinggi 100% itu dihitung
       DI LUAR padding, sehingga isinya meluber ~34px keluar kartunya. Ditulis
       di sini supaya gaya ini tetap benar di layar mana pun ia dipakai. */
    .dsb, .dsb *, .dsb *::before, .dsb *::after { box-sizing: border-box; }

    .dsb {
        --dsb-tepi: #e9edf3;
        --dsb-tinta: #1c1f26;
        --dsb-redup: #6b7280;
        --dsb-jingga: #f26522;
        padding: 0 clamp(4px, 1.4vw, 18px) 28px;
    }

    /* Dipakai saat bahasa rupa ini dipinjam di dalam wadah yang sudah punya
       tepinya sendiri (isi jendela): padding halaman tidak boleh ikut. */
    .dsb.is-datar { padding: 0; }

    .dsb-bagian { margin-bottom: clamp(20px, 3vw, 32px); }

    /* ===== Rak: satu kisi 12 kolom untuk seluruh dasbor ==========
       Semua yang tampil di halaman ini — sapaan, kepala bagian, kartu angka,
       grafik, daftar — duduk pada kisi yang SAMA. Sebelumnya tiap kelompok
       punya kisinya sendiri (dua kolom di sini, tiga di sana, Bootstrap row di
       tempat lain), sehingga tepi kartu dari kelompok berbeda tidak pernah
       segaris. Ketidaksegarisan itulah yang terbaca sebagai "kurang rapi",
       bukan warna atau bentuk kartunya. */
    .dsb-rak {
        display: grid; grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: clamp(12px, 1.2vw, 16px);
        align-items: stretch;
    }
    .dsb-rak > * { grid-column: span 12; min-width: 0; }
    /* Jarak kepala ke kartunya diatur oleh gap raknya sendiri; margin bawaan
       kepala akan menambahinya sehingga barisnya renggang sendirian. */
    .dsb-rak > .dsb-kepala { margin-bottom: 0; }
    .k-3 { grid-column: span 3; }
    .k-4 { grid-column: span 4; }
    .k-5 { grid-column: span 5; }
    .k-6 { grid-column: span 6; }
    .k-7 { grid-column: span 7; }
    .k-8 { grid-column: span 8; }

    /* Tablet & laptop sempit.
       Kartu ANGKA masih muat berdua (span 6), tetapi kartu yang berisi daftar,
       grafik, atau agenda tidak: pada lebar setengah layar tablet, nama
       pelanggan, waktu, nominal, dan status berdesakan sampai terpatah di
       tempat yang janggal. Yang lebar dari 5 kolom karena itu langsung
       melebar penuh. */
    @media (max-width: 1199.98px) {
        .k-3, .k-4 { grid-column: span 6; }
        .k-5, .k-6, .k-7, .k-8 { grid-column: span 12; }
        /* Kartu terakhir tidak ditinggalkan sendirian setengah lebar dengan
           lubang kosong di sebelahnya. */
        .dsb-rak > .k-3:last-child,
        .dsb-rak > .k-4:last-child { grid-column: span 12; }
    }
    @media (max-width: 767.98px) {
        .k-3, .k-4, .k-5, .k-6, .k-7, .k-8 { grid-column: span 12; }
    }

    /* Kepala bagian.
       Versi pertamanya hanya tumpukan teks rata kiri: label kecil, judul, lalu
       satu kalimat panjang berisi periode — terbaca sebagai paragraf, bukan
       sebagai kepala bagian, dan tidak ada apa pun yang menahan mata. Sekarang
       ia punya tiga bagian yang jelas: ubin ikon berwarna sebagai jangkar,
       judul, dan keterangan yang dipadatkan jadi chip. */
    /* Pita, BUKAN kartu. Kepala yang diberi bingkai & bayangan sendiri menjadi
       kotak di atas kotak: ia lalu bersaing dengan kartu angka di bawahnya,
       padahal tugasnya hanya memberi nama pada kelompok itu. Pita berwarna
       sangat tipis sudah cukup menambatkannya ke halaman — tanpa menambah satu
       kotak lagi untuk dibaca mata. */
    .dsb-kepala {
        display: flex; align-items: center; gap: 14px; margin-bottom: 14px;
        padding: 13px 16px; border-radius: 16px;
        /* Warnanya menipis ke kanan, tetapi TIDAK sampai habis: pada versi yang
           memudar jadi bening, tombol di ujung kanan duduk di luar pitanya dan
           terlihat seperti tercecer dari kepala bagian. */
        background: linear-gradient(100deg,
            color-mix(in srgb, var(--c, #94a3b8) 10%, #fff) 0%,
            color-mix(in srgb, var(--c, #94a3b8) 5%, #fff) 45%,
            color-mix(in srgb, var(--c, #94a3b8) 3%, #fff) 100%);
    }
    .dsb-kepala-teks { min-width: 0; flex: 1; }

    /* Ubin ikon kepala: warnanya mengikuti --c bagian itu, sehingga tiap
       bagian punya satu warna yang dipakai bersama kartu di bawahnya. */
    .dsb-kepala-ikon {
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        width: 48px; height: 48px; border-radius: 15px;
        background: linear-gradient(135deg,
            color-mix(in srgb, var(--c) 16%, #fff),
            color-mix(in srgb, var(--c) 7%, #fff));
        border: 1px solid color-mix(in srgb, var(--c) 24%, #fff);
        color: var(--c); font-size: 1.32rem;
    }
    .dsb-kepala-ikon i.bi { display: block; line-height: 1; }
    .dsb-kepala-ikon i.bi::before { display: block; line-height: 1; }

    /* Chip keterangan: periode dan catatan pendek. Satu kalimat panjang
       ("Periode berjalan 21 Agt – 20 Sep 2026 — dihitung dari tanggal 21
       sampai 20") memaksa mata membaca sampai habis untuk menemukan satu
       potong data; sebagai chip, periodenya terbaca sekali lihat. */
    .dsb-chip-deret { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 7px; }
    .dsb-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 999px;
        background: #fff;
        border: 1px solid color-mix(in srgb, var(--c) 20%, #fff);
        color: color-mix(in srgb, var(--c) 75%, #000);
        font-size: .74rem; font-weight: 700; white-space: nowrap;
    }
    .dsb-chip i.bi { font-size: .78rem; line-height: 1; }
    .dsb-chip i.bi::before { display: block; line-height: 1; }
    .dsb-chip.is-samar {
        background: rgba(255, 255, 255, .72); border-color: var(--dsb-tepi);
        color: #64748b; font-weight: 600; white-space: normal;
    }
    .dsb-kicker {
        display: inline-flex; align-items: center; gap: 7px; margin-bottom: 4px;
        color: var(--dsb-jingga); font-size: .68rem; font-weight: 700;
        letter-spacing: .16em; text-transform: uppercase;
    }
    .dsb-kicker i.bi { font-size: .72rem; line-height: 1; }
    .dsb-kicker i.bi::before { display: block; line-height: 1; }
    .dsb-judul {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: clamp(1.15rem, 2.2vw, 1.4rem);
        line-height: 1.2; letter-spacing: -.02em; margin: 0;
    }
    .dsb-sub { color: var(--dsb-redup); font-size: .85rem; margin: 4px 0 0; line-height: 1.5; }

    /* Tautan aksi. Mewarisi --c bagiannya, jadi tombol "Buka Cash Flow" satu
       keluarga dengan ubin ikon dan chip di sebelahnya — bukan tombol abu-abu
       yang seolah milik halaman lain. */
    .dsb-tautan {
        display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; flex-shrink: 0;
        color: color-mix(in srgb, var(--c, #475569) 78%, #000);
        font-size: .82rem; font-weight: 700; text-decoration: none;
        padding: 9px 14px; border-radius: 11px;
        border: 1px solid color-mix(in srgb, var(--c, #94a3b8) 26%, #fff);
        background: #fff;
        transition: color .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }
    .dsb-tautan i.bi { font-size: .85rem; line-height: 1; transition: transform .18s ease; }
    .dsb-tautan i.bi::before { display: block; line-height: 1; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tautan:hover {
            background: var(--c, #475569); color: #fff; border-color: transparent;
            box-shadow: 0 8px 18px color-mix(in srgb, var(--c, #475569) 28%, transparent);
        }
        .dsb-tautan:hover i.bi { transform: translateX(2px); }
    }

    /* ===== Kartu dasar =========================================== */
    /* Sengaja BUKAN .card: kelas itu ditimpa gaya kaca template dengan
       !important di templateindex, dan melawannya hanya menumpuk !important
       baru di kedua sisi. */
    .dsb-kartu {
        position: relative; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi); border-radius: 18px;
        /* Kolom flex: kartu yang duduk berdampingan sama tinggi, dan isinya
           boleh memanjang mengisi tinggi itu — bukan menumpuk di atas lalu
           meninggalkan petak kosong di bawah. */
        display: flex; flex-direction: column;
        transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    .dsb-kartu > .dsb-daftar,
    .dsb-kartu > .dsb-kartu-isi { flex: 1 1 auto; }
    .dsb-kartu-isi { padding: clamp(16px, 2.2vw, 22px); }
    /* Isi yang berdiri tegak: tombol/penutupnya menempel ke DASAR kartu, jadi
       kartu yang direntangkan setinggi tetangganya tidak menyisakan petak
       putih di bawah tombolnya. */
    .dsb-kartu-isi.is-tegak { display: flex; flex-direction: column; }
    .dsb-kartu-isi.is-tegak > :last-child { margin-top: auto; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-kartu:hover { border-color: #dfe5ee; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); }
    }

    /* ===== Sapuan hero ========================================== */
    .dsb-hero {
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #fff7ed 0%, #ffffff 46%, #f5f3ff 100%);
        border: 1px solid var(--dsb-tepi); border-radius: 20px;
        padding: clamp(18px, 2.6vw, 26px);
        margin-bottom: clamp(18px, 2.6vw, 26px);
        display: flex; align-items: center; justify-content: space-between;
        gap: 20px; flex-wrap: wrap;
    }
    /* Dua bulatan warna yang saling tindih di pojok — tanda pengenal yang sama
       dipakai kartu kategori & langkah "Cara Pesan" di beranda. */
    .dsb-hero::before,
    .dsb-hero::after {
        content: ""; position: absolute; border-radius: 50%; pointer-events: none;
    }
    .dsb-hero::before {
        top: -70px; right: -40px; width: 190px; height: 190px;
        background: rgba(242, 101, 34, .10);
    }
    .dsb-hero::after {
        bottom: -110px; right: -70px; width: 190px; height: 190px;
        background: rgba(124, 58, 237, .08);
    }
    .dsb-hero > * { position: relative; z-index: 1; }

    /* flex-basis, bukan lebar otomatis: tanpa itu baris keterangan yang panjang
       mengklaim satu baris penuh dan tombol aksi selalu turun ke bawah, bahkan
       di layar lebar yang sebenarnya muat. */
    .dsb-hero-teks { min-width: 0; flex: 1 1 360px; }
    .dsb-hero-ket span.d-block + span.d-block { margin-top: 2px; }
    .dsb-salam {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: clamp(1.4rem, 3.4vw, 2rem);
        line-height: 1.15; letter-spacing: -.03em; margin: 0 0 6px;
    }
    .dsb-hero-ket { color: var(--dsb-redup); font-size: .9rem; margin: 0; line-height: 1.55; }

    .dsb-segar {
        display: inline-flex; align-items: center; gap: 5px; margin-left: 6px;
        padding: 2px 9px; border-radius: 999px;
        background: rgba(255, 255, 255, .7); border: 1px solid var(--dsb-tepi);
        color: #94a3b8; font-size: .72rem; font-weight: 600; white-space: nowrap;
    }

    .dsb-hero-aksi { display: flex; align-items: center; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }

    /* Kartu identitas: foto + nama + titik daring. */
    .dsb-aku {
        display: flex; align-items: center; gap: 12px;
        background: #fff; border: 1px solid var(--dsb-tepi); border-radius: 14px;
        padding: 8px 16px 8px 8px;
    }
    .dsb-aku-foto { position: relative; flex-shrink: 0; }
    /* Cadangan saat foto profil belum diunggah: huruf awal nama di ubin warna.
       Sebelumnya gambarnya disembunyikan begitu saja, menyisakan lubang kosong
       dengan titik hijau menggantung sendirian di dalam kartu. */
    .dsb-aku-inisial {
        display: inline-flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; border-radius: 12px;
        background: #ede9fe; color: #6d28d9;
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 800; font-size: 1.05rem; line-height: 1; text-transform: uppercase;
    }
    .dsb-aku-foto img {
        width: 44px; height: 44px; border-radius: 12px; object-fit: cover; display: block;
    }
    .dsb-titik {
        position: absolute; right: -3px; bottom: -3px;
        width: 13px; height: 13px; border-radius: 50%; border: 2.5px solid #fff;
    }
    .dsb-titik.is-daring { background: #16a34a; }
    /* Titik di foto memakai warna yang SAMA dengan lencananya — satu keadaan
       tidak boleh muncul dalam dua warna di baris yang sama. */
    .dsb-titik.is-luring { background: #ef4444; }
    .dsb-aku-nama {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
        color: var(--dsb-tinta); font-size: .92rem; line-height: 1.2; margin: 0;
    }
    .dsb-aku-peran {
        display: block; color: var(--dsb-redup); font-size: .74rem; margin-top: 3px;
    }

    /* Tombol. Ditulis penuh (bukan .btn bawaan) supaya tinggi, radius, dan
       ikonnya tidak bergantung pada gaya global template. */
    /* Tombol dasar SENGAJA punya rupa sendiri: tanpa background, tombol
       bawaan peramban/template tampil abu-abu pudar — persis yang tidak
       diinginkan di bilah kepala. Ikonnya berwarna (bisa diatur per tombol
       lewat --ikon) supaya barisnya hidup, bukan sederet kotak kelabu. */
    .dsb-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        height: 44px; padding: 0 18px; border: 1px solid var(--dsb-tepi); border-radius: 12px;
        background: #fff; color: #334155;
        font-size: .86rem; font-weight: 700; line-height: 1; text-decoration: none;
        cursor: pointer; white-space: nowrap;
        box-shadow: 0 6px 16px -12px rgba(15, 23, 42, .55);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, border-color .18s ease;
    }
    .dsb-tombol i.bi { font-size: .95rem; line-height: 1; color: var(--ikon, #7c3aed); }
    .dsb-tombol i.bi::before { display: block; line-height: 1; }
    /* Ragam berlatar penuh memakai warna teksnya sendiri untuk ikon. */
    .dsb-tombol.is-utama i.bi, .dsb-tombol.is-bahaya i.bi { color: inherit; }
    .dsb-tombol.is-utama { background: #7c3aed; color: #fff; box-shadow: 0 8px 18px rgba(124, 58, 237, .26); }
    .dsb-tombol.is-bahaya { background: #fff; color: #dc2626; border-color: #fecaca; }
    .dsb-tombol.is-lembut { background: #fff; color: #475569; border-color: var(--dsb-tepi); }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tombol:hover { transform: translateY(-2px); }
        .dsb-tombol.is-bahaya:hover { background: #dc2626; color: #fff; border-color: transparent; }
        .dsb-tombol.is-lembut:hover { border-color: #cbd5e1; color: #1c1f26; }
        .dsb-tombol:hover { border-color: #cbd5e1; color: #1c1f26; }
    }

    /* ===== Kartu angka ========================================== */
    .dsb-deret { display: grid; gap: 14px; }
    .dsb-deret.is-dua { grid-template-columns: repeat(2, 1fr); }
    .dsb-deret.is-tiga { grid-template-columns: repeat(3, 1fr); }

    /* Ikon di KIRI, angka di kanannya — bukan ikon di atas lalu teks di bawah.
       Kartu di dasbor selebar setengah layar; dengan susunan bertumpuk, seluruh
       isinya berkumpul di tepi kiri dan menyisakan petak putih selebar telapak
       tangan di kanan tiap kartu. */
    .dsb-stat {
        position: relative; overflow: hidden; height: 100%;
        background: #fff; border: 1px solid var(--dsb-tepi); border-radius: 16px;
        padding: 20px;
        display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: 16px;
        grid-template-rows: auto auto 1fr; align-content: start;
        transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    /* Ikon menempati kolom pertama sepanjang kartu; sisanya mengisi kolom kedua. */
    .dsb-stat > .dsb-ikon { grid-row: 1 / span 20; margin-bottom: 0; }
    .dsb-stat > :not(.dsb-ikon) { grid-column: 2; }
    /* Sapuan warna pojok: penanda bahwa kartu ini punya "warna urusan"
       sendiri — hijau untuk uang masuk, merah untuk keluar, dan seterusnya. */
    .dsb-stat::before {
        content: ""; position: absolute; top: -36px; right: -36px;
        width: 104px; height: 104px; border-radius: 50%;
        background: color-mix(in srgb, var(--c) 12%, transparent);
        transition: transform .3s ease;
    }
    @media (hover: hover) and (pointer: fine) {
        .dsb-stat:hover {
            border-color: color-mix(in srgb, var(--c) 34%, #fff);
            transform: translateY(-3px);
            box-shadow: 0 12px 26px color-mix(in srgb, var(--c) 16%, transparent);
        }
        .dsb-stat:hover::before { transform: scale(1.3); }
        .dsb-stat:hover .dsb-ikon { background: var(--c); color: #fff; border-color: transparent; }
    }
    .dsb-stat > * { position: relative; z-index: 1; }

    /* Ubin ikon: isinya BENAR-BENAR di tengah (flex + line-height 1 pada
       pseudo-element Bootstrap Icons, yang bawaannya menyisakan ruang bawah). */
    .dsb-ikon {
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        width: 46px; height: 46px; border-radius: 14px; margin-bottom: 14px;
        background: color-mix(in srgb, var(--c) 12%, #fff);
        border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
        color: var(--c); font-size: 1.3rem;
        transition: background .22s ease, color .22s ease, border-color .22s ease;
    }
    .dsb-ikon i.bi { display: block; line-height: 1; }
    .dsb-ikon i.bi::before { display: block; line-height: 1; }
    .dsb-ikon.is-kecil { width: 38px; height: 38px; border-radius: 11px; font-size: .98rem; margin-bottom: 0; }

    .dsb-stat-label {
        color: var(--dsb-redup); font-size: .78rem; font-weight: 700;
        letter-spacing: .04em; text-transform: uppercase; margin: 0 0 6px;
    }
    .dsb-stat-nilai {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: clamp(1.3rem, 2.6vw, 1.65rem);
        line-height: 1.15; letter-spacing: -.025em; margin: 0;
        font-variant-numeric: tabular-nums;
    }
    .dsb-stat-nilai.is-hijau { color: #15803d; }
    .dsb-stat-nilai.is-merah { color: #dc2626; }
    /* Keterangan didorong ke DASAR kartu (margin-top: auto). Dalam satu baris,
       kartu yang isinya lebih pendek tetap menaruh keterangannya di garis yang
       sama dengan tetangganya — mata membaca deretan itu sebagai satu baris,
       bukan sebagai kartu-kartu yang kebetulan berjajar. */
    .dsb-stat-ket {
        display: flex; align-items: center; gap: 6px;
        color: var(--dsb-redup); font-size: .76rem; line-height: 1.5;
        margin: 10px 0 0; align-self: start;
    }
    .dsb-stat > .dsb-stat-ket:last-child { margin-top: auto; padding-top: 10px; }
    .dsb-stat-ket i.bi { flex-shrink: 0; }
    .dsb-stat.is-utama { padding: 22px; column-gap: 18px; }
    /* Satuan di belakang angka besar ("182 pesanan"): dibuat kecil dan redup
       supaya yang dibaca lebih dulu tetap angkanya, dan kalimatnya tidak
       terbaca sebagai judul kartu. */
    .dsb-stat-satuan {
        margin-left: 6px; font-size: .85rem; font-weight: 700; color: var(--dsb-redup);
        letter-spacing: 0;
    }

    .dsb-stat.is-utama .dsb-stat-nilai { font-size: clamp(1.5rem, 3.2vw, 2rem); }
    .dsb-stat.is-utama > .dsb-ikon { width: 54px; height: 54px; border-radius: 16px; font-size: 1.5rem; }

    /* ===== Kartu "yang menunggu dikerjakan" =====================
       Beda dari kartu angka biasa: isinya panjangnya tidak seragam (ada yang
       punya pil, ada yang tidak; ada keterangan satu baris, ada dua). Karena
       itu teksnya dibungkus SATU kolom flex, dan keterangannya didorong ke
       dasar kolom — sehingga baris bawah semua kartu berhenti di satu garis
       berapa pun isinya. */
    .dsb-tugas {
        position: relative; overflow: hidden; height: 100%;
        display: flex; align-items: flex-start; gap: 15px;
        background: #fff; border: 1px solid var(--dsb-tepi); border-radius: 16px;
        padding: 20px;
        transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    .dsb-tugas::before {
        content: ""; position: absolute; top: -36px; right: -36px;
        width: 104px; height: 104px; border-radius: 50%;
        background: color-mix(in srgb, var(--c) 12%, transparent);
        transition: transform .3s ease;
    }
    .dsb-tugas > * { position: relative; z-index: 2; }
    .dsb-tugas > .dsb-ikon { margin-bottom: 0; flex-shrink: 0; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tugas:hover {
            border-color: color-mix(in srgb, var(--c) 34%, #fff);
            transform: translateY(-3px);
            box-shadow: 0 12px 26px color-mix(in srgb, var(--c) 16%, transparent);
        }
        .dsb-tugas:hover::before { transform: scale(1.3); }
        .dsb-tugas:hover .dsb-ikon { background: var(--c); color: #fff; border-color: transparent; }
    }

    .dsb-tugas-isi { display: flex; flex-direction: column; align-items: flex-start; min-width: 0; flex: 1; align-self: stretch; }
    .dsb-tugas-angka {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: 1.6rem; line-height: 1.1;
        letter-spacing: -.025em; font-variant-numeric: tabular-nums;
    }
    /* Satuan menempel pada angkanya dengan ukuran & bobot yang jelas berbeda:
       "38 akan habis" harus terbaca sebagai satu angka beserta satuannya,
       bukan sebagai kalimat. */
    .dsb-tugas-satuan {
        margin-left: 6px; font-size: .85rem; font-weight: 700; color: var(--dsb-redup);
        letter-spacing: 0;
    }
    .dsb-tugas-isi .dsb-pil { margin-top: 10px; }
    /* Keterangan selalu di DASAR kolom. */
    .dsb-tugas-isi .dsb-stat-ket { margin-top: auto; padding-top: 12px; align-self: stretch; }
    .dsb-tugas-isi .dsb-stat-ket > span { overflow-wrap: anywhere; }

    @media (max-width: 1199.98px) {
        /* Kartu tugas berpasangan dua-dua di tablet — isinya pendek, jadi
           setengah lebar masih cukup (tidak seperti kartu berisi daftar). */
        .dsb-tugas { grid-column: span 6; }
        .dsb-tugas.is-penuh-sedang { grid-column: span 12; }
    }
    @media (max-width: 767.98px) {
        .dsb-tugas, .dsb-tugas.is-penuh-sedang { grid-column: span 12; }
    }
    @media (max-width: 575.98px) {
        .dsb-tugas { padding: 17px; gap: 13px; }
        .dsb-tugas-angka { font-size: 1.45rem; }
    }

    /* ===== Aksi cepat =========================================== */
    .dsb-aksi { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
    .dsb-aksi-item {
        display: flex; align-items: center; gap: 13px; min-width: 0;
        padding: 13px 15px; border-radius: 14px; text-decoration: none;
        background: color-mix(in srgb, var(--c) 5%, #fff);
        border: 1px solid color-mix(in srgb, var(--c) 18%, #fff);
        transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
    }
    .dsb-aksi-item .dsb-ikon { margin-bottom: 0; width: 42px; height: 42px; font-size: 1.15rem; }
    .dsb-aksi-teks { min-width: 0; flex: 1; }
    .dsb-aksi-nama {
        display: block; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 700; font-size: .92rem; color: var(--dsb-tinta); line-height: 1.25;
    }
    .dsb-aksi-ket { display: block; color: var(--dsb-redup); font-size: .78rem; margin-top: 2px; }
    .dsb-aksi-panah { color: color-mix(in srgb, var(--c) 70%, #000); font-size: .95rem; transition: transform .18s ease; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-aksi-item:hover {
            border-color: color-mix(in srgb, var(--c) 38%, #fff);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px color-mix(in srgb, var(--c) 16%, transparent);
        }
        .dsb-aksi-item:hover .dsb-ikon { background: var(--c); color: #fff; border-color: transparent; }
        .dsb-aksi-item:hover .dsb-aksi-panah { transform: translateX(3px); }
    }

    /* Tautan yang menutupi seluruh kartu.
       Dipakai supaya kartu bisa diklik tanpa membuat KARTUNYA sebuah <a> —
       markupnya jadi satu jalur, dan isinya (pil, lencana) tetap boleh
       mengandung tautannya sendiri karena berada di lapisan atasnya. */
    .dsb-tutup-kartu { position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
    .dsb-stat:has(.dsb-tutup-kartu) { cursor: pointer; }
    .dsb-stat > :not(.dsb-tutup-kartu) { position: relative; z-index: 2; }

    /* Pil kecil di dalam kartu (mis. "Hari ini: Rp 2.005"). */
    .dsb-pil {
        display: inline-flex; justify-self: start; align-items: center; gap: 6px; margin-top: 10px;
        padding: 5px 10px; border-radius: 999px; font-size: .74rem; font-weight: 600;
        background: color-mix(in srgb, var(--c) 10%, #fff);
        border: 1px solid color-mix(in srgb, var(--c) 24%, #fff);
        color: color-mix(in srgb, var(--c) 78%, #000);
    }

    /* ===== Kepala di dalam kartu ================================ */
    .dsb-kartu-kepala {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 16px clamp(16px, 2.2vw, 20px); border-bottom: 1px solid #f1f5f9;
        min-height: 68px; flex: 0 0 auto;
    }
    .dsb-kartu-kepala-kiri { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .dsb-kartu-judul {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
        color: var(--dsb-tinta); font-size: 1rem; line-height: 1.25; margin: 0;
    }
    .dsb-kartu-sub { display: block; color: var(--dsb-redup); font-size: .78rem; margin-top: 2px; }

    /* ===== Daftar baris (pengganti tabel) ======================= */
    /* Tabel di panel ini selalu berakhir dengan geseran mendatar di HP, dan
       kolom yang tergeser keluar layar sama saja dengan tidak ada. Baris
       daftar menampung isi yang sama tanpa pernah melebihi lebar layar. */
    .dsb-daftar { display: flex; flex-direction: column; }
    .dsb-baris {
        display: flex; align-items: center; gap: 12px;
        padding: 13px clamp(16px, 2.2vw, 22px);
        border-bottom: 1px solid #f5f7fa; text-decoration: none; color: inherit;
        transition: background .18s ease;
    }
    .dsb-baris:last-child { border-bottom: 0; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-baris:hover { background: #fbfcfe; }
    }
    .dsb-avatar {
        display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
        width: 38px; height: 38px; border-radius: 12px;
        background: color-mix(in srgb, var(--c) 12%, #fff);
        color: color-mix(in srgb, var(--c) 82%, #000);
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 800; font-size: .95rem; line-height: 1; text-transform: uppercase;
    }
    .dsb-baris-isi { min-width: 0; flex: 1; }
    .dsb-baris-judul {
        display: block; font-weight: 700; color: var(--dsb-tinta); font-size: .88rem;
        line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    /* Di bawah 1200px kartunya menyempit dan nomor pesanan mulai terpotong
       jadi "INV-20260915-00…" — bentuk yang tidak bisa dicocokkan maupun
       dicari. Judul baris adalah IDENTITAS, jadi lebih baik turun baris
       daripada hilang ujungnya. Diukur: pada 768px empat nomor pesanan
       terpotong sebelum aturan ini. */
    @media (max-width: 1199.98px) {
        .dsb-baris-judul { white-space: normal; overflow-wrap: anywhere; }
    }
    .dsb-baris-meta {
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
        color: var(--dsb-redup); font-size: .76rem; margin-top: 3px;
    }
    .dsb-baris-kanan {
        display: flex; flex-direction: column; align-items: flex-end; gap: 5px;
        flex-shrink: 0; text-align: right;
    }
    .dsb-baris-nilai {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: .88rem; line-height: 1;
        font-variant-numeric: tabular-nums; white-space: nowrap;
    }

    .dsb-lencana {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 9px; border-radius: 999px;
        font-size: .68rem; font-weight: 700; letter-spacing: .03em; white-space: nowrap;
    }
    /* Titik status di dalam lencana — dibuat dari CSS, bukan ikon huruf: kotak
       glif ikon selalu lebih lebar dari titiknya dan membuat jaraknya timpang. */
    .dsb-lencana .dsb-bulat { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
    .dsb-lencana.is-hijau { background: #dcfce7; color: #15803d; }
    .dsb-lencana.is-kuning { background: #fef9c3; color: #a16207; }
    .dsb-lencana.is-biru { background: #dbeafe; color: #1d4ed8; }
    .dsb-lencana.is-ungu { background: #ede9fe; color: #6d28d9; }
    .dsb-lencana.is-merah { background: #fee2e2; color: #b91c1c; }
    .dsb-lencana.is-abu { background: #f1f5f9; color: #475569; }

    /* Dua status yang dulu ikut memakai is-abu: LURING (lawan dari ONLINE) dan
       TAMU (pelanggan yang belum jadi member). Abu-abunya bukan soal kontras
       — teksnya cukup gelap — melainkan soal ARTI: di dasbor ini abu-abu
       dipakai untuk ketiadaan data (pesanan 'draft', target 'Belum Ada'),
       sedangkan luring dan non-member adalah keadaan yang sah dan pasti.
       Karena warnanya sama, keduanya terbaca sebagai baris yang datanya belum
       lengkap, bukan sebagai status.

       Diberi kelas SENDIRI, bukan menumpang is-merah/is-kuning: kedua kelas
       itu sudah dipakai status pesanan (cancelled & pending) di kartu
       sebelahnya, dan kelas yang sama dengan dua arti akan saling menular
       begitu salah satunya disetel ulang. */
    /* Nila untuk "belum dikerjakan": keadaan yang PASTI, jadi tidak boleh
       abu-abu — abu-abu di sini berarti datanya yang belum ada. Dibedakan dari
       is-biru yang sudah dipakai label kategori di baris yang sama. */
    .dsb-lencana.is-nila { background: #e0e7ff; color: #4338ca; }
    .dsb-lencana.is-luring { background: #fee2e2; color: #b91c1c; }
    .dsb-lencana.is-tamu { background: #ffedd5; color: #c2410c; }

    /* Garis kemajuan (mis. berapa bagian pinjaman yang sudah dikembalikan).
       Satu garis lebih cepat dibaca daripada dua nominal yang harus
       dibandingkan sendiri di kepala. */
    .dsb-kemajuan {
        display: block; height: 6px; border-radius: 999px; overflow: hidden;
        background: #eef2f7; margin-top: 12px;
    }
    .dsb-kemajuan > span { display: block; height: 100%; border-radius: 999px; background: var(--c); }

    /* Deret data "label — nilai" (Info Saya). Dibuat kisi dua kolom, bukan
       flex space-between: pada flex, nilai yang panjang (alamat surel) mendesak
       labelnya sampai pecah baris, dan tiap baris jadi punya titik mula yang
       berbeda. */
    .dsb-data { display: flex; flex-direction: column; }
    .dsb-data-baris {
        display: grid; grid-template-columns: minmax(0, auto) minmax(0, 1fr);
        gap: 12px; align-items: baseline;
        padding: 9px 0; border-bottom: 1px solid #f5f7fa;
    }
    .dsb-data-baris:last-child { border-bottom: 0; }
    .dsb-data-label {
        display: inline-flex; align-items: center; gap: 7px;
        color: var(--dsb-redup); font-size: .82rem; white-space: nowrap;
    }
    .dsb-data-nilai {
        font-weight: 700; color: var(--dsb-tinta); font-size: .84rem;
        text-align: right; overflow-wrap: anywhere;
    }

    /* Penggeser periode. Dua tombol panah, bukan kotak pilih berisi daftar
       bulan: yang hampir selalu dicari adalah "periode sebelum ini". */
    .dsb-geser { display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .dsb-geser-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px;
        background: #fff; border: 1px solid var(--dsb-tepi); color: #475569;
        font-size: .8rem; font-weight: 700; cursor: pointer;
        transition: border-color .18s ease, color .18s ease, background .18s ease;
    }
    .dsb-geser-btn.is-kini { color: var(--c, #475569); }
    .dsb-geser-btn:disabled { opacity: .4; cursor: not-allowed; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-geser-btn:not(:disabled):hover { border-color: color-mix(in srgb, var(--c, #94a3b8) 40%, #fff); color: var(--c, #1c1f26); }
    }

    /* Penanda "sedang menggambar". Ditimpa grafik begitu Apex selesai; tanpa
       ini kartunya kosong beberapa ratus milidetik dan terbaca seperti tidak
       ada datanya. */
    .dsb-memuat {
        position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
        gap: 9px; color: #94a3b8; font-size: .82rem; pointer-events: none;
    }
    .dsb-putar {
        width: 15px; height: 15px; border-radius: 50%;
        border: 2px solid #e2e8f0; border-top-color: #94a3b8;
        animation: dsbPutar .8s linear infinite;
    }
    @keyframes dsbPutar { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .dsb-putar { animation: none; } }
    /* Begitu Apex menaruh grafiknya, penandanya disembunyikan. */
    .dsb-grafik:has(.apexcharts-canvas) .dsb-memuat { display: none; }
    .dsb-grafik { position: relative; }

    /* Wadah grafik. Tingginya dipatok supaya kartu grafik dan kartu di
       sebelahnya berakhir di garis yang sama — grafik Apex menghitung
       tingginya sendiri, dan tanpa patokan ini kedua kartu selalu beda tinggi
       sampai grafiknya selesai digambar. */
    .dsb-grafik { min-height: 320px; }
    .dsb-grafik.is-donat { min-height: 300px; display: flex; align-items: center; justify-content: center; }
    .dsb-grafik > div { width: 100%; }

    /* ===== Keadaan kosong ======================================= */
    .dsb-kosong { text-align: center; padding: 34px 18px; }
    .dsb-kosong-ikon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 54px; height: 54px; border-radius: 16px; margin-bottom: 12px;
        background: #f8fafc; border: 1px solid var(--dsb-tepi);
        color: #94a3b8; font-size: 1.35rem;
    }
    .dsb-kosong-ikon i.bi { display: block; line-height: 1; }
    .dsb-kosong-judul { font-weight: 700; color: #334155; font-size: .92rem; margin: 0 0 4px; }
    .dsb-kosong-ket { color: var(--dsb-redup); font-size: .82rem; margin: 0; }

    /* ===== Layar sedang & kecil ================================= */
    @media (max-width: 1199.98px) {
        .dsb-deret.is-tiga { grid-template-columns: repeat(2, 1fr); }
        /* Kartu ketiga mengisi satu baris penuh, bukan menyisakan lubang. */
        .dsb-deret.is-tiga > :last-child:nth-child(odd) { grid-column: 1 / -1; }
    }
    /* Kartu berisi DAFTAR butuh lebar lebih dari kartu angka: di tablet, dua
       daftar berdampingan memeras nama, waktu, nominal, dan status ke kolom
       selebar 380px sampai keterangannya terpatah di tempat yang janggal. */
    @media (max-width: 991.98px) {
        .dsb-deret.is-daftar { grid-template-columns: 1fr; }
    }
    @media (max-width: 767.98px) {
        .dsb-deret.is-dua,
        .dsb-deret.is-tiga { grid-template-columns: 1fr; }
        .dsb-deret.is-tiga > :last-child:nth-child(odd) { grid-column: auto; }

        /* Arah flex berubah jadi kolom di sini, dan pada kolom flex-basis
           mengatur TINGGI — basis 360px milik blok sapaan berubah jadi rongga
           kosong setinggi sepertiga layar di antara sapaan dan tombolnya. */
        .dsb-hero { flex-direction: column; align-items: stretch; text-align: left; }
        .dsb-hero-teks { flex: 0 0 auto; }
        .dsb-hero-aksi { width: 100%; }
        .dsb-aku { flex: 1 1 100%; }
        /* Dua tombol berbagi rata satu baris — sasaran sentuh tetap lebar. */
        .dsb-hero-aksi .dsb-tombol { flex: 1 1 0; }

        /* Ubin ikon naik ke atas: pada layar sempit keterangannya jadi dua
           baris chip, dan ikon yang tetap di tengah terbaca seolah menempel
           pada chip, bukan pada judulnya. */
        .dsb-kepala { flex-wrap: wrap; gap: 12px; align-items: flex-start; }
        .dsb-kepala-ikon { width: 44px; height: 44px; border-radius: 13px; font-size: 1.2rem; }
        /* Tombolnya turun ke baris sendiri selebar penuh: sasaran sentuh yang
           lebar, dan judulnya tidak perlu berbagi baris dengan apa pun. */
        .dsb-kepala > .dsb-tautan { flex: 1 0 100%; justify-content: center; }
    }
    @media (max-width: 575.98px) {
        .dsb { padding-bottom: 20px; }
        .dsb-stat { padding: 17px; }
        .dsb-ikon { width: 42px; height: 42px; border-radius: 12px; font-size: 1.15rem; margin-bottom: 12px; }

        .dsb-stat { column-gap: 14px; }
        .dsb-stat .dsb-stat-label { margin-bottom: 3px; }
        .dsb-stat:not(.is-utama) .dsb-stat-nilai { font-size: 1.25rem; }
        .dsb-stat:not(.is-utama)::before { width: 76px; height: 76px; top: -30px; right: -30px; }

        /* Nama & waktu turun jadi dua baris; titik pemisahnya tidak diperlukan
           lagi dan justru menggantung di ujung baris pertama. */
        .dsb-baris-meta { flex-direction: column; align-items: flex-start; gap: 1px; }
        .dsb-pisah { display: none; }
        /* Nomor pesanan adalah identitas — lebih baik turun baris daripada
           terpotong jadi "INV-20260915-00…" yang tidak bisa dicocokkan. */
        .dsb-baris-judul { white-space: normal; overflow-wrap: anywhere; }
        .dsb-lencana { font-size: .62rem; padding: 3px 8px; }

        /* Chip keterangan berubah jadi teks polos: di layar sempit kalimatnya
           pecah dua baris, dan pil berisi dua baris terbaca seperti tombol
           yang gagal muat — bukan sebagai catatan kecil. */
        .dsb-chip.is-samar {
            background: none; border: 0; padding: 0; font-size: .78rem;
        }
        .dsb-baris { gap: 10px; padding-inline: 16px; }
        .dsb-avatar { width: 34px; height: 34px; border-radius: 10px; font-size: .85rem; }
        .dsb-kartu-kepala { padding-inline: 16px; }
        /* Kendali di sisi kanan kepala kartu (saringan, tautan) turun ke baris
           sendiri. Tanpa ini judulnya terhimpit jadi satu kata per baris —
           "Saringan / & Cara / Pandang" — dan kepala kartunya setinggi empat
           baris hanya untuk memuat tiga kata. */
        .dsb-kartu-kepala { flex-wrap: wrap; }
        .dsb-kartu-kepala > .dsb-kartu-kepala-kiri { flex: 1 1 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dsb-stat, .dsb-stat::before, .dsb-ikon, .dsb-tombol, .dsb-tautan, .dsb-tautan i.bi { transition: none; }
    }

    /* ===== Tombol muat ulang di kepala halaman =====
       Rupanya sama dengan penanda "Data per 14:20" di sebelahnya supaya
       keduanya terbaca sepasang: yang satu mengatakan angkanya sejak kapan,
       yang satu menyegarkannya. */
    .dsb-segar.is-tombol {
        cursor: pointer; color: #64748b;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .dsb-segar.is-tombol:hover { background: #fff; color: var(--dsb-tinta); border-color: #cbd5e1; }
    .dsb-segar.is-tombol:disabled { cursor: progress; opacity: .7; }

    /* Isi tombol memakai kelas TUNGGAL, bukan `.dsb-segar.is-tombol > span`.
       Livewire menyembunyikan elemen wire:loading lewat aturan berbobot dua
       pemilih atribut; `.dsb-segar.is-tombol > span` berbobot dua kelas + satu
       elemen — lebih tinggi — sehingga ia MENGALAHKAN penyembunyinya dan
       penanda "Memuat…" ikut tampil terus sejak halaman dibuka.
       Satu kelas (0,1,0) kalah dari aturan Livewire (0,2,0), jadi
       penyembunyiannya kembali bekerja tanpa kehilangan tata letaknya. */
    .dsb-segar-isi { display: inline-flex; align-items: center; gap: 5px; }

    /* Sasaran sentuh di layar sempit & perangkat sentuh.

       Diukur: "Muat ulang" setinggi 19px dan tombol "Hubungi" 25px — keduanya
       jauh di bawah ukuran yang bisa ditekan jempol dengan yakin (~36px).
       Hurufnya sengaja tidak ikut dibesarkan; yang ditambah kotak sentuhnya.

       Dua syarat sekaligus: lebar layar (bisa diuji, dan menangkap ponsel &
       tablet) ATAU pointer kasar (menangkap layar sentuh besar). */
    @media (max-width: 991.98px), (pointer: coarse) {
        .dsb-segar.is-tombol { min-height: 36px; padding-inline: 12px; }
        .dsb-baris-aksi { min-height: 36px; padding-inline: 13px; }
    }
    .dsb-putar.is-kecil { width: 11px; height: 11px; border-width: 1.5px; }

    /* ===== Keadaan memuat saat periode digeser =====
       Tanpa ini, menekan panah periode tidak memberi tanda apa pun: halaman
       diam sesaat lalu angkanya sudah berganti, dan pada sambungan lambat
       admin menekan panahnya dua kali. */
    .dsb-sedang-muat { opacity: .45; pointer-events: none; transition: opacity .12s ease; }
    .dsb-chip.is-memuat { border-color: #cbd5e1; color: #475569; }

    /* ===== Kartu yang TIDAK ikut pemilih periode =====
       "Pendapatan Hari Ini" duduk satu bagian dengan kartu-kartu periode.
       Saat periode digeser ke belakang, empat kartu berubah dan kartu ini
       tetap hari ini — tanpa penanda, angkanya terbaca sebagai angka periode
       lampau yang keliru. */
    .dsb-tanda-kini {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 2px 8px; border-radius: 999px;
        background: #eef2ff; color: #4338ca;
        font-size: .68rem; font-weight: 700; white-space: nowrap;
    }

    /* ===== Daftar dengan tombol aksi di kanan (mis. hubungi WhatsApp) ===== */
    .dsb-baris-aksi {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 11px; border-radius: 10px;
        background: #dcfce7; color: #15803d;
        font-size: .74rem; font-weight: 700; text-decoration: none; white-space: nowrap;
        transition: background .15s ease;
    }
    .dsb-baris-aksi:hover { background: #bbf7d0; color: #166534; }
    .dsb-baris-aksi.is-mati { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
    /* Kolom kanan biasanya menumpuk (nilai di atas, keterangan di bawah).
       Pada baris yang berisi lencana + tombol, menumpuk membuat barisnya dua
       kali lebih tinggi tanpa alasan — keduanya pendek dan muat berdampingan. */
    .dsb-baris-kanan.is-mendatar { flex-direction: row; align-items: center; gap: 9px; }
    @media (max-width: 575.98px) {
        .dsb-baris-kanan.is-mendatar { flex-direction: column; align-items: flex-end; gap: 6px; }
    }

    /* ===== Tombol tambahan untuk jendela =====
       is-hijau/is-kuning dipakai tombol keputusan di kaki jendela detail,
       tempat warna tombol adalah ARTI (selesaikan / terlambat), bukan hiasan. */
    .dsb-tombol.is-hijau { background: #16a34a; color: #fff; box-shadow: 0 8px 18px rgba(22, 163, 74, .26); }
    .dsb-tombol.is-kuning { background: #d97706; color: #fff; box-shadow: 0 8px 18px rgba(217, 119, 6, .24); }
    .dsb-tombol.is-biru { background: #fff; color: #0369a1; border-color: #bae6fd; }
    .dsb-tombol:disabled { opacity: .55; cursor: not-allowed; box-shadow: none; }
    /* Tombol mungil untuk di dalam kartu kecil (kartu papan scrum), tempat
       tombol setinggi 44px akan setinggi separuh kartunya sendiri. */
    .dsb-tombol.is-mungil { height: 32px; padding: 0 12px; font-size: .78rem; border-radius: 10px; }
    .dsb-tombol.is-mungil i.bi { font-size: .85rem; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tombol.is-biru:hover { background: #0284c7; color: #fff; border-color: transparent; }
        .dsb-tombol:disabled:hover { transform: none; }
    }
    @media (max-width: 575.98px) {
        /* Tombol keputusan melebar penuh: berdampingan di 390px masing-masing
           hanya selebar ~150px dan teksnya terpotong. */
        .dsb-tombol.is-penuh-sempit { width: 100%; }
    }

    /* ===== Medan isian ===============================================
       Sampai sekarang sistem desain ini hanya punya cara MENAMPILKAN, belum
       cara MEMINTA. Akibatnya tiap layar yang butuh formulir jatuh kembali ke
       kotak isian bawaan Bootstrap — dan formulirnya lalu tidak mirip apa pun
       di sekitarnya. */
    .dsb-medan { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
    .dsb-label {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--dsb-tinta); font-size: .8rem; font-weight: 700;
    }
    .dsb-label .dsb-wajib { color: #dc2626; }
    .dsb-label-ket { color: var(--dsb-redup); font-size: .76rem; font-weight: 500; }

    .dsb-isian {
        width: 100%; min-height: 40px; padding: 9px 13px;
        border: 1px solid var(--dsb-tepi); border-radius: 11px; background: #fff;
        color: var(--dsb-tinta); font-size: .86rem; font-weight: 600; font-family: inherit;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .dsb-isian::placeholder { color: #9aa5b5; font-weight: 500; }
    .dsb-isian:focus {
        outline: none; border-color: #c4b5fd; box-shadow: 0 0 0 3px rgba(124, 58, 237, .14);
    }
    .dsb-isian:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
    textarea.dsb-isian { min-height: 92px; line-height: 1.6; font-weight: 500; resize: vertical; }

    /* Panah kotak pilih digambar sendiri sebagai data URI: berkas gambar luar
       tidak ikut ter-deploy (public/build di-gitignore). */
    select.dsb-isian {
        appearance: none; padding-right: 32px; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6.5 8 10l3.5-3.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 11px center; background-size: 16px;
    }

    /* Kotak cari: ikon DI DALAM kotaknya, bukan tombol terpisah di sebelahnya. */
    .dsb-cari { position: relative; display: flex; align-items: center; min-width: 0; }
    .dsb-cari > i.bi {
        position: absolute; left: 12px; color: #94a3b8; font-size: .9rem;
        pointer-events: none; line-height: 1;
    }
    .dsb-cari > i.bi::before { display: block; line-height: 1; }
    .dsb-cari .dsb-isian { padding-left: 36px; }
    .dsb-cari-hapus {
        position: absolute; right: 8px; display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 8px; border: 0; background: transparent;
        color: #94a3b8; cursor: pointer; padding: 0; font-size: .8rem;
    }
    @media (hover: hover) and (pointer: fine) {
        .dsb-cari-hapus:hover { background: #f1f5f9; color: var(--dsb-tinta); }
    }

    .dsb-galat { color: #dc2626; font-size: .76rem; font-weight: 600; }
    /* Medan yang ditolak validasi. Warnanya di TEPI, bukan latar merah penuh:
       latar merah membuat teks yang sudah diketik jadi susah dibaca justru
       ketika orang harus membacanya untuk memperbaikinya. */
    .dsb-isian.is-galat { border-color: #fca5a5; }
    .dsb-isian.is-galat:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .14); }

    /* ===== Isi jendela (modal) =======================================
       Kepala, badan, dan kaki jendela. Ditulis di sistem desain karena
       jendela adalah tempat kedua setelah tabel di mana dialek gaya baru
       paling gampang tumbuh — tiap layar cenderung membuat kepalanya
       sendiri. */
    .dsb-jendela-kepala {
        display: flex; align-items: flex-start; gap: 13px;
        padding: 18px clamp(16px, 2.2vw, 22px); border-bottom: 1px solid #f1f5f9;
    }
    .dsb-jendela-teks { min-width: 0; flex: 1 1 auto; display: flex; flex-direction: column; gap: 7px; }
    .dsb-jendela-judul {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
        color: var(--dsb-tinta); font-size: 1.02rem; line-height: 1.3; margin: 0;
        overflow-wrap: anywhere;
    }
    .dsb-jendela-lencana { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
    .dsb-jendela-isi { padding: clamp(16px, 2.2vw, 22px); }
    .dsb-jendela-kaki {
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
        padding: 15px clamp(16px, 2.2vw, 22px);
        background: #fbfcfe; border-top: 1px solid #f1f5f9;
    }
    .dsb-jendela-kaki-ket {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--dsb-redup); font-size: .82rem; line-height: 1.5; min-width: 0;
    }
    .dsb-jendela-aksi { display: inline-flex; align-items: center; gap: 9px; flex-wrap: wrap; }
    /* Tombol tutup: kotak sama dengan tombol aksi baris, jadi sudut kanan atas
       jendela tidak punya ukuran sendiri. */
    .dsb-jendela-tutup {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px;
        background: #fff; border: 1px solid var(--dsb-tepi); color: #64748b;
        font-size: .9rem; cursor: pointer; padding: 0;
    }
    .dsb-jendela-tutup i.bi::before { display: block; line-height: 1; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-jendela-tutup:hover { background: #f1f5f9; color: var(--dsb-tinta); border-color: #cbd5e1; }
    }
    @media (max-width: 575.98px) {
        .dsb-jendela-tutup { width: 36px; height: 36px; }
        /* Keterangan kaki naik ke atas tombolnya; berdampingan, keduanya
           menyusut sampai teksnya pecah satu kata per baris. */
        .dsb-jendela-kaki { flex-direction: column; align-items: stretch; }
        .dsb-jendela-aksi { width: 100%; }
    }

    /* ===== Kerangka jendela (modal) =====
       Dipakai bersama oleh layar Task dan Pemesanan RSC — ditaruh di sini,
       bukan disalin ke tiap layar, supaya perilakunya (yang menggulung hanya
       jendelanya, halaman di belakang terkunci) tidak bisa menyimpang
       diam-diam antar-layar. Nama kelasnya dipertahankan (ts-modal) karena
       sudah dipakai empat jendela di layar Task. */
    .ts-modal-back { position: fixed; inset: 0; background: rgba(15, 23, 42, .5); backdrop-filter: blur(2px); z-index: 1055; }
    /* Yang menggulung hanya JENDELANYA, bukan halaman di belakangnya.

       Dulu lapisan .ts-modal sendiri yang menggulung, jadi saat jendelanya
       panjang: kepala dan tombol keputusannya ikut menghilang ke atas
       layar, dan halaman di belakangnya tetap bisa ikut tergulung —
       menutup jendela lalu mendaratkan pembacanya di tempat yang berbeda
       dari tempat ia menekan tadi. */
    .ts-modal {
        position: fixed; inset: 0; z-index: 1056;
        display: flex; align-items: center; justify-content: center;
        padding: 3vh 12px; overflow: hidden;
    }
    .ts-modal-card {
        background: #fff; border-radius: 22px; width: 100%; max-width: 560px;
        box-shadow: 0 30px 70px rgba(15, 23, 42, .32);
        /* Kolom: kepala & kaki tetap, badan yang menggulung. */
        display: flex; flex-direction: column;
        max-height: 94vh; min-height: 0; overflow: hidden;
    }
    .ts-modal-card > .dsb-jendela-kepala,
    .ts-modal-card > .dsb-jendela-kaki { flex: 0 0 auto; }
    .ts-modal-card > .dsb-jendela-isi { flex: 1 1 auto; min-height: 0; overflow-y: auto; -webkit-overflow-scrolling: touch; }
    /* Guliran yang mentok di ujung jendela tidak diteruskan ke halaman. */
    .ts-modal-card > .dsb-jendela-isi { overscroll-behavior: contain; }

    /* Halaman di belakang dikunci selama ada jendela terbuka. Lebar bilah
       gulung diganti padding supaya isinya tidak melompat mendatar saat
       bilah itu hilang. */
    body.ts-terkunci { overflow: hidden; }

    /* ===== Tabel =====================================================
       Ditulis di sistem desain, bukan di satu layar, karena layar admin
       berikutnya akan membutuhkan tabel yang sama — dan tabel adalah tempat
       dialek gaya baru paling gampang tumbuh.

       Bukan <table> berpembatas kotak: barisnya dibuat setinggi baris daftar
       (.dsb-baris) supaya tabel dan daftar di layar yang sama terbaca sebagai
       satu keluarga. */
    .dsb-tabel-bungkus {
        /* Satu-satunya tempat gulung mendatar dibolehkan. Tanpa ini, tabel
           lebar mendorong SELURUH halaman dan tiap layar ikut bergeser. */
        overflow-x: auto; -webkit-overflow-scrolling: touch;
    }
    .dsb-tabel { width: 100%; border-collapse: collapse; min-width: 720px; }
    .dsb-tabel thead th {
        text-align: left; padding: 11px 14px;
        background: #f8fafc; border-bottom: 1px solid var(--dsb-tepi);
        color: var(--dsb-redup); font-size: .72rem; font-weight: 800;
        letter-spacing: .04em; text-transform: uppercase; white-space: nowrap;
    }
    .dsb-tabel thead th:first-child { border-top-left-radius: 12px; }
    .dsb-tabel thead th:last-child { border-top-right-radius: 12px; }
    .dsb-tabel tbody td { padding: 12px 14px; border-bottom: 1px solid #f2f5f9; vertical-align: middle; }
    /* Hanya baris paling akhir DI SELURUH tabel yang kehilangan garisnya.
       Dengan satu <tbody> per task, `tbody tr:last-child` akan menghapus
       pemisah antar-task juga. */
    .dsb-tabel tbody:last-child tr:last-child td { border-bottom: 0; }
    .dsb-tabel tbody tr.is-klik { cursor: pointer; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tabel tbody tr.is-klik:hover td { background: #f8fafc; }
    }
    /* Baris yang menuntut perhatian diberi pita warna di tepi kiri, bukan
       seluruh barisnya diwarnai: latar berwarna penuh membuat teksnya susah
       dibaca dan tabel terlihat seperti peringatan galat. */
    .dsb-tabel tbody tr.is-tanda td:first-child { box-shadow: inset 3px 0 0 var(--c, #e11d48); }
    .dsb-tabel .dsb-tabel-utama { display: flex; align-items: center; gap: 11px; min-width: 0; }
    /* align-items: flex-start — tanpa ini lencana di dalam kolom ini melar
       selebar kolomnya (bawaan flex adalah stretch) dan "Selesai" terbaca
       seperti batang hijau, bukan lencana. */
    .dsb-tabel .dsb-tabel-teks {
        min-width: 0; display: flex; flex-direction: column; align-items: flex-start; gap: 3px;
    }
    .dsb-tabel .dsb-tabel-judul {
        font-weight: 700; color: var(--dsb-tinta); font-size: .88rem; line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .dsb-tabel .dsb-tabel-meta {
        display: flex; align-items: center; flex-wrap: wrap; gap: 6px;
        color: var(--dsb-redup); font-size: .76rem;
    }
    .dsb-tabel .dsb-tabel-angka { font-variant-numeric: tabular-nums; white-space: nowrap; }
    /* Garis kemajuan harus melebar mengikuti kolomnya; sebagai anak kolom
       ber-align-items: flex-start ia menyusut sampai selebar nol. */
    .dsb-tabel .dsb-tabel-teks > .dsb-kemajuan { align-self: stretch; min-width: 90px; }
    .dsb-tabel .dsb-tabel-aksi { display: inline-flex; align-items: center; gap: 6px; }

    /* Kepala kolom yang bisa diurutkan. Panah hanya muncul pada kolom yang
       sedang dipakai; panah di semua kolom sekaligus membuat tidak ada yang
       terbaca sebagai aktif. */
    .dsb-tabel-urut {
        display: inline-flex; align-items: center; gap: 6px; padding: 0;
        background: none; border: 0; cursor: pointer; color: inherit;
        font: inherit; letter-spacing: inherit; text-transform: inherit;
    }
    .dsb-tabel-urut i.bi { font-size: .78rem; opacity: .45; }
    .dsb-tabel-urut i.bi::before { display: block; line-height: 1; }
    .dsb-tabel-urut.aktif { color: var(--dsb-tinta); }
    .dsb-tabel-urut.aktif i.bi { opacity: 1; color: #7c3aed; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tabel-urut:hover { color: var(--dsb-tinta); }
        .dsb-tabel-urut:hover i.bi { opacity: .85; }
    }

    /* Salinan isi kolom yang menghilang di layar sempit. Di layar lebar ia
       tidak perlu — kolomnya sendiri sudah ada — jadi disembunyikan supaya
       keterangannya tidak tertulis dua kali berdampingan. */
    .dsb-tabel-samar { display: none; align-items: center; gap: 5px; }
    @media (max-width: 1199.98px) {
        .dsb-tabel-samar { display: inline-flex; }
    }
    @media (max-width: 767.98px) {
        .dsb-tabel-samar { display: none; }
    }

    /* Avatar kecil untuk sub-baris. */
    .dsb-avatar.is-kecil { width: 30px; height: 30px; border-radius: 9px; font-size: .78rem; }

    /* x-cloak: sub-baris disembunyikan SEBELUM Alpine sempat jalan. Tanpa ini,
       seluruh penerima berkedip tampil sekejap tiap halaman dimuat. */
    [x-cloak] { display: none !important; }

    /* Tombol aksi baris: kotak 34px, ikon di tengah. Kecil, tetapi tetap di
       atas ambang yang bisa ditekan jempol saat barisnya jadi kartu. */
    .dsb-tabel-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 10px;
        background: #fff; border: 1px solid var(--dsb-tepi); color: #64748b;
        font-size: .9rem; cursor: pointer; padding: 0;
        transition: color .15s ease, border-color .15s ease, background .15s ease;
    }
    .dsb-tabel-btn i.bi::before { display: block; line-height: 1; }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tabel-btn:hover { background: #f1f5f9; color: var(--dsb-tinta); border-color: #cbd5e1; }
        .dsb-tabel-btn.is-bahaya:hover { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    }
    @media (max-width: 991.98px), (pointer: coarse) {
        .dsb-tabel-btn { width: 36px; height: 36px; }
    }

    /* Kolom yang boleh menghilang lebih dulu saat layar menyempit. Isinya
       TIDAK hilang: yang disembunyikan selalu punya salinan di baris meta di
       bawah judul, jadi tidak ada informasi yang lenyap bersama kolomnya. */
    @media (max-width: 1199.98px) { .dsb-tabel .k-lebar { display: none; } }
    @media (max-width: 991.98px) { .dsb-tabel .k-sedang { display: none; } }

    /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu bertumpuk.
       Tabel yang digulung mendatar di layar 390px praktis tidak terbaca —
       kolom pertama hilang begitu digulung, dan tanpa kolom pertama sisanya
       kehilangan konteks. */
    @media (max-width: 767.98px) {
        .dsb-tabel { min-width: 0; }
        .dsb-tabel thead { display: none; }
        .dsb-tabel tbody tr {
            display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9;
        }
        .dsb-tabel tbody td {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 4px 0; border: 0;
        }
        .dsb-tabel tbody td:first-child { padding-bottom: 9px; }
        .dsb-tabel tbody td[data-judul]::before {
            content: attr(data-judul);
            color: var(--dsb-redup); font-size: .72rem; font-weight: 700;
            letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
        }
        .dsb-tabel .k-lebar, .dsb-tabel .k-sedang { display: flex; }
        /* Sel tanpa isi tidak ikut mencetak judulnya: baris berlabel tanpa
           nilai terbaca seperti data yang gagal dimuat. */
        .dsb-tabel tbody td.is-kosong { display: none; }
        .dsb-tabel tbody tr.is-tanda { box-shadow: inset 3px 0 0 var(--c, #e11d48); }
        .dsb-tabel tbody tr.is-tanda td:first-child { box-shadow: none; }
    }

    /* ===== Cetak =====
       Dasbor sering dicetak atau disimpan jadi PDF untuk rapat. Tanpa aturan
       ini yang ikut tercetak adalah sidebar, tombol, dan bayangan kartu —
       tiga hal yang tidak berarti apa-apa di atas kertas. */
    @media print {
        .dsb { background: #fff; }
        .dsb-hero-aksi, .dsb-geser, .dsb-tautan, .dsb-aksi, .dsb-tombol,
        .dsb-segar.is-tombol, .dsb-baris-aksi, .dsb-memuat { display: none !important; }
        .dsb-kartu, .dsb-stat, .dsb-tugas {
            box-shadow: none !important; border: 1px solid #cbd5e1 !important;
            break-inside: avoid; page-break-inside: avoid;
        }
        .dsb-bagian { break-inside: avoid; }
        .dsb-rak { gap: 10px; }
        /* Warna latar ubin ikon & lencana harus benar-benar tercetak; tanpa
           ini peramban membuangnya dan status kehilangan artinya. */
        .dsb-ikon, .dsb-lencana, .dsb-pil, .dsb-chip, .bnd-pil {
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
    }
</style>

<script>
        /* Kunci gulung halaman selama ada jendela terbuka.

           Dipasang sebagai PENGAMAT, bukan ditempel di tiap tombol buka/tutup:
           jendela di layar ini dibuka dan ditutup oleh Livewire (dan bisa juga
           oleh tombol Esc, klik latar, atau perpindahan halaman), jadi satu-
           satunya tempat yang pasti tahu keadaannya adalah DOM itu sendiri. */
        (function () {
            // Sekali saja per sesi halaman. wire:navigate menjalankan ulang skrip
            // di badan halaman tiap pindah layar; tanpa penjaga ini, pengamat
            // dan penangan Esc akan terpasang berkali-kali.
            if (window.__dsbJendelaTerpasang) return;
            window.__dsbJendelaTerpasang = true;

            // SELALU <body> yang sedang tampil, jangan disimpan sekali di awal:
            // wire:navigate mengganti elemen <body> tiap pindah halaman, dan
            // kunci yang dipasang ke <body> lama tidak berpengaruh apa-apa —
            // halaman di belakang jendela tetap ikut tergulir.
            const badan = () => document.body;

            const lebarBilah = () => window.innerWidth - document.documentElement.clientWidth;

            // Elemen yang dipegang fokus SEBELUM jendela terbuka, supaya bisa
            // dikembalikan saat ditutup. Tanpa itu, pengguna papan ketik
            // mendarat di awal halaman tiap kali menutup satu task.
            let fokusSebelumnya = null;

            const perbarui = () => {
                const jendela = document.querySelector('.ts-modal [role="dialog"]');
                const adaJendela = !!jendela;
                if (adaJendela === badan().classList.contains('ts-terkunci')) return;

                if (adaJendela) {
                    const bilah = lebarBilah();
                    badan().classList.add('ts-terkunci');
                    // Tanpa ini, hilangnya bilah gulung melebarkan halaman dan
                    // seluruh isinya bergeser beberapa piksel saat jendela dibuka.
                    if (bilah > 0) badan().style.paddingRight = bilah + 'px';

                    fokusSebelumnya = document.activeElement;
                    // Fokus ke jendelanya, bukan ke tombol pertamanya: pembaca
                    // layar lalu membacakan judul dialognya lebih dulu.
                    jendela.focus({ preventScroll: true });
                } else {
                    badan().classList.remove('ts-terkunci');
                    badan().style.paddingRight = '';

                    if (fokusSebelumnya && document.contains(fokusSebelumnya)) {
                        fokusSebelumnya.focus({ preventScroll: true });
                    }
                    fokusSebelumnya = null;
                }
            };

            /* Esc menutup jendela teratas.

               Ditulis di sini, bukan di tiap jendela: keempat jendela di layar
               ini ditutup lewat properti Livewire yang berbeda-beda, dan
               nama propertinya sudah dibawa tiap kartu lewat data-tutup. */
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;

                const jendela = [...document.querySelectorAll('.ts-modal [data-tutup]')].pop();
                if (!jendela) return;

                const akar = jendela.closest('[wire\\:id]');
                if (!akar || !window.Livewire) return;

                e.preventDefault();
                window.Livewire.find(akar.getAttribute('wire:id'))?.set(jendela.dataset.tutup, false);
            });

            perbarui();
            new MutationObserver(perbarui).observe(document.documentElement, { childList: true, subtree: true });

            // Berpindah halaman lewat wire:navigate tidak selalu melepas kelasnya
            // sendiri — dan halaman berikutnya lalu tidak bisa digulung sama sekali.
            document.addEventListener('livewire:navigating', () => {
                badan().classList.remove('ts-terkunci');
                badan().style.paddingRight = '';
            });
        })();
</script>
@endonce
