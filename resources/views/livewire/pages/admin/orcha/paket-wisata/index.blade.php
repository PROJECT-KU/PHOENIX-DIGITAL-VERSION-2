@section('title')
Paket Wisata Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Paket Wisata Orcha',
            'keterangan' => 'Daftar paket yang tayang di website Orcha.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari nama paket...">
                        </div>
                    </div>

                    <div class="col-8 col-lg-2">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach ($pilihan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4 col-lg-2 text-end">
                        <a href="{{ route('admin.orcha.paket.tambah') }}" wire:navigate
                            class="btn btn-primary rounded-3 orcha-tombol w-100 justify-content-center">
                            <i class="bi bi-plus-lg"></i>
                            <span class="d-none d-sm-inline">Tambah</span>
                        </a>
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
                                    <th>Paket</th>
                                    <th>Penayangan</th>
                                    <th>Jadwal</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Min. peserta</th>
                                    <th class="text-center">Pendaftar</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($daftar as $baris)
                                    <tr wire:key="paket-{{ $baris['id'] }}">
                                        <td>
                                            <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $baris['kategori_label'] }} · {{ $baris['durasi'] }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge orcha-lencana-{{ $baris['status_tayang'] ?? 'tayang' }}">
                                                {{ $baris['status_tayang_label'] ?? '—' }}
                                            </span>
                                            @if (($baris['status_tayang'] ?? '') === 'terjadwal' && $baris['tayang_mulai'])
                                                <div class="text-muted" style="font-size:.74rem">
                                                    mulai
                                                    {{ \Carbon\Carbon::parse($baris['tayang_mulai'])->translatedFormat('d M Y, H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small">
                                            {{ $baris['jadwal_label'] ?: '—' }}
                                            @if ($baris['batas_pelunasan'])
                                                <div class="text-muted" style="font-size:.78rem">
                                                    Pelunasan sd
                                                    {{ \Carbon\Carbon::parse($baris['batas_pelunasan'])->translatedFormat('d M Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-nowrap">
                                            Rp {{ number_format((float) $baris['harga'], 0, ',', '.') }}
                                            @if ($baris['harga_asli'])
                                                <div class="text-muted text-decoration-line-through"
                                                    style="font-size:.75rem">
                                                    Rp {{ number_format((float) $baris['harga_asli'], 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $baris['minimal_peserta'] }}</td>
                                        <td class="text-center fw-semibold">{{ $baris['jumlah_pendaftar'] ?? 0 }}</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ $baris['tautan_publik'] }}" target="_blank" rel="noopener"
                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat" title="Lihat di website">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            <a href="{{ route('admin.orcha.paket.ubah', $baris['id']) }}" wire:navigate
                                                class="btn btn-sm orcha-aksi orcha-aksi-ubah" title="Ubah">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm orcha-bahaya pcek-konfirmasi"
                                                data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                data-title="Hapus paket ini?"
                                                data-text="{{ addslashes($baris['nama']) }} akan hilang dari website Orcha."
                                                data-confirm="Ya, hapus" data-icon="warning" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                                <i class="bi bi-map"></i>
                                            </div>
                                            <p class="text-muted mb-0">Belum ada paket yang cocok.</p>
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
