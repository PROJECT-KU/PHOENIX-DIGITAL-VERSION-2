<x-guest-layout>
dashjart

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
                  <img src="{{ asset('storage/img/product/' .$product->image) }}" alt="{{ $product->nama_akun }}" class="img-fluid main-product-image drift-zoom" id="main-product-image" data-zoom="{{ asset('storage/img/product/' .$product->image) }}">
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
                  {{-- <span class="save-amount">Save $50.00</span>
                  <span class="discount-percent">(21% off)</span> --}}
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
                            data-value="{{ $product->harga_perbulan }}"
                            data-multiplier="1"
                            data-regular="{{ $product->harga_awal }}">
                          <div class="option-content">
                              <div class="option-title">Per Bulan</div>
                              <div class="option-price">{{ $product->formatted('harga_perbulan') }}</div>
                          </div>
                          <i class="bi bi-check-circle-fill check-icon"></i>
                      </label>

                      <label class="price-option selectable">
                          <input type="radio" name="price_option"
                                value="5bulan"
                                data-value="{{ $product->harga_5_perbulan }}"
                                data-multiplier="5">
                          <div class="option-content">
                              <div class="option-title">5 Bulan</div>
                              <div class="option-price">{{ $product->formatted('harga_5_perbulan') }}</div>
                          </div>
                          <i class="bi bi-check-circle-fill check-icon"></i>
                      </label>

                      <label class="price-option selectable">
                          <input type="radio" name="price_option"
                                value="10bulan"
                                data-value="{{ $product->harga_10_perbulan }}"
                                data-multiplier="10">
                          <div class="option-content">
                              <div class="option-title">10 Bulan</div>
                              <div class="option-price">{{ $product->formatted('harga_10_perbulan') }}</div>
                          </div>
                          <i class="bi bi-check-circle-fill check-icon"></i>
                      </label>

                      <label class="price-option selectable">
                          <input type="radio" name="price_option"
                                value="pertahun"
                                data-value="{{ $product->harga_pertahun }}"
                                data-multiplier="12">
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
                      <input type="number" class="quantity-input" value="1" min="1" max="18">
                      <button class="quantity-btn increase" type="button">
                        <i class="bi bi-plus"></i>
                      </button>
                    </div>
                  </div>
                </div>

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
                                    <img src="{{ asset('storage/img/product/' .$product->image) }}" alt="{{ $product->nama_akun }}" class="img-fluid main-product-image drift-zoom" id="main-product-image" data-zoom="{{ asset('storage/img/product/' .$product->image) }}">
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
                                <span class="badge-category">Audio Equipment</span>
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

                            <h1 class="product-name">{{ $product->nama_akun }}</h1>

                            <div class="pricing-section">
                                <div class="price-display">
                                    <span class="sale-price">{{ $product->formatted('harga_awal') }}</span>
                                    <span class="regular-price">$239.99</span>
                                </div>
                                <div class="savings-info">
                                    {{-- <span class="save-amount">Save $50.00</span>
                  <span class="discount-percent">(21% off)</span> --}}
                                </div>
                            </div>

                            <div class="product-description">
                                <p>{{ $product->deskripsi }}</p>
                            </div>

                            <!-- Product Variants -->
                            <div class="mt-4 price-options-card">
                                <h4 class="section-title">Pilih Paket Harga</h4>

                                <div class="price-select-list">

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option"
                                            value="perbulan"
                                            data-price="{{ $product->formatted('harga_perbulan') }}"
                                            checked>
                                        <div class="option-content">
                                            <div class="option-title">Per Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_perbulan') }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option"
                                            value="5bulan"
                                            data-price="{{ $product->formatted('harga_5_perbulan') }}">
                                        <div class="option-content">
                                            <div class="option-title">5 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_5_perbulan') }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option"
                                            value="10bulan"
                                            data-price="{{ $product->formatted('harga_10_perbulan') }}">
                                        <div class="option-content">
                                            <div class="option-title">10 Bulan</div>
                                            <div class="option-price">{{ $product->formatted('harga_10_perbulan') }}</div>
                                        </div>
                                        <i class="bi bi-check-circle-fill check-icon"></i>
                                    </label>

                                    <label class="price-option selectable">
                                        <input type="radio" name="price_option"
                                            value="pertahun"
                                            data-price="{{ $product->formatted('harga_pertahun') }}">
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
                                            <input type="number" class="quantity-input" value="1" min="1" max="18">
                                            <button class="quantity-btn increase" type="button">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <button class="btn primary-action">
                                        <i class="bi bi-bag-plus"></i>
                                        Add to Cart
                                    </button>
                                    <button class="btn secondary-action">
                                        <i class="bi bi-lightning"></i>
                                        Buy Now
                                    </button>
                                </div>
                            </div>

                            <!-- Benefits List -->

                        </div>
                    </div>
                </div>

                <!-- Information Tabs -->
                <div class="mt-5 row" data-aos="fade-up" data-aos-delay="300">
                    <div class="col-12">
                        <div class="info-tabs-container">
                            <nav class="tabs-navigation nav">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ecommerce-product-details-5-overview" type="button">Overview</button>
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ecommerce-product-details-5-customer-reviews" type="button">Reviews (127)</button>
                            </nav>

                            <div class="tab-content">
                                <!-- Overview Tab -->
                                <div class="tab-pane fade show active" id="ecommerce-product-details-5-overview">
                                    <div class="overview-content">
                                        <div class="row g-4">
                                            <div class="col-lg-8">
                                                <div class="content-section">
                                                    <h3>Product Deskripsi</h3>
                                                    <p>{{ $product->deskripsi }}</p>

                                                    <div class="highlights-grid">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="package-contents">
                                                    <h4>Package Contents</h4>
                                                    <ul class="contents-list">
                                                        <li><i class="bi bi-check-circle"></i>Premium Audio Device</li>
                                                        <li><i class="bi bi-check-circle"></i>Premium Carrying Case</li>
                                                        <li><i class="bi bi-check-circle"></i>USB-C Fast Charging Cable</li>
                                                        <li><i class="bi bi-check-circle"></i>3.5mm Audio Connector</li>
                                                        <li><i class="bi bi-check-circle"></i>Quick Start Guide</li>
                                                        <li><i class="bi bi-check-circle"></i>Warranty Documentation</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reviews Tab -->
                                <div class="tab-pane fade" id="ecommerce-product-details-5-customer-reviews">
                                    <div class="reviews-content">
                                        <div class="reviews-header">
                                            <div class="rating-overview">
                                                <div class="average-score">
                                                    <div class="score-display">4.6</div>
                                                    <div class="score-stars">
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-fill"></i>
                                                        <i class="bi bi-star-half"></i>
                                                    </div>
                                                    <div class="total-reviews">127 customer reviews</div>
                                                </div>

                                                <div class="rating-distribution">
                                                    <div class="rating-row">
                                                        <span class="stars-label">5★</span>
                                                        <div class="progress-container">
                                                            <div class="progress-fill" style="width: 68%;"></div>
                                                        </div>
                                                        <span class="count-label">86</span>
                                                    </div>
                                                    <div class="rating-row">
                                                        <span class="stars-label">4★</span>
                                                        <div class="progress-container">
                                                            <div class="progress-fill" style="width: 22%;"></div>
                                                        </div>
                                                        <span class="count-label">28</span>
                                                    </div>
                                                    <div class="rating-row">
                                                        <span class="stars-label">3★</span>
                                                        <div class="progress-container">
                                                            <div class="progress-fill" style="width: 6%;"></div>
                                                        </div>
                                                        <span class="count-label">8</span>
                                                    </div>
                                                    <div class="rating-row">
                                                        <span class="stars-label">2★</span>
                                                        <div class="progress-container">
                                                            <div class="progress-fill" style="width: 3%;"></div>
                                                        </div>
                                                        <span class="count-label">4</span>
                                                    </div>
                                                    <div class="rating-row">
                                                        <span class="stars-label">1★</span>
                                                        <div class="progress-container">
                                                            <div class="progress-fill" style="width: 1%;"></div>
                                                        </div>
                                                        <span class="count-label">1</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="write-review-cta">
                                                <h4>Share Your Experience</h4>
                                                <p>Help others make informed decisions</p>
                                                <button class="btn review-btn">Write Review</button>
                                            </div>
                                        </div>

                                        <div class="customer-reviews-list">
                                            <div class="review-card">
                                                <div class="reviewer-profile">
                                                    <img src="assets/img/person/person-f-3.webp" alt="Customer" class="profile-pic">
                                                    <div class="profile-details">
                                                        <div class="customer-name">Sarah Martinez</div>
                                                        <div class="review-meta">
                                                            <div class="review-stars">
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                            </div>
                                                            <span class="review-date">March 28, 2024</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h5 class="review-headline">Outstanding audio quality and comfort</h5>
                                                <div class="review-text">
                                                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam. Eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
                                                </div>
                                                <div class="review-actions">
                                                    <button class="action-btn"><i class="bi bi-hand-thumbs-up"></i> Helpful (12)</button>
                                                    <button class="action-btn"><i class="bi bi-chat-dots"></i> Reply</button>
                                                </div>
                                            </div>

                                            <div class="review-card">
                                                <div class="reviewer-profile">
                                                    <img src="assets/img/person/person-m-5.webp" alt="Customer" class="profile-pic">
                                                    <div class="profile-details">
                                                        <div class="customer-name">David Chen</div>
                                                        <div class="review-meta">
                                                            <div class="review-stars">
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star"></i>
                                                            </div>
                                                            <span class="review-date">March 15, 2024</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h5 class="review-headline">Great value, minor connectivity issues</h5>
                                                <div class="review-text">
                                                    <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Overall satisfied with the purchase.</p>
                                                </div>
                                                <div class="review-actions">
                                                    <button class="action-btn"><i class="bi bi-hand-thumbs-up"></i> Helpful (8)</button>
                                                    <button class="action-btn"><i class="bi bi-chat-dots"></i> Reply</button>
                                                </div>
                                            </div>

                                            <div class="review-card">
                                                <div class="reviewer-profile">
                                                    <img src="assets/img/person/person-f-7.webp" alt="Customer" class="profile-pic">
                                                    <div class="profile-details">
                                                        <div class="customer-name">Emily Rodriguez</div>
                                                        <div class="review-meta">
                                                            <div class="review-stars">
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                            </div>
                                                            <span class="review-date">February 22, 2024</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h5 class="review-headline">Perfect for work-from-home setup</h5>
                                                <div class="review-text">
                                                    <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
                                                </div>
                                                <div class="review-actions">
                                                    <button class="action-btn"><i class="bi bi-hand-thumbs-up"></i> Helpful (15)</button>
                                                    <button class="action-btn"><i class="bi bi-chat-dots"></i> Reply</button>
                                                </div>
                                            </div>

                                            <div class="load-more-section">
                                                <button class="btn load-more-reviews">Show More Reviews</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- /Product Details Section -->

    </main>

</x-guest-layout>