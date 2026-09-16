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
    /* ===== Kerangka halaman ===================================== */
    .dsb {
        --dsb-tepi: #e9edf3;
        --dsb-tinta: #1c1f26;
        --dsb-redup: #6b7280;
        --dsb-jingga: #f26522;
        padding: 0 clamp(4px, 1.4vw, 18px) 28px;
    }

    .dsb-bagian { margin-bottom: clamp(20px, 3vw, 32px); }

    /* Kepala bagian.
       Versi pertamanya hanya tumpukan teks rata kiri: label kecil, judul, lalu
       satu kalimat panjang berisi periode — terbaca sebagai paragraf, bukan
       sebagai kepala bagian, dan tidak ada apa pun yang menahan mata. Sekarang
       ia punya tiga bagian yang jelas: ubin ikon berwarna sebagai jangkar,
       judul, dan keterangan yang dipadatkan jadi chip. */
    .dsb-kepala {
        display: flex; align-items: center; gap: 14px; margin-bottom: 16px;
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
        background: color-mix(in srgb, var(--c) 9%, #fff);
        border: 1px solid color-mix(in srgb, var(--c) 20%, #fff);
        color: color-mix(in srgb, var(--c) 75%, #000);
        font-size: .74rem; font-weight: 700; white-space: nowrap;
    }
    .dsb-chip i.bi { font-size: .78rem; line-height: 1; }
    .dsb-chip i.bi::before { display: block; line-height: 1; }
    .dsb-chip.is-samar {
        background: #f8fafc; border-color: var(--dsb-tepi); color: #64748b; font-weight: 600;
        white-space: normal;
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
        background: color-mix(in srgb, var(--c, #f1f5f9) 7%, #fff);
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
        transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
    }
    .dsb-kartu-isi { padding: clamp(16px, 2.2vw, 22px); }
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
    .dsb-titik.is-luring { background: #94a3b8; }
    .dsb-aku-nama {
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700;
        color: var(--dsb-tinta); font-size: .92rem; line-height: 1.2; margin: 0;
    }
    .dsb-aku-peran {
        display: block; color: var(--dsb-redup); font-size: .74rem; margin-top: 3px;
    }

    /* Tombol. Ditulis penuh (bukan .btn bawaan) supaya tinggi, radius, dan
       ikonnya tidak bergantung pada gaya global template. */
    .dsb-tombol {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        height: 44px; padding: 0 18px; border: 1px solid transparent; border-radius: 12px;
        font-size: .86rem; font-weight: 700; line-height: 1; text-decoration: none;
        cursor: pointer; white-space: nowrap;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
    }
    .dsb-tombol i.bi { font-size: .95rem; line-height: 1; }
    .dsb-tombol i.bi::before { display: block; line-height: 1; }
    .dsb-tombol.is-utama { background: #7c3aed; color: #fff; box-shadow: 0 8px 18px rgba(124, 58, 237, .26); }
    .dsb-tombol.is-bahaya { background: #fff; color: #dc2626; border-color: #fecaca; }
    .dsb-tombol.is-lembut { background: #fff; color: #475569; border-color: var(--dsb-tepi); }
    @media (hover: hover) and (pointer: fine) {
        .dsb-tombol:hover { transform: translateY(-2px); }
        .dsb-tombol.is-bahaya:hover { background: #dc2626; color: #fff; border-color: transparent; }
        .dsb-tombol.is-lembut:hover { border-color: #cbd5e1; color: #1c1f26; }
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
        position: relative; overflow: hidden;
        background: #fff; border: 1px solid var(--dsb-tepi); border-radius: 16px;
        padding: 20px;
        display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: 16px;
        align-content: start;
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
    .dsb-stat-ket {
        display: flex; align-items: center; gap: 6px;
        color: var(--dsb-redup); font-size: .76rem; line-height: 1.5; margin: 8px 0 0;
    }
    .dsb-stat-ket i.bi { flex-shrink: 0; }
    .dsb-stat.is-utama { padding: 22px; column-gap: 18px; }
    .dsb-stat.is-utama .dsb-stat-nilai { font-size: clamp(1.5rem, 3.2vw, 2rem); }
    .dsb-stat.is-utama > .dsb-ikon { width: 54px; height: 54px; border-radius: 16px; font-size: 1.5rem; }

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
        padding: 18px clamp(16px, 2.2vw, 22px); border-bottom: 1px solid #f1f5f9;
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
        .dsb-baris { gap: 10px; padding-inline: 16px; }
        .dsb-avatar { width: 34px; height: 34px; border-radius: 10px; font-size: .85rem; }
        .dsb-kartu-kepala { padding-inline: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .dsb-stat, .dsb-stat::before, .dsb-ikon, .dsb-tombol, .dsb-tautan, .dsb-tautan i.bi { transition: none; }
    }
</style>
@endonce
