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

        <form wire:submit="simpan" class="orcha-form">
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Keterangan Paket</h6>

                            <div class="row g-3">
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Nama paket <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        wire:model="nama" value="{{ $nama }}" placeholder="Open Trip Banyuwangi">
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
                                    <input type="text" class="form-control" wire:model="durasi" value="{{ $durasi }}"
                                        placeholder="3 Hari 2 Malam">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal berangkat</label>
                                    <input type="date" class="form-control @error('tanggalBerangkat') is-invalid @enderror"
                                        wire:model="tanggalBerangkat" value="{{ $tanggalBerangkat }}">
                                    @error('tanggalBerangkat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tanggal pulang</label>
                                    <input type="date" class="form-control @error('tanggalPulang') is-invalid @enderror"
                                        wire:model="tanggalPulang" value="{{ $tanggalPulang }}">
                                    @error('tanggalPulang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold">Titik jemput</label>
                                    <input type="text" class="form-control" wire:model="titikJemput" value="{{ $titikJemput }}"
                                        placeholder="Jogja, Klaten, Surakarta">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Minimal peserta <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('minimalPeserta') is-invalid @enderror"
                                        wire:model="minimalPeserta" value="{{ $minimalPeserta }}" min="1">
                                    @error('minimalPeserta') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Catatan promo</label>
                                    <input type="text" class="form-control" wire:model="catatanPromo" value="{{ $catatanPromo }}"
                                        placeholder="Harga khusus sampai akhir bulan">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Destinasi & fasilitas: dipilih dari daftar bersama yang
                         tumbuh sendiri. Dua kartu ini bentuknya sama persis, jadi
                         ditulis sekali lewat perulangan. --}}
                    @foreach ([['destinasi', 'Destinasi yang Dikunjungi', $saranDestinasi, $destinasi, 'destinasiBaru', 'Destinasi lain, mis. Pantai Pulau Merah'], ['fasilitas', 'Fasilitas', $saranFasilitas, $fasilitas, 'fasilitasBaru', 'Fasilitas lain, mis. Tiket kapal']] as [$jenis, $judulKartu, $saran, $terpilih, $medanBaru, $contoh])
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <div class="orcha-kepala-kartu">
                                    <h6 class="fw-bold mb-0">{{ $judulKartu }}</h6>
                                    <span class="badge orcha-hitung">{{ count($terpilih) }} dipilih</span>
                                </div>
                                <p class="orcha-petunjuk">
                                    Klik untuk memasukkan ke paket ini. Tanda
                                    <i class="bi bi-x-lg"></i> pada daftar bawah menghapusnya dari
                                    pilihan untuk semua paket.
                                </p>

                                @if ($terpilih)
                                    <div class="orcha-terpilih">
                                        @foreach ($terpilih as $urutan => $item)
                                            <span class="orcha-cip orcha-cip-aktif" wire:key="pilih-{{ $jenis }}-{{ $urutan }}">
                                                <span>{{ $item }}</span>
                                                <button type="button" class="orcha-cip-buang"
                                                    wire:click="buang('{{ $jenis }}', {{ $urutan }})"
                                                    title="Keluarkan dari paket ini">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="orcha-kosong">Belum ada yang dipilih.</p>
                                @endif

                                <div class="orcha-saran">
                                    @forelse ($saran as $item)
                                        @php $sudah = in_array($item['nama'], $terpilih, true); @endphp
                                        <span class="orcha-cip {{ $sudah ? 'orcha-cip-sudah' : '' }}"
                                            wire:key="saran-{{ $jenis }}-{{ $item['id'] }}">
                                            <button type="button" class="orcha-cip-pilih"
                                                wire:click="jungkit('{{ $jenis }}', @js($item['nama']))"
                                                title="{{ $sudah ? 'Keluarkan dari paket ini' : 'Masukkan ke paket ini' }}">
                                                <i class="bi {{ $sudah ? 'bi-check2' : 'bi-plus' }}"></i>
                                                {{ $item['nama'] }}
                                            </button>
                                            <button type="button" class="orcha-cip-hapus pcek-konfirmasi"
                                                data-action="hapusSaran" data-arg="{{ $item['id'] }}"
                                                data-title="Hapus dari daftar pilihan?"
                                                data-text="{{ addslashes($item['nama']) }} tidak lagi muncul sebagai pilihan cepat. Paket yang sudah tersimpan tidak berubah."
                                                data-confirm="Ya, hapus" data-icon="warning"
                                                title="Hapus dari daftar pilihan">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </span>
                                    @empty
                                        <span class="orcha-kosong mb-0">
                                            Daftar masih kosong — tambahkan di bawah.
                                        </span>
                                    @endforelse
                                </div>

                                <div class="input-group orcha-tambah">
                                    <input type="text" class="form-control" wire:model="{{ $medanBaru }}"
                                        wire:keydown.enter.prevent="tambah('{{ $jenis }}')"
                                        placeholder="{{ $contoh }}">
                                    <button type="button" class="btn btn-primary"
                                        wire:click="tambah('{{ $jenis }}')" wire:loading.attr="disabled">
                                        <i class="bi bi-plus-lg"></i> Tambah
                                    </button>
                                </div>
                                <div class="form-text">
                                    Yang ditambahkan di sini langsung masuk daftar pilihan, jadi paket
                                    berikutnya tinggal mengklik.
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Itinerary: baris per agenda, bukan mengetik format sendiri --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <div class="orcha-kepala-kartu">
                                <h6 class="fw-bold mb-0">Itinerary</h6>
                                <span class="badge orcha-hitung">{{ count($hari) }} hari</span>
                            </div>
                            <p class="orcha-petunjuk">
                                Isi jam dan kegiatannya. Baris tanpa kegiatan tidak ikut tersimpan.
                            </p>

                            @foreach ($hari as $urutanHari => $satuHari)
                                <div class="orcha-hari" wire:key="hari-{{ $urutanHari }}">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="orcha-nomor-hari">{{ $urutanHari + 1 }}</span>
                                        <input type="text" class="form-control form-control-lg fw-semibold"
                                            wire:model="hari.{{ $urutanHari }}.nama" value="{{ $satuHari['nama'] }}"
                                            placeholder="Day 1">

                                        @if (count($hari) > 1)
                                            <button type="button" class="btn btn-outline-danger rounded-3"
                                                wire:click="buangHari({{ $urutanHari }})" title="Hapus hari ini">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @foreach ($satuHari['agenda'] as $urutanAgenda => $agenda)
                                        <div class="orcha-baris-agenda" wire:key="agenda-{{ $urutanHari }}-{{ $urutanAgenda }}">
                                            <input type="text" class="form-control form-control-lg orcha-jam"
                                                wire:model="hari.{{ $urutanHari }}.agenda.{{ $urutanAgenda }}.jam"
                                                value="{{ $agenda['jam'] }}" placeholder="07.00">
                                            <input type="text" class="form-control form-control-lg"
                                                wire:model="hari.{{ $urutanHari }}.agenda.{{ $urutanAgenda }}.kegiatan"
                                                value="{{ $agenda['kegiatan'] }}"
                                                placeholder="Penjemputan di meeting point">
                                            <button type="button" class="btn btn-light border rounded-3"
                                                wire:click="buangAgenda({{ $urutanHari }}, {{ $urutanAgenda }})"
                                                title="Hapus baris">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    @endforeach

                                    <button type="button" class="btn btn-sm btn-light border rounded-3 orcha-tombol mt-1"
                                        wire:click="tambahAgenda({{ $urutanHari }})">
                                        <i class="bi bi-plus-lg"></i>
                                        <span>Tambah kegiatan</span>
                                    </button>
                                </div>
                            @endforeach

                            <button type="button" class="btn btn-outline-primary rounded-3 orcha-tombol w-100"
                                wire:click="tambahHari">
                                <i class="bi bi-calendar-plus"></i>
                                <span>Tambah Hari</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Harga</h6>

                            <label class="form-label small fw-semibold">Harga jual <span class="text-danger">*</span></label>
                            <input type="number" class="form-control mb-3 @error('harga') is-invalid @enderror"
                                wire:model="harga" value="{{ $harga }}" min="0">
                            @error('harga') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                            <label class="form-label small fw-semibold">Harga sebelum diskon</label>
                            <input type="number" class="form-control mb-1" wire:model="hargaAsli" value="{{ $hargaAsli }}" min="0">
                            <div class="form-text mb-3">Kosongkan bila tidak ada coretan harga.</div>

                            <label class="form-label small fw-semibold">Diskon (%)</label>
                            <input type="number" class="form-control mb-3" wire:model="diskonPersen" value="{{ $diskonPersen }}" min="0" max="100">

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
