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
            transform: translateY(-2px);
            filter: brightness(1.04);
            box-shadow: 0 10px 24px rgba(242, 101, 34, .38);
            color: #fff;
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
    </style>
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
                <div class="container" data-aos="fade-up" data-aos-delay="100" wire:ignore.self>
                    <div class="row g-4">
                        @forelse ($bundlings as $item)
                            @php
                                $durs = $item->durations ?? [];
                                $old = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_awal);
                                $now = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_bundling);
                            @endphp
                            <div class="col-12 col-md-6 col-xl-4" wire:key="bundling-{{ $item->id }}" data-aos="fade-up">
                                <div class="bdl-card">
                                    <div class="bdl-eyebrow"><i class="bi bi-box2-heart-fill"></i> Paket Bundling</div>
                                    <h2 class="bdl-title">{{ $item->nama_paket }}</h2>

                                    @if (trim((string) $item->deskripsi) !== '')
                                        <div class="bdl-desc">{{ $item->deskripsi }}</div>
                                    @endif

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
                                <div class="text-center alert alert-warning rounded-4">
                                    <i class="bi bi-search"></i>
                                    <p class="mt-2 mb-0">Tidak ada produk yang ditemukan</p>
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
