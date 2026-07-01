
@section('title')
Data Gaji Karyawan || PT. Asthana Cipta Mandiri
@stop
<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before {
            display: block;
            line-height: 1;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.9) !important;
        }

        .category-progress {
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: rgba(139, 92, 246, 0.1);
            overflow: hidden;
        }

        .category-progress-bar {
            height: 100%;
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .grand-total-card {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            border-radius: 20px;
            box-shadow: 0 12px 28px rgba(124, 58, 237, 0.28);
        }

        .grand-total-card .stat-icon-wrapper {
            width: 46px;
            height: 46px;
            font-size: 1.3rem;
            border-radius: 14px;
            color: #fff;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Gaji Karyawan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Gaji Karyawan']
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABEL DATA GAJI ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                @include('livewire.pages.admin.gaji-karyawans.partials.filter')

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>ID Transaksi</th>
                                <th>Nama Karyawan</th>
                                <th>Periode</th>
                                <th>Tanggal Bayar</th>
                                <th>Gaji Pokok</th>
                                <th>Total Gaji</th>
                                <th class="text-center">Status</th>
                                <th>Waktu Data Dibuat</th>
                                <th class="text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($gajikaryawan as $item)
                            <tr style="text-align: center;">
                                <td class="fw-bold">{{ $item->id_transaksi }}</td>
                                <td>{{ $item->karyawan->name ?? '-' }}</td>
                                <td>{{ $item->periode_label }}</td>
                                <td>{{ $item->tanggal_transaksi_formatted }}</td>
                                <td>{{ $item->gaji_pokok_formatted }}</td>
                                <td class="fw-semibold">{{ $item->total_formatted }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>{{ $item->created_at_formatted }}</td>
                                <td class="text-center text-nowrap">
                                    <button type="button" wire:click="downloadSlip('{{ $item->id }}')"
                                        wire:loading.attr="disabled" wire:target="downloadSlip"
                                        class="btn btn-sm btn-success text-white p-2" title="Cetak Slip Gaji">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                    @if (auth()->user()->hasPermission('edit_gajikaryawan'))
                                    <a href="{{ route('admin.gajikaryawan.edit', $item->id) }}" wire:navigate
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_gajikaryawan'))
                                    <button type="button" class="btn btn-sm btn-danger p-2 delete-gajikaryawan-btn"
                                        data-id="{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Gaji
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada data gaji karyawan yang ditemukan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $gajikaryawan->links('vendor.pagination') }}
                </div>
            </div>
        </div>

        <!--================== TOTAL GAJI PER STATUS ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Total Gaji Per Status</h5>
                </div>

                @if($totalGaji->isNotEmpty())
                @php $grandTotal = $totalGaji->sum('total_gaji'); @endphp

                <div class="row g-3 align-items-stretch">
                    @foreach($totalGaji as $item)
                    @php
                    $isCompleted = $item->status === 'completed';
                    $label = $isCompleted ? 'Sudah Dibayar (Completed)' : 'Menunggu (Pending)';
                    $icon = $isCompleted ? 'bi-check-circle-fill' : 'bi-hourglass-split';
                    $gradient = $isCompleted ? 'bg-gradient-green' : 'bg-gradient-purple';
                    $warna = $isCompleted ? '#059669' : '#7c3aed';
                    $persen = $grandTotal > 0 ? round(($item->total_gaji / $grandTotal) * 100) : 0;
                    @endphp
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100 stat-card overflow-hidden category-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="stat-icon-wrapper {{ $gradient }} flex-shrink-0">
                                        <i class="bi {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">{{ $label }}</p>
                                        <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($item->total_gaji, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted" style="font-size: 0.78rem;">Kontribusi</span>
                                    <span class="fw-bold" style="font-size: 0.78rem; color: {{ $warna }};">{{ $persen }}%</span>
                                </div>
                                <div class="category-progress">
                                    <div class="category-progress-bar {{ $gradient }}" style="width: {{ $persen }}%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Grand Total -->
                <div class="grand-total-card mt-3 p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0"
                            style="background: rgba(255,255,255,0.2); box-shadow: none;">
                            <i class="bi bi-cash-stack"></i>
                        </span>
                        <span class="fw-semibold text-white" style="font-size: 1.05rem;">Total Keseluruhan Gaji</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                </div>

                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="empty-state-icon-wrapper mb-3">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Data</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada data gaji karyawan.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>
