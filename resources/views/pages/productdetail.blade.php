<x-guest-layout>
    <main class="main">

        <!-- Page Title -->
        <div class="page-title light-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">{{ $product->nama_akun }}</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="/">Home</a></li>
                        <li class="current">Product Details</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <!-- Product Details Section -->
        <section id="product-details" class="product-details section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row g-4">
                    <!-- Product Gallery -->
                    <div class="col-lg-7" data-aos="zoom-in" data-aos-delay="150">
                        <div class="product-gallery">
                            <div class="main-showcase">
                                <div class="image-zoom-container">
                                    <img src="{{ asset('storage/img/product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}" class="img-fluid main-product-image drift-zoom"
                                        id="main-product-image"
                                        data-zoom="{{ asset('storage/img/product/' . $product->image) }}">
                                </div>
                            </div>

                            <div class="thumbnail-grid">

                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="col-lg-5" data-aos="fade-left" data-aos-delay="200">
                        <div class="product-details">
                            <div class="product-badge-container">
                                <span class="badge-category">{{ $product->nama_akun }}</span>
                                <div class="rating-group">
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-half"></i>
                                    </div>
                                    <span class="review-text">(127 reviews)</span>
                                </div>
                            </div>

                            <h1 class="product-name"></h1>

                            <div class="pricing-section">
                                <div class="price-display">
                                    <span id="salePrice" class="sale-price">
                                        {{ $product->formatted('harga_awal') }}
                                    </span>
                                    <span id="regularPrice" class="regular-price" style="display:none;"></span>
                                </div>
                                <div class="savings-info">
                                </div>
                            </div>

                            <div class="product-description">
                                <p>{{ $product->deskripsi }}</p>
                            </div>

                            <!-- Price Options -->
                            <div class="price-options-card mt-4">
                                <h4 class="section-title">Pilih Paket Harga</h4>

                                <div class="price-select-list">

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="perbulan"
                                            data-value="{{ $product->harga_perbulan }}" data-multiplier="1"
                                            data-regular="{{ $product->harga_awal }}">
                                        <div class="option-content">
                                            <div class="option-title">Per Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_perbulan') }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="5bulan"
                                            data-value="{{ $product->harga_5_perbulan }}" data-multiplier="5">
                                        <div class="option-content">
                                            <div class="option-title">5 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_5_perbulan') }}
                                            </div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="10bulan"
                                            data-value="{{ $product->harga_10_perbulan }}" data-multiplier="10">
                                        <div class="option-content">
                                            <div class="option-title">10 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_10_perbulan') }}
                                            </div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option" value="pertahun"
                                            data-value="{{ $product->harga_pertahun }}" data-multiplier="12">
                                        <div class="option-content">
                                            <div class="option-title">Pertahun</div>
                                            <div class="option-price">{{ $product->formatted('harga_pertahun') }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                </div>
                            </div>

                            <!-- Product Variants -->
                            <div class="variant-section">

                            </div>

                            <!-- Purchase Options -->
                            <div class="purchase-section">
                                <div class="quantity-control">
                                    <label class="control-label">Quantity:</label>
                                    <div class="quantity-input-group">
                                        <div class="quantity-selector">
                                            <button class="quantity-btn decrease" type="button">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" class="quantity-input" value="1"
                                                min="1" max="18">
                                            <button class="quantity-btn increase" type="button">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>
