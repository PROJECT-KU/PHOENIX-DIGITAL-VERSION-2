<!-- Best Sellers Section -->
<section id="best-sellers" class="best-sellers section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up" wire:ignore.self>
        <h2>Paket Bundling</h2>
    </div>
    <!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100" wire:ignore.self>
        <div class="row g-5">
            @foreach ($bundlings as $item)
            <div class="col-lg-4 col-md-6 mb-5" wire:key="bundling-{{ $item->id }}">
                <div class="product-item shadow-sm">
                    <div class="product-image-baru">
                        <div class="ratio ratio-21x9">
                            <img src="{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}" 
                                class="w-100 h-100"
                                style="object-fit:cover">
                        </div>
                    </div>

                    <div class="product-info-baru">
                        <div class="product-info-top">
                            <h2 class="product-name fw-bold">{{ $item->nama_paket }}</h2>

                            <ul class="product-category">
                                @foreach([1,2,3,4,5] as $i)
                                    @php $product = $item->{'product'.$i}; @endphp
                                    @if($product)
                                        <li>{{ $product->nama_akun }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="product-price">
                            <span class="old-price">{{ $item->harga_awal }}</span>
                            <span class="current-price">{{ $item->harga_bundling }}</span>
                        </div>

                        <button
                            type="button"
                            class="cart-btn"
                            wire:click="addToCart('{{ $item->id }}')"
                            wire:loading.attr="disabled"
                        >
                            Add to Cart
                        </button>

                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mx-auto col-lg-8">
        <div class="text-center main-content" data-aos="zoom-in" data-aos-delay="200">
            <div class="action-buttons" data-aos="fade-up" data-aos-delay="450">
                <a href="{{ route('bundling.product-bundlings') }}" class="btn-view-deals">View All Bundlings</a>
            </div>
        </div>
    </div>
</section><!-- /Best Sellers Section -->