{{--
    Gaya khas Arsip Surat.

    Ditulis INLINE, bukan lewat Vite: public/build di-gitignore, jadi berkas
    hasil build tidak ikut ter-deploy dan CSS-nya tidak akan pernah sampai ke
    server. Yang umum tetap datang dari dasbor-gaya; di sini hanya yang
    memang khas layar surat.

    Semua ukuran memakai clamp() supaya satu aturan melayani ponsel sampai
    layar lebar tanpa belasan media query yang saling menimpa.
--}}
@once
<style>
    /* Baris aksi: ikon, penjelasan, lalu tombol di ujung kanan. Dipakai di
       kartu yang isinya satu keputusan saja. */
    .sr-baris-aksi {
        display: flex;
        align-items: center;
        gap: .85rem;
        flex-wrap: wrap;
    }

    .sr-baris-teks {
        display: flex;
        flex-direction: column;
        gap: .1rem;
        flex: 1 1 16rem;
        min-width: 0;
    }

    .sr-baris-teks b { font-size: .92rem; }

    /* ---------- Tab status sebagai kartu ---------- */
    /* Angkanya ikut di kartunya, bukan cuma namanya: yang pertama dicari
       staf administrasi tiap pagi adalah BERAPA yang menumpuk, dan menaruh
       angka itu di balik satu klik membuat halaman ini harus dibuka dua kali. */
    .sr-tab-deret {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(8.5rem, 1fr));
        gap: .5rem;
    }

    .sr-tab {
        --c: #64748b;
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .6rem .7rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }

    .sr-tab:hover { border-color: var(--c); transform: translateY(-1px); }

    .sr-tab.is-aktif {
        border-color: var(--c);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--c) 22%, transparent);
        background: color-mix(in srgb, var(--c) 7%, #fff);
    }

    /* Tab yang angkanya nol disamarkan, TIDAK disembunyikan: hilangnya tab
       membuat posisi tab lain bergeser tiap kali data berubah, dan admin
       kehilangan hafalan letaknya. */
    .sr-tab.is-nol { opacity: .62; }

    .sr-tab-ikon {
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        width: 2rem;
        height: 2rem;
        border-radius: 9px;
        background: color-mix(in srgb, var(--c) 14%, #fff);
        color: var(--c);
        font-size: .95rem;
    }

    .sr-tab-teks { display: flex; flex-direction: column; min-width: 0; line-height: 1.2; }
    .sr-tab-angka { font-size: 1.02rem; font-weight: 800; color: #0f172a; }
    .sr-tab-label { font-size: .72rem; font-weight: 600; color: #64748b; }

    /* ---------- Saringan ---------- */
    .sr-saring {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
        gap: .6rem .7rem;
        align-items: end;
    }

    /* Kotak cari diberi ruang lebih: isinya kalimat, bukan pilihan. */
    .sr-saring .sr-medan-cari { grid-column: span 2; }

    @media (max-width: 575.98px) {
        .sr-saring .sr-medan-cari { grid-column: span 1; }
    }

    /* ---------- Kepala daftar ---------- */
    .sr-daftar-kepala {
        --c: #0ea5c6;
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .7rem .9rem;
        border-bottom: 1px solid #eef2f7;
    }

    .sr-daftar-judul { display: flex; flex-direction: column; line-height: 1.25; flex: 1 1 auto; min-width: 0; }
    .sr-daftar-judul b { font-size: .92rem; }
    .sr-daftar-judul span { font-size: .74rem; color: #64748b; }

    /* ---------- Rak yang tidak meregang ---------- */
    /* dsb-rak meregangkan kartu sebaris jadi setinggi yang tertinggi. Bagus
       untuk kartu angka yang memang harus segaris, buruk untuk dua daftar
       yang panjangnya berbeda: yang pendek menyisakan lubang putih sebesar
       selisihnya. Di sini tiap kartu berhenti di akhir isinya. */
    .sr-rak-puncak { align-items: start; }

    /* ---------- Tabel ---------- */
    /* Lebar kolom ditentukan di sini, karena k-lebar/k-sedang di dasbor-gaya
       HANYA menyembunyikan kolom di layar sempit — keduanya tidak mengatur
       lebar sama sekali. Tanpa ini, delapan kolom dibagi rata dan "Perihal"
       — satu-satunya kolom berisi kalimat — terlipat jadi empat baris
       sementara kolom lencana menyisakan ruang kosong.

       Persentase pada table-layout bawaan bersifat saran, bukan kunci; itu
       disengaja supaya sel berisi nama panjang masih boleh melebar. */
    .sr-tabel .sr-kol-perihal { width: 34%; }
    .sr-tabel .sr-kol-pihak { width: 18%; }
    .sr-tabel .sr-kol-tanggal { width: 15%; }
    .sr-tabel .sr-kol-aksi { width: 1%; white-space: nowrap; }

    /* Tabel yang hidup di dalam kartu setengah lebar. dsb-tabel memaksa
       min-width 720px supaya tabel berkolom banyak tidak terhimpit; di kartu
       7 dari 12 lajur, aturan itu justru membuatnya meluber dan memotong
       kolom paling kanan. */
    .sr-tabel-sempit { min-width: 560px; }

    /* Nama klasifikasi tidak boleh dipenggal di tengah kata: "Umum &
       Ketatausaha/an" terbaca seperti data yang rusak, bukan seperti kolom
       yang sempit. */
    .sr-tabel-sempit .dsb-tabel-judul { word-break: normal; overflow-wrap: break-word; }

    /* Nomor agenda tidak boleh terpotong jadi dua baris: itu nomor yang
       disebutkan orang, dan "SM-2026-" di satu baris tidak berarti apa-apa. */
    .sr-tabel .sr-agenda { white-space: nowrap; font-variant-numeric: tabular-nums; }

    .sr-pihak { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }

    /* Garis sifat di tepi kiri baris: surat segera & rahasia harus terlihat
       saat mata memindai, tanpa harus membaca kolomnya. */
    .sr-tabel tbody tr { border-left: 3px solid transparent; }
    .sr-tabel tbody tr.is-segera { border-left-color: #d97706; }
    .sr-tabel tbody tr.is-sangat-segera { border-left-color: #e11d48; }
    .sr-tabel tbody tr.is-rahasia { border-left-color: #7c3aed; }

    .sr-halaman { padding: .7rem .9rem 0; }
    .sr-halaman .pagination { margin: 0; justify-content: center; flex-wrap: wrap; }

    /* ---------- Borang ---------- */
    .sr-borang {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
        gap: .75rem .85rem;
        align-items: start;
    }

    .sr-borang .sr-lebar { grid-column: 1 / -1; }

    /* ---------- Nomor surat keluar ---------- */
    .sr-nomor-saklar {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin: 0 0 .4rem;
        padding: .32rem .6rem;
        border: 1px solid #e3d9c4;
        border-radius: 999px;
        background: #fdf9f0;
        color: #6b5a33;
        font-size: .78rem;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
    }

    .sr-nomor-saklar input { width: 15px; height: 15px; margin: 0; accent-color: #a16207; cursor: pointer; }

    .sr-nomor-baris { display: flex; align-items: center; gap: .45rem; flex-wrap: wrap; }
    .sr-nomor-baris .dsb-isian { flex: 1 1 14rem; min-width: 0; font-variant-numeric: tabular-nums; }

    /* Kotak yang sedang diisikan sistem dibedakan warnanya, bukan hanya
       dikunci: kotak abu-abu yang tetap putih membuat admin mengetik di
       dalamnya lalu heran ketikannya tidak muncul. */
    .sr-nomor-baris .dsb-isian[readonly] { background: #faf6ec; color: #6b5a33; cursor: default; }

    /* Arti tiap potongan nomor. Nomor surat adalah satu-satunya bagian borang
       yang bentuknya diatur aturan kantor, dan admin baru tidak punya tempat
       lain untuk belajar membacanya. */
    .sr-nomor-arti { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }

    .sr-nomor-arti span {
        display: inline-flex;
        flex-direction: column;
        gap: .1rem;
        padding: .25rem .5rem;
        border: 1px solid #ece3d2;
        border-radius: 8px;
        background: #fffdf8;
        line-height: 1.25;
    }

    .sr-nomor-arti b { font-size: .8rem; color: #3f3524; font-variant-numeric: tabular-nums; }
    .sr-nomor-arti i { font-size: .66rem; font-style: normal; color: #8a7c5e; }

    /* ---------- Rincian (halaman detail) ---------- */
    /* Dua kolom rapat: label kecil di atas, isinya di bawah. Tabel dua kolom
       akan menyisakan lajur kosong panjang pada nilai yang pendek. */
    .sr-rincian {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
        gap: .7rem .9rem;
    }

    .sr-rincian > div { display: flex; flex-direction: column; gap: .12rem; min-width: 0; }
    .sr-rincian dt { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .sr-rincian dd { margin: 0; font-size: .88rem; color: #0f172a; word-break: break-word; }

    /* ---------- Lampiran & disposisi ---------- */
    .sr-lampiran-baris {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .9rem;
        border-top: 1px solid #f1f5f9;
    }

    .sr-lampiran-teks { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; line-height: 1.3; }
    .sr-lampiran-teks b { font-size: .85rem; word-break: break-all; }
    .sr-lampiran-teks span { font-size: .72rem; color: #64748b; }

    /* ---------- Antrean pindaian (belum tersimpan) ---------- */
    /* Nama berkasnya ditampilkan satu per satu, bukan cuma jumlahnya: admin
       yang memilih sepuluh lembar perlu melihat lembar mana yang sudah masuk
       sebelum menekan simpan, dan angka "10 berkas" tidak menjawab itu. */
    .sr-antrean {
        display: flex;
        flex-direction: column;
        gap: .3rem;
        margin: .5rem 0 .35rem;
    }

    .sr-antrean-baris {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .35rem .55rem;
        border: 1px solid #e8eef5;
        border-radius: 9px;
        background: #f8fbff;
    }

    .sr-antrean-baris .bi-paperclip { color: #0284c7; font-size: .9rem; flex: 0 0 auto; }

    .sr-antrean-teks { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; line-height: 1.25; }
    .sr-antrean-teks b { font-size: .8rem; word-break: break-all; }
    .sr-antrean-teks span { font-size: .7rem; color: #64748b; }

    .sr-dis-baris {
        --c: #0284c7;
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        padding: .65rem .9rem;
        border-top: 1px solid #f1f5f9;
        border-left: 3px solid var(--c);
    }

    .sr-dis-isi { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: .18rem; }
    .sr-dis-instruksi { font-size: .86rem; color: #0f172a; white-space: pre-line; word-break: break-word; }
    .sr-dis-meta { font-size: .73rem; color: #64748b; display: flex; flex-wrap: wrap; gap: .35rem .6rem; }

    /* ---------- Ponsel ---------- */
    @media (max-width: 575.98px) {
        /* Tiga tombol di kepala halaman tidak boleh dibungkus ke baris baru
           satu per satu: barisnya jadi tiga tingkat dan daftar suratnya
           terdorong keluar layar pertama. Dibiarkan sebaris dengan sebagian
           teksnya disembunyikan — pola yang sama dipakai Pesanan Toko.
           overflow: hidden sebagai jaring kalau huruf perangkat diperbesar
           lewat pengaturan aksesibilitas. */
        .sr-hero-daftar .dsb-hero-aksi { flex-wrap: nowrap; gap: 8px; }

        .sr-hero-daftar .dsb-hero-aksi .dsb-tombol {
            min-width: 0;
            padding-left: 14px;
            padding-right: 14px;
            overflow: hidden;
        }

        .sr-hp-sembunyi { display: none; }
    }
</style>
@endonce
