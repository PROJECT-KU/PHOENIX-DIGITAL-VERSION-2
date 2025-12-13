<div wire:poll.1s="updateTimer" id="call-to-action" class="call-to-action section">
    @if ($flashSale)
        <div class="container">
            <div class="row">
                <div class="mx-auto col-lg-8">
                    <div class="text-center main-content">
                        <div class="offer-badge">
                            <span class="limited-time">Diskon Hingga</span>
                            @if ($flashSale->tipe_diskon === 'persen')
                                <span class="offer-text">{{ number_format($flashSale->diskon_member_persen, 0) }}%</span>
                            @else
                                <span
                                    class="offer-text">{{ number_format($flashSale->diskon_member_nominal, 0) }}%</span>
                            @endif
                        </div>
                        <h2>{{ $flashSale->badge_text ?? 'FLASH SALE' }}</h2>

                        <div class="subtitle">
                            <h3 class="mb-0 fw-bold">{{ $flashSale->nama_promo }}</h3>
                            @if ($flashSale->deskripsi)
                                <p class="mb-0 opacity-90">{{ $flashSale->deskripsi }}</p>
                            @endif
                        </div>

                        <!-- Countdown Timer -->
                        <div class="countdown-wrapper">
                            <p class="mb-2 fw-bold text-muted">Berakhir dalam:</p>
                            <div class="countdown d-flex justify-content-center">
                                <!-- Days -->
                                <div class="timer-box bg-white text-dark rounded p-3">
                                    <h3 class="count-days">
                                        {{ str_pad($timeRemaining['days'] ?? 0, 2, '0', STR_PAD_LEFT) }}</h3>
                                    <h4 class="text-muted">Hari</h4>
                                </div>

                                <!-- Hours -->
                                <div class="timer-box bg-white text-dark rounded p-3">
                                    <h3 class="count_hours">
                                        {{ str_pad($timeRemaining['hours'] ?? 0, 2, '0', STR_PAD_LEFT) }}</h3>
                                    <h4 class="text-muted">Jam</h4>
                                </div>

                                <!-- Minutes -->
                                <div class="timer-box bg-white text-dark rounded p-3">
                                    <h3 class="count-minutes">
                                        {{ str_pad($timeRemaining['minutes'] ?? 0, 2, '0', STR_PAD_LEFT) }}
                                    </h3>
                                    <h4 class="text-muted">Menit</h4>
                                </div>

                                <!-- Seconds -->
                                <div class="timer-box bg-white text-dark rounded p-3">
                                    <h3 class="count-seconds">
                                        {{ str_pad($timeRemaining['seconds'] ?? 0, 2, '0', STR_PAD_LEFT) }}
                                    </h3>
                                    <small class="text-muted">Detik</small>
                                </div>
                            </div>
                        </div>


                        <div class="action-buttons">
                            <a href="{{ route('shop.index') }}" class="btn-shop-now">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row featured-products-row">
                @foreach ($featuredProducts as $product)
                    @php
                        $originalPrice = $product->harga_perbulan;
                        $discountedPrice = $this->getDiscountedPrice($originalPrice);
                        $discountPercentage =
                            $originalPrice > 0
                                ? round((($originalPrice - $discountedPrice) / $originalPrice) * 100)
                                : 0;
                    @endphp
                    <div class="col-lg-3 col-md-6">
                        <div class="product-showcase">
                            <div class="product-image">
                                @if ($product->image)
                                    <img src="{{ asset('storage/img/product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}" class="img-fluid">
                                @else
                                    <img class="main-image img-fluid" style="object-fit: cover"
                                        src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                        alt="">
                                @endif
                                @if ($discountPercentage > 0)
                                    <div class="discount-badge">-{{ $discountPercentage }}%</div>
                                @endif
                            </div>
                            <div class="product-details">
                                <a href="{{ route('shop.detail-product', $product->id) }}">
                                    {{ $product->nama_akun }}
                                </a>
                                <div class="price-section">
                                    <span
                                        class="original-price">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    <span
                                        class="sale-price text-muted">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
