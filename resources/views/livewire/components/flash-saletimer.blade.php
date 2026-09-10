<div @if ($flashSale && !$showDurationModal) wire:poll.1s="updateTimer" @endif id="call-to-action"
    class="{{ $flashSale ? 'call-to-action section' : '' }}">
    @include('partials.media-produk-style')
    @if ($flashSale)
    <style>
        /* ===== Etalase Flash Sale =====
           Ditulis inline: public/build masuk .gitignore, jadi markup bisa sampai
           ke server tanpa CSS-nya.

           Yang membuat pita promo terlihat murah hampir selalu sama: warna
           berteriak, lencana bertumpuk, dan gerakan yang tidak menyampaikan
           apa-apa. Yang membuatnya terasa mahal juga sama di mana-mana:
           satu bidang gelap, satu warna aksen, ruang kosong yang berani, dan
           SATU hal yang bergerak — jamnya, karena ia memang sedang berjalan.

           Bidangnya TERANG, sementara hero di atasnya gelap. Dua bidang gelap
           beruntun terasa berat dan iramanya mati; terang-gelap-terang memberi
           halaman ini napas.

           Yang menahan supaya bagian terang tidak jadi hambar: SATU benda gelap
           di dalamnya — panel jamnya. Satu benda gelap di atas bidang terang
           selalu jadi titik jatuh mata, dan di sini yang paling pantas dilihat
           lebih dulu memang jamnya. */
        #call-to-action.section { padding: 24px 0 30px; background: none; }

        /* Kepala bagian di dalam promo disamakan dengan kepala bagian lain di
           halaman; aturan lama .call-to-action mewarnainya cokelat dan
           membesarkannya. */
        #call-to-action .kb-kepala { text-align: left; }
        #call-to-action .kb-judul { color: #1c1f26; font-size: 2.1rem; text-align: left; }
        #call-to-action .kb-sub { color: #6b7280; text-align: left; }

        /* Kartu tunggal: pita kepala + kisi produk, dipisah satu garis.
           Kartunya sendiri tidak lagi memakai grid — grid dipindah ke pita
           kepalanya saja, supaya kisi produk di bawah bebas memakai empat
           kolomnya sendiri tanpa bertabrakan dengan pembagian dua kolom. */
        #call-to-action .fsx-hero {
            position: relative; overflow: hidden;
            background:
                radial-gradient(58% 80% at 92% 12%, rgba(251, 169, 25, .16) 0%, rgba(251, 169, 25, 0) 62%),
                radial-gradient(52% 70% at 4% 98%, rgba(242, 101, 34, .10) 0%, rgba(242, 101, 34, 0) 60%),
                linear-gradient(122deg, #ffffff 0%, #fffaf4 58%, #fff5ea 100%);
            border: 1px solid #f6e3d2; border-radius: 26px;
            box-shadow: 0 18px 44px rgba(180, 110, 60, .10);
        }

        /* --- Pita kepala --- */
        #call-to-action .fsx-kepala {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: minmax(0, 1fr) auto auto;
            align-items: center; gap: 18px 32px;
            /* Padding atas dilebihkan untuk memberi tempat label yang
               menggantung dari garis atas (36px) plus jaraknya. */
            padding: 58px 30px 18px;
        }
        #call-to-action .fsx-kiri { grid-column: 1; min-width: 0; }
        #call-to-action .fsx-tengah {
            grid-column: 2; display: flex; flex-direction: column; align-items: center;
            text-align: center;
            /* Dipisah garis tipis dari kolom sebelahnya: tanpa itu angka besar
               ini menempel pada judul dan terbaca sebagai bagian dari kalimat,
               bukan sebagai tawarannya. */
            padding-right: 32px; border-right: 1px solid #f2ddc9;
        }
        #call-to-action .fsx-kanan { grid-column: 3; }
        #call-to-action .fsx-kaki { grid-column: 1 / -1; }

        /* Garis jingga melintang di tepi ATAS, bukan di tepi kiri. Label
           kartunya duduk di tengah garis itu, jadi garisnya bukan sekadar
           hiasan — ia alas tempat nama kartu berdiri. */
        #call-to-action .fsx-hero::before {
            content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px;
            background: linear-gradient(90deg, #f26522, #fba919, #f26522);
        }

        #call-to-action .fsx-pita-atas {
            position: absolute; z-index: 3; top: 0; left: 50%; transform: translateX(-50%);
            display: inline-flex; align-items: center; gap: 9px;
            background: linear-gradient(135deg, #f26522, #fb8b3c);
            color: #fff; font-family: 'Poppins', sans-serif;
            /* Huruf kapital berjarak lebar. Pada label sependek ini jarak antar
               huruf yang longgar membuatnya terbaca sebagai PENANDA, bukan
               sebagai kata biasa yang kebetulan ditulis besar. */
            font-size: .72rem; font-weight: 800; letter-spacing: .24em;
            text-transform: uppercase; white-space: nowrap;
            /* Pengaman terakhir: berapa pun panjang teksnya, label tidak boleh
               melebar sampai menyaingi lebar kartunya sendiri. */
            max-width: min(80vw, 340px); overflow: hidden; text-overflow: ellipsis;
            padding: 9px 26px 9px 24px;
            /* Sudut atas lurus supaya menyatu dengan garisnya, sudut bawah
               membulat — bentuk label yang menggantung, bukan kotak yang
               kebetulan menempel. */
            border-radius: 0 0 14px 14px;
            box-shadow: 0 8px 20px rgba(242, 101, 34, .32);
        }
        #call-to-action .fsx-pita-atas .fsx-titik {
            background: #fff;
            box-shadow: 0 0 0 0 rgba(255, 255, 255, .7);
            animation: fsxDenyutPutih 2s ease-out infinite;
        }
        @keyframes fsxDenyutPutih {
            0%   { box-shadow: 0 0 0 0 rgba(255, 255, 255, .65); }
            70%  { box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }

        /* Butiran halus, sama seperti di hero: gradien selebar ini selalu
           memperlihatkan pita warna di layar 8-bit. */
        #call-to-action .fsx-hero::after {
            content: ""; position: absolute; inset: 0; pointer-events: none;
            opacity: .30; mix-blend-mode: multiply;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23n)' opacity='.5'/%3E%3C/svg%3E");
        }

        #call-to-action .fsx-kiri, #call-to-action .fsx-kanan { position: relative; z-index: 1; min-width: 0; }

        /* --- Penanda status --- */
        #call-to-action .fsx-lencana {
            display: inline-flex; align-items: center; gap: 9px;
            background: #fff3ea; border: 1px solid #f8d9c2;
            color: #d9531a; font-size: .74rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            padding: 6px 13px; border-radius: 999px; margin-bottom: 10px;
        }
        #call-to-action .fsx-titik {
            width: 7px; height: 7px; border-radius: 50%; background: #f26522;
            box-shadow: 0 0 0 0 rgba(242, 101, 34, .6);
            animation: fsxDenyut 2s ease-out infinite;
        }
        @keyframes fsxDenyut {
            0%   { box-shadow: 0 0 0 0 rgba(242, 101, 34, .5); }
            70%  { box-shadow: 0 0 0 9px rgba(242, 101, 34, 0); }
            100% { box-shadow: 0 0 0 0 rgba(242, 101, 34, 0); }
        }

        /* --- Judul & besar diskon --- */
        #call-to-action .fsx-judul {
            font-family: 'Poppins', sans-serif; font-weight: 800; color: #1c1f26;
            font-size: 2.05rem; line-height: 1.1; letter-spacing: -.03em; margin: 0;
            /* Hanya bagian depan nama promo yang tampil di sini, jadi ia boleh
               besar tanpa memakan tiga baris. Sisanya turun ke .fsx-ekor. */
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        #call-to-action .fsx-ekor {
            margin: 6px 0 0; font-size: .88rem; line-height: 1.5; color: #78808c;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* Angka diskon dibuat sebesar mungkin dan satuannya dikecilkan. Inilah
           satu-satunya angka yang benar-benar menentukan orang jadi membeli
           atau tidak, jadi ia yang paling besar di bidang ini. */
        #call-to-action .fsx-diskon-label {
            font-size: .88rem; font-weight: 600; letter-spacing: .04em;
            text-transform: uppercase; color: #9aa2ae; white-space: nowrap; margin-bottom: 1px;
        }
        /* Siapa yang berhak, tepat di bawah angkanya. Pertanyaan "saya dapat
           tidak?" muncul persis setelah orang melihat angka diskon. */
        #call-to-action .fsx-diskon-berhak {
            margin-top: 4px; font-size: .76rem; font-weight: 600; color: #c07a3f;
            white-space: nowrap;
        }
        #call-to-action .fsx-diskon-angka {
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 3.1rem;
            line-height: 1; letter-spacing: -.045em; white-space: nowrap;
            background: linear-gradient(120deg, #fba919, #f26522);
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: #fba919;
        }

        /* Keterangan promo disembunyikan di pita kepala: isinya hampir selalu
           mengulang nama promonya, dan di pita yang harus ringkas ia hanya
           menambah baris tanpa menambah keterangan. Tetap terbaca pembaca
           layar lewat judulnya. */
        #call-to-action .fsx-ket { display: none; }

        /* Tombol "Belanja Sekarang" dicabut dari pita. Produk yang ditawarkan
           ada tepat di bawahnya lengkap dengan tombol keranjangnya sendiri;
           satu tombol lagi di atas hanya menambah pilihan tanpa menambah
           kemungkinan. Tautan "Lihat Semua Produk" di kepala isi sudah
           mengurus yang ingin melihat selebihnya. */
        #call-to-action .fsx-tombol {
            display: none; align-items: center; gap: 9px;
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff;
            font-weight: 700; font-size: .92rem; text-decoration: none;
            padding: 12px 24px; border-radius: 12px;
            box-shadow: 0 16px 34px rgba(242, 101, 34, .34);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        #call-to-action .fsx-tombol:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 20px 42px rgba(242, 101, 34, .44);
        }
        #call-to-action .fsx-tombol i.bi { line-height: 1; }
        #call-to-action .fsx-tombol i.bi::before { display: block; line-height: 1; }

        /* --- Jam --- */
        /* Panel jam sengaja gelap di tengah kartu terang. Satu benda gelap di
           atas bidang terang selalu jadi titik jatuh mata — dan di bagian ini
           yang paling pantas dilihat lebih dulu memang jamnya. Ia juga
           menyambung ke hero gelap di atasnya, jadi halaman tetap satu bahasa. */
        #call-to-action .fsx-jam {
            background: linear-gradient(140deg, #232937 0%, #171b23 100%);
            border: 1px solid #2b3240; border-radius: 18px;
            padding: 16px 20px;
            box-shadow: 0 14px 32px rgba(28, 31, 38, .20);
        }
        #call-to-action .fsx-hitung-label {
            display: block; font-size: .72rem; font-weight: 700; letter-spacing: .16em;
            text-transform: uppercase; color: rgba(255, 255, 255, .45); margin-bottom: 11px;
        }
        #call-to-action .fsx-hitung { display: flex; align-items: flex-start; gap: 4px; }
        #call-to-action .fsx-satuan { flex: 0 0 auto; min-width: 46px; text-align: center; }
        #call-to-action .fsx-satuan b {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.95rem; line-height: 1; color: #fff; letter-spacing: -.03em;
            /* Angka detik berganti tiap detik. Tanpa lebar angka yang seragam,
               seluruh jam ikut bergoyang tiap kali angkanya berubah. */
            font-variant-numeric: tabular-nums;
        }
        #call-to-action .fsx-satuan small {
            display: block; margin-top: 6px; font-size: .58rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase; color: rgba(255, 255, 255, .40);
        }
        #call-to-action .fsx-titik-dua {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.4rem;
            line-height: 1.25; color: rgba(255, 255, 255, .30); flex: 0 0 auto;
            /* Titik dua berkedip adalah tanda universal jam yang sedang
               berjalan — satu-satunya gerakan di panel ini yang benar-benar
               menyampaikan sesuatu. */
            animation: fsxKedip 1s steps(1, end) infinite;
        }
        @keyframes fsxKedip { 0%, 49% { opacity: 1; } 50%, 100% { opacity: .18; } }

        /* --- Sisa kuota (hanya bila dipasang admin) --- */
        #call-to-action .fsx-kuota { margin-top: 20px; }
        #call-to-action .fsx-kuota-bilah {
            height: 5px; border-radius: 999px; background: rgba(255, 255, 255, .10); overflow: hidden;
        }
        #call-to-action .fsx-kuota-bilah span {
            display: block; height: 100%; border-radius: 999px;
            background: linear-gradient(90deg, #f26522, #fba919);
        }
        #call-to-action .fsx-kuota-teks {
            display: block; margin-top: 9px; font-size: .76rem; color: rgba(255, 255, 255, .48);
        }
        #call-to-action .fsx-kuota-teks b { color: #fba919; }

        /* --- Kaki: angka nyata, dipisah titik tengah --- */
        #call-to-action .fsx-fakta {
            grid-column: 1 / -1; position: relative; z-index: 1;
            display: flex; flex-wrap: wrap; align-items: center; gap: 6px 12px;
            margin: 0; padding-top: 14px;
            border-top: 1px solid #f6e8dc;
            font-size: .82rem; color: #8b939f;
        }
        #call-to-action .fsx-fakta span { color: #d6c3b4; }

        @media (prefers-reduced-motion: reduce) {
            #call-to-action .fsx-titik, #call-to-action .fsx-titik-dua { animation: none; }
            #call-to-action .fsx-titik-dua { opacity: 1; }
        }

        @media (max-width: 1199.98px) {
            /* Tiga kolom butuh sekitar 900px untuk bernapas. Di bawah itu
               kolom tengah dan jam bertukar jadi satu baris berdua, judulnya
               di atas — tetap membentang, tidak langsung menumpuk semua. */
            #call-to-action .fsx-kepala { grid-template-columns: auto 1fr; gap: 16px 26px; }
            #call-to-action .fsx-kiri { grid-column: 1 / -1; }
            #call-to-action .fsx-tengah { grid-column: 1; align-items: flex-start; padding-right: 26px; }
            #call-to-action .fsx-kanan { grid-column: 2; justify-self: end; }
        }

        @media (max-width: 991.98px) {
            /* DUA per baris, bukan empat yang dipaksakan. Di lebar ini empat
               kartu tidak muat dan membungkus jadi 3+1 — baris terakhir berisi
               satu kartu kesepian selalu terbaca seperti ada yang gagal dimuat,
               bukan seperti susunan yang disengaja. */
            /* min-width WAJIB dinolkan di sini. Aturan dasarnya memasang
               min-width: 180px supaya kartu tidak menyempit berlebihan di
               layar lebar — tapi di layar 390px dua kartu 180px ditambah
               jaraknya melebihi lebar yang tersedia, dan keduanya terlempar
               jadi satu per baris. */
            #call-to-action .fsx-deret-produk.featured-products-row > [class*="col-"] {
                flex: 1 1 calc(50% - 9px); max-width: calc(50% - 9px); min-width: 0;
            }
        }

        @media (max-width: 767.98px) {
            /* Padding atas tetap melebih supaya label yang menggantung dari
               garis atas tidak menabrak judul; di lebar ini label dan judul
               sempat berjarak 15px saja. */
            #call-to-action .fsx-kepala { grid-template-columns: 1fr; gap: 16px; padding: 48px 20px 16px; }
            #call-to-action .fsx-tengah {
                grid-column: 1; padding-right: 0; border-right: 0;
                padding-bottom: 14px; border-bottom: 1px solid #f2ddc9;
            }
            #call-to-action .fsx-kanan { grid-column: 1; justify-self: stretch; }
            #call-to-action .fsx-isi { padding: 20px 22px 24px; }
            #call-to-action .fsx-judul { font-size: 1.75rem; }
            #call-to-action .fsx-diskon-angka { font-size: 2.6rem; }
            #call-to-action .kb-judul { font-size: 1.6rem; }
        }
        @media (max-width: 575.98px) {
            #call-to-action .fsx-hero { border-radius: 20px; }
            /* Label menggantung setinggi 36px dari garis atas; padding 22px
               membuat judul MENIMPA labelnya. Ini tidak terlihat saat membaca
               kode — hanya saat mengukur jarak keduanya. */
            #call-to-action .fsx-kepala { padding: 46px 18px 16px; }
            #call-to-action .fsx-isi { padding: 18px 18px 22px; }
            #call-to-action .fsx-judul { font-size: 1.55rem; }
            #call-to-action .fsx-ekor { font-size: .82rem; -webkit-line-clamp: 1; }
            #call-to-action .fsx-diskon-angka { font-size: 2.3rem; }
            #call-to-action .fsx-jam { padding: 18px 16px; }
            #call-to-action .fsx-satuan b { font-size: 1.75rem; }
            #call-to-action .fsx-titik-dua { font-size: 1.3rem; }
            #call-to-action .fsx-satuan small { font-size: .58rem; letter-spacing: .06em; }
        }

        /* --- Bagian isi: kisi produk di dalam kartu yang sama --- */
        #call-to-action .fsx-isi {
            position: relative; z-index: 1;
            background: #fff; border-top: 1px solid #f6e8dc;
            padding: 22px 34px 28px;
        }
        #call-to-action .fsx-isi-kepala {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; margin-bottom: 18px;
        }
        #call-to-action .fsx-isi-kepala h3 {
            margin: 0; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: 1rem; color: #1c1f26; letter-spacing: -.01em;
        }
        #call-to-action .fsx-isi-kepala a {
            display: inline-flex; align-items: center; gap: 7px; flex: 0 0 auto;
            color: #f26522; font-weight: 700; font-size: .86rem; text-decoration: none;
            white-space: nowrap;
        }
        #call-to-action .fsx-isi-kepala a:hover { color: #d9531a; }
        #call-to-action .fsx-isi-kepala a i.bi { line-height: 1; }

        /* ----- Kartu produk promo: sistem kartu yang seragam -----
           Kartunya mewarisi gaya .fs-card dari CSS lama di server, yang tidak
           bisa disunting lewat git. Yang di bawah ini menimpanya supaya kartu
           promo memakai bahasa visual yang sama dengan kartu lain di beranda:
           bingkai tipis, sudut sama, dan angkat halus saat disentuh. */
        #call-to-action .fsx-deret-produk .fs-card {
            background: #fff; border: 1px solid #eceff4; border-radius: 18px;
            overflow: hidden;
            transition: border-color .22s ease, transform .22s ease, box-shadow .22s ease;
        }
        #call-to-action .fsx-deret-produk .fs-card:hover {
            border-color: #f7c9ae; transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(242, 101, 34, .12);
        }

        /* Latar netral untuk area gambar: logo produk datang dengan warna
           latar yang berbeda-beda, dan tanpa bidang penenang ini deretan
           kartunya terlihat seperti tambal sulam. */
        /* Rona hangat sangat tipis, bukan abu netral: ia mengikat kartu-kartu
           ini pada promo yang menaunginya tanpa perlu satu kalimat pun. Cukup
           terang supaya logo produk berlatar putih tetap terbaca. */
        #call-to-action .fsx-deret-produk .fs-card-media { background: #fffaf4; }

        /* Lencana diskon: satu warna padat, di pojok, tanpa bayangan tebal. */
        #call-to-action .fsx-deret-produk .fs-badge {
            background: #f26522; color: #fff; border: 0;
            font-size: .7rem; font-weight: 700; letter-spacing: .02em;
            padding: 5px 11px; border-radius: 999px;
            box-shadow: 0 4px 12px rgba(242, 101, 34, .3);
        }

        #call-to-action .fsx-deret-produk .fs-name {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1rem;
            color: #1c1f26; line-height: 1.3; text-decoration: none;
        }
        #call-to-action .fsx-deret-produk .fs-name:hover { color: #f26522; }

        /* Harga: yang berlaku besar dan berwarna, yang lama kecil dan redup.
           Sebelumnya keduanya hampir sebesar, jadi mata harus membandingkan
           dulu sebelum tahu mana yang harus dibayar. */
        #call-to-action .fsx-deret-produk .fs-price { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; }
        #call-to-action .fsx-deret-produk .fs-price-sale {
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.4rem;
            color: #f26522; letter-spacing: -.02em; line-height: 1.1;
        }
        #call-to-action .fsx-deret-produk .fs-price-orig {
            font-size: .85rem; color: #a8b0bb; text-decoration: line-through;
        }
        #call-to-action .fsx-deret-produk .fs-price small { font-size: .78rem; color: #9aa2ae; }

        #call-to-action .fsx-hemat-chip {
            display: inline-flex; align-items: center; gap: 6px; align-self: flex-start;
            background: #eefaf1; border: 1px solid #cdeed8; color: #17803d;
            font-size: .76rem; font-weight: 700; padding: 5px 11px; border-radius: 999px;
        }
        #call-to-action .fsx-hemat-chip i.bi { font-size: .82rem; line-height: 1; }

        /* Satu tombol utama, satu tombol tenang — sama seperti di hero. */
        #call-to-action .fsx-deret-produk .fs-btn-cart {
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff; border: 0;
            font-weight: 700; font-size: .88rem; padding: 11px 18px; border-radius: 11px;
            box-shadow: 0 8px 18px rgba(242, 101, 34, .26);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        #call-to-action .fsx-deret-produk .fs-btn-cart:hover {
            transform: translateY(-1px); box-shadow: 0 12px 24px rgba(242, 101, 34, .34);
        }
        #call-to-action .fsx-deret-produk .fs-btn-view {
            background: #fff; border: 1px solid #e6e9ef; color: #4b5563;
            font-weight: 600; font-size: .88rem; padding: 11px 18px; border-radius: 11px;
            text-decoration: none; transition: border-color .18s ease, color .18s ease;
        }
        #call-to-action .fsx-deret-produk .fs-btn-view:hover { border-color: #f26522; color: #f26522; }

        /* ----- Kartu produk promo: TEGAK, empat sejajar -----
           Kartu melebar (gambar di samping) hanya masuk akal saat promonya
           berisi dua produk. Begitu barisnya diisi empat, kartu melebar
           menyisakan tinggi berlebih dan keterangannya jadi sempit; kartu tegak
           memakai lebarnya untuk gambar dan tingginya untuk teks — bentuk yang
           memang dipakai seluruh kartu produk lain di beranda ini. */
        #call-to-action .fsx-deret-produk { justify-content: flex-start; gap: 18px; }
        /* width: auto WAJIB disebut. Bootstrap memberi col-lg-3 lebar 25%
           tetap, dan 25% x 4 ditambah tiga jarak 18px melebihi lebar barisnya —
           kartu keempat terlempar ke baris berikutnya, dan di 1100px modulnya
           membengkak dari 707px jadi 1.596px. Dengan width: auto, flex-basis
           yang memegang kendali dan jaraknya ikut diperhitungkan. */
        #call-to-action .fsx-deret-produk > [class*="col-"] {
            flex: 1 1 0; max-width: none; width: auto;
        }
        /* Batas menyempit hanya dipasang di layar lebar, lewat min-width media
           query — bukan sebagai aturan dasar yang lalu harus dinolkan lagi di
           tiap layar sempit. Aturan yang dipasang lalu dicabut berulang kali
           adalah cara paling mudah membuat satu di antaranya terlewat. */
        @media (min-width: 992px) {
            #call-to-action .fsx-deret-produk > [class*="col-"] { min-width: 180px; }
        }
        #call-to-action .fsx-deret-produk .fs-card {
            display: flex; flex-direction: column; height: 100%;
        }
        #call-to-action .fsx-deret-produk .fs-card-media {
            flex: 0 0 auto; aspect-ratio: 16 / 11; position: relative; overflow: hidden;
        }
        #call-to-action .fsx-deret-produk .fs-card-media img {
            width: 100%; height: 100%; object-fit: contain; padding: 16px;
        }
        #call-to-action .fsx-deret-produk .fs-card-body {
            flex: 1 1 auto; min-width: 0; padding: 15px 16px 16px;
            display: flex; flex-direction: column; gap: 8px;
        }
        /* Keterangan singkat tidak muat di kartu selebar 220px tanpa
           mendesak harga dan tombolnya; namanya sudah cukup di ukuran ini. */
        #call-to-action .fsx-ringkas { display: none; }
        /* Tombol didorong ke dasar kartu supaya seluruh kartu di satu baris
           punya garis tombol yang sejajar, berapa pun panjang nama produknya. */
        #call-to-action .fsx-deret-produk .fs-actions {
            margin-top: auto; padding-top: 4px; display: flex; gap: 8px;
        }
        #call-to-action .fsx-deret-produk .fs-btn-cart { flex: 1 1 auto; justify-content: center; }
        #call-to-action .fsx-deret-produk .fs-btn-view { flex: 0 0 auto; }

        @media (max-width: 575.98px) {
            /* DUA per baris, bukan satu. Menumpuk empat kartu setinggi 387px
               menghasilkan modul promo setinggi 2.272 piksel — satu dinding
               yang harus digulir lama sebelum halaman berlanjut. Dua per baris
               memotongnya jadi separuh.

               Digulir mendatar sebenarnya lebih pendek lagi, tapi tidak dipakai:
               sebagian pembeli toko ini tidak terbiasa dengan geser mendatar,
               dan barang yang tidak ditemukan sama saja dengan tidak dijual. */
            #call-to-action .fsx-deret-produk { gap: 12px; }
            /* Kekhususannya sengaja dinaikkan dengan menyebut kedua kelas:
               aturan lama .featured-products-row memaksa 100% di lebar ini dan
               ia berada LEBIH BAWAH di berkas, jadi pada kekhususan yang sama
               ia yang menang. Baris paket bundling tetap memakai aturan lama. */
            #call-to-action .fsx-deret-produk.featured-products-row > [class*="col-"] {
                flex: 1 1 calc(50% - 6px); max-width: calc(50% - 6px); min-width: 0;
            }
            #call-to-action .fsx-deret-produk .fs-card-body { padding: 12px 12px 13px; gap: 6px; }
            #call-to-action .fsx-deret-produk .fs-name { font-size: .86rem; }
            #call-to-action .fsx-deret-produk .fs-price-sale { font-size: 1.1rem; }
            #call-to-action .fsx-deret-produk .fs-price-orig { font-size: .74rem; }
            #call-to-action .fsx-hemat-chip { font-size: .68rem; padding: 4px 8px; }

            /* Pada kartu selebar 165px dua tombol bersebelahan tidak muat tanpa
               keduanya jadi terlalu kecil untuk disentuh. "Lihat Detail"
               dilepas — nama produk di atasnya sudah menuju halaman yang sama. */
            #call-to-action .fsx-deret-produk .fs-actions { gap: 6px; }
            #call-to-action .fsx-deret-produk .fs-btn-view { display: none; }
            #call-to-action .fsx-deret-produk .fs-btn-cart { width: 100%; padding: 10px 12px; font-size: .82rem; }
        }

        /* Kartu produk dipusatkan. Dengan grid Bootstrap, dua kartu menempel ke
           kiri dan menyisakan separuh baris kosong — terlihat seperti ada yang
           gagal dimuat, bukan seperti promo yang memang hanya berisi dua produk. */
        .featured-products-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; margin: 0; }
        .featured-products-row > [class*="col-"] { flex: 0 1 264px; max-width: 264px; width: auto; padding: 0; }

        @media (max-width: 575.98px) {
            #call-to-action .featured-products-row > [class*="col-"] { flex: 1 1 100%; max-width: none; }
        }
    </style>
        <div class="container">
            @php
                // Promo sering diisi dengan teks yang SAMA di ketiga kolomnya —
                // di server, nama_promo, badge_text, dan deskripsi ketiganya
                // "Pay Day Sale". Dulu ketiganya dicetak apa adanya, jadi satu
                // nama yang sama muncul tiga kali berturut-turut dan memakan
                // separuh layar. Yang kembar disaring di sini.
                $nama = trim((string) $flashSale->nama_promo);
                // Label di garis atas HARUS pendek. Ia legend kartu — tugasnya
                // memberi tahu jenis kartunya dalam sekali lihat, bukan
                // menyampaikan kalimat.
                //
                // badge_text sering diisi kalimat utuh oleh admin (di promo ini:
                // "Rayakan Kemerdekaan, Belanja Makin Hemat!"), dan dipasang di
                // garis atas dengan huruf kapital berjarak lebar ia jadi selebar
                // 478 piksel — pada lebar itu ia berhenti terbaca sebagai label
                // dan mulai terbaca sebagai judul kedua yang menyaingi judul
                // aslinya.
                //
                // Jadi: dipakai HANYA bila memang sependek label. Selebihnya
                // "Flash Sale", yang justru kata yang paling ingin dikenali
                // pengunjung di sini.
                $lencana = trim((string) $flashSale->badge_text);

                if ($lencana === '' || mb_strlen($lencana) > 18 || strcasecmp($lencana, $nama) === 0) {
                    $lencana = 'Flash Sale';
                }

                // Nama promo dipecah jadi JUDUL dan EKOR.
                //
                // Admin menulis nama promo sebagai satu kalimat penuh
                // ("Merdeka Sale! Diskon 17% All Item Spesial HUT RI ke-81").
                // Ditampilkan utuh sebesar judul ia memakan tiga baris;
                // dikecilkan supaya muat, ia jadi tidak terbaca. Keduanya salah.
                //
                // Yang benar: bagian depannya hampir selalu nama promonya
                // ("Merdeka Sale!") dan sisanya keterangan. Dipisah, judulnya
                // bisa besar tanpa memakan tiga baris, dan keterangannya tetap
                // ada di bawahnya.
                $judul = $nama;
                $ekor = '';

                foreach (['!', '—', '–', ':', ','] as $pisah) {
                    if (($pos = mb_strpos($nama, $pisah)) !== false && $pos < mb_strlen($nama) - 1) {
                        $judul = trim(mb_substr($nama, 0, $pos + ($pisah === '!' ? 1 : 0)));
                        $ekor = trim(mb_substr($nama, $pos + 1), " \t-–—:,");
                        break;
                    }
                }

                // Tanpa tanda pisah apa pun: nama yang panjang dipotong di kata
                // ketiga. Tiga kata cukup untuk mengenali sebuah promo, dan
                // sisanya tetap tampil sebagai keterangan.
                if ($ekor === '' && str_word_count($nama) > 5) {
                    $kata = preg_split('/\s+/', $nama) ?: [];
                    $judul = implode(' ', array_slice($kata, 0, 3));
                    $ekor = implode(' ', array_slice($kata, 3));
                }

                $ket = trim((string) $flashSale->deskripsi);
                if ($ket !== '' && (strcasecmp($ket, $nama) === 0 || strcasecmp($ket, $lencana) === 0)) {
                    $ket = '';
                }

                // Diskon NOMINAL adalah rupiah, bukan persen. Sebelumnya tanda %
                // ditempelkan ke angkanya juga, sehingga potongan Rp25.000 tampil
                // sebagai "Diskon hingga 25.000%" di beranda.
                // Angkanya diwarnai jingga, kalimatnya tidak: yang perlu
                // ditangkap dalam sekejap adalah BESAR potongannya, bukan kata
                // "hemat". Dirakit di sini, bukan di Blade, supaya angka yang
                // sudah diformat tidak perlu dipecah lagi belakangan.
                // Angka besar dipisah dari satuannya supaya bisa diberi ukuran
                // berbeda. Yang harus tertangkap dalam sekejap adalah BESAR
                // potongannya; kata "diskon" dan tanda persen boleh kecil.
                $persen = $flashSale->tipe_diskon === 'persen';
                $angkaDiskon = $persen
                    ? number_format($flashSale->diskon_member_persen, 0)
                    : number_format($flashSale->diskon_member_nominal, 0, ',', '.');
                $satuanDiskon = $persen ? '%' : '';
                $awalanDiskon = $persen ? '' : 'Rp';

                // "Sampai" hanya dipakai bila potongannya MEMANG berbeda antara
                // member dan non-member. Kalau keduanya sama — dan di promo ini
                // keduanya 17% — kata itu pagar tanpa isi: ia membuat tawaran
                // terdengar lebih ragu daripada kenyataannya, padahal setiap
                // pembeli pasti mendapat angka yang tertulis.
                $nilaiMember = $persen ? (float) $flashSale->diskon_member_persen : (float) $flashSale->diskon_member_nominal;
                $nilaiUmum = $persen ? (float) $flashSale->diskon_non_member_persen : (float) $flashSale->diskon_non_member_nominal;
                $labelDiskon = ($nilaiUmum > 0 && abs($nilaiMember - $nilaiUmum) > 0.001) ? 'Diskon sampai' : 'Diskon';

                // Siapa yang berhak. Pertanyaan "saya dapat tidak?" muncul di
                // kepala pembeli tepat setelah ia melihat angkanya, dan
                // menjawabnya di tempat itu juga jauh lebih berguna daripada
                // membiarkannya mencari sendiri di syarat & ketentuan.
                $berhak = match ($flashSale->untuk_member) {
                    'member_only' => 'khusus member',
                    'non_member_only' => 'khusus non-member',
                    default => 'untuk semua pembeli',
                };

                if ($flashSale->untuk_pembeli_pertama) {
                    $berhak = 'khusus pembeli pertama';
                }

                // Keterangan kaki dirakit dari angka yang BENAR-BENAR ADA di
                // basis data. Tidak ada satu pun yang dikarang: berapa produk
                // yang ikut, berapa kali promo ini sudah dipakai, dan sampai
                // kapan berlakunya. Angka nyata yang sederhana lebih meyakinkan
                // daripada klaim besar yang tidak bisa diperiksa siapa pun.
                $fakta = [];

                $jumlahIkut = $flashSale->products()->count() + $flashSale->bundlings()->count();
                if ($jumlahIkut > 0) {
                    $fakta[] = $jumlahIkut.' produk ikut promo';
                }

                if ((int) $flashSale->total_penggunaan > 0) {
                    $fakta[] = number_format($flashSale->total_penggunaan, 0, ',', '.').' kali sudah dipakai';
                }

                if ((int) $flashSale->min_pembelian > 0) {
                    $fakta[] = 'min. belanja Rp'.number_format($flashSale->min_pembelian, 0, ',', '.');
                }

                $fakta[] = 'berakhir '.$flashSale->selesai_promo->locale('id')->translatedFormat('d M Y');

                // Sisa kuota hanya ditampilkan bila kuotanya memang dipasang.
                // Bilah kelangkaan yang angkanya dikarang adalah kebohongan
                // yang paling menggoda untuk dibuat, dan paling merusak begitu
                // ketahuan.
                $kuota = (int) $flashSale->kuota;
                $terpakai = min((int) $flashSale->total_penggunaan, $kuota);
                $sisaKuota = $kuota > 0 ? max(0, $kuota - $terpakai) : null;
                $persenTerpakai = $kuota > 0 ? round($terpakai / $kuota * 100) : 0;
            @endphp

            <div class="fsx-hero">
                {{-- Pita kepala membentang KE SAMPING, tiga kolom: siapa —
                     apa tawarannya — sampai kapan.

                     Sebelumnya keempat unsurnya menumpuk ke bawah dan pitanya
                     setinggi 304px, hampir empat puluh persen seluruh modul,
                     untuk keterangan yang muat dalam separuhnya. Pita yang
                     lebih tinggi daripada barang yang diumumkannya bukan lagi
                     pita. --}}
                {{-- Label menempel di TENGAH GARIS ATAS kartu, seperti legend
                     pada fieldset.

                     Sebelumnya ia lencana kecil di pojok kiri, sejajar dengan
                     segala isi lain — sekadar satu unsur di antara banyak. Di
                     tengah garis atas ia berhenti jadi unsur dan mulai jadi
                     NAMA kartunya: terbaca sebelum apa pun yang lain, dan tidak
                     bisa dikira bagian dari kalimat di sekitarnya.

                     Titik berdenyut ikut pindah. Ia mengatakan "sedang
                     berlangsung sekarang", dan itu satu-satunya hal yang perlu
                     disampaikan gerakan di bagian ini. --}}
                <span class="fsx-pita-atas">
                    <i class="fsx-titik"></i> {{ $lencana }}
                </span>

                <div class="fsx-kepala">
                <div class="fsx-kiri">
                    <h2 class="fsx-judul">{{ $judul }}</h2>
                    @if ($ekor)
                        <p class="fsx-ekor">{{ $ekor }}</p>
                    @endif
                </div>

                <div class="fsx-tengah">
                    <span class="fsx-diskon-label">{{ $labelDiskon }}</span>
                    <span class="fsx-diskon-angka">{{ $awalanDiskon }}{{ $angkaDiskon }}{{ $satuanDiskon }}</span>
                    <span class="fsx-diskon-berhak">{{ $berhak }}</span>
                </div>

                <div class="fsx-kanan">
                    <div class="fsx-jam">
                        <span class="fsx-hitung-label">Berakhir dalam</span>

                        {{-- Jam, bukan empat kotak. Angka besar dengan titik dua
                             tipis di antaranya terbaca sebagai waktu yang sedang
                             berjalan; empat kotak terpisah terbaca sebagai empat
                             lencana yang kebetulan berisi angka. --}}
                        <div class="fsx-hitung">
                            @foreach ([
                                ['days', 'Hari'], ['hours', 'Jam'], ['minutes', 'Menit'], ['seconds', 'Detik'],
                            ] as $i => [$kunci, $label])
                                @if ($i > 0)<span class="fsx-titik-dua">:</span>@endif
                                <span class="fsx-satuan">
                                    <b>{{ str_pad($timeRemaining[$kunci] ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
                                    <small>{{ $label }}</small>
                                </span>
                            @endforeach
                        </div>

                        @if ($sisaKuota !== null)
                            {{-- Hanya muncul bila kuotanya memang dipasang admin.
                                 Bilah kelangkaan berangka karangan adalah
                                 kebohongan yang paling menggoda dibuat dan paling
                                 merusak begitu ketahuan. --}}
                            <div class="fsx-kuota">
                                <div class="fsx-kuota-bilah"><span style="width: {{ min(100, $persenTerpakai) }}%"></span></div>
                                <span class="fsx-kuota-teks">Sisa <b>{{ number_format($sisaKuota, 0, ',', '.') }}</b> dari {{ number_format($kuota, 0, ',', '.') }} kuota</span>
                            </div>
                        @endif
                    </div>
                </div>

                    <div class="fsx-kaki">
                        <p class="fsx-fakta">
                            @foreach ($fakta as $f)
                                @if (! $loop->first)<span aria-hidden="true">·</span>@endif{{ $f }}
                            @endforeach
                        </p>
                    </div>
                </div>{{-- /.fsx-kepala --}}

                {{-- Bagian produk berada DI DALAM kartu yang sama dengan
                     pengumumannya, dipisah satu garis.

                     Dipisah jadi dua kartu, pembeli tidak otomatis tahu bahwa
                     produk di bawah adalah yang kena promo di atas — hubungan
                     itu harus dijelaskan dengan kalimat, dan kalimat penjelas
                     selalu lebih lemah daripada susunan yang sudah menjelaskan
                     dirinya sendiri.

                     Syaratnya pengumumannya harus menyusut jadi PITA KEPALA.
                     Pengumuman setinggi hero ditumpuk dengan empat produk
                     menghasilkan kartu setinggi seribu piksel, dan pada tinggi
                     itu ia berhenti terbaca sebagai kartu. --}}
                <div class="fsx-isi">
                    <div class="fsx-isi-kepala">
                        <h3>Produk yang ikut promo</h3>
                        <a href="{{ route('shop.index') }}">Lihat Semua Produk <i class="bi bi-arrow-right"></i></a>
                    </div>

                    <div class="row featured-products-row fsx-deret-produk g-3 g-lg-4">
                @foreach ($featuredProducts as $product)
                    @php
                        $originalPrice = $product->harga_perbulan;
                        $discountedPrice = $this->getDiscountedPrice($originalPrice);
                        $best = $this->getBestDiscount();
                    @endphp
                    <div class="col-lg-3 col-md-6">
                        <div class="fs-card">
                            <div class="fs-card-media">
                                @if ($product->image)
                                    <img src="{{ asset('storage/img/Product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}">
                                @else
                                    <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                        alt="{{ $product->nama_akun }}">
                                @endif
                                @if ($best)
                                    <span class="fs-badge">Diskon s.d.
                                        @if ($best['isNominal'])
                                            Rp{{ number_format($best['value'], 0, ',', '.') }}
                                        @else
                                            {{ number_format($best['value'], 0) }}%
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="fs-card-body">
                                <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-name">{{ $product->nama_akun }}</a>
                                @if (filled($product->deskripsi))
                                    <p class="fsx-ringkas">{{ Str::limit(strip_tags($product->deskripsi), 110) }}</p>
                                @endif
                                <div class="fs-price">
                                    <span class="fs-price-sale">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                    @if ($discountedPrice < $originalPrice)
                                        <span class="fs-price-orig">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <small>/bln</small>
                                </div>

                                {{-- Nilai promo dalam RUPIAH, bukan hanya persen.
                                     "Diskon 17%" menuntut pembeli menghitung
                                     sendiri; "Hemat Rp11.900" sudah selesai
                                     dihitung, dan angka yang sudah selesai
                                     dihitung jauh lebih cepat meyakinkan. --}}
                                @if ($discountedPrice < $originalPrice)
                                    <span class="fsx-hemat-chip">
                                        <i class="bi bi-piggy-bank-fill"></i>
                                        Hemat Rp{{ number_format($originalPrice - $discountedPrice, 0, ',', '.') }}/bln
                                    </span>
                                @endif
                                <div class="fs-actions">
                                    <button type="button" wire:click="openDuration('{{ $product->id }}')"
                                        wire:loading.attr="disabled" wire:target="openDuration('{{ $product->id }}')"
                                        class="fs-btn-cart">
                                        <span wire:loading.remove wire:target="openDuration('{{ $product->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                        <span wire:loading wire:target="openDuration('{{ $product->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                    <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-btn-view">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paket bundling yang menumpang etalase promo ini.

                 Barisnya SENGAJA terpisah dari kartu produk di atas: kartu produk
                 membaca kolom khas Product dan menghitung diskon, sedangkan paket
                 punya kolomnya sendiri dan harganya sudah harga promo — tidak
                 didiskon lagi supaya tidak terpotong dua kali. --}}
            @if (count($featuredBundlings))
                <div class="row featured-products-row g-3 g-lg-4 mt-1">
                    @foreach ($featuredBundlings as $paket)
                        @php
                            // Satu sumber perhitungan untuk SEMUA halaman (beranda, daftar
                            // bundling, etalase ini) sekaligus cerminan PromoService —
                            // supaya angka di kartu tidak pernah berbeda dari yang ditagih
                            // di keranjang.
                            $hp = \App\Support\HargaPaket::untuk($paket);
                            $hargaPaket = $hp['bayar'];
                            $hargaAwal = $hp['coret'];
                        @endphp
                        <div class="col-lg-3 col-md-6" wire:key="fs-bundling-{{ $paket->id }}">
                            <div class="fs-card">
                                <div class="fs-card-media">
                                    @if ($paket->gambar)
                                        <img src="{{ asset('storage/img/ProductBundlings/' . $paket->gambar) }}"
                                            alt="{{ $paket->nama_paket }}" loading="lazy">
                                    @endif
                                    <span class="fs-badge">
                                        {{ $hp['potongan'] > 0 ? 'Hemat Rp' . number_format($hp['potongan'], 0, ',', '.') : 'Paket Spesial' }}
                                    </span>
                                </div>
                                <div class="fs-card-body">
                                    <a href="{{ route('bundling.index') }}" class="fs-name">{{ $paket->nama_paket }}</a>
                                    <div class="fs-price">
                                        <span class="fs-price-sale">Rp{{ number_format($hargaPaket, 0, ',', '.') }}</span>
                                        @if ($hargaAwal > $hargaPaket)
                                            <span class="fs-price-orig">Rp{{ number_format($hargaAwal, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                    <div class="fs-actions">
                                        <button type="button" wire:click="tambahPaket('{{ $paket->id }}')"
                                            wire:loading.attr="disabled" wire:target="tambahPaket('{{ $paket->id }}')"
                                            class="fs-btn-cart">
                                            <span wire:loading.remove wire:target="tambahPaket('{{ $paket->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                            <span wire:loading wire:target="tambahPaket('{{ $paket->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                        </button>
                                        <a href="{{ route('bundling.detail', $paket->id) }}" class="fs-btn-view">Lihat</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
                </div>{{-- /.fsx-isi --}}
            </div>{{-- /.fsx-hero --}}
        </div>
    @endif

    {{-- ===== Modal Pilih Durasi ===== --}}
    @if ($showDurationModal)
        <div class="fs-modal-overlay" wire:key="fs-dur-modal" wire:click.self="closeDuration">
            <div class="fs-modal">
                <button type="button" class="fs-modal-close" wire:click="closeDuration" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

                <div class="fs-modal-head">
                    <div class="fs-modal-thumb">
                        @if ($pickProductImage)
                            <img src="{{ asset('storage/img/Product/' . $pickProductImage) }}" alt="{{ $pickProductName }}">
                        @else
                            <i class="bi bi-box-seam"></i>
                        @endif
                    </div>
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow"><i class="bi bi-lightning-charge-fill"></i> Flash Sale</span>
                        <h4>{{ $pickProductName }}</h4>
                        <p>Pilih durasi langganan</p>
                    </div>
                </div>

                <div class="fs-modal-options">
                    @foreach ($pickPackages as $p)
                        @php $active = ($pickType === $p['duration_type'] && (int) $pickValue === (int) $p['duration_value']); @endphp
                        <button type="button" class="fs-opt {{ $active ? 'is-active' : '' }}"
                            wire:click="selectPackage('{{ $p['duration_type'] }}', {{ $p['duration_value'] }})">
                            <span class="fs-opt-radio"></span>
                            <span class="fs-opt-info">
                                <span class="fs-opt-label">{{ $p['label'] }}</span>
                                @if (!empty($p['savings']) && $p['savings'] > 0)
                                    <span class="fs-opt-save">Hemat Rp{{ number_format($p['savings'], 0, ',', '.') }}</span>
                                @endif
                            </span>
                            <span class="fs-opt-price">
                                @if (($p['discounted'] ?? $p['price']) < $p['price'])
                                    <span class="fs-opt-orig">Rp{{ number_format($p['price'], 0, ',', '.') }}</span>
                                @endif
                                <span class="fs-opt-now">Rp{{ number_format($p['discounted'] ?? $p['price'], 0, ',', '.') }}</span>
                            </span>
                        </button>
                    @endforeach

                    {{-- Durasi custom (bila produk punya harga per bulan) --}}
                    @if ($pickPerBulan > 0)
                        @php
                            $cp = $this->customPricing();
                            $customBase = $cp['base'];
                            $customDisc = $cp['discounted'];
                            $customSave = $cp['savings'];
                        @endphp
                        <div class="fs-opt fs-opt-custom {{ $pickIsCustom ? 'is-active' : '' }}">
                            <span class="fs-opt-radio" wire:click="chooseCustom"></span>
                            <span class="fs-opt-info" wire:click="chooseCustom">
                                <span class="fs-opt-label">Durasi lain</span>
                                <span class="fs-opt-sub">
                                    @if ($cp['matched'])
                                        Sesuai paket {{ $pickCustomMonths }} bulan
                                    @else
                                        Rp{{ number_format($pickPerBulan, 0, ',', '.') }}/bulan
                                    @endif
                                </span>
                            </span>
                            <div class="fs-stepper">
                                <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                <span class="fs-stepper-val">{{ $pickCustomMonths }} bln</span>
                                <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                            </div>
                        </div>
                        @if ($pickIsCustom)
                            <div class="fs-custom-total">
                                <span class="fs-custom-total-left">
                                    Total {{ $pickCustomMonths }} bulan
                                    @if ($customSave > 0)
                                        <span class="fs-opt-save">Hemat Rp{{ number_format($customSave, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="fs-custom-total-price">
                                    @if ($customDisc < $customBase)
                                        <span class="fs-opt-orig">Rp{{ number_format($customBase, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="fs-opt-now">Rp{{ number_format($customDisc, 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

                <button type="button" class="fs-modal-add" wire:click="confirmAddToCart"
                    wire:loading.attr="disabled" wire:target="confirmAddToCart">
                    <span wire:loading.remove wire:target="confirmAddToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                    <span wire:loading wire:target="confirmAddToCart"><span class="spinner-border spinner-border-sm"></span> Memproses…</span>
                </button>
            </div>
        </div>
    @endif

</div>
