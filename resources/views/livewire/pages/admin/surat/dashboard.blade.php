@section('title')
Dashboard Arsip Surat || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* yang sama dengan dasbor lemon, Orcha, dan Asthana. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.surat.partials.surat-gaya')

    @php
        /*
         | Semua perbandingan dirakit DI SINI, bukan di kondisi direktif.
         |
         | Tanda "lebih besar" di dalam kondisi @if blok membuat Livewire
         | melewatkan penanda morph-nya, dan halaman jadi timpang tanpa galat
         | (PenandaMorphAdminTest menjaga ini).
         */
        // Unsur terakhir = tab mana yang dibuka kartunya di daftar surat.
        $kartuPerlu = collect([
            ['surat_baru', 'Surat belum ditindak', 'bi-envelope-exclamation', '#d97706', 'sudah dicatat, belum dibaca siapa pun', 'baru'],
            ['disposisi_telat', 'Disposisi lewat tenggat', 'bi-hourglass-bottom', '#b91c1c', 'tugasnya belum ditutup', 'telat'],
            ['retensi_habis', 'Masa simpan habis', 'bi-archive', '#7c3aed', 'perlu diputuskan: arsipkan atau musnahkan', 'retensi'],
        ]);
        $jumlahPerlu = $kartuPerlu->sum(fn ($k) => (int) ($perlu[$k[0]] ?? 0));
        $tanpaPerlu = $jumlahPerlu === 0;

        $kartuAngka = collect([
            ['masuk_bulan', 'Surat masuk bulan ini', 'bi-envelope-arrow-down', '#0ea5c6', now()->locale('id')->translatedFormat('F Y')],
            ['keluar_bulan', 'Surat keluar bulan ini', 'bi-envelope-arrow-up', '#17a974', now()->locale('id')->translatedFormat('F Y')],
            ['belum_selesai', 'Belum selesai', 'bi-list-task', '#d97706', 'semua periode'],
            ['seluruh', 'Seluruh arsip', 'bi-archive-fill', '#4f46e5', 'sejak awal pencatatan'],
        ]);

        $warnaSifat = [
            'biasa' => '#64748b',
            'segera' => '#d97706',
            'sangat_segera' => '#e11d48',
            'rahasia' => '#7c3aed',
        ];
        $warnaStatus = [
            'baru' => '#d97706',
            'diproses' => '#0284c7',
            'selesai' => '#16a34a',
            'diarsipkan' => '#64748b',
        ];
        $warnaArah = ['masuk' => '#0ea5c6', 'keluar' => '#17a974'];
        $ikonArah = ['masuk' => 'bi-envelope-arrow-down', 'keluar' => 'bi-envelope-arrow-up'];

        $adaTerbaru = $suratTerbaru->isNotEmpty();
        $adaDisposisi = $disposisiMenggantung->isNotEmpty();
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-folder2-open"></i> Mode Arsip Surat</span>
                <h1 class="dsb-judul">Buku Agenda Surat</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Catatan surat masuk dan surat keluar beserta klasifikasi, disposisi,
                        lampiran pindaian, dan tempat penyimpanan berkas fisiknya.
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                @if ($bolehCatat)
                    <a href="{{ route('admin.surat.create', ['arah' => 'masuk']) }}" wire:navigate class="dsb-tombol is-utama">
                        <i class="bi bi-envelope-arrow-down"></i><span>Catat Surat Masuk</span>
                    </a>
                    <a href="{{ route('admin.surat.create', ['arah' => 'keluar']) }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #17a974">
                        <i class="bi bi-envelope-arrow-up"></i><span>Catat Surat Keluar</span>
                    </a>
                @endif
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #d97706">
                    <i class="bi bi-box-arrow-left"></i><span>Kembali ke lemon</span>
                </a>
            </div>
        </header>

        {{-- ================== PERLU DITINDAK ================== --}}
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
                                <span class="dsb-chip is-samar">Semua surat sudah ditindak — tidak ada yang menunggu</span>
                            @endif
                        </div>
                    </div>
                </div>

                @foreach ($kartuPerlu as [$kunci, $label, $ikon, $warna, $ket, $tab])
                    <article class="dsb-tugas k-4" style="--c: {{ $warna }}">
                        <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <span class="dsb-tugas-isi">
                            <span class="dsb-stat-label">{{ $label }}</span>
                            <span class="dsb-tugas-angka">{{ (int) ($perlu[$kunci] ?? 0) }}</span>
                            <span class="dsb-stat-ket"><i class="bi {{ $ikon }}"></i><span>{{ $ket }}</span></span>
                        </span>
                        <a class="dsb-tutup-kartu" href="{{ route('admin.surat.index', ['tab' => $tab]) }}" wire:navigate>
                            <span class="visually-hidden">Buka {{ $label }}</span>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- ================== RINGKASAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kepala" style="--c: #4f46e5">
                    <span class="dsb-kepala-ikon"><i class="bi bi-bar-chart-fill"></i></span>
                    <div class="dsb-kepala-teks">
                        <span class="dsb-kicker">Ringkasan</span>
                        <h2 class="dsb-judul">Isi Arsip</h2>
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

        {{-- ================== DUA KOLOM: TERBARU & DISPOSISI ================== --}}
        {{-- sr-rak-puncak: kedua kartu berhenti di akhir isinya. Diregangkan
             sama tinggi, kartu yang daftarnya lebih pendek menyisakan lubang
             putih sebesar selisihnya — dan selisih itu biasanya besar, karena
             surat yang dicatat selalu jauh lebih banyak daripada tugas yang
             masih menggantung. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak sr-rak-puncak">
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0ea5c6"><i class="bi bi-clock-history"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Surat terakhir dicatat</h3>
                                <span class="dsb-kartu-sub">Enam yang paling akhir masuk buku agenda</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.surat.index') }}" wire:navigate class="dsb-tautan">Lihat semua</a>
                    </div>
                    <div class="dsb-daftar">
                        @forelse ($suratTerbaru as $s)
                            <a class="dsb-baris text-decoration-none" href="{{ route('admin.surat.detail', $s) }}" wire:navigate>
                                <span class="dsb-avatar" style="--c: {{ $warnaArah[$s->arah] ?? '#64748b' }}">
                                    <i class="bi {{ $ikonArah[$s->arah] ?? 'bi-envelope' }}"></i>
                                </span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $s->perihal }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ $s->nomor_agenda }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $s->pihak_label }}: {{ $s->pihak }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $s->tanggal_surat_teks }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    <span class="dsb-lencana" style="--c: {{ $warnaStatus[$s->status] ?? '#64748b' }}">{{ $s->status_label }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-envelope"></i></span>
                                <p class="dsb-kosong-judul">Buku agenda masih kosong</p>
                                <p class="dsb-kosong-ket">
                                    Mulai dari tombol <b>Catat Surat Masuk</b> di atas. Satu surat
                                    cukup diisi perihal dan tanggalnya; sisanya bisa dilengkapi nanti.
                                </p>
                                @if ($bolehCatat)
                                    <a href="{{ route('admin.surat.create', ['arah' => 'masuk']) }}" wire:navigate class="dsb-tombol is-utama" style="margin-top: 14px;">
                                        <i class="bi bi-plus-lg"></i><span>Catat surat pertama</span>
                                    </a>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b91c1c"><i class="bi bi-person-check"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Disposisi menggantung</h3>
                                <span class="dsb-kartu-sub">Tugas yang belum ditutup, paling mendesak di atas</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-daftar">
                        @forelse ($disposisiMenggantung as $d)
                            <a class="dsb-baris text-decoration-none" href="{{ route('admin.surat.detail', $d->surat_id) }}" wire:navigate>
                                <span class="dsb-avatar" style="--c: {{ $d->telat ? '#b91c1c' : '#0284c7' }}">
                                    <i class="bi bi-person-check"></i>
                                </span>
                                <span class="dsb-baris-isi">
                                    <span class="dsb-baris-judul">{{ $d->kepada_teks }}</span>
                                    <span class="dsb-baris-meta">
                                        <span>{{ \Illuminate\Support\Str::limit($d->instruksi, 54) }}</span>
                                        <span class="dsb-pisah">•</span>
                                        <span>{{ $d->batas_teks }}</span>
                                    </span>
                                </span>
                                <span class="dsb-baris-kanan">
                                    @if ($d->telat)
                                        <span class="dsb-lencana" style="--c: #b91c1c">Lewat tenggat</span>
                                    @else
                                        <span class="dsb-lencana" style="--c: #0284c7">{{ $d->status_label }}</span>
                                    @endif
                                </span>
                            </a>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-check2-circle"></i></span>
                                <p class="dsb-kosong-judul">Tidak ada tugas menggantung</p>
                                <p class="dsb-kosong-ket">
                                    Disposisi dibuat dari halaman detail surat: tentukan siapa yang
                                    mengerjakan, apa perintahnya, dan kapan tenggatnya.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== KLASIFIKASI ================== --}}
        @if ($bolehKlasifikasi)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi sr-baris-aksi">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-tags-fill"></i></span>
                            <div class="sr-baris-teks">
                                <b>Klasifikasi surat</b>
                                <span class="dsb-kartu-sub">
                                    {{ $klasifikasiAktif }} kode aktif. Kode klasifikasi menentukan
                                    surat ini termasuk urusan apa dan berapa lama wajib disimpan.
                                </span>
                            </div>
                            <a href="{{ route('admin.surat.klasifikasi') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #7c3aed">
                                <i class="bi bi-gear"></i><span>Atur klasifikasi</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>
