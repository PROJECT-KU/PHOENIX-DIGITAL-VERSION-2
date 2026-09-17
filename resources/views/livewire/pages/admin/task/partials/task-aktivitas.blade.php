{{--
    Tampilan "Aktivitas" ala GitHub — CARA PANDANG saja.

    Grafik kontribusi: satu kotak = satu hari, warnanya makin pekat makin banyak
    task diselesaikan hari itu. Setahun penuh (seperti GitHub), tahunnya ikut
    filter tahun yang sudah ada.

    Datanya dari $aktivitas (dihitung di dataAktivitas() memakai Task::visibleTo()
    yang SAMA) — tidak ada aturan visibilitas baru.
--}}
@php
    $tahun = $aktivitas['tahun'];
    $perHari = $aktivitas['perHari'];

    // Grid ala GitHub: kolom = minggu, baris = hari (Senin..Minggu).
    // Mulai dari hari Senin pada/atau sebelum 1 Januari supaya kolom rapi.
    $mulai = \Carbon\Carbon::create($tahun, 1, 1)->startOfDay();
    $akhir = \Carbon\Carbon::create($tahun, 12, 31)->endOfDay();
    $kursor = $mulai->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

    $minggu = [];
    $labelBulan = [];   // index kolom => nama bulan (ditulis sekali per bulan)
    $kolomKe = 0;
    $bulanTerakhir = null;

    while ($kursor->lte($akhir)) {
        $kolom = [];
        for ($h = 0; $h < 7; $h++) {
            $tgl = $kursor->copy()->addDays($h);
            $kolom[] = [
                'tanggal' => $tgl->toDateString(),
                'dalamTahun' => (int) $tgl->year === $tahun,
                'depan' => $tgl->isFuture(),
                'jumlah' => $perHari[$tgl->toDateString()] ?? 0,
                'label' => $tgl->locale('id')->translatedFormat('d M Y'),
            ];
        }
        // Tandai label bulan saat bulan berganti (pakai hari pertama kolom yg dalam tahun).
        $acuan = collect($kolom)->firstWhere('dalamTahun', true);
        if ($acuan) {
            $b = \Carbon\Carbon::parse($acuan['tanggal'])->format('M');
            if ($b !== $bulanTerakhir) {
                $labelBulan[$kolomKe] = \Carbon\Carbon::parse($acuan['tanggal'])->locale('id')->translatedFormat('M');
                $bulanTerakhir = $b;
            }
        }
        $minggu[] = $kolom;
        $kolomKe++;
        $kursor->addWeek();
    }

    // Skala warna: 0 / 1 / 2 / 3 / 4+ (seperti GitHub).
    $tingkat = function (int $n): int {
        if ($n <= 0) return 0;
        if ($n === 1) return 1;
        if ($n === 2) return 2;
        if ($n === 3) return 3;
        return 4;
    };
@endphp

{{-- Rupanya memakai bahasa rupa dasbor (dsb-*) seperti tabel dan papan
     scrum: kartu angka, ubin ikon berwarna, dan baris daftar yang sama.
     Yang tetap khas layar ini hanya kisi kontribusinya. --}}
