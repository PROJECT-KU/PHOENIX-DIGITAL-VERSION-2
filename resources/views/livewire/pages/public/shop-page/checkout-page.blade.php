<div>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Checkout</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{route('shop.index')}}">Shop</a></li>
                    <li><a href="{{route('cart')}}">Keranjang</a></li>
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
                                                wire:model.live.debounce.500ms="no_hp"
                                                placeholder="08123456789"
                                                maxlength="15">
                                            @if($isLoadingCustomer)
                                            <span class="input-group-text">
                                                <span class="spinner-border spinner-border-sm"></span>
                                            </span>
                                            @endif
                                        </div>
                                        @error('no_hp')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @if($customerFound)
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Data pelanggan ditemukan
                                        </small>
                                        @endif
                                    </div>

                                    <!-- Nama -->
                                    <div class="form-group">
                                        <label class="form-label">Nama Lengkap *</label>
                                        <input type="text"
                                            class="form-control @error('nama') is-invalid @enderror"
                                            wire:model="nama"
                                            placeholder="Nama lengkap Anda"
                                            {{ $customerFound ? 'readonly' : '' }}>
                                        @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="form-group">
                                        <label class="form-label">Email *</label>
                                        <input type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email"
                                            placeholder="email@example.com"
                                            {{ $customerFound ? 'readonly' : '' }}>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Catatan -->
                                    <div class="form-group">
                                        <label class="form-label">Catatan (Opsional)</label>
                                        <textarea class="form-control"
                                            wire:model="customer_notes"
                                            rows="3"
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
                                    @foreach($cart as $item)
                                    <div class="mb-2 d-flex justify-content-between">
                                        <small>
                                            {{ $item['product_name'] }}<br>
                                            <span class="text-muted">{{ $item['duration_value'] }} {{ $item['duration_type'] }} x{{ $item['quantity'] }}</span>
                                        </small>
                                        <small><strong>Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</strong></small>
                                    </div>
                                    @endforeach

                                    <div class="mb-3 d-flex justify-content-between">
                                        <h6>Total</h6>
                                        <h5>Rp {{ number_format($total, 0, ',', '.') }}</h5>
                                    </div>

                                    <div class="place-order-container">
                                        <button type="submit"
                                            class="btn btn-primary place-order-btn"
                                            wire:loading.attr="disabled">
                                            <span wire:loading.remove>Bayar Sekarang</span>
                                            <span wire:loading.remove class="btn-price">Rp
                                                {{ number_format($total, 0, ',', '.') }}</span>
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