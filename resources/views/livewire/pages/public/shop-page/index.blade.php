<main class="main">
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-bag-fill"></i> Katalog</span>
                <h1>Shop</h1>
                <p>Pilihan akun premium &amp; tools AI untuk riset dan produktivitas Anda.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Beranda</a></li>
                    <li class="current">Shop</li>
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

                <!-- Category Product List Section -->
                <section style="padding-top: 0;" id="category-product-list" class="category-product-list section">
                    <div class="container">
                        {{-- Filter & urutkan (opsional) --}}
                        <div class="shop-filter">
                            <div class="shop-filter-controls">
                                @if (count($categories))
                                    <select wire:model.live="tipe" class="shop-select">
                                        <option value="">Semua Kategori</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c }}">{{ $c }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <select wire:model.live="sortBy" class="shop-select">
                                    <option value="">Urutkan: Terbaru</option>
                                    <option value="termurah">Harga: Termurah</option>
                                    <option value="termahal">Harga: Termahal</option>
                                    <option value="nama">Nama: A–Z</option>
                                    <option value="terlama">Terlama</option>
                                </select>
                                @if ($tipe || $sortBy)
                                    <button type="button" wire:click="resetFilters" class="shop-reset"><i class="bi bi-x-circle"></i> Reset</button>
                                @endif
                            </div>
                            <div class="shop-filter-count">{{ $products->total() }} produk</div>
                        </div>

                        <div class="row g-3 g-lg-4">
                            @forelse ($products as $item)
                                @php
                                    $bestDiscount = $this->getBestDiscount($item->id);
                                    $isFlash = $bestDiscount && ($bestDiscount['promo']->tipe_promo ?? null) === 'flash_sale';
                                    // Produk JASA: harga per bulan = 0. Pakai harga paket terkecil (per pengecekan).
                                    $isJasa = (bool) $item->butuh_file;
                                    $originalPrice = $isJasa ? (int) ($item->hargaSekali() ?? 0) : (int) $item->harga_perbulan;
                                    if ($bestDiscount) {
                                        if ($bestDiscount['type'] === 'persen') {
                                            $discountedPrice = (int) round(
                                                $originalPrice - ($originalPrice * $bestDiscount['value']) / 100,
                                            );
                                        } else {
                                            $discountedPrice = (int) max(0, $originalPrice - $bestDiscount['value']);
                                        }
                                    } else {
                                        $discountedPrice = $originalPrice;
                                    }
                                @endphp
                                <div class="col-6 col-md-4 col-lg-3" wire:key="product-{{ $item->id }}">
                                    <div class="fs-card">
                                        <div class="fs-card-media">
                                            @if ($item->image)
                                                <img src="{{ asset('storage/img/Product/' . $item->image) }}"
                                                    alt="{{ $item->nama_akun }}">
                                            @else
                                                <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                    alt="{{ $item->nama_akun }}">
                                            @endif

                                            @if ($bestDiscount)
                                                @if ($isFlash)
                                                    <span class="fs-badge fs-badge-flash"><i
                                                            class="bi bi-lightning-charge-fill"></i>
                                                        @if ($bestDiscount['type'] === 'persen')
                                                            Diskon s.d. {{ number_format($bestDiscount['value'], 0) }}%
                                                        @else
                                                            Diskon s.d. Rp{{ number_format($bestDiscount['value'], 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="fs-badge">
                                                        @if ($bestDiscount['type'] === 'persen')
                                                            @if ($bestDiscount['member_value'] != $bestDiscount['non_member_value'])
                                                                Diskon {{ number_format($bestDiscount['non_member_value'], 0) }}–{{ number_format($bestDiscount['member_value'], 0) }}%
                                                            @else
                                                                Diskon {{ number_format($bestDiscount['value'], 0) }}%
                                                            @endif
                                                        @else
                                                            Diskon Rp{{ number_format($bestDiscount['value'], 0, ',', '.') }}
                                                        @endif
                                                    </span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="fs-card-body">
                                            <a href="{{ route('shop.detail-product', $item->id) }}"
                                                class="fs-name">{{ $item->nama_akun }}</a>

                                            <div class="fs-price">
                                                @if ($isJasa)
                                                    <small class="text-muted me-1">Mulai</small>
                                                @endif
                                                <span class="fs-price-sale">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                                @if ($discountedPrice < $originalPrice)
                                                    <span class="fs-price-orig">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                                @endif
                                                <small>{{ $isJasa ? '/cek' : '/bln' }}</small>
                                            </div>

                                            <div class="fs-actions">
                                                <button type="button" wire:click="openDuration('{{ $item->id }}')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="openDuration('{{ $item->id }}')" class="fs-btn-cart">
                                                    <span wire:loading.remove
                                                        wire:target="openDuration('{{ $item->id }}')">
                                                        @if ($isJasa)
                                                            {{-- Jasa: harga ditentukan di halaman produk (unggah file / add-on) --}}
                                                            <i class="bi bi-sliders"></i> Atur Pesanan
                                                        @else
                                                            <i class="bi bi-cart-plus"></i> Keranjang
                                                        @endif
                                                    </span>
                                                    <span wire:loading wire:target="openDuration('{{ $item->id }}')"><span
                                                            class="spinner-border spinner-border-sm"></span></span>
                                                </button>
                                                <a href="{{ route('shop.detail-product', $item->id) }}"
                                                    class="fs-btn-view">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    {{-- Empty state beranimasi — seragam dengan halaman Bundling,
                                         menggantikan kotak peringatan datar yang terasa seperti error. --}}
                                    <div class="shp-empty">
                                        <div class="shp-empty-art">
                                            <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                                                aria-label="Produk tidak ditemukan">
                                                <defs>
                                                    <radialGradient id="seGlow" cx="50%" cy="50%" r="50%">
                                                        <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                                        <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                                    </radialGradient>
                                                    <linearGradient id="seBag" x1="0" y1="0" x2="0" y2="1">
                                                        <stop offset="0%" stop-color="#fdc069" />
                                                        <stop offset="100%" stop-color="#f4772b" />
                                                    </linearGradient>
                                                    <linearGradient id="seBagFold" x1="0" y1="0" x2="1" y2="0">
                                                        <stop offset="0%" stop-color="#f7a23e" />
                                                        <stop offset="100%" stop-color="#e15a18" />
                                                    </linearGradient>
                                                </defs>

                                                <ellipse class="se-glow" cx="120" cy="110" rx="80" ry="80" fill="url(#seGlow)" />
                                                <ellipse class="se-shadow" cx="120" cy="180" rx="56" ry="8" fill="#e15a18" />

                                                <g transform="translate(48,70)"><path class="se-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                                                <g transform="translate(196,88)"><path class="se-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                                                <g transform="translate(188,146)"><path class="se-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                                                <g transform="translate(54,150)"><path class="se-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                                                {{-- Kantong belanja --}}
                                                <g class="se-bag">
                                                    <path d="M78,86 L162,86 L154,168 Q153,174 147,174 L93,174 Q87,174 86,168 Z" fill="url(#seBag)" />
                                                    <path d="M78,86 L162,86 L160,104 L80,104 Z" fill="url(#seBagFold)" opacity=".55" />
                                                    <path d="M100,86 L100,72 Q100,56 120,56 Q140,56 140,72 L140,86" fill="none"
                                                        stroke="#ffe9d0" stroke-width="7" stroke-linecap="round" />
                                                    <path d="M78,86 L162,86 L154,168 Q153,174 147,174 L93,174 Q87,174 86,168 Z" fill="none"
                                                        stroke="#ffffff" stroke-opacity=".45" stroke-width="1.5" />
                                                </g>

                                                {{-- Kaca pembesar --}}
                                                <g class="se-lens">
                                                    <circle cx="146" cy="126" r="26" fill="#fff8ef" fill-opacity=".92" stroke="#f26522" stroke-width="5" />
                                                    <path d="M165,145 L182,162" stroke="#e15a18" stroke-width="8" stroke-linecap="round" />
                                                    <path class="se-shine" d="M136,116 Q142,110 150,112" stroke="#ffffff" stroke-width="4"
                                                        stroke-linecap="round" fill="none" opacity=".85" />
                                                </g>
                                            </svg>
                                        </div>

                                        @if ($search)
                                            <h3 class="shp-empty-title">Produk tidak ditemukan</h3>
                                            <p class="shp-empty-sub">Tidak ada produk yang cocok dengan pencarian
                                                <b>"{{ $search }}"</b>. Coba kata kunci lain, ya.</p>
                                            <button type="button" class="shp-empty-btn" wire:click="$set('search', '')">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian
                                            </button>
                                        @elseif ($tipe || $sortBy)
                                            <h3 class="shp-empty-title">Tidak ada yang cocok</h3>
                                            <p class="shp-empty-sub">Filter yang dipilih belum menemukan produk apa pun.
                                                Coba longgarkan filternya, ya.</p>
                                            <button type="button" class="shp-empty-btn"
                                                wire:click="$set('tipe', ''); $set('sortBy', '')">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                                            </button>
                                        @else
                                            <h3 class="shp-empty-title">Belum ada produk</h3>
                                            <p class="shp-empty-sub">Koleksi produk sedang disiapkan. Sementara itu,
                                                lihat paket bundling kami, yuk!</p>
                                            <a href="{{ url('/bundling/product') }}" class="shp-empty-btn">
                                                <i class="bi bi-box2-heart"></i> Lihat Paket Bundling
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        @if ($products->hasPages())
                            <div class="mt-5 ph-pagination">
                                {{ $products->links('pagination.ph') }}
                            </div>
                        @endif
                    </div>
                </section>
            </section>
        </div>
    </section>
    <!-- end list product -->

    {{-- ===== Modal Pilih Durasi (seragam dengan Flash Sale) ===== --}}
    @if ($showDurationModal)
        <div class="fs-modal-overlay" wire:key="shop-dur-modal" wire:click.self="closeDuration">
            <div class="fs-modal">
                <button type="button" class="fs-modal-close" wire:click="closeDuration" aria-label="Tutup"><i
                        class="bi bi-x-lg"></i></button>

                <div class="fs-modal-head">
                    <div class="fs-modal-thumb">
                        @if ($pickProductImage)
                            <img src="{{ asset('storage/img/Product/' . $pickProductImage) }}"
                                alt="{{ $pickProductName }}">
                        @else
                            <i class="bi bi-box-seam"></i>
                        @endif
                    </div>
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow">
                            @if ($pickIsFlash)
                                <i class="bi bi-lightning-charge-fill"></i> Flash Sale
                            @else
                                <i class="bi bi-box-seam"></i> Pilih Paket
                            @endif
                        </span>
                        <h4>{{ $pickProductName }}</h4>
                        <p>Pilih durasi langganan</p>
                    </div>
                </div>

                <div class="fs-modal-options">
                    @foreach ($pickPackages as $p)
                        @php $active = ($pickType === $p['duration_type'] && (int) $pickValue === (int) $p['duration_value']); @endphp
                        <button type="button" class="fs-opt {{ $active ? 'is-active' : '' }}"
                            wire:click="selectPackage('{{ $p['duration_type'] }}', {{ $p['duration_value'] }})">
                            <span class="fs-opt-radio"></span>
                            <span class="fs-opt-info">
                                <span class="fs-opt-label">{{ $p['label'] }}</span>
                                @if (!empty($p['savings']) && $p['savings'] > 0)
                                    <span class="fs-opt-save">Hemat Rp{{ number_format($p['savings'], 0, ',', '.') }}</span>
                                @endif
                            </span>
                            <span class="fs-opt-price">
                                @if (($p['discounted'] ?? $p['price']) < $p['price'])
                                    <span class="fs-opt-orig">Rp{{ number_format($p['price'], 0, ',', '.') }}</span>
                                @endif
                                <span class="fs-opt-now">Rp{{ number_format($p['discounted'] ?? $p['price'], 0, ',', '.') }}</span>
                            </span>
                        </button>
                    @endforeach

                    {{-- Durasi custom (bila produk punya harga per bulan) --}}
                    @if ($pickPerBulan > 0)
                        @php
                            $cp = $this->customPricing();
                            $customBase = $cp['base'];
                            $customDisc = $cp['discounted'];
                            $customSave = $cp['savings'];
                        @endphp
                        <div class="fs-opt fs-opt-custom {{ $pickIsCustom ? 'is-active' : '' }}">
                            <span class="fs-opt-radio" wire:click="chooseCustom"></span>
                            <span class="fs-opt-info" wire:click="chooseCustom">
                                <span class="fs-opt-label">Durasi lain</span>
                                <span class="fs-opt-sub">
                                    @if ($cp['matched'])
                                        Sesuai paket {{ $pickCustomMonths }} bulan
                                    @else
                                        Rp{{ number_format($pickPerBulan, 0, ',', '.') }}/bulan
                                    @endif
                                </span>
                            </span>
                            <div class="fs-stepper">
                                <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                <span class="fs-stepper-val">{{ $pickCustomMonths }} bln</span>
                                <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                            </div>
                        </div>
                        @if ($pickIsCustom)
                            <div class="fs-custom-total">
                                <span class="fs-custom-total-left">
                                    Total {{ $pickCustomMonths }} bulan
                                    @if ($customSave > 0)
                                        <span class="fs-opt-save">Hemat Rp{{ number_format($customSave, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="fs-custom-total-price">
                                    @if ($customDisc < $customBase)
                                        <span class="fs-opt-orig">Rp{{ number_format($customBase, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="fs-opt-now">Rp{{ number_format($customDisc, 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

                <button type="button" class="fs-modal-add" wire:click="confirmAddToCart" wire:loading.attr="disabled"
                    wire:target="confirmAddToCart">
                    <span wire:loading.remove wire:target="confirmAddToCart"><i class="bi bi-cart-plus"></i> Tambah ke
                        Keranjang</span>
                    <span wire:loading wire:target="confirmAddToCart"><span
                            class="spinner-border spinner-border-sm"></span> Memproses…</span>
                </button>
            </div>
        </div>
    @endif
</main>
