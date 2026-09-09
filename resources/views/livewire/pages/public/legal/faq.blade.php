@section('title')
    FAQ — Pertanyaan Umum | Phoenix Digital
@endsection

<main class="legal-page faq-lebar">
    <style>
        /* Ditulis inline, bukan di resources/css/public-custom-styles.css: berkas
           itu dikompilasi Vite ke public/build yang MASUK .gitignore dan tidak
           ikut terdeploy — salinan di server masih tertanggal 19 Agustus, jadi
           aturan yang ditulis di sana tidak akan pernah sampai ke pengunjung.

           Kartunya dulu selebar ~810px di layar 1470px: lebih dari separuh layar
           kosong sementara tujuh jawaban bertumpuk menurun. Dilebarkan lalu
           dipecah dua kolom, sehingga hampir seluruh isinya terbaca sekaligus. */
        .faq-lebar .legal-card { max-width: 1180px; }

        /* column-count, bukan grid: tiap jawaban panjangnya berbeda-beda, dan
           kolom otomatis menyeimbangkan tingginya sendiri. Dengan grid, satu
           jawaban panjang akan meninggalkan lubang di sebelahnya. */
        .faq-kolom { column-count: 2; column-gap: 30px; }

        /* Tanpa ini satu jawaban bisa terbelah di tengah, judulnya tertinggal di
           kolom kiri sementara isinya pindah ke kanan. */
        .faq-kolom .legal-block { break-inside: avoid; page-break-inside: avoid; }

        /* Garis pemisah antar blok tidak lagi berarti begitu isinya berkolom:
           yang di dasar kolom akan berhias garis menggantung. */
        .faq-kolom .legal-block { border-bottom: 0; }

        .faq-lebar .legal-hero { padding-top: 34px; padding-bottom: 26px; }
        .faq-lebar .legal-hero h1 { margin-bottom: 6px; }

        @media (max-width: 991.98px) {
            .faq-kolom { column-count: 1; }
        }
    </style>
    <div class="legal-hero">
        <div class="container">
            <span class="ph-sec-eyebrow"><i class="bi bi-patch-question"></i> Bantuan</span>
            <h1>Pertanyaan Umum (FAQ)</h1>
            <p>Jawaban singkat untuk pertanyaan yang paling sering ditanyakan seputar pemesanan, pembayaran, dan garansi di Phoenix Digital.</p>
        </div>
    </div>

    <div class="container">
        <div class="legal-card">
            <div class="faq-kolom">
            <div class="legal-block">
                <h2><span><i class="bi bi-bag-check"></i></span> Bagaimana cara memesan?</h2>
                <p>Pilih produk atau paket bundling di halaman <a href="{{ route('shop.index') }}">Shop</a>, masukkan ke keranjang, lalu lanjut ke checkout. Isi nomor WhatsApp, nama, dan email, kemudian selesaikan pembayaran. Untuk pemesanan kolektif kampus/instansi, silakan <a href="https://wa.me/6289505967995" target="_blank" rel="noopener">booking via WhatsApp</a>.</p>
            </div>

            <div class="legal-block legal-highlight">
                <h2><span><i class="bi bi-shield-check"></i></span> Metode pembayaran apa saja & bagaimana agar aman?</h2>
                <p>Pembayaran <b>hanya melalui Transfer Bank dan QRIS</b>. Demi keamanan, pastikan pembayaran selalu tertuju <b>atas nama Phoenix Digital Warehouse</b>. Selain nama itu, dipastikan penipuan — jangan lanjutkan dan konfirmasikan ke admin kami.</p>
            </div>

            <div class="legal-block">
                <h2><span><i class="bi bi-clock-history"></i></span> Berapa lama pesanan diproses?</h2>
                <p>Pesanan diproses setelah pembayaran terverifikasi. Detail akun/lisensi dikirim melalui WhatsApp atau kanal yang disepakati secepatnya pada jam operasional.</p>
            </div>

            <div class="legal-block">
                <h2><span><i class="bi bi-patch-check"></i></span> Apakah akun bergaransi?</h2>
                <p>Ya. Setiap akun bergaransi selama masa aktif sesuai paket yang dibeli. Bila ada kendala pada masa garansi, hubungi kami dan tim akan membantu.</p>
            </div>

            <div class="legal-block legal-highlight">
                <h2><span><i class="bi bi-phone"></i></span> Berapa batas perangkat per akun?</h2>
                <p>Maksimal <b>2 (dua) perangkat</b> per akun. Jika dipakai di lebih dari 2 perangkat, akun dapat <b>terblokir otomatis</b> oleh sistem penyedia — kondisi ini menghanguskan garansi dan di luar kebijakan kami. Selengkapnya lihat <a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a>.</p>
            </div>

            <div class="legal-block">
                <h2><span><i class="bi bi-arrow-counterclockwise"></i></span> Bagaimana kebijakan pengembalian dana (refund)?</h2>
                <p>Jika akun <b>belum diserahkan</b>, dana dikembalikan <b>100%</b>. Jika akun <b>sudah diserahkan/diaktifkan</b>, pengembalian maksimal <b>50%</b>. Detail ada di <a href="{{ route('terms') }}">Syarat &amp; Ketentuan</a>.</p>
            </div>

            <div class="legal-block">
                <h2><span><i class="bi bi-lock"></i></span> Bagaimana data saya dijaga?</h2>
                <p>Kami hanya menggunakan data Anda untuk memproses pesanan dan layanan purnajual. Selengkapnya baca <a href="{{ route('privacy') }}">Kebijakan Privasi</a>.</p>
            </div>

            </div>

            <div class="legal-contact">
                <i class="bi bi-whatsapp"></i>
                <div>
                    <strong>Masih ada pertanyaan?</strong>
                    <span>Hubungi kami di <a href="https://wa.me/6289505967995" target="_blank" rel="noopener">0895-0596-7995</a></span>
                </div>
            </div>
        </div>
    </div>

    @php
        $faqLd = [
            ['Bagaimana cara memesan di Phoenix Digital?', 'Pilih produk atau paket bundling di halaman Shop, masukkan ke keranjang, lalu lanjut checkout. Isi nomor WhatsApp, nama, dan email, kemudian selesaikan pembayaran.'],
            ['Metode pembayaran apa saja dan bagaimana agar aman?', 'Pembayaran hanya melalui Transfer Bank dan QRIS. Pastikan pembayaran selalu tertuju atas nama Phoenix Digital Warehouse. Selain nama itu dipastikan penipuan.'],
            ['Berapa lama pesanan diproses?', 'Pesanan diproses setelah pembayaran terverifikasi. Detail akun atau lisensi dikirim melalui WhatsApp atau kanal yang disepakati secepatnya pada jam operasional.'],
            ['Apakah akun bergaransi?', 'Ya. Setiap akun bergaransi selama masa aktif sesuai paket yang dibeli. Bila ada kendala pada masa garansi, hubungi kami dan tim akan membantu.'],
            ['Berapa batas perangkat per akun?', 'Maksimal 2 (dua) perangkat per akun. Jika dipakai di lebih dari 2 perangkat, akun dapat terblokir otomatis oleh sistem penyedia dan menghanguskan garansi.'],
            ['Bagaimana kebijakan pengembalian dana (refund)?', 'Jika akun belum diserahkan, dana dikembalikan 100%. Jika akun sudah diserahkan atau diaktifkan, pengembalian maksimal 50%.'],
            ['Bagaimana data saya dijaga?', 'Kami hanya menggunakan data Anda untuk memproses pesanan dan layanan purnajual. Selengkapnya dijelaskan pada Kebijakan Privasi.'],
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqLd)->map(fn ($q) => [
                '@type' => 'Question',
                'name' => $q[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]],
            ])->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</main>
