<x-guest-layout>
    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Product</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="/">Home</a></li>
            <li class="current">Product</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- About 2 Section -->
    <section id="about-2" class="about-2 section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        {{-- <span class="section-badge"><i class="bi bi-info-circle"></i> Product</span> --}}
        <div class="row">
          <div class="col-lg-12">
            <h2 class="about-title">Flash Sale</h2>
            <p class="about-description">Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.</p>
          </div>
        </div>

        <section id="category-header" class="category-header section">

            <div class="container" data-aos="fade-up">

            <!-- Filter and Sort Options -->
                <div class="filter-container mb-4" data-aos="fade-up" data-aos-delay="100">
                  <div class="row g-3">
                      <div class="col-12 col-md-6 col-lg-8">

                          <form action="{{ route('homeproduct') }}" method="GET" class="filter-item search-form">
                              <label for="productSearch" class="form-label">Search Products</label>

                              <div class="input-group">
                                  <input 
                                      type="text" 
                                      class="form-control" 
                                      id="productSearch" 
                                      name="search"
                                      value="{{ request('search') }}"
                                      placeholder="Search for products..."
                                      aria-label="Search for products"
                                  >

                                  <button class="btn search-btn" type="submit">
                                      <i class="bi bi-search"></i>
                                  </button>
                              </div>
                          </form>

                      </div>
                  </div>
              </div>


            </div>

            <!-- Category Product List Section -->
          <section id="category-product-list" class="category-product-list section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

              <div class="row g-4">
                @foreach ($product as $item)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card" data-aos="zoom-in">

                        <div class="product-image">
                            <img src="{{ asset('storage/img/product/' . $item->image) }}" 
                                class="main-image img-fluid" 
                                alt="{{ $item->nama_akun }}">

                            <img src="{{ asset('storage/img/product/' . $item->image) }}" 
                                class="hover-image img-fluid" 
                                alt="{{ $item->nama_akun }}">

                            <div class="product-overlay">
                                <div class="product-actions">
                                    <a href="{{ route('productdetail', $item->id) }}" type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="" 
                                      class="action-btn" 
                                      data-bs-toggle="tooltip" 
                                      title="Detail Product">
                                        <i class="bi bi-cart-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="product-details">
                            <div class="product-category">Premium Product</div>

                            <h4 class="product-title">
                                <a href="{{ url('product-details/' . $item->id) }}">
                                    {{ $item->nama_akun }}
                                </a>
                            </h4>

                            <div class="product-meta">
                                <div class="product-price">
                                    Rp {{ number_format($item->harga_perbulan, 0, ',', '.') }}/bulan
                                </div>

                                <div class="product-rating">
                                    <i class="bi bi-star-fill"></i>
                                    4.8 <span>(24)</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach

              </div>

            </div>

          </section><!-- /Category Product List Section -->

        </section><!-- /Category Header Section -->

        <div class="row featured-products-row" data-aos="fade-up" data-aos-delay="500">
          <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
            <div class="product-showcase">
              <div class="product-image">
                <img src="{{ 'niceshop/assets/img/product/product-5.webp' }}" alt="Featured Product" class="img-fluid">
                <div class="discount-badge">-45%</div>
              </div>
              <div class="product-details">
                <h6>Premium Wireless Headphones</h6>
                <div class="price-section">
                  <span class="original-price">$129</span>
                  <span class="sale-price">$71</span>
                </div>
                <div class="rating-stars">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <span class="rating-count">(324)</span>
                </div>
              </div>
            </div>
          </div><!-- End Product Showcase -->

          <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="150">
            <div class="product-showcase">
              <div class="product-image">
                <img src="{{ 'niceshop/assets/img/product/product-7.webp' }}" alt="Featured Product" class="img-fluid">
                <div class="discount-badge">-60%</div>
              </div>
              <div class="product-details">
                <h6>Smart Fitness Tracker</h6>
                <div class="price-section">
                  <span class="original-price">$89</span>
                  <span class="sale-price">$36</span>
                </div>
                <div class="rating-stars">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-half"></i>
                  <span class="rating-count">(198)</span>
                </div>
              </div>
            </div>
          </div><!-- End Product Showcase -->

          <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
            <div class="product-showcase">
              <div class="product-image">
                <img src="{{ 'niceshop/assets/img/product/product-11.webp' }}" alt="Featured Product" class="img-fluid">
                <div class="discount-badge">-35%</div>
              </div>
              <div class="product-details">
                <h6>Luxury Travel Backpack</h6>
                <div class="price-section">
                  <span class="original-price">$159</span>
                  <span class="sale-price">$103</span>
                </div>
                <div class="rating-stars">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <span class="rating-count">(267)</span>
                </div>
              </div>
            </div>
          </div><!-- End Product Showcase -->

          <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="250">
            <div class="product-showcase">
              <div class="product-image">
                <img src="{{ 'niceshop/assets/img/product/product-1.webp' }}" alt="Featured Product" class="img-fluid">
                <div class="discount-badge">-55%</div>
              </div>
              <div class="product-details">
                <h6>Artisan Coffee Mug Set</h6>
                <div class="price-section">
                  <span class="original-price">$75</span>
                  <span class="sale-price">$34</span>
                </div>
                <div class="rating-stars">
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star-fill"></i>
                  <i class="bi bi-star"></i>
                  <span class="rating-count">(142)</span>
                </div>
              </div>
            </div>
          </div><!-- End Product Showcase -->
        </div>


    </section><!-- /Testimonials Section -->
</x-guest-layout>