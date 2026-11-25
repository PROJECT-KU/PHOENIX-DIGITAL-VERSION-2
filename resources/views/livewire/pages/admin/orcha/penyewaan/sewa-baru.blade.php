@section('title')
Catat Sewa Kendaraan || lemon
@stop

<div class="page-heading">
    <div class="page-title mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="gradient-text fw-bold mb-1">Catat Sewa Kendaraan</h3>
                <div class="breadcrumb-custom">
                    @php
                        $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Sewa Masuk', 'url' => route('admin.orcha.penyewaan')],
                            ['name' => 'Catat Sewa'],
                        ];
                    @endphp
                    <x-breadcrumb :items="$breadcrumbs" />
                </div>
                <p class="text-muted mb-0 mt-2">
                    Untuk pesanan yang masuk lewat telepon atau WhatsApp. Harga, modal, dan tenggat
                    pengembaliannya dihitung Orcha dari tarif unitnya — sama persis dengan pesanan
                    yang datang dari situs.
                </p>
            </div>
        </div>
    </div>

    @if ($galat)
    <div class="alert alert-danger rounded-4 border-0">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ $galat }}
    </div>
    @endif

    {{-- ===== Berhasil ===== --}}
    @if ($hasil)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 text-center">
            <i class="bi bi-check-circle-fill text-success" style="font-size:2.5rem"></i>
            <h5 class="fw-bold mt-3 mb-1">Penyewaan tercatat</h5>
            <p class="text-muted">
                Kode <b>{{ $hasil['kode'] ?? '—' }}</b> — {{ $hasil['nama_kendaraan'] ?? '' }}
                untuk {{ $hasil['nama'] ?? '' }}.
            </p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                @if (!empty($hasil['id']))
                <a href="{{ route('admin.orcha.penyewaan.detail', $hasil['id']) }}" wire:navigate
                    class="btn btn-primary">Buka rinciannya</a>
                @endif
                <button type="button" class="btn btn-light border" wire:click="lagi">Catat satu lagi</button>
            </div>
        </div>
    </div>
    @else

    {{-- ===== Borang ===== --}}
    <form wire:submit="simpan">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Unit & jadwal</h6>

                        <div class="mb-3">
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            {{-- @selected pada SETIAP kotak pilih di layar ini.

                                 wire:model saja tidak menulis atribut selected ke
                                 HTML: nilainya baru dipasang JavaScript setelah
                                 Livewire hidup. Pada gambar pertama halaman —
                                 dan pada potret layar — kotaknya memperlihatkan
                                 pilihan PERTAMA, bukan yang sebenarnya terpilih. --}}
                            <select class="form-select" wire:model.live="unitId">
                                <option value="" @selected($unitId === '')>— pilih unit —</option>
                                @foreach ($daftarUnit as $u)
                                <option value="{{ $u['id'] }}" @selected($unitId === (string) $u['id'])>{{ $u['sebutan'] ?? $u['nama'] }}</option>
                                @endforeach
                            </select>
                            @error('unitId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            @if (empty($daftarUnit))
                            {{-- Daftar kosong berarti Orcha tidak terjangkau, BUKAN
                                 bahwa armadanya habis. Dibedakan karena keduanya
                                 menuntut tindakan yang sama sekali berbeda. --}}
                            <div class="text-warning small mt-1">
                                <i class="bi bi-exclamation-triangle"></i>
                                Daftar armada belum bisa diambil dari Orcha. Coba muat ulang sebentar lagi.
                            </div>
                            @endif
                        </div>

                        @if ($unit)
                        {{-- Aturan unitnya ditulis terang-terangan. Admin yang
                             menerima telepon harus bisa menjawab "boleh lepas
                             kunci?" tanpa membuka halaman armada di tab lain. --}}
                        <div class="alert alert-light border rounded-4 small">
                            <div><b>{{ $unit['kapasitas'] ?? '?' }} penumpang</b> · {{ $unit['jenis_label'] ?? '' }}</div>
                            <div>{{ $unit['lepas_kunci_label'] ?? '' }} · {{ $unit['sopir_label'] ?? '' }}</div>
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Transmisi <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="transmisi">
                                    @foreach ($unit['transmisi_tersedia'] ?? ['Manual', 'Matic'] as $t)
                                    <option value="{{ $t }}" @selected($transmisi === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="satuan">
                                    @foreach ($satuanPilihan as $s)
                                    <option value="{{ $s }}" @selected($satuan === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <label class="form-label">Durasi <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="30" class="form-control" wire:model="durasi">
                                @error('durasi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-4">
                                <label class="form-label">Tanggal mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="tanggalMulai">
                                @error('tanggalMulai') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-4">
                                <label class="form-label">Jam mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" wire:model="jamMulai">
                                @error('jamMulai') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-sm-6">
                                <label class="form-label">Sopir</label>
                                <select class="form-select" wire:model.live="denganSopir"
                                    @if ($unit && ! ($unit['lepas_kunci'] ?? true)) disabled @endif>
                                    <option value="ya" @selected($denganSopir === 'ya')>Dengan sopir kami</option>
                                    <option value="tidak" @selected($denganSopir === 'tidak')>Lepas kunci</option>
                                </select>
                                @if ($unit && ! ($unit['lepas_kunci'] ?? true))
                                <div class="text-muted small mt-1">Unit ini hanya disewakan bersama sopir.</div>
                                @endif
                            </div>

                            <div class="col-sm-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="luar-kota" wire:model.live="luarKota">
                                    <label class="form-check-label" for="luar-kota">Perjalanan luar kota</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Antar & jemput</h6>

                        <div class="mb-3">
                            <label class="form-label">
                                {{ $denganSopir === 'ya' ? 'Titik penjemputan' : 'Lokasi pengantaran' }}
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" wire:model="lokasiAntar"
                                placeholder="Mis. Bandara YIA / Jl. Kaliurang km 5">
                            @error('lokasiAntar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        @if ($denganSopir === 'ya')
                        <div class="mb-3">
                            <label class="form-label">Tujuan</label>
                            <input type="text" class="form-control" wire:model="tujuan" placeholder="Mis. Bromo">
                        </div>
                        @else
                        <div class="mb-3">
                            <label class="form-label">Lokasi pengembalian</label>
                            <input type="text" class="form-control" wire:model="lokasiKembali"
                                placeholder="Kosongkan bila sama dengan lokasi pengantaran">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3">Penyewa</h6>

                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="nama">
                            @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="whatsapp" placeholder="0812-3456-7890">
                            @error('whatsapp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-muted fw-normal">(opsional)</span></label>
                            {{-- Opsional di sini, wajib di borang publik: penyewa
                                 yang menelepon kerap tidak punya surel, dan
                                 menolak pesanannya karena itu berarti kehilangan
                                 pesanan yang sudah disepakati. --}}
                            <input type="email" class="form-control" wire:model="email">
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            {{-- Boleh langsung 'dp_masuk': pesanan telepon kerap
                                 sudah disertai transfer sebelum barisnya dibuat. --}}
                            <select class="form-select" wire:model="status">
                                @foreach ($daftarStatus as $kunci => $label)
                                <option value="{{ $kunci }}" @selected($status === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea rows="3" class="form-control" wire:model="catatan"></textarea>
                            @error('catatan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="simpan"><i class="bi bi-check-lg"></i> Simpan</span>
                                <span wire:loading wire:target="simpan">Menyimpan…</span>
                            </button>
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate class="btn btn-light border">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
