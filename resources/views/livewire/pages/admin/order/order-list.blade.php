
@section('title')
Data Pesanan || PT. Asthana Cipta Mandiri
@stop
<div wire:poll.15s="watchNewPayments">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Pesanan Toko</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pesanan Toko']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari kode pesanan, nama, no hp, email, produk, username akun...">

                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_pemesanantoko'))
                        <a wire:navigate href="{{ route('admin.pesanantoko.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Data</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== FILTER ==================-->
        <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                            style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <span>Filter Periode</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="filterMonth" class="form-select rounded-3" style="min-width: 180px;">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                            <option value="{{ $month['value'] }}">{{ ucfirst($month['label']) }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterYear" class="form-select rounded-3" style="min-width: 160px;">
                            <option value="">Semua Tahun</option>
                            @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        @if ($search || $filterMonth || $filterYear)
                        <button wire:click="resetFilters" type="button" class="btn btn-danger rounded-3"
                            title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Pusatkan ikon Bootstrap (bi) di stat-icon-wrapper */
            .stat-icon-wrapper i.bi {
                display: flex;
                align-items: center;
                justify-content: center;
                line-height: 1;
            }

            .stat-icon-wrapper i.bi::before {
                display: block;
                line-height: 1;
            }

            .customer-glossy-tabs {
                display: flex;
                width: 100%;
                gap: .5rem;
                padding: .5rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.55);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.6);
                box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12);
                overflow-x: auto;
            }

            .customer-glossy-tab {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .6rem;
                border: none;
                background: transparent;
                color: #6b7280;
                font-weight: 600;
                font-size: 1.05rem;
                line-height: 1;
                padding: .95rem 1.5rem;
                border-radius: 999px;
                cursor: pointer;
                transition: all .25s ease;
                text-transform: capitalize;
                white-space: nowrap;
            }

            .customer-glossy-tab i {
                font-size: 1.25rem;
                line-height: 1;
                display: inline-flex;
                align-items: center;
            }

            .customer-glossy-tab:hover:not(.active) {
                color: #4e46e5;
                background: rgba(108, 99, 255, 0.10);
            }

            .customer-glossy-tab.active {
                color: #fff;
                background: linear-gradient(135deg, #6c63ff, #4e46e5);
                box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45);
                transform: translateY(-1px);
            }

            .customer-glossy-tab .tab-count {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 1.75rem;
                height: 1.75rem;
                padding: 0 .55rem;
                font-size: .82rem;
                font-weight: 800;
                line-height: 1;
                border-radius: 999px;
                color: #fff;
                background: linear-gradient(135deg, #7c73ff, #4e46e5);
                border: 1px solid rgba(255, 255, 255, 0.45);
                box-shadow: 0 4px 10px rgba(78, 70, 229, 0.40), inset 0 1px 1px rgba(255, 255, 255, 0.45);
                transition: all .25s ease;
            }

            .customer-glossy-tab:hover:not(.active) .tab-count {
                transform: scale(1.08);
            }

            .customer-glossy-tab.active .tab-count {
                color: #4e46e5;
                background: linear-gradient(135deg, #ffffff, #eef0ff);
                border-color: rgba(255, 255, 255, 0.9);
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.9);
            }

            @media (max-width: 575.98px) {
                .customer-glossy-tab {
                    flex: 0 0 auto;
                    justify-content: center;
                    padding: .6rem .9rem;
                    font-size: .9rem;
                }
            }
        </style>

        <div class="mt-3 mb-3">
            <div class="customer-glossy-tabs">
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'all') active @endif"
                    wire:click="setTab('all')">
                    <i class="bi bi-list-check"></i>
                    <span>Semua Pesanan</span>
                    <span class="tab-count">{{ $tabCounts['all'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'neworder') active @endif"
                    wire:click="setTab('neworder')">
                    <i class="bi bi-bag-plus"></i>
                    <span>Pesanan Baru</span>
                    <span class="tab-count">{{ $tabCounts['neworder'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'processing') active @endif"
                    wire:click="setTab('processing')">
                    <i class="bi bi-hourglass"></i>
                    <span>Pesanan Diproses</span>
                    <span class="tab-count">{{ $tabCounts['processing'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'completed') active @endif"
                    wire:click="setTab('completed')">
                    <i class="bi bi-bag-check"></i>
                    <span>Pesanan Selesai</span>
                    <span class="tab-count">{{ $tabCounts['completed'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'cancelled') active @endif"
                    wire:click="setTab('cancelled')">
                    <i class="bi bi-x-circle"></i>
                    <span>Pesanan Dibatalkan</span>
                    <span class="tab-count">{{ $tabCounts['cancelled'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'draft') active @endif"
                    wire:click="setTab('draft')">
                    <i class="bi bi-inbox"></i>
                    <span>Draft</span>
                    <span class="tab-count">{{ $tabCounts['draft'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($activeTab === 'habis') active @endif"
                    wire:click="setTab('habis')">
                    <i class="bi bi-hourglass-bottom"></i>
                    <span>Akun Habis</span>
                    <span class="tab-count">{{ $tabCounts['habis'] }}</span>
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    @if ($activeTab === 'habis')
                    {{-- Tab Akun Habis: menampilkan ITEM yang habis, bukan order --}}
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Kode Pesanan</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th class="text-center">Masa Aktif</th>
                                <th class="text-center">Status Langganan</th>
                                <th class="text-center">Pemberitahuan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($habisItems as $item)
                            <tr style="text-align: center;">
                                <td class="fw-bold">{{ $item->order->order_number ?? '-' }}</td>
                                <td>{{ $item->order->customer->nama ?? '-' }}</td>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-center">
                                    @if ($item->end_date)
                                    <span class="d-block">s/d
                                        {{ \Carbon\Carbon::parse($item->end_date)->translatedFormat('d M Y') }}</span>
                                    <small class="fw-semibold {{ $item->isHabis() ? 'text-danger' : 'text-success' }}">
                                        {{ $item->getRemainingLabel() }}
                                    </small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">{!! $item->getSubscriptionStatusBadge() !!}</td>
                                <td class="text-center">
                                    @if ($item->habis_notified_at)
                                    <span class="badge bg-success-subtle text-success border border-success"
                                        title="Diberi tahu {{ $item->habis_notified_at->translatedFormat('d M Y H:i') }}">
                                        <i class="bi bi-check2-circle"></i> Sudah
                                    </span>
                                    @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-exclamation-circle"></i> Belum
                                    </span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    @if ($item->order)
                                    <a wire:navigate href="{{ route('admin.pesanantoko.detail', $item->order) }}"
                                        title="detail pesanan" class="btn btn-sm btn-primary p-2">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-hourglass-bottom"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Akun Habis
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada item pesanan yang masa aktifnya sudah habis.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $habisItems->links('vendor.pagination') }}
                    </div>
                    @else
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Kode Pesanan</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th class="text-center">Status</th>
                                <th>Tanggal Pemesanan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                            <tr style="text-align: center;">
                                <td class="fw-bold">{{ $order->order_number }}</td>
                                <td>{{ $order->customer->nama }}</td>
                                <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @php
                                    $color = '';
                                    if ($order->status == 'draft') {
                                    $color = 'secondary';
                                    }
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
                                <td>{{ $order->created_at->translatedFormat('d F Y, H:i') }}</td>
                                <td class="text-center text-nowrap">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        @if ($order->status === 'draft' && $order->payment_method === 'qris_dinamis')
                                            <a href="{{ route('admin.pesanantoko.qris', $order) }}"
                                                title="lanjutkan pembayaran QRIS"
                                                class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 px-2">
                                                <i class="bi bi-play-fill"></i> <span>Lanjutkan</span>
                                            </a>
                                        @endif
                                        <a wire:navigate href="{{ route('admin.pesanantoko.detail', $order) }}"
                                            title="detail pesanan"
                                            class="btn btn-sm btn-primary d-inline-flex align-items-center justify-content-center p-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-inbox"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Pesanan
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada data pemesanan yang ditemukan saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $orders->links('vendor.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->

    @push('scripts')
        <script>
            // Notifikasi saat pembayaran QRIS baru terdeteksi (dari polling watchNewPayments)
            if (!window.__orderPaidToastBound) {
                window.__orderPaidToastBound = true;
                window.addEventListener('order-paid-toast', function (e) {
                    var d = e.detail || {};
                    if (Array.isArray(d)) d = d[0] || {};
                    var amount = new Intl.NumberFormat('id-ID').format(d.total || 0);
                    if (typeof Swal === 'undefined') return;
                    Swal.fire({
                        title: 'Pembayaran Diterima!',
                        html: 'Pesanan <b>' + (d.orderNumber || '') + '</b><br>' +
                            (d.customerName || '') + ' · <b>Rp ' + amount + '</b>',
                        icon: 'success',
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: 'rgba(16, 185, 129, 0.15)',
                        customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                        buttonsStyling: false,
                        timer: 4500,
                        showConfirmButton: false,
                    });
                });
            }
        </script>
    @endpush
</div>