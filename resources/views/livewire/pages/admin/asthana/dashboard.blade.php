@section('title')
Dashboard Asthana || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* yang sama dengan dasbor lemon & dasbor Orcha. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')

    @php
        // Semua perbandingan dirakit di sini: direktif blok yang memuat ">"
        // membuat penanda morph Livewire timpang (PenandaMorphAdminTest).
        $kartuPerlu = collect([
            ['pelamar_baru', 'Pelamar baru', 'bi-person-badge', 'admin.pelamar.index', '#2455e6', 'lamaran belum ditinjau', $boleh['pelamar']],
            ['pesan_belum_dibaca', 'Pesan belum dibaca', 'bi-envelope', 'admin.message.index', '#d97706', 'dari formulir kontak situs', $boleh['pesan']],
            ['lowongan_lewat_batas', 'Lowongan lewat batas', 'bi-hourglass-bottom', 'admin.lowongan.index', '#b91c1c', 'masih aktif, sudah tidak tampil', $boleh['lowongan']],
        ])->filter(fn ($k) => $k[6])->values();
        $jumlahPerlu = $kartuPerlu->sum(fn ($k) => (int) ($perlu[$k[0]] ?? 0));
        $lebarPerlu = 'k-'.max(4, intdiv(12, max(1, $kartuPerlu->count())));
        $adaPerlu = $kartuPerlu->isNotEmpty();
        $tanpaPerlu = $jumlahPerlu === 0;

        $kartuAngka = collect([
            ['lowongan_terbuka', 'Lowongan terbuka', 'bi-briefcase', '#0ea5c6', 'tampil di situs'],
            ['pelamar_bulan_ini', 'Pelamar bulan ini', 'bi-people', '#2455e6', now()->locale('id')->translatedFormat('F Y')],
            ['karyawan', 'Karyawan', 'bi-person-vcard', '#17a974', 'akun lemon'],
            ['isi_situs', 'Isi situs terbit', 'bi-globe2', '#7c3aed', 'portofolio + berita'],
        ])->filter(fn ($k) => $angka[$k[0]] !== null)->values();
        $adaAngka = $kartuAngka->isNotEmpty();

        $warnaTahap = ['baru' => '#64748b', 'diseleksi' => '#2455e6', 'wawancara' => '#0ea5c6', 'diterima' => '#17a974', 'ditolak' => '#b91c1c'];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-buildings"></i> Mode Asthana</span>
                <h1 class="dsb-judul">Dashboard Asthana</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Situs perusahaan, karyawan, lowongan kerja, pelamar, dan pesan masuk PT. Asthana Cipta Mandiri.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                @if (config('asthana.situs_url'))
                    <a href="{{ config('asthana.situs_url') }}" target="_blank" rel="noopener" class="dsb-tombol is-lembut" style="--ikon: #2455e6">
                        <i class="bi bi-box-arrow-up-right"></i><span>Buka situs</span>
                    </a>
                @endif
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #d97706">
                    <i class="bi bi-box-arrow-left"></i><span>Kembali ke lemon</span>
                </a>
            </div>
        </header>

        {{-- ================== PERLU DITINDAK ================== --}}
        @if ($adaPerlu)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #d97706">
                        <span class="dsb-kepala-ikon"><i class="bi bi-exclamation-diamond-fill"></i></span>
                        <div class="dsb-kepala-teks is-hitung-atas">
                            <span class="dsb-kicker">Butuh Perhatian</span>
                            <h2 class="dsb-judul">Yang Menunggu Dikerjakan</h2>
                            <div class="dsb-chip-deret">
                                <span class="dsb-chip dsb-chip-hitung"><i class="bi bi-list-check"></i>{{ $jumlahPerlu }} hal</span>
                                @if ($tanpaPerlu)
                                    <span class="dsb-chip is-samar">Tidak ada yang menunggu ditindak saat ini</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @foreach ($kartuPerlu as [$kunci, $label, $ikon, $rute, $warna, $ket])
                        <article class="dsb-tugas {{ $lebarPerlu }}" style="--c: {{ $warna }}">
                            <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                            <span class="dsb-tugas-isi">
                                <span class="dsb-stat-label">{{ $label }}</span>
                                <span class="dsb-tugas-angka">{{ (int) ($perlu[$kunci] ?? 0) }}</span>
                                <span class="dsb-stat-ket"><i class="bi {{ $ikon }}"></i><span>{{ $ket }}</span></span>
                            </span>
                            <a class="dsb-tutup-kartu" href="{{ route($rute) }}" wire:navigate>
                                <span class="visually-hidden">Buka {{ $label }}</span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================== RINGKASAN ================== --}}
        @if ($adaAngka)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kepala" style="--c: #2455e6">
                        <span class="dsb-kepala-ikon"><i class="bi bi-bar-chart-fill"></i></span>
                        <div class="dsb-kepala-teks">
                            <span class="dsb-kicker">Ringkasan</span>
                            <h2 class="dsb-judul">Asthana Hari Ini</h2>
                        </div>
                    </div>
                    @foreach ($kartuAngka as [$kunci, $label, $ikon, $warna, $ket])
                        <article class="dsb-stat k-3" style="--c: {{ $warna }}">
                            <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                            <span class="dsb-stat-label">{{ $label }}</span>
                            <span class="dsb-stat-nilai">{{ number_format((int) $angka[$kunci], 0, ',', '.') }}</span>
                            <span class="dsb-stat-ket"><i class="bi {{ $ikon }}"></i><span>{{ $ket }}</span></span>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================== TERBARU ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                @if ($boleh['pelamar'])
                    <div class="dsb-kartu k-6">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #2455e6"><i class="bi bi-person-badge"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Lamaran terbaru</h3>
                                    <span class="dsb-kartu-sub">Lima yang paling akhir masuk</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.pelamar.index') }}" wire:navigate class="dsb-tautan">Lihat semua</a>
                        </div>
                        <div class="dsb-daftar">
                            @forelse ($pelamarTerbaru as $lamaran)
                                <a class="dsb-baris text-decoration-none" href="{{ route('admin.pelamar.detail', $lamaran->id) }}" wire:navigate>
                                    <span class="dsb-avatar" style="--c: #2455e6">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lamaran->name, 0, 1)) }}</span>
                                    <span class="dsb-baris-isi">
                                        <span class="dsb-baris-judul">{{ $lamaran->name }}</span>
                                        <span class="dsb-baris-meta">
                                            <span>{{ $lamaran->posisi_label }}</span>
                                            <span class="dsb-pisah">•</span>
                                            <span>{{ $lamaran->created_at->locale('id')->diffForHumans() }}</span>
                                        </span>
                                    </span>
                                    <span class="dsb-baris-kanan">
                                        <span class="dsb-lencana" style="--c: {{ $warnaTahap[$lamaran->status] ?? '#64748b' }}">{{ $lamaran->status_label }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-person-badge"></i></span>
                                    <p class="dsb-kosong-judul">Belum ada lamaran</p>
                                    <p class="dsb-kosong-ket">Lamaran dari halaman Karir situs akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if ($boleh['pesan'])
                    <div class="dsb-kartu k-6">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-envelope"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Pesan terbaru</h3>
                                    <span class="dsb-kartu-sub">Dari formulir kontak situs</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.message.index') }}" wire:navigate class="dsb-tautan">Lihat semua</a>
                        </div>
                        <div class="dsb-daftar">
                            @forelse ($pesanTerbaru as $pesan)
                                @php $belumDibaca = $pesan->read_at === null; @endphp
                                <a class="dsb-baris text-decoration-none" href="{{ route('admin.message.detail', $pesan) }}" wire:navigate>
                                    <span class="dsb-avatar" style="--c: #d97706">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pesan->name, 0, 1)) }}</span>
                                    <span class="dsb-baris-isi">
                                        <span class="dsb-baris-judul">{{ $pesan->name }}</span>
                                        <span class="dsb-baris-meta">
                                            <span>{{ \Illuminate\Support\Str::limit($pesan->message, 60) }}</span>
                                        </span>
                                    </span>
                                    <span class="dsb-baris-kanan">
                                        @if ($belumDibaca)
                                            <span class="dsb-lencana" style="--c: #d97706">Baru</span>
                                        @else
                                            <span class="dsb-baris-meta">{{ $pesan->created_at->locale('id')->diffForHumans() }}</span>
                                        @endif
                                    </span>
                                </a>
                            @empty
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-envelope"></i></span>
                                    <p class="dsb-kosong-judul">Belum ada pesan</p>
                                    <p class="dsb-kosong-ket">Pesan dari halaman Kontak situs akan muncul di sini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
