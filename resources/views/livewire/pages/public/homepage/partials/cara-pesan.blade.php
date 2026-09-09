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

           BUKAN empat kartu berjajar. Kartu adalah wadah untuk hal-hal yang
           berdiri sendiri — produk, paket, artikel — sedangkan ini satu ALUR:
           langkah 3 tidak berarti apa-apa tanpa langkah 2. Membungkusnya dalam
           empat kotak justru menyembunyikan hubungan itu, dan menghasilkan kisi
           kartu yang sudah tiga kali muncul di halaman yang sama.

           Diganti garis proses: nomor duduk di atas satu garis mendatar yang
           menyambung dari langkah pertama ke terakhir. Bentuknya sendiri yang
           mengatakan "ini urutan" — tanpa perlu hiasan tambahan. */
        .cp-deret { display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px 24px; }

        .cp-langkah { position: relative; padding-top: 4px; }

        /* Garis penyambung ditarik dari sisi kanan nomor sampai nomor berikutnya,
           dan TIDAK digambar setelah langkah terakhir — garis yang menjulur ke
           ruang kosong membuat alurnya seolah belum selesai. */
        .cp-langkah:not(:last-child)::after {
            content: ""; position: absolute; top: 21px; left: 52px; right: -24px;
            border-top: 1px solid #ecd9c6;
        }

        /* Nomor bercincin, bukan kotak terisi: cincin membuatnya terbaca sebagai
           titik pada sebuah garis, sedangkan kotak terisi terbaca sebagai lencana. */
        .cp-nomor {
            position: relative; z-index: 1;
            width: 42px; height: 42px; border-radius: 50%; margin-bottom: 16px;
            display: flex; align-items: center; justify-content: center;
            background: #fff; border: 1.5px solid #f7c9a3; color: #f26522;
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.05rem;
        }

        .cp-judul {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.02rem;
            color: #1c1f26; margin: 0 0 6px; line-height: 1.3; letter-spacing: -.01em;
        }
        .cp-ket { color: #6b7280; font-size: .875rem; line-height: 1.65; margin: 0; padding-right: 18px; }

        @media (max-width: 991.98px) {
            .cp-deret { grid-template-columns: repeat(2, 1fr); }
            /* Pada dua kolom, garis di ujung baris menjulur ke tepi kanan yang
               kosong; hanya langkah ganjil yang masih punya tetangga di sampingnya. */
            .cp-langkah:nth-child(2n)::after { display: none; }
        }
        @media (max-width: 575.98px) {
            .cp-deret { grid-template-columns: 1fr; gap: 22px; }

            /* Nomor pindah ke kolomnya sendiri di kiri, teks di kolom kanan.
               Kalau nomornya tetap DI ATAS teks, garis tegak penyambungnya harus
               melewati tempat yang sama dengan tulisan — dan ia benar-benar
               menembus kata-katanya. */
            .cp-langkah { display: grid; grid-template-columns: 38px 1fr; column-gap: 14px; }
            .cp-nomor { grid-column: 1; grid-row: 1 / span 2; width: 38px; height: 38px; margin-bottom: 0; }
            .cp-judul, .cp-ket { grid-column: 2; }
            .cp-judul { align-self: center; }

            /* Garis turun di jalur nomor, bukan di jalur teks. */
            .cp-langkah:not(:last-child)::after {
                top: 44px; bottom: -22px; left: 19px; right: auto;
                width: 0; border-top: 0; border-left: 1px solid #ecd9c6;
            }
            .cp-ket { padding-right: 0; }
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
