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
                'label' => $tgl->translatedFormat('d M Y'),
            ];
        }
        // Tandai label bulan saat bulan berganti (pakai hari pertama kolom yg dalam tahun).
        $acuan = collect($kolom)->firstWhere('dalamTahun', true);
        if ($acuan) {
            $b = \Carbon\Carbon::parse($acuan['tanggal'])->format('M');
            if ($b !== $bulanTerakhir) {
                $labelBulan[$kolomKe] = \Carbon\Carbon::parse($acuan['tanggal'])->translatedFormat('M');
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

<div class="akt-wrap">
    {{-- Ringkasan angka --}}
    <div class="akt-stats">
        <div class="akt-stat">
            <span class="akt-stat-angka">{{ $aktivitas['total'] }}</span>
            <span class="akt-stat-label">task selesai di {{ $tahun }}</span>
        </div>
        <div class="akt-stat">
            <span class="akt-stat-angka">{{ $aktivitas['streak'] }}</span>
            <span class="akt-stat-label">hari beruntun terpanjang</span>
        </div>
        <div class="akt-stat">
            <span class="akt-stat-angka">{{ $aktivitas['rataMingguan'] }}</span>
            <span class="akt-stat-label">rata-rata per minggu</span>
        </div>
        <div class="akt-stat">
            <span class="akt-stat-angka">{{ $aktivitas['terbaik']['jumlah'] }}</span>
            <span class="akt-stat-label">
                hari terbaik
                @if ($aktivitas['terbaik']['tanggal'])
                    ({{ \Carbon\Carbon::parse($aktivitas['terbaik']['tanggal'])->translatedFormat('d M') }})
                @endif
            </span>
        </div>
    </div>

    {{-- Grafik kontribusi --}}
    <div class="akt-graf-card">
        <div class="akt-graf-head">
            <span class="fw-bold text-dark" style="font-size:.9rem;">
                <i class="bi bi-grid-3x3 me-1"></i>Grafik Aktivitas {{ $tahun }}
            </span>
            <span class="text-muted" style="font-size:.75rem;">Ganti tahun lewat filter di atas</span>
        </div>

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

    {{-- Linimasa --}}
    <div class="akt-linimasa-card">
        <div class="fw-bold text-dark mb-3" style="font-size:.9rem;">
            <i class="bi bi-clock-history me-1"></i>Aktivitas Terbaru
        </div>

        @forelse ($aktivitas['linimasa'] as $t)
            @php $telat = $t->hariTerlambat(); @endphp
            <div class="akt-lini-item">
                <span class="akt-lini-dot {{ $telat > 0 ? 'is-telat' : '' }}">
                    <i class="bi {{ $telat > 0 ? 'bi-exclamation' : 'bi-check-lg' }}"></i>
                </span>
                <div class="akt-lini-isi">
                    <button type="button" class="akt-lini-judul" wire:click="openTask('{{ $t->id }}')">{{ $t->nama }}</button>
                    <div class="akt-lini-meta">
                        diselesaikan {{ $t->completed_at->translatedFormat('d M Y') }}
                        @if ($t->karyawan)
                            oleh {{ \Illuminate\Support\Str::of($t->karyawan->name)->explode(' ')->first() }}
                        @endif
                        @if ($telat > 0)
                            <span class="text-danger fw-semibold">— telat {{ $telat }} hari</span>
                        @else
                            <span class="text-success fw-semibold">— tepat waktu</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0" style="font-size:.88rem;">
                Belum ada task yang diselesaikan di tahun {{ $tahun }}.
            </p>
        @endforelse
    </div>
</div>
