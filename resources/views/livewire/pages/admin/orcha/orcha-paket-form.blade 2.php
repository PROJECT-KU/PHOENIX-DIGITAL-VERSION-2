@section('title')
{{ $ubah ? 'Ubah' : 'Tambah' }} Paket Wisata || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => $ubah ? 'Ubah Paket Wisata' : 'Tambah Paket Wisata',
            'keterangan' => 'Tersimpan langsung di server Orcha, termasuk fotonya.',
        ])

        <form wire:submit="simpan">
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Keterangan Paket</h6>

                            <div class="row g-3">
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Nama paket <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        wire:model="nama" placeholder="Open Trip Banyuwangi">
                                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model="kategori">
                                        @foreach ($pilihanKategori as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Durasi</label>
                                    <input type="text" class="form-control" wire:model="durasi"
                                        placeholder="3 Hari 2 Malam">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal berangkat</label>
                                    <input type="date" class="form-control @error('tanggalBerangkat') is-invalid @enderror"
                                        wire:model="tanggalBerangkat">
                                    @error('tanggalBerangkat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal pulang</label>
                                    <input type="date" class="form-control @error('tanggalPulang') is-invalid @enderror"
                                        wire:model="tanggalPulang">
                                    @error('tanggalPulang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Titik jemput</label>
                                    <input type="text" class="form-control" wire:model="titikJemput"
                                        placeholder="Jogja, Klaten, Surakarta">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Minimal peserta <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('minimalPeserta') is-invalid @enderror"
                                        wire:model="minimalPeserta" min="1">
                                    @error('minimalPeserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Catatan promo</label>
                                    <input type="text" class="form-control" wire:model="catatanPromo"
                                        placeholder="Harga khusus sampai akhir bulan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Isi Paket</h6>

                            <label class="form-label small fw-semibold">Destinasi yang dikunjungi</label>
                            <textarea class="form-control" rows="5" wire:model="destinasiTeks"
                                placeholder="Satu destinasi satu baris:&#10;Kawah Ijen&#10;Pantai Pulau Merah&#10;Taman Nasional Baluran"></textarea>
                            <div class="form-text mb-3">Satu baris satu destinasi.</div>

                            <label class="form-label small fw-semibold">Fasilitas</label>
                            <textarea class="form-control" rows="5" wire:model="fasilitasTeks"
                                placeholder="Satu fasilitas satu baris:&#10;Transportasi AC&#10;Homestay&#10;Tiket masuk wisata"></textarea>
                            <div class="form-text mb-3">Satu baris satu fasilitas.</div>

                            <label class="form-label small fw-semibold">Itinerary</label>
                            <textarea class="form-control font-monospace" rows="10" wire:model="itineraryTeks"
                                placeholder="Day 1&#10;18.00 | Penjemputan meeting point&#10;19.00 | Perjalanan ke Banyuwangi&#10;&#10;Day 2&#10;03.00 | Tiba di Banyuwangi"></textarea>
                            <div class="form-text">
                                Baris tanpa tanda <code>|</code> dianggap judul hari; baris dengan <code>|</code>
                                dianggap agenda (<code>jam | kegiatan</code>).
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Harga</h6>

                            <label class="form-label small fw-semibold">Harga jual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control mb-3 @error('harga') is-invalid @enderror"
                                wire:model="harga" min="0">
                            @error('harga') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                            <label class="form-label small fw-semibold">Harga sebelum diskon</label>
                            <input type="number" class="form-control mb-1" wire:model="hargaAsli" min="0">
                            <div class="form-text mb-3">Kosongkan bila tidak ada coretan harga.</div>

                            <label class="form-label small fw-semibold">Diskon (%)</label>
                            <input type="number" class="form-control mb-3" wire:model="diskonPersen" min="0" max="100">

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="paket-terbaik"
                                    wire:model="pilihanTerbaik">
                                <label class="form-check-label small" for="paket-terbaik">
                                    Tandai sebagai pilihan terbaik
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Foto Sampul</h6>

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
                                Maksimal 4 MB. Dibiarkan kosong berarti foto lama tetap dipakai.
                            </div>

                            <div wire:loading wire:target="gambar" class="text-muted small mt-2">
                                Mengunggah…
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary rounded-3" wire:loading.attr="disabled"
                            wire:target="simpan">
                            <span wire:loading.remove wire:target="simpan">
                                {{ $ubah ? 'Simpan Perubahan' : 'Tambah Paket' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan ke Orcha…</span>
                        </button>
                        <a href="{{ route('admin.orcha.paket') }}" wire:navigate
                            class="btn btn-light border rounded-3">Batal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
