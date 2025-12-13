@section('title')
Beranda | Phoenix Digital
@endsection

<x-guest-layout>
 dashjart
  <main class="main">

    <!--================== BANNERS ==================-->
    <section id="hero" class="hero section">
      <div class="hero-container">
        @forelse($banners as $banner)
        <div class="hero-content">
          <div class="content-wrapper" data-aos="fade-up" data-aos-delay="100">
            <h1 class="hero-title">{{ $banner->judul }}</h1>
            <p class="hero-description">{{ $banner->deskripsi }}</p>
            <div class="hero-actions" data-aos="fade-up" data-aos-delay="200">
              <a href="#products" class="btn-primary">Shop Now</a>
              <a href="#categories" class="btn-secondary">Browse Categories</a>
            </div>
            <div class="features-list" data-aos="fade-up" data-aos-delay="300">
              <div class="feature-item">
                <i class="bi bi-truck"></i>
                <span>Free Shipping</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-award"></i>
                <span>Quality Guarantee</span>
              </div>
              <div class="feature-item">
                <i class="bi bi-headset"></i>
                <span>24/7 Support</span>
              </div>
            </div>
          </div>
        </div>
        @endforeach

        <div class="hero-visuals">
          <div class="product-showcase" data-aos="fade-left" data-aos-delay="200">

            @forelse($banners as $banner)
            <div class="product-card featured">
              <img style="width: 100%;" src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                alt="{{ $banner->judul ?? 'Banner' }}" class="img-fluid">
              <div class="product-badge">Promo</div>
            </div>
            @empty
            <p>Tidak ada banner aktif</p>
            @endforelse

            <div class="product-grid">
              <div class="product-mini" data-aos="zoom-in" data-aos-delay="400">
                <img src="{{ 'niceshop/assets/img/product/scopus.png' }}" alt="Product" class="img-fluid">
                <span class="mini-price">$89</span>
              </div>
              <div class="product-mini" data-aos="zoom-in" data-aos-delay="500">
                <img src="{{ 'niceshop/assets/img/product/grammarly.png' }}" alt="Product" class="img-fluid">
                <span class="mini-price">$149</span>
              </div>
            </div>
          </div>

          <div class="floating-elements">
            <div class="floating-icon cart" data-aos="fade-up" data-aos-delay="600">
              <i class="bi bi-cart3"></i>
              <span class="notification-dot">3</span>
            </div>
            <div class="floating-icon wishlist" data-aos="fade-up" data-aos-delay="700">
              <i class="bi bi-heart"></i>
            </div>
            <div class="floating-icon search" data-aos="fade-up" data-aos-delay="800">
              <i class="bi bi-search"></i>
            </div>
          </div>
        </div>
      </div>

    </section>
    <!--================== END ==================-->

    <!--================== PRODUK TERLARIS ==================-->
    <section id="promo-cards" class="promo-cards section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">

          <style>
            @media (max-width: 767.98px) {
              .image-scopcus {
                margin-top: 0 !important;
                margin-bottom: 8rem !important;
              }

              .card-scopus {
                margin: 0 auto;
                max-width: 80%;
              }

              /* Perbaiki tinggi di mobile */
              .image-scopcus .d-flex {
                min-height: auto !important;
                /* hilangkan tinggi fix 500px */
              }

              .category-content {
                margin-top: 1rem;
              }

              .text-scopus {
                text-align: justify;

              }
            }

            /* Tambahan: tablet 768px - 991.98px */
            @media (min-width: 768px) and (max-width: 991.98px) {
              .card-scopus {
                margin: 0 auto;
                /* center */
                max-width: 75%;
                /* lebih besar dari mobile */
                float: none !important;
              }

              .text-scopus {
                text-align: justify;
                margin-right: 10px;
              }
            }
          </style>

          <div class="col-lg-6">
            <div class="category-featured" data-aos="fade-right" data-aos-delay="200">
              <div class="category-image image-scopcus">
                <div class="d-flex justify-content-center align-items-center" style="min-height: 500px;">
                  <div class="card border-0 card-scopus" style="border-radius: 20px; overflow: hidden; margin: 30px; box-shadow: -8px 8px 20px rgba(255, 165, 0, 0.5);">
                    <img src="{{ 'niceshop/assets/img/product/scopus.png' }}"
                      alt="Scopus Lisensi & AI"
                      class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="category-content">
                <span class="category-tag">Trending Now</span>
                <h2>Scopus Lisensi & Scopus AI</h2>
                <p class="text-scopus" style="text-align: justify; margin-right: 5px; font-size: 12px;">
                  Akun Scopus Lisensi & Scopus AI memberi akses ke database jurnal ilmiah terbesar dengan
                  dukungan AI untuk pencarian, analisis, dan rekomendasi publikasi. Solusi ideal bagi
                  peneliti, akademisi, dan profesional yang membutuhkan sumber ilmiah terkini.</p>
                <a href="#" class="btn-shop">Explore Products <i class="bi bi-arrow-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="row gy-4">

              <div class="col-xl-6">
                <div class="category-card cat-men" data-aos="fade-up" data-aos-delay="300">
                  <div class="category-image">
                    <img src="{{ 'niceshop/assets/img/product/grammarly.png' }}" alt="Men's Fashion" class="img-fluid">
                  </div>
                  <div class="category-content">
                    <h4>Grammarly Premium</h4>
                    <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <div class="category-card cat-kids" data-aos="fade-up" data-aos-delay="400">
                  <div class="category-image">
                    <img src="{{ 'niceshop/assets/img/product/quillbot.png' }}" alt="Kid's Fashion" class="img-fluid">
                  </div>
                  <div class="category-content">
                    <h4>Quillbot Premium</h4>
                    <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <div class="category-card cat-cosmetics" data-aos="fade-up" data-aos-delay="500">
                  <div class="category-image">
                    <img src="{{ 'niceshop/assets/img/product/consensus.png' }}" alt="Cosmetics" class="img-fluid">
                  </div>
                  <div class="category-content">
                    <h4>Consensus Premium</h4>
                    <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <div class="category-card cat-accessories" data-aos="fade-up" data-aos-delay="600">
                  <div class="category-image">
                    <img src="{{ 'niceshop/assets/img/product/gamma.png' }}" alt="Accessories" class="img-fluid">
                  </div>
                  <div class="category-content">
                    <h4>Gamma AI Premium</h4>
                    <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>
    <!--================== END ==================-->

    <!--================== FLASH SALE ==================-->
    <section id="call-to-action" class="call-to-action section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row">
          <div class="col-lg-8 mx-auto">
            <div class="main-content text-center" data-aos="zoom-in" data-aos-delay="200">
              <div class="offer-badge" data-aos="fade-down" data-aos-delay="250">
                <span class="limited-time">Limited Time</span>
                <span class="offer-text">50% OFF</span>
              </div>

              <h2 data-aos="fade-up" data-aos-delay="300">Exclusive Flash Sale</h2>

              <p class="subtitle" data-aos="fade-up" data-aos-delay="350">Don't miss out on our biggest sale of the year. Premium quality products at unbeatable prices for the next 48 hours only.</p>

              <div class="countdown-wrapper" data-aos="fade-up" data-aos-delay="400">
                <div class="countdown d-flex justify-content-center" data-count="2025/12/31">
                  <div>
                    <h3 class="count-days"></h3>
                    <h4>Days</h4>
                  </div>
                  <div>
                    <h3 class="count-hours"></h3>
                    <h4>Hours</h4>
                  </div>
                  <div>
                    <h3 class="count-minutes"></h3>
                    <h4>Minutes</h4>
                  </div>
                  <div>
                    <h3 class="count-seconds"></h3>
                    <h4>Seconds</h4>
                  </div>
                </div>
              </div>

              <div class="action-buttons" data-aos="fade-up" data-aos-delay="450">
                <a href="#" class="btn-shop-now">Shop Now</a>
                <a href="#" class="btn-view-deals">View All Deals</a>
              </div>
            </div>
          </div>
        </div>

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
          </div>
        </div>
      </div>
    </section>
    <!--================== END ==================-->

    <!-- Best Sellers Section -->
    <section id="best-sellers" class="best-sellers section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Best Sellers</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5">

          @foreach ($product as $item)
          <div class="col-lg-3 col-md-6">
              <div class="product-item">
                  <div class="product-image">

                      <img src="{{ asset('storage/img/product/' . $item->image) }}" 
                          alt="{{ $item->nama_akun }}" 
                          class="img-fluid" 
                          loading="lazy">

                      <a href="{{ route('productdetail', $item->id) }}" class="cart-btn">
                          Detail Product
                      </a>
                  </div>

                  <div class="product-info">
                      <h4 class="product-name">
                          <a href="{{ route('productdetail', $item->id) }}">
                              {{ $item->nama_akun }}
                          </a>
                      </h4>

                      <div class="product-rating">
                          <div class="stars">
                              <i class="bi bi-star-fill"></i>
                              <i class="bi bi-star-fill"></i>
                              <i class="bi bi-star-fill"></i>
                              <i class="bi bi-star-fill"></i>
                              <i class="bi bi-star"></i>
                          </div>
                          <span class="rating-count">(24)</span>
                      </div>

                      {{-- Harga (ambil harga bulanan sebagai contoh) --}}
                      <div class="product-price">
                          {{ $item->formatted('harga_perbulan') }}/bulan
                      </div>

                  </div>
              </div>
          </div>
          @endforeach


        </div>

      </div>

    </section><!-- /Best Sellers Section -->

    <!-- Cards Section -->
    <section id="cards" class="cards section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-lg-4 col-md-6 mb-5 mb-md-0" data-aos="fade-up" data-aos-delay="200">
            <div class="product-category">
              <h3 class="category-title">
                <i class="bi bi-fire"></i> Trending Now
              </h3>
              <div class="product-list">
                <div class="product-card">
                  <div class="product-image">
                    <img src="{{ 'niceshop/assets/img/product/product-1.webp' }}" alt="Premium Leather Tote" class="img-fluid">
                    <div class="product-badges">
                      <span class="badge-new">New</span>
                    </div>
                  </div>
                  <div class="product-info">
                    <h4 class="product-name">Premium Leather Tote</h4>
                    <div class="product-rating">
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-fill"></i>
                      <i class="bi bi-star-half"></i>
                      <span>(24)</span>
                    </div>
                    <div class="product-price">
                      <span class="current-price">$87.50</span>

    <main class="main">

        <!--================== BANNERS ==================-->
        <section id="hero" class="hero section">
            <div class="hero-container">
                @forelse($banners as $banner)
                <div class="hero-content">
                    <div class="content-wrapper" data-aos="fade-up" data-aos-delay="100">
                        <h1 class="hero-title">{{ $banner->judul }}</h1>
                        <p class="hero-description">{{ $banner->deskripsi }}</p>
                        <div class="hero-actions" data-aos="fade-up" data-aos-delay="200">
                            <a href="#products" class="btn-primary">Shop Now</a>
                            <a href="#categories" class="btn-secondary">Browse Categories</a>
                        </div>
                        <div class="features-list" data-aos="fade-up" data-aos-delay="300">
                            <div class="feature-item">
                                <i class="bi bi-truck"></i>
                                <span>Free Shipping</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-award"></i>
                                <span>Quality Guarantee</span>
                            </div>
                            <div class="feature-item">
                                <i class="bi bi-headset"></i>
                                <span>24/7 Support</span>
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach

                <div class="hero-visuals">
                    <div class="product-showcase" data-aos="fade-left" data-aos-delay="200">

                        @forelse($banners as $banner)
                        <div class="product-card featured">
                            <img style="width: 100%;" src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                alt="{{ $banner->judul ?? 'Banner' }}" class="img-fluid">
                            <!-- <div class="product-badge">Promo</div> -->
                        </div>
                        @empty
                        <p>Tidak ada banner aktif</p>
                        @endforelse

                        <div class="product-grid">
                            <div class="product-mini" data-aos="zoom-in" data-aos-delay="400">
                                <img src="{{ 'niceshop/assets/img/product/scopus.png' }}" alt="Product" class="img-fluid">
                                <span class="mini-price">$89</span>
                            </div>
                            <div class="product-mini" data-aos="zoom-in" data-aos-delay="500">
                                <img src="{{ 'niceshop/assets/img/product/grammarly.png' }}" alt="Product" class="img-fluid">
                                <span class="mini-price">$149</span>
                            </div>
                        </div>
                    </div>

                    <div class="floating-elements">
                        <div class="floating-icon cart" data-aos="fade-up" data-aos-delay="600">
                            <i class="bi bi-cart3"></i>
                            <span class="notification-dot">3</span>
                        </div>
                        <div class="floating-icon wishlist" data-aos="fade-up" data-aos-delay="700">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div class="floating-icon search" data-aos="fade-up" data-aos-delay="800">
                            <i class="bi bi-search"></i>
                        </div>
                    </div>
                </div>
            </div>

        </section>
        <!--================== END ==================-->

        <!--================== PRODUK TERLARIS ==================-->
        <section id="promo-cards" class="promo-cards section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row gy-4">
                    <style>
                        @media (max-width: 767.98px) {
                            .image-scopcus {
                                margin-top: 0 !important;
                                margin-bottom: 8rem !important;
                            }

                            .card-scopus {
                                margin: 0 auto;
                                max-width: 80%;
                            }

                            /* Perbaiki tinggi di mobile */
                            .image-scopcus .d-flex {
                                min-height: auto !important;
                                /* hilangkan tinggi fix 500px */
                            }

                            .category-content {
                                margin-top: 1rem;
                            }

                            .text-scopus {
                                text-align: justify;

                            }
                        }

                        /* Tambahan: tablet 768px - 991.98px */
                        @media (min-width: 768px) and (max-width: 991.98px) {
                            .card-scopus {
                                margin: 0 auto;
                                /* center */
                                max-width: 75%;
                                /* lebih besar dari mobile */
                                float: none !important;
                            }

                            .text-scopus {
                                text-align: justify;
                                margin-right: 10px;
                            }
                        }
                    </style>

                    <div class="col-lg-6">
                        <div class="category-featured" data-aos="fade-right" data-aos-delay="200">
                            <div class="category-image image-scopcus">
                                <div class="d-flex justify-content-center align-items-center" style="min-height: 500px;">
                                    <div class="border-0 card card-scopus" style="border-radius: 20px; overflow: hidden; margin: 30px; box-shadow: -8px 8px 20px rgba(255, 165, 0, 0.5);">
                                        <img src="{{ 'niceshop/assets/img/product/scopus.png' }}"
                                            alt="Scopus Lisensi & AI"
                                            class="img-fluid">
                                    </div>
                                </div>
                            </div>
                            <div class="category-content">
                                <span class="category-tag">Trending Now</span>
                                <h2>Scopus Lisensi & Scopus AI</h2>
                                <p class="text-scopus" style="text-align: justify; margin-right: 5px; font-size: 12px;">
                                    Akun Scopus Lisensi & Scopus AI memberi akses ke database jurnal ilmiah terbesar dengan
                                    dukungan AI untuk pencarian, analisis, dan rekomendasi publikasi. Solusi ideal bagi
                                    peneliti, akademisi, dan profesional yang membutuhkan sumber ilmiah terkini.</p>
                                <a href="#" class="btn-shop">Explore Products <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="row gy-4">
                            <div class="col-xl-6">
                                <div class="category-card cat-men" data-aos="fade-up" data-aos-delay="300">
                                    <div class="category-image">
                                        <img src="{{ 'niceshop/assets/img/product/grammarly.png' }}" alt="Men's Fashion" class="img-fluid">
                                    </div>
                                    <div class="category-content">
                                        <h4>Grammarly Premium</h4>
                                        <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="category-card cat-kids" data-aos="fade-up" data-aos-delay="400">
                                    <div class="category-image">
                                        <img src="{{ 'niceshop/assets/img/product/quillbot.png' }}" alt="Kid's Fashion" class="img-fluid">
                                    </div>
                                    <div class="category-content">
                                        <h4>Quillbot Premium</h4>
                                        <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="category-card cat-cosmetics" data-aos="fade-up" data-aos-delay="500">
                                    <div class="category-image">
                                        <img src="{{ 'niceshop/assets/img/product/consensus.png' }}" alt="Cosmetics" class="img-fluid">
                                    </div>
                                    <div class="category-content">
                                        <h4>Consensus Premium</h4>
                                        <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="category-card cat-accessories" data-aos="fade-up" data-aos-delay="600">
                                    <div class="category-image">
                                        <img src="{{ 'niceshop/assets/img/product/gamma.png' }}" alt="Accessories" class="img-fluid">
                                    </div>
                                    <div class="category-content">
                                        <h4>Gamma AI Premium</h4>
                                        <a href="#" class="card-link">Shop Now <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!--================== FLASH SALE ==================-->
        <section id="call-to-action" class="call-to-action section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row">
                    <div class="mx-auto col-lg-8">
                        <div class="text-center main-content" data-aos="zoom-in" data-aos-delay="200">
                            <div class="offer-badge" data-aos="fade-down" data-aos-delay="250">
                                <span class="limited-time">Limited Time</span>
                                <span class="offer-text">50% OFF</span>
                            </div>

                            <h2 data-aos="fade-up" data-aos-delay="300">Exclusive Flash Sale</h2>

                            <p class="subtitle" data-aos="fade-up" data-aos-delay="350">Don't miss out on our biggest sale of the year. Premium quality products at unbeatable prices for the next 48 hours only.</p>

                            <div class="countdown-wrapper" data-aos="fade-up" data-aos-delay="400">
                                <div class="countdown d-flex justify-content-center" data-count="2025/12/31">
                                    <div>
                                        <h3 class="count-days"></h3>
                                        <h4>Days</h4>
                                    </div>
                                    <div>
                                        <h3 class="count-hours"></h3>
                                        <h4>Hours</h4>
                                    </div>
                                    <div>
                                        <h3 class="count-minutes"></h3>
                                        <h4>Minutes</h4>
                                    </div>
                                    <div>
                                        <h3 class="count-seconds"></h3>
                                        <h4>Seconds</h4>
                                    </div>
                                </div>
                            </div>

                            <div class="action-buttons" data-aos="fade-up" data-aos-delay="450">
                                <a href="#" class="btn-shop-now">Shop Now</a>
                                <a href="#" class="btn-view-deals">View All Deals</a>
                            </div>
                        </div>
                    </div>
                </div>

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
                    </div>
                </div>
            </div>
        </section>
        <!--================== END ==================-->

        <!-- Best Sellers Section -->
        <section id="best-sellers" class="best-sellers section">

            <!-- Section Title -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Best Sellers</h2>
                <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
            </div>
            <!-- End Section Title -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-5">
                    @foreach ($product as $item)
                    <div class="col-lg-3 col-md-6">
                        <div class="product-item">
                            <div class="product-image">

                                <img src="{{ asset('storage/img/product/' . $item->image) }}"
                                    alt="{{ $item->nama_akun }}"
                                    class="img-fluid"
                                    loading="lazy">

                                <a href="{{ route('productdetail', $item->id) }}" class="cart-btn">
                                    Detail Product
                                </a>
                            </div>

                            <div class="product-info">
                                <div class="product-category">Premium Collection</div>

                                <h4 class="product-name">
                                    <a href="{{ route('productdetail', $item->id) }}">
                                        {{ $item->nama_akun }}
                                    </a>
                                </h4>

                                <div class="product-rating">
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star-fill"></i>
                                        <i class="bi bi-star"></i>
                                    </div>
                                    <span class="rating-count">(24)</span>
                                </div>

                                {{-- Harga (ambil harga bulanan sebagai contoh) --}}
                                <div class="product-price">
                                    {{ $item->formatted('harga_perbulan') }}/bulan
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section><!-- /Best Sellers Section -->
    </main>
</x-guest-layout>