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
        /* Ditulis inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
        .cp-deret { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .cp-langkah {
            position: relative; background: #fff; border: 1px solid #eceff3;
            border-radius: 14px; padding: 20px 18px;
        }
        /* Garis penyambung antar langkah: menegaskan ini URUTAN, bukan empat
           keterangan yang berdiri sendiri. Ditarik dari tepi kanan kartu ke
           tetangganya, dan tidak digambar setelah kartu terakhir. */
        .cp-langkah:not(:last-child)::after {
            content: ""; position: absolute; top: 34px; right: -16px; width: 16px;
            border-top: 2px dashed #f7c9a3;
        }
        /* Angka polos, bukan kotak bergradasi berbayang. Empat kotak menyala
           berderet membuat langkah-langkah ini tampak seperti iklan; yang
           dibutuhkan pembaca hanya urutannya. */
        .cp-nomor {
            display: block; margin-bottom: 10px;
            font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.6rem;
            color: #f26522; line-height: 1;
        }
        .cp-judul { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1rem; color: #23272f; margin: 0 0 6px; line-height: 1.3; }
        .cp-ket { color: #6b7280; font-size: .86rem; line-height: 1.6; margin: 0; }

        @media (max-width: 991.98px) {
            .cp-deret { grid-template-columns: repeat(2, 1fr); }
            /* Pada dua kolom, garis penyambung ikut memutus di ujung baris —
               kalau tidak, ia menggantung ke ruang kosong di tepi kanan. */
            .cp-langkah:nth-child(2n)::after { display: none; }
        }
        @media (max-width: 575.98px) {
            .cp-deret { grid-template-columns: 1fr; }
            .cp-langkah::after { display: none !important; }
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
