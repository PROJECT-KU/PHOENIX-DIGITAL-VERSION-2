@section('title')
Armada Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Armada Orcha',
            'keterangan' => 'Armada beserta tarif per jam, per 12 jam, dan per hari.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari nama atau merek kendaraan...">
                        </div>
                    </div>

                    <div class="col-8 col-lg-2">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua jenis</option>
                            @foreach ($pilihan as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4 col-lg-2 text-end">
                        <a href="{{ route('admin.orcha.armada.tambah') }}" wire:navigate
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
                                    <th>Unit</th>
                                    <th>Transmisi</th>
                                    <th class="text-end">Per jam</th>
                                    <th class="text-end">12 jam</th>
                                    <th class="text-end">Per hari</th>
                                    <th class="text-end">Sopir</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rupiah = fn ($angka) => $angka ? 'Rp ' . number_format((float) $angka, 0, ',', '.') : '—';
                                @endphp
                                @forelse ($daftar as $baris)
                                    <tr wire:key="kendaraan-{{ $baris['id'] }}">
                                        <td>
                                            <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $baris['merek'] }} · {{ $baris['jenis_label'] }} ·
                                                {{ $baris['kapasitas'] }} kursi
                                                {{ $baris['nopol'] ? '· ' . $baris['nopol'] : '' }}
                                            </div>
                                        </td>
                                        <td class="small">{{ $baris['transmisi_label'] }}</td>
                                        <td class="text-end small text-nowrap">{{ $rupiah($baris['tarif']['jam']) }}</td>
                                        <td class="text-end small text-nowrap">{{ $rupiah($baris['tarif']['12jam']) }}</td>
                                        <td class="text-end fw-semibold text-nowrap">{{ $rupiah($baris['tarif']['hari']) }}</td>
                                        <td class="text-end small text-nowrap">{{ $rupiah($baris['tarif']['sopir_per_hari']) }}</td>
                                        <td class="text-center">
                                            @if ($baris['tersedia'])
                                                <span class="badge bg-success-subtle text-success">Siap</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Tidak aktif</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('admin.orcha.armada.ubah', $baris['id']) }}" wire:navigate
                                                class="btn btn-sm btn-light border rounded-3 orcha-tombol" title="Ubah">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm orcha-bahaya pcek-konfirmasi"
                                                data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                data-title="Hapus unit ini?"
                                                data-text="{{ addslashes($baris['nama']) }} akan hilang dari daftar sewa."
                                                data-confirm="Ya, hapus" data-icon="warning" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                                <i class="bi bi-truck"></i>
                                            </div>
                                            <p class="text-muted mb-0">Belum ada kendaraan yang cocok.</p>
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
