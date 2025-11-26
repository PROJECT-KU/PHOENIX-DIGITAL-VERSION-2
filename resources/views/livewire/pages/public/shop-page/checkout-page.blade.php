<div>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Checkout</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li><a href="{{ route('cart') }}">Keranjang</a></li>
                    <li class="current">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section style="padding-top: 20px;" class="checkout section">
        <div class="container">
            <div class="checkout-container">
                <form wire:submit="checkout" class="checkout-form">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="checkout-section" id="customer-info">
                                <div class="section-header">
                                    <h3>Informasi Pelanggan</h3>
                                </div>

                                <div class="section-content">
                                    <!-- No HP dengan auto search -->
                                    <div class="form-group">
                                        <label class="form-label">Nomor HP / WhatsApp *</label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control @error('no_hp') is-invalid @enderror"
                                                wire:model.live.debounce.500ms="no_hp" placeholder="08123456789"
                                                maxlength="15">
                                            @if ($isLoadingCustomer)
                                                <span class="input-group-text">
                                                    <span class="spinner-border spinner-border-sm"></span>
                                                </span>
                                            @endif
                                        </div>
                                        @error('no_hp')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @if ($customerFound)
                                            <small class="text-success">
                                                <i class="bi bi-check-circle"></i> Data pelanggan ditemukan
                                            </small>
                                        @endif
                                    </div>

                                    <!-- Nama -->
                                    <div class="form-group">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                            wire:model="nama" placeholder="Nama lengkap Anda"
                                            {{ $customerFound ? 'readonly' : '' }}>
                                        @error('nama')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="form-group">
                                        <label class="form-label">Email *</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email" placeholder="email@example.com"
                                            {{ $customerFound ? 'readonly' : '' }}>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Opsi Penggunaan Poin -->
                                    @if ($showPointsOption)
                                        <div class="form-group">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" id="usePoints"
                                                            wire:model.live="usePoints">
                                                        <label class="form-check-label" for="usePoints">
                                                            <strong>Gunakan Poin Member</strong>
                                                        </label>
                                                    </div>
                                                    <small class="text-muted d-block mt-2">
                                                        <i class="bi bi-star-fill text-warning"></i>
                                                        Anda memiliki
                                                        <strong>{{ number_format($availablePoints, 0, ',', '.') }}
                                                            poin</strong>
                                                        (senilai Rp {{ number_format($pointsValue, 0, ',', '.') }})
                                                    </small>
                                                    @if ($usePoints)
                                                        <div class="alert alert-info mt-2 mb-0">
                                                            <small>
                                                                <i class="bi bi-info-circle"></i>
                                                                Poin Anda akan digunakan untuk mengurangi total
                                                                pembayaran
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Catatan -->
                                    <div class="form-group">
                                        <label class="form-label">Catatan (Opsional)</label>
                                        <textarea class="form-control" wire:model="customer_notes" rows="3"
                                            placeholder="Catatan tambahan untuk pesanan Anda"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="checkout-section">
                                <div class="section-header">
                                    <h3>Ringkasan Pesanan</h3>
                                </div>

                                <div class="section-content">
                                    @foreach ($cart as $item)
                                        <div class="mb-2 d-flex justify-content-between">
                                            <small>
                                                {{ $item['product_name'] }}<br>
                                                <span class="text-muted">{{ $item['duration_value'] }}
                                                    {{ $item['duration_type'] }} x{{ $item['quantity'] }}</span>
                                            </small>
                                            <small><strong>Rp
                                                    {{ number_format($item['subtotal'], 0, ',', '.') }}</strong></small>
                                        </div>
                                    @endforeach

                                    <hr>

                                    <div class="mb-2 d-flex justify-content-between">
                                        <span>Subtotal</span>
                                        <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                                    </div>

                                    @if ($discount > 0)
                                        <div class="mb-2 d-flex justify-content-between text-danger">
                                            <span>
                                                <i class="bi bi-star-fill"></i> Diskon Poin
                                            </span>
                                            <strong>- Rp {{ number_format($discount, 0, ',', '.') }}</strong>
                                        </div>
                                        <hr>
                                    @endif

                                    <div class="mb-3 d-flex justify-content-between">
                                        <h6>Total Pembayaran</h6>
                                        <h5 class="{{ $discount > 0 ? 'text-success' : '' }}">
                                            Rp {{ number_format($finalTotal, 0, ',', '.') }}
                                        </h5>
                                    </div>

                                    @if ($finalTotal == 0 && $usePoints)
                                        <div class="alert alert-success">
                                            <small>
                                                <i class="bi bi-check-circle"></i>
                                                Pesanan Anda akan dibayar penuh dengan poin!
                                            </small>
                                        </div>
                                    @endif

                                    <!-- Kode Referral (Tambahkan section ini) -->
                                    @if ($showReferralInput)
                                        <div class="form-group">
                                            <label class="form-label">
                                                Kode Referral
                                                <span class="badge bg-info text-white ms-1">Opsional</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" wire:model="referralCode"
                                                    placeholder="kode referral" maxlength="9"
                                                    style="text-transform: uppercase;"
                                                    {{ $referralValid ? 'readonly' : '' }}>

                                                @if (!$referralValid)
                                                    <button class="btn btn-outline-primary" type="button"
                                                        wire:click="checkReferralCode" wire:loading.attr="disabled"
                                                        wire:target="checkReferralCode">
                                                        <span wire:loading.remove wire:target="checkReferralCode">
                                                            <i class="bi bi-check-circle"></i> Validasi
                                                        </span>
                                                        <span wire:loading wire:target="checkReferralCode">
                                                            <span class="spinner-border spinner-border-sm"></span>
                                                        </span>
                                                    </button>
                                                @else
                                                    <button class="btn btn-success" type="button" disabled>
                                                        <i class="bi bi-check-circle-fill"></i> Tervalidasi
                                                    </button>
                                                @endif
                                            </div>

                                            @if ($referralMessage)
                                                <small
                                                    class="d-block mt-2 {{ $referralValid ? 'text-success' : 'text-danger' }}">
                                                    <i
                                                        class="bi {{ $referralValid ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                                    {{ $referralMessage }}
                                                </small>
                                            @endif

                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-info-circle"></i>
                                                Punya kode referral dari teman? Masukkan untuk mendapat keuntungan
                                                bersama!
                                            </small>
                                        </div>
                                    @endif

                                    <div class="place-order-container">
                                        <button type="submit" class="btn btn-primary place-order-btn w-100"
                                            wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                {{ $finalTotal > 0 ? 'Bayar Sekarang' : 'Selesaikan Pesanan' }}
                                            </span>
                                            @if ($finalTotal > 0)
                                                <span wire:loading.remove class="btn-price">
                                                    Rp {{ number_format($finalTotal, 0, ',', '.') }}
                                                </span>
                                            @endif
                                            <span wire:loading>
                                                <span class="spinner-border spinner-border-sm"></span>
                                                Memproses...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
