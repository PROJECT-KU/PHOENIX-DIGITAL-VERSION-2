<div class="tk-page">
    <style>
        /* ===== Halaman Tentang Kami =====
           Bahasa visual sama dengan Beranda, Shop, Bundling, dan Layanan:
           kartu judul bersama (.ph-page-title), kartu putih bersudut 18px
           dengan warna per kartu (--c), ubin ikon berwarna, sapuan pojok
           seperti Cara Pesan. Kelas tk-*: aturan .abt-* di
           public-custom-styles.css server beku, jadi desain ini tidak
           bergantung padanya — kecuali ilustrasi (.abt-visual/.abt-illus)
           yang memang sudah bagus beserta animasinya, dan tetap dipakai. */
        .tk-page { --tk-ink: #1c1f26; --tk-muted: #64748b; --tk-line: #eceff4; --tk-font: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .tk-section { padding: 18px 0 64px; }
        .tk-blok { margin-top: 56px; }

        /* Ubin ikon bersama — glif tunggal selalu display:block + line-height:1
           supaya benar-benar di tengah ubinnya. */
        .tk-ubin {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 14px; font-size: 1.15rem;
            background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c);
        }
        .tk-ubin.is-padat {
            background: linear-gradient(140deg, var(--c), color-mix(in srgb, var(--c) 60%, #fff)); color: #fff;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--c) 85%, transparent);
        }
        .tk-ubin.is-besar { width: 56px; height: 56px; border-radius: 18px; font-size: 1.45rem; }
        .tk-ubin i.bi, .tk-ubin i.bi::before { display: block; line-height: 1; }

        .tk-kepala { margin-bottom: 22px; }
        .tk-kepala h2 {
            margin: 10px 0 6px; font-family: var(--tk-font); font-weight: 800; letter-spacing: -.02em;
            font-size: clamp(1.35rem, 1.05rem + 1.1vw, 1.8rem); color: var(--tk-ink);
        }
        .tk-kepala p { margin: 0; max-width: 640px; color: var(--tk-muted); font-size: .95rem; line-height: 1.65; }

        /* Tombol */
        .tk-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            height: 46px; padding: 0 20px; border-radius: 13px; border: 1.5px solid transparent;
            font-weight: 700; font-size: .9rem; text-decoration: none; white-space: nowrap;
            transition: background .18s, border-color .18s, color .18s, transform .18s, filter .18s;
        }
        .tk-btn i.bi, .tk-btn i.bi::before { display: block; line-height: 1; font-size: 1.05rem; }
        .tk-btn:hover { transform: translateY(-1px); }
        .tk-btn.is-utama {
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            box-shadow: 0 12px 22px -12px rgba(242, 101, 34, .8);
        }
        .tk-btn.is-utama:hover { color: #fff; filter: brightness(1.05); }
        .tk-btn.is-wa { background: #16a34a; color: #fff; box-shadow: 0 12px 22px -12px rgba(22, 163, 74, .85); }
        .tk-btn.is-wa:hover { background: #15803d; color: #fff; }
        .tk-btn.is-garis { background: #fff; color: var(--tk-ink); border-color: #e5e0d8; }
        .tk-btn.is-garis:hover { border-color: #f26522; color: #c2410c; }
        .tk-btn.is-garis .bi-whatsapp { color: #16a34a; }

        /* ===== Kartu pembuka ===== */
        .tk-intro {
            display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); gap: 32px; align-items: center;
            padding: 32px 34px; border: 1px solid #f7dcc6; border-radius: 22px; overflow: hidden;
            background:
                radial-gradient(60% 90% at 100% 0%, rgba(251, 169, 25, .14), transparent 60%),
                linear-gradient(160deg, #fff7ef 0%, #fff 72%);
        }
        .tk-intro h2 {
            margin: 10px 0; font-family: var(--tk-font); font-weight: 800; letter-spacing: -.02em;
            font-size: clamp(1.45rem, 1.1rem + 1.3vw, 2rem); line-height: 1.25; color: var(--tk-ink);
        }
        .tk-lead { margin: 0 0 10px; font-size: 1rem; line-height: 1.7; color: #334155; }
        .tk-teks { margin: 0; font-size: .93rem; line-height: 1.75; color: #4b5563; }
        .tk-teks b { color: #334155; }
        .tk-janji { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .tk-chip {
            display: inline-flex; align-items: center; gap: 9px; height: 40px; padding: 0 15px 0 7px; border-radius: 99px;
            background: #fff; border: 1px solid color-mix(in srgb, var(--c) 25%, #eceff4);
            color: #334155; font-size: .83rem; font-weight: 700;
        }
        .tk-chip .tk-ubin { width: 28px; height: 28px; border-radius: 10px; font-size: .85rem; }
        .tk-aksi { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .tk-visual { display: flex; align-items: center; justify-content: center; min-width: 0; }
        .tk-visual .abt-visual { margin: 0; width: 100%; }

        /* ===== Kartu nilai ===== */
        .tk-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .tk-kartu {
            position: relative; display: flex; flex-direction: column; gap: 12px; padding: 22px 20px;
            background: #fff; border: 1px solid var(--tk-line); border-radius: 18px; overflow: hidden;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }
        .tk-kartu::before {
            content: ""; position: absolute; top: -38px; right: -38px; width: 108px; height: 108px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 10%, transparent); transition: transform .35s ease;
        }
        .tk-kartu:hover {
            transform: translateY(-4px); border-color: color-mix(in srgb, var(--c) 35%, #fff);
            box-shadow: 0 18px 34px -22px color-mix(in srgb, var(--c) 75%, transparent);
        }
        .tk-kartu:hover::before { transform: scale(1.25); }
        .tk-kartu > * { position: relative; }
        .tk-kartu h3 { margin: 0; font-family: var(--tk-font); font-weight: 800; font-size: 1rem; line-height: 1.35; color: var(--tk-ink); }
        .tk-kartu p { margin: 0; font-size: .86rem; line-height: 1.6; color: var(--tk-muted); }

        /* ===== Angka ===== */
        .tk-angka {
            display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; padding: 26px;
            border: 1px solid #f7dcc6; border-radius: 22px;
            background: linear-gradient(160deg, #fff7ef 0%, #fff 70%);
        }
        .tk-stat {
            display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px;
            padding: 18px 12px; background: #fff; border: 1px solid var(--tk-line); border-radius: 16px;
            box-shadow: 0 12px 26px -24px rgba(15, 23, 42, .5);
        }
        .tk-stat-num {
            font-family: var(--tk-font); font-weight: 800; letter-spacing: -.02em; line-height: 1;
            font-size: clamp(1.5rem, 1.2rem + 1vw, 2rem); color: var(--c);
        }
        .tk-stat p { margin: 0; font-size: .83rem; font-weight: 600; color: var(--tk-muted); }

        /* ===== Kampus & instansi ===== */
        .tk-kampus {
            display: flex; align-items: center; flex-wrap: wrap; gap: 20px; padding: 26px 28px; border-radius: 22px;
            border: 1px solid color-mix(in srgb, var(--c) 22%, #eceff4);
            background: linear-gradient(135deg, color-mix(in srgb, var(--c) 9%, #fff) 0%, #fff 68%);
        }
        .tk-kampus-isi { flex: 1 1 320px; min-width: 0; }
        .tk-kampus h3 { margin: 0 0 6px; font-family: var(--tk-font); font-weight: 800; font-size: 1.15rem; color: var(--tk-ink); }
        .tk-kampus p { margin: 0; font-size: .9rem; line-height: 1.65; color: #475569; }

        /* ===== Ajakan penutup ===== */
        .tk-cta {
            position: relative; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 20px 28px; padding: 30px 34px; border-radius: 22px; overflow: hidden; color: #fff;
            background:
                radial-gradient(70% 130% at 100% 0%, rgba(251, 169, 25, .35), transparent 60%),
                linear-gradient(135deg, #23272f 0%, #3a2a20 100%);
        }
        .tk-cta-kiri { flex: 1 1 380px; min-width: 0; }
        .tk-cta h2 { margin: 10px 0 6px; font-family: var(--tk-font); font-weight: 800; font-size: 1.5rem; color: #fff; }
        .tk-cta p { margin: 0; color: rgba(255, 255, 255, .8); font-size: .93rem; line-height: 1.6; }
        .tk-cta .ph-sec-eyebrow { background: rgba(251, 169, 25, .16); color: #fbbf24; border-color: transparent; }
        .tk-cta-aksi { display: flex; flex-wrap: wrap; gap: 10px; }
        .tk-cta .tk-btn.is-garis { background: rgba(255, 255, 255, .1); color: #fff; border-color: rgba(255, 255, 255, .3); }
        .tk-cta .tk-btn.is-garis:hover { background: rgba(255, 255, 255, .18); color: #fff; border-color: rgba(255, 255, 255, .5); }

        @media (max-width: 991.98px) {
            .tk-intro { grid-template-columns: minmax(0, 1fr); }
            .tk-grid, .tk-angka { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .tk-blok { margin-top: 40px; }
            .tk-intro { padding: 22px 18px; gap: 22px; }
            .tk-intro-aksi .tk-btn, .tk-aksi .tk-btn, .tk-cta-aksi .tk-btn { flex: 1 1 auto; }
            .tk-angka { padding: 16px; gap: 10px; }
            .tk-stat { padding: 14px 8px; }
            .tk-kampus { padding: 20px 18px; }
            .tk-kampus .tk-btn { width: 100%; }
            .tk-cta { padding: 24px 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .tk-kartu, .tk-kartu:hover, .tk-btn:hover { transform: none; }
            .tk-kartu::before { transition: none; }
        }
    </style>

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

    <section class="tk-section">
        <div class="container">

            {{-- Kartu pembuka --}}
            <div class="tk-intro">
                <div>
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Siapa Kami</span>
                    <h2>Akses tools premium jadi mudah, aman, &amp; terjangkau</h2>
                    <p class="tk-lead">Phoenix Digital membantu pelajar, peneliti, dan profesional mendapatkan akun
                        premium, lisensi resmi, serta tools AI untuk mempercepat riset dan pekerjaan sehari-hari.</p>
                    <p class="tk-teks">Kami mengutamakan layanan yang <b>terpercaya dan amanah</b> dengan
                        <b>respons cepat</b>. Dari kebutuhan perorangan hingga pemesanan kolektif untuk
                        <b>kampus &amp; instansi</b>, semuanya kami layani dengan harga yang bersahabat.</p>

                    <div class="tk-janji">
                        @foreach ($janji as $j)
                            <span class="tk-chip" style="--c: {{ $j['warna'] }}">
                                <span class="tk-ubin"><i class="bi {{ $j['ikon'] }}"></i></span> {{ $j['teks'] }}
                            </span>
                        @endforeach
                    </div>

                    <div class="tk-aksi">
                        <a class="tk-btn is-utama" href="{{ route('shop.index') }}"><i class="bi bi-bag"></i> Mulai Belanja</a>
                        <a class="tk-btn is-garis" href="{{ $waTanya }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Hubungi Kami
                        </a>
                    </div>
                </div>

                <div class="tk-visual">
                    <div class="abt-visual">
                        <div class="abt-visual-glow"></div>
                        @php $aboutImg = public_path('storage/img/about-research.png'); @endphp
                        @if (file_exists($aboutImg))
                            <img loading="lazy" src="{{ asset('storage/img/about-research.png') }}?v={{ filemtime($aboutImg) }}"
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

            {{-- Alasan mempercayai kami --}}
            <div class="tk-blok">
                <div class="tk-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-heart-fill"></i> Kenapa Phoenix Digital</span>
                    <h2>Alasan pelanggan mempercayai kami</h2>
                    <p>Komitmen kami sederhana: layanan yang jujur, cepat, dan menguntungkan Anda.</p>
                </div>
                <div class="tk-grid">
                    @foreach ($nilai as $n)
                        <article class="tk-kartu" style="--c: {{ $n['warna'] }}">
                            <span class="tk-ubin is-padat"><i class="bi {{ $n['ikon'] }}"></i></span>
                            <h3>{{ $n['judul'] }}</h3>
                            <p>{{ $n['teks'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Angka --}}
            <div class="tk-blok">
                <div class="tk-kepala">
                    <span class="ph-sec-eyebrow"><i class="bi bi-graph-up-arrow"></i> Sejauh Ini</span>
                    <h2>Phoenix Digital dalam angka</h2>
                    <p>Dipercaya perorangan sampai kampus &amp; instansi.</p>
                </div>
                <div class="tk-angka">
                    @foreach ($angka as $a)
                        <div class="tk-stat" style="--c: {{ $a['warna'] }}">
                            <span class="tk-ubin"><i class="bi {{ $a['ikon'] }}"></i></span>
                            <div class="tk-stat-num">
                                <span data-purecounter-start="0" data-purecounter-end="{{ $a['nilai'] }}"
                                    data-purecounter-duration="2" class="purecounter">{{ $a['nilai'] }}</span>+
                            </div>
                            <p>{{ $a['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kampus & instansi --}}
            <div class="tk-blok">
                <div class="tk-kampus" style="--c: #4f46e5">
                    <span class="tk-ubin is-padat is-besar"><i class="bi bi-mortarboard-fill"></i></span>
                    <div class="tk-kampus-isi">
                        <h3>Untuk Kampus &amp; Instansi</h3>
                        <p>Butuh banyak akun untuk kelas, laboratorium, atau tim riset? Kami melayani pemesanan
                            kolektif dengan harga khusus — amanah dan respons cepat.</p>
                    </div>
                    <a class="tk-btn is-wa" href="{{ $waKampus }}" target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i> Booking via WhatsApp
                    </a>
                </div>
            </div>

            {{-- Ajakan penutup --}}
            <div class="tk-blok">
                <div class="tk-cta">
                    <div class="tk-cta-kiri">
                        <span class="ph-sec-eyebrow"><i class="bi bi-rocket-takeoff-fill"></i> Mulai Sekarang</span>
                        <h2>Siap tingkatkan produktivitas Anda?</h2>
                        <p>Jelajahi katalog akun premium &amp; tools AI kami, atau tanyakan apa pun langsung ke admin.</p>
                    </div>
                    <div class="tk-cta-aksi">
                        <a class="tk-btn is-utama" href="{{ route('shop.index') }}"><i class="bi bi-bag"></i> Lihat Produk</a>
                        <a class="tk-btn is-garis" href="{{ $waTanya }}" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i> Chat Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
