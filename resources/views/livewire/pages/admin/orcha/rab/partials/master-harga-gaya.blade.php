{{-- Gaya layar master harga — dipakai daftarnya maupun halaman borangnya.

     Ditulis telanjang, bukan dititipkan ke tumpukan: templateindex tidak
     menyediakan satu pun tempat tumpuk, jadi yang dititipkan ke sana hilang
     tanpa galat dan halamannya tetap 200.

     Satu berkas untuk dua layar, bukan dua salinan: kelas yang sama dengan
     nilai yang berbeda adalah selisih yang baru terlihat saat keduanya
     dibuka berdampingan. --}}
    <style>
        /* Saringan: satu baris di layar lebar, melipat sendiri saat sempit. */
        .mh-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        /* DUA baris yang disengaja, bukan satu baris yang kebetulan pecah.

           Layar ini punya empat kotak saring — satu lebih banyak daripada
           Vendor — dan dipaksakan sebaris, labelnya terpotong jadi "Semua
           kategor" dan "Dipakai & belun". Jadi kotak cari mengambil
           barisnya sendiri, dan kotak saring beserta tombolnya membagi
           habis baris kedua. */
        .mh-saring > .mh-medan-cari { flex: 1 1 100%; min-width: 0; }
        .mh-saring > .mh-pilih { flex: 1 1 150px; width: auto; min-width: 0; max-width: none; }
        .mh-saring .dsb-tombol { flex: 0 0 auto; }

        /* Baris utama: cari MELAR, tidak mengambil satu baris penuh.

           flex-basis 100% di atas ditulis saat baris ini masih berisi empat
           kotak saring — dipaksakan sebaris, label selectnya terpotong. Sejak
           keempatnya pindah ke balik "Saringan lainnya", yang tersisa cuma
           cari dan tiga tombol: masih mengambil baris penuh, kotak carinya
           menyisakan separuh baris di bawahnya menganggur di kanan.

           Basisnya 320px, bukan 0: di bawah itu kotak cari jadi lebih sempit
           daripada teks petunjuknya sendiri, dan tombolnya yang harus turun. */
        .mh-saring-utama > .mh-medan-cari { flex: 1 1 200px; }

        /* Tombolnya dirapatkan di laptop kecil, bukan dibiarkan pecah baris.

           Kartunya cuma 756px pada layar 1200px (bilah samping masih
           terbuka), sementara kotak cari + tiga tombol butuh 818px — satu
           tombol turun sendirian dan meninggalkan 560px menganggur di
           kanannya. */
        @media (max-width: 1299.98px) {
            .mh-saring-utama > .dsb-tombol { padding-left: 12px; padding-right: 12px; gap: 6px; }
        }

        /* Kotak cari bersama memakai .form-control Bootstrap; yang disamakan
           rupanya saja — markup & perilakunya tetap milik partial. */
        .mh-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; box-shadow: none;
        }
        .mh-saring .form-control:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .mh-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        /* Isian yang melar, bukan kolom tetap.

           Jumlah kotak yang terlihat berubah-ubah — destinasi hanya untuk
           tiket, kapasitas hanya untuk satuan yang menghitung unit. Pada
           grid berkolom tetap, tiap kotak yang disembunyikan meninggalkan
           lubang; dengan flex-basis, kotak di baris terakhir melebar
           membagi habis lebarnya. */
        /* Formulir dibagi BAGIAN BERJUDUL, pola yang sama dengan borang
           Tambah Pesanan: ikon berwarna, judul, lalu satu kalimat biasa yang
           menjelaskan isinya. Dua belas kotak tanpa judul terbaca sebagai
           satu dinding — matanya tidak punya tempat berhenti, dan yang
           membacanya tidak tahu kotak mana yang boleh dilewati. */
        .mh-bagian {
            display: block; padding: 16px clamp(14px, 2vw, 18px) 18px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 16px;
            background: #fdfdff;
        }
        .mh-bagian + .mh-bagian { margin-top: 14px; }
        .mh-bagian-kepala { display: flex; align-items: flex-start; gap: 11px; margin-bottom: 14px; }
        .mh-bagian-ikon {
            --c: var(--dsb-utama);
            flex: 0 0 auto; width: 38px; height: 38px; border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1rem; color: #fff; background: var(--c);
            box-shadow: 0 6px 14px -6px color-mix(in srgb, var(--c) 70%, transparent);
        }
        .mh-bagian-judul { margin: 0; font-size: .92rem; font-weight: 800; color: var(--dsb-tinta, #1c1f26); }
        .mh-bagian-ket { display: block; font-size: .78rem; line-height: 1.45; color: var(--dsb-redup, #6b7280); }

        .mh-medan-rak { display: flex; flex-wrap: wrap; gap: 14px 16px; align-items: flex-start; }
        .mh-medan-rak > .dsb-medan { flex: 1 1 215px; min-width: 0; }
        /* Label setinggi isinya, TANPA min-height.
           Mematoknya dua baris memang menyejajarkan kotak saat ada
           keterangan yang melipat — tetapi harganya jarak menganggur di
           bawah SETIAP label satu baris, dan itu yang pertama terlihat.
           Jadi keterangannya yang dijaga tetap pendek (lihat .dsb-label-ket
           di bawah), bukan tingginya yang dipaksa. */
        .mh-medan-rak > .dsb-medan > .dsb-label { align-items: baseline; }
        /* Keterangan tidak boleh melipat: begitu ia dua baris, kotak di
           bawahnya turun sendirian sementara tetangganya diam. Kalau
           kepanjangan ia dipotong dengan elipsis, dan kalimat utuhnya
           tetap terbaca lewat sorotan. */
        .mh-medan-rak .dsb-label-ket {
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;
        }
        .mh-medan-rak > .dsb-medan.is-lebar { flex-basis: 100%; }
        .mh-medan-rak .dsb-kartu-sub,
        .mh-medan-rak .dsb-galat { display: block; margin-top: 5px; }
        .mh-medan-rak .dsb-label-ket { margin-left: 4px; font-weight: 500; }
        .mh-medan-rak + .mh-medan-rak { margin-top: 14px; }

        /* "Rp" di dalam kotak harga.

           Aturan bersamanya menyasar .form-control, sedangkan isian di sini
           memakai .dsb-isian — tanpa padding kirinya, "Rp" duduk tepat di
           atas angka pertama dan yang terbaca jadi "Rp0.000". */
        .mh-medan-rak .orcha-rupiah .dsb-isian { padding-left: 2.4rem; font-weight: 600; }

        /* Kotak muatan: angkanya kecil di kiri, pilihan bernamanya di kanan.

           Angka telanjang "14" tidak mengatakan apa-apa; "Hiace · 14" sekaligus
           menjawab pertanyaan yang sebenarnya ada di kepala — ini muatan satu
           kendaraan, bukan jumlah kendaraannya. */
        .mh-muat { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .mh-muat-angka { flex: 0 0 104px; width: 104px; }

        /* Flex yang melar, bukan deret yang berhenti di tengah jalan.

           Dengan basis sama dan grow sama, lima chip di satu baris jadi
           sama lebar — dan yang penting, chip terakhir di baris manapun
           ikut melar menutup sisanya. Grid auto-fit tidak bisa begitu:
           di layar sempit, chip kelima duduk sendirian di kolom pertama
           dan kolom kedua menganggur. */
        .mh-muat-pilih {
            display: flex; flex-wrap: wrap; gap: 6px;
            flex: 1 1 260px; min-width: 0;
        }
        .mh-muat-pilih > .mh-muat-chip { flex: 1 1 118px; }
        .mh-muat-chip {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 7px 10px; border-radius: 10px; cursor: pointer; min-width: 0;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .79rem; font-weight: 600;
            white-space: nowrap;
        }
        .mh-muat-chip:hover { border-color: #c7d2fe; }
        .mh-muat-chip.is-aktif { border-color: #4f46e5; background: #f6f5ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .1); }
        .mh-muat-kursi {
            padding: 1px 6px 2px; border-radius: 6px;
            background: #f1f5f9; color: var(--dsb-redup, #6b7280); font-size: .7rem; font-weight: 700;
        }
        .mh-muat-chip.is-aktif .mh-muat-kursi { background: #e0e7ff; color: #4338ca; }

        /* Dua harga: saklar dulu, kotaknya menyusul.

           Dua kotak berdampingan dengan label penuh ("Hari kerja" /
           "Akhir pekan & hari libur") supaya tidak ada yang perlu diingat —
           satu kotak berlabel "Harga 2" adalah angka yang suatu hari diisi
           di kolom yang salah. Hari kerjanya hanya DITAMPILKAN di sini,
           bukan kotak kedua yang bisa berbeda dari kotak Harga di atas. */
        .mh-dua-isi {
            display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;
        }
        .mh-dua-kotak {
            flex: 1 1 190px; min-width: 0;
            display: flex; flex-direction: column; gap: 5px;
            padding: 11px 12px 12px; border-radius: 12px;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        /* Yang bisa diketik dibedakan dari yang cuma ditampilkan — kalau
           tidak, kotak hari kerja terbaca seperti kotak yang macet. */
        .mh-dua-kotak:last-child { background: #fffbeb; border-color: #fde68a; }
        .mh-dua-angka {
            font-size: .95rem; font-weight: 700; font-variant-numeric: tabular-nums;
            color: var(--dsb-tinta, #1c1f26);
        }
        .mh-dua-ket { display: block; margin-top: 8px; }

        /* Tarif musim: satu kotak per musim, warna berbeda dari kotak akhir
           pekan. Keduanya kotak harga bersyarat yang bersebelahan, dan yang
           berwarna sama akan tertukar — lalu tarif Nataru terketik di kotak
           Sabtu, yang berlaku sepanjang tahun. */
        .mh-sakelar-musim { margin-top: 12px; }
        .mh-musim-rak { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .mh-musim-kotak {
            flex: 1 1 190px; min-width: 0;
            display: flex; flex-direction: column; gap: 5px;
            padding: 11px 12px 12px; border-radius: 12px;
            background: #f5f3ff; border: 1px solid #ddd6fe;
        }

        /* Harga akhir pekan di daftar: pil, bukan angka kedua yang telanjang.
           Dua angka bertumpuk tanpa penanda terbaca sebagai "harga lama &
           harga baru", dan yang dipakai orang selalu yang atas. */
        .mh-jejak-pekan {
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
            padding: 0 7px 1px; border-radius: 999px;
        }
        .mh-akhir-pekan {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 1px 7px 2px; border-radius: 999px; margin-top: 2px;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
            font-size: .7rem; font-weight: 700; white-space: nowrap;
            font-variant-numeric: tabular-nums; cursor: help;
        }
        .mh-dua-ket a { font-weight: 600; }

        /* Destinasi baru: satu blok selebar rak, bukan kotak terjepit.

           Judul & keterangannya selebar blok (flex-basis 100%), isiannya
           melar mengisi sisa baris, dua tombolnya menempel di kanan — jadi
           barisnya habis terpakai dan tidak ada tombol yang jatuh sendirian. */
        .mh-baru {
            flex: 1 1 100%;
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            padding: 12px 14px; border-radius: 13px;
            background: #f0fdfa; border: 1px solid #99f6e4;
        }
        .mh-baru-judul {
            flex: 1 1 100%;
            display: flex; align-items: center; gap: 7px;
            font-size: .82rem; font-weight: 700; color: #0f766e;
        }
        .mh-baru .dsb-isian { flex: 1 1 240px; min-width: 0; background: #fff; }
        .mh-baru .dsb-tombol { flex: 0 0 auto; background: #fff; }
        .mh-baru-kaki { flex: 1 1 100%; margin-top: 0 !important; }
        /* Vendor baru memakai kotak yang sama dengan destinasi baru, warnanya
           saja yang mengikuti warna vendor di seluruh layar. */
        .mh-baru.is-vendor { background: #f5fafd; border-color: #cfe6f3; }
        .mh-baru.is-vendor .mh-baru-judul { color: #0369a1; }
        /* Tiket terusan: daftar tempat lain yang tercakup. */
        /* SELEBAR RAK, bukan terjepit di dalam medan Destinasi.
           Di seperempat lebar kartu, kotaknya berdiri sendirian sementara
           tiga kolom di sebelahnya — Vendor, Provinsi, Daerah — menganggur
           satu baris penuh. Isinya pun mendatar supaya lebar itu terpakai,
           bukan menumpuk ke bawah di kolom yang sempit. */
        .mh-terusan {
            flex: 1 1 100%; padding: 10px 12px; border-radius: 12px;
            background: #f8fafc; border: 1px solid var(--dsb-tepi, #e9edf3);
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px;
        }
        .mh-terusan-petunjuk {
            display: block; margin-top: 7px; font-size: .76rem; line-height: 1.45;
            color: var(--dsb-redup, #6b7280);
        }
        .mh-terusan-judul { flex: 0 0 auto; font-size: .78rem; font-weight: 600; color: var(--dsb-tinta, #1c1f26); line-height: 1.35; }
        .mh-terusan-judul .dsb-label-ket { display: block; font-weight: 400; }
        .mh-terusan-tambah { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; flex: 1 1 240px; }
        /* Anak lenturnya kini PEMBUNGKUS pemilih, bukan kotak pilihnya
           sendiri: aturan lama memilih .dsb-isian yang sudah tidak ada di
           baris ini, jadi tombolnya menciut selebar tulisannya. */
        .mh-terusan-tambah > .op-pilih { flex: 1 1 180px; min-width: 0; }
        /* Setinggi kotak pilih di sebelahnya (.dsb-isian, min-height 40px),
           bukan 44px bawaan .dsb-tombol: sepasang kotak yang berbeda dua
           piksel di satu baris terbaca sebagai salah susun, bukan sebagai
           rancangan. */
        .mh-terusan-tambah > .dsb-tombol { flex: 0 0 auto; height: 40px; padding: 0 15px; }
        .mh-cip-rak { display: flex; flex-wrap: wrap; gap: 6px; flex: 0 1 auto; }
        .mh-cip {
            display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
            padding: 3px 6px 3px 10px; border-radius: 999px; font-size: .78rem;
            background: #eef2ff; color: #3730a3; border: 1px solid #c7d2fe;
        }
        .mh-cip button { border: 0; background: transparent; color: inherit; line-height: 1; padding: 0 2px; }
        .mh-tambah {
            display: inline-flex; align-items: center; gap: 5px; margin-top: 6px;
            padding: 0; border: 0; background: none;
            font-size: .76rem; font-weight: 600; color: #0f766e;
        }
        .mh-tambah:hover { text-decoration: underline; }

        /* Baris "terisi sendiri", sepasang dengan .rb-rakitan di daftar RAB. */
        .mh-rakitan {
            display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
            margin-top: 14px; padding: 11px 14px;
            background: #f6fbf8; border: 1px solid #d1fae5; border-radius: 13px;
        }
        .mh-rakitan-teks { display: flex; flex-direction: column; min-width: 0; flex: 1 1 240px; }
        .mh-rakitan-judul { font-size: .9rem; color: var(--dsb-tinta, #1c1f26); }
        /* Tombolnya tidak ikut melar mengisi sisa baris, dan di layar sempit
           turun selebar kartu — bukan terjepit di sisa ruang kalimat. */
        .mh-rakitan-pakai { flex: 0 0 auto; height: 38px; padding: 0 14px; }
        @media (max-width: 575.98px) {
            .mh-rakitan-pakai { flex: 1 1 100%; justify-content: center; }
        }

        /* Peringatan yang menyebut akibatnya, bukan cuma "tidak valid". */
        .mh-peringatan {
            display: flex; align-items: flex-start; gap: 8px;
            margin-top: 12px; padding: 11px 14px;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 13px;
            color: #92400e; font-size: .8rem; line-height: 1.5;
        }
        .mh-peringatan i { margin-top: 2px; flex-shrink: 0; }

        .mh-sakelar { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 14px; }
        .mh-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }

        /* Sel bertumpuk: judul tegas di atas, keterangannya di bawah. */
        .mh-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .mh-utama.is-kanan { align-items: flex-end; }
        .mh-catatan { font-style: italic; }
        .mh-status { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }

        .mh-vendor-kontak {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 5px;
            font-size: .79rem; font-weight: 600; color: #0369a1;
        }
        .mh-vendor-kosong {
            display: flex; align-items: flex-start; gap: 6px; margin-top: 5px;
            font-size: .75rem; line-height: 1.45; color: #92400e;
        }
        .mh-vendor-tautan { display: inline-flex; align-items: center; gap: 5px; color: #0f766e; font-weight: 600; text-decoration: none; }
        .mh-vendor-tautan:hover { text-decoration: underline; }
        .mh-vendor-baris { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .mh-vendor-baris:hover { color: #0369a1; text-decoration: underline; }

        .mh-pasang {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            padding: 12px 14px; border-radius: 13px;
            background: #f6fbf8; border: 1px solid #d1fae5;
        }
        .mh-pasang-judul {
            flex: 1 1 100%; display: flex; align-items: center; gap: 7px;
            font-size: .84rem; font-weight: 700; color: #047857;
        }
        .mh-pasang .dsb-isian { background: #fff; }
        .mh-pasang .dsb-tombol { flex: 0 0 auto; }
        .mh-pasang-kaki { flex: 1 1 100%; margin-top: 0 !important; }
        /* Judulnya mengambil barisnya sendiri: ia keterangan, bukan kendali.
           Yang bisa ditekan semuanya berkumpul di baris perintah di bawahnya. */
        .mh-pasang-judul { flex: 1 1 100%; }

        /* Satu baris perintah: verba, pelengkapnya, lalu Jalankan.
           Pelengkapnya melar mengisi sisa lebar — baris yang separuhnya
           kosong terbaca seperti ada yang belum selesai dipasang. */
        .mh-perintah { flex: 1 1 100%; display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; }
        .mh-perintah > .mh-perintah-aksi { flex: 0 1 215px; }
        /* Pelengkapnya selebar yang memang dibutuhkan — kotak persen yang
           melar sepanjang baris terbaca seperti kotak yang salah. Sisa
           lebarnya diisi keterangan, bukan dibiarkan kosong. */
        .mh-perintah > .mh-perintah-isi { flex: 0 1 260px; min-width: 0; }
        .mh-perintah > .mh-perintah-isi.mh-persen { flex: 0 0 125px; }
        .mh-perintah > .mh-perintah-ket {
            flex: 1 1 220px; min-width: 0;
            font-size: .79rem; line-height: 1.45; color: var(--dsb-redup, #6b7280);
        }
        /* Dua tombol sebagai satu blok: ia tidak pernah pecah ke dua baris
           berbeda, dan jaraknya ke kotak perintah tetap sama berapa pun
           panjang keterangannya. */
        .mh-perintah > .mh-perintah-tombol { flex: 0 0 auto; display: flex; align-items: center; gap: 8px; }
        .mh-perintah .mh-perintah-isi .dsb-isian { width: 100%; }

        .mh-centang { width: 34px; }
        .mh-centang .form-check-input { margin: 0; }
        .mh-utama.is-tengah { align-items: center; }

        /* Delapan kolom di layar laptop membuat beberapa sel pendek pecah di
           tempat yang salah — "DI / Yogyakarta", "2 bulan / yang lalu". Yang
           dipagari hanya potongan yang panjangnya memang terbatas; nama
           harga, daerah, dan nama vendor tetap boleh turun baris. */
        .mh-wilayah, .mh-diubah, .mh-satuan { white-space: nowrap; }
        .mh-vendor-sel { text-decoration: none; color: var(--dsb-tinta, #1c1f26); }
        /* Nama vendor DIPOTONG di dalam selnya, bukan dibiarkan meluber.

           Sudah dipotong 26 huruf saat dirender, tetapi di laptop kecil
           kolomnya ikut menyusut dan 26 huruf pun tidak muat — hurufnya lalu
           menindih kolom harga tanpa satu pun tanda bahwa ada yang terpotong.
           Nama utuhnya tetap terbaca lewat sorotan. */
        .mh-vendor-sel > span,
        .mh-tabel .dsb-tabel-meta {
            display: block; max-width: 100%;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        /* Kecuali baris lencana: isinya memang beberapa benda yang melipat. */
        .mh-tabel .dsb-tabel-teks > .dsb-tabel-meta {
            display: flex; flex-wrap: wrap; white-space: normal; overflow: visible;
        }
        .mh-vendor-sel:hover { color: #0369a1; text-decoration: underline; }
        .mh-tanpa-vendor {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .78rem; font-weight: 600; color: #b45309;
        }
        /* Kelompok aksi tidak pecah DI DALAM dirinya: kalau kehabisan tempat,
           seluruh kelompoknya yang turun — "Perubahan terakhir" di satu baris
           dan "PDF" sendirian di bawahnya terbaca seperti dua hal yang tidak
           berhubungan. Judul kartunya yang mengalah lebih dulu. */
        .mh-kepala-aksi { display: flex; align-items: center; flex-wrap: nowrap; gap: 10px; }
        /* nowrap benar saat ada tempat, dan meluber saat tidak. Di ponsel
           kelompoknya boleh melipat ke dalam dirinya — satu-satunya pilihan
           lain adalah keluar layar. */
        @media (max-width: 575.98px) {
            .mh-kepala-aksi { flex-wrap: wrap; }
            .mh-kepala-aksi .dsb-tombol { flex: 1 1 auto; justify-content: center; }
        }
        .mh-kepala-aksi .dsb-tombol { flex: 0 0 auto; }
        /* Kepala kartunya boleh melipat: di layar sempit kelompok aksi turun
           utuh ke bawah judulnya. */
        .mh-daftar-kepala { flex-wrap: wrap; }
        .mh-daftar-kepala > .dsb-kartu-kepala-kiri { flex: 1 1 320px; }

        .mh-dipakai { font-weight: 700; font-size: .86rem; color: var(--dsb-utama, #4f46e5); }
        /* NAMA SENDIRI, bukan .mh-tambah — kelas itu sudah dipakai tombol
           kecil "Destinasinya belum ada di daftar", yang justru membuang
           padding dan latarnya. Dipakai ulang di sini, tombol besarnya
           kehilangan padding dan tulisannya menempel ke tepi. */
        .mh-tambah-harga { margin-left: auto; }

        /* ===== Ringkasan ===== */
        /* Kartu status "Semua harga" tidak bisa diklik — ia bukan saringan,
           cuma jumlah seluruhnya. Dibuat tidak terlihat bisa ditekan, supaya
           tidak ada yang menunggu sesuatu terjadi. */
        /* Label BOLEH melipat di layar ini.

           Bawaannya nowrap, dan itu ukuran kata bahasa Inggris: "Completed"
           muat, "Masa berlakunya lewat" tidak — ia terpotong di tengah kata
           tanpa ada yang terlihat salah. Hanya di sini, supaya Pesanan Toko
           yang labelnya memang pendek tidak ikut berubah. */
        .mh-kartu-status .pt-kartu-label { white-space: normal; }
        .mh-kartu-status .pt-kartu-tab { min-height: 118px; }

        .mh-kartu-diam { cursor: default; opacity: 1; }
        .mh-kartu-diam .pt-kartu-ikon { background: #f1f5f9; color: #94a3b8; }

        /* Saringan yang dilipat. Pola yang sama dengan Pesanan Toko: yang
           sering dipakai tetap terlihat, sisanya di balik satu tombol. */
        .mh-lanjutan { margin-top: 12px; }
        .mh-lanjutan-panah { transition: transform .15s ease; }
        .mh-lanjutan-panah.is-buka { transform: rotate(180deg); }
        .mh-saring-lanjut {
            margin-top: 12px; padding-top: 12px;
            border-top: 1px dashed var(--dsb-tepi, #e9edf3);
        }

        /* ===== Pita kategori ===== */
        .mh-tabel tbody tr.mh-pita td {
            padding: 7px 14px; background: #f8fafc; border-bottom: 1px solid #eef2f7;
            border-left: 3px solid var(--c); font-size: .76rem; font-weight: 800;
            letter-spacing: .03em; text-transform: uppercase; color: var(--c);
        }
        .mh-tabel tbody tr.mh-pita td i { margin-right: 6px; }
        .mh-tabel tbody tr.mh-pita:hover td { background: #f8fafc; }

        .mh-persen { position: relative; }
        .mh-persen .dsb-isian { background: #fff; padding-right: 30px; }
        .mh-persen-tanda {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            font-size: .82rem; font-weight: 700; color: var(--dsb-redup, #6b7280); pointer-events: none;
        }

        /* ===== Riwayat ===== */
        /* Daftar perubahan dalam satu sel: medan di kiri dengan lebar tetap,
           nilainya di kanan. Lebar tetap itu yang membuat tiga baris
           perubahan terbaca sebagai daftar, bukan sebagai tiga kalimat yang
           kebetulan bertumpuk. */
        .mh-ubah { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
        .mh-ubah-baris {
            display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 10px;
            font-size: .8rem; line-height: 1.4; min-width: 0;
        }
        .mh-ubah-medan {
            flex: 0 0 118px; min-width: 0;
            font-size: .72rem; font-weight: 800; letter-spacing: .02em;
            text-transform: uppercase; color: var(--dsb-redup, #6b7280);
        }
        .mh-ubah-nilai {
            display: inline-flex; align-items: baseline; flex-wrap: wrap; gap: 6px;
            min-width: 0; color: var(--dsb-tinta, #1c1f26);
        }
        .mh-ubah-nilai .bi { font-size: .72rem; color: #94a3b8; }

        @media (max-width: 575.98px) {
            .mh-ubah-medan { flex: 1 1 100%; }
        }

        .mh-riwayat-lama { color: var(--dsb-redup, #6b7280); text-decoration: line-through; }
        .mh-selisih { display: inline-flex; align-items: center; gap: 4px; font-weight: 800; font-size: .86rem; }
        .mh-selisih i { font-size: .78rem; }
        /* Naik merah, turun hijau — dari sudut pandang yang membayar, bukan
           yang menjual. Harga beli yang naik adalah kabar buruk. */
        .mh-selisih.is-naik { color: var(--dsb-bahaya, #be123c); }
        .mh-selisih.is-turun { color: var(--dsb-aman, #1f7a44); }

        /* ===== Kepala tabel yang lengket =====

           position:sticky menempel pada KOTAK GULIR TERDEKAT, dan pembungkus
           tabel sudah punya overflow-x:auto — yang diam-diam membuat
           overflow-y ikut auto. Jadi kepalanya melekat pada pembungkus itu,
           yang tidak pernah bergulir tegak, dan aturannya tidak melakukan
           apa pun. Tidak ada galat; kepalanya hanya ikut hilang ke atas
           seperti sebelumnya.

           Maka pembungkusnya yang dijadikan kotak gulirnya sendiri — tetapi
           HANYA saat barisnya memang banyak. Daftar lima baris di dalam
           kotak setinggi 70vh meninggalkan ruang kosong di bawahnya. */
        .mh-tabel-tinggi { max-height: 70vh; overflow-y: auto; }
        .mh-tabel-tinggi thead th { position: sticky; top: 0; z-index: 3; }
        /* Lebar kolom DIPATOK.

           Dibiarkan otomatis, lebarnya dibagi menurut isi terpanjang: satu
           nama vendor berkata tujuh membuat kolomnya melar dan kolom nama —
           yang justru paling banyak isinya — menyusut sampai lencananya
           melipat tiga baris. Persentase, jadi tetap luwes di lebar mana pun. */
        /* table-layout: fixed, bukan sekadar menulis width.

           Dengan layout auto, width cuma SARAN: peramban membaginya menurut
           isi terpanjang, dan yang menang kolom yang isinya tidak bisa
           melipat — pil "Rp 31.000 akhir pekan" dan empat tombol aksi.
           Kolom nama, yang isinya justru paling banyak, menyerahkan lebarnya
           karena lencananya memang bisa melipat. Diukur: 23,5% untuk nama
           sementara aksi 17,5%, padahal width-nya sudah ditulis 40%.

           Angkanya berjumlah 100% dan diukur dari isi yang sebenarnya: aksi
           butuh 154px dan harga 160px pada lebar tabel 1039px. */
        .mh-tabel { table-layout: fixed; }
        /* PIKSEL, bukan persen: kotak centang lebarnya tetap 33px berapa pun
           lebar layarnya, dan 4% dari tabel selebar 790px cuma 32px — satu
           piksel kurang, dan kotaknya meluap keluar selnya. min-width tidak
           menolong: di table-layout: fixed lebar kolom diambil dari width
           saja, dan min-width pada selnya diabaikan. */
        .mh-tabel th:nth-child(1), .mh-tabel td:nth-child(1) { width: 36px; }
        .mh-tabel th:nth-child(2), .mh-tabel td:nth-child(2) { width: 28%; }
        .mh-tabel th:nth-child(3), .mh-tabel td:nth-child(3) { width: 13%; }
        .mh-tabel th:nth-child(4), .mh-tabel td:nth-child(4) { width: 13%; }
        .mh-tabel th:nth-child(5), .mh-tabel td:nth-child(5) { width: 16%; }
        .mh-tabel th:nth-child(6), .mh-tabel td:nth-child(6) { width: 10%; }
        .mh-tabel th:nth-child(7), .mh-tabel td:nth-child(7) { width: 16%; }

        /* Nama provinsi BOLEH melipat di dalam tabel.

           nowrap-nya disetel saat kolomnya selebar isi terpanjang; dengan
           lebar yang dipatok, "Semua provinsi" butuh 111px di kolom 96px dan
           meluber keluar selnya — menindih kolom sebelahnya tanpa satu pun
           tanda bahwa ada yang salah. */
        .mh-tabel .mh-wilayah,
        /* "5 hari yang lalu" sama halnya: nowrap-nya butuh 87px di kolom 76px. */
        .mh-tabel .mh-diubah,
        /* Dan pil "Rp 31.000 akhir pekan": 160px di kolom yang menyusut jadi
           128px begitu layarnya 1000px. */
        .mh-tabel .mh-akhir-pekan { white-space: normal; }

        /* Tombol aksi dirapatkan di laptop kecil.

           Empat tombol 34px plus celahnya butuh 168px, sementara kolomnya
           menyusut jadi 145px begitu layarnya 1000px — tombol terakhir
           (hapus) meluber keluar sel dan menindih tepi kartu. Dirapatkan,
           bukan disembunyikan: tombol yang hilang di balik tepi layar tidak
           bisa ditemukan sama sekali. */
        @media (min-width: 768px) and (max-width: 1399.98px) {
            .mh-tabel .dsb-tabel-aksi { gap: 4px; }
            .mh-tabel .dsb-tabel-aksi .dsb-tabel-btn { width: 30px; height: 30px; }
        }

        .mh-tabel tbody tr.is-mati { background: #fbfcfd; }
        .mh-tabel tbody tr.is-mati .dsb-tabel-judul { color: var(--dsb-redup, #6b7280); }
        .mh-belum { font-size: .78rem; color: var(--dsb-redup, #6b7280); }

        .mh-lepas {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .76rem; font-weight: 600; color: #b45309;
        }
        .mh-lepas-ket {
            padding: 1px 7px 2px; border-radius: 7px;
            background: #fffbeb; border: 1px solid #fde68a; font-size: .68rem; font-weight: 700;
        }

        .mh-halaman {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9;
        }
        .mh-halaman > * { margin-bottom: 0 !important; margin-top: 0 !important; }

        /* ===== Penolakan kembaran ===== */
        .mh-kembar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            margin-top: 14px; padding: 13px 15px; border-radius: 13px;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        }
        .mh-kembar > i { font-size: 1.05rem; color: var(--dsb-awas, #b45309); }
        .mh-kembar-teks { flex: 1 1 260px; display: flex; flex-direction: column; gap: 2px; min-width: 0; }

        /* ===== Tambah lagi & tempel ===== */
        /* Isi kiri berkelompok, bukan tiap benda mendorong sendiri.

           Dulu margin-right:auto menempel di .mh-lanjut, jadi apa pun yang
           ditambahkan sesudahnya ikut terlempar ke kanan bersama Batal &
           Simpan — "Tempel dari Excel" berakhir menempel di tombol simpan,
           seolah bagian dari aksi yang sama. */
        .mh-kaki-kiri { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-right: auto; }

        .mh-lanjut {
            display: inline-flex; align-items: center; gap: 7px;
            font-size: .82rem; font-weight: 600; color: var(--dsb-redup, #6b7280); cursor: pointer;
        }
        .mh-tempel-bungkus { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e5e9f0; }
        .mh-tempel { display: flex; flex-direction: column; gap: 10px; }
        .mh-tempel-kepala { display: flex; flex-direction: column; gap: 3px; }
        .mh-tempel-contoh {
            padding: 1px 7px 2px; border-radius: 6px;
            background: #eef2ff; color: #4338ca; font-size: .78rem; font-weight: 600;
        }
        .mh-tempel textarea.dsb-isian {
            /* Monospace: yang ditempel berkolom, dan kolom hanya terlihat
               lurus bila tiap hurufnya selebar huruf lain. */
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .82rem; line-height: 1.6; padding: 11px 13px; height: auto;
        }
        .mh-tempel-kaki { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .mh-tempel-kaki .dsb-chip { margin-right: auto; }
        .mh-tempel-gagal {
            display: flex; flex-direction: column; gap: 2px;
            padding: 11px 13px; border-radius: 11px;
            background: #fff7ed; border: 1px solid #fed7aa;
            font-size: .78rem; color: #9a3412;
        }

        /* ===== Tawaran batalkan ===== */
        .mh-batal-pita {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            padding: 12px 15px; border-radius: 15px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
        }
        .mh-batal-pita > i { color: var(--dsb-aman, #1f7a44); font-size: 1.05rem; }
        .mh-batal-teks { flex: 1 1 220px; font-size: .86rem; font-weight: 600; color: #166534; min-width: 0; }

        /* ===== Dipakai di & perubahan terakhir ===== */
        .mh-dipakai-btn {
            background: none; border: 0; padding: 0; cursor: pointer;
            border-bottom: 1px dashed currentColor;
        }
        /* Daftarnya menggulir di dalam kartunya sendiri. Dua puluh baris
           perubahan yang mendorong tabel sampai keluar layar membuat orang
           menutup panelnya hanya untuk bisa melihat tabelnya lagi. */
        .mh-riwayat-semua { padding-top: 0; max-height: 330px; overflow-y: auto; }
        /* Judul kolom yang bisa ditekan. Rupanya tetap judul kolom — tombol
           berbingkai di kepala tabel membuat tabelnya terlihat seperti
           barisan kendali, bukan data. */
        .mh-urut {
            display: inline-flex; align-items: center; gap: 5px;
            background: none; border: 0; padding: 0; cursor: pointer;
            font: inherit; color: inherit; letter-spacing: inherit; text-transform: inherit;
        }
        .mh-urut:hover { color: var(--dsb-utama, #4f46e5); }
        .mh-urut.is-nyala { color: var(--dsb-utama, #4f46e5); }
        .mh-urut i { font-size: .7rem; }

        .mh-pilih-semua {
            margin-left: 10px; padding: 0; border: 0; background: none; cursor: pointer;
            font-size: .78rem; font-weight: 700; color: #047857;
            border-bottom: 1px dashed currentColor;
        }
        .mh-pilih-semua:hover { color: #065f46; }

        .mh-perintah-panah { flex: 0 0 auto; color: var(--dsb-redup, #6b7280); }
        .mh-periode { flex: 0 0 165px; }
        .mh-jejak-potong {
            padding-top: 0; padding-bottom: 10px;
            font-size: .79rem; color: var(--dsb-redup, #6b7280);
        }

        /* display: none DITULIS SENDIRI di sini.
           Mengandalkan aturan bawaan Livewire untuk menyembunyikannya
           membuat pitanya tampil terus saat halaman tidak sedang memuat —
           persis jebakan "wire:loading kalah bobot CSS" yang sudah pernah
           menggigit di repo ini. wire:loading.flex menyetel display lewat
           style elemen, jadi ia tetap menang saat memang memuat. */
        /*
         | .mh-pita-muat, BUKAN .mh-muat.
         |
         | "muat" (kapasitas satu kendaraan) dan "memuat" (sedang mengambil
         | data) sempat memakai nama kelas yang sama di berkas ini. Aturan
         | yang ditulis belakangan menang, jadi display:none milik pita ini
         | ikut menyembunyikan KOTAK KAPASITAS di formulir — kotaknya ada di
         | DOM, tidak terlihat, dan validasinya tetap menuntut diisi. Harga
         | bersatuan per unit jadi tidak bisa disimpan sama sekali, tanpa
         | satu pun pesan yang menyebut sebabnya.
         */
        .mh-pita-muat {
            display: none; align-items: center; gap: 9px;
            padding: 9px clamp(14px, 2vw, 20px);
            background: #f8fafc; border-bottom: 1px solid #eef2f7;
            font-size: .82rem; font-weight: 600; color: var(--dsb-redup, #6b7280);
        }

        .mh-baris-pilih {
            display: inline-flex; align-items: center; gap: 7px; margin-right: auto;
            font-size: .8rem; color: var(--dsb-redup, #6b7280);
        }
        .mh-baris-pilih .dsb-isian { width: auto; height: 36px; padding: 0 30px 0 11px; font-size: .8rem; }

        /* Peringatan yang tidak menolak: ia memberi tahu, bukan menghentikan. */
        .mh-ingat {
            display: flex; align-items: flex-start; gap: 7px; margin-top: 5px;
            font-size: .78rem; line-height: 1.45; color: #92400e;
        }
        .mh-ingat i { margin-top: 2px; }

        .mh-jejak-nihil {
            display: flex; align-items: center; gap: 8px; margin: 0;
            font-size: .84rem; color: var(--dsb-redup, #6b7280);
        }
        .mh-jejak { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; }
        .mh-jejak li {
            display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 10px;
            padding: 9px 0; border-bottom: 1px solid #f3f5f9; font-size: .84rem;
        }
        .mh-jejak li:last-child { border-bottom: 0; }
        .mh-jejak-tanggal { flex: 0 0 120px; color: var(--dsb-redup, #6b7280); font-size: .78rem; }
        .mh-jejak-nama {
            background: none; border: 0; padding: 0; cursor: pointer;
            font-weight: 700; color: var(--dsb-tinta, #1c1f26); text-align: left;
            border-bottom: 1px dashed #cbd5e1;
        }
        .mh-jejak-nama:hover { color: var(--dsb-utama, #4f46e5); }
        .mh-jejak-angka { color: var(--dsb-redup, #6b7280); }
        .mh-jejak-oleh { color: var(--dsb-redup, #6b7280); font-size: .78rem; }

        /* Keterangan label turun ke barisnya sendiri di layar sempit:
           di samping judulnya ia membelah dua-duanya jadi dua baris. */
        @media (max-width: 575.98px) {
            .mh-medan-rak .dsb-label-ket { display: block; margin-left: 0; margin-top: 2px; }
            /* Kotak angkanya ikut selebar baris: di layar sempit ia duduk
               sendirian, dan 104px menyisakan dua pertiga baris menganggur. */
            .mh-muat-angka { flex: 1 1 100%; width: auto; }

            /* Dua tombol destinasi baru berbagi satu baris; kalau tidak
               cukup, masing-masing melar selebar baris. Yang tidak boleh
               terjadi cuma satu: tombol sempit dengan baris kosong di
               kanannya. */
            .mh-baru .dsb-tombol { flex: 1 1 130px; justify-content: center; }
        }

        @media (max-width: 991.98px) {
            .mh-saring > .mh-medan-cari { flex: 1 1 100%; }
            .mh-saring > .mh-pilih { flex: 1 1 150px; max-width: none; }
            .mh-sakelar { grid-template-columns: 1fr; }
        }

        @media (max-width: 767.98px) {
            /* Tombol tambah berhenti didorong ke kanan: di layar sempit ia
               berdiri sendiri di barisnya, dan "didorong ke ujung" hanya
               menyisakan lubang di kirinya.

               Dipilih lewat .mh-saring juga, bukan .mh-tambah saja: aturan
               .mh-saring .dsb-tombol di atas berbobot lebih tinggi, dan
               tanpa ini flex-nya tidak pernah berubah. */
            .mh-saring .mh-tambah-harga { margin-left: 0; flex: 1 1 100%; justify-content: center; }

            /* Baris perintah menumpuk utuh: dua kotak yang lebarnya berbeda
               sedikit, bertumpuk, terbaca seperti salah sejajar. */
            .mh-perintah > .mh-perintah-aksi,
            .mh-perintah > .mh-perintah-isi,
            .mh-perintah > .mh-perintah-isi.mh-persen,
            .mh-perintah > .mh-perintah-tombol { flex: 1 1 100%; }
            /* Kedua tombol membagi barisnya rata; yang menjalankan diberi
               porsi lebih besar karena itu yang dituju. */
            .mh-perintah > .mh-perintah-tombol .dsb-tombol { flex: 1 1 0; justify-content: center; }
            .mh-perintah > .mh-perintah-tombol .mh-perintah-jalan { flex: 2 1 0; }
        }

        /* Penyembunyian kolom di layar sempit TIDAK lagi memakai nomor kolom.

           Dua aturan nth-child dulu menunjuk Wilayah (ke-4) dan Dipakai
           (ke-7) pada susunan delapan kolom. Begitu kolom Kategori dilebur
           ke sel nama, nomornya bergeser — ke-7 jadi kolom AKSI, justru yang
           kedua aturan itu dibuat untuk melindungi. Nomor kolom adalah
           patokan yang diam-diam berubah arti.

           Sekarang kolomnya sendiri yang menyandang kelasnya, dan salinannya
           tetap terbaca sebagai .dsb-tabel-samar di baris lencana.

           Wilayah memakai ambang 1400px, bukan .k-lebar (1200px) bawaan:
           ambang itu DIUKUR penulis aslinya, dan memang di situ tujuh kolom
           berhenti muat. Dipaksa ke 1200px, pada laptop 1280px sel namanya
           meluber 20px dan tombol hapus menindih tepi kartu. */
        @media (min-width: 768px) and (max-width: 1399.98px) {
            .mh-tabel .mh-k-wilayah { display: none; }
            /* Lebar yang ditinggalkan Wilayah diberikan ke kolom nama, bukan
               dibagi rata: di sanalah isinya paling banyak, dan tanpa ini
               baris lencananya meluber 8px keluar selnya. */
            .mh-tabel th:nth-child(2), .mh-tabel td:nth-child(2) { width: 37%; }
            /* Kolom aksi ikut dilebarkan dan paddingnya dirapatkan: empat
               tombol 30px butuh 146px, dan 16% dari tabel 800px cuma 128px —
               tombol hapus meluber menindih tepi kartu. */
            .mh-tabel th:nth-child(7), .mh-tabel td:nth-child(7) { width: 19%; }
            .mh-tabel td:nth-child(7) { padding-left: 6px; padding-right: 10px; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. Tanpa ini
           kolom terakhir — tombol ubah & hapus — tidak pernah terlihat
           tanpa digeser lebih dulu. */
        @media (max-width: 767.98px) {
            /* table-layout: fixed TERUS BERLAKU walau barisnya sudah jadi
               blok: kotak tabelnya masih ada, jadi persentase kolom di atas
               tetap mengunci lebar sel, dan nama harga meluap keluar kartunya
               tanpa satu pun tanda. Kotak tabelnya ikut dibubarkan di sini,
               dan lebar selnya dikembalikan ke isi. */
            .mh-tabel { min-width: 0; display: block; table-layout: auto; }
            .mh-tabel tbody { display: block; }
            .mh-tabel thead { display: none; }
            .mh-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            /* !important: aturan persentase di atas memakai :nth-child, dan
               bobotnya lebih tinggi daripada pemilih mana pun yang masuk akal
               di sini. */
            .mh-tabel tbody td { width: auto !important; }
            .mh-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .mh-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            /* Isi sel harus boleh menyempit; medan flex yang mempertahankan
               lebar isinya mendorong kartunya keluar layar. */
            .mh-tabel tbody td > * { min-width: 0; }
            .mh-utama, .mh-status { align-items: flex-end; text-align: right; }

            /* Pita kategori cuma satu sel tanpa data-judul, jadi aturan
               "label kiri, nilai kanan" melemparkan tulisannya ke tepi kanan
               — terbaca seperti nilai dari label yang hilang. */
            .mh-tabel tbody tr.mh-pita { padding: 0; }
            .mh-tabel tbody tr.mh-pita td {
                display: block; text-align: left !important;
                padding: 8px 15px; border-left-width: 3px;
            }
        }
    </style>
