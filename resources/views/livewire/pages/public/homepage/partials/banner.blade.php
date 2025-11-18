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