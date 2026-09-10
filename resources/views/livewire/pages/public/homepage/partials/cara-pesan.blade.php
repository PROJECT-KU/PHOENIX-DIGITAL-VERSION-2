{{-- Empat langkah dari memilih sampai menerima.

     Menjawab keraguan terbesar pembeli akun premium: "setelah saya bayar, saya
     dapat apa dan kapan?" Sebelumnya jawabannya hanya disinggung sekilas lewat
     chip "Proses Instan" di hero — sebuah janji tanpa penjelasan, yang justru
     menimbulkan pertanyaan alih-alih menjawabnya.

     Langkah keempat sengaja menyebut DUA kemungkinan (akun dikirim / hasil
     diunduh), karena toko ini menjual dua hal yang cara penyerahannya berbeda.
     Menyebut satu saja akan menyesatkan separuh pembeli. --}}
<section id="cara-pesan" class="section">
    <style>
        /* Ditulis inline: public/build masuk .gitignore dan tidak ikut terdeploy.

           SATU gagasan: ini bukan empat kartu, melainkan satu JALUR berisi
           empat perhentian.

           Sebelumnya keempatnya kartu setara dengan angka pucat di pojok.
           Angka memang menandai urutan, tapi angka saja tidak membuat mata
           bergerak dari satu kartu ke kartu berikutnya — dan urutan justru
           satu-satunya isi bagian ini. Sekarang ada garis putus-putus yang
           menembus celah antar kartu, dan bulatan bernomor duduk di atasnya:
           bentuk yang sudah dikenali semua orang sebagai "langkah demi
           langkah", tanpa perlu satu kata penjelas pun. */
        .cp-deret {
            position: relative;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;
        }

        /* Garis jalur. Digambar di lapisan paling belakang dan hanya terlihat
           di CELAH antar kartu — kartunya berlatar putih pekat dan berada di
           atasnya, jadi garisnya tampak muncul dan menghilang di antara
           perhentian. Tepi kiri dan kanannya disisakan supaya jalurnya tidak
           menjulur keluar dari kartu pertama dan terakhir. */
        .cp-deret::before {
            content: ""; position: absolute; z-index: 0;
            top: 48px; left: 64px; right: 64px;
            border-top: 2px dashed #f8d8bf;
        }

        .cp-langkah {
            position: relative; z-index: 1;
            background: #fff; border: 1px solid #eceff3; border-radius: 16px;
            padding: 26px 22px 24px;
            transition: border-color .18s ease;
        }
        .cp-langkah:hover { border-color: #f7c9a3; }

        /* Perhentian: bulatan bernomor. Menggantikan angka pucat raksasa di
           pojok — angka itu hiasan, bulatan ini penanda posisi pada jalur.
           Ukurannya dipatok supaya semua bulatan duduk di ketinggian yang
           sama persis dengan garisnya. */
        .cp-nomor {
            display: flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 50%; margin-bottom: 16px;
            background: #fff; border: 2px solid #f26522; color: #f26522;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: 1.15rem; line-height: 1;
            font-variant-numeric: tabular-nums;
            transition: background .22s ease, color .22s ease, box-shadow .22s ease;
        }
        /* Perhentian yang disentuh terisi penuh — menandai "Anda di sini". */
        .cp-langkah:hover .cp-nomor {
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff;
            border-color: transparent;
            box-shadow: 0 8px 18px rgba(242, 101, 34, .3);
        }

        .cp-judul {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: 1.05rem;
            color: #1c1f26; margin: 0 0 8px; line-height: 1.3; letter-spacing: -.015em;
        }
        .cp-ket { color: #6b7280; font-size: .89rem; line-height: 1.65; margin: 0; }

        @media (max-width: 991.98px) {
            .cp-deret { grid-template-columns: repeat(2, 1fr); }
            /* Dua baris: garis lurus mendatar tidak lagi menggambarkan jalurnya
               dengan benar — jalur yang menyeberang ke baris berikutnya tanpa
               belokan justru menyesatkan. */
            .cp-deret::before { display: none; }
        }
        @media (max-width: 575.98px) {
            .cp-deret { grid-template-columns: 1fr; gap: 12px; }
            .cp-langkah { padding: 20px 18px 18px; }
            .cp-nomor { width: 38px; height: 38px; font-size: 1rem; margin-bottom: 12px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .cp-langkah, .cp-nomor { transition: none; }
        }
    </style>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <x-kepala-bagian
            ikon="bi-signpost-split-fill"
            kicker="Mudah & Cepat"
            judul="Cara Pesan"
            sub="Empat langkah dari memilih sampai akun atau hasil ada di tangan Anda." />

        <div class="cp-deret">
            @foreach ([
                ['Pilih produk', 'Telusuri akun premium, paket bundling, atau layanan cek & parafrase yang Anda butuhkan.'],
                ['Bayar', 'Transfer atau QRIS. Pembayaran terverifikasi otomatis, tanpa perlu mengirim bukti.'],
                ['Kami proses', 'Akun disiapkan seketika. Untuk layanan cek & parafrase, naskah Anda mulai dikerjakan tim kami.'],
                ['Terima hasilnya', 'Akun dikirim lewat email & WhatsApp; hasil pengerjaan diunduh dari halaman pribadi Anda.'],
            ] as $i => [$judul, $ket])
                <div class="cp-langkah">
                    <span class="cp-nomor">{{ $i + 1 }}</span>
                    <h3 class="cp-judul">{{ $judul }}</h3>
                    <p class="cp-ket">{{ $ket }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
