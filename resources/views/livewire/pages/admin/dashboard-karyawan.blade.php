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
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        // Salam dari waktu SERVER (lihat catatan yang sama di dasbor pengurus).
        $jam = (int) now()->format('H');
        $salam = match (true) {
            $jam >= 5 && $jam < 11 => 'Selamat pagi',
            $jam >= 11 && $jam < 15 => 'Selamat siang',
            $jam >= 15 && $jam < 18 => 'Selamat sore',
            $jam >= 1 && $jam < 5 => 'Selamat dini hari',
            default => 'Selamat malam',
        };
        $namaDepan = \Illuminate\Support\Str::of($user->name)->trim()->explode(' ')->first();

        // Foto hanya dipakai bila berkasnya memang ada — lihat catatan yang
        // sama di dasbor pengurus.
        $fotoAku = $user->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo)
            ? Storage::url($user->profile_photo)
            : null;

        // Status pinjaman: warna + kata, satu sumber untuk lencana & kartunya.
        $rupaPinjaman = match ($statusPinjaman) {
            'lunas' => ['is-hijau', 'Lunas', '#16a34a', 'bi-patch-check-fill'],
            'berjalan' => ['is-kuning', 'Berjalan', '#d97706', 'bi-hourglass-split'],
            default => ['is-abu', 'Belum Ada', '#64748b', 'bi-dash-circle'],
        };

        // Berapa bagian pinjaman yang sudah dikembalikan — angka ini yang
        // sebenarnya ingin diketahui karyawan, bukan dua nominal terpisah.
        $persenKembali = $totalPinjaman > 0
            ? min(100, (int) round($totalPengembalian / $totalPinjaman * 100))
            : 0;
    @endphp

    <div class="dsb">
        @include('livewire.pages.admin.partials.birthday-card')

        {{-- ================== SAPAAN ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $salam }}, {{ $namaDepan }} 👋</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Ringkasan gaji, pinjaman, dan agenda Anda</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <div class="dsb-aku">
                    <span class="dsb-aku-foto">
                        @if ($fotoAku)
                            <img src="{{ $fotoAku }}" alt="Foto {{ $user->name }}">
                        @else
                            <span class="dsb-aku-inisial">{{ \Illuminate\Support\Str::substr($user->name, 0, 1) }}</span>
                        @endif
                        <span class="dsb-titik {{ $user->isOnline() ? 'is-daring' : 'is-luring' }}"></span>
                    </span>
                    <span>
                        <span class="dsb-aku-nama">{{ $user->name }}</span>
                        <span class="dsb-aku-peran">{{ $detail->jabatan ?? ($user->role->name ?? 'Karyawan') }}</span>
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

        {{-- ================== RINGKASAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kepala" style="--c: #7c3aed">
                <span class="dsb-kepala-ikon"><i class="bi bi-wallet2"></i></span>
                <div class="dsb-kepala-teks">
                    <span class="dsb-kicker">Ringkasan Saya</span>
                    <h2 class="dsb-judul">Gaji &amp; Pinjaman</h2>
                    <div class="dsb-chip-deret">
                        <span class="dsb-chip"><i class="bi bi-shield-lock"></i>Hanya data Anda</span>
                        <span class="dsb-chip is-samar">Tidak ada angka rekan kerja di layar ini</span>
                    </div>
                </div>
            </div>

            <div class="dsb-deret is-dua mb-3">
                <article class="dsb-stat is-utama" style="--c: #7c3aed">
                    <span class="dsb-ikon"><i class="bi bi-cash-coin"></i></span>
                    <p class="dsb-stat-label">Gaji Terakhir</p>
                    <p class="dsb-stat-nilai">{{ $gajiTerakhir ? $gajiTerakhir->total_formatted : 'Rp 0' }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-calendar-check"></i>
                        <span>{{ $gajiTerakhir->periode_label ?? 'Belum ada slip yang tercatat' }}</span>
                    </p>
                </article>

                <article class="dsb-stat is-utama" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-safe2-fill"></i></span>
                    <p class="dsb-stat-label">Total Gaji {{ $tahunIni }}</p>
                    <p class="dsb-stat-nilai">{{ $rp($totalGajiTahunIni) }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-graph-up-arrow"></i><span>Akumulasi sepanjang tahun ini</span></p>
                </article>
            </div>

            <div class="dsb-deret is-dua">
                <article class="dsb-stat" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Sisa Pinjaman</p>
                    <p class="dsb-stat-nilai {{ $sisaPinjaman > 0 ? '' : 'is-hijau' }}">{{ $rp($sisaPinjaman) }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Dikembalikan {{ $rp($totalPengembalian) }} dari {{ $rp($totalPinjaman) }}</span>
                    </p>
                    @if ($totalPinjaman > 0)
                        {{-- Satu garis kemajuan lebih cepat dibaca daripada dua
                             nominal yang harus dibandingkan sendiri di kepala. --}}
                        <span style="display:block; height:6px; border-radius:999px; background:#eef2f7; margin-top:12px; overflow:hidden;">
                            <span style="display:block; height:100%; width:{{ $persenKembali }}%; border-radius:999px; background:#16a34a;"></span>
                        </span>
                        <span class="dsb-stat-ket" style="margin-top:6px;">{{ $persenKembali }}% terbayar</span>
                    @endif
                </article>

                <article class="dsb-stat" style="--c: {{ $rupaPinjaman[2] }}">
                    <span class="dsb-ikon"><i class="bi {{ $rupaPinjaman[3] }}"></i></span>
                    <p class="dsb-stat-label">Status Pinjaman</p>
                    <p class="dsb-stat-nilai" style="font-size:1.35rem;">{{ $rupaPinjaman[1] }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-receipt"></i>
                        <span>{{ $sisaPinjaman > 0 ? 'Masih ada sisa yang berjalan' : 'Tidak ada tanggungan berjalan' }}</span>
                    </p>
                </article>
            </div>
        </section>

        {{-- ================== AGENDA ================== --}}
        @include('livewire.pages.admin.partials.agenda-saya')

        {{-- ================== GRAFIK + RIWAYAT + INFO ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-kepala" style="--c: #16a34a">
                <span class="dsb-kepala-ikon"><i class="bi bi-bar-chart-line-fill"></i></span>
                <div class="dsb-kepala-teks">
                    <span class="dsb-kicker">Rincian</span>
                    <h2 class="dsb-judul">Riwayat &amp; Data Diri</h2>
                    <div class="dsb-chip-deret">
                        <span class="dsb-chip"><i class="bi bi-calendar-range"></i>Tahun {{ $tahunIni }}</span>
                        <span class="dsb-chip is-samar">Perjalanan gaji beserta catatan pinjaman</span>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-7">
                    {{-- Tanpa h-100: grafiknya setinggi 360px, dan kartu yang
                         direntangkan setinggi kolom kanan menyisakan petak putih
                         kosong di bawah grafik. --}}
                    <div class="dsb-kartu">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-graph-up"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Grafik Gaji Saya</h3>
                                    <span class="dsb-kartu-sub">Gaji diterima vs pengembalian pinjaman</span>
                                </div>
                            </div>
                            <span class="dsb-lencana is-hijau">{{ $tahunIni }}</span>
                        </div>
                        <div class="dsb-kartu-isi">
                            <div id="karyawan-gaji-chart"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="dsb-kartu mb-3">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-arrow-left-right"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Riwayat Pinjaman</h3>
                                    <span class="dsb-kartu-sub">Enam catatan terakhir</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-daftar">
                            @forelse ($riwayat as $r)
                                @php $masuk = $r['arah'] === 'masuk'; @endphp
                                <div class="dsb-baris">
                                    <span class="dsb-avatar" style="--c: {{ $masuk ? '#16a34a' : '#e11d48' }}">
                                        <i class="bi {{ $masuk ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                                    </span>
                                    <span class="dsb-baris-isi">
                                        <span class="dsb-baris-judul">{{ $r['jenis'] }}</span>
                                        <span class="dsb-baris-meta">{{ $r['tanggal'] }}</span>
                                    </span>
                                    <span class="dsb-baris-kanan">
                                        <span class="dsb-baris-nilai" style="color: {{ $masuk ? '#15803d' : '#dc2626' }};">
                                            {{ $masuk ? '+' : '−' }}{{ $r['nominal'] }}
                                        </span>
                                    </span>
                                </div>
                            @empty
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-check2-circle"></i></span>
                                    <p class="dsb-kosong-judul">Tidak ada pinjaman aktif</p>
                                    <p class="dsb-kosong-ket">Catatan peminjaman &amp; pengembalian akan tampil di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dsb-kartu">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-vcard-fill"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Info Saya</h3>
                                    <span class="dsb-kartu-sub">Dipakai untuk pembayaran gaji</span>
                                </div>
                            </div>
                        </div>
                        <div class="dsb-kartu-isi">
                            @foreach ([
                                ['bi-envelope', 'Email', $user->email],
                                ['bi-briefcase', 'Jabatan', $detail->jabatan ?? '-'],
                                ['bi-bank', 'Bank', $detail && $detail->nama_bank ? $detail->nama_bank . ' — ' . $detail->nomor_rekening : '-'],
                            ] as [$ikon, $label, $nilai])
                                <div class="d-flex align-items-center justify-content-between gap-3 py-2"
                                    style="border-bottom: 1px solid #f5f7fa;">
                                    <span class="d-inline-flex align-items-center gap-2" style="color:#6b7280; font-size:.82rem;">
                                        <i class="bi {{ $ikon }}"></i>{{ $label }}
                                    </span>
                                    <span style="font-weight:700; color:#1c1f26; font-size:.84rem; text-align:right; word-break:break-word;">{{ $nilai }}</span>
                                </div>
                            @endforeach

                            <a href="{{ route('admin.account.profile') }}" wire:navigate
                                class="dsb-tombol is-lembut w-100 mt-3">
                                <i class="bi bi-gear"></i><span>Pengaturan Profil</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
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
