<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/');
    }
}; ?>

@section('title')
    Dashboard || Phoenix Digital
@stop

<div>
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-9">

                <!--================== MENAMPILKAN DATA KEUANGAN ==================-->
                <div class="row">
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon purple mb-2">
                                            <i class="iconly-boldWallet"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Pemasukan Umum</h6>
                                        <h6 class="font-extrabold mb-0">Rp. 112.000</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldWork"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Pemasukan RSc</h6>
                                        <h6 class="font-extrabold mb-0">Rp. 183.000</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldBuy"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Pengeluaran Keseluruhan</h6>
                                        <h6 class="font-extrabold mb-0">Rp. 80.000</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldUser"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Total Pengeluaran Gaji</h6>
                                        <h6 class="font-extrabold mb-0">Rp. 112</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== MENAMPILKAN GRAFIK ==================-->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Grafik Pemasukan & Pengeluaran</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-profile-visit"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--================== END ==================-->

                <div class="row">
                    <div class="col-12 col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Profile Visit</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-7">
                                        <div class="d-flex align-items-center">
                                            <svg class="bi text-primary" width="32" height="32" fill="blue"
                                                style="width:10px">
                                                <use
                                                    xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                            </svg>
                                            <h5 class="mb-0 ms-3">Europe</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-0 text-end">862</h5>
                                    </div>
                                    <div class="col-12">
                                        <div id="chart-europe"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-7">
                                        <div class="d-flex align-items-center">
                                            <svg class="bi text-success" width="32" height="32" fill="blue"
                                                style="width:10px">
                                                <use
                                                    xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                            </svg>
                                            <h5 class="mb-0 ms-3">America</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-0 text-end">375</h5>
                                    </div>
                                    <div class="col-12">
                                        <div id="chart-america"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-7">
                                        <div class="d-flex align-items-center">
                                            <svg class="bi text-success" width="32" height="32" fill="blue"
                                                style="width:10px">
                                                <use
                                                    xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                            </svg>
                                            <h5 class="mb-0 ms-3">India</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-0 text-end">625</h5>
                                    </div>
                                    <div class="col-12">
                                        <div id="chart-india"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-7">
                                        <div class="d-flex align-items-center">
                                            <svg class="bi text-danger" width="32" height="32" fill="blue"
                                                style="width:10px">
                                                <use
                                                    xlink:href="assets/static/images/bootstrap-icons.svg#circle-fill" />
                                            </svg>
                                            <h5 class="mb-0 ms-3">Indonesia</h5>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <h5 class="mb-0 text-end">1025</h5>
                                    </div>
                                    <div class="col-12">
                                        <div id="chart-indonesia"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--================== MENAMPILAN DATA PEMBELI TERBARU ==================-->
                    <div class="col-12 col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Pembeli Terbaru</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-lg">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="col-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md">
                                                            <img src="{{ asset('mazer/compiled/jpg/5.jpg') }}">
                                                        </div>
                                                        <p class="font-bold ms-3 mb-0">Si Cantik</p>
                                                    </div>
                                                </td>
                                                <td class="col-auto">
                                                    <p class=" mb-0">Congratulations on your graduation!</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="col-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md">
                                                            <img src="{{ asset('mazer/compiled/jpg/2.jpg') }}">
                                                        </div>
                                                        <p class="font-bold ms-3 mb-0">Si Ganteng</p>
                                                    </div>
                                                </td>
                                                <td class="col-auto">
                                                    <p class=" mb-0">Wow amazing design! Can you make another
                                                        tutorial for
                                                        this design?</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--================== END ==================-->

                </div>
            </div>

            <div class="col-12 col-lg-3">
                <!--================== MENAMPILKAN DATA AUTH ==================-->
                <div class="card">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl">
                                <img src="{{ asset('mazer/compiled/jpg/1.jpg') }}" alt="Face 1">
                            </div>

                            <div class="ms-3 name">
                                <span id="greeting" class="text-dark font-weight-bold d-block"
                                    style="font-size:13px;"></span>
                                <h5 class="font-bold">{{ Auth::user()->name }}</h5>
                                <h6 class="text-muted mb-0">
                                    @if (Auth::user()->isOnline())
                                        <span class="text-success">🟢 Online</span>
                                    @else
                                        <span class="text-danger">🔴 Offline</span>
                                    @endif
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex p-0 border-0" style="border-top:1px;">
                        <button
                            class="btn btn-outline-primary font-bold w-50 d-flex justify-content-center align-items-center me-1 mb-2 ms-2">
                            <i class="iconly-boldUser"></i>
                            <span>Detail Profile</span>
                        </button>

                        <button wire:click="logout"
                            class="btn btn-outline-danger font-bold w-50 d-flex justify-content-center align-items-center ms-1 mb-2 me-2">
                            <i class="iconly-boldLogout"></i>
                            <span>Logout</span>
                        </button>

                    </div>
                </div>
                <!--================== END ==================-->

                <!--================== MENAMPILKAN DATA KARYAWAN ONLINE ==================-->

                @livewire('pages.admin.online-users')
                <!--================== END ==================-->


                <div class="card">
                    <div class="card-header">
                        <h4>Visitors Profile</h4>
                    </div>
                    <div class="card-body">
                        <div id="chart-visitors-profile"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>



@push('scripts')
    <!--================== PUSHER REAL TIME ONLINE/OFFLINE ==================-->
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
    <!--================== END ==================-->


    <!-- Need: Apexcharts -->
    <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('mazer/static/js/pages/dashboard.js') }}"></script>

    @push('scripts')
        <!-- Need: Apexcharts -->
        <script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('mazer/static/js/pages/dashboard.js') }}"></script>
    @endpush


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
    <!--================== END ==================-->
@endpush

const greetingElement = document.getElementById("greeting");
greetingElement.innerText = getGreeting();
</script>
<!--================== END ==================-->
