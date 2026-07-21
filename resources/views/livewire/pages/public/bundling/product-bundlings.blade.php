<main class="main">
    <style>
        /* Tema brand (oranye) — clean, rapi, tidak norak */
        .bdl-card {
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #f1e6d8;
            border-radius: 22px;
            padding: 1.7rem 1.4rem;
            box-shadow: 0 12px 30px rgba(242, 101, 34, .1);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .bdl-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 46px rgba(242, 101, 34, .18);
            border-color: rgba(242, 101, 34, .3);
        }

        .bdl-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: center;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #f26522;
            background: #fff8f1;
            border: 1px solid #f1e6d8;
            padding: 5px 13px;
            border-radius: 999px;
            margin-bottom: .85rem;
        }

        .bdl-title {
            color: #23272f;
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: .5rem;
            line-height: 1.25;
            text-align: center;
        }

        .bdl-desc {
            color: #6b7280;
            font-size: .9rem;
            line-height: 1.6;
            margin-bottom: 1.1rem;
            white-space: pre-line;
            text-align: center;
        }

        .bdl-promo {
            display: inline-block;
            background: linear-gradient(135deg, #fba919, #f26522);
            color: #fff;
            font-weight: 800;
            letter-spacing: .5px;
            padding: .6rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(242, 101, 34, .35);
        }

        .bdl-price-old {
            color: #9aa1ab;
            text-decoration: line-through;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .bdl-price-now {
            color: #f26522;
            font-weight: 800;
            font-size: 2rem;
            line-height: 1;
        }

        .bdl-price-unit {
            color: #94a3b8;
            font-size: .9rem;
        }

        .bdl-incl {
            border: 1px solid #f1e6d8;
            border-radius: 16px;
            background: #fff8f1;
            padding: 1rem 1.1rem;
        }

        .bdl-incl-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #f26522;
            margin-bottom: .5rem;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .bdl-incl-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .5rem;
            padding: .45rem 0;
            border-bottom: 1px dashed #f1e6d8;
        }

        .bdl-incl-row:last-child {
            border-bottom: 0;
        }

        .bdl-incl-name {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            color: #23272f;
            font-size: .9rem;
        }

        .bdl-incl-name i {
            color: #16a34a;
            flex-shrink: 0;
        }

        .bdl-dur-badge {
            background: #fff;
            color: #f26522;
            border: 1px solid #f1e6d8;
            font-weight: 700;
            font-size: .75rem;
            padding: .25rem .65rem;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bdl-order-btn {
            width: 100%;
            border: 0;
            background: linear-gradient(135deg, #fba919, #f26522);
            color: #fff;
            font-weight: 700;
            font-size: 1.02rem;
            padding: .85rem;
            border-radius: 14px;
            transition: all .18s ease;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(242, 101, 34, .3);
        }

        .bdl-order-btn:hover {
            background: linear-gradient(135deg, #fba919, #f26522);
            color: #fff;
            transform: translateY(-2px);
            filter: brightness(1.04);
            box-shadow: 0 10px 24px rgba(242, 101, 34, .38);
        }

        .bdl-order-btn:disabled {
            opacity: .8;
            cursor: default;
        }

        .bdl-foot {
            font-size: .8rem;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }

        /* Tombol pesan sejajar ikon (animasi hover seragam dengan tombol Keranjang flash sale) */
        .bdl-order-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Header halaman branded (canvas lebih menarik) */
        .bdl-page-title {
            background: radial-gradient(120% 140% at 0% 0%, #ffe6c9 0%, #fffdfa 58%) !important;
            border-bottom: 1px solid #f1e6d8;
            padding: 30px 0;
        }
        .bdl-page-head .ph-sec-eyebrow { margin-bottom: 8px; }
        .bdl-page-head h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            color: #23272f;
            font-size: clamp(1.6rem, 3vw, 2.3rem);
        }
        .bdl-page-head p { color: #6b7280; margin: 6px 0 0; max-width: 520px; font-size: .95rem; }
        .bdl-page-title .breadcrumbs a { color: #f26522; }
        .bdl-page-title .breadcrumbs a:hover { color: #f4772b; }
        .bdl-page-title .breadcrumbs .current { color: #6b7280; }

        /* ===== Empty state: vector + animasi ===== */
        .bdl-empty { text-align: center; padding: 30px 16px 20px; max-width: 480px; margin: 0 auto; }
        .bdl-empty-art { margin-bottom: 6px; }
        .bdl-empty-art svg { width: 260px; max-width: 82%; height: auto; overflow: visible; }
        .bdl-empty-title { font-family: 'Poppins', sans-serif; font-weight: 800; color: #23272f; font-size: 1.35rem; margin: 4px 0 6px; }
        .bdl-empty-sub { color: #6b7280; font-size: .95rem; line-height: 1.6; margin: 0 auto 18px; max-width: 400px; }
        .bdl-empty-btn { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #fba919, #f26522); color: #fff; font-weight: 700; padding: .7rem 1.4rem; border-radius: 12px; box-shadow: 0 8px 20px rgba(242, 101, 34, .28); text-decoration: none; border: 0; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
        .bdl-empty-btn:hover { color: #fff; transform: translateY(-2px); filter: brightness(1.04); box-shadow: 0 10px 24px rgba(242, 101, 34, .36); }

        .be-box { animation: be-bob 3.4s ease-in-out infinite; }
        .be-lid { animation: be-lidfloat 3.4s ease-in-out infinite; }
        .be-glow, .be-shadow, .be-spark { transform-box: fill-box; transform-origin: center; }
        .be-glow { animation: be-glowpulse 3.4s ease-in-out infinite; }
        .be-shadow { animation: be-shadowpulse 3.4s ease-in-out infinite; }
        .be-spark { animation: be-twinkle 2s ease-in-out infinite; }
        .be-spark.s2 { animation-delay: .5s; }
        .be-spark.s3 { animation-delay: 1s; }
        .be-spark.s4 { animation-delay: 1.4s; }

        @keyframes be-bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes be-lidfloat { 0%, 100% { transform: translateY(-8px); } 50% { transform: translateY(-16px); } }
        @keyframes be-glowpulse { 0%, 100% { opacity: .45; transform: scale(1); } 50% { opacity: .75; transform: scale(1.08); } }
        @keyframes be-shadowpulse { 0%, 100% { opacity: .16; transform: scaleX(1); } 50% { opacity: .09; transform: scaleX(.82); } }
        @keyframes be-twinkle { 0%, 100% { opacity: .25; transform: scale(.6); } 50% { opacity: 1; transform: scale(1); } }

        @media (prefers-reduced-motion: reduce) {
            .be-box, .be-lid, .be-glow, .be-shadow, .be-spark { animation: none !important; }
            .be-lid { transform: translateY(-10px); }
        }
    </style>
    @include('partials.bundling-deskripsi-style')

    <!-- Page Title -->
    <div class="page-title bdl-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="bdl-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box2-heart-fill"></i> Hemat Lebih</span>
                <h1 class="mb-0">Paket Bundling</h1>
                <p>Gabungan beberapa akun premium dalam satu paket — lebih lengkap &amp; lebih hemat.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Beranda</a></li>
                    <li class="current">Paket Bundling</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <!-- list product -->
    <section style="padding-top: 20px;">
        <div class="container">
            <section style="padding-top: 0;" id="category-header" class="category-header section">
                <div class="container">
                    @if ($search)
                        <div class="mb-4 alert alert-info" role="alert">
                            Menampilkan hasil pencarian untuk: <strong>{{ $search }}</strong>
                            <button wire:click="$set('search', '')" class="btn-close float-end"
                                aria-label="Clear search"></button>
                        </div>
                    @endif
                </div>
            </section>

            <section id="best-sellers" class="best-sellers section">
                <div class="container" wire:ignore.self>
                    <div class="row g-4 justify-content-center">
                        @forelse ($bundlings as $item)
                            @php
                                $durs = $item->durations ?? [];
                                $old = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_awal);
                                $now = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_bundling);
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4" wire:key="bundling-{{ $item->id }}">
                                <div class="bdl-card">
                                    {{-- Header otomatis dari data — nama & pills seragam. --}}
                                    @php $__prod = collect([1, 2, 3, 4, 5])->map(fn ($i) => $item->{'product'.$i})->filter()->map->nama_akun->all(); @endphp
                                    @include('partials.bundling-header', ['produk' => $__prod, 'nama' => $item->nama_paket, 'nomor' => $loop->iteration])

                                    @include('partials.bundling-deskripsi', ['teks' => $item->deskripsi])

                                    <div class="text-center mb-3">
                                        <span class="bdl-promo">PROMO HARI INI!</span>
                                    </div>

                                    <div class="text-center mb-3">
                                        @if ($old > $now && $old > 0)
                                            <div class="bdl-price-old">Rp {{ number_format($old, 0, ',', '.') }}</div>
                                        @endif
                                        <div>
                                            <span class="bdl-price-now">Rp {{ number_format($now, 0, ',', '.') }}</span>
                                            <span class="bdl-price-unit">/ paket</span>
                                        </div>
                                    </div>

                                    {{-- Akun yang termasuk paket + durasinya --}}
                                    <div class="bdl-incl mb-3">
                                        <div class="bdl-incl-title"><i class="bi bi-box-seam"></i> Termasuk dalam paket</div>
                                        @foreach ([1, 2, 3, 4, 5] as $i)
                                            @php $product = $item->{'product'.$i}; @endphp
                                            @if ($product)
                                                @php $dur = $durs['product_'.$i] ?? null; @endphp
                                                <div class="bdl-incl-row">
                                                    <span class="bdl-incl-name">
                                                        <i class="bi bi-check-circle-fill"></i>{{ $product->nama_akun }}
                                                    </span>
                                                    <span class="bdl-dur-badge">
                                                        {{ (int) ($dur['value'] ?? 1) }} {{ ucfirst($dur['type'] ?? 'bulan') }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <button type="button" class="bdl-order-btn mt-auto"
                                        wire:click="addToCart('{{ $item->id }}')"
                                        wire:loading.attr="disabled" wire:target="addToCart('{{ $item->id }}')">
                                        <span wire:loading.remove wire:target="addToCart('{{ $item->id }}')"><i class="bi bi-cart-plus"></i> Pesan Sekarang!</span>
                                        <span wire:loading wire:target="addToCart('{{ $item->id }}')"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                                    </button>

                                    <p class="bdl-foot mt-3 mb-0">🎉 <b>Jangan lewatkan kesempatan terbatas ini!</b> Promo bisa berakhir kapan saja.</p>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="bdl-empty">
                                    <div class="bdl-empty-art">
                                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                                            aria-label="Belum ada paket bundling">
                                            <defs>
                                                <radialGradient id="beGlow" cx="50%" cy="50%" r="50%">
                                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                                </radialGradient>
                                                <linearGradient id="beLid" x1="0" y1="0" x2="1" y2="1">
                                                    <stop offset="0%" stop-color="#fbc25a" />
                                                    <stop offset="100%" stop-color="#f26522" />
                                                </linearGradient>
                                                <linearGradient id="beLeft" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#fdc069" />
                                                    <stop offset="100%" stop-color="#f7a23e" />
                                                </linearGradient>
                                                <linearGradient id="beRight" x1="0" y1="0" x2="0" y2="1">
                                                    <stop offset="0%" stop-color="#f4772b" />
                                                    <stop offset="100%" stop-color="#e15a18" />
                                                </linearGradient>
                                            </defs>

                                            <ellipse class="be-glow" cx="120" cy="112" rx="80" ry="80" fill="url(#beGlow)" />
                                            <ellipse class="be-shadow" cx="120" cy="182" rx="60" ry="8" fill="#e15a18" />

                                            <g transform="translate(46,74)"><path class="be-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                                            <g transform="translate(198,92)"><path class="be-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                            <g transform="translate(190,142)"><path class="be-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                                            <g transform="translate(52,146)"><path class="be-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                                            <g class="be-box">
                                                <path d="M66,100 L120,122 L120,176 L66,150 Z" fill="url(#beLeft)" />
                                                <path d="M174,100 L120,122 L120,176 L174,150 Z" fill="url(#beRight)" />
                                                <path d="M120,78 L174,100 L120,122 L66,100 Z" fill="#ffe9d0" />
                                                <path d="M120,86 L162,103 L120,120 L78,103 Z" fill="#f6d3ac" />
                                                <path d="M120,78 L174,100 L120,122 L66,100 Z" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="1.5" />
                                            </g>

                                            <g class="be-lid">
                                                <path d="M120,30 L166,50 L120,70 L74,50 Z" fill="url(#beLid)" />
                                                <path d="M74,50 L74,58 L120,78 L120,70 Z" fill="#e15a18" />
                                                <path d="M166,50 L166,58 L120,78 L120,70 Z" fill="#f4772b" />
                                                <circle cx="120" cy="42" r="6" fill="#fff3e0" />
                                                <circle cx="120" cy="42" r="6" fill="none" stroke="#f26522" stroke-opacity=".4" stroke-width="1.5" />
                                            </g>
                                        </svg>
                                    </div>
                                    @if ($search)
                                        <h3 class="bdl-empty-title">Paket tidak ditemukan</h3>
                                        <p class="bdl-empty-sub">Tidak ada paket bundling yang cocok dengan pencarian
                                            <b>"{{ $search }}"</b>. Coba kata kunci lain, ya.</p>
                                        <button type="button" class="bdl-empty-btn" wire:click="$set('search', '')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                                        </button>
                                    @else
                                        <h3 class="bdl-empty-title">Belum ada paket bundling</h3>
                                        <p class="bdl-empty-sub">Saat ini belum ada paket bundling yang aktif. Sementara itu,
                                            cek koleksi produk satuan kami, yuk!</p>
                                        <a href="{{ url('/shop') }}" class="bdl-empty-btn">
                                            <i class="bi bi-bag"></i> Lihat Produk Satuan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section><!-- /Best Sellers Section -->
        </div>
    </section>
    <!-- end list product -->
</main>
