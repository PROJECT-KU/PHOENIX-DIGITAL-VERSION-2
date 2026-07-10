<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'));
    }
}; ?>

@section('title')
Dashboard || lemon
@stop

<div>

    <style>
        /* Pusatkan ikon Bootstrap (bi) di dalam stat-icon-wrapper yang
           aslinya didesain untuk font iconly. */
        .stat-icon-wrapper {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

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
    </style>

    @php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $statusBadge = [
    'lunas' => ['#dcfce7', '#15803d', 'Lunas'],
    'berjalan' => ['#fef9c3', '#a16207', 'Berjalan'],
    'pending' => ['#f1f5f9', '#475569', 'Belum Ada'],
    ];
    $sb = $statusBadge[$statusPinjaman] ?? $statusBadge['pending'];

    $slipStatusBadge = function ($s) {
    return match ($s) {
    'completed' => ['#dcfce7', '#15803d', 'Dibayar'],
    'pending' => ['#fef9c3', '#a16207', 'Pending'],
    default => ['#f1f5f9', '#475569', ucfirst($s ?? '-')],
    };
    };
    @endphp

    <div class="container-fluid">
        @include('livewire.pages.admin.partials.birthday-card')
        <!--================== HEADER ==================-->
        <div class="container-fluid">
            <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
                <div class="card-body p-4 d-flex align-items-center">

                    <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between w-100 gap-4">

                        <div class="title-wrapper text-center text-lg-start">
                            <h3 class="gradient-text fw-bold mb-1">Dashboard</h3>
                            <p class="text-muted mb-0 small">Selamat datang kembali di sistem panel</p>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">

                            <div class="d-flex align-items-center bg-white px-3 py-2 shadow-sm" style="border-radius: 50px; border: 1px solid #f1f5f9;">
                                <div class="position-relative">
                                    <img src="{{ Auth::user()->profile_photo ? Storage::url(Auth::user()->profile_photo) : asset('mazer/compiled/jpg/1.jpg') }}" alt="User Avatar" class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                                    @if (Auth::user()->isOnline())
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 14px; height: 14px; transform: translate(-2px, -2px);" title="Online"></span>
                                    @else
                                    <span class="position-absolute bottom-0 end-0 bg-danger border border-2 border-white rounded-circle" style="width: 14px; height: 14px; transform: translate(-2px, -2px);" title="Offline"></span>
                                    @endif
                                </div>

                                <div class="ms-3 text-start pe-3">
                                    <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">{{ Auth::user()->name }}</h6>
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-circle-fill {{ Auth::user()->isOnline() ? 'text-success' : 'text-danger' }} me-1" style="font-size: 0.4rem; vertical-align: middle;"></i>
                                        {{ Auth::user()->isOnline() ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.account.profile') }}" wire:navigate class="btn btn-primary d-flex align-items-center justify-content-center shadow-sm text-decoration-none" style="border-radius: 12px; padding: 10px 18px;">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>

                                <button type="button" class="btn btn-danger btn-logout d-flex align-items-center justify-content-center shadow-sm" style="border-radius: 12px; padding: 10px 18px;">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--================== END HEADER ==================-->

        <!--================== KARTU RINGKAS ==================-->
        <div class="container-fluid">

            <div class="row g-4 mb-4 align-items-stretch">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"><i class="bi bi-cash-coin"></i></div>
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Gaji Terakhir</p>
                                <h4 class="fw-bold mb-0 text-dark">{{ $gajiTerakhir ? $gajiTerakhir->total_formatted : 'Rp 0' }}</h4>
                                <small class="text-muted">{{ $gajiTerakhir->periode_label ?? 'Belum ada data' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"><i class="bi bi-wallet2"></i></div>
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Total Gaji {{ $tahunIni }}</p>
                                <h4 class="fw-bold mb-0 text-dark">{{ $rp($totalGajiTahunIni) }}</h4>
                                <small class="text-muted">Akumulasi tahun ini</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper bg-gradient-green flex-shrink-0"><i class="bi bi-cash-stack"></i></div>
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Sisa Pinjaman</p>
                                <h4 class="fw-bold mb-0 text-dark">{{ $rp($sisaPinjaman) }}</h4>
                                <small class="text-muted">dari {{ $rp($totalPinjaman) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                        <div class="card-body p-4 d-flex align-items-center gap-3">
                            <div class="stat-icon-wrapper bg-gradient-red flex-shrink-0"><i class="bi bi-patch-check-fill"></i></div>
                            <div>
                                <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Status Pinjaman</p>
                                <span class="badge rounded-pill mt-1" style="background: {{ $sb[0] }}; color: {{ $sb[1] }}; font-size: 0.9rem;">{{ $sb[2] }}</span>
                                <div><small class="text-muted">Dikembalikan {{ $rp($totalPengembalian) }}</small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!--================== GRAFIK GAJI & PENGEMBALIAN ==================-->
            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#059669,#10b981); color:#fff;">
                                <i class="bi bi-graph-up"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Grafik Gaji Saya</h5>
                                <span class="text-muted" style="font-size: 0.85rem;">Tahun: {{ $tahunIni }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="karyawan-gaji-chart"></div>
                    </div>
                </div>
            </div>

            <!--================== RIWAYAT PINJAMAN + PROFIL ==================-->
            <div class="col-12 col-xl-5">
                <!-- Riwayat -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                                <i class="bi bi-arrow-left-right"></i>
                            </span>
                            <h6 class="fw-bold mb-0 text-dark">Riwayat Pinjaman</h6>
                        </div>

                        @forelse ($riwayat as $r)
                        <div class="ks-list-row d-flex align-items-center gap-3 p-3 mb-2">
                            <span class="stat-icon-wrapper flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.9rem; border-radius: 10px; background: {{ $r['arah'] === 'masuk' ? '#ecfdf5' : '#fef2f2' }}; color: {{ $r['arah'] === 'masuk' ? '#059669' : '#e11d48' }};">
                                <i class="bi {{ $r['arah'] === 'masuk' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $r['jenis'] }}</div>
                                <small class="text-muted">{{ $r['tanggal'] }}</small>
                            </div>
                            <div class="fw-bold text-end" style="font-size: 0.85rem; color: {{ $r['arah'] === 'masuk' ? '#059669' : '#e11d48' }};">
                                {{ $r['arah'] === 'masuk' ? '+' : '-' }}{{ $r['nominal'] }}
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                            <p class="mb-0" style="font-size: 0.85rem;">Tidak ada pinjaman aktif.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Profil singkat -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="stat-icon-wrapper" style="width: 38px; height: 38px; font-size: 1rem; border-radius: 11px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;">
                                <i class="bi bi-person-vcard-fill"></i>
                            </span>
                            <h6 class="fw-bold mb-0 text-dark">Info Saya</h6>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted" style="font-size: 0.85rem;">Email</span>
                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $user->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted" style="font-size: 0.85rem;">Jabatan</span>
                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $detail->jabatan ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted" style="font-size: 0.85rem;">Bank</span>
                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                {{ $detail && $detail->nama_bank ? $detail->nama_bank . ' - ' . $detail->nomor_rekening : '-' }}
                            </span>
                        </div>
                        <a href="{{ route('admin.account.profile') }}" wire:navigate
                            class="btn btn-light rounded-pill w-100 mt-3 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-gear"></i><span>Pengaturan Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--================== GRAFIK GAJI KARYAWAN ==================-->
@push('scripts')
<script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
<script>
    function renderKaryawanGajiChart() {
        const el = document.querySelector("#karyawan-gaji-chart");
        if (!el) return;

        const dataGaji = @json($dataGrafikGaji);
        const dataPengembalian = @json($dataGrafikPengembalian);

        const options = {
            series: [
                { name: 'Gaji Diterima', data: dataGaji },
                { name: 'Pengembalian Pinjaman', data: dataPengembalian }
            ],
            chart: {
                type: 'area',
                height: 360,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#10b981', '#f43f5e'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            legend: { position: 'top', horizontalAlign: 'right' },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                labels: { style: { fontWeight: 600, colors: '#64748b' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b' },
                    formatter: function(value) {
                        if (value === 0) return 0;
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(value) {
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        };

        el.innerHTML = '';
        new ApexCharts(el, options).render();
    }

    document.addEventListener('DOMContentLoaded', renderKaryawanGajiChart);
    document.addEventListener('livewire:navigated', () => setTimeout(renderKaryawanGajiChart, 100));
    document.addEventListener('livewire:updated', () => {
        if (document.querySelector("#karyawan-gaji-chart")) renderKaryawanGajiChart();
    });
</script>
@endpush
<!--================== END GRAFIK GAJI KARYAWAN ==================-->

<!--================== SWEET ALERT LOGOUT ==================-->
@push('scripts')
<script>
    if (!window.logoutListenerAdded) {
        window.logoutListenerAdded = true;

        const glossyConfig = {
            background: 'rgba(255, 255, 255, 0.8)',
            backdrop: 'rgba(139, 92, 246, 0.15)',
            customClass: {
                popup: 'swal-glossy-popup',
                confirmButton: 'btn-glossy-confirm',
                cancelButton: 'btn-glossy-cancel',
                title: 'swal-glossy-title'
            },
            buttonsStyling: false
        };

        document.addEventListener('click', function(event) {
            const logoutBtn = event.target.closest('.btn-logout');
            if (!logoutBtn) return;
            event.preventDefault();

            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: "Anda harus login kembali untuk masuk ke sistem.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal',
                ...glossyConfig
            }).then((result) => {
                if (result.isConfirmed) {
                    const comp = logoutBtn.closest('[wire\\:id]');
                    if (comp) Livewire.find(comp.getAttribute('wire:id')).call('logout');
                }
            });
        });
    }
</script>
@endpush
<!--================== END SWEET ALERT LOGOUT ==================-->