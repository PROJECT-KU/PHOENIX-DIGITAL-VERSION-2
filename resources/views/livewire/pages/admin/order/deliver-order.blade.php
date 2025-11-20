<div>
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Kirim Akun ke Pelanggan</h4>
        </div>
        <div class="card-body">

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Preview Info -->
            <div class="mb-4 alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Peringatan:</strong> Pastikan data akun sudah benar sebelum mengirim ke pelanggan.
            </div>

            <!-- Order Info -->
            <div class="mb-4 card bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Informasi Pesanan</h6>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>Order Number:</strong></div>
                        <div class="col-8">{{ $order->order_number }}</div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>Produk:</strong></div>
                        <div class="col-8">{{ $orderItem->product_name }}</div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>Durasi:</strong></div>
                        <div class="col-8">{{ $orderItem->getDurationLabel() }}</div>
                    </div>

                    <hr>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>Pelanggan:</strong></div>
                        <div class="col-8">{{ $order->customer->nama }}</div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>Email:</strong></div>
                        <div class="col-8">
                            <a href="mailto:{{ $order->customer->email }}">
                                {{ $order->customer->email }}
                            </a>
                        </div>
                    </div>

                    <div class="mb-2 row">
                        <div class="col-4"><strong>No HP:</strong></div>
                        <div class="col-8">
                            <a href="https://wa.me/{{ $order->customer->no_hp }}" target="_blank">
                                {{ $order->customer->no_hp }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Details to Send -->
            <div class="mb-4 card border-primary">
                <div class="text-white card-header bg-primary">
                    <h6 class="mb-0">Detail Akun yang Akan Dikirim</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Username / Email:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $orderItem->account_username }}"
                                readonly>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="navigator.clipboard.writeText('{{ $orderItem->account_username }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Password:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $orderItem->account_password }}"
                                readonly>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="navigator.clipboard.writeText('{{ $orderItem->account_password }}')">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    @if ($orderItem->account_link)
                        <div class="mb-3">
                            <label class="form-label"><strong>Link Akses:</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $orderItem->account_link }}"
                                    readonly>
                                <a href="{{ $orderItem->account_link }}" target="_blank"
                                    class="btn btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label"><strong>Periode Berlangganan:</strong></label>
                        <div class="mb-0 alert alert-info">
                            <i class="bi bi-calendar-check"></i>
                            Mulai: <strong>{{ $orderItem->start_date->format('d F Y') }}</strong><br>
                            Sampai: <strong>{{ $orderItem->end_date->format('d F Y') }}</strong><br>
                            <small class="text-muted">({{ $orderItem->remaining_days }} hari)</small>
                        </div>
                    </div>

                    @if ($orderItem->account_notes)
                        <div class="mb-3">
                            <label class="form-label"><strong>Catatan untuk Pelanggan:</strong></label>
                            <div class="mb-0 alert alert-secondary">
                                {{ $orderItem->account_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Notes -->
            <form wire:submit="deliverToCustomer">
                <div class="mb-4">
                    <label class="form-label"><strong>Catatan Pengiriman (Opsional)</strong></label>
                    <textarea class="form-control" wire:model="deliveryNotes" rows="3"
                        placeholder="Tambahkan catatan pengiriman jika diperlukan"></textarea>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Dengan menekan tombol di bawah, sistem akan:
                    <ul class="mt-2 mb-0">
                        <li>Mengirim email ke pelanggan dengan detail akun</li>
                        <li>Mengubah status pesanan menjadi "Delivered"</li>
                        <li>Menandai bahwa akun sudah dikirim</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled"
                        onclick="return confirm('Yakin ingin mengirim akun ke pelanggan? Email akan langsung terkirim.')">
                        <span wire:loading.remove>
                            <i class="bi bi-send"></i> Kirim Akun ke Pelanggan
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm"></span>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
