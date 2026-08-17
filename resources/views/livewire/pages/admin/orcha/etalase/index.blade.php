@php
    $judul = match ($jenis) {
        'testimoni' => 'Testimoni',
        'partner' => 'Partner',
        default => 'Destinasi Populer',
    };
    $asalGambar = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalGambar . $jalur)
        : null;
@endphp

@section('title')
{{ $judul }} Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $judul . ' Orcha',
            'keterangan' => 'Ditambah dan diubah dari sini; tersimpan di server Orcha.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari {{ strtolower($judul) }}...">
                        </div>
                    </div>

                    @if ($jenis === 'destinasi')
                        <div class="col-12 col-lg-3">
                            <select wire:model.live="filterStatus" class="form-select">
                                <option value="">Semua wilayah</option>
                                @foreach ($pilihanWilayah as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-12 {{ $jenis === 'destinasi' ? 'col-lg-3' : 'col-lg-6' }} text-lg-end">
                        <button type="button" class="btn btn-primary rounded-3 orcha-tombol orcha-tombol-tambah" wire:click="tambah">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah {{ $judul }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($daftar as $baris)
                <div class="col-12 col-md-6 col-xl-4" wire:key="{{ $jenis }}-{{ $baris['id'] }}">
                    <div class="card orcha-kartu h-100">
                        @php $foto = $tautanGambar($baris['foto'] ?? $baris['logo'] ?? null); @endphp

                        @if ($foto)
                            <img src="{{ $foto }}" alt="{{ $baris['nama'] }}"
                                class="rounded-top-4" style="height: 150px; object-fit: cover">
                        @endif

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h6 class="fw-bold mb-0">{{ $baris['nama'] }}</h6>

                                @if ($jenis === 'testimoni')
                                    <span class="text-warning small text-nowrap">
                                        {{ str_repeat('★', (int) $baris['rating']) }}
                                    </span>
                                @elseif ($jenis === 'destinasi')
                                    <span class="badge bg-light text-dark">{{ $baris['wilayah_label'] ?? '' }}</span>
                                @endif
                            </div>

                            @if ($jenis === 'destinasi')
                                <div class="text-muted small mb-2">{{ $baris['provinsi'] ?: '—' }}</div>
                                <p class="small text-muted mb-0">
                                    {{ \Illuminate\Support\Str::limit($baris['deskripsi'] ?? '', 110) }}
                                </p>
                            @elseif ($jenis === 'testimoni')
                                <p class="small text-muted mb-0">
                                    {{ \Illuminate\Support\Str::limit($baris['isi'] ?? '', 140) }}
                                </p>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-light border rounded-3 orcha-tombol"
                                    wire:click='ubah(@json($baris))'>
                                    <i class="bi bi-pencil"></i>
                                    <span>Ubah</span>
                                </button>

                                <button type="button" class="btn btn-sm orcha-bahaya pcek-konfirmasi"
                                    data-action="hapus" data-arg="{{ $baris['id'] }}"
                                    data-title="Hapus {{ strtolower($judul) }}?"
                                    data-text="{{ addslashes($baris['nama']) }} akan dihapus dari website Orcha."
                                    data-confirm="Ya, hapus" data-icon="warning">
                                    <i class="bi bi-trash"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body text-center py-5">
                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                <i class="bi bi-collection"></i>
                            </div>
                            <p class="text-muted mb-0">Belum ada {{ strtolower($judul) }}.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Formulir tambah / ubah --}}
    @if ($formTerbuka)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,45,74,.35)">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold mb-0">
                            {{ $sedangDiubah ? 'Ubah' : 'Tambah' }} {{ $judul }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="tutup"></button>
                    </div>

                    <form wire:submit="simpan">
                        <div class="modal-body">
                            <label class="form-label small fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control mb-3 @error('nama') is-invalid @enderror"
                                wire:model="nama">
                            @error('nama') <div class="invalid-feedback d-block mb-3">{{ $message }}</div> @enderror

                            @if ($jenis === 'destinasi')
                                <label class="form-label small fw-semibold">Wilayah <span class="text-danger">*</span></label>
                                <select class="form-select mb-3" wire:model="wilayah">
                                    @foreach ($pilihanWilayah as $kunci => $label)
                                        <option value="{{ $kunci }}">{{ $label }}</option>
                                    @endforeach
                                </select>

                                <label class="form-label small fw-semibold">Provinsi</label>
                                <input type="text" class="form-control mb-3" wire:model="provinsi"
                                    placeholder="Jawa Timur">

                                <label class="form-label small fw-semibold">Deskripsi</label>
                                <textarea class="form-control mb-3" rows="3" wire:model="deskripsi"></textarea>

                                <label class="form-label small fw-semibold">Perkiraan pengunjung</label>
                                <input type="number" class="form-control mb-3" wire:model="totalPengunjung" min="0">
                            @elseif ($jenis === 'testimoni')
                                <label class="form-label small fw-semibold">Rating <span class="text-danger">*</span></label>
                                <select class="form-select mb-3" wire:model="rating">
                                    @for ($bintang = 5; $bintang >= 1; $bintang--)
                                        <option value="{{ $bintang }}">
                                            {{ str_repeat('★', $bintang) }} ({{ $bintang }})
                                        </option>
                                    @endfor
                                </select>

                                <label class="form-label small fw-semibold">Isi testimoni <span class="text-danger">*</span></label>
                                <textarea class="form-control mb-3 @error('isi') is-invalid @enderror" rows="4"
                                    wire:model="isi"></textarea>
                                @error('isi') <div class="invalid-feedback d-block mb-3">{{ $message }}</div> @enderror
                            @endif

                            <label class="form-label small fw-semibold">
                                {{ $jenis === 'partner' ? 'Logo' : ($jenis === 'testimoni' ? 'Foto orangnya' : 'Foto') }}
                            </label>

                            @if ($gambar)
                                <div class="mb-2">
                                    <img src="{{ $gambar->temporaryUrl() }}" alt=""
                                        class="img-fluid rounded-3" style="max-height: 140px">
                                </div>
                            @elseif ($gambarLama && $tautanGambar($gambarLama))
                                <div class="mb-2">
                                    <img src="{{ $tautanGambar($gambarLama) }}" alt=""
                                        class="img-fluid rounded-3" style="max-height: 140px">
                                </div>
                            @endif

                            <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                wire:model="gambar" accept="image/*">
                            @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Maksimal 4 MB. Kosong berarti gambar lama tetap dipakai.</div>
                            <div wire:loading wire:target="gambar" class="text-muted small mt-2">Mengunggah…</div>
                        </div>

                        <div class="modal-footer border-0">
                            <button type="button" class="btn orcha-bahaya" wire:click="tutup">
                                <i class="bi bi-x-lg"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary rounded-3" wire:loading.attr="disabled"
                                wire:target="simpan">
                                <span wire:loading.remove wire:target="simpan">Simpan</span>
                                <span wire:loading wire:target="simpan">Menyimpan…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
