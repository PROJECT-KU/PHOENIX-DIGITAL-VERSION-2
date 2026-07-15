@section('title')
Data Cash Flow || lemon
@stop
<div>
    <style>
        .siklus-chip {
            padding: 6px 14px 6px 6px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(124, 58, 237, .10), rgba(37, 99, 235, .08));
            border: 1px solid rgba(124, 58, 237, .2);
        }

        .siklus-chip-ico {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
            color: #fff;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .siklus-chip-ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            line-height: 1;
        }

        .siklus-chip-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #7c3aed;
        }

        .siklus-chip-date {
            font-size: .88rem;
            font-weight: 700;
            color: #1e293b;
        }

        .siklus-chip-arrow {
            color: #94a3b8;
            font-size: .8rem;
        }
    </style>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start">
                        <h3 class="gradient-text fw-bold mb-1">Laporan Cashflow</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Cashflow']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <button wire:click="downloadReport" wire:loading.attr="disabled" wire:target="downloadReport"
                        type="button" class="btn btn-danger rounded-pill d-flex align-items-center justify-content-center gap-2 px-3 flex-shrink-0">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ================== FILTER PERIODE ================== --}}
        <div class="card border-0 shadow-sm rounded-4 stat-card mb-4">
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
                        <select wire:model.live="modePeriode" class="form-select rounded-3 fw-semibold" style="min-width: 175px;"
                            title="Cara menghitung periode">
                            <option value="kalender">📅 Kalender (1–akhir bln)</option>
                            <option value="siklus20">🔄 Siklus Gaji ({{ \App\Support\PeriodeGaji::cutoffDay() + 1 }}–{{ \App\Support\PeriodeGaji::cutoffDay() }})</option>
                        </select>

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

                        @if($bulan || $tahun || $modePeriode !== 'kalender')
                        <button wire:click="resetFilter" type="button"
                            class="btn btn-light-danger rounded-3 d-inline-flex align-items-center justify-content-center"
                            title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>

                @if($modePeriode === 'siklus20')
                <div class="mt-3 pt-3 border-top">
                    @if($siklusMulai && $siklusAkhir)
                    <div class="siklus-chip d-inline-flex align-items-center gap-2">
                        <span class="siklus-chip-ico d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-calendar-range"></i>
                        </span>
                        <span class="siklus-chip-label">Periode</span>
                        <span class="siklus-chip-date">{{ $siklusMulai->translatedFormat('d M Y') }}</span>
                        <i class="bi bi-arrow-right siklus-chip-arrow"></i>
                        <span class="siklus-chip-date">{{ $siklusAkhir->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="text-muted mt-2" style="font-size:.78rem;">
                        <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Sama dengan periode di fitur <b>Gaji</b> — gajian tanggal {{ \App\Support\PeriodeGaji::cutoffDay() }}.
                    </div>
                    @else
                    <span class="text-muted" style="font-size:.85rem;">
                        <i class="bi bi-info-circle me-1" style="vertical-align:-0.125em;"></i>Pilih <b>bulan</b> dulu. Siklus mengikuti tanggal gajian (tgl {{ \App\Support\PeriodeGaji::cutoffDay() }}) — contoh: pilih Juli → {{ \App\Support\PeriodeGaji::label(7, now()->year) }}.
                    </span>
                    @endif
                </div>
                @endif
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
                                    <i class="bi bi-info-circle ms-1" title="Total penjualan dikurangi modal (pembelian akun)"></i>
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
                                        <i class="bi bi-box-seam text-danger me-1"></i>Total Modal (Pembelian Akun)
                                    </p>
                                    <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($omset['modal'], 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mb-0 mt-2" style="font-size: 0.75rem;">
                            <i class="bi bi-calculator me-1"></i>
                            Omset bersih = Total Penjualan &minus; Total Modal (Pembelian Akun) <b>pada periode terpilih</b> (per bulan).
                        </p>
                    </div>
                </div>

                {{-- ===== Rincian omset bersih per produk ===== --}}
                @if ($produkTotal > 0)
                <div class="mt-4 pt-3 border-top">
                    <p class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size: .9rem;">
                        <i class="bi bi-list-columns-reverse text-primary"></i>
                        <span>Rincian Omset Bersih per Produk <span class="text-muted fw-normal">(periode terpilih)</span></span>
                    </p>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr style="text-align:center; font-size:.8rem;" class="text-muted">
                                    <th style="width:48px;">No</th>
                                    <th>Produk</th>
                                    <th title="Total penjualan (tanpa kode unik)">Penjualan</th>
                                    <th>Modal (Beli Akun)</th>
                                    <th>Omset Bersih</th>
                                    <th>Status Modal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produkItems as $i => $row)
                                <tr style="text-align:center;">
                                    <td class="text-muted">{{ ($produkPage - 1) * $produkPerPage + $i + 1 }}</td>
                                    <td class="fw-semibold">{{ $row['nama'] }}</td>
                                    <td class="text-success fw-semibold">Rp {{ number_format($row['penjualan'], 0, ',', '.') }}</td>
                                    <td class="text-danger">Rp {{ number_format($row['modal'], 0, ',', '.') }}</td>
                                    <td class="fw-bold {{ $row['bersih'] < 0 ? 'text-danger' : 'text-dark' }}">
                                        Rp {{ number_format($row['bersih'], 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if ($row['modal'] <= 0)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary">—</span>
                                            @elseif ($row['tertutup'])
                                            <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle me-1"></i>Tertutup</span>
                                            @else
                                            <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-hourglass-split me-1"></i>Belum tertutup</span>
                                            @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="text-align:center; background: rgba(108,99,255,.06);">
                                    <td colspan="2" class="fw-bold text-end">Total Periode</td>
                                    <td class="fw-bold text-success">Rp {{ number_format($omset['penjualan'], 0, ',', '.') }}</td>
                                    <td class="fw-bold text-danger">Rp {{ number_format($omset['modal'], 0, ',', '.') }}</td>
                                    <td class="fw-bold {{ $omset['bersih'] < 0 ? 'text-danger' : 'text-primary' }}">Rp {{ number_format($omset['bersih'], 0, ',', '.') }}</td>
                                    <td>—</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($produkTotalPages > 1)
                    @php
                    $pFirst = ($produkPage - 1) * $produkPerPage + 1;
                    $pLast = $pFirst + count($produkItems) - 1;
                    $pStart = max($produkPage - 2, 1);
                    $pEnd = min($produkPage + 2, $produkTotalPages);
                    @endphp
                    <nav class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-center gap-2 mt-3">
                        <div class="small text-muted text-center text-sm-start order-2 order-sm-1">
                            Menampilkan
                            <span class="fw-semibold">{{ $pFirst }}</span> sampai
                            <span class="fw-semibold">{{ $pLast }}</span> dari
                            <span class="fw-semibold">{{ $produkTotal }}</span> data
                        </div>
                        <ul class="pagination mb-0 flex-wrap justify-content-center order-1 order-sm-2">
                            @if ($produkPage <= 1)
                                <li class="page-item disabled" aria-disabled="true"><span class="page-link">@lang('pagination.previous')</span></li>
                                @else
                                <li class="page-item">
                                    <button type="button" class="page-link" wire:click="produkPrev" wire:loading.attr="disabled" rel="prev">@lang('pagination.previous')</button>
                                </li>
                                @endif

                                @if ($pStart > 1)
                                <li class="page-item"><button type="button" class="page-link" wire:click="produkGoto(1)">1</button></li>
                                @if ($pStart > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                @endif

                                @for ($i = $pStart; $i <= $pEnd; $i++)
                                    @if ($i==$produkPage)
                                    <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                    @else
                                    <li class="page-item"><button type="button" class="page-link" wire:click="produkGoto({{ $i }})">{{ $i }}</button></li>
                                    @endif
                                    @endfor

                                    @if ($pEnd < $produkTotalPages)
                                        @if ($pEnd < $produkTotalPages - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                        <li class="page-item"><button type="button" class="page-link" wire:click="produkGoto({{ $produkTotalPages }})">{{ $produkTotalPages }}</button></li>
                                        @endif

                                        @if ($produkPage < $produkTotalPages)
                                            <li class="page-item">
                                            <button type="button" class="page-link" wire:click="produkNext" wire:loading.attr="disabled" rel="next">@lang('pagination.next')</button>
                                            </li>
                                            @else
                                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">@lang('pagination.next')</span></li>
                                            @endif
                        </ul>
                    </nav>
                    @endif
                </div>
                @endif
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
                            <tr class="text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px; text-align: center;">
                                <th class="border-0">Tanggal</th>
                                <th class="border-0">Kategori</th>
                                <th class="border-0">Deskripsi</th>
                                <th class="border-0">Sumber</th>
                                <th class="border-0">Nominal</th>
                                <th class="border-0">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $item)
                            <tr style="text-align: center;">
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
                                        Pengeluaran {{ $item->sourceable->jenis_pengeluaran === 'pembelian_akun' ? 'Pembelian Akun' : 'Lainnya' }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\Modal')
                                    <a wire:navigate href="{{ route('admin.modal.index') }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat modal">
                                        Modal Operasional
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\Pemasukan')
                                    <a wire:navigate href="{{ route('admin.pemasukan.index') }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pemasukan">
                                        {{ $item->sourceable->kategori ? 'Pemasukan: ' . $item->sourceable->kategori : 'Pemasukan Lainnya' }}
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\PemesananRsc')
                                    <a wire:navigate href="{{ route('admin.pesananrsc.detail', ['nama_camp' => $item->sourceable->nama_camp, 'batch_camp' => $item->sourceable->batch_camp]) }}"
                                        class="text-decoration-none fw-semibold text-primary" title="Lihat pesanan Rumah Scopus">
                                        Pesanan Rumah Scopus
                                        <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.7rem;"></i>
                                    </a>
                                    @elseif($item->sourceable_type === 'App\Models\OrderItem')
                                    <span class="fw-semibold text-warning">
                                        <i class="bi bi-box-seam me-1"></i>Modal Akun: {{ $item->sourceable->product_name ?? '-' }}
                                    </span>
                                    @else
                                    <span class="text-secondary">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $item->type == 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $item->type == 'income' ? '+' : '-' }}
                                    Rp {{ number_format($item->amount) }}
                                </td>
                                <td>
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