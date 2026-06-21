<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Laporan Cashflow</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Cashflow']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== FILTER PERIODE ================== --}}
        <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                            style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <span>Filter Periode</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="bulan" class="form-select rounded-3" style="min-width: 160px;">
                            <option value="">Semua Bulan</option>
                            @foreach($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="tahun" class="form-select rounded-3" style="min-width: 130px;">
                            <option value="">Semua Tahun</option>
                            @foreach($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>

                        @if($bulan || $tahun)
                        <button wire:click="resetFilter" type="button" class="btn btn-light-danger rounded-3" title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif

                        <button wire:click="downloadReport" wire:loading.attr="disabled" type="button"
                            class="btn rounded-pill text-white fw-semibold d-inline-flex align-items-center justify-content-center gap-2 px-3 shadow-sm"
                            style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                            <span wire:loading.remove wire:target="downloadReport" class="d-inline-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-arrow-down fs-6"></i><span>Unduh Laporan PDF</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== SUMMARY CARDS ================== --}}
        <div class="row g-4 mb-4 align-items-stretch">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-green flex-shrink-0">
                            <i class="bi bi-arrow-down-circle"></i>
                        </div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Pemasukan</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($summary['income']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-red flex-shrink-0">
                            <i class="bi bi-arrow-up-circle"></i>
                        </div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Pengeluaran</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($summary['expense']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper {{ $summary['net'] < 0 ? 'bg-gradient-red' : 'bg-gradient-purple' }} flex-shrink-0">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Net Cashflow</p>
                            <h4 class="fw-bold mb-0 {{ $summary['net'] < 0 ? 'text-danger' : 'text-dark' }}">
                                Rp {{ number_format($summary['net']) }}
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-blue flex-shrink-0">
                            <i class="bi bi-upc-scan"></i>
                        </div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Total Kode Unik</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalKodeUnik) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== OMSET BERSIH PENJUALAN ================== --}}
        <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden mb-4">
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    {{-- Omset bersih utama --}}
                    <div class="col-12 col-lg-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper {{ $omset['bersih'] < 0 ? 'bg-gradient-red' : 'bg-gradient-green' }} flex-shrink-0">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">
                                    Omset Bersih Penjualan
                                    <i class="bi bi-info-circle ms-1" title="Total penjualan dikurangi modal (harga awal produk)"></i>
                                </p>
                                <h3 class="fw-bold mb-1 {{ $omset['bersih'] < 0 ? 'text-danger' : 'text-dark' }}">
                                    Rp {{ number_format($omset['bersih'], 0, ',', '.') }}
                                </h3>
                                <span class="badge bg-light-{{ $omset['bersih'] < 0 ? 'danger' : 'success' }} text-{{ $omset['bersih'] < 0 ? 'danger' : 'success' }} fw-semibold">
                                    <i class="bi bi-percent me-1"></i>Margin {{ $omset['margin'] }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Rincian penjualan & modal --}}
                    <div class="col-12 col-lg-7">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 rounded-4 h-100" style="background: rgba(16, 185, 129, 0.08);">
                                    <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">
                                        <i class="bi bi-cart-check text-success me-1"></i>Total Penjualan
                                    </p>
                                    <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($omset['penjualan'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-4 h-100" style="background: rgba(244, 63, 94, 0.08);">
                                    <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">
                                        <i class="bi bi-box-seam text-danger me-1"></i>Total Modal (Harga Awal)
                                    </p>
                                    <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($omset['modal'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">
                            <i class="bi bi-calculator me-1"></i>
                            Omset bersih = Total Penjualan &minus; Total Modal, dari pesanan yang sudah dibayar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== TABEL TRANSAKSI ================== --}}
        <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden">
            <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Riwayat Transaksi</h5>
                    <small class="text-muted">Daftar pemasukan &amp; pengeluaran</small>
                </div>
                <span class="badge bg-light-primary text-primary fw-semibold">
                    {{ $reports->total() }} Transaksi
                </span>
            </div>

            <div class="card-body px-4 pb-2">
                <div class="table-responsive">
                    <table class="table align-middle cashflow-table mb-0">
                        <thead>
                            <tr class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <th class="border-0">Tanggal</th>
                                <th class="border-0">Kategori</th>
                                <th class="border-0">Deskripsi</th>
                                <th class="border-0">Sumber</th>
                                <th class="border-0 text-end">Nominal</th>
                                <th class="border-0 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $item)
                            <tr>
                                <td class="text-nowrap">
                                    <span class="fw-semibold text-dark">{{ $item->transaction_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light-{{ $item->type == 'income' ? 'success' : 'danger' }} text-{{ $item->type == 'income' ? 'success' : 'danger' }}">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem; vertical-align: middle;"></i>
                                        {{ ucfirst($item->category) }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $item->description }}</td>
                                <td class="text-muted">
                                    @if(!$item->sourceable)
                                    <span class="text-secondary">-</span>
                                    @elseif($item->sourceable_type === 'App\Models\Order')
                                    <a wire:navigate href="{{ route('admin.pesanantoko.detail', $item->sourceable) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pesanan">
                                        Order #{{ $item->sourceable->order_number ?? '-' }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\Loan')
                                    <a wire:navigate href="{{ route('admin.loan.edit', $item->sourceable->id) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pinjaman">
                                        Pinjaman {{ $item->sourceable->nama_peminjam ?? 'peminjam' }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\Pengembalian')
                                    <a wire:navigate href="{{ route('admin.pengembalian.edit', $item->sourceable->id) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pengembalian">
                                        Pengembalian {{ $item->sourceable->nama_pengembalian }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\GajiKaryawans')
                                    <a wire:navigate href="{{ route('admin.gajikaryawan.edit', $item->sourceable) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat gaji karyawan">
                                        Gaji {{ $item->sourceable->karyawan->name ?? 'User' }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\Spending')
                                    <a wire:navigate href="{{ route('admin.spending.edit', $item->sourceable->id) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pengeluaran">
                                        Pengeluaran {{ $item->sourceable->jenis_pengeluaran }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\PemesananRsc')
                                    <a wire:navigate href="{{ route('admin.pesananrsc.detail', ['nama_camp' => $item->sourceable->nama_camp, 'batch_camp' => $item->sourceable->batch_camp]) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pesanan Rumah Scopus">
                                        Pesanan Rumah Scopus
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @else
                                    <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $item->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $item->type == 'income' ? '+' : '-' }}
                                    Rp {{ number_format($item->amount) }}
                                </td>
                                <td class="text-center">
                                    <button wire:click="$dispatch('openDetail', { id: '{{ $item->id }}' })"
                                        class="btn btn-sm btn-light-primary rounded-pill px-3" title="Lihat detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-cash"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Cash Flow
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Data Cash Flow belum tersedia untuk ditampilkan saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent border-0 px-4 pb-4 pt-2">
                {{ $reports->links('vendor.pagination') }}
            </div>

            <livewire:pages.admin.cashflow.cashflow-detail />
        </div>
    </div>

    @push('styles')
    <style>
        /* Pastikan ikon presisi di tengah wrapper */
        .stat-icon-wrapper {
            line-height: 1;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .stat-icon-wrapper i::before {
            display: block;
            line-height: 1;
        }

        .cashflow-table thead th {
            font-weight: 600;
        }

        .cashflow-table tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .cashflow-table tbody tr:last-child {
            border-bottom: none;
        }

        .cashflow-table tbody tr:hover {
            background-color: rgba(124, 58, 237, 0.04);
        }

        .cashflow-table td,
        .cashflow-table th {
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            vertical-align: middle;
        }
    </style>
    @endpush
</div>