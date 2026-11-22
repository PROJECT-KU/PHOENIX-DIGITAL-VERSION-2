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

    /* ---------- Borang catat surat: dua lajur tetap ---------- */
    /*
       DUA lajur, bukan auto-fit.

       Dengan auto-fit, lebar kartu menentukan jumlah lajurnya: pada layar
       lebar muncul TIGA lajur, dan medan terakhir berdiri sendirian dengan
       dua lajur kosong menganga di sebelahnya. Jumlah medannya genap, jadi
       dua lajur selalu habis terbagi.
    */
    .sr-borang-duo { grid-template-columns: repeat(2, minmax(0, 1fr)); }

    /* ---------- Pilih karyawan penanggung jawab ---------- */
    .sr-pilih-kotak {
        display: flex;
        align-items: center;
        gap: .6rem;
        flex-wrap: wrap;
        padding: .5rem .6rem;
        border: 1px solid #e6e9f0;
        border-radius: 12px;
        background: #fcfcfe;
    }

    .sr-pilih-kotak.is-galat { border-color: #e11d48; background: #fff1f2; }
    .sr-pilih-terpilih { display: flex; align-items: center; gap: .55rem; flex: 1 1 12rem; min-width: 0; }

    .sr-pilih-ikon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        flex: 0 0 auto;
        border-radius: 9px;
        background: #f3e8ff;
        color: #7c3aed;
        font-size: .9rem;
    }

    .sr-pilih-teks { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
    .sr-pilih-teks b { font-size: .85rem; }
    .sr-pilih-teks span { font-size: .72rem; color: #64748b; }
    .sr-pilih-belum { flex: 1 1 8rem; font-size: .82rem; color: #94a3b8; }
    .sr-pilih-aksi { display: flex; align-items: center; gap: .35rem; flex-wrap: wrap; }

    /* Isi jendela pilih (SweetAlert). Jendelanya dirender di body, di luar
       komponen — gaya ini global sehingga tetap terpakai di sana. */
    .sr-pilih-cari {
        width: 100%;
        padding: .55rem .75rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: .9rem;
        outline: 0;
    }

    .sr-pilih-cari:focus { border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); }

    .sr-pilih-daftar {
        margin-top: .6rem;
        max-height: 17rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: .25rem;
        text-align: left;
    }

    .sr-pilih-baris {
        display: flex;
        flex-direction: column;
        gap: .1rem;
        width: 100%;
        padding: .5rem .65rem;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }

    .sr-pilih-baris:hover { border-color: #c4b5fd; background: #faf5ff; }
    .sr-pilih-baris b { font-size: .86rem; color: #1f2937; }
    .sr-pilih-baris span { font-size: .72rem; color: #64748b; }
    .sr-pilih-nihil { margin: .8rem 0; font-size: .85rem; color: #94a3b8; }

    /* ---------- Kotak unggah berkas ---------- */
    /* Input berkas bawaan peramban ditindih, bukan disembunyikan: elemennya
       tetap ada dan tetap yang menerima klik, jadi wire:model dan pemilihan
       banyak berkas bekerja persis seperti sebelumnya. */
    .sr-unggah {
        position: relative;
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem .85rem;
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        cursor: pointer;
        transition: border-color .15s, background .15s;
    }

    .sr-unggah:hover { border-color: #0891b2; background: #f0fdff; }
    .sr-unggah.is-galat { border-color: #e11d48; background: #fff1f2; }

    .sr-unggah-asli {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .sr-unggah-ikon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        border-radius: 10px;
        background: #e0f2fe;
        color: #0369a1;
        font-size: 1rem;
    }

    .sr-unggah-teks { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
    .sr-unggah-teks b { font-size: .85rem; color: #334155; }
    .sr-unggah-teks span { font-size: .72rem; color: #64748b; }

    /* ---------- Tembusan sebagai keping ---------- */
    /* Kotaknya meniru .dsb-isian tetapi tingginya ikut isinya, dan isian
       teksnya duduk di dalam kotak yang sama supaya terbaca sebagai SATU
       medan, bukan dua. */
    .sr-tembusan {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .3rem;
        height: auto;
        min-height: 2.6rem;
        padding: .3rem .45rem;
        cursor: text;
    }

    .sr-tembusan:focus-within { border-color: #a5b4fc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); }

    .sr-tembusan-ketik {
        flex: 1 1 7rem;
        min-width: 6rem;
        border: 0;
        outline: 0;
        background: transparent;
        padding: .18rem .1rem;
        font: inherit;
        color: inherit;
    }

    .sr-keping {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        max-width: 100%;
        padding: .18rem .3rem .18rem .5rem;
        border: 1px solid #dbeafe;
        border-radius: 999px;
        background: #eff6ff;
        color: #1e3a8a;
        font-size: .78rem;
        line-height: 1.3;
    }

    .sr-keping span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .sr-keping button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: rgba(30, 58, 138, .1);
        color: #1e3a8a;
        font-size: .6rem;
        cursor: pointer;
    }

    .sr-keping button:hover { background: #1e3a8a; color: #fff; }

    /*
       Medan yang IKUT MEMANJANG mengikuti tinggi barisnya.

       Antrean pindaian di sel sebelah tumbuh tiap kali satu berkas dipilih,
       dan sel ini akan berhenti di tinggi aslinya — menyisakan ruang kosong
       memanjang di kiri justru saat borangnya paling penuh. Kotak ringkasan
       ikut memanjang, jadi ruang itu terpakai untuk hal yang memang berguna:
       tempat menulis lebih lega.
    */
    .sr-tumbuh { align-self: stretch; display: flex; flex-direction: column; }
    .sr-tumbuh textarea { flex: 1 1 auto; min-height: 7rem; }

    /* Satu sel kisi yang berisi beberapa medan bertumpuk. */
    .sr-kolom { display: flex; flex-direction: column; gap: .75rem; min-width: 0; }

    /*
       Pemisah antar kartu: garis warna di tepi atas.

       Kartu putih di atas latar nyaris putih hanya dibatasi tepi tipis, dan
       pada borang panjang ketiganya terbaca seperti satu bidang. Garisnya
       memakai warna ikon kartu itu sendiri, jadi ia sekaligus menandai
       bagian mana yang sedang dibaca.
    */
    .sr-kartu-borang {
        position: relative;
        overflow: hidden;
        border-color: #e6e9f0;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -16px rgba(15, 23, 42, .18);
    }

    .sr-kartu-borang::before {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: var(--aksen, #94a3b8);
    }

    /* Garis tipis memisahkan judul kartu dari isiannya. */
    .sr-kartu-borang .dsb-kartu-kepala { border-bottom: 1px solid #f1f5f9; }

    /*
       Kedua kartu atas berakhir di garis yang sama.

       Tingginya disamakan, lalu medan-medannya direnggangkan merata supaya
       kartu yang isinya lebih pendek tidak menyisakan blok kosong di bawah —
       jarak antar medan yang sedikit lebih lega terbaca sebagai kelonggaran,
       bukan sebagai ruang yang terlupa diisi.
    */
    /*
       Dua kartu atas berakhir di garis yang sama.

       Tingginya disamakan, lalu medan-medannya direnggangkan merata supaya
       kartu yang isinya lebih pendek tidak menyisakan blok kosong di bawah.
       Jarak antar medan yang sedikit lebih lega terbaca sebagai kelonggaran,
       bukan sebagai ruang yang terlupa diisi.

       Hanya kartu BORANG yang diperlakukan begini. Bilah "Simpan ke buku
       agenda" juga tinggal di rak yang sama dan isinya sengaja berbaris
       mendatar; memaksanya jadi kolom membuat tombolnya turun ke tengah dan
       bilahnya memanjang ke bawah tanpa isi.
    */
    .sr-rak-borang { align-items: stretch; }
    .sr-rak-borang > .sr-kartu-borang { display: flex; flex-direction: column; }
    .sr-rak-borang > .sr-kartu-borang > .dsb-kartu-isi { flex: 1 1 auto; display: flex; flex-direction: column; }
    .sr-rak-borang > .sr-kartu-borang > .dsb-kartu-isi > .sr-borang { flex: 1 1 auto; align-content: space-between; }

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
    /* Keping arti nomor DIBAGI RATA selebar kotaknya.
       Dibiarkan mengalir, keping terakhir berhenti di tengah dan menyisakan
       ruang kosong memanjang di kanannya — padahal jumlahnya berubah
       (empat keping tanpa klasifikasi, lima dengan klasifikasi). */
    .sr-nomor-arti { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: .45rem; }
    .sr-nomor-arti span { flex: 1 1 0; min-width: 4.4rem; }

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
    /*
       Bentuknya meniru kartu rincian Pesanan Toko: nama medan di kiri,
       isinya di kanan, dipisah garis putus halus.

       Dua lajur, bukan empat. Empat lajur membuat nilai sepanjang nama orang
       patah jadi dua baris, dan tinggi tiap sel jadi tidak sama — daftarnya
       terbaca bergerigi. Dua lajur juga membagi sepuluh medan dengan rata
       (5 + 5), jadi baris terakhirnya tidak pernah menyisakan sel menganga.
    */
    .sr-rincian {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 1.7rem;
    }

    .sr-rincian > div {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: .9rem;
        min-width: 0;
        padding: .5rem 0;
        border-bottom: 1px dashed #e6ebf3;
    }

    /* Dua medan terakhir menutup daftarnya — satu di tiap lajur. */
    .sr-rincian > div:nth-last-child(-n+2) { border-bottom: 0; padding-bottom: 0; }

    .sr-rincian dt { flex: 0 0 auto; font-size: .8rem; font-weight: 500; color: #64748b; }

    .sr-rincian dd {
        margin: 0;
        min-width: 0;
        text-align: right;
        font-size: .86rem;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }

    /* Medan yang belum terisi: tetap terbaca, tetapi tidak ikut menarik mata. */
    .sr-rincian dd.is-kosong { font-weight: 500; color: #94a3b8; }
    .sr-rincian dd.sr-kode { font-variant-numeric: tabular-nums; letter-spacing: .01em; }
    .sr-rincian-keping { display: inline-flex; flex-wrap: wrap; gap: .25rem; justify-content: flex-end; max-width: 100%; }

    /* Satu lajur di layar sempit: di sana dua lajur membuat nilainya
       tertekan jadi beberapa baris. */
    @media (max-width: 767.98px) {
        .sr-rincian { grid-template-columns: minmax(0, 1fr); }
        .sr-rincian > div:nth-last-child(-n+2) { border-bottom: 1px dashed #e6ebf3; padding-bottom: .5rem; }
        .sr-rincian > div:last-child { border-bottom: 0; padding-bottom: 0; }
    }

    .sr-ringkasan {
        margin-top: .95rem;
        padding: .7rem .85rem .75rem;
        border: 1px solid #eef2f7;
        border-left: 3px solid #cbd5e1;
        border-radius: .55rem;
        background: #f8fafc;
    }

    .sr-ringkasan p {
        margin: .2rem 0 0;
        font-size: .86rem;
        line-height: 1.55;
        color: #334155;
        white-space: pre-line;
        word-break: break-word;
    }

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

    /* ---------- Ubin fakta cepat (halaman surat) ---------- */
    .sr-fakta { display: flex; align-items: flex-start; gap: .6rem; }
    .sr-fakta-teks { display: flex; flex-direction: column; min-width: 0; line-height: 1.3; }
    .sr-fakta-teks b { font-size: .95rem; color: #1f2937; overflow-wrap: anywhere; }
    .sr-fakta-teks .dsb-kartu-sub { font-size: .7rem; }

    /* ---------- Kartu pindaian ----------
       Kotak unggah di kiri, daftar berkasnya di kanan. Bertumpuk, kartu ini
       memanjang ke bawah persis seperti waktu ia masih menumpang di kartu
       kanan — yang berpindah cuma letak lubangnya. */
    /* Kotak unggah selebar penuh di atas, daftar berkasnya DUA LAJUR di
       bawahnya. Berdampingan, kotak unggah yang pendek menyisakan lubang
       sepanjang daftar; bertumpuk satu lajur, daftar lima berkas membuat
       kartunya memanjang tanpa perlu. */
    .sr-pindaian-unggah { margin-bottom: .9rem; }

    /*
       FLEX, bukan kisi.

       Kisi menyisakan sel kosong di baris terakhir setiap kali jumlah
       berkasnya bukan kelipatan lajur — lima berkas di tiga lajur
       meninggalkan satu lubang selebar sepertiga kartu. Dengan flex, baris
       terakhir MELAR memenuhi lebarnya, jadi berapa pun jumlah berkasnya
       tidak pernah ada sel yang menganga.
    */
    .sr-pindaian-daftar {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem .8rem;
        align-content: start;
    }

    .sr-pindaian-daftar > * { flex: 1 1 22rem; min-width: 0; }

    .sr-pindaian-daftar .sr-lampiran-baris {
        border: 1px solid #eef2f7;
        border-radius: 11px;
        background: #fcfdff;
    }

    .sr-pindaian-daftar .dsb-kosong { flex: 1 1 100%; padding: 1.4rem 1rem; }

    /* Keadaan kosong yang RINGKAS: di bawah borang yang sudah tinggi,
       kotak kosong setinggi 200px terbaca seperti bagian yang gagal dimuat. */
    .sr-kosong-ringkas { padding: 1.1rem 1rem; min-height: 0; }
    .sr-kosong-ringkas .dsb-kosong-ikon { width: 38px; height: 38px; font-size: 1rem; margin-bottom: .5rem; }
    .sr-kosong-ringkas .dsb-kosong-judul { font-size: .9rem; }

    /* ---------- Jendela pratinjau pindaian ---------- */
    .sr-pratinjau-judul { font-size: 1rem !important; font-weight: 700 !important; color: #0f172a; word-break: break-word; }

    .sr-pratinjau-bingkai {
        width: 100%;
        height: min(70vh, 660px);
        min-height: 300px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .sr-pratinjau-gambar {
        max-width: 100%;
        max-height: min(70vh, 660px);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
    }

    .sr-pratinjau-pesan { padding: 1.2rem .5rem .4rem; color: #475569; font-size: .9rem; line-height: 1.55; }
    .sr-pratinjau-pesan i { display: block; margin-bottom: .55rem; font-size: 2rem; color: #94a3b8; }
    .sr-pratinjau-pesan p { margin: 0 0 .35rem; }
    .sr-pratinjau-pesan b { color: #0f172a; }

    /* ---------- Kaitan antar surat & riwayat ---------- */
    /* Tombol ekspor duduk di kepala kartu daftar, bukan di kepala halaman:
       di kepala halaman ia akan berdesakan dengan tombol catat surat, dan
       yang diekspor memang isi tabel ini, bukan isi halaman. */
    .sr-kepala-aksi { display: flex; align-items: center; gap: .4rem; flex-wrap: wrap; justify-content: flex-end; }

    /* ---------- Penyusutan arsip ---------- */
    .sr-musnah-baris, .sr-ba-baris {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .9rem;
        border-top: 1px solid #f1f5f9;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
    }

    .sr-musnah-baris:hover, .sr-ba-baris:hover { background: #fdf9f0; }
    .sr-musnah-baris input { width: 16px; height: 16px; margin: 0; accent-color: #a16207; flex: 0 0 auto; }
    .sr-ba-baris i { color: #b91c1c; font-size: 1rem; flex: 0 0 auto; }

    .sr-musnah-teks { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; line-height: 1.3; }
    .sr-musnah-teks b { font-size: .84rem; }
    .sr-musnah-teks span { font-size: .74rem; color: #64748b; }

    /* Tanggalnya rata kanan dan tidak pernah dipenggal: inilah angka yang
       membuat seseorang berani mencentang barisnya. */
    .sr-musnah-tgl { flex: 0 0 auto; text-align: right; font-size: .7rem; color: #64748b; white-space: nowrap; line-height: 1.35; }
    .sr-musnah-tgl b { font-size: .78rem; color: #7c3aed; }

    .sr-kait { margin-top: .85rem; display: flex; flex-direction: column; gap: .3rem; }

    .sr-kait-baris {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .4rem .6rem;
        border: 1px solid #e8eef5;
        border-radius: 9px;
        background: #f8fbff;
        color: #334155;
        text-decoration: none;
        font-size: .82rem;
        line-height: 1.3;
    }

    .sr-kait-baris:hover { border-color: #bfdbfe; background: #eff6ff; color: #1d4ed8; }
    .sr-kait-baris i { color: #0284c7; flex: 0 0 auto; }
    .sr-kait-baris b { font-variant-numeric: tabular-nums; }

    .sr-jejak-baris {
        display: flex;
        align-items: flex-start;
        gap: .6rem;
        padding: .55rem .9rem;
        border-top: 1px solid #f1f5f9;
    }

    .sr-jejak-teks { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; line-height: 1.35; }
    .sr-jejak-teks b { font-size: .83rem; }
    .sr-jejak-teks span { font-size: .78rem; color: #475569; overflow-wrap: anywhere; }

    /* Waktu & pelakunya rata kanan dan tidak pernah dipenggal: inilah dua
       keterangan yang membuat riwayat bisa dipakai sebagai bukti. */
    .sr-jejak-waktu {
        flex: 0 0 auto;
        text-align: right;
        font-size: .72rem;
        color: #64748b;
        white-space: nowrap;
        line-height: 1.35;
    }

    .sr-jejak-waktu span { color: #94a3b8; }

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

        /* Tiga tombol kepala TIDAK boleh berbagi satu baris di ponsel.
           Dulu kata "Masuk"/"Keluar" disembunyikan supaya muat — dan dua
           tombol itu jadi sama-sama berbunyi "Surat", sementara "Dashboard"
           terpenggal. Yang satu mencatat surat yang kita TERIMA dan yang
           satunya yang kita KIRIM; tertukar berarti salah masuk buku agenda.
           Jadi yang mengalah barisnya, bukan katanya. */
        /* Berlaku untuk SEMUA kepala layar arsip surat, bukan cuma daftar:
           halaman detail punya lima tombol dan akan terjepit dengan cara
           yang sama persis. Gaya ini hanya ikut ke layar yang memuat
           partial ini, jadi layar lemon lain tidak tersentuh. */
        .dsb-hero-aksi { flex-wrap: wrap; }
        .dsb-hero-aksi .dsb-tombol { flex: 1 1 calc(50% - 6px); min-width: 0; }
        .dsb-hero-aksi .dsb-tombol:first-child { flex: 1 1 100%; }

        /* Riwayat di ponsel: waktu & pelakunya turun ke bawah, rata kiri.
           Dipaksa tetap di kanan, keterangannya tinggal selebar dua kata. */
        .sr-jejak-baris { flex-wrap: wrap; }
        .sr-jejak-waktu { flex: 1 1 100%; text-align: left; padding-left: 2.1rem; white-space: normal; }

        /* Satu lajur di ponsel: dua lajur selebar 170px membuat kotak
           tanggal dan kotak pilih terpotong isinya. */
        .sr-borang-duo { grid-template-columns: minmax(0, 1fr); }

        /* Satu lajur di ponsel: kotak unggah dan daftarnya berdampingan
           selebar 170px membuat nama berkas terpenggal tiap dua huruf. */
        .sr-pindaian-daftar > * { flex: 1 1 100%; }

        .sr-musnah-baris { flex-wrap: wrap; }
        .sr-musnah-tgl { flex: 1 1 100%; text-align: left; padding-left: 2.2rem; }
    }
</style>
@endonce
