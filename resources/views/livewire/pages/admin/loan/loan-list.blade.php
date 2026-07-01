<div>
    <!--================== GLOSSY TABS STYLE ==================-->
    <style>
        .loan-tabs {
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

        .loan-tab {
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

        .loan-tab i {
            font-size: 1.05rem;
        }

        .loan-tab:hover {
            color: #7c3aed;
            background: rgba(139, 92, 246, 0.08);
            transform: translateY(-1px);
        }

        .loan-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
        }

        @media (max-width: 575px) {
            .loan-tabs {
                width: 100%;
            }

            .loan-tab {
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
        .loan-tab i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i::before,
        .loan-tab i::before {
            display: block;
            line-height: 1;
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
                        <h3 class="gradient-text fw-bold mb-1">Data Peminjaman</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Peminjaman']
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABS ==================-->
        <div class="loan-tabs mb-4">
            <a href="{{ route('admin.loan.index') }}"
                class="loan-tab {{ request()->routeIs('admin.loan.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i>
                <span>Peminjaman</span>
            </a>
            <a href="{{ route('admin.pengembalian.index') }}"
                class="loan-tab {{ request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-return-left"></i>
                <span>Pengembalian</span>
            </a>
        </div>

        <!--================== TABEL DATA PEMINJAMAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                @include('livewire.pages.admin.loan.partials.filter')

                @include('livewire.pages.admin._shared.pinjaman-table')
            </div>
        </div>

        <!--================== TOTAL PER PEMINJAM ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                        style="width: 42px; height: 42px; font-size: 1.2rem; border-radius: 13px;">
                        <i class="bi bi-people-fill"></i>
                    </span>
                    <h5 class="fw-bold mb-0">Total Peminjaman Per Peminjam</h5>
                </div>

                @if($totalLoans->isNotEmpty())
                @php
                $grandSisa = $totalLoans->sum('sisa_peminjaman');
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th class="text-start">Nama Peminjam</th>
                                <th>Total Pinjaman</th>
                                <th>Total Pengembalian</th>
                                <th>Sisa Peminjaman</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($totalLoans as $item)
                            <tr style="text-align: center;">
                                <td class="text-start fw-semibold">{{ $item->nama_peminjam }}</td>
                                <td>Rp {{ number_format($item->total_pinjaman, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->total_pengembalian, 0, ',', '.') }}</td>
                                <td>
                                    <strong class="{{ $item->sisa_peminjaman <= 0 ? 'text-success' : 'text-danger' }}">
                                        Rp {{ number_format($item->sisa_peminjaman, 0, ',', '.') }}
                                    </strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Grand Total -->
                <div class="grand-total-card mt-3 p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0"
                            style="background: rgba(255,255,255,0.2); box-shadow: none;">
                            <i class="bi bi-wallet2"></i>
                        </span>
                        <span class="fw-semibold text-white" style="font-size: 1.05rem;">Total Sisa Peminjaman Keseluruhan</span>
                    </div>
                    <h3 class="fw-bold mb-0 text-white">Rp {{ number_format($grandSisa, 0, ',', '.') }}</h3>
                </div>

                @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="empty-state-icon-wrapper mb-3">
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Data</h5>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada data peminjaman.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>
