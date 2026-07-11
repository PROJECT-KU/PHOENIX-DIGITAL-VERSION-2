<div @if ($flashSale && !$showDurationModal) wire:poll.1s="updateTimer" @endif id="call-to-action"
    class="{{ $flashSale ? 'call-to-action section' : '' }}">
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
                                <div class="timer-box">
                                    <h3 class="count-days">
                                        {{ str_pad($timeRemaining['days'] ?? 0, 2, '0', STR_PAD_LEFT) }}</h3>
                                    <h4 class="text-muted">Hari</h4>
                                </div>

                                <!-- Hours -->
                                <div class="timer-box">
                                    <h3 class="count_hours">
                                        {{ str_pad($timeRemaining['hours'] ?? 0, 2, '0', STR_PAD_LEFT) }}</h3>
                                    <h4 class="text-muted">Jam</h4>
                                </div>

                                <!-- Minutes -->
                                <div class="timer-box">
                                    <h3 class="count-minutes">
                                        {{ str_pad($timeRemaining['minutes'] ?? 0, 2, '0', STR_PAD_LEFT) }}
                                    </h3>
                                    <h4 class="text-muted">Menit</h4>
                                </div>

                                <!-- Seconds -->
                                <div class="timer-box">
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

            <div class="row featured-products-row g-3 g-lg-4">
                @foreach ($featuredProducts as $product)
                    @php
                        $originalPrice = $product->harga_perbulan;
                        $discountedPrice = $this->getDiscountedPrice($originalPrice);
                        $best = $this->getBestDiscount();
                    @endphp
                    <div class="col-lg-3 col-md-6">
                        <div class="fs-card">
                            <div class="fs-card-media">
                                @if ($product->image)
                                    <img src="{{ asset('storage/img/product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}">
                                @else
                                    <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                        alt="{{ $product->nama_akun }}">
                                @endif
                                @if ($best)
                                    <span class="fs-badge">Diskon s.d.
                                        @if ($best['isNominal'])
                                            Rp{{ number_format($best['value'], 0, ',', '.') }}
                                        @else
                                            {{ number_format($best['value'], 0) }}%
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="fs-card-body">
                                <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-name">{{ $product->nama_akun }}</a>
                                <div class="fs-price">
                                    <span class="fs-price-sale">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                    @if ($discountedPrice < $originalPrice)
                                        <span class="fs-price-orig">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <small>/bln</small>
                                </div>
                                <div class="fs-actions">
                                    <button type="button" wire:click="openDuration('{{ $product->id }}')"
                                        wire:loading.attr="disabled" wire:target="openDuration('{{ $product->id }}')"
                                        class="fs-btn-cart">
                                        <span wire:loading.remove wire:target="openDuration('{{ $product->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                        <span wire:loading wire:target="openDuration('{{ $product->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                    <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-btn-view">Lihat</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== Modal Pilih Durasi ===== --}}
    @if ($showDurationModal)
        <div class="fs-modal-overlay" wire:key="fs-dur-modal" wire:click.self="closeDuration">
            <div class="fs-modal">
                <button type="button" class="fs-modal-close" wire:click="closeDuration" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

                <div class="fs-modal-head">
                    <div class="fs-modal-thumb">
                        @if ($pickProductImage)
                            <img src="{{ asset('storage/img/product/' . $pickProductImage) }}" alt="{{ $pickProductName }}">
                        @else
                            <i class="bi bi-box-seam"></i>
                        @endif
                    </div>
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow"><i class="bi bi-lightning-charge-fill"></i> Flash Sale</span>
                        <h4>{{ $pickProductName }}</h4>
                        <p>Pilih durasi langganan</p>
                    </div>
                </div>

                <div class="fs-modal-options">
                    @foreach ($pickPackages as $p)
                        @php $active = ($pickType === $p['duration_type'] && (int) $pickValue === (int) $p['duration_value']); @endphp
                        <button type="button" class="fs-opt {{ $active ? 'is-active' : '' }}"
                            wire:click="selectPackage('{{ $p['duration_type'] }}', {{ $p['duration_value'] }})">
                            <span class="fs-opt-radio"></span>
                            <span class="fs-opt-info">
                                <span class="fs-opt-label">{{ $p['label'] }}</span>
                                @if (!empty($p['savings']) && $p['savings'] > 0)
                                    <span class="fs-opt-save">Hemat Rp{{ number_format($p['savings'], 0, ',', '.') }}</span>
                                @endif
                            </span>
                            <span class="fs-opt-price">
                                @if (($p['discounted'] ?? $p['price']) < $p['price'])
                                    <span class="fs-opt-orig">Rp{{ number_format($p['price'], 0, ',', '.') }}</span>
                                @endif
                                <span class="fs-opt-now">Rp{{ number_format($p['discounted'] ?? $p['price'], 0, ',', '.') }}</span>
                            </span>
                        </button>
                    @endforeach

                    {{-- Durasi custom (bila produk punya harga per bulan) --}}
                    @if ($pickPerBulan > 0)
                        @php
                            $customBase = $pickCustomMonths * $pickPerBulan;
                            $customDisc = (int) round($this->getDiscountedPrice($customBase));
                        @endphp
                        <div class="fs-opt fs-opt-custom {{ $pickIsCustom ? 'is-active' : '' }}">
                            <span class="fs-opt-radio" wire:click="chooseCustom"></span>
                            <span class="fs-opt-info" wire:click="chooseCustom">
                                <span class="fs-opt-label">Durasi lain</span>
                                <span class="fs-opt-sub">Rp{{ number_format($pickPerBulan, 0, ',', '.') }}/bulan</span>
                            </span>
                            <div class="fs-stepper">
                                <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                <span class="fs-stepper-val">{{ $pickCustomMonths }} bln</span>
                                <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                            </div>
                        </div>
                        @if ($pickIsCustom)
                            <div class="fs-custom-total">
                                <span class="fs-custom-total-left">
                                    Total {{ $pickCustomMonths }} bulan
                                    @if ($customBase > $customDisc)
                                        <span class="fs-opt-save">Hemat Rp{{ number_format($customBase - $customDisc, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="fs-custom-total-price">
                                    @if ($customDisc < $customBase)
                                        <span class="fs-opt-orig">Rp{{ number_format($customBase, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="fs-opt-now">Rp{{ number_format($customDisc, 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

                <button type="button" class="fs-modal-add" wire:click="confirmAddToCart"
                    wire:loading.attr="disabled" wire:target="confirmAddToCart">
                    <span wire:loading.remove wire:target="confirmAddToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                    <span wire:loading wire:target="confirmAddToCart"><span class="spinner-border spinner-border-sm"></span> Memproses…</span>
                </button>
            </div>
        </div>
    @endif
</div>
