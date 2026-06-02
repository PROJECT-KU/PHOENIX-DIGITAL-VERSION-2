<div>
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0 text-muted">Riwayat Pesanan</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Riwayat Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <div class="container py-3">
        <div class="d-flex justify-content-end align-items-center mb-2">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#restoreModal">
                <i class="bi bi-arrow-repeat me-2"></i>Pulihkan Riwayat
            </button>
        </div>

        @if($this->myOrders->count() > 0)
        <div class="accordion" id="orderAccordion" wire:poll.15s>
            @foreach($this->myOrders as $order)
            <div class="accordion-item mb-3 border rounded overflow-hidden">
                <h2 class="accordion-header" id="heading{{ $order->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $order->id }}">
                        <div class="d-flex w-100 justify-content-between align-items-center me-3">
                            <div class="d-flex flex-column gap-2">
                                <span class="fw-bold text-dark">{{ $order->order_number }}</span>
                                <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                            </div>
                            <div>
                                @php
                                $badgeClass = match($order->status) {
                                'paid', 'completed' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill me-2">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <span class="fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse{{ $order->id }}" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="text-muted">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Durasi</th>
                                        <th class="text-end">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td class="py-2">
                                            {{ $item->product_name }}
                                        </td>
                                        <td class="text-center py-2">
                                            {{ $item->duration_value }} {{ ucfirst($item->duration_type) }}
                                        </td>
                                        <td class="text-end py-2">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-top">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold pt-3">Total Bayar</td>
                                        <td class="text-end fw-bold pt-3 text-primary">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-cart-x display-1 text-muted"></i>
            </div>
            <h4 class="text-muted">Belum ada riwayat pesanan</h4>
            <p class="text-secondary mb-4">
                Pesanan Anda akan muncul di sini secara otomatis.<br>
                Jika Anda memesan lewat perangkat lain, gunakan fitur <strong>Pulihkan Riwayat</strong>.
            </p>
            <a href="{{ route('shop.index') }}" class="btn btn-primary">Mulai Belanja</a>
        </div>
        @endif

        <div wire:ignore.self class="modal fade" id="restoreModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Pulihkan Riwayat Pesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">
                            Masukkan Nomor HP dan Kode Invoice dari salah satu pesanan Anda sebelumnya untuk menyinkronkan data ke perangkat ini.
                        </p>

                        <form wire:submit.prevent="restoreSession">
                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp</label>
                                <input type="number" wire:model="phoneNumber" class="form-control @error('phoneNumber') is-invalid @enderror" placeholder="0821*********">
                                @error('phoneNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kode Invoice / No. Pesanan</label>
                                <input type="text" wire:model="invoiceCode" class="form-control @error('invoiceCode') is-invalid @enderror" placeholder="kode pesanan">
                                @error('invoiceCode') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="restoreSession">Pulihkan Data</span>
                                    <span wire:loading wire:target="restoreSession">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    const restoreModalEl = document.getElementById('restoreModal');
    const restoreModal = new bootstrap.Modal(restoreModalEl);

    $wire.on('restore-success', (data) => {
        const modalInstance = bootstrap.Modal.getInstance(restoreModalEl);
        modalInstance.hide();

        Swal.fire({
            title: 'Berhasil!',
            text: data[0].message,
            icon: 'success',
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Oke'
        }).then((result) => {
            window.location.reload();
        });
    });
</script>
@endscript