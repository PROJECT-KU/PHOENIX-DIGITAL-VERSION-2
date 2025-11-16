<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="border-0 card">
                <div class="card-body">
                    <div class="mb-4 text-center">
                        <i class="bi bi-credit-card-2-front text-primary" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">Pembayaran</h3>
                        <p class="text-muted">Order #{{ $order->order_number }}</p>
                    </div>

                    @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Order Summary -->
                    <div class="mb-4 card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">Detail Pesanan</h6>

                            @foreach($order->items as $item)
                            <div class="mb-2 d-flex justify-content-between">
                                <div>
                                    <strong>{{ $item->product_name }}</strong><br>
                                    <small class="text-muted">{{ $item->getDurationLabel() }} x{{ $item->quantity }}</small>
                                </div>
                                <div>
                                    <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            @endforeach

                            <hr>

                            <div class="d-flex justify-content-between">
                                <h5>Total Pembayaran</h5>
                                <h4 class="text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="mb-4 card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history text-warning fs-4 me-3"></i>
                                <div>
                                    <strong>Batas Waktu Pembayaran</strong><br>
                                    <span class="text-muted">{{ $order->expired_at->format('d F Y, H:i') }} WIB</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    @if($paymentUrl)
                    <div class="gap-2 mb-3 d-grid">
                        <a href="{{ $paymentUrl }}"
                            target="_blank"
                            class="btn btn-primary btn-lg">
                            <i class="bi bi-wallet2"></i> Bayar Sekarang
                        </a>
                    </div>

                    <div class="text-center">
                        <button wire:click="checkPaymentStatus"
                            class="btn btn-outline-primary"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm"></span>
                                Mengecek...
                            </span>
                        </button>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Gagal membuat link pembayaran. Silakan hubungi admin.
                    </div>
                    @endif

                    <hr class="my-4">

                    <!-- Customer Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informasi Pelanggan</h6>
                            <p class="mb-1"><strong>Nama:</strong> {{ $order->customer->nama }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->customer->email }}</p>
                            <p class="mb-1"><strong>No HP:</strong> {{ $order->customer->no_hp }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Status Pesanan</h6>
                            <p class="mb-1">
                                <strong>Status:</strong>
                                {!! $order->getStatusBadge() !!}
                            </p>
                            <p class="mb-1">
                                <strong>Dibuat:</strong>
                                {{ $order->created_at->format('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-4 card">
                <div class="card-body">
                    <h6 class="mb-3">Petunjuk Pembayaran</h6>
                    <ol>
                        <li>Klik tombol <strong>"Bayar Sekarang"</strong> di atas</li>
                        <li>Pilih metode pembayaran yang Anda inginkan (QRIS, E-Wallet, Bank Transfer, dll)</li>
                        <li>Ikuti instruksi pembayaran yang muncul</li>
                        <li>Setelah pembayaran berhasil, Anda akan menerima email konfirmasi</li>
                        <li>Akun premium Anda akan dikirimkan via email maksimal 1x24 jam</li>
                    </ol>

                    <div class="mt-3 alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Catatan:</strong> Jika Anda tidak menyelesaikan pembayaran dalam waktu yang ditentukan, pesanan akan otomatis dibatalkan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>