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

        {{-- ================== AKSI CEPAT ==================
             Sama dengan dasbor pengurus: yang paling sering dikerjakan tidak
             boleh perlu dicari di menu samping lebih dulu. --}}
        @php
            $aksiKaryawan = [];

            if (\Illuminate\Support\Facades\Route::has('admin.presensi.index') && auth()->user()->hasPermission('view_presensi')) {
                $aksiKaryawan[] = ['#16a34a', 'bi-fingerprint', 'Presensi',
                    $presensiHariIni?->waktu_pulang ? 'Presensi hari ini sudah lengkap'
                        : ($presensiHariIni ? 'Belum absen pulang' : 'Belum absen hari ini'),
                    route('admin.presensi.index')];
            }

            if (\Illuminate\Support\Facades\Route::has('admin.task-saya.index')) {
                $aksiKaryawan[] = ['#7c3aed', 'bi-clipboard-check-fill', 'Task Saya',
                    $taskRingkas['belum'] + $taskRingkas['dikerjakan'] > 0
                        ? ($taskRingkas['belum'] + $taskRingkas['dikerjakan']).' task belum selesai'
                        : 'Semua task sudah selesai',
                    route('admin.task-saya.index')];
            }

            if (\Illuminate\Support\Facades\Route::has('admin.kegiatan.index') && auth()->user()->hasPermission('view_kegiatan')) {
                $aksiKaryawan[] = ['#0284c7', 'bi-calendar-event-fill', 'Kalender Kegiatan', 'Agenda dan jadwal bersama', route('admin.kegiatan.index')];
            }
        @endphp

        @if (! empty($aksiKaryawan))
            <section class="dsb-bagian">
                <div class="dsb-kartu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #f26522"><i class="bi bi-lightning-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Aksi Cepat</h3>
                                <span class="dsb-kartu-sub">Yang paling sering dibuka tiap hari</span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        <div class="dsb-aksi">
                            @foreach ($aksiKaryawan as [$warna, $ikon, $nama, $ket, $tautan])
                                <a class="dsb-aksi-item" href="{{ $tautan }}" wire:navigate style="--c: {{ $warna }}">
                                    <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                                    <span class="dsb-aksi-teks">
                                        <span class="dsb-aksi-nama">{{ $nama }}</span>
                                        <span class="dsb-aksi-ket">{{ $ket }}</span>
                                    </span>
                                    <i class="bi bi-arrow-right dsb-aksi-panah"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== PEKERJAAN HARI INI ==================
             Dasbor ini sebelumnya hanya memuat gaji, pinjaman, dan data diri —
             semuanya hal yang dibuka sebulan sekali. Dua hal yang dibuka TIAP
             HARI, presensi dan task, justru tidak ada sama sekali dan harus
             dicari lewat menu samping.

             Sengaja diletakkan di ATAS ringkasan gaji: gaji bulan ini bisa
             dibaca kapan saja, sedangkan tenggat hari ini tidak. --}}
        @php
            $belumSelesai = $taskRingkas['belum'] + $taskRingkas['dikerjakan'];

            // Presensi dibaca sebagai TIGA keadaan, bukan ada/tidak ada:
            // belum absen, sedang bekerja, dan sudah pulang. Menggabungkan dua
            // yang terakhir membuat "sudah absen" terbaca selesai padahal
            // absen pulangnya belum ditekan.
            if (! $presensiHariIni) {
                $rupaPresensi = ['#e11d48', 'bi-fingerprint', 'Belum absen', 'Presensi hari ini belum tercatat'];
            } elseif (! $presensiHariIni->waktu_pulang) {
                $rupaPresensi = ['#d97706', 'bi-clock-fill', 'Sedang bekerja',
                    'Masuk '.\Illuminate\Support\Carbon::parse($presensiHariIni->waktu_masuk)->format('H:i').' • belum absen pulang'];
            } else {
                $rupaPresensi = ['#16a34a', 'bi-check-circle-fill', 'Sudah pulang',
                    \Illuminate\Support\Carbon::parse($presensiHariIni->waktu_masuk)->format('H:i').' – '
                    .\Illuminate\Support\Carbon::parse($presensiHariIni->waktu_pulang)->format('H:i')
                    .' • '.$presensiHariIni->durasi_label];
            }
        @endphp

        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #16a34a">
                    <span class="dsb-kepala-ikon"><i class="bi bi-clipboard-check-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Hari Ini</span>
                        <h2 class="dsb-judul">Presensi &amp; Task Saya</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar3"></i>{{ now()->locale('id')->translatedFormat('l, d M Y') }}</span>
                            <span class="dsb-chip is-samar">Hanya pekerjaan yang ditugaskan kepada Anda</span>
                        </div>
                    </div>
                </div>

                <article class="dsb-stat k-4" style="--c: {{ $rupaPresensi[0] }}">
                    <span class="dsb-ikon"><i class="bi {{ $rupaPresensi[1] }}"></i></span>
                    <p class="dsb-stat-label">Presensi Hari Ini</p>
                    <p class="dsb-stat-nilai" style="font-size: 1.35rem;">{{ $rupaPresensi[2] }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-info-circle"></i><span>{{ $rupaPresensi[3] }}</span></p>
                </article>

                <article class="dsb-stat k-4" style="--c: #7c3aed">
                    <span class="dsb-ikon"><i class="bi bi-list-check"></i></span>
                    <p class="dsb-stat-label">Task Belum Selesai</p>
                    <p class="dsb-stat-nilai">{{ $belumSelesai }}<span class="dsb-stat-satuan">task</span></p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-hourglass-split"></i>
                        <span>{{ $taskRingkas['dikerjakan'] }} dikerjakan • {{ $taskRingkas['belum'] }} belum dimulai</span>
                    </p>
                </article>

                <article class="dsb-stat k-4" style="--c: {{ $taskRingkas['telat'] > 0 ? '#e11d48' : '#16a34a' }}">
                    <span class="dsb-ikon"><i class="bi {{ $taskRingkas['telat'] > 0 ? 'bi-clipboard-x-fill' : 'bi-patch-check-fill' }}"></i></span>
                    <p class="dsb-stat-label">Lewat Tenggat</p>
                    <p class="dsb-stat-nilai">{{ $taskRingkas['telat'] }}<span class="dsb-stat-satuan">task</span></p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-calendar-x"></i>
                        <span>{{ $taskRingkas['telat'] > 0 ? 'Kerjakan yang ini lebih dulu' : 'Tidak ada yang lewat tenggat' }}</span>
                    </p>
                </article>

                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-card-checklist"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Yang Perlu Dikerjakan</h3>
                                <span class="dsb-kartu-sub">Yang lewat tenggat lebih dulu, lalu yang paling dekat</span>
                            </div>
                        </div>
                        @if (\Illuminate\Support\Facades\Route::has('admin.task-saya.index'))
                            <a href="{{ route('admin.task-saya.index') }}" wire:navigate class="dsb-tautan">
                                <span>Semua Task</span><i class="bi bi-arrow-right"></i>
                            </a>
                        @endif
                    </div>

                    <div class="dsb-daftar">
                        @forelse ($taskSaya as $task)
                            @php
                                $telat = $task->deadline_selesai && $task->deadline_selesai->isBefore(today());
                                $tenggatHariIni = $task->deadline_selesai && $task->deadline_selesai->isSameDay(today());
                                // Task::hariTerlambat() hanya berlaku untuk task yang SUDAH
                                // selesai (ia membandingkan completed_at). Yang ada di daftar
                                // ini justru yang belum selesai, jadi selisihnya dihitung
                                // langsung terhadap hari ini.
                                $hariTelat = $telat ? (int) $task->deadline_selesai->startOfDay()->diffInDays(today()) : 0;
                            @endphp
                            <div class="dsb-baris">
                                <span class="dsb-avatar" style="--c: {{ $telat ? '#e11d48' : '#7c3aed' }}">
                                    <i class="bi {{ $telat ? 'bi-exclamation-lg' : 'bi-check2' }}"></i>
                                </span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $task->nama }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $task->category->nama ?? 'Tanpa kategori' }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>Tenggat {{ $task->deadline_selesai?->locale('id')->translatedFormat('d M Y') ?? 'tidak diatur' }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-lencana {{ $telat ? 'is-luring' : ($tenggatHariIni ? 'is-kuning' : 'is-abu') }}">
                                        {{ $telat ? 'TELAT '.$hariTelat.' HARI' : ($tenggatHariIni ? 'HARI INI' : strtoupper($task->progress)) }}
                                    </span>
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-emoji-smile"></i></span>
                                <p class="dsb-kosong-judul">Tidak ada task yang menunggu</p>
                                <p class="dsb-kosong-ket">Semua task yang ditugaskan kepada Anda sudah selesai.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== RINGKASAN SAYA ==================
             Kepala bagian dan keempat kartunya duduk pada rak 12 kolom yang
             sama, jadi tepinya segaris — lihat catatan .dsb-rak. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
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

                <article class="dsb-stat is-utama k-6" style="--c: #7c3aed">
                    <span class="dsb-ikon"><i class="bi bi-cash-coin"></i></span>
                    <p class="dsb-stat-label">Gaji Terakhir</p>
                    <p class="dsb-stat-nilai">{{ $gajiTerakhir ? $gajiTerakhir->total_formatted : 'Rp 0' }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-calendar-check"></i>
                        <span>{{ $gajiTerakhir->periode_label ?? 'Belum ada slip yang tercatat' }}</span>
                    </p>
                </article>

                <article class="dsb-stat is-utama k-6" style="--c: #0284c7">
                    <span class="dsb-ikon"><i class="bi bi-safe2-fill"></i></span>
                    <p class="dsb-stat-label">Total Gaji {{ $tahunIni }}</p>
                    <p class="dsb-stat-nilai">{{ $rp($totalGajiTahunIni) }}</p>
                    <p class="dsb-stat-ket"><i class="bi bi-graph-up-arrow"></i><span>Akumulasi sepanjang tahun ini</span></p>
                </article>

                <article class="dsb-stat k-6" style="--c: #16a34a">
                    <span class="dsb-ikon"><i class="bi bi-cash-stack"></i></span>
                    <p class="dsb-stat-label">Sisa Pinjaman</p>
                    <p class="dsb-stat-nilai {{ $sisaPinjaman > 0 ? '' : 'is-hijau' }}">{{ $rp($sisaPinjaman) }}</p>
                    @if ($totalPinjaman > 0)
                        {{-- Satu garis kemajuan lebih cepat dibaca daripada dua
                             nominal yang harus dibandingkan sendiri di kepala. --}}
                        <span class="dsb-kemajuan" title="{{ $persenKembali }}% terbayar">
                            <span style="width: {{ $persenKembali }}%"></span>
                        </span>
                    @endif
                    <p class="dsb-stat-ket">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>{{ $persenKembali }}% terbayar — {{ $rp($totalPengembalian) }} dari {{ $rp($totalPinjaman) }}</span>
                    </p>
                </article>

                <article class="dsb-stat k-6" style="--c: {{ $rupaPinjaman[2] }}">
                    <span class="dsb-ikon"><i class="bi {{ $rupaPinjaman[3] }}"></i></span>
                    <p class="dsb-stat-label">Status Pinjaman</p>
                    <p class="dsb-stat-nilai" style="font-size: 1.35rem;">{{ $rupaPinjaman[1] }}</p>
                    <p class="dsb-stat-ket">
                        <i class="bi bi-receipt"></i>
                        <span>{{ $sisaPinjaman > 0 ? 'Masih ada sisa yang berjalan' : 'Tidak ada tanggungan berjalan' }}</span>
                    </p>
                </article>
            </div>
        </section>

        {{-- ================== AGENDA & DATA DIRI ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #d97706">
                    <span class="dsb-kepala-ikon"><i class="bi bi-calendar3"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Harian</span>
                        <h2 class="dsb-judul">Agenda &amp; Data Diri</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip is-samar">Kegiatan yang menunggu Anda, dan data yang dipakai membayar gaji</span>
                        </div>
                    </div>
                </div>

                <div class="k-7">@include('livewire.pages.admin.partials.agenda-saya')</div>

                <div class="dsb-kartu k-5">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-person-vcard-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Info Saya</h3>
                                <span class="dsb-kartu-sub">Dipakai untuk pembayaran gaji</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi is-tegak">
                        <div class="dsb-data">
                            @foreach ([
                                ['bi-envelope', 'Email', $user->email],
                                ['bi-briefcase', 'Jabatan', $detail->jabatan ?? '-'],
                                ['bi-bank', 'Bank', $detail && $detail->nama_bank ? $detail->nama_bank . ' — ' . $detail->nomor_rekening : '-'],
                            ] as [$ikon, $label, $nilai])
                                <div class="dsb-data-baris">
                                    <span class="dsb-data-label"><i class="bi {{ $ikon }}"></i>{{ $label }}</span>
                                    <span class="dsb-data-nilai">{{ $nilai }}</span>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('admin.account.profile') }}" wire:navigate class="dsb-tombol is-lembut w-100 mt-3" style="--ikon: #64748b">
                            <i class="bi bi-gear"></i><span>Pengaturan Profil</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== RIWAYAT ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #16a34a">
                    <span class="dsb-kepala-ikon"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Rincian</span>
                        <h2 class="dsb-judul">Riwayat Gaji &amp; Pinjaman</h2>
                        <div class="dsb-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-calendar-range"></i>Tahun {{ $tahunIni }}</span>
                            <span class="dsb-chip is-samar">Perjalanan gaji beserta catatan pinjaman</span>
                        </div>
                    </div>
                </div>

                <div class="dsb-kartu k-8">
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
                        <div class="dsb-grafik"><div id="karyawan-gaji-chart"></div></div>
                    </div>
                </div>

                <div class="dsb-kartu k-4">
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
