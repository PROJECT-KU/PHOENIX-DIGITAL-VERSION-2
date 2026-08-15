@section('title')
Sewa Kendaraan Masuk || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Sewa Kendaraan Masuk',
            'keterangan' => 'Pemesanan sewa yang dikirim lewat formulir di website Orcha.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari kode, nama, WhatsApp, atau unit...">
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua status</option>
                            @foreach ($pilihanStatus as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Penyewa</th>
                                <th>Kendaraan</th>
                                <th>Mulai</th>
                                <th class="text-end">Estimasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="penyewaan-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">{{ $baris['whatsapp'] }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $baris['kendaraan']['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['kendaraan']['transmisi'] }} · {{ $baris['durasi_label'] }} ·
                                            {{ $baris['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }}
                                        </div>
                                    </td>
                                    <td class="small text-nowrap">
                                        {{ $baris['tanggal_mulai'] ? \Carbon\Carbon::parse($baris['tanggal_mulai'])->translatedFormat('d M Y') : '—' }}
                                        <div class="text-muted" style="font-size:.78rem">{{ $baris['jam_mulai'] ?: '—' }}</div>
                                    </td>
                                    <td class="text-end fw-semibold text-nowrap">
                                        {{ $baris['estimasi_biaya'] ? 'Rp ' . number_format($baris['estimasi_biaya'], 0, ',', '.') : '—' }}
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm"
                                            wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)">
                                            @foreach ($pilihanStatus as $kunci => $label)
                                                <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-truck"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada pemesanan sewa yang cocok.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
