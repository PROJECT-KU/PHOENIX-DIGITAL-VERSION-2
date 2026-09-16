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
    @include('livewire.pages.admin.partials.dasbor-gaya')

    @php
        // Salam dirakit di server: satu-satunya waktu yang benar bagi toko ini
        // adalah waktu servernya. Versi lama mengandalkan jam KOMPUTER ADMIN
        // lewat JavaScript, jadi laptop yang zonanya meleset menyapa "Selamat
        // Malam" pada pukul sembilan pagi.
        $jam = (int) now()->format('H');
        $salam = match (true) {
            $jam >= 5 && $jam < 11 => 'Selamat pagi',
            $jam >= 11 && $jam < 15 => 'Selamat siang',
            $jam >= 15 && $jam < 18 => 'Selamat sore',
            $jam >= 1 && $jam < 5 => 'Selamat dini hari',
            default => 'Selamat malam',
        };
        $namaDepan = \Illuminate\Support\Str::of(Auth::user()->name)->trim()->explode(' ')->first();

        $fotoAku = Auth::user()->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->profile_photo)
            ? Storage::url(Auth::user()->profile_photo)
            : null;

        // Lencana status pesanan: warnanya sama dengan yang dipakai halaman
        // pesanan, supaya satu status tidak pernah berganti warna antar layar.
        $warnaStatus = [
            'pending' => 'is-kuning',
            'draft' => 'is-abu',
            'paid' => 'is-hijau',
            'processing' => 'is-biru',
            'completed' => 'is-ungu',
            'cancelled' => 'is-merah',
        ];
    @endphp

    <div class="dsb">
        @include('livewire.pages.admin.partials.birthday-card')

        {{-- ================== SAPAAN & AKSI CEPAT ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $salam }}, {{ $namaDepan }} 👋</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Ringkasan toko &amp; keuangan periode {{ $periodeLabel }}</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <div class="dsb-aku">
                    <span class="dsb-aku-foto">
                        {{-- Foto hanya dipasang bila BERKASNYA ada. Memasangnya
                             begitu saja meninggalkan gambar rusak (atau, saat
                             disembunyikan lewat onerror, lubang kosong) di
                             kartu identitas — dan sebagian besar admin memang
                             belum pernah mengunggah foto. --}}
                        @if ($fotoAku)
                            <img src="{{ $fotoAku }}" alt="Foto {{ Auth::user()->name }}">
                        @else
                            <span class="dsb-aku-inisial">{{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                        <span class="dsb-titik {{ Auth::user()->isOnline() ? 'is-daring' : 'is-luring' }}"
                            title="{{ Auth::user()->isOnline() ? 'Online' : 'Offline' }}"></span>
                    </span>
                    <span>
                        <span class="dsb-aku-nama">{{ Auth::user()->name }}</span>
                        <span class="dsb-aku-peran">{{ Auth::user()->role->name ?? 'Pengguna' }}</span>
                    </span>
                </div>

                <a href="{{ route('admin.account.profile') }}" wire:navigate class="dsb-tombol is-utama">
                    <i class="bi bi-person-fill"></i><span>Profil</span>
                </a>

                <button type="button" class="dsb-tombol is-bahaya btn-logout">
                    <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                </button>
            </div>
        </header>

        {{-- Bot Turnitin: kabar bot + kartu yang butuh tangan admin (kuota habis, gagal) --}}
        <livewire:pages.admin.bot-turnitin.panel-bot-turnitin />

        {{-- ================== RINGKASAN KEUANGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kepala" style="--c: #16a34a">
                <span class="dsb-kepala-ikon"><i class="bi bi-graph-up-arrow"></i></span>
                <div class="dsb-kepala-teks">
                    <span class="dsb-kicker">Ringkasan</span>
                    <h2 class="dsb-judul">Uang Masuk &amp; Keluar</h2>
                    <div class="dsb-chip-deret">
                        <span class="dsb-chip"><i class="bi bi-calendar-range"></i>{{ $periodeLabel }}</span>
                        <span class="dsb-chip is-samar">Periode dihitung tanggal 21 sampai 20</span>
                    </div>
                </div>
                <a href="{{ route('admin.cashflow.index') }}" wire:navigate class="dsb-tautan">
                    <span>Buka Cash Flow</span><i class="bi bi-arrow-right"></i>
                </a>
            </div>

            {{-- Dua kartu utama di atas, tiga pendukung di bawah: susunan yang
                 sama dipakai layar Cash Flow, jadi mata admin tidak perlu
                 belajar dua tata letak untuk angka yang sama. --}}
            <div class="dsb-deret is-dua mb-3">
                <article class="dsb-stat is-utama" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-calendar-day-fill"></i></span>
                    <p class="dsb-stat-label">Pendapatan Hari Ini</p>
                    <p class="dsb-stat-nilai is-hijau">Rp {{ $pendapatanHariIni }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-wallet2"></i>
                        <span>Pesanan dibayar {{ now()->translatedFormat('d M Y') }} (paid/proses/selesai)</span>
                    </p>
                    <x-banding-harian :data="$bandingPendapatan" />
                </article>

                <article class="dsb-stat is-utama" style="--c: {{ $saldoIsNegatif ? '#dc2626' : '#7c3aed' }}">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Saldo Bersih</p>
                    <p class="dsb-stat-nilai {{ $saldoIsNegatif ? 'is-merah' : '' }}">Rp {{ $saldoBersih }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Pemasukan − Pengeluaran • {{ $periodeLabel }}</span>
                    </p>
                </article>
            </div>

            <div class="dsb-deret is-tiga">
                <article class="dsb-stat" style="--c: #059669">
                    <span class="dsb-ikon"><i class="bi bi-graph-up-arrow"></i></span>
                    <p class="dsb-stat-label">Total Pemasukan</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalPemasukan }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Cashflow • {{ $periodeLabel }}</span></p>
                </article>

                <article class="dsb-stat" style="--c: #e11d48">
                    <span class="dsb-ikon"><i class="bi bi-graph-down-arrow"></i></span>
                    <p class="dsb-stat-label">Total Pengeluaran</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalPengeluaran }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-wallet2"></i><span>Cashflow • {{ $periodeLabel }}</span></p>
                </article>

                <article class="dsb-stat" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-upc-scan"></i></span>
                    <p class="dsb-stat-label">Total Kode Unik</p>
                    <p class="dsb-stat-nilai">Rp {{ $totalKodeUnik }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-calendar-check"></i><span>Periode {{ $periodeLabel }}</span></p>
                    {{-- Kode unik HARI INI menumpang di kartu ini, bukan jadi kartu
                         keempat: susunan 2 atas + 3 bawah tetap utuh. --}}
                    <span class="dsb-pil"><i class="bi bi-calendar-day"></i>Hari ini: <b>Rp {{ $kodeUnikHariIni }}</b></span>
                    <x-banding-harian :data="$bandingKodeUnik" />
                </article>
            </div>
        </section>

        {{-- ================== GRAFIK KEUANGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kartu">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-bar-chart-line-fill"></i></span>
                        <div>
                            <h3 class="dsb-kartu-judul">Grafik Keuangan</h3>
                            <span class="dsb-kartu-sub">Pemasukan vs pengeluaran sepanjang {{ now()->year }}</span>
                        </div>
                    </div>
                    <span class="dsb-lencana is-hijau">{{ now()->year }}</span>
                </div>
                <div class="dsb-kartu-isi">
                    <div id="finance-chart"></div>
                </div>
            </div>
        </section>

        {{-- ================== AGENDA KEGIATAN SAYA ================== --}}
        @include('livewire.pages.admin.partials.agenda-saya')

        {{-- ================== PESANAN & PELANGGAN TERBARU ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kepala" style="--c: #7c3aed">
                <span class="dsb-kepala-ikon"><i class="bi bi-lightning-charge-fill"></i></span>
                <div class="dsb-kepala-teks">
                    <span class="dsb-kicker">Terbaru</span>
                    <h2 class="dsb-judul">Pesanan &amp; Pelanggan</h2>
                    <div class="dsb-chip-deret">
                        <span class="dsb-chip"><i class="bi bi-bag-check"></i>{{ $recentOrders->count() }} pesanan terakhir</span>
                        <span class="dsb-chip is-samar">Klik barisnya untuk membuka</span>
                    </div>
                </div>
            </div>

            <div class="dsb-deret is-dua is-daftar">
                {{-- Pesanan terbaru --}}
                <div class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-bag-check-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pesanan Terbaru</h3>
                                <span class="dsb-kartu-sub">Toko</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.pesanantoko.index') }}" wire:navigate class="dsb-tautan">
                            <span>Semua</span><i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    {{-- Daftar baris, bukan tabel: di HP, tabel lima kolom selalu
                         berakhir tergeser keluar layar — dan kolom yang tidak
                         terlihat sama saja dengan kolom yang tidak ada. --}}
                    <div class="dsb-daftar">
                        @forelse ($recentOrders as $order)
                            <a href="{{ route('admin.pesanantoko.detail', $order->id) }}" wire:navigate class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #7c3aed">{{ \Illuminate\Support\Str::substr($order->customer->nama ?? 'U', 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $order->order_number }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $order->customer->nama ?? 'Umum' }}</span>
                                        <span class="dsb-pisah">•</span>
                                        {{-- locale('id'): APP_LOCALE=en, tanpa ini tertulis "2 hours ago". --}}
                                        <span>{{ $order->created_at?->locale('id')->diffForHumans() }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-baris-nilai">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                    <span class="dsb-lencana {{ $warnaStatus[$order->status] ?? 'is-abu' }}">{{ strtoupper($order->status) }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-bag"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pesanan</p>
                                <p class="dsb-kosong-ket">Pesanan baru akan muncul di sini begitu masuk.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Pelanggan terbaru --}}
                <div class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-people-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pelanggan Terbaru</h3>
                                <span class="dsb-kartu-sub">Pendaftar terakhir</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.customer.index') }}" wire:navigate class="dsb-tautan">
                            <span>Semua</span><i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($recentCustomers as $customer)
                            <a href="{{ route('admin.customer.edit', $customer->id) }}" wire:navigate class="dsb-baris">
                                <span class="dsb-avatar" style="--c: #16a34a">{{ \Illuminate\Support\Str::substr($customer->nama ?? 'U', 0, 1) }}</span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $customer->nama ?? 'Umum' }}</span>
                                    <span class="dsb-baris-meta">
                                        <span><i class="bi bi-telephone"></i> {{ $customer->no_hp }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ (int) $customer->point }} poin</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-lencana {{ $customer->status_member === 'active' ? 'is-hijau' : 'is-abu' }}">
                                        {{ $customer->status_member === 'active' ? 'MEMBER' : 'NON-MEMBER' }}
                                    </span>
                                    <span class="dsb-baris-meta">{{ $customer->created_at?->locale('id')->diffForHumans() }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-person-plus"></i></span>
                                <p class="dsb-kosong-judul">Belum ada pelanggan</p>
                                <p class="dsb-kosong-ket">Pelanggan baru muncul di sini setelah pesanan pertamanya.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== PENGGUNA DARING & METODE BAYAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kepala" style="--c: #0284c7">
                <span class="dsb-kepala-ikon"><i class="bi bi-activity"></i></span>
                <div class="dsb-kepala-teks">
                    <span class="dsb-kicker">Pantauan</span>
                    <h2 class="dsb-judul">Tim &amp; Cara Bayar</h2>
                    <div class="dsb-chip-deret">
                        <span class="dsb-chip is-samar">Siapa yang sedang daring, dan lewat mana pembeli membayar</span>
                    </div>
                </div>
            </div>

            <div class="dsb-deret is-dua is-daftar">
                <div>@livewire('pages.admin.online-users')</div>

                <div class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-credit-card-2-front-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Metode Pembayaran</h3>
                                <span class="dsb-kartu-sub">Sebaran pesanan per metode</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @if (empty($counts))
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-credit-card"></i></span>
                                <p class="dsb-kosong-judul">Belum ada data</p>
                                <p class="dsb-kosong-ket">Belum ada pesanan dengan metode pembayaran.</p>
                            </div>
                        @else
                            <div id="chart-visitors-profile"></div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!--================== PUSHER REAL TIME ONLINE/OFFLINE ==================-->
@push('scripts')
<script>
    /* Memperbarui SATU baris di daftar "Karyawan Online" saat status berubah,
       tanpa menunggu polling 10 detik berikutnya.

       Penanda yang dicari adalah kelasnya (.dsb-lencana), bukan "span pertama
       di dalam baris" seperti versi lama — baris sekarang punya beberapa span
       (foto, nama, keterangan), dan yang pertama adalah fotonya. */
    Echo.channel('online-users')
        .listen('.UserOnlineStatusChanged', (e) => {
            const pengguna = e.user;
            const baris = document.getElementById(`user-${pengguna.id}`);
            const wadah = document.getElementById('online-users-container');
            if (!wadah) return;

            const lencana = (daring) =>
                `<span class="dsb-lencana ${daring ? 'is-hijau' : 'is-abu'}">` +
                `<span class="dsb-bulat"></span>${daring ? 'ONLINE' : 'OFFLINE'}</span>`;

            const keterangan = (u) => u.online
                ? 'Sedang daring'
                : `Terakhir terlihat ${u.last_seen_at || 'tidak diketahui'}`;

            if (baris) {
                const tanda = baris.querySelector('.dsb-lencana');
                if (tanda) tanda.outerHTML = lencana(pengguna.online);

                const ket = baris.querySelector('.dsb-baris-isi .dsb-baris-meta');
                if (ket) ket.textContent = keterangan(pengguna);
                return;
            }

            const barisBaru = document.createElement('div');
            barisBaru.className = 'dsb-baris';
            barisBaru.id = `user-${pengguna.id}`;
            barisBaru.innerHTML =
                `<span class="dsb-avatar" style="--c: #7c3aed">${(pengguna.name || 'U').charAt(0)}</span>` +
                `<span class="dsb-baris-isi">` +
                `<span class="dsb-baris-judul"></span>` +
                `<span class="dsb-baris-meta"></span>` +
                `</span>` +
                `<span class="dsb-baris-kanan">${lencana(pengguna.online)}</span>`;

            // Nama & keterangan dipasang sebagai TEKS, bukan disisipkan sebagai
            // HTML: nama pengguna diisi orang, dan tidak boleh bisa membawa tag.
            barisBaru.querySelector('.dsb-baris-judul').textContent = pengguna.name || '-';
            barisBaru.querySelector('.dsb-baris-meta').textContent = keterangan(pengguna);

            const kosong = wadah.querySelector('.dsb-kosong');
            if (kosong) kosong.remove();
            wadah.appendChild(barisBaru);
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

{{-- Salam TIDAK lagi dirakit di peramban.

     Skrip lamanya membaca jam dari komputer admin (new Date().getHours()),
     jadi laptop yang zona waktunya meleset menyapa "Selamat Malam" pada pukul
     sembilan pagi. Sekarang dirakit di server, lihat $salam di atas. --}}

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
