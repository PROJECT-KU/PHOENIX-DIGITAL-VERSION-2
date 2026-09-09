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

           Kartu dengan SATU gagasan kuat, bukan banyak hiasan kecil. Angkanya
           sendiri yang jadi visual: besar, pucat, ditaruh di pojok. Cara ini
           lazim di tata letak editorial dan terbaca sebagai keputusan desain —
           berbeda dari lencana bulat mungil yang bisa ditempel di mana saja dan
           karena itu terasa seperti hasil cetakan.

           Tanpa bayangan, tanpa gradasi, tanpa kartu yang melompat saat
           disentuh. Yang bergerak hanya garis tepinya. */
        .cp-deret { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }

        .cp-langkah {
            position: relative; overflow: hidden;
            background: #fff; border: 1px solid #eceff3; border-radius: 16px;
            padding: 26px 22px 24px;
            transition: border-color .18s ease;
        }
        .cp-langkah:hover { border-color: #f7c9a3; }

        /* Angka pucat di pojok. Ditaruh di lapisan belakang supaya tulisan tetap
           yang pertama terbaca — angkanya penanda urutan, bukan judul. */
        /* Angkanya utuh di dalam kartu, tidak dipotong tepi. Angka yang separuh
           terpangkas terbaca sebagai kesalahan susun, bukan sebagai gaya. */
        .cp-nomor {
            position: absolute; top: 14px; right: 18px; z-index: 0;
            font-family: 'Poppins', sans-serif; font-weight: 800;
            font-size: 3.4rem; line-height: 1; letter-spacing: -.05em;
            color: #fdeee0; user-select: none;
        }

        .cp-judul, .cp-ket { position: relative; z-index: 1; }
        .cp-judul {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.02rem;
            color: #1c1f26; margin: 0 0 7px; line-height: 1.3; letter-spacing: -.01em;
        }
        /* Garis pendek di bawah judul: penanda kecil yang mengikat keempat kartu
           tanpa menambah warna atau bentuk baru. */
        .cp-judul::after {
            content: ""; display: block; width: 26px; margin-top: 10px;
            border-top: 2px solid #f26522;
        }
        .cp-ket { color: #6b7280; font-size: .875rem; line-height: 1.65; margin: 0; }

        @media (max-width: 991.98px) { .cp-deret { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 575.98px) {
            .cp-deret { grid-template-columns: 1fr; gap: 12px; }
            .cp-langkah { padding: 22px 18px 20px; }
            .cp-nomor { font-size: 2.9rem; top: 12px; right: 16px; }
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
