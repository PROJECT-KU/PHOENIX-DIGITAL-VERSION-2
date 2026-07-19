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

    <!--================== HEADER ==================-->
    <div class="container-fluid">
        @include('livewire.pages.admin.partials.birthday-card')
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

    <!--================== MENAMPILKAN DATA KEUANGAN ==================-->
    <div class="container-fluid">

        <div class="row g-4 mb-4 align-items-stretch">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-green flex-shrink-0"><i class="bi bi-arrow-down-circle-fill"></i></div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Total Pemasukan</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ $totalPemasukan }}</h4>
                            <span class="d-block mt-1 text-muted" style="font-size: 0.75rem;"><i class="bi bi-wallet2 me-1"></i>Sinkron Cashflow • {{ now()->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-red flex-shrink-0"><i class="bi bi-arrow-up-circle-fill"></i></div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Total Pengeluaran</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ $totalPengeluaran }}</h4>
                            <span class="d-block mt-1 text-muted" style="font-size: 0.75rem;"><i class="bi bi-wallet2 me-1"></i>Sinkron Cashflow • {{ now()->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper {{ $saldoIsNegatif ? 'bg-gradient-red' : 'bg-gradient-purple' }} flex-shrink-0"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Saldo Bersih</p>
                            <h4 class="fw-bold mb-0 {{ $saldoIsNegatif ? 'text-danger' : 'text-dark' }}">Rp {{ $saldoBersih }}</h4>
                            <span class="d-block mt-1 text-muted" style="font-size: 0.75rem;"><i class="bi bi-graph-up-arrow me-1"></i>Pemasukan − Pengeluaran</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"><i class="bi bi-upc-scan"></i></div>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.85rem;">Total Kode Unik</p>
                            <h4 class="fw-bold mb-0 text-dark">Rp {{ $totalKodeUnik }}</h4>
                            <span class="d-block mt-1 text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar-check me-1"></i>Periode: {{ now()->translatedFormat('F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== END MENAMPILKAN DATA KEUANGAN ==================-->

    <!--================== MENAMPILKAN GRAFIK PEMASUKAN & PENGELUARAN ==================-->
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 stat-card">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Grafik Keuangan</h5>
                            <span class="text-muted" style="font-size: 0.85rem;">Tahun: {{ now()->year }}</span>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="finance-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--================== END MENAMPILKAN GRAFIK PEMASUKAN & PENGELUARAN ==================-->

    <!--================== MENAMPILKAN DATA ORDER & CUSTOMER TERBARU ==================-->
    <div class="row g-4 mb-4 align-items-stretch">

        <!-- GRID KIRI: Orderan Terbaru -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Orderan Terbaru</h5>
                    <a href="{{ route('admin.pesanantoko.index') }}" class="btn btn-sm btn-light-primary fw-semibold" style="border-radius: 8px;">
                        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-primary">
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Kode Psn</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Customer</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Status</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Total</th>
                                    <th class="text-uppercase text-center" style="font-size: 0.70rem;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                <tr style="background: rgba(var(--bs-primary-rgb), 0.02);">
                                    <td class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $order->order_number }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-gradient-purple text-white me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%; font-size: 0.7rem;">
                                                {{ substr($order->customer->nama, 0, 1) }}
                                            </div>
                                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $order->customer->nama ?? 'Umum' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                        $colors = ['pending' => 'bg-light-warning text-warning', 'processing' => 'bg-light-info text-info', 'paid' => 'bg-light-success text-success', 'cancelled' => 'bg-light-danger text-danger', 'completed' => 'bg-light-primary text-primary'];
                                        $badgeClass = $colors[$order->status] ?? 'bg-light-secondary text-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                            {{ strtoupper($order->status) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark" style="font-size: 0.85rem;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.pesanantoko.detail', $order->id) }}" class="btn btn-sm btn-icon btn-light-primary rounded-3">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID KANAN: Customer Terbaru -->
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card">
                <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Customer Terbaru</h5>
                    <a href="{{ route('admin.customer.index') }}" class="btn btn-sm btn-light-primary fw-semibold" style="border-radius: 8px;">
                        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-success">
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Nama</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">No Hp</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Status</th>
                                    <th class="text-uppercase" style="font-size: 0.70rem;">Point</th>
                                    <th class="text-uppercase text-center" style="font-size: 0.70rem;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentCustomers as $customer)
                                <tr style="background: rgba(var(--bs-success-rgb), 0.02);">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-gradient-green text-white me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 50%; font-size: 0.7rem;">
                                                {{ substr($customer->nama, 0, 1) }}
                                            </div>
                                            <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $customer->nama ?? 'Umum' }}</span>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-info" style="font-size: 0.85rem;">{{ $customer->no_hp }}</td>
                                    <td>
                                        <span class="badge {{ $customer->status_member === 'active' ? 'bg-light-success text-success' : 'bg-light-danger text-danger' }} px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                            {{ ucfirst($customer->status_member) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-warning" style="font-size: 0.85rem;">{{ $customer->point }} Pt</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.customer.edit', $customer->id) }}" class="btn btn-sm btn-icon btn-light-success rounded-3">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--================== END MENAMPILKAN DATA ORDER & CUSTOMER TERBARU ==================-->

    <!--================== MENAMPILKAN DATA ONLINE USERS & VISITORS PROFILE ==================-->
    <div class="container-fluid">
        <div class="row">

            <div class="col-12 col-xl-6">
                <div class="mb-4">
                    @livewire('pages.admin.online-users')
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden"
                    style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(15px);">

                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0">Metode Pembayaran</h5>
                        <small class="text-muted">Distribusi order per metode pembayaran</small>
                    </div>

                    <div class="card-body px-4 pb-4">
                        @if(empty($counts))
                        <div class="d-flex flex-column align-items-center justify-content-center py-5">
                            <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-credit-card"></i></div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Data</h6>
                            <p class="text-muted mb-0" style="font-size:.9rem;">Belum ada order dengan metode pembayaran.</p>
                        </div>
                        @else
                        <div id="chart-visitors-profile" class="mt-3"></div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!--================== END MENAMPILKAN DATA ONLINE USERS & VISITORS PROFILE ==================-->

</div>

<!--================== PUSHER REAL TIME ONLINE/OFFLINE ==================-->
@push('scripts')
<script>
    Echo.channel('online-users')
        .listen('.UserOnlineStatusChanged', (e) => {
            console.log("Realtime data:", e);
            const user = e.user;
            const container = document.getElementById(`user-${user.id}`);

            if (container) {
                let span = container.querySelector('span');
                if (span) {
                    span.innerHTML = user.online ?
                        '🟢 Online' :
                        `🔴 Offline (Terakhir online ${user.last_seen_at})`;

                    span.className = user.online ? 'text-success' : 'text-danger';
                }
            } else {
                // Jika user baru, tambahkan ke daftar
                let newUser = document.createElement('div');
                newUser.classList.add('recent-message', 'd-flex', 'px-4', 'py-3');
                newUser.id = `user-${user.id}`;
                newUser.innerHTML = `
                <div class="avatar avatar-lg">
                    <img src="{{ asset('mazer/compiled/jpg/4.jpg') }}" alt="Face">
                </div>
                <div class="name ms-4">
                    <h5 class="mb-1">${user.name}</h5>
                    <span class="${user.online ? 'text-success' : 'text-danger'}">
                        ${user.online ? '🟢 Online' : '🔴 Offline (Terakhir online ' + user.last_seen_at + ')'}
                    </span>
                </div>
            `;
                document.getElementById('online-users-container').appendChild(newUser);
            }
        });
</script>
@endpush
<!--================== END PUSHER REAL TIME ONLINE/OFFLINE ==================-->

<!--================== GRAFIK PEMASUKAN & PENGELUARAN ==================-->
@push('scripts')
<script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
@endpush

<script>
    // 1. Kita bungkus logika grafik ke dalam sebuah fungsi khusus
    function renderFinanceChart() {
        const chartElement = document.querySelector("#finance-chart");

        // Jika elemen grafik tidak ada di halaman ini, hentikan proses
        if (!chartElement) return;

        // AMAN DARI AUTO-FORMATTER — data dari cashflow (income vs expense)
        const dataPemasukan = @json($dataGrafikPemasukan);
        const dataPengeluaran = @json($dataGrafikPengeluaran);

        const chartOptions = {
            series: [{
                    name: 'Pemasukan',
                    data: dataPemasukan
                },
                {
                    name: 'Pengeluaran',
                    data: dataPengeluaran
                }
            ],
            chart: {
                type: 'area',
                height: 380,
                toolbar: {
                    show: false
                },
                fontFamily: 'inherit'
            },
            colors: ['#10b981', '#f43f5e'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                labels: {
                    style: {
                        fontWeight: 600,
                        colors: '#64748b'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b'
                    },
                    formatter: function(value) {
                        if (value === 0) return 0;
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(value) {
                        return "Rp " + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            }
        };

        // Bersihkan sisa grafik sebelumnya agar tidak menumpuk saat kembali ke halaman ini
        chartElement.innerHTML = '';
        const chart = new ApexCharts(chartElement, chartOptions);
        chart.render();
    }

    // 2. PANGGIL FUNGSI BERDASARKAN EVENT

    // Eksekusi saat halaman direfresh normal (F5)
    document.addEventListener('DOMContentLoaded', renderFinanceChart);

    // Eksekusi saat berpindah halaman via SPA Livewire (wire:navigate)
    document.addEventListener('livewire:navigated', renderFinanceChart);
</script>

<script>
    document.addEventListener('DOMContentLoaded', renderFinanceChart);
    document.addEventListener('livewire:navigated', () => {
        setTimeout(renderFinanceChart, 100);
    });

    document.addEventListener('livewire:updated', () => {
        if (document.querySelector("#finance-chart")) {
            renderFinanceChart();
        }
    });
</script>
<!--================== END GRAFIK PEMASUKAN & PENGELUARAN ==================-->

<!--================== UCAPAN SELAMAT ==================-->
<script>
    function getGreeting() {
        const currentTime = new Date();
        const currentHour = currentTime.getHours();
        let greeting;

        if (currentHour >= 5 && currentHour < 11) {
            greeting = "Selamat Pagi ";
        } else if (currentHour >= 11 && currentHour < 15) {
            greeting = "Selamat Siang ";
        } else if (currentHour >= 15 && currentHour < 18) {
            greeting = "Selamat Sore ";
        } else if (currentHour >= 1 && currentHour < 5) {
            greeting = "Selamat Dini Hari ";
        } else {
            greeting = "Selamat Malam ";
        }

        return greeting;
    }


    const greetingElement = document.getElementById("greeting");
    greetingElement.innerText = getGreeting();
</script>
<!--================== END UCAPAN SELAMAT ==================-->

<!--================== SWEET ALERT LOGOUT ==================-->
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

            if (logoutBtn) {
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
                        const livewireComponentId = logoutBtn.closest('[wire\\:id]').getAttribute('wire:id');
                        Livewire.find(livewireComponentId).call('logout');
                    }
                });
            }
        });
    }
