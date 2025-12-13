<main class="main">
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0 text-muted">{{ $product->nama_akun }}</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/shop">Shop</a></li>
                    <li class="current">Product Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <section id="product-details" class="product-details section">
        <div class="container">
            <div class="row g-4">
                <!-- gallery -->
                <div class="col-lg-7">
                    <div class="product-gallery">
                        <div class="main-showcase">
                            <div class="image-zoom-container">
                                @if ($product->image)
                                    <img src="{{ asset('storage/img/Product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}" class="img-fluid main-product-image drift-zoom">
                                @else
                                    <img class="img-fluid main-product-image drift-zoom" style="object-fit: cover"
                                        src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                        alt="{{ $product->nama_akun }}">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- detail -->
                <div class="col-lg-5">
                    <div class="product-details">
                        <div class="product-badge-container">
                            <span class="badge-category">{{ $product->nama_akun }}</span>
                            <div class="rating-group">
                                <!-- rating markup -->
                            </div>
                        </div>

                        <div class="pricing-section" wire:ignore>
                            <div class="price-display">
                                <span id="salePrice" class="sale-price">
                                    {{ $product->formatted('harga_awal') }}
                                </span>
                                <span id="regularPrice" class="regular-price">

                                </span>
                            </div>
                        </div>

                        <div class="product-description">
                            <p>{{ $product->deskripsi }}</p>
                        </div>

                        <!-- Price Options: gunakan wire:click untuk set state Livewire -->
                        <div class="price-options-card mt-4" id="selectPackageModal" wire:ignore>
                            <h4 class="section-title">Pilih Paket Harga</h4>
                            <div class="price-select-list">
                                @php
                                    $packages = [
                                        ['Per Bulan', 'perbulan', 'bulan', 1, 'harga_perbulan'],
                                        ['5 Bulan', '5bulan', 'bulan', 5, 'harga_5_perbulan'],
                                        ['10 Bulan', '10bulan', 'bulan', 10, 'harga_10_perbulan'],
                                        ['Pertahun', 'pertahun', 'tahun', 12, 'harga_pertahun'],
                                    ];
                                @endphp
                                @foreach ($packages as [$label, $value, $clickType, $multiplier, $field])
                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="{{ $value }}"
                                            wire:click="selectPackage('{{ $clickType }}', {{ $multiplier }})"
                                            data-value="{{ $product->{$field} }}"
                                            data-multiplier="{{ $multiplier }}"
                                            data-regular="{{ $product->harga_awal }}">

                                        <div class="option-content">
                                            <div class="option-title">{{ $label }}</div>
                                            <div class="option-price">{{ $product->formatted($field) }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Purchase Options -->
                        <div class="purchase-section mt-3">
                            <div class="action-buttons mt-3">
                                <button class="btn primary-action" wire:click="addToCart">
                                    <i class="bi bi-bag-plus"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
