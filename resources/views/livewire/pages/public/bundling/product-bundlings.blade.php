<main class="main">
    <style>
        .bdl-card {
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #ffffff, #fbfcff);
            border: 1px solid #eef0f6;
            border-radius: 22px;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 12px 30px rgba(76, 29, 149, .08);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .bdl-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 42px rgba(76, 29, 149, .15);
        }

        .bdl-title {
            color: #1e1b7a;
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .bdl-desc {
            color: #334155;
            font-size: .95rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
            white-space: pre-line;
        }

        .bdl-promo {
            display: inline-block;
            background: linear-gradient(135deg, #fcd34d, #fbbf24);
            color: #b91c1c;
            font-weight: 800;
            letter-spacing: .5px;
            padding: .7rem 1.6rem;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(251, 191, 36, .45);
        }

        .bdl-price-old {
            color: #ef4444;
            text-decoration: line-through;
            font-weight: 700;
            font-size: 1.35rem;
        }

        .bdl-price-now {
            color: #15803d;
            font-weight: 800;
            font-size: 2.1rem;
            line-height: 1;
        }

        .bdl-price-unit {
            color: #94a3b8;
            font-size: .9rem;
        }

        .bdl-incl {
            border: 1px solid #eef0f6;
            border-radius: 16px;
            background: linear-gradient(135deg, #f8faff, #f4f7ff);
            padding: 1rem 1.1rem;
        }

        .bdl-incl-title {
            font-size: .74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #7c3aed;
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
            border-bottom: 1px dashed #e6e8f2;
        }

        .bdl-incl-row:last-child {
            border-bottom: 0;
        }

        .bdl-incl-name {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            color: #1e293b;
            font-size: .9rem;
        }

        .bdl-incl-name i {
            color: #22c55e;
            flex-shrink: 0;
        }

        .bdl-dur-badge {
            background: #ede9fe;
            color: #6d28d9;
            font-weight: 700;
            font-size: .75rem;
            padding: .25rem .65rem;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .bdl-order-btn {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-weight: 600;
            font-size: 1.05rem;
            padding: .9rem;
            border-radius: 14px;
            transition: all .18s ease;
        }

        .bdl-order-btn:hover {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, .08), rgba(78, 70, 229, .04));
            color: #4e46e5;
        }

        .bdl-foot {
            font-size: .82rem;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }
    </style>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Shoping</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Home</a></li>
                    <li class="current">Paket Bundlings</li>
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
                                        <span wire:loading.remove wire:target="addToCart('{{ $item->id }}')">Pesan Sekarang!</span>
                                        <span wire:loading wire:target="addToCart('{{ $item->id }}')">Memproses...</span>
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
