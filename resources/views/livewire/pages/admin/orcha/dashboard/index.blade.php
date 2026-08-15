@section('title')
Dashboard Orcha Journey || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Dashboard Orcha Journey',
            'keterangan' => 'Data diambil langsung dari server Orcha. Tidak ada salinan yang disimpan di lemon.',
        ])

        {{-- Yang perlu ditindak dulu, baru angka-angka lain. Admin membuka
             halaman ini untuk tahu "apa yang harus saya kerjakan sekarang". --}}
        @php
            $perlu = [
                ['pendaftaran_baru', 'Pendaftaran baru', 'bi-clipboard-check', 'admin.orcha.pendaftaran'],
                ['penyewaan_baru', 'Sewa kendaraan baru', 'bi-truck', 'admin.orcha.penyewaan'],
                ['pembatalan_diajukan', 'Pembatalan diajukan', 'bi-x-circle', 'admin.orcha.pembatalan'],
                ['pesan_belum_dibaca', 'Pesan belum dibaca', 'bi-inbox', 'admin.orcha.pesan'],
            ];
            $adaPekerjaan = collect($perlu)->sum(fn ($baris) => (int) ($perluDitindak[$baris[0]] ?? 0)) > 0;
        @endphp

        <div class="row g-3 mb-4">
            @foreach ($perlu as [$kunci, $label, $ikon, $rute])
                @php $nilai = (int) ($perluDitindak[$kunci] ?? 0); @endphp
                <div class="col-6 col-lg-3">
                    <a href="{{ route($rute) }}" wire:navigate class="text-decoration-none">
                        <div class="card orcha-kartu h-100">
                            <div class="card-body d-flex align-items-center gap-3 p-3">
                                <span class="orcha-ikon {{ $nilai > 0 ? 'perlu' : '' }}">
                                    <i class="bi {{ $ikon }}"></i>
                                </span>
                                <div>
                                    <div class="orcha-angka">{{ $nilai }}</div>
                                    <div class="text-muted small">{{ $label }}</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        @if (! $galat && ! $adaPekerjaan)
            <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <span>Tidak ada yang menunggu ditindak di Orcha saat ini.</span>
            </div>
        @endif

        {{-- Angka etalase --}}
        <div class="row g-3 mb-4">
            @foreach ($kartu as $item)
                @if (! str_contains($item['kunci'], 'baru') && $item['kunci'] !== 'pembatalan_diajukan' && $item['kunci'] !== 'pesan_belum_dibaca')
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card orcha-kartu h-100">
                            <div class="card-body text-center p-3">
                                <div class="orcha-angka">{{ $item['nilai'] }}</div>
                                <div class="text-muted small">{{ $item['label'] }}</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="row g-4">
            {{-- Rincian paket & armada --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Paket per kategori</h6>
                        @forelse ($paketPerKategori as $baris)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small">{{ $baris['label'] }}</span>
                                <span class="fw-bold">{{ $baris['jumlah'] }}</span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data.</p>
                        @endforelse

                        <h6 class="fw-bold mt-4 mb-3">Armada per jenis</h6>
                        @forelse ($kendaraanPerJenis as $baris)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="small">{{ $baris['label'] }}</span>
                                <span class="small text-muted">
                                    <span class="fw-bold text-dark">{{ $baris['tersedia'] }}</span> siap /
                                    {{ $baris['jumlah'] }} unit
                                </span>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Pendaftaran terbaru --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Pendaftaran terbaru</h6>
                            <a href="{{ route('admin.orcha.pendaftaran') }}" wire:navigate
                                class="small text-decoration-none">Lihat semua</a>
                        </div>
                        @forelse ($pendaftaranTerbaru as $baris)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="orcha-kode small">{{ $baris['kode'] }}</span>
                                    <span class="badge bg-light text-dark">{{ $baris['status_label'] }}</span>
                                </div>
                                <div class="small fw-semibold">{{ $baris['nama'] }}</div>
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ $baris['paket']['nama'] }} · {{ $baris['jumlah_peserta'] }} peserta
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada pendaftaran.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sewa terbaru --}}
            <div class="col-12 col-xl-4">
                <div class="card orcha-kartu h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Sewa kendaraan terbaru</h6>
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                class="small text-decoration-none">Lihat semua</a>
                        </div>
                        @forelse ($penyewaanTerbaru as $baris)
                            <div class="py-2 border-bottom">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="orcha-kode small">{{ $baris['kode'] }}</span>
                                    <span class="badge bg-light text-dark">{{ $baris['status_label'] }}</span>
                                </div>
                                <div class="small fw-semibold">{{ $baris['nama'] }}</div>
                                <div class="text-muted" style="font-size:.78rem">
                                    {{ $baris['kendaraan']['nama'] }} · {{ $baris['durasi_label'] }}
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada pemesanan sewa.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
