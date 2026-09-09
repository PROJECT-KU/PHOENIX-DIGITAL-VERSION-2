@section('title')
    Syarat & Ketentuan | Phoenix Digital
@endsection

<main class="legal-page sk-lebar">
    <style>
        /* Ditulis inline: resources/css/public-custom-styles.css dikompilasi ke
           public/build yang MASUK .gitignore dan tidak ikut terdeploy — salinan
           di server masih tertanggal 19 Agustus.

           Berbeda dari FAQ & Member yang dipecah dua kolom seimbang: teks hukum
           dibaca BERURUTAN, jadi memecahnya jadi dua kolom justru memaksa mata
           naik-turun. Yang dipakai di sini pola daftar isi — sama seperti
           halaman ketentuan Orcha: navigasi lengket di kiri, isinya di kanan.
           Pembaca bisa melompat ke pasal yang dicarinya tanpa menggulir. */
        .sk-lebar .legal-card { max-width: 1180px; }
        .sk-lebar .legal-hero { padding-top: 34px; padding-bottom: 26px; }
        .sk-lebar .legal-hero h1 { margin-bottom: 6px; }

        .sk-tata { display: grid; grid-template-columns: 250px 1fr; gap: 36px; align-items: start; }

        /* Lengket, dengan tinggi maksimum & gulir sendiri: kalau daftarnya lebih
           tinggi daripada layar, tanpa ini bagian bawahnya mustahil dijangkau. */
        .sk-isi-nav {
            position: sticky; top: 90px; max-height: calc(100vh - 110px); overflow-y: auto;
            border-right: 1px solid #f1f3f6; padding-right: 18px;
        }
        .sk-isi-nav b {
            display: block; font-size: .68rem; font-weight: 800; letter-spacing: .08em;
            text-transform: uppercase; color: #a8b3c4; margin-bottom: 10px; padding-left: 12px;
        }
        .sk-isi-nav a {
            display: block; padding: 8px 12px; border-radius: 10px; margin-bottom: 2px;
            font-size: .86rem; font-weight: 600; color: #6b7280; text-decoration: none;
            border-left: 2px solid transparent; transition: background .15s ease, color .15s ease;
        }
        .sk-isi-nav a:hover { background: #fff3e6; color: #d9531a; border-left-color: #f26522; }

        /* Sasaran lompatan diberi jarak dari tepi atas: tanpa ini judul pasal
           tersembunyi di balik bilah navigasi yang menempel di puncak layar. */
        .sk-lebar .legal-block { scroll-margin-top: 90px; }

        @media (max-width: 991.98px) {
            .sk-tata { grid-template-columns: 1fr; gap: 20px; }
            .sk-isi-nav {
                position: static; max-height: none; overflow: visible;
                border-right: 0; padding-right: 0; border-bottom: 1px solid #f1f3f6; padding-bottom: 14px;
            }
            /* Mendatar di layar sempit: daftar tegak sepanjang sembilan baris
               justru mendorong isinya turun jauh dari pandangan. */
            .sk-isi-nav-tautan { display: flex; flex-wrap: wrap; gap: 6px; }
            .sk-isi-nav a { border-left: 0; background: #f8fafc; padding: 6px 11px; font-size: .8rem; }
        }
    </style>
    <div class="legal-hero">
        <div class="container">
            <span class="ph-sec-eyebrow"><i class="bi bi-file-earmark-text"></i> Legal</span>
            <h1>Syarat &amp; Ketentuan</h1>
            <p>Ketentuan penggunaan layanan Phoenix Digital. Dengan bertransaksi, Anda dianggap menyetujui poin-poin berikut.</p>
        </div>
    </div>

    <div class="container">
        <div class="legal-card">
            @php
                // Urutannya HARUS sama dengan urutan blok di bawah; id-nya sk-1..sk-9.
                $pasal = [
                    'Tentang Layanan', 'Pemesanan & Pembayaran', 'Pengiriman Akun', 'Garansi',
                    'Batas Perangkat & Blokir Otomatis', 'Kebijakan Refund', 'Tanggung Jawab Pengguna',
                    'Larangan', 'Perubahan Ketentuan',
                ];
            @endphp

            <div class="sk-tata">
                <nav class="sk-isi-nav" aria-label="Daftar isi">
                    <b>Daftar Isi</b>
                    <div class="sk-isi-nav-tautan">
                        @foreach ($pasal as $i => $judul)
                            <a href="#sk-{{ $i + 1 }}">{{ $i + 1 }}. {{ $judul }}</a>
                        @endforeach
                    </div>
                </nav>

                <div>
            <div class="legal-block" id="sk-1">
                <h2><span>1</span> Tentang Layanan</h2>
                <p>Phoenix Digital menyediakan akun premium, lisensi, dan tools AI untuk kebutuhan riset serta produktivitas. Kami mengutamakan layanan yang <b>terpercaya, amanah, dan respons cepat</b>. Kami juga melayani kebutuhan <b>kampus/instansi</b> — silakan <a href="https://wa.me/6289505967995" target="_blank" rel="noopener">booking melalui WhatsApp</a> untuk pemesanan kolektif.</p>
            </div>

            <div class="legal-block" id="sk-2">
                <h2><span>2</span> Pemesanan &amp; Pembayaran</h2>
                <p>Pemesanan dilakukan melalui website. Metode pembayaran yang tersedia <b>hanya Transfer Bank dan QRIS</b>. Pesanan diproses setelah pembayaran terverifikasi.</p>
                <p>Demi keamanan, pastikan pembayaran ditujukan <b>atas nama Phoenix Digital Warehouse</b>. Jika ragu, konfirmasikan terlebih dahulu ke admin kami melalui <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20konfirmasi%20pembayaran." target="_blank" rel="noopener">WhatsApp 0895-0596-7995</a>.</p>
            </div>

            <div class="legal-block" id="sk-3">
                <h2><span>3</span> Pengiriman Akun</h2>
                <p>Detail akun/lisensi dikirim melalui WhatsApp atau kanal yang disepakati setelah pembayaran dikonfirmasi. Kami mengusahakan proses secepat mungkin pada jam operasional.</p>
            </div>

            <div class="legal-block" id="sk-4">
                <h2><span>4</span> Garansi</h2>
                <p>Setiap akun bergaransi selama masa aktif sesuai paket yang dibeli. Jika terjadi kendala pada masa garansi, hubungi kami dan tim akan membantu secepatnya.</p>
            </div>

            <div class="legal-block legal-highlight" id="sk-5">
                <h2><span>5</span> Batas Perangkat &amp; Blokir Otomatis</h2>
                <p>Setiap akun hanya boleh digunakan pada <b>maksimal 2 (dua) perangkat</b>. Jika digunakan pada lebih dari 2 perangkat, akun akan <b>terblokir secara otomatis</b> oleh sistem penyedia. Kondisi ini <b>menghanguskan garansi</b>, berada <b>di luar kebijakan kami</b>, serta <b>tidak ada pembaruan maupun pembukaan pemblokiran</b>. Mohon patuhi batas perangkat demi kenyamanan bersama.</p>
            </div>

            <div class="legal-block legal-highlight" id="sk-6">
                <h2><span>6</span> Kebijakan Refund</h2>
                <p>Jika akun <b>belum diserahkan</b>, dana dikembalikan <b>100%</b>. Namun jika akun <b>sudah diserahkan/diaktifkan</b>, pengembalian dana maksimal <b>50%</b> — karena akun telah digunakan/terpakai. Pengajuan refund menyertakan bukti pembayaran dan alasan yang jelas.</p>
            </div>

            <div class="legal-block" id="sk-7">
                <h2><span>7</span> Tanggung Jawab Pengguna</h2>
                <p>Pengguna wajib menjaga kerahasiaan akun yang diterima dan menggunakannya secara wajar. Kerusakan akibat pelanggaran ketentuan penyedia layanan asli di luar tanggung jawab kami.</p>
            </div>

            <div class="legal-block" id="sk-8">
                <h2><span>8</span> Larangan</h2>
                <p>Dilarang menjual ulang, menyalahgunakan, atau membagikan akun di luar kesepakatan tanpa izin. Pelanggaran dapat menggugurkan garansi.</p>
            </div>

            <div class="legal-block" id="sk-9">
                <h2><span>9</span> Perubahan Ketentuan</h2>
                <p>Syarat &amp; Ketentuan dapat diperbarui sewaktu-waktu. Versi terbaru yang berlaku adalah yang tercantum pada halaman ini.</p>
            </div>

                </div>
            </div>

            <div class="legal-contact">
                <i class="bi bi-whatsapp"></i>
                <div>
                    <strong>Butuh bantuan?</strong>
                    <span>Hubungi kami di <a href="https://wa.me/6289505967995" target="_blank" rel="noopener">0895-0596-7995</a></span>
                </div>
            </div>
        </div>
    </div>
</main>
