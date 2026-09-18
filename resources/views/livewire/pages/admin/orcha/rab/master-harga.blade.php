@section('title')
Master Harga || lemon
@stop

@php
    $formTerbuka = $tambah || $sunting;
    $perluKapasitas = in_array($isian['satuan'] ?? '', $berkapasitas, true);
    $jumlahData = (int) ($meta['total'] ?? count($daftar));
    $bisaBersih = $this->adaSaringan();
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Master Harga',
            'keterangan' => 'Harga tiket, armada, hotel, makan, dan pemandu yang dipakai menyusun RAB.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-bagian-kepala mb-0">
                    <div class="orcha-bagian-nomor"><i class="bi bi-info-circle"></i></div>
                    <div>
                        <div class="orcha-bagian-judul">Cara harganya dipakai</div>
                        <div class="orcha-bagian-sub">
                            Harga <strong>dibekukan</strong> saat masuk ke sebuah RAB. Mengubah harga di sini
                            hanya berlaku untuk RAB berikutnya; RAB lama diberi tanda supaya bisa diperbarui bila perlu.
                            <br>
                            Isi <strong>destinasi</strong> pada tiket supaya ikut tertarik saat destinasinya dipilih di itinerary.
                            Tandai <strong>wajib</strong> untuk biaya yang selalu ada (makan, parkir, asuransi) — otomatis masuk ke setiap RAB baru di provinsinya.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saringan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-3">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari nama, destinasi, atau daerah...'])
                    </div>
                    <div class="col-6 col-lg-3">
                        <select wire:model.live="filterKategori" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach ($kategori as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <select wire:model.live="filterProvinsi" class="form-select">
                            <option value="">Semua provinsi</option>
                            <option value="-">Berlaku umum (semua provinsi)</option>
                            @foreach ($provinsi as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-grid">
                        <button type="button" wire:click="bukaTambah" class="orcha-btn orcha-btn-utama justify-content-center">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah Harga</span>
                        </button>
                    </div>
                </div>
                @if ($bisaBersih)
                    <div class="mt-2">
                        <button type="button" wire:click="bersihkanSaringan" class="btn btn-link btn-sm p-0 text-decoration-none">
                            <i class="bi bi-x-circle"></i> Bersihkan saringan
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if ($formTerbuka)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <h6 class="fw-bold mb-3 orcha-judul-ikon">
                        <i class="bi bi-tags text-primary"></i>
                        {{ $sunting ? 'Ubah Harga' : 'Harga Baru' }}
                    </h6>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select wire:model="isian.kategori" class="form-select">
                                @foreach ($kategori as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-semibold">Nama <span class="text-danger">*</span></label>
                            <input type="text" wire:model="isian.nama" maxlength="150"
                                class="form-control @error('isian.nama') is-invalid @enderror"
                                placeholder="Tiket Candi Prambanan (domestik) / Sewa Hiace + driver + BBM">
                            @error('isian.nama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Provinsi</label>
                            <select wire:model.live="isian.provinsi" class="form-select">
                                <option value="">Berlaku umum (semua provinsi)</option>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Destinasi <span class="text-muted fw-normal">(untuk tiket)</span></label>
                            <input type="text" wire:model.blur="isian.destinasi" list="rab-daftar-destinasi" maxlength="150"
                                class="form-control" placeholder="Pilih dari katalog">
                            <datalist id="rab-daftar-destinasi">
                                @foreach ($destinasi as $d)
                                    <option value="{{ $d }}"></option>
                                @endforeach
                            </datalist>
                            <div class="form-text">Nama harus sama dengan katalog supaya ikut tertarik.</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Daerah</label>
                            <input type="text" wire:model="isian.daerah" maxlength="80" class="form-control" placeholder="Sleman">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold">Dihitung per <span class="text-danger">*</span></label>
                            <select wire:model.live="isian.satuan" class="form-select">
                                @foreach ($satuan as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">Harga <span class="text-danger">*</span></label>
                            <div class="orcha-rupiah">
                                <input type="text" inputmode="numeric" wire:model.blur="isian.harga"
                                    class="orcha-uang form-control @error('isian.harga') is-invalid @enderror" placeholder="50.000">
                            </div>
                            @error('isian.harga')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small fw-semibold">
                                Kapasitas
                                @if ($perluKapasitas)<span class="text-danger">*</span>@endif
                            </label>
                            <input type="number" min="1" wire:model="isian.kapasitas"
                                class="form-control @error('isian.kapasitas') is-invalid @enderror"
                                placeholder="{{ $perluKapasitas ? '14 kursi / 2 orang per kamar' : 'Tidak perlu' }}">
                            @if ($perluKapasitas)
                                <div class="form-text">Jumlah unit dihitung sendiri: 17 peserta ÷ 14 kursi = 2 unit.</div>
                            @endif
                            @error('isian.kapasitas')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Catatan internal</label>
                            <input type="text" wire:model="isian.catatan" maxlength="500" class="form-control"
                                placeholder="Vendor, nomor kontak, harga berlaku sampai…">
                        </div>

                        <div class="col-12 col-lg-6">
                            <label class="orcha-sakelar-kartu {{ $otomatis ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $otomatis ? 'bi-magic' : 'bi-hand-index' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $otomatis ? 'Wajib — masuk sendiri' : 'Dipilih manual' }}</span>
                                    <span class="ket">
                                        {{ $otomatis
                                            ? 'Otomatis masuk ke setiap RAB baru di provinsinya.'
                                            : 'Ditambahkan ke RAB bila admin memilihnya.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="otomatis">
                                </span>
                            </label>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                                <span class="isi">
                                    <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                    <span class="ket">
                                        {{ $aktif
                                            ? 'Muncul sebagai pilihan saat menyusun RAB.'
                                            : 'Tersimpan, tetapi tidak ditawarkan di RAB baru.' }}
                                    </span>
                                </span>
                                <span class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-3 mt-4 border-top">
                        <button type="button" wire:click="tutup" class="orcha-btn orcha-btn-lembut">Batal</button>
                        <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                            class="orcha-btn orcha-btn-utama">
                            <span wire:loading.remove wire:target="simpan"><i class="bi bi-check2-circle"></i> Simpan</span>
                            <span wire:loading wire:target="simpan">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="text-muted small mb-3">{{ $jumlahData }} harga tersimpan.</div>
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>NAMA</th>
                                <th>KATEGORI</th>
                                <th>WILAYAH</th>
                                <th>DIHITUNG PER</th>
                                <th class="text-end">HARGA</th>
                                <th>STATUS</th>
                                <th class="text-end">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="mh-{{ $baris['id'] }}">
                                    <td>
                                        <div class="fw-bold">{{ $baris['nama'] }}</div>
                                        @if ($baris['destinasi'])
                                            <div class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $baris['destinasi'] }}</div>
                                        @endif
                                        @if ($baris['catatan'])
                                            <div class="text-muted small fst-italic">{{ $baris['catatan'] }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary-emphasis">{{ $baris['kategori_label'] }}</span></td>
                                    <td class="small">
                                        {{ $baris['provinsi'] ?: 'Semua provinsi' }}
                                        @if ($baris['daerah'])<div class="text-muted">{{ $baris['daerah'] }}</div>@endif
                                    </td>
                                    <td class="small">
                                        {{ $baris['satuan_label'] }}
                                        @if ($baris['kapasitas'])<div class="text-muted">kapasitas {{ $baris['kapasitas'] }}</div>@endif
                                    </td>
                                    <td class="text-end fw-bold text-nowrap">{{ $baris['harga_teks'] }}</td>
                                    <td>
                                        @if ($baris['aktif'])
                                            <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Mati</span>
                                        @endif
                                        @if ($baris['otomatis'])
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Wajib</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                                wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                title="Ubah harga ini">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-hapus pcek-konfirmasi"
                                                data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                data-title="Hapus {{ $baris['nama'] }}?"
                                                data-text="RAB yang sudah memakainya tidak berubah."
                                                data-confirm="Ya, hapus" data-icon="warning"
                                                title="Hapus harga ini">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-3"><i class="bi bi-tags"></i></div>
                                        <p class="text-muted mb-0">
                                            {{ $bisaBersih ? 'Tidak ada harga yang cocok dengan saringan.' : 'Belum ada harga. Mulai dari tiket destinasi yang paling sering diminta.' }}
                                        </p>
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
