<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Detail Pesanan {{ $order->order_number }}</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Pesanan Toko', 'url' => route('admin.pesanantoko.index')],
                ['name' => 'Detail Pesanan'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="mb-0 card">
            <div class="card-body">
                <a wire:navigate href="{{ route('admin.pesanantoko.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle me-1"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <h5 class="card-title">Data Pesanan</h5>
                        <p class="mb-1"><strong>No. Order:</strong> {{ $order->order_number }}</p>
                        <p class="mb-1"><strong>Tanggal:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</p>
                        <p class="mb-0"><strong>Status:</strong> {{ $order->status }}</p>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="card-title">Data Pembeli</h5>
                        <p class="mb-1"><strong>Nama:</strong> {{ $order->customer->nama ?? '-' }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->customer->email ?? '-' }}</p>
                        <p class="mb-0"><strong>Telepon:</strong> {{ $order->customer->no_hp ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Item Pesanan</h5>
                <div class="table-responsive">
                    <table class="table align-middle table-sm">
                        <thead class="table-secondary">
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Durasi</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product->nama_akun ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->duration_value }} {{ $item->duration_type }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                                    <td>{!! $item->getDeliveryStatusBadge() !!}</td>
                                    <td>
                                        <a wire:navigate href="{{ route('admin.pesanantoko.process', $item->id) }}"
                                            class="btn btn-sm btn-primary">Proses Pesanan</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        Belum ada item pesanan</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($order->items->count())
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th colspan="2">Rp
                                        {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