<div class="dsb-rak">
    @php
        $angkaAkt = [
            ['#16a34a', 'bi-check-circle-fill', $aktivitas['total'], 'Task selesai', 'Sepanjang '.$tahun],
            ['#f26522', 'bi-fire', $aktivitas['streak'], 'Hari beruntun', 'Rentetan terpanjang tahun ini'],
            ['#0284c7', 'bi-speedometer2', $aktivitas['rataMingguan'], 'Rata-rata', 'Task selesai per minggu'],
            ['#7c3aed', 'bi-trophy-fill', $aktivitas['terbaik']['jumlah'], 'Hari terbaik',
                $aktivitas['terbaik']['tanggal']
                    ? \Carbon\Carbon::parse($aktivitas['terbaik']['tanggal'])->locale('id')->translatedFormat('d M Y')
                    : 'Belum ada'],
        ];
    @endphp

    @foreach ($angkaAkt as [$warna, $ikon, $nilai, $label, $ket])
        <article class="dsb-stat k-3" style="--c: {{ $warna }}">
            <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
            <p class="dsb-stat-label">{{ $label }}</p>
            <p class="dsb-stat-nilai">{{ $nilai }}</p>
            <p class="dsb-stat-ket"><i class="bi bi-dot"></i><span>{{ $ket }}</span></p>
        </article>
    @endforeach

    {{-- Grafik kontribusi --}}
    <div class="dsb-kartu k-12">
        <div class="dsb-kartu-kepala">
            <div class="dsb-kartu-kepala-kiri">
                <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                <div>
                    <h3 class="dsb-kartu-judul">Grafik Aktivitas {{ $tahun }}</h3>
                    <span class="dsb-kartu-sub">Ganti tahunnya lewat saringan di atas</span>
                </div>
            </div>
        </div>
        <div class="dsb-kartu-isi">

        <div class="akt-scroll">
            <div class="akt-graf">
                {{-- Label bulan --}}
                <div class="akt-bulan-row">
                    <span class="akt-hari-spacer"></span>
                    @foreach ($minggu as $i => $_)
                        <span class="akt-bulan-cell">{{ $labelBulan[$i] ?? '' }}</span>
                    @endforeach
                </div>

                <div class="akt-grid-row">
                    {{-- Label hari (Sen/Rab/Jum seperti GitHub) --}}
                    <div class="akt-hari-col">
                        @foreach (['Sen', '', 'Rab', '', 'Jum', '', ''] as $h)
                            <span class="akt-hari-label">{{ $h }}</span>
                        @endforeach
                    </div>

                    {{-- Kotak per minggu --}}
                    @foreach ($minggu as $kolom)
                        <div class="akt-minggu">
                            @foreach ($kolom as $hari)
                                @if (! $hari['dalamTahun'] || $hari['depan'])
                                    <span class="akt-kotak akt-kosong"></span>
                                @else
                                    <span class="akt-kotak akt-l{{ $tingkat($hari['jumlah']) }}"
                                        title="{{ $hari['jumlah'] }} task selesai — {{ $hari['label'] }}"></span>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Legenda --}}
        <div class="akt-legenda">
            <span>Sedikit</span>
            <span class="akt-kotak akt-l0"></span>
            <span class="akt-kotak akt-l1"></span>
            <span class="akt-kotak akt-l2"></span>
            <span class="akt-kotak akt-l3"></span>
            <span class="akt-kotak akt-l4"></span>
            <span>Banyak</span>
            </div>
        </div>
    </div>

    {{-- Linimasa --}}
    <div class="dsb-kartu k-12">
        <div class="dsb-kartu-kepala">
            <div class="dsb-kartu-kepala-kiri">
                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-clock-history"></i></span>
                <div>
                    <h3 class="dsb-kartu-judul">Aktivitas Terbaru</h3>
                    <span class="dsb-kartu-sub">Task yang terakhir diselesaikan</span>
                </div>
            </div>
        </div>

        <div class="dsb-daftar">
            @forelse ($aktivitas['linimasa'] as $t)
                @php $telat = $t->hariTerlambat(); @endphp
                <div class="dsb-baris" wire:key="akt-{{ $t->id }}" wire:click="openTask('{{ $t->id }}')" style="cursor: pointer;">
                    <span class="dsb-avatar" style="--c: {{ $telat > 0 ? '#d97706' : '#16a34a' }}">
                        <i class="bi {{ $telat > 0 ? 'bi-exclamation-lg' : 'bi-check-lg' }}"></i>
                    </span>
                    <span class="dsb-baris-isi">
                        <span class="dsb-baris-judul">{{ $t->nama }}</span>
                        <span class="dsb-baris-meta">
                            <span>{{ $t->completed_at->locale('id')->translatedFormat('d M Y') }}</span>
                            @if ($t->karyawan)
                                <span class="dsb-pisah">&bull;</span>
                                <span>{{ \Illuminate\Support\Str::of($t->karyawan->name)->explode(' ')->first() }}</span>
                            @endif
                        </span>
                    </span>
                    <span class="dsb-baris-kanan">
                        <span class="dsb-lencana {{ $telat > 0 ? 'is-kuning' : 'is-hijau' }}">
                            {{ $telat > 0 ? 'Telat '.$telat.' hari' : 'Tepat waktu' }}
                        </span>
                    </span>
                </div>
            @empty
                <div class="dsb-kosong">
                    <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                    <p class="dsb-kosong-judul">Belum ada yang diselesaikan</p>
                    <p class="dsb-kosong-ket">Belum ada task yang diselesaikan sepanjang {{ $tahun }}.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
