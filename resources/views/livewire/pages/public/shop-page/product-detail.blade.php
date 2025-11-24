<main class="main">
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">{{ $product->nama_akun }}</h1>
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
                                <img src="{{ asset('storage/img/product/' .$product->image) }}" alt="{{ $product->nama_akun }}" class="img-fluid main-product-image drift-zoom" id="main-product-image" data-zoom="{{ asset('storage/img/product/' .$product->image) }}">
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

                                    <!-- Per Bulan -->
                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="perbulan"
                                            wire:click="selectPackage('bulan', 1)"
                                            data-value="{{ $product->harga_perbulan }}"
                                            data-multiplier="1"
                                            data-regular="{{ $product->harga_awal }}">
                                        <div class="option-content">
                                            <div class="option-title">Per Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_perbulan') }}</div>
                                        </div>
                                    </label>

                                    <!-- 5 Bulan -->
                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="5bulan"
                                            wire:click="selectPackage('bulan', 5)"
                                            data-value="{{ $product->harga_5_perbulan }}"
                                            data-multiplier="5"
                                            data-regular="{{ $product->harga_awal }}">
                                        <div class="option-content">
                                            <div class="option-title">5 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_5_perbulan') }}</div>
                                        </div>
                                    </label>

                                    <!-- 10 Bulan -->
                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="10bulan"
                                            wire:click="selectPackage('bulan', 10)"
                                            data-value="{{ $product->harga_10_perbulan }}"
                                            data-multiplier="10"
                                            data-regular="{{ $product->harga_awal }}">
                                        <div class="option-content">
                                            <div class="option-title">10 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_10_perbulan') }}</div>
                                        </div>
                                    </label>

                                    <!-- Pertahun -->
                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="pertahun"
                                            wire:click="selectPackage('tahun', 12)"
                                            data-value="{{ $product->harga_pertahun }}"
                                            data-multiplier="12"
                                            data-regular="{{ $product->harga_awal }}">
                                        <div class="option-content">
                                            <div class="option-title">Pertahun</div>
                                            <div class="option-price">{{ $product->formatted('harga_pertahun') }}</div>
                                        </div>
                                    </label>
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
                <!-- tabs, reviews etc. keep same structure below (no change needed) -->
            </div>
    </section>
</main>