</script>
<!--================== END SWEEAT ALERT LOGOUT ==================-->

<!--================== GRAFIK VISITORS PROFILE ==================-->
<script>
    function renderVisitorsChart() {
        const chartElement = document.querySelector("#chart-visitors-profile");

        // Jangan render jika elemen tidak ada
        if (!chartElement) return;

        // Validasi Data: Pastikan data dari PHP sudah terisi
        const visitorCounts = @json($counts ?? []);
        const visitorCountries = @json($countries ?? []);

        if (visitorCounts.length === 0) return;

        const optionsVisitors = {
            series: visitorCounts,
            chart: {
                type: 'donut',
                height: 350,
                animations: {
                    enabled: true
                }
            },
            labels: visitorCountries,
            colors: ['#7c3aed', '#3b82f6', '#10b981', '#f43f5e'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%'
                    }
                }
            }
        };

        chartElement.innerHTML = '';
        const chart = new ApexCharts(chartElement, optionsVisitors);
        chart.render();
    }

    // Gunakan MutationObserver agar lebih akurat
    const observer = new MutationObserver((mutations, obs) => {
        const chartElement = document.querySelector("#chart-visitors-profile");
        if (chartElement) {
            renderVisitorsChart();
            obs.disconnect(); // Hentikan pemantauan setelah grafik tampil
        }
    });

    // Jalankan inisialisasi
    document.addEventListener('livewire:navigated', () => {
        // Beri sedikit waktu untuk rendering Livewire selesai (50ms)
        setTimeout(renderVisitorsChart, 50);

        // Mulai memantau jika grafik belum muncul (fallback)
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });

    // Backup: Tetap jalankan saat DOM content siap
    document.addEventListener('DOMContentLoaded', () => setTimeout(renderVisitorsChart, 50));
</script>
<!--================== END GRAFIK VISITORS PROFILE ==================-->