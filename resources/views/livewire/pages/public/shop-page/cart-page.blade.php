<div>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Keranjang</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Keranjang</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section id="section" class="checkout section" style="padding-top: 20px;">
        <div class="container">
            @if (empty($cart))
                <div class="my-5 text-center">
                    <i class="bi bi-cart-x fs-1"></i>
                    <p class="mt-3 mb-3">Keranjang Anda kosong</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Mulai Belanja</a>
                </div>
            @else
                <div class="row">
                    <div class="col-lg-8">
                        <div class="order-summary">
                            <div class="order-summary-header">
                                <h3>Produk Pilihanmu</h3>

                                <button wire:click="$dispatch('confirm-empty-cart')"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="mr-2 bi bi-trash"></i> Kosongkan Keranjang
                                </button>
                            </div>
                            <div class="gap-3 flex-column order-summary-content d-flex">
                                @foreach ($cart as $key => $item)
                                    <div class="d-flex align-items-center">
                                        @if ($item['product_image'])
                                            <img src="{{ $item['product_image'] ? asset('storage/img/product/' . $item['product_image']) : 'https://via.placeholder.com/80' }}"
                                                alt="{{ $item['product_name'] }}" class="rounded"
                                                style="width: 80px; height: 80px; object-fit: cover;">
                                        @else
                                            <img class="rounded" style="object-fit: cover; width: 80px; height: 80px;"
                                                src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                alt="">
                                        @endif

                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">{{ $item['product_name'] }}</h6>
                                            <small class="text-muted">
                                                Paket {{ $item['duration_value'] }} {{ $item['duration_type'] }}
                                            </small>
                                            <div class="mt-2">
                                                <strong>Rp {{ number_format($item['price'], 0, ',', '.') }}</strong>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center">
                                            <button
                                                wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <span class="mx-3">{{ $item['quantity'] }}</span>
                                            <button
                                                wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                                class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>

                                        <div class="ms-4 text-end" style="min-width: 120px;">
                                            <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                                        </div>
                                        <button
                                            wire:click="$dispatch('confirm-delete-product-cart', '{{ $key }}')"
                                            class="btn btn-sm btn-outline-danger ms-3">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="checkout-section">
                            <div class="section-header">
                                <h3>Ringkasan Pesanan</h3>
                            </div>
                            <div class="section-content">
                                <div class="mb-3 d-flex justify-content-between">
                                    <span>Jumlah Produk</span>
                                    <strong>{{ $totalQuantity }} Produk</strong>
                                </div>
                                <div class="mb-3 d-flex justify-content-between">
                                    <span>Subtotal</span>
                                    <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                </div>
                                <div class="mt-5 place-order-container">
                                    <a href="{{ route('checkout') }}" type="button"
                                        class="btn btn-primary place-order-btn">
                                        <span class="btn-text">Checkout</span>
                                        <span class="btn-price">Rp
                                            {{ number_format($total, 0, ',', '.') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
