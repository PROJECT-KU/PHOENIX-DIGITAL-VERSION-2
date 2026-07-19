<div>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-fire"></i> Tentang Kami</span>
                <h1>Tentang Phoenix Digital</h1>
                <p>Penyedia akun premium, lisensi, &amp; tools AI untuk riset dan produktivitas — terpercaya, amanah,
                    respons cepat.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li class="current">Tentang Kami</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <!-- Intro -->
    <section class="abt-intro">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6" data-aos="fade-up">
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Siapa Kami</span>
                    <h2 class="abt-heading">Akses tools premium jadi mudah, aman, &amp; terjangkau</h2>
                    <p class="abt-lead">Phoenix Digital membantu pelajar, peneliti, dan profesional mendapatkan akun
                        premium, lisensi resmi, serta tools AI untuk mempercepat riset dan pekerjaan sehari-hari.</p>
                    <p class="abt-text">Kami mengutamakan layanan yang <b>terpercaya dan amanah</b> dengan <b>respons
                            cepat</b>. Dari kebutuhan perorangan hingga pemesanan kolektif untuk <b>kampus &amp;
                            instansi</b>, semuanya kami layani dengan harga yang bersahabat.</p>

                    <div class="abt-trust">
                        <span class="abt-chip"><i class="bi bi-shield-check"></i> Transaksi Aman</span>
                        <span class="abt-chip"><i class="bi bi-lightning-charge"></i> Respons Cepat</span>
                        <span class="abt-chip"><i class="bi bi-patch-check"></i> Bergaransi</span>
                    </div>

                    <div class="abt-actions">
                        <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai
                            Belanja</a>
                        <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya."
                            target="_blank" rel="noopener" class="ph-empty-btn ghost"><i class="bi bi-whatsapp"></i>
                            Hubungi Kami</a>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="150">
                    <div class="abt-visual">
                        <div class="abt-visual-glow"></div>
                        {{-- Gambar AI Anda: taruh file di storage/app/public/img/about-research.png (jpg/png).
                             Jika belum ada, otomatis pakai ilustrasi vektor di bawah sebagai fallback. --}}
                        @php $aboutImg = public_path('storage/img/about-research.png'); @endphp
                        @if (file_exists($aboutImg))
                            <img src="{{ asset('storage/img/about-research.png') }}?v={{ filemtime($aboutImg) }}"
                                alt="Ilustrasi riset Phoenix Digital" class="abt-illus abt-illus-img">
                        @else
                        <svg class="abt-illus" viewBox="0 0 460 400" fill="none"
                            xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Ilustrasi orang sedang meneliti data">
                            <defs>
                                <linearGradient id="abtG" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#fbc25a" />
                                    <stop offset="1" stop-color="#f26522" />
                                </linearGradient>
                                <linearGradient id="abtGv" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#fba919" />
                                    <stop offset="1" stop-color="#f26522" />
                                </linearGradient>
                                <linearGradient id="abtBg" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#ffe9d0" />
                                    <stop offset="1" stop-color="#fff7ee" />
                                </linearGradient>
                                <linearGradient id="abtInk" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#464c56" />
                                    <stop offset="1" stop-color="#2b3038" />
                                </linearGradient>
                                <linearGradient id="abtCard" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#ffffff" />
                                    <stop offset="1" stop-color="#fffaf3" />
                                </linearGradient>
                            </defs>

                            {{-- Latar berlapis (kedalaman) --}}
                            <rect x="34" y="60" width="392" height="278" rx="34" fill="url(#abtBg)" />
                            <circle cx="392" cy="104" r="46" fill="#fbaf45" opacity=".16" />
                            <circle cx="70" cy="300" r="30" fill="#f26522" opacity=".08" />
                            <ellipse cx="232" cy="352" rx="168" ry="16" fill="#e15a18" opacity=".10" />

                            {{-- Kartu molekul (riset medis) — mengambang --}}
                            <g>
                                <rect x="70" y="94" width="96" height="74" rx="16" fill="#f26522" opacity=".08" transform="translate(3,4)" />
                                <rect x="70" y="94" width="96" height="74" rx="16" fill="url(#abtCard)" stroke="#f1e6d8" stroke-width="1.5" />
                                <g stroke="url(#abtGv)" stroke-width="2.6">
                                    <line x1="98" y1="140" x2="116" y2="120" />
                                    <line x1="116" y1="120" x2="138" y2="132" />
                                    <line x1="138" y1="132" x2="132" y2="152" />
                                    <line x1="132" y1="152" x2="108" y2="152" />
                                    <line x1="108" y1="152" x2="98" y2="140" />
                                    <line x1="116" y1="120" x2="108" y2="152" />
                                </g>
                                <circle cx="98" cy="140" r="6" fill="#f26522" />
                                <circle cx="116" cy="120" r="7" fill="#fba919" />
                                <circle cx="138" cy="132" r="6" fill="#f4772b" />
                                <circle cx="132" cy="152" r="5" fill="#fbc25a" />
                                <circle cx="108" cy="152" r="5" fill="#f7a23e" />
                            </g>
                            {{-- Kaca pembesar (meneliti) --}}
                            <g class="abt-illus-mag">
                                <circle cx="158" cy="160" r="18" fill="rgba(251,169,25,.14)" stroke="url(#abtGv)" stroke-width="5" />
                                <line x1="171" y1="173" x2="184" y2="186" stroke="url(#abtGv)" stroke-width="7" stroke-linecap="round" />
                            </g>

                            {{-- Kartu AI — mengambang --}}
                            <g class="abt-illus-bulb">
                                <rect x="352" y="150" width="78" height="62" rx="16" fill="#f26522" opacity=".08" transform="translate(3,4)" />
                                <rect x="352" y="150" width="78" height="62" rx="16" fill="url(#abtCard)" stroke="#f1e6d8" stroke-width="1.5" />
                                <rect x="376" y="166" width="30" height="30" rx="8" fill="url(#abtGv)" />
                                <g stroke="url(#abtGv)" stroke-width="2.4" stroke-linecap="round">
                                    <line x1="382" y1="162" x2="382" y2="166" />
                                    <line x1="391" y1="162" x2="391" y2="166" />
                                    <line x1="400" y1="162" x2="400" y2="166" />
                                    <line x1="382" y1="196" x2="382" y2="200" />
                                    <line x1="391" y1="196" x2="391" y2="200" />
                                    <line x1="400" y1="196" x2="400" y2="200" />
                                    <line x1="372" y1="174" x2="376" y2="174" />
                                    <line x1="372" y1="188" x2="376" y2="188" />
                                    <line x1="406" y1="174" x2="410" y2="174" />
                                    <line x1="406" y1="188" x2="410" y2="188" />
                                </g>
                                <text x="391" y="186" font-family="Poppins, sans-serif" font-size="13" font-weight="800" fill="#ffffff" text-anchor="middle">AI</text>
                            </g>

                            {{-- Meja --}}
                            <rect x="66" y="298" width="330" height="16" rx="8" fill="url(#abtGv)" />
                            <rect x="66" y="298" width="330" height="5" rx="2.5" fill="#ffffff" opacity=".4" />
                            <rect x="66" y="314" width="330" height="8" rx="4" fill="#e15a18" opacity=".22" />

                            {{-- Monitor dashboard data (fokus riset) --}}
                            <g>
                                <rect x="292" y="288" width="10" height="14" fill="#3a4049" />
                                <rect x="278" y="300" width="38" height="5" rx="2.5" fill="#3a4049" />
                                <rect x="248" y="206" width="132" height="84" rx="9" fill="url(#abtInk)" />
                                <rect x="254" y="212" width="120" height="72" rx="4" fill="#fffaf2" />
                                <rect x="262" y="219" width="42" height="7" rx="3.5" fill="url(#abtG)" />
                                <circle cx="362" cy="222" r="2" fill="#cfc7bb" />
                                <circle cx="368" cy="222" r="2" fill="#cfc7bb" />
                                {{-- line chart + area --}}
                                <path d="M262 254 L278 246 L292 250 L306 238 L320 244 L334 232 L348 236 L362 228 L362 260 L262 260 Z" fill="url(#abtG)" opacity=".18" />
                                <polyline points="262,254 278,246 292,250 306,238 320,244 334,232 348,236 362,228" fill="none" stroke="url(#abtGv)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="306" cy="238" r="2.6" fill="#f26522" />
                                <circle cx="362" cy="228" r="2.6" fill="#f26522" />
                                {{-- donut --}}
                                <circle cx="276" cy="272" r="9" fill="none" stroke="#eee7dd" stroke-width="4" />
                                <path d="M276 263 a9 9 0 0 1 8 13" fill="none" stroke="url(#abtGv)" stroke-width="4" stroke-linecap="round" />
                                {{-- bars --}}
                                <rect x="332" y="266" width="6" height="10" rx="2" fill="#fbc25a" />
                                <rect x="342" y="260" width="6" height="16" rx="2" fill="#f7a23e" />
                                <rect x="352" y="263" width="6" height="13" rx="2" fill="#f26522" />
                            </g>

                            {{-- Kursi --}}
                            <rect x="92" y="200" width="18" height="104" rx="9" fill="#e7dbcc" />

                            {{-- Orang (peneliti berkacamata) --}}
                            <g class="abt-illus-person">
                                <path d="M116 300 C116 240 140 216 168 216 C196 216 220 240 220 300 Z" fill="url(#abtGv)" />
                                <path d="M154 222 L168 238 L182 222" stroke="#ffffff" stroke-opacity=".55" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M206 252 C226 256 246 264 260 272" stroke="url(#abtGv)" stroke-width="15" stroke-linecap="round" fill="none" />
                                <circle cx="262" cy="273" r="8" fill="#f6c9a0" />
                                <rect x="160" y="198" width="16" height="22" rx="7" fill="#e8b184" />
                                <circle cx="168" cy="184" r="25" fill="#f6c9a0" />
                                <path d="M142 183 C140 164 153 153 168 153 C184 153 196 165 194 184 C189 172 180 167 168 167 C154 167 147 172 142 183 Z" fill="#33281f" />
                                <path d="M142 183 C140 197 145 205 150 210 L150 188 C146 186 144 184 142 183 Z" fill="#33281f" />
                                <circle cx="143" cy="188" r="4" fill="#e8b184" />
                                <g stroke="#3a4049" stroke-width="2" fill="none">
                                    <circle cx="160" cy="186" r="6.5" />
                                    <circle cx="178" cy="186" r="6.5" />
                                    <line x1="166.5" y1="186" x2="171.5" y2="186" />
                                    <line x1="184.5" y1="184" x2="190" y2="182" />
                                </g>
                            </g>

                            {{-- Tanaman kecil di meja --}}
                            <g>
                                <path d="M96 298 L114 298 L111 288 L99 288 Z" fill="#f4772b" />
                                <path d="M105 288 C99 276 93 274 90 267 C99 267 105 274 105 285 Z" fill="#7fae5a" />
                                <path d="M105 288 C111 276 117 274 120 267 C111 267 105 274 105 285 Z" fill="#93c06b" />
                            </g>

                            {{-- Cangkir --}}
                            <g>
                                <rect x="226" y="286" width="16" height="12" rx="2" fill="#ffffff" stroke="#f1e6d8" stroke-width="1.2" />
                                <path d="M242 289 h3 a3.5 3.5 0 0 1 0 7 h-3" fill="none" stroke="#f1e6d8" stroke-width="1.6" />
                                <rect x="226" y="285" width="16" height="3" rx="1.5" fill="url(#abtGv)" />
                            </g>

                            {{-- Aksen berkelip --}}
                            <path class="abt-illus-spark" d="M212 96 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3 z" fill="#fba919" />
                            <circle class="abt-illus-spark2" cx="60" cy="150" r="5" fill="#f4772b" />
                        </svg>
                        @endif

                        <span class="abt-badge abt-b1"><i class="bi bi-robot"></i> Tools AI</span>
                        <span class="abt-badge abt-b2"><i class="bi bi-mortarboard-fill"></i> Untuk Kampus</span>
                        <span class="abt-badge abt-b3"><i class="bi bi-award-fill"></i> Lisensi Resmi</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="abt-values">
        <div class="container">
            <div class="ph-sec-head" data-aos="fade-up">
                <span class="ph-sec-eyebrow"><i class="bi bi-heart-fill"></i> Kenapa Phoenix Digital</span>
                <h2 class="ph-sec-title">Alasan pelanggan mempercayai kami</h2>
                <p class="ph-sec-sub">Komitmen kami sederhana: layanan yang jujur, cepat, dan menguntungkan Anda.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up">
                    <div class="abt-card">
                        <span class="abt-card-ic"><i class="bi bi-shield-check"></i></span>
                        <h3>Terpercaya &amp; Amanah</h3>
                        <p>Produk sesuai deskripsi dan transaksi yang aman. Kepercayaan Anda adalah prioritas kami.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="abt-card">
                        <span class="abt-card-ic"><i class="bi bi-lightning-charge-fill"></i></span>
                        <h3>Respons Cepat</h3>
                        <p>Pesanan dan pertanyaan dilayani secepat mungkin pada jam operasional kami.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="abt-card">
                        <span class="abt-card-ic"><i class="bi bi-patch-check-fill"></i></span>
                        <h3>Bergaransi</h3>
                        <p>Setiap akun bergaransi selama masa aktif paket. Ada kendala? Kami bantu selesaikan.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="abt-card">
                        <span class="abt-card-ic"><i class="bi bi-tags-fill"></i></span>
                        <h3>Harga Hemat</h3>
                        <p>Nikmati paket bundling dan flash sale untuk mendapatkan tools premium dengan harga terbaik.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="abt-stats">
        <div class="container">
            <div class="abt-stats-inner" data-aos="fade-up">
                <div class="row gy-4">
                    <div class="col-6 col-lg-3">
                        <div class="abt-stat">
                            <i class="bi bi-emoji-smile-fill"></i>
                            <div class="abt-num">
                                <span data-purecounter-start="0" data-purecounter-end="800"
                                    data-purecounter-duration="2" class="purecounter">0</span><span
                                    class="abt-plus">+</span>
                            </div>
                            <p>Pelanggan Puas</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="abt-stat">
                            <i class="bi bi-box-seam-fill"></i>
                            <div class="abt-num">
                                <span data-purecounter-start="0" data-purecounter-end="120"
                                    data-purecounter-duration="2" class="purecounter">0</span><span
                                    class="abt-plus">+</span>
                            </div>
                            <p>Produk &amp; Tools</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="abt-stat">
                            <i class="bi bi-bag-check-fill"></i>
                            <div class="abt-num">
                                <span data-purecounter-start="0" data-purecounter-end="1500"
                                    data-purecounter-duration="2" class="purecounter">0</span><span
                                    class="abt-plus">+</span>
                            </div>
                            <p>Transaksi Selesai</p>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="abt-stat">
                            <i class="bi bi-mortarboard-fill"></i>
                            <div class="abt-num">
                                <span data-purecounter-start="0" data-purecounter-end="25"
                                    data-purecounter-duration="2" class="purecounter">0</span><span
                                    class="abt-plus">+</span>
                            </div>
                            <p>Kampus &amp; Instansi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kampus & Instansi -->
    <section class="abt-campus-wrap">
        <div class="container">
            <div class="abt-campus" data-aos="fade-up">
                <div class="abt-campus-ic"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="abt-campus-body">
                    <h3>Untuk Kampus &amp; Instansi</h3>
                    <p>Butuh banyak akun untuk kelas, laboratorium, atau tim riset? Kami melayani pemesanan kolektif
                        dengan harga khusus — amanah dan respons cepat.</p>
                </div>
                <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20booking%20untuk%20kampus%2Finstansi."
                    target="_blank" rel="noopener" class="ph-empty-btn flex-shrink-0"><i class="bi bi-whatsapp"></i>
                    Booking via WhatsApp</a>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="abt-cta">
        <div class="container" data-aos="fade-up">
            <span class="ph-sec-eyebrow"><i class="bi bi-rocket-takeoff-fill"></i> Mulai Sekarang</span>
            <h2>Siap tingkatkan produktivitas Anda?</h2>
            <p>Jelajahi katalog akun premium &amp; tools AI kami, atau tanyakan apa pun langsung ke admin.</p>
            <div class="abt-actions">
                <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Lihat Produk</a>
                <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20bertanya."
                    target="_blank" rel="noopener" class="ph-empty-btn ghost"><i class="bi bi-whatsapp"></i> Chat
                    Admin</a>
            </div>
        </div>
    </section>
</div>
