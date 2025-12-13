<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="text-center card">
                <div class="py-5 card-body">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>

                    <h2 class="mb-3">Pembayaran Berhasil!</h2>
                    <p class="mb-4 text-muted">
                        Terima kasih telah melakukan pemesanan.<br>
                        Order #{{ $order->order_number }}
                    </p>

                    <div class="alert alert-success">
                        <i class="bi bi-envelope-check"></i>
                        <strong>Email konfirmasi telah dikirim ke:</strong><br>
                        {{ $order->customer->email }}
                    </div>

                    <div class="mb-4 card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3">Detail Pesanan Anda</h6>

                            @foreach($order->items as $item)
                            <div class="mb-2 d-flex justify-content-between">
                                <div class="text-start">
                                    <strong>{{ $item->product_name }}</strong><br>
                                    <small class="text-muted">{{ $item->getDurationLabel() }}</small>
                                </div>
                                <div>
                                    <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                            @endforeach

                            <hr>

                            <div class="d-flex justify-content-between">
                                <h6>Total Dibayar</h6>
                                <h5 class="text-success">Rp {{ number_format($order->total, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-clock"></i>
                        <strong>Akun premium Anda akan dikirimkan via email maksimal dalam 1x24 jam.</strong>
                    </div>

                    <div class="gap-2 d-grid">
                        <a href="{{ route('shop.index') }}" class="btn btn-primary">
                            <i class="bi bi-house"></i> Kembali ke Beranda
                        </a>
                    </div>

                    <div class="mt-4 text-muted">
                        <small>
                            Ada pertanyaan? Hubungi kami di<br>
                            <i class="bi bi-whatsapp"></i> +62 812-3456-7890
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>