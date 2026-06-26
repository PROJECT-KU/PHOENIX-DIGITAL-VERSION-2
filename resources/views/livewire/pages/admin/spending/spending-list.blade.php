<div>
    <!--================== GLOSSY TABS STYLE ==================-->
    <style>
        .spending-tabs {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: 6px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.08);
        }

        .spending-tab {
            display: inline-flex;
            flex: 1 1 0;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 13px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b;
            text-decoration: none;
            white-space: nowrap;
            transition: 0.3s;
        }

        .spending-tab i {
            font-size: 1.05rem;
        }

        .spending-tab:hover {
            color: #7c3aed;
            background: rgba(139, 92, 246, 0.08);
            transform: translateY(-1px);
        }

        .spending-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
        }

        @media (max-width: 575px) {
            .spending-tabs {
                width: 100%;
            }

            .spending-tab {
                flex: 1 1 auto;
                justify-content: center;
                padding: 10px 14px;
            }
        }

        /* Presisi ikon di dalam wrapper & tab */
        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i,
        .spending-tab i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before,
        .spending-tab i::before {
            display: block;
            line-height: 1;
        }

        /* Kategori Card */
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

        /* Grand Total */
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
                        <h3 class="gradient-text fw-bold mb-1">Data Pengeluaran</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pengeluaran']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABS ==================-->
        <div class="spending-tabs mb-4">
            <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'lainnya']) }}"
                class="spending-tab {{ $jenisPengeluaran !== 'pembelian_akun' ? 'active' : '' }}">
                <i class="bi bi-receipt"></i>
                <span>Pengeluaran Lainnya</span>
            </a>
            <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'pembelian_akun']) }}"
                class="spending-tab {{ $jenisPengeluaran === 'pembelian_akun' ? 'active' : '' }}">
                <i class="bi bi-bag-check"></i>
                <span>Pengeluaran Pembelian Akun</span>
            </a>
        </div>

        <!--================== TABEL DATA PENGELUARAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                @include('livewire.pages.admin.spending.partials.filter')

                @php
                $isPembelianAkun = $jenisPengeluaran === 'pembelian_akun';
                // Saat mencari, data dua kategori tergabung -> selalu tampilkan kolom PIC Pembeli
                $showPicColumn = $isPembelianAkun || filled($search);
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>ID Transaksi</th>
                                <th>Waktu Transaksi</th>
                                <th>Nominal</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Status</th>
                                <th>Penginput</th>
                                @if ($showPicColumn)
                                <th>PIC Pembeli</th>
                                @endif
                                <th>Waktu Data Dibuat</th>
                                @if (auth()->user()->hasAnyPermission(['edit_spending', 'delete_spending']))
                                <th class="text-center" width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($spendings as $spending)
                            <tr style="text-align: center;">
                                <td class="fw-bold">{{ $spending->id_transaksi }}</td>
                                <td>{{ $spending->tanggal_transaksi_formatted }}</td>
                                <td>{{ $spending->nominal_formatted }}</td>
                                <td class="text-truncate" style="max-width: 200px;">{{ Str::limit($spending->deskripsi, 50) }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-{{ $spending->status === 'completed' ? 'success' : ($spending->status === 'rejected' ? 'danger' : ($spending->status === 'approved' ? 'info' : 'warning')) }}">
                                        {{ ucfirst($spending->status) }}
                                    </span>
                                </td>
                                <td>{{ $spending->namaPenginput }}</td>
                                @if ($showPicColumn)
                                <td>{{ $spending->jenis_pengeluaran === 'pembelian_akun' ? ($spending->namaPicPembeli ?: '-') : '-' }}</td>
                                @endif
                                <td>{{ $spending->created_at_formatted }}</td>
                                @if (auth()->user()->hasAnyPermission(['edit_spending', 'delete_spending']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_spending'))
                                    <a href="{{ route('admin.spending.edit', $spending->id) }}" wire:navigate
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_spending'))
                                    <button type="button" class="btn btn-sm btn-danger p-2 delete-spending-btn"
                                        data-id="{{ $spending->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $showPicColumn ? 9 : 8 }}" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Pengeluaran
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada data pengeluaran yang ditemukan.
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
                    {{ $spendings->links('vendor.pagination') }}
                </div>
            </div>
        </div>

        <!--================== TOTAL PER KATEGORI ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                        <i class="bi bi-pie-chart-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Total Pengeluaran Per Kategori</h5>
                </div>

                @if($totalSpendings->isNotEmpty())
                @php $grandTotal = $totalSpendings->sum('total_pengeluaran'); @endphp

                <div class="row g-3 align-items-stretch">
                    @foreach($totalSpendings as $item)
                    @php
                    $isAkun = $item->jenisPengeluaran === 'pembelian_akun';
                    $label = $isAkun ? 'Pembelian Akun' : ($item->jenisPengeluaran === 'lainnya' ? 'Pengeluaran Lainnya' : 'Tidak Diketahui');
                    $icon = $isAkun ? 'bi-bag-check-fill' : 'bi-receipt';
                    $gradient = $isAkun ? 'bg-gradient-blue' : 'bg-gradient-purple';
                    $persen = $grandTotal > 0 ? round(($item->total_pengeluaran / $grandTotal) * 100) : 0;
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
                                        <h4 class="fw-bold mb-0 text-dark">Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted" style="font-size: 0.78rem;">Kontribusi</span>
                                    <span class="fw-bold" style="font-size: 0.78rem; color: {{ $isAkun ? '#2563eb' : '#7c3aed' }};">{{ $persen }}%</span>
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
                            <i class="bi bi-wallet2"></i>
                        </span>
                        <span class="fw-semibold text-white" style="font-size: 1.05rem;">Total Keseluruhan Pengeluaran</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</h3>
                </div>

                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="empty-state-icon-wrapper mb-3">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Data</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada data pengeluaran.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>
