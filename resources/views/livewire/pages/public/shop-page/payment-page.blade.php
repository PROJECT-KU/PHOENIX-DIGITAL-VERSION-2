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

                            @foreach ($order->items as $item)
                                <div class="mb-2 d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $item->product_name }}</strong><br>
                                        <small class="text-muted">{{ $item->getDurationLabel() }}
                                            x{{ $item->quantity }}</small>
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
                                    <span class="text-muted">
                                        {{ optional($payment->expired_at)->format('d F Y, H:i') }} WIB
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QRIS Payment -->
                    @if ($qrCodeImage)

                        <div wire:poll.15s="checkPaymentStatus">

                            <div class="card border-success mb-4">
                                <div class="card-body text-center">

                                    <h5 class="mb-3">
                                        <i class="bi bi-qr-code-scan"></i>
                                        Scan QRIS Untuk Pembayaran
                                    </h5>

                                    <img src="data:image/png;base64,{{ $qrCodeImage }}" alt="QRIS"
                                        class="img-fluid border rounded p-2 bg-white" style="max-width:300px;">

                                    <div class="mt-3">
                                        <h4 class="text-primary">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </h4>
                                    </div>

                                    @if ($qrisNmid)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                NMID: {{ $qrisNmid }}
                                            </small>
                                        </div>
                                    @endif

                                    @if ($qrisInvoiceId)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                Invoice QRIS: {{ $qrisInvoiceId }}
                                            </small>
                                        </div>
                                    @endif

                                    {{-- STATUS EXPIRED --}}
                                    @if (!$payment->isExpired())
                                        <div class="alert alert-warning mt-3">
                                            <strong>Sisa Waktu Pembayaran</strong><br>
                                            <span id="countdown"
                                                data-expired="{{ $payment->expired_at->format('Y-m-d H:i:s') }}">
                                            </span>
                                        </div>
                                    @else
                                        <div class="alert alert-danger mt-3">
                                            <strong>QRIS Sudah Kadaluarsa</strong>
                                        </div>

                                        <button wire:click="generateNewQris" class="btn btn-primary">
                                            Buat QRIS Baru
                                        </button>
                                    @endif

                                    <div class="alert alert-info mt-3 text-start">
                                        <strong>Cara Pembayaran:</strong>
                                        <ol class="mb-0 mt-2">
                                            <li>Buka aplikasi Mobile Banking atau E-Wallet.</li>
                                            <li>Pilih menu Scan QRIS.</li>
                                            <li>Scan QR Code di atas.</li>
                                            <li>Pastikan nominal sesuai.</li>
                                            <li>Selesaikan pembayaran.</li>
                                        </ol>
                                    </div>

                                    @if (!$payment->isExpired())
                                        <div wire:poll.60s="checkPaymentStatus">

                                            <button wire:click="checkPaymentStatus" class="btn btn-success btn-lg"
                                                wire:loading.attr="disabled">

                                                <span wire:loading.remove>
                                                    <i class="bi bi-check-circle"></i>
                                                    Saya Sudah Bayar
                                                </span>

                                                <span wire:loading>
                                                    <span class="spinner-border spinner-border-sm"></span>
                                                    Mengecek...
                                                </span>

                                            </button>

                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    Status pembayaran diperiksa otomatis setiap 1 menit.
                                                </small>
                                            </div>

                                        </div>
                                    @endif

                                </div>
                            </div>

                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            QRIS tidak dapat dibuat.
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
                        <strong>Catatan:</strong> Jika Anda tidak menyelesaikan pembayaran dalam waktu yang ditentukan,
                        pesanan akan otomatis dibatalkan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
