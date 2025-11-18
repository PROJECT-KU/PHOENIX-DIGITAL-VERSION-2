<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Pesanan Toko</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pesanan Toko']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="gap-2 d-flex align-items-center">
                <div class="mb-0 form-group position-relative has-icon-left w-25">
                    <input wire:model.defer="searchInput" wire:keydown.enter="searchCustomer" type="text"
                        class="form-control border-secondary" placeholder="cari kode pesanan atau no hp">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                @if ($this->search != '')
                    <button class="btn btn-outline-secondary" wire:click='resetSearch'><i
                            class="bi bi-x-circle"></i></button>
                @endif
                <button wire:click='searchCustomer' type="button" class="rounded btn btn-primary">cari</button>
            </div>

            <ul class="mt-3 mb-1 nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'all') active @endif" wire:click="setTab('all')">
                        <i class="bi bi-list-check me-1"></i>
                        <span>Semua Pesanan</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'neworder') active @endif"
                        wire:click="setTab('neworder')">
                        <i class="bi bi-bag-plus me-1"></i>
                        <span>Pesanan Baru</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'processing') active @endif"
                        wire:click="setTab('processing')">
                        <i class="bi bi-hourglass me-1"></i>
                        <span>Pesanan Diproses</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'completed') active @endif"
                        wire:click="setTab('completed')">
                        <i class="bi bi-bag-check me-1"></i>
                        <span>Pesanan Selesai</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'cancelled') active @endif"
                        wire:click="setTab('cancelled')">
                        <i class="bi bi-x-circle me-1"></i>
                        <span>Pesanan Dibatalkan</span>
                    </button>
                </li>
            </ul>

            <div class="card">
                <div class="p-0 card-body">
                    <table class="table mb-0 table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kode Pesanan</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tgl Pesan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->customer->nama }}</td>
                                    <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $color = '';
                                            if ($order->status == 'pending') {
                                                $color = 'warning';
                                            }
                                            if ($order->status == 'processing') {
                                                $color = 'info';
                                            }
                                            if ($order->status == 'paid') {
                                                $color = 'success';
                                            }
                                            if ($order->status == 'cancelled') {
                                                $color = 'danger';
                                            }
                                            if ($order->status == 'completed') {
                                                $color = 'primary';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ strtoupper($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"
                                            wire:click="$dispatch('show-order-detail', { id: {{ $order->id }} })">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        {{-- aksi lain: ubah status, dll --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-4 text-center">
                                        <div class="text-muted">
                                            <i class="mb-2 bi bi-inbox fs-1"></i>
                                            <p>Tidak ada data pemesanan yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
