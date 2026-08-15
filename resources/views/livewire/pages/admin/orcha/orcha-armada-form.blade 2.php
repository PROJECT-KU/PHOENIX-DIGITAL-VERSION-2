@section('title')
{{ $ubah ? 'Ubah' : 'Tambah' }} Kendaraan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $ubah ? 'Ubah Kendaraan' : 'Tambah Kendaraan',
            'keterangan' => 'Tarif per jam, per 12 jam, dan per hari disimpan terpisah karena memang berbeda.',
        ])

        <form wire:submit="simpan">
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Keterangan Unit</h6>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Nama unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        wire:model="nama" placeholder="All New Avanza">
                                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Merek <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('merek') is-invalid @enderror"
                                        wire:model="merek" placeholder="Toyota">
                                    @error('merek') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Jenis <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model="jenis">
                                        @foreach ($pilihanJenis as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Nomor polisi</label>
                                    <input type="text" class="form-control" wire:model="nopol" placeholder="AB 1234 CD">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Kapasitas (kursi) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('kapasitas') is-invalid @enderror"
                                        wire:model="kapasitas" min="1">
                                    @error('kapasitas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Transmisi tersedia <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        @foreach (['Manual', 'Matic'] as $pilihan)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    id="transmisi-{{ $pilihan }}" value="{{ $pilihan }}"
                                                    wire:model="transmisi">
                                                <label class="form-check-label" for="transmisi-{{ $pilihan }}">
                                                    {{ $pilihan }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('transmisi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        Centang keduanya bila unit ini tersedia dalam dua transmisi — daftar di
                                        website akan menulis "Manual &amp; Matic".
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="unit-tersedia"
                                            wire:model="tersedia">
                                        <label class="form-check-label small" for="unit-tersedia">
                                            Unit siap disewakan (tampil di website)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1">Tarif</h6>
                            <p class="text-muted small mb-3">
                                Kosongkan satuan yang memang tidak dijual — bus biasanya tidak dilepas per jam.
                            </p>

                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Per hari <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('tarifHari') is-invalid @enderror"
                                        wire:model="tarifHari" min="0">
                                    @error('tarifHari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Per jam</label>
                                    <input type="number" class="form-control" wire:model="tarifJam" min="0">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Paket 12 jam</label>
                                    <input type="number" class="form-control" wire:model="tarif12Jam" min="0">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Sopir / hari</label>
                                    <input type="number" class="form-control" wire:model="tarifSopir" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Foto Unit</h6>

                            @if ($gambar)
                                <img src="{{ $gambar->temporaryUrl() }}" alt=""
                                    class="img-fluid rounded-3 mb-3" style="max-height: 180px">
                            @elseif ($gambarLama)
                                <img src="{{ str_starts_with($gambarLama, 'http') ? $gambarLama : rtrim(str_replace('/api/v1', '', config('orcha.url')), '/') . $gambarLama }}"
                                    alt="" class="img-fluid rounded-3 mb-3" style="max-height: 180px">
                            @endif

                            <input type="file" class="form-control @error('gambar') is-invalid @enderror"
                                wire:model="gambar" accept="image/*">
                            @error('gambar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">
                                Maksimal 4 MB. Tanpa foto, kartu di website memakai latar gradasi bermerek.
                            </div>

                            <div wire:loading wire:target="gambar" class="text-muted small mt-2">Mengunggah…</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-3" wire:loading.attr="disabled"
                            wire:target="simpan">
                            <span wire:loading.remove wire:target="simpan">
                                {{ $ubah ? 'Simpan Perubahan' : 'Tambah Kendaraan' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan ke Orcha…</span>
                        </button>
                        <a href="{{ route('admin.orcha.armada') }}" wire:navigate
                            class="btn btn-light border rounded-3">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
