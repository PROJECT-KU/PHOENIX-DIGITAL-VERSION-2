@section('title')
    Beranda | Phoenix Digital
@endsection
<x-guest-layout>
    <div class="main">
        <!--================== HEADER ==================-->
        <section id="hero" class="hero section dark-background">
            <img src="{{ asset('global/assets/img/hero-bg-2.jpg') }}" alt="" class="hero-bg">
            <div class="container">
                <div class="row gy-4 justify-content-between">
                    <div class="col-lg-4 order-lg-last hero-img" data-aos="zoom-out" data-aos-delay="100">
                        <img src="{{ asset('global/assets/img/hero-img.png') }}" class="img-fluid animated"
                            alt="">
                    </div>

                    <div class="col-lg-6  d-flex flex-column justify-content-center" data-aos="fade-in">
                        <h1>Butuh Akun Premium? <span>Dapatkan Sekarang dengan Harga Gila-Gilaan!</span></h1>
                        <p>Paket lengkap untuk kebutuhan riset, tugas, dan produktivitas. Dijamin aktif atau uang
                            kembali.</p>
                        <div class="d-flex">
                            <a href="https://wa.me/6289505967995?text=Halo%20kak%2C%20saya%20tertarik%20dengan%20promo%20akun%20premium.%20Boleh%20minta%20info%20lebih%20lanjut%3F"
                                target="_blank" class="btn-get-started">
                                Cek Promo Hari Ini!
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 24 150 28 " preserveAspectRatio="none">
                <defs>
                    <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z">
                    </path>
                </defs>
                <g class="wave1">
                    <use xlink:href="#wave-path" x="50" y="3"></use>
                </g>
                <g class="wave2">
                    <use xlink:href="#wave-path" x="50" y="0"></use>
                </g>
                <g class="wave3">
                    <use xlink:href="#wave-path" x="50" y="9"></use>
                </g>
            </svg>
        </section>
        <!--================== END ==================-->

        <!--================== PRODUK ==================-->
        <section id="promo" class="pricing section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Promo Akun Premium</h2>
                <div><span>Super Murah,</span> <span class="description-title">Langsung Aktif & Terjamin Legal</span>
                </div>
            </div>

            <div class="container">
                <div class="row gy-4 justify-content-center">

                    <!-- Paket Silaturahmi Hemat -->
                    <div class="col-lg-4 d-flex" data-aos="zoom-in" data-aos-delay="100">
                        <div class="pricing-item flex-fill">
                            <h3>Paket Silaturahmi Hemat</h3>
                            <p class="description">
                                🌟 Cocok untuk kamu yang ingin coba dulu! <br>
                                Dalam satu paket, kamu langsung dapat:<br>
                                ✅ <strong>Scopus Lisensi</strong><br>
                                ✅ <strong>Scopus AI</strong><br>
                                ✅ <strong>DeepL Pro</strong><br>
                                💡 Ideal untuk mahasiswa akhir, dosen, atau peneliti yang ingin hemat tapi tetap
                                maksimal.<br>
                                📌 Langsung aktif, tanpa ribet!<br>
                                🎯 Harga bersahabat, kualitas profesional.
                            </p>
                            <h4 class="text-center">
                                <span class="badge bg-warning text-danger fw-bold">PROMO HARI INI!</span><br><br>
                                <span class="text-decoration-line-through text-danger">
                                    <sup>Rp</sup>250.000
                                </span><br>
                                <span class="fw-bold text-success" style="font-size: 30px;">
                                    <sup>Rp</sup>180.000
                                </span>
                                <span style="font-size: 0.9rem;">/ bulan</span>
                            </h4>
                            <a href="https://wa.me/6289505967995?text=Halo%2C%20saya%20tertarik%20dengan%20Paket%20Silaturahmi%20Hemat"
                                class="cta-btn" target="_blank">Pesan Sekarang!</a>
                            <p class="text-center">🎉 <strong>Jangan lewatkan kesempatan terbatas ini!</strong> Promo
                                bisa berakhir kapan saja.</p>
                        </div>
                    </div>

                    <!-- Paket Spesial Terbatas -->
                    <div class="col-lg-4 d-flex" data-aos="zoom-in" data-aos-delay="200">
                        <div class="pricing-item featured flex-fill">
                            <p class="popular">Popular</p>
                            <h3>Paket Spesial Terbatas</h3>
                            <p class="description">
                                💼 Butuh akses jangka panjang dan andalan? Ini dia jawabannya!<br>
                                ✅ <strong>Scopus Lisensi</strong><br>
                                ✅ <strong>Scopus AI</strong><br>
                                ✅ <strong>SciSpace Pro</strong><br>
                                🧠 Cocok banget buat peneliti serius, dosen, dan pejuang skripsi.<br>
                                🕑 Promo hanya untuk hari ini — stok terbatas!<br>
                                🔒 Aman, legal, dan dijamin aktif.
                            </p>
                            <h4 class="text-center">
                                <span class="badge bg-warning text-danger fw-bold">PROMO HARI INI!</span><br><br>
                                <span class="text-decoration-line-through text-danger">
                                    <sup>Rp</sup>650.000
                                </span><br>
                                <span class="fw-bold text-success" style="font-size: 30px;">
                                    <sup>Rp</sup>300.000
                                </span>
                                <span style="font-size: 0.9rem;">/ 6 bulan</span>
                            </h4>
                            <a href="https://wa.me/6289505967995?text=Halo%2C%20saya%20tertarik%20dengan%20Paket%20Spesial%20Terbatas"
                                class="cta-btn" target="_blank">Pesan Sekarang!</a>
                            <p class="text-center">🎉 <strong>Jangan lewatkan kesempatan terbatas ini!</strong> Promo
                                bisa berakhir kapan saja.</p>
                        </div>
                    </div>

                    <!-- Paket Sultan Edu -->
                    <div class="col-lg-4 d-flex" data-aos="zoom-in" data-aos-delay="300">
                        <div class="pricing-item flex-fill">
                            <h3>Paket Sultan Edu</h3>
                            <p class="description">
                                🔥 Paket paling lengkap dan tahan lama, buat kamu yang nggak mau ribet setahun ke
                                depan!<br>
                                ✅ <strong>Scopus Lisensi 1 tahun</strong><br>
                                ✅ <strong>Scopus AI</strong><br>
                                ✅ <strong>Grammarly Premium</strong><br>
                                🎁 Bonus: <strong>SciSpace 1 bulan GRATIS</strong><br>
                                📚 Ideal untuk dosen, mahasiswa S2/S3, penulis akademik, dan profesional.<br>
                                💰 Bayar sekali, tenang setahun!<br>
                            </p>
                            <h4 class="text-center">
                                <span class="badge bg-warning text-danger fw-bold">PROMO HARI INI!</span><br><br>
                                <span class="text-decoration-line-through text-danger">
                                    <sup>Rp</sup>900.000
                                </span><br>
                                <span class="fw-bold text-success" style="font-size: 30px;">
                                    <sup>Rp</sup>600.000
                                </span>
                                <span style="font-size: 0.9rem;">/ tahun</span>
                            </h4>
                            <a href="https://wa.me/6289505967995?text=Halo%2C%20saya%20tertarik%20dengan%20Paket%20Sultan%20Edu"
                                class="cta-btn" target="_blank">Pesan Sekarang!</a>
                            <p class="text-center">🎉 <strong>Jangan lewatkan kesempatan terbatas ini!</strong> Promo
                                bisa berakhir kapan saja.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== MENGAPA MEMILIH KAMI ==================-->
        <section id="tentang" class="about section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row align-items-xl-center gy-5">

                    <div class="col-xl-5 content" style="margin-top: -50px;">
                        <h3>Mengapa Memilih Kami?</h3>
                        <h2>Solusi Akun Premium Terpercaya, Terjangkau, & Terjamin</h2>
                        <p>
                            Kami hadir sebagai partner digital terbaik untuk mahasiswa, dosen, dan peneliti. Dengan
                            harga yang bersahabat dan kualitas premium, kami telah dipercaya oleh ribuan pengguna di
                            seluruh Indonesia.
                            <br>
                            Pilih kami, dan rasakan kemudahan dalam riset & penulisan ilmiah tanpa hambatan!
                        </p>
                    </div>

                    <div class="col-xl-7">
                        <div class="row gy-4 icon-boxes">

                            <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                                <div class="icon-box">
                                    <i class="bi bi-chat-dots-fill"></i>
                                    <h3>Respon Cepat 24/7</h3>
                                    <p>
                                        Tim support kami siap membantu kapan pun Anda butuh. Konsultasi, bantuan teknis,
                                        atau pertanyaan seputar akun premium—kami hadir 24 jam setiap hari.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                                <div class="icon-box">
                                    <i class="bi bi-tags-fill"></i>
                                    <h3>Harga Terjangkau</h3>
                                    <p>
                                        Nikmati akses ke akun premium berkualitas dengan harga yang bersahabat di
                                        kantong. Solusi hemat untuk mahasiswa, dosen, hingga profesional.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                                <div class="icon-box">
                                    <i class="bi bi-star-fill"></i>
                                    <h3>Kualitas Terjamin</h3>
                                    <p>
                                        Kami hanya menyediakan akun premium original dan legal dengan performa terbaik.
                                        Dapatkan pengalaman penggunaan yang lancar, aman, dan terpercaya.
                                    </p>
                                </div>
                            </div>

                            <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
                                <div class="icon-box">
                                    <i class="bi bi-graph-up-arrow"></i>
                                    <h3>Pemesanan Instan</h3>
                                    <p>
                                        Proses pemesanan cepat dan praktis! Tanpa ribet, tanpa antre – akun langsung
                                        dikirim ke WhatsApp Anda setelah pembayaran. Hemat waktu, langsung pakai!
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== STATISTIK PEMBELI ==================-->
        <section id="statistik" class="stats section light-background">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row gy-4 text-center">

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-emoji-smile" style="font-size: 2.5rem; color: #ffc107;"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="3501"
                                data-purecounter-duration="1" class="purecounter"></span>
                            <p>Pelanggan Puas</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-box-seam" style="font-size: 2.5rem; color: #0d6efd;"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="8059"
                                data-purecounter-duration="1" class="purecounter"></span>
                            <p>Paket Terjual</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-headset" style="font-size: 2.5rem; color: #28a745;"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="25989"
                                data-purecounter-duration="1" class="purecounter"></span>
                            <p>Jam Layanan Aktif</p>
                        </div>
                    </div>

                    <!-- DIGANTI -->
                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-bar-chart-line-fill" style="font-size: 2.5rem; color: #17a2b8;"></i>
                        <div class="stats-item d-flex flex-column align-items-center">
                            <!-- Angka dan Persen dalam satu baris -->
                            <div class="d-flex align-items-baseline">
                                <span data-purecounter-start="0" data-purecounter-end="98"
                                    data-purecounter-duration="1" class="purecounter"
                                    style="font-size: 36px; font-weight: bold;"></span>
                                <span style="font-size: 36px; margin-left: 4px;">%</span>
                            </div>
                            <p style="margin: 0; font-size: 16px;">Tingkat Kepuasan Pelanggan</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== PRODUK ==================-->
        <section id="produk" class="features section">
            <div class="container section-title" data-aos="fade-up">
                <h2>Promo Akun Premium Terlaris</h2>
                <div><span>Diskon Besar-besaran,</span> <span class="description-title">Akun Resmi, Langsung Aktif, &
                        Legal</span></div>
            </div>

            <div class="container">
                <div class="row gy-4">

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="features-item">
                            <i class="bi bi-journal-text" style="color: #ffbb2c;"></i>
                            <h3><a href="#" class="stretched-link">Scopus Lisensi + Scopus AI</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="features-item">
                            <i class="bi bi-spellcheck" style="color: #5578ff;"></i>
                            <h3><a href="#" class="stretched-link">Grammarly Premium</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="features-item">
                            <i class="bi bi-chat-dots" style="color: #e80368;"></i>
                            <h3><a href="#" class="stretched-link">ChatGPT Premium</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="features-item">
                            <i class="bi bi-lightbulb" style="color: #e361ff;"></i>
                            <h3><a href="#" class="stretched-link">SciSpace AI</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="500">
                        <div class="features-item">
                            <i class="bi bi-translate" style="color: #47aeff;"></i>
                            <h3><a href="#" class="stretched-link">DeepL Pro</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="600">
                        <div class="features-item">
                            <i class="bi bi-pencil-square" style="color: #ffa76e;"></i>
                            <h3><a href="#" class="stretched-link">QuillBot Premium</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="700">
                        <div class="features-item">
                            <i class="bi bi-graph-up-arrow" style="color: #11dbcf;"></i>
                            <h3><a href="#" class="stretched-link">Scite.ai</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="800">
                        <div class="features-item">
                            <i class="bi bi-bar-chart-fill" style="color: #4233ff;"></i>
                            <h3><a href="#" class="stretched-link">Gamma AI</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="900">
                        <div class="features-item">
                            <i class="bi bi-shield-check" style="color: #b2904f;"></i>
                            <h3><a href="#" class="stretched-link">Turnitin</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="1000">
                        <div class="features-item">
                            <i class="bi bi-file-earmark-text" style="color: #b20969;"></i>
                            <h3><a href="#" class="stretched-link">Paperpal</a></h3>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="1100">
                        <div class="features-item">
                            <i class="bi bi-windows" style="color: #ff5828;"></i>
                            <h3><a href="#" class="stretched-link">Lisensi MS Office</a></h3>
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== FAQ ==================-->
        <section id="faq" class="faq section light-background">
            <div class="container-fluid">
                <div class="row gy-4">
                    <div class="col-lg-7 d-flex flex-column justify-content-center order-2 order-lg-1">

                        <div class="content px-xl-5" data-aos="fade-up" data-aos-delay="100">
                            <h3><span>Pertanyaan yang </span><strong>Sering Diajukan</strong></h3>
                            <p>
                                Berikut adalah beberapa pertanyaan umum dari pelanggan kami. Jika masih ada yang ingin
                                ditanyakan, jangan ragu untuk menghubungi kami.
                            </p>
                        </div>

                        <div class="faq-container px-xl-5" data-aos="fade-up" data-aos-delay="200">

                            <div class="faq-item faq-active">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>Apakah akun ready stock?</h3>
                                <div class="faq-content">
                                    <p>Ya, semua produk yang tertera di katalog kami adalah ready stock dan siap untuk
                                        langsung dikirim setelah pemesanan dilakukan.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>

                            <div class="faq-item">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>Berapa lama pengiriman setelah pemesanan?</h3>
                                <div class="faq-content">
                                    <p>Setelah pembayaran dikonfirmasi, pesanan akan langsung kami proses dan kirim di
                                        hari yang sama atau maksimal 1x24 jam (hari kerja).</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>

                            <div class="faq-item">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>Apakah akun ini terpercaya dan aman?</h3>
                                <div class="faq-content">
                                    <p>Tentu saja! Kami sudah melayani ribuan pelanggan dengan rating kepuasan tinggi.
                                        Semua transaksi dijamin aman dan informasi pelanggan dijaga kerahasiaannya.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>

                            <div class="faq-item">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>Bagaimana jika akun error atau tidak bisa digunakan?</h3>
                                <div class="faq-content">
                                    <p>Jika terjadi kendala pada akun yang Anda terima, kami siap bantu! Silakan
                                        langsung hubungi tim support kami untuk panduan solusi atau penggantian.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>

                            <div class="faq-item">
                                <i class="faq-icon bi bi-question-circle"></i>
                                <h3>Apakah support tersedia 24 jam?</h3>
                                <div class="faq-content">
                                    <p>Ya! Tim support kami siap membantu Anda 24/7. Kapan pun Anda butuh bantuan,
                                        langsung hubungi kami melalui chat yang tersedia.</p>
                                </div>
                                <i class="faq-toggle bi bi-chevron-right"></i>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-5 order-1 order-lg-2">
                        <img src="{{ asset('global/assets/img/faq.jpg') }}" class="img-fluid" alt="FAQ Image"
                            data-aos="zoom-in" data-aos-delay="100">
                    </div>

                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== KONTAK ==================-->
        <section id="kontak" class="contact section">

            <div class="container section-title" data-aos="fade-up">
                <h2>Hubungi Kami</h2>
                <div><span>Pesan Sekarang,</span> <span class="description-title">Respon Cepat & Ramah 24/7</span>
                </div>
            </div>

            <div class="container" data-aos="fade" data-aos-delay="100">
                <div class="row gy-4">

                    <div class="col-lg-4">
                        <!-- <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
                        <i class="bi bi-geo-alt flex-shrink-0"></i>
                        <div>
                            <h3>Address</h3>
                            <p>A108 Adam Street, New York, NY 535022</p>
                        </div>
                    </div> -->

                        <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
                            <i class="bi bi-whatsapp flex-shrink-0"></i>
                            <div>
                                <h3>Chat via WhatsApp</h3>
                                <p><a href="https://wa.me/6289505967995" target="_blank">+62 895-0596-7995</a></p>
                            </div>
                        </div>

                        <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
                            <i class="bi bi-envelope flex-shrink-0"></i>
                            <div>
                                <h3>Email</h3>
                                <p><a
                                        href="mailto:phoenixdigitalwarehouse@gmail.com">phoenixdigitalwarehouse@gmail.com</a>
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-8">
                        <form action="https://formspree.io/f/xkgjkvry?language=id" method="POST"
                            class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                            <div class="row gy-4">

                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Nama Anda" required="">
                                </div>

                                <div class="col-md-6 ">
                                    <input type="email" class="form-control" name="email"
                                        placeholder="Email Anda" required="">
                                </div>

                                <div class="col-md-12">
                                    <input type="text" class="form-control" name="subject" placeholder="Subject"
                                        required="">
                                </div>

                                <div class="col-md-12">
                                    <textarea class="form-control" name="message" rows="6" placeholder="Pesan" required=""></textarea>
                                </div>

                                <div class="col-md-12 text-center">
                                    <button type="submit">Kirim Pesan</button>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </section>
        <!--================== END ==================-->
    </div>
</x-guest-layout>
