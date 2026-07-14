
@section('title')
Data Pengembalian || lemon
@stop
<div>
    <!--================== GLOSSY TABS STYLE ==================-->
    <style>
        .loan-tabs {
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

        .loan-tab {
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
            text-decoration: none;
            white-space: nowrap;
        }

        .loan-tab i {
            font-size: 1.25rem;
            line-height: 1;
            display: inline-flex;
            align-items: center;
        }

        .loan-tab:hover:not(.active) {
            color: #4e46e5;
            background: rgba(108, 99, 255, 0.10);
        }

        .loan-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45);
            transform: translateY(-1px);
        }

        @media (max-width: 575.98px) {
            .loan-tabs {
                gap: .35rem;
                padding: .35rem;
            }
            .loan-tab {
                padding: .6rem .35rem;
                font-size: .82rem;
                gap: .35rem;
            }
            .loan-tab i {
                font-size: 1rem;
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

        .grand-total-card .grand-total-label { font-size: 1.05rem; line-height: 1.3; }
        .grand-total-card .grand-total-amount { letter-spacing: -.01em; }
        .grand-total-card .gt-divider { display: none; width: 64px; height: 2px; background: rgba(255, 255, 255, .35); border-radius: 2px; margin: .2rem auto .15rem; }

        @media (max-width: 575.98px) {
            .grand-total-card { border-radius: 22px; padding: 1.5rem 1.15rem !important; }
            .grand-total-card .stat-icon-wrapper { width: 58px; height: 58px; font-size: 1.7rem; border-radius: 18px; }
            .grand-total-card .grand-total-label { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; opacity: .92; }
            .grand-total-card .grand-total-amount { font-size: 1.95rem; margin-top: .05rem; }
            .grand-total-card .gt-divider { display: block; }
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Pengembalian</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Pengembalian']
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

        <!--================== TABEL DATA PENGEMBALIAN ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                @include('livewire.pages.admin.pengembalian.partials.filter')

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
                <div class="grand-total-card mt-3 p-4 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 text-center text-sm-start">
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3">
                        <span class="stat-icon-wrapper flex-shrink-0"
                            style="background: rgba(255,255,255,0.2); box-shadow: none;">
                            <i class="bi bi-wallet2"></i>
                        </span>
                        <span class="fw-semibold text-white grand-total-label">Total Sisa Peminjaman Keseluruhan</span>
                    </div>
                    <span class="gt-divider"></span>
                    <h3 class="fw-bold mb-0 text-white grand-total-amount">Rp {{ number_format($grandSisa, 0, ',', '.') }}</h3>
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
