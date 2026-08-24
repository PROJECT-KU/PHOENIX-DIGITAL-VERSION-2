@section('title')
Detail Pesan Kontak || lemon
@stop

@php
    $terkait = $pesan['pesanan_terkait'] ?? [];
    $lain = $pesan['pesan_lain'] ?? [];
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($pesan))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-inbox"></i></div>
                    <p class="text-muted mb-3">Pesan tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.pesan') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke kotak masuk
                    </a>
                </div>
            </div>
        @else

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.pesan') }}" class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Kotak masuk
                            </a>
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">{{ $pesan['nama'] }}</h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge orcha-lencana-bayar-diterima">{{ $pesan['keperluan_label'] }}</span>
                                <span class="text-muted" style="font-size:.82rem">
                                    masuk
                                    {{ \Carbon\Carbon::parse($pesan['dibuat_pada'])->locale('id')->translatedFormat('j F Y, H:i') }}
                                </span>
                                @if ($pesan['sudah_dibaca'])
                                    <span class="text-muted orcha-ikon-teks" style="font-size:.78rem">
                                        <i class="bi bi-check2-all"></i> dibaca
                                        {{ \Carbon\Carbon::parse($pesan['dibaca_pada'])->locale('id')->translatedFormat('j M, H:i') }}
                                    </span>
                                @else
                                    <span class="badge orcha-lencana-bayar-menunggu">Belum dibaca</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            @if (! ($pesan['sudah_dibaca'] ?? false))
                                <button type="button" class="orcha-btn orcha-btn-lembut"
                                    wire:click="tandaiDibaca" wire:loading.attr="disabled">
                                    <i class="bi bi-check2"></i> Tandai Dibaca
                                </button>
                            @endif

                            @if ($this->tautanWa())
                                <button type="button" class="orcha-btn orcha-btn-lembut"
                                    data-wa-pesan="{{ $this->pesanWa() }}"
                                    title="Salin teks balasannya untuk ditempel di WhatsApp">
                                    <i class="bi bi-clipboard"></i> Salin Balasan
                                </button>

                                <a href="{{ $this->tautanWa() }}" target="_blank" rel="noopener"
                                    class="orcha-btn orcha-btn-wa" data-wa-pesan="{{ $this->pesanWa() }}">
                                    <i class="bi bi-whatsapp"></i> Balas via WA
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-7">

                    {{-- Isi pesannya diberi ruang paling besar. Itu yang dibaca,
                         sisanya hanya keterangan di sekitarnya. --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-chat-left-text text-primary"></i> Isi Pesan
                            </h2>

                            <div class="orcha-pesan-isi">{{ $pesan['pesan'] }}</div>

                            <div class="row g-3 mt-1">
                                @foreach ([
                                    ['WhatsApp', $pesan['whatsapp'] ?: '—', 'bi-whatsapp'],
                                    ['Email', $pesan['email'] ?: '—', 'bi-envelope'],
                                ] as [$label, $nilai, $ikon])
                                    <div class="col-12 col-sm-6">
                                        <div class="orcha-label-kecil orcha-ikon-teks">
                                            <i class="bi {{ $ikon }}"></i> {{ $label }}
                                        </div>
                                        <div class="fw-bold" style="font-size:.9rem">{{ $nilai }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Pesan sebelumnya dari orang yang sama. Yang sudah tiga kali
                         bertanya dan belum dibalas perlu diperlakukan berbeda dari
                         yang baru pertama menulis. --}}
                    @if ($lain)
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-3 p-lg-4">
                                <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    Pesan Lain dari Pengirim Ini
                                    <span class="badge orcha-lencana-catat ms-1">{{ count($lain) }}</span>
                                </h2>

                                @foreach ($lain as $satu)
                                    <div class="orcha-alasan {{ $satu['sudah_dibaca'] ? '' : 'orcha-alasan-tinggi' }} mb-2">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span class="orcha-label-kecil mb-0">{{ $satu['keperluan_label'] }}</span>
                                            <span class="text-muted" style="font-size:.76rem">
                                                {{ \Carbon\Carbon::parse($satu['dibuat_pada'])->locale('id')->translatedFormat('j M Y, H:i') }}
                                                @unless ($satu['sudah_dibaca'])
                                                    · <strong class="text-danger">belum dibaca</strong>
                                                @endunless
                                            </span>
                                        </div>
                                        <div class="mt-1" style="font-size:.86rem">
                                            {{ \Illuminate\Support\Str::limit($satu['pesan'], 180) }}
                                        </div>
                                        <a href="{{ route('admin.orcha.pesan.detail', $satu['id']) }}" wire:navigate
                                            class="orcha-tautan-balik mt-2" style="font-size:.8rem">
                                            Buka pesan ini <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-lg-5">

                    {{-- Pesanan pengirim. Inilah yang menentukan nada balasan:
                         calon pelanggan ditawari, pemesan ditenangkan. --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-bag-check text-primary"></i> Pesanan Pengirim
                            </h2>

                            @forelse ($terkait as $satu)
                                <div class="orcha-alasan mb-2">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <span class="orcha-kode">{{ $satu['kode'] }}</span>
                                        <span class="badge orcha-lencana-bayar-{{ $satu['status'] === 'batal' ? 'ditolak' : 'diterima' }}">
                                            {{ $satu['status_label'] }}
                                        </span>
                                    </div>
                                    <div class="fw-bold mt-1" style="font-size:.9rem">
                                        {{ $satu['keterangan'] ?: '—' }}
                                    </div>
                                    <div class="text-muted" style="font-size:.78rem">
                                        {{ $satu['jenis_label'] }}
                                        @if ($satu['mulai'])
                                            · mulai {{ \Carbon\Carbon::parse($satu['mulai'])->locale('id')->translatedFormat('j M Y') }}
                                        @endif
                                    </div>
                                </div>
                            @empty
                                {{-- Bukan kekurangan data, melainkan keterangan yang berguna:
                                     orang ini belum pernah memesan, jadi pesannya pertanyaan
                                     calon pelanggan. --}}
                                <div class="orcha-alasan">
                                    <span class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi bi-person-plus"></i> Belum pernah memesan
                                    </span>
                                    <div class="mt-1" style="font-size:.86rem">
                                        Nomor dan email ini belum tercatat pada pesanan mana pun.
                                        Perlakukan sebagai pertanyaan calon pelanggan.
                                    </div>
                                </div>
                            @endforelse

                            @if ($terkait)
                                <div class="text-muted mt-2" style="font-size:.78rem">
                                    Dicocokkan dari nomor WhatsApp dan email pengirim. Kode yang tidak
                                    muncul di sini bisa jadi dipesan dengan kontak berbeda.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')

    <style>
        /* Isi pesan dibaca utuh, jadi diberi ruang dan jarak baris yang lega —
           bukan diperlakukan seperti keterangan kecil di sekitarnya. */
        .orcha-pesan-isi {
            white-space: pre-line;
            font-size: .95rem;
            line-height: 1.7;
            color: #24384a;
            background: #f7f9fb;
            border: 1px solid #e3ecf3;
            border-left: 4px solid #1d6fa5;
            border-radius: .8rem;
            padding: 1rem 1.15rem;
        }
    </style>
</div>
