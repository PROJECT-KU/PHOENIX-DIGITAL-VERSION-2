<main class="main">
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Shoping</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Home</a></li>
                    <li class="current">Paket Bundlings</li>
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
            </section>

            <section id="best-sellers" class="best-sellers section">
                <div class="container" data-aos="fade-up" data-aos-delay="100" wire:ignore.self>
                    <div class="row g-5">
                        @forelse ($bundlings as $item)
                        <div class="col-xl-3 col-lg-3 col-md-6" wire:key="bundling-{{ $item->id }}">
                            <div class="product-item shadow-sm rounded-4">
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

                <div class="mt-5" wire:ignore>
                    {{ $bundlings->links('vendor.pagination') }}
                </div>
            </section><!-- /Best Sellers Section -->
        </div>
    </section>
    <!-- end list product -->
</main>
