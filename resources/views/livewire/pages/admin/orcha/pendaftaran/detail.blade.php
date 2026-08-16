@section('title')
Detail Pendaftaran || lemon
@stop

@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;

    $wa = fn ($nomor) => 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $nomor));

    $tagihan = $pendaftaran['tagihan'] ?? [];
    $pembayaran = $pendaftaran['pembayaran'] ?? [];
    $pembatalan = $pendaftaran['pembatalan'] ?? null;
    $peserta = $pendaftaran['peserta'] ?? [];
    $belumIsi = collect($pendaftaran['peserta_belum_isi'] ?? [])
        ->map(fn ($nama) => mb_strtolower(trim($nama)))
        ->all();

    $persenBayar = ($tagihan['total'] ?? 0) > 0
        ? min(100, round(($tagihan['sudah'] ?? 0) / $tagihan['total'] * 100))
        : 0;
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pendaftaran))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-person-x"></i></div>
                    <p class="text-muted mb-3">Data pendaftaran tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.pendaftaran') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            {{-- ============ KEPALA ============
                 Kode, nama, dan status berdiri paling depan: tiga hal itu yang
                 disebut pelanggan saat menelepon. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.pendaftaran') }}"
                                class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Semua pendaftaran
                            </a>
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">
                                {{ $pendaftaran['nama'] }}
                            </h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-kode">{{ $pendaftaran['kode'] }}</span>
                                <span class="text-muted" style="font-size:.82rem">
                                    Mendaftar
                                    {{ \Carbon\Carbon::parse($pendaftaran['dibuat_pada'])->translatedFormat('d F Y, H:i') }}
                                    WIB
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ $wa($pendaftaran['whatsapp']) }}" target="_blank" rel="noopener"
                                class="orcha-btn orcha-btn-wa">
                                <i class="bi bi-whatsapp"></i> Hubungi Pemesan
                            </a>

                            <select class="form-select" style="width:auto"
                                wire:change="ubahStatus($event.target.value)">
                                @foreach ($pilihanStatus as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($pendaftaran['status'] === $kunci)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- ============ RINGKASAN ANGKA ============ --}}
                    @if ($tagihan)
                        <div class="row g-3 mt-1">
                            @foreach ([
                                ['Total tagihan', $tagihan['total_teks'], '', 'bi-receipt'],
                                ['Sudah dibayar', $tagihan['sudah_teks'], 'lunas', 'bi-cash-coin'],
                                ['Sisa', $tagihan['sisa_teks'], ($tagihan['lunas'] ?? false) ? 'lunas' : 'sisa', 'bi-hourglass-split'],
                                ['Peserta', $pendaftaran['jumlah_peserta'] . ' orang', '', 'bi-people'],
                            ] as [$label, $nilai, $kelas, $ikon])
                                <div class="col-6 col-lg-3">
                                    <div class="orcha-ringkas {{ $kelas }}">
                                        <div class="orcha-label-kecil">
                                            <i class="bi {{ $ikon }}"></i> {{ $label }}
                                        </div>
                                        <div class="angka">{{ $nilai }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <div class="d-flex justify-content-between" style="font-size:.78rem">
                                <span class="text-muted">Kemajuan pembayaran</span>
                                <span class="fw-bold text-dark">{{ $persenBayar }}%</span>
                            </div>
                            <div class="orcha-palang mt-1 {{ ($tagihan['lunas'] ?? false) ? 'lunas' : '' }}">
                                <span style="width: {{ $persenBayar }}%"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($pembatalan)
                <div class="alert alert-danger border-0 rounded-4 d-flex gap-3 align-items-start">
                    <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                    <div>
                        <strong>Ada pengajuan pembatalan</strong>
                        <span class="badge bg-danger ms-1">{{ $pembatalan['status'] }}</span>
                        <div style="font-size:.88rem" class="mt-1">
                            {{ $pembatalan['jumlah_dibatalkan'] }} peserta ·
                            {{ $pembatalan['alasan_label'] }} ·
                            diajukan
                            {{ \Carbon\Carbon::parse($pembatalan['dibuat_pada'])->translatedFormat('d M Y') }}
                            oleh {{ $pembatalan['nama_pemohon'] }}
                        </div>
                        @if ($pembatalan['penjelasan'])
                            <div style="font-size:.85rem" class="mt-1 fst-italic">"{{ $pembatalan['penjelasan'] }}"</div>
                        @endif
                        <div style="font-size:.82rem" class="mt-1">
                            Rekening pengembalian: <strong>{{ $pembatalan['rekening'] }}</strong>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row g-4">

                {{-- ============ KOLOM KIRI ============ --}}
                <div class="col-12 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3" style="font-size:1.05rem">
                                <i class="bi bi-person-vcard text-primary"></i> Data Pemesan
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    ['Nama lengkap', $pendaftaran['nama'], null],
                                    ['WhatsApp', $pendaftaran['whatsapp'], $wa($pendaftaran['whatsapp'])],
                                    ['Email', $pendaftaran['email'] ?: '—', $pendaftaran['email'] ? 'mailto:' . $pendaftaran['email'] : null],
                                    ['Status pendaftaran', $pendaftaran['status_label'], null],
                                ] as [$label, $nilai, $tautan])
                                    <div class="col-12 col-md-6">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        @if ($tautan)
                                            <a href="{{ $tautan }}" target="_blank" rel="noopener"
                                                class="fw-bold text-decoration-none">{{ $nilai }}</a>
                                        @else
                                            <div class="fw-bold">{{ $nilai }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($pendaftaran['catatan'])
                                <div class="mt-3 p-3 rounded-3 bg-light">
                                    <div class="orcha-label-kecil">Catatan dari pemesan</div>
                                    <div style="font-size:.9rem">{{ $pendaftaran['catatan'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ============ PESERTA ============
                         Rombongan sering berangkat dari kota berbeda, dan tiap
                         peserta mengisi riwayat kesehatannya sendiri. Kedua hal
                         itu ditampilkan berdampingan supaya admin tahu siapa
                         yang perlu ditagih tanpa membuka menu lain. --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h2 class="fw-bold mb-0" style="font-size:1.05rem">
                                    <i class="bi bi-people-fill text-primary"></i> Peserta &amp; Titik Jemput
                                </h2>
                                <span class="badge {{ ($pendaftaran['kesehatan_lengkap'] ?? false) ? 'orcha-lencana-bayar-diterima' : 'orcha-lencana-bayar-menunggu' }}">
                                    <i class="bi bi-heart-pulse"></i>
                                    {{ $pendaftaran['kesehatan_terisi'] ?? 0 }}/{{ $pendaftaran['jumlah_peserta'] }}
                                    riwayat kesehatan
                                </span>
                            </div>

                            @forelse ($peserta as $satu)
                                @php
                                    $sudahIsi = ! in_array(mb_strtolower(trim($satu['nama'] ?? '')), $belumIsi, true);
                                    $inisial = collect(explode(' ', trim($satu['nama'] ?? '?')))->filter()->take(2)
                                        ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))->implode('');
                                @endphp
                                <div class="orcha-peserta">
                                    <span class="orcha-inisial">{{ $inisial ?: '?' }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $satu['nama'] ?: '—' }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            <i class="bi bi-geo-alt"></i>
                                            {{ $satu['titik_jemput'] ?: 'Titik jemput belum dipilih' }}
                                        </div>
                                    </div>
                                    @if ($sudahIsi)
                                        <span class="orcha-lencana-aman"><i class="bi bi-check-circle-fill"></i> Kesehatan terisi</span>
                                    @else
                                        <span class="orcha-lencana-awas"><i class="bi bi-clock-history"></i> Belum mengisi</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-muted mb-0" style="font-size:.9rem">
                                    Pendaftaran ini belum mencatat nama peserta satu per satu.
                                </p>
                            @endforelse

                            <div class="mt-3">
                                @if (! auth()->user()->hasPermission('view_orcha_kesehatan'))
                                    <span class="text-muted" style="font-size:.85rem">
                                        <i class="bi bi-lock"></i> Riwayat kesehatan hanya bisa dibuka akun berizin.
                                    </span>
                                @elseif (($pendaftaran['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                    <button type="button" class="orcha-btn orcha-btn-kesehatan"
                                        wire:click="bukaRiwayat" wire:loading.attr="disabled">
                                        <i class="bi bi-heart-pulse"></i>
                                        Lihat Riwayat Kesehatan ({{ $pendaftaran['jumlah_riwayat_kesehatan'] }})
                                    </button>
                                @else
                                    <span class="text-muted" style="font-size:.85rem">
                                        <i class="bi bi-info-circle"></i> Belum ada peserta yang mengisi riwayat kesehatan.
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ KOLOM KANAN ============ --}}
                <div class="col-12 col-lg-5">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3" style="font-size:1.05rem">
                                <i class="bi bi-suitcase-lg text-primary"></i> Perjalanan
                            </h2>

                            <div class="d-flex flex-column gap-3">
                                @foreach ([
                                    ['Paket', $pendaftaran['paket']['nama'] ?: '—', 'bi-map'],
                                    ['Tanggal berangkat', $pendaftaran['tanggal_berangkat'] ? \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->translatedFormat('l, d F Y') : 'Menyusul', 'bi-calendar-event'],
                                    ['Titik jemput rombongan', $pendaftaran['titik_jemput'] ?: 'Dikonfirmasi tim', 'bi-geo-alt'],
                                ] as [$label, $nilai, $ikon])
                                    <div class="d-flex gap-3">
                                        <div class="stat-icon-wrapper bg-gradient-blue" style="width:38px;height:38px;flex:0 0 38px">
                                            <i class="bi {{ $ikon }}"></i>
                                        </div>
                                        <div>
                                            <div class="orcha-label-kecil">{{ $label }}</div>
                                            <div class="fw-bold">{{ $nilai }}</div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (! empty($pendaftaran['jemput_per_titik']))
                                    <div>
                                        <div class="orcha-label-kecil mb-1">Pengelompokan jemputan</div>
                                        @foreach ($pendaftaran['jemput_per_titik'] as $titik => $orang)
                                            <div style="font-size:.85rem">
                                                <span class="fw-semibold text-dark">{{ $titik }}:</span>
                                                <span class="text-muted">{{ implode(', ', $orang) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ============ PEMBAYARAN ============ --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h2 class="fw-bold mb-0" style="font-size:1.05rem">
                                    <i class="bi bi-cash-stack text-primary"></i> Bukti Pembayaran
                                </h2>
                                <a href="{{ route('admin.orcha.pembayaran') }}" class="text-decoration-none"
                                    style="font-size:.8rem">Kelola semua</a>
                            </div>

                            @forelse ($pembayaran as $bayar)
                                <div class="d-flex gap-3 pb-3 mb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    @if ($bayar['bukti'])
                                        <a href="{{ $tautanBukti($bayar['bukti']) }}" target="_blank" rel="noopener">
                                            <img src="{{ $tautanBukti($bayar['bukti']) }}" alt="Bukti transfer"
                                                class="orcha-bukti">
                                        </a>
                                    @else
                                        <div class="orcha-bukti d-flex align-items-center justify-content-center text-muted">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <span class="fw-bold">{{ $bayar['nominal_formatted'] }}</span>
                                            <span class="badge orcha-lencana-bayar-{{ $bayar['status'] }}">
                                                {{ $bayar['status_label'] }}
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $bayar['jenis_label'] }} ·
                                            {{ $bayar['tanggal_transfer'] ? \Carbon\Carbon::parse($bayar['tanggal_transfer'])->translatedFormat('d M Y') : '—' }}
                                        </div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $bayar['bank_pengirim'] }} a.n. {{ $bayar['atas_nama_pengirim'] }}
                                        </div>
                                        @if ($bayar['catatan_admin'])
                                            <div class="mt-1" style="font-size:.78rem">
                                                <span class="orcha-label-kecil d-inline">Catatan admin:</span>
                                                {{ $bayar['catatan_admin'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-cash-coin"></i></div>
                                    <p class="text-muted mb-0" style="font-size:.88rem">
                                        Belum ada bukti transfer yang dikirim pelanggan.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ============ RIWAYAT KESEHATAN ============
         Data sensitif: hanya dimuat saat dibuka, dan pembukaannya tercatat di
         sisi Orcha beserta akun yang membukanya. --}}
    @if ($riwayatTerbuka)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,45,74,.35)">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0 pb-2">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Riwayat Kesehatan Peserta</h5>
                            <span class="text-muted small">
                                {{ $pendaftaran['nama'] ?? '' }} · {{ $pendaftaran['kode'] ?? '' }}
                            </span>
                        </div>
                        <button type="button" class="btn-close" wire:click="tutupRiwayat"></button>
                    </div>

                    <div class="modal-body pt-2">
                        <div class="alert alert-info border-0 rounded-3 d-flex gap-2 align-items-start"
                            style="font-size:.82rem">
                            <i class="bi bi-shield-lock"></i>
                            <span>Data kesehatan bersifat pribadi. Pembukaannya tercatat di server Orcha
                                beserta akun yang membukanya.</span>
                        </div>

                        @forelse ($riwayat as $peserta)
                            @include('livewire.pages.admin.orcha.partials.kartu-kesehatan', ['peserta' => $peserta])
                        @empty
                            <p class="text-muted mb-0">Belum ada riwayat kesehatan yang diisi.</p>
                        @endforelse
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="orcha-btn orcha-btn-lembut" wire:click="tutupRiwayat">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
