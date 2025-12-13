<main class="main">
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0 text-muted">Shopping</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Home</a></li>
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
                        <div class="row g-4">
                            @forelse ($products as $item)
                                @php
                                    $bestDiscount = $this->getBestDiscount($item->id);
                                @endphp
                                <div class="col-6 col-md-4 col-lg-3" wire:key="product-{{ $item->id }}">
                                    <div class="product-card">
                                        <div class="product-image">
                                            @if ($item->image)
                                                <div>
                                                    <img src="{{ asset('storage/img/Product/' . $item->image) }}"
                                                        class="img-fluid" alt="{{ $item->nama_akun }}">
                                                </div>
                                            @else
                                                <div>
                                                    <img class="img-fluid" style="object-fit: cover"
                                                        src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                        alt="">
                                                </div>
                                            @endif

                                            <!-- Promo Badge -->
                                            @if ($bestDiscount)
                                                <div class="discount-badge"
                                                    style="background: {{ $bestDiscount['promo']->badge_color ?? '#FF6B6B' }};">
                                                    @if ($bestDiscount['type'] === 'persen')
                                                        @if ($bestDiscount['member_value'] != $bestDiscount['non_member_value'])
                                                            diskon
                                                            {{ number_format($bestDiscount['non_member_value'], 0) }}-{{ number_format($bestDiscount['member_value'], 0) }}%
                                                        @else
                                                            diskon{{ number_format($bestDiscount['value'], 0) }}%
                                                        @endif
                                                    @else
                                                        diskon Rp{{ number_format($bestDiscount['value'], 0) }}
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="product-overlay">
                                                <div class="product-actions">
                                                    <a href="{{ route('shop.detail-product', $item->id) }}"
                                                        class="action-btn" data-bs-toggle="tooltip"
                                                        title="Detail Product">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="action-btn" data-bs-toggle="modal"
                                                        data-bs-target="#selectPackageModal{{ $item->id }}"
                                                        title="Tambah ke Keranjang">
                                                        <i class="bi bi-cart-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="product-details">
                                            <h4 class="product-title">
                                                <a href="{{ route('shop.detail-product', $item->id) }}">
                                                    {{ $item->nama_akun }}
                                                </a>
                                            </h4>
                                            <div class="product-meta">
                                                @if ($bestDiscount)
                                                    <div class="product-price flex-column align-items-start">
                                                        @php
                                                            $originalPrice = $item->harga_perbulan;
                                                            if ($bestDiscount['type'] === 'persen') {
                                                                $discountedPrice =
                                                                    $originalPrice -
                                                                    ($originalPrice * $bestDiscount['value']) / 100;
                                                            } else {
                                                                $discountedPrice = max(
                                                                    0,
                                                                    $originalPrice - $bestDiscount['value'],
                                                                );
                                                            }
                                                        @endphp
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="text-danger small d-block">
                                                                <del>Rp
                                                                    {{ number_format($originalPrice, 0, ',', '.') }}</del>
                                                            </span>
                                                            <div class="d-flex align-items-baseline gap-2">
                                                                <span class="sale-price fs-6 text-dark small fw-bold">Rp
                                                                    {{ number_format($discountedPrice, 0, ',', '.') }}/bulan
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="product-price text-muted">
                                                        Mulai Rp
                                                        {{ number_format($item->harga_perbulan, 0, ',', '.') }}/bulan
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Pilih Paket -->
                                <div class="modal fade" id="selectPackageModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title text-dark">Pilih Paket {{ $item->nama_akun }}
                                                </h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="gap-2 d-flex flex-column">
                                                    @if ($item->harga_perbulan)
                                                        @php
                                                            $price = $item->harga_perbulan;
                                                            if ($bestDiscount) {
                                                                if ($bestDiscount['type'] === 'persen') {
                                                                    $discountedPrice =
                                                                        $price -
                                                                        ($price * $bestDiscount['value']) / 100;
                                                                } else {
                                                                    $discountedPrice = max(
                                                                        0,
                                                                        $price - $bestDiscount['value'],
                                                                    );
                                                                }
                                                                $totalSaving =
                                                                    $item->harga_perbulan * 1 - $discountedPrice;
                                                            }
                                                        @endphp
                                                        <button type="button" class="btn btn-outline-light w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 1)"
                                                            data-bs-dismiss="modal">
                                                            <div
                                                                class="d-flex w-100 justify-content-between align-items-center">
                                                                <p class="mb-0 text-dark">Paket 1 Bulan</p>
                                                                <div class="text-end">
                                                                    @if ($bestDiscount)
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                                            <small
                                                                                class="text-danger fs-6 text-decoration-line-through">
                                                                                Rp
                                                                                {{ number_format($price, 0, ',', '.') }}
                                                                            </small>
                                                                            <strong class="text-dark fs-5">
                                                                                Rp
                                                                                {{ number_format($discountedPrice, 0, ',', '.') }}
                                                                            </strong>
                                                                        </div>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($totalSaving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @else
                                                                        <strong>Rp
                                                                            {{ number_format($price, 0, ',', '.') }}</strong>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_5_perbulan)
                                                        @php
                                                            $price = $item->harga_5_perbulan;
                                                            $saving = $item->harga_perbulan * 5 - $price;
                                                            if ($bestDiscount) {
                                                                if ($bestDiscount['type'] === 'persen') {
                                                                    $discountedPrice =
                                                                        $price -
                                                                        ($price * $bestDiscount['value']) / 100;
                                                                } else {
                                                                    $discountedPrice = max(
                                                                        0,
                                                                        $price - $bestDiscount['value'],
                                                                    );
                                                                }
                                                                $totalSaving =
                                                                    $item->harga_perbulan * 5 - $discountedPrice;
                                                            }
                                                        @endphp
                                                        <button type="button" class="btn btn-outline-light w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 5)"
                                                            data-bs-dismiss="modal">
                                                            <div
                                                                class="d-flex w-100 justify-content-between align-items-center">
                                                                <p class="mb-0 text-dark">Paket 5 Bulan</p>
                                                                <div class="text-end">
                                                                    @if ($bestDiscount)
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                                            <small
                                                                                class="text-danger fs-6 text-decoration-line-through">
                                                                                Rp
                                                                                {{ number_format($price, 0, ',', '.') }}
                                                                            </small>
                                                                            <strong class="text-dark fs-5">
                                                                                Rp
                                                                                {{ number_format($discountedPrice, 0, ',', '.') }}
                                                                            </strong>
                                                                        </div>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($totalSaving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @else
                                                                        <strong>Rp
                                                                            {{ number_format($price, 0, ',', '.') }}</strong>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($saving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_10_perbulan)
                                                        @php
                                                            $price = $item->harga_10_perbulan;
                                                            $saving = $item->harga_perbulan * 10 - $price;
                                                            if ($bestDiscount) {
                                                                if ($bestDiscount['type'] === 'persen') {
                                                                    $discountedPrice =
                                                                        $price -
                                                                        ($price * $bestDiscount['value']) / 100;
                                                                } else {
                                                                    $discountedPrice = max(
                                                                        0,
                                                                        $price - $bestDiscount['value'],
                                                                    );
                                                                }
                                                                $totalSaving =
                                                                    $item->harga_perbulan * 10 - $discountedPrice;
                                                            }
                                                        @endphp
                                                        <button type="button" class="btn btn-outline-light w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 10)"
                                                            data-bs-dismiss="modal">
                                                            <div
                                                                class="d-flex w-100 justify-content-between align-items-center">
                                                                <p class="mb-0 text-dark">Paket 10 Bulan</p>
                                                                <div class="text-end">
                                                                    @if ($bestDiscount)
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                                            <small
                                                                                class="text-danger fs-6 text-decoration-line-through">
                                                                                Rp
                                                                                {{ number_format($price, 0, ',', '.') }}
                                                                            </small>
                                                                            <strong class="text-dark fs-5">
                                                                                Rp
                                                                                {{ number_format($discountedPrice, 0, ',', '.') }}
                                                                            </strong>
                                                                        </div>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($totalSaving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @else
                                                                        <strong>Rp
                                                                            {{ number_format($price, 0, ',', '.') }}</strong>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($saving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_pertahun)
                                                        @php
                                                            $price = $item->harga_pertahun;
                                                            $saving = $item->harga_perbulan * 12 - $price;
                                                            if ($bestDiscount) {
                                                                if ($bestDiscount['type'] === 'persen') {
                                                                    $discountedPrice =
                                                                        $price -
                                                                        ($price * $bestDiscount['value']) / 100;
                                                                } else {
                                                                    $discountedPrice = max(
                                                                        0,
                                                                        $price - $bestDiscount['value'],
                                                                    );
                                                                }
                                                                $totalSaving =
                                                                    $item->harga_perbulan * 12 - $discountedPrice;
                                                            }
                                                        @endphp
                                                        <button type="button" class="btn btn-outline-light w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'tahun', 1)"
                                                            data-bs-dismiss="modal">
                                                            <div
                                                                class="d-flex w-100 justify-content-between align-items-center">
                                                                <p class="mb-0 text-dark">Paket 1 Tahun</p>
                                                                <div class="text-end">
                                                                    @if ($bestDiscount)
                                                                        <div
                                                                            class="d-flex align-items-center justify-content-end gap-2">
                                                                            <small
                                                                                class="text-danger fs-6 text-decoration-line-through">
                                                                                Rp
                                                                                {{ number_format($price, 0, ',', '.') }}
                                                                            </small>
                                                                            <strong class="text-dark fs-5">
                                                                                Rp
                                                                                {{ number_format($discountedPrice, 0, ',', '.') }}
                                                                            </strong>
                                                                        </div>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($totalSaving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @else
                                                                        <strong>Rp
                                                                            {{ number_format($price, 0, ',', '.') }}</strong>
                                                                        <small class="d-block text-muted">
                                                                            hemat hingga Rp
                                                                            {{ number_format($saving, 0, ',', '.') }}
                                                                            untuk member
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center alert alert-warning">
                                        <i class="bi bi-search"></i>
                                        <p class="mt-2 mb-0">Tidak ada produk yang ditemukan</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-5">
                            {{ $products->links('vendor.pagination') }}
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </section>
    <!-- end list product -->
    <style>
        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #FF6B6B;
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 14px;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }
    </style>
</main>
