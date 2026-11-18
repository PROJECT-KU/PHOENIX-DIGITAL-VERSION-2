@section('title')
Keuntungan Sewa || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Gaya khusus layar ini. Inline, bukan lewat Vite: public/build masuk
           .gitignore dan tidak ikut ter-deploy. */
        .ks-rentang { display: flex; flex-wrap: wrap; gap: 8px; }
        .ks-saring { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    </style>

    @php
        $rp = fn ($angka) => 'Rp '.number_format((int) $angka, 0, ',', '.');
        $adaUntung = ($ringkas['margin_persen'] ?? null) !== null;
        $untung = (int) ($ringkas['untung'] ?? 0);
        // untung null = belum ada modal sama sekali; nol yang sah tetap ditampilkan.
    @endphp

    <div class="dsb ks-laporan">

        @if ($galat)
            <div class="dsb-galat">{{ $galat }}</div>
        @endif

        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Keuntungan Sewa</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        Selisih harga sewa dan modal yang dibayarkan ke vendor.
                        Pesanan yang batal tidak ikut, dan denda tidak dihitung sebagai untung.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.keuntungan') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #0284c7">
                    <i class="bi bi-graph-up-arrow"></i><span>Keuntungan Paket</span>
                </a>
            </div>
        </header>

        {{-- ===== Saringan ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="ks-rentang mb-3">
                        @foreach ([
                            ['semua', 'Semua waktu'],
                            ['bulan-ini', 'Bulan ini'],
                            ['bulan-lalu', 'Bulan lalu'],
                            ['tahun-ini', 'Tahun ini'],
                        ] as [$kunci, $label])
                            <button type="button"
                                class="dsb-tombol {{ $this->rentangAktif() === $kunci ? 'is-utama' : 'is-lembut' }}"
                                wire:click="pilihRentang('{{ $kunci }}')">
                                <span>{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="ks-saring">
                        <div>
                            <label class="form-label small fw-semibold">Dari tanggal</label>
                            <input type="date" class="form-control" wire:model.live="dari">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Sampai tanggal</label>
                            <input type="date" class="form-control" wire:model.live="sampai">
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Vendor</label>
                            <select class="form-select" wire:model.live="vendorId">
                                <option value="" @selected($this->vendorId === '')>Semua vendor</option>
                                @foreach ($daftarVendor as $v)
                                    <option value="{{ $v['id'] }}" @selected((string) $this->vendorId === (string) $v['id'])>
                                        {{ $v['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            {{-- Dua pertanyaan yang berbeda: sewa yang JALAN bulan ini
                                 dipakai menilai kesibukan armada, sewa yang DIPESAN bulan
                                 ini dipakai menilai pemasaran. --}}
                            <label class="form-label small fw-semibold">Dihitung menurut</label>
                            <select class="form-select" wire:model.live="dasar">
                                @foreach ($pilihanDasar as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected((string) $this->dasar === (string) $kunci)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Angka utama =====

             Tiap ubin membawa keterangan dasarnya sendiri. Pendapatan dihitung
             dari SELURUH pesanan; untung hanya dari yang modalnya sudah
             dicatat. Dua dasar yang berbeda di satu baris wajib disebut, kalau
             tidak admin akan mengurangkan sendiri pendapatan dengan modal dan
             mendapat angka yang bukan untungnya. --}}
        <div class="dsb-rak dsb-bagian">
            @php
                $nDihitung = (int) ($ringkas['pesanan_dihitung'] ?? 0);
            @endphp
            @foreach ([
                ['Pendapatan', $rp($ringkas['pendapatan'] ?? 0), 'bi-cash', '#0f766e',
                    'dari '.number_format((int) ($ringkas['pesanan'] ?? 0), 0, ',', '.').' pesanan'],
                ['Modal ke vendor', $rp($ringkas['modal'] ?? 0), 'bi-wallet2', '#b45309',
                    'dari '.number_format($nDihitung, 0, ',', '.').' pesanan yang dicatat'],
                ['Untung kotor', $adaUntung
                    ? $rp($untung).' · '.$ringkas['margin_persen'].'%'
                    : 'Belum bisa dihitung',
                    'bi-graph-up-arrow',
                    ! $adaUntung ? '#94a3b8' : ($untung < 0 ? '#b91c1c' : '#047857'),
                    $adaUntung
                        ? 'sewa '.$rp($ringkas['pendapatan_dihitung'] ?? 0).' − modal'
                        : 'belum ada modal yang dicatat'],
                ['Pesanan dihitung', number_format($nDihitung, 0, ',', '.').' dari '
                    .number_format((int) ($ringkas['pesanan'] ?? 0), 0, ',', '.'),
                    'bi-receipt', '#4f46e5', 'sisanya belum dicatat modalnya'],
            ] as [$label, $nilai, $ikon, $warna, $ket])
                <article class="dsb-stat k-3" style="--c: {{ $warna }}">
                    <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <span class="dsb-stat-label">{{ $label }}</span>
                    <span class="dsb-stat-nilai">{{ $nilai }}</span>
                    <span class="dsb-stat-ket"><span>{{ $ket }}</span></span>
                </article>
            @endforeach
        </div>

        {{-- Angka yang paling penting di layar ini.

             Selama masih ada pesanan yang modalnya belum dicatat, untung di
             atas BUKAN untung sebenarnya — ia untung dari sebagian pesanan
             saja. Rekap yang diam soal ini terlihat lebih rapi dan justru
             karena itu lebih berbahaya. --}}
        @if ((int) ($ringkas['tanpa_modal'] ?? 0) > 0)
            <div class="dsb-bagian">
                <div class="orcha-alasan orcha-alasan-sedang d-flex gap-2 align-items-start mb-0">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <div>
                        <strong>{{ $ringkas['tanpa_modal'] }} pesanan belum dicatat modalnya, dan tidak ikut dihitung.</strong>
                        <div style="font-size:.88rem" class="mt-1">
                            Angka untung di atas karena itu masih kurang dari yang sebenarnya.
                            Lengkapi lewat tab <strong>Belum ada vendor</strong> di
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                class="fw-bold text-decoration-none">daftar sewa</a>.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== Per unit & per vendor ===== --}}
        <div class="dsb-rak dsb-bagian">
            @foreach ([
                ['Per unit', 'bi-truck-front', '#4f46e5', $perUnit, 'Unit mana yang paling menguntungkan.'],
                ['Per vendor', 'bi-building', '#b45309', $perVendor, 'Vendor mana yang marginnya paling tipis.'],
            ] as [$judul, $ikon, $warna, $baris, $sub])
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}"><i class="bi {{ $ikon }}"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">{{ $judul }}</h2>
                                <span class="dsb-kartu-sub">{{ $sub }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        @forelse ($baris as $satu)
                            <div class="dsb-data-baris">
                                <span class="dsb-data-label">
                                    {{ $satu['nama'] }}
                                    <span class="text-muted">· {{ $satu['pesanan'] }} pesanan</span>
                                </span>
                                <span class="dsb-data-nilai"
                                    style="color: {{ $satu['untung'] < 0 ? '#b91c1c' : 'inherit' }}">
                                    {{ $rp($satu['untung']) }}
                                    @if ($satu['margin_persen'] !== null)
                                        <span class="text-muted">· {{ $satu['margin_persen'] }}%</span>
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-inbox"></i></span>
                                <p class="dsb-kosong-judul">Belum ada yang bisa dihitung</p>
                                <p class="dsb-kosong-ket">
                                    Sewa baru masuk hitungan setelah modalnya dicatat.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== Rincian per pesanan ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0f766e"><i class="bi bi-list-ul"></i></span>
                        <div>
                            <h2 class="dsb-kartu-judul">Rincian per pesanan</h2>
                            <span class="dsb-kartu-sub">Pesanan mana saja yang membentuk angka di atas.</span>
                        </div>
                    </div>
                </div>
                <div class="dsb-kartu-isi">
                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel">
                            <thead>
                                <tr>
                                    <th>Pesanan</th>
                                    <th>Unit</th>
                                    <th>Vendor</th>
                                    <th class="dsb-tabel-angka">Sewa</th>
                                    <th class="dsb-tabel-angka">Modal</th>
                                    <th class="dsb-tabel-angka">Untung</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rincian as $baris)
                                    <tr>
                                        <td data-judul="Pesanan">
                                            <span class="dsb-tabel-teks">
                                                <a class="dsb-tautan"
                                                    href="{{ route('admin.orcha.penyewaan.detail', $baris['id']) }}" wire:navigate>
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                </a>
                                                <span class="dsb-tabel-meta">
                                                    <span>{{ $baris['kode'] }} · {{ $baris['status_label'] }}</span>
                                                </span>
                                            </span>
                                        </td>
                                        <td data-judul="Unit">{{ $baris['kendaraan'] }}</td>
                                        <td data-judul="Vendor">{{ $baris['vendor'] ?: '—' }}</td>
                                        <td class="dsb-tabel-angka" data-judul="Sewa">{{ $rp($baris['pendapatan']) }}</td>
                                        <td class="dsb-tabel-angka" data-judul="Modal">
                                            {{ $baris['modal'] === null ? 'Belum dicatat' : $rp($baris['modal']) }}
                                        </td>
                                        <td class="dsb-tabel-angka" data-judul="Untung"
                                            style="color: {{ ($baris['untung'] ?? 0) < 0 ? '#b91c1c' : 'inherit' }}">
                                            {{ $baris['untung'] === null ? '—' : $rp($baris['untung']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="dsb-kosong">
                                                <span class="dsb-kosong-ikon"><i class="bi bi-inbox"></i></span>
                                                <p class="dsb-kosong-judul">Tidak ada sewa pada rentang ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @include('livewire.pages.admin.orcha.partials.paginasi', ['meta' => $meta])
                </div>
            </div>
        </div>
    </div>

    {{-- Penangan wire:navigate dkk. Wajib di tiap layar Orcha: pemasangannya
         lewat livewire:initialized hanya jalan sekali per muat penuh, jadi
         halaman yang dibuka lewat wire:navigate kehilangan penangannya — dan
         F5 menyembunyikan gejalanya. --}}
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
