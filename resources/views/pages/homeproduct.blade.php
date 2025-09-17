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
                        <div class="filter-item search-form">
                        <label for="productSearch" class="form-label">Search Products</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="productSearch" placeholder="Search for products..." aria-label="Search for products">
                            <button class="btn search-btn" type="button">
                            <i class="bi bi-search"></i>
                            </button>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>

            </div>

            <!-- Category Product List Section -->
          <section id="category-product-list" class="category-product-list section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

              <div class="row g-4">
                <!-- Product 1 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-f-1.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-f-2.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Women's Fashion</div>
                      <h4 class="product-title"><a href="product-details.html">Tempor Incididunt</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$129.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.8 <span>(42)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 2 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="100">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-m-1.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-m-2.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="product-badge new">New</div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Men's Collection</div>
                      <h4 class="product-title"><a href="product-details.html">Elit Consectetur</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$95.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.6 <span>(28)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 3 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-f-3.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-f-4.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="product-badge sale">-25%</div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Accessories</div>
                      <h4 class="product-title"><a href="product-details.html">Adipiscing Magna</a></h4>
                      <div class="product-meta">
                        <div class="product-price">
                          $75.00
                          <span class="original-price">$99.00</span>
                        </div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.9 <span>(56)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 4 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-m-3.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-m-4.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Footwear</div>
                      <h4 class="product-title"><a href="product-details.html">Labore Dolore</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$145.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.7 <span>(35)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 5 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="400">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-f-5.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-f-6.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Men's Fashion</div>
                      <h4 class="product-title"><a href="product-details.html">Magna Aliqua</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$89.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.5 <span>(23)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 6 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="500">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-m-5.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-m-6.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="product-badge sale">-15%</div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Women's Fashion</div>
                      <h4 class="product-title"><a href="product-details.html">Eiusmod Tempor</a></h4>
                      <div class="product-meta">
                        <div class="product-price">
                          $110.00
                          <span class="original-price">$129.00</span>
                        </div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.8 <span>(47)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 7 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="600">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-f-7.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-f-8.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Accessories</div>
                      <h4 class="product-title"><a href="product-details.html">Incididunt Labore</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$55.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.6 <span>(31)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product 8 -->
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="product-card" data-aos="zoom-in" data-aos-delay="700">
                    <div class="product-image">
                      <img src="{{ 'niceshop/assets/img/product/product-m-7.webp' }}" class="main-image img-fluid" alt="Product">
                      <img src="{{ 'niceshop/assets/img/product/product-m-8.webp' }}" class="hover-image img-fluid" alt="Product Variant">
                      <div class="product-overlay">
                        <div class="product-actions">
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Quick View">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button type="button" class="action-btn" data-bs-toggle="tooltip" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                          </button>
                        </div>
                      </div>
                      <div class="product-badge new">New</div>
                    </div>
                    <div class="product-details">
                      <div class="product-category">Men's Fashion</div>
                      <h4 class="product-title"><a href="product-details.html">Aliqua Magna</a></h4>
                      <div class="product-meta">
                        <div class="product-price">$79.00</div>
                        <div class="product-rating">
                          <i class="bi bi-star-fill"></i>
                          4.7 <span>(39)</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

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