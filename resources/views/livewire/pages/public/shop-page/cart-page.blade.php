<div>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-cart-fill"></i> Keranjang</span>
                <h1>Keranjang Belanja</h1>
                <p>Cek kembali produk pilihan Anda sebelum melanjutkan ke pembayaran.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Keranjang</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="cart-section">
        <div class="container">
            @if (empty($cart))
                <div class="ph-empty my-4">
                    <div class="ph-empty-art">
                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Keranjang kosong">
                            <defs>
                                <radialGradient id="peGlowC" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="peCart" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fbc25a" />
                                    <stop offset="100%" stop-color="#f26522" />
                                </linearGradient>
                            </defs>
                            <ellipse class="pe-glow" cx="120" cy="108" rx="78" ry="78" fill="url(#peGlowC)" />
                            <ellipse class="pe-shadow" cx="120" cy="184" rx="58" ry="8" fill="#e15a18" />

                            <g transform="translate(48,66)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                            <g transform="translate(196,86)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <g transform="translate(190,140)"><path class="pe-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                            <g transform="translate(58,150)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                            <g class="pe-float">
                                <path d="M40 60 H60 L86 92" fill="none" stroke="url(#peCart)" stroke-width="6"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M80 86 L184 86 L168 140 L96 140 Z" fill="url(#peCart)" />
                                <path d="M80 86 L184 86" stroke="#ffffff" stroke-opacity=".55" stroke-width="3"
                                    stroke-linecap="round" />
                                <path d="M110 92 L104 134 M134 92 L134 134 M158 92 L164 134" stroke="#ffffff"
                                    stroke-opacity=".35" stroke-width="2.5" stroke-linecap="round" />
                                <path d="M112 140 V150 M156 140 V150" stroke="#e15a18" stroke-width="3"
                                    stroke-linecap="round" />
                                <circle cx="112" cy="158" r="9" fill="#e15a18" />
                                <circle cx="156" cy="158" r="9" fill="#e15a18" />
                                <circle cx="112" cy="158" r="3.4" fill="#fff3e0" />
                                <circle cx="156" cy="158" r="3.4" fill="#fff3e0" />
                            </g>
                        </svg>
                    </div>
                    <h3 class="ph-empty-title">Keranjang Anda kosong</h3>
                    <p class="ph-empty-sub">Belum ada produk di keranjang. Yuk pilih akun premium &amp; tools AI favoritmu!</p>
                    <div class="ph-empty-actions">
                        <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    {{-- Daftar produk --}}
                    <div class="col-lg-8">
                        <div class="cart-card">
                            <div class="cart-card-head">
                                <h3><i class="bi bi-bag-check-fill"></i> Produk Pilihanmu <span class="cart-head-count">{{ $totalQuantity }}</span></h3>
                                <button type="button" wire:click="$dispatch('confirm-empty-cart')" class="cart-clear">
                                    <i class="bi bi-trash"></i> Kosongkan
                                </button>
                            </div>

                            <div class="cart-items">
                                @foreach ($cart as $key => $item)
                                    @php $isBundle = ($item['type'] ?? 'product') === 'bundling'; @endphp
                                    <div class="cart-item" wire:key="cart-{{ $key }}">
                                        <div class="cart-item-media">
                                            @if ($item['product_image'])
                                                <img src="{{ asset(($isBundle ? 'storage/img/ProductBundlings/' : 'storage/img/Product/') . $item['product_image']) }}"
                                                    alt="{{ $item['product_name'] }}">
                                            @else
                                                <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                    alt="{{ $item['product_name'] }}">
                                            @endif
                                        </div>

                                        <div class="cart-item-info">
                                            <h6 class="cart-item-name">{{ $item['product_name'] }}</h6>
                                            <span class="cart-item-dur">
                                                @if ($isBundle)
                                                    <i class="bi bi-box2-heart-fill"></i> Paket Bundling
                                                @else
                                                    <i class="bi bi-calendar2-week"></i> {{ $item['duration_value'] }} {{ ucfirst($item['duration_type']) }}
                                                @endif
                                            </span>
                                            @php
                                                $durVal = (int) ($item['duration_value'] ?? 1);
                                                $perBulan = (!$isBundle && ($item['duration_type'] ?? '') === 'bulan' && $durVal > 1)
                                                    ? intdiv((int) $item['price'], max(1, $durVal)) : null;
                                            @endphp
                                            <div class="cart-item-unit">
                                                @if ($perBulan)
                                                    Rp {{ number_format($perBulan, 0, ',', '.') }} <small>/ bulan × {{ $durVal }}</small>
                                                @else
                                                    Rp {{ number_format($item['price'], 0, ',', '.') }} <small>/ {{ $isBundle ? 'paket' : 'item' }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="cart-item-subtotal">
                                            <span class="cart-item-sub-label">Subtotal</span>
                                            <strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong>
                                        </div>

                                        <button type="button" class="cart-item-remove"
                                            wire:click="$dispatch('confirm-delete-product-cart', '{{ $key }}')" aria-label="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('shop.index') }}" class="cart-continue"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
                    </div>

                    {{-- Ringkasan --}}
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h3 class="cart-summary-title"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h3>
                            <div class="cart-summary-row">
                                <span>Jumlah Produk</span>
                                <strong>{{ $totalQuantity }} item</strong>
                            </div>
                            <div class="cart-summary-row">
                                <span>Subtotal</span>
                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="cart-summary-total">
                                <span>Total</span>
                                <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </div>

                            <a href="{{ route('checkout') }}" class="cart-checkout">
                                <span><i class="bi bi-lock-fill"></i> Checkout</span>
                                <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </a>

                            <p class="cart-summary-note"><i class="bi bi-info-circle"></i> Harga <b>belum termasuk promo</b> — diterapkan saat checkout.</p>
                            <p class="cart-summary-note"><i class="bi bi-shield-check"></i> Pembayaran aman — Transfer Bank &amp; QRIS.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
