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

        <form wire:submit="simpan" class="orcha-form">
            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Keterangan Unit</h6>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Nama unit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror"
                                        wire:model="nama" value="{{ $nama }}" placeholder="All New Avanza">
                                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Merek <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('merek') is-invalid @enderror"
                                        wire:model="merek" value="{{ $merek }}" placeholder="Toyota">
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
                                    <input type="text" class="form-control" wire:model="nopol" value="{{ $nopol }}" placeholder="AB 1234 CD">
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Kapasitas (kursi) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('kapasitas') is-invalid @enderror"
                                        wire:model="kapasitas" value="{{ $kapasitas }}" min="1">
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
                                    <div class="orcha-rupiah">
                                        <input type="text" inputmode="numeric" class="orcha-uang form-control @error('tarifHari') is-invalid @enderror"
                                            wire:model.blur="tarifHariTeks" value="{{ $tarifHariTeks }}"
                                            placeholder="350.000">
                                    </div>
                                    @error('tarifHari') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Per jam</label>
                                    <div class="orcha-rupiah">
                                        <input type="text" inputmode="numeric" class="orcha-uang form-control"
                                            wire:model.blur="tarifJamTeks" value="{{ $tarifJamTeks }}"
                                            placeholder="55.000">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Paket 12 jam</label>
                                    <div class="orcha-rupiah">
                                        <input type="text" inputmode="numeric" class="orcha-uang form-control"
                                            wire:model.blur="tarif12JamTeks" value="{{ $tarif12JamTeks }}"
                                            placeholder="280.000">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold">Sopir / hari</label>
                                    <div class="orcha-rupiah">
                                        <input type="text" inputmode="numeric" class="orcha-uang form-control"
                                            wire:model.blur="tarifSopirTeks" value="{{ $tarifSopirTeks }}"
                                            placeholder="150.000">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="orcha-lengket">
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

                    {{-- Ringkasan unit: keadaannya sekarang, bukan isian.

                         Kolom ini sebelumnya hanya memuat kotak unggah foto dan dua
                         tombol, sementara kolom kiri memanjang — separuhnya kosong.
                         Yang pantas mengisinya adalah hal yang perlu diketahui admin
                         SEBELUM menyimpan: unitnya sedang di mana, sudah berapa kali
                         dipakai, dan apakah keadaannya cocok dengan status yang
                         sedang dipilih. --}}
                    @if ($ubah)
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                    <i class="bi bi-clipboard-data text-primary"></i> Ringkasan Unit
                                </h6>

                                @php $bermasalah = $this->bagianBermasalah(); @endphp

                                {{-- Peringatan yang paling penting di halaman ini: unit
                                     dengan bagian rusak masih ditawarkan di website.
                                     Sengaja TIDAK memblokir — kadang unit tetap layak jalan
                                     meski spionnya lecet, dan yang tahu itu pemiliknya,
                                     bukan sistem. --}}
                                @if ($tersedia && $bermasalah)
                                    <div class="alert alert-warning border-0 rounded-3 d-flex gap-2 align-items-start"
                                        style="font-size:.82rem">
                                        <i class="bi bi-exclamation-triangle-fill" style="line-height:1.5"></i>
                                        <span>
                                            Unit ditandai <strong>siap disewakan</strong> padahal
                                            {{ implode(', ', array_slice($bermasalah, 0, 3)) }}
                                            @if (count($bermasalah) > 3)
                                                dan {{ count($bermasalah) - 3 }} bagian lain
                                            @endif
                                            masih bermasalah. Pastikan memang layak jalan sebelum
                                            menyimpan.
                                        </span>
                                    </div>
                                @endif

                                <div class="orcha-ringkas {{ ($jadwal['sedang_disewa'] ?? false) ? 'sisa' : 'lunas' }}"
                                    style="height:auto">
                                    <div class="orcha-label-kecil orcha-ikon-teks">
                                        <i class="bi {{ ($jadwal['sedang_disewa'] ?? false) ? 'bi-arrow-up-right-circle' : 'bi-check-circle' }}"></i>
                                        Keadaan sekarang
                                    </div>
                                    <div class="angka" style="font-size:1rem">
                                        @if ($jadwal['sedang_disewa'] ?? false)
                                            Sedang disewa
                                        @elseif (! $tersedia)
                                            Tidak ditawarkan
                                        @else
                                            Siap disewakan
                                        @endif
                                    </div>
                                </div>

                                <div class="row g-3 mt-1">
                                    @foreach (array_filter([
                                        ['Sudah disewa', $jumlahPenyewaan . '×'],
                                        ($jadwal['kembali_pada'] ?? null)
                                            ? ['Dijadwalkan kembali', \Carbon\Carbon::parse($jadwal['kembali_pada'])->translatedFormat('j M, H:i')]
                                            : null,
                                        ($jadwal['mulai_berikutnya'] ?? null)
                                            ? ['Terpesan berikutnya', \Carbon\Carbon::parse($jadwal['mulai_berikutnya'])->translatedFormat('j M, H:i')]
                                            : null,
                                        ($kondisi['diperiksa_pada'] ?? null)
                                            ? ['Diperiksa terakhir', \Carbon\Carbon::parse($kondisi['diperiksa_pada'])->translatedFormat('j M Y')]
                                            : ['Diperiksa terakhir', 'Belum pernah'],
                                    ]) as [$label, $nilai])
                                        <div class="col-6">
                                            <div class="orcha-label-kecil">{{ $label }}</div>
                                            <div class="fw-bold" style="font-size:.88rem">{{ $nilai }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                @if ($jadwal['kode_berjalan'] ?? $jadwal['kode_berikutnya'] ?? null)
                                    <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                        class="orcha-tautan-balik mt-3" style="font-size:.8rem">
                                        Lihat penyewaannya
                                        <span class="orcha-kode">{{ $jadwal['kode_berjalan'] ?? $jadwal['kode_berikutnya'] }}</span>
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="orcha-btn orcha-btn-utama" wire:loading.attr="disabled"
                            wire:target="simpan">
                            <i class="bi bi-save"></i>
                            <span wire:loading.remove wire:target="simpan">
                                {{ $ubah ? 'Simpan Perubahan' : 'Tambah Kendaraan' }}
                            </span>
                            <span wire:loading wire:target="simpan">Menyimpan ke Orcha…</span>
                        </button>
                        <a href="{{ route('admin.orcha.armada') }}" wire:navigate class="orcha-btn orcha-btn-lembut">
                            Batal
                        </a>
                    </div>
                    </div>
                </div>
            </div>

                {{-- Kondisi unit: dibaca DAN disunting.

                     Sebelumnya kondisi hanya bisa berubah saat penyewa
                     mengembalikan unitnya. Setelah pemilik membawa mobilnya ke
                     bengkel dan kacanya diganti, tidak ada tempat untuk
                     menyatakan unit itu sudah baik lagi — ia terus terbaca
                     "rusak" sampai ada penyewa berikutnya yang mengembalikannya.

                     Disimpan lewat tombolnya sendiri, bukan ikut tombol simpan
                     utama: yang ini mengubah keadaan fisik unit, yang itu
                     mengubah tarif dan keterangannya. Menggabungkan keduanya
                     berarti mengubah tarif sambil tanpa sengaja menyatakan
                     unitnya sudah diperbaiki. --}}
                @if ($ubah)
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3 orcha-judul-ikon">
                                <i class="bi bi-tools text-primary"></i> Kondisi Unit
                            </h6>

                            @if ($jadwal['sedang_disewa'] ?? false)
                                <div class="alert alert-info border-0 rounded-3 d-flex gap-2 align-items-start mb-3"
                                    style="font-size:.82rem">
                                    <i class="bi bi-arrow-up-right-circle" style="line-height:1.5"></i>
                                    <span>
                                        Unit ini <strong>sedang disewa</strong>
                                        @if ($jadwal['kode_berjalan'] ?? null)
                                            ({{ $jadwal['kode_berjalan'] }})
                                        @endif
                                        @if ($jadwal['kembali_pada'] ?? null)
                                            dan dijadwalkan kembali
                                            {{ \Carbon\Carbon::parse($jadwal['kembali_pada'])->translatedFormat('j M Y, H:i') }}.
                                        @endif
                                        Kondisi yang dicatat di sini akan tertimpa oleh pemeriksaan
                                        saat unitnya kembali.
                                    </span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                <span class="orcha-label-kecil mb-0">
                                    @if ($kondisi && $kondisi['diperiksa_pada'])
                                        Terakhir diperiksa
                                        {{ \Carbon\Carbon::parse($kondisi['diperiksa_pada'])->translatedFormat('j M Y') }}
                                    @else
                                        Belum pernah diperiksa
                                    @endif
                                </span>
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                    wire:click="semuaBaik">
                                    <i class="bi bi-check2-all"></i> Semua baik
                                </button>
                            </div>

                            {{-- Tiga kolom, bukan satu tumpukan. Dua belas bagian yang
                                 berderet ke bawah membuat kartunya jauh lebih tinggi
                                 daripada isi kolom di sebelahnya — dan separuh halaman
                                 jadi bidang kosong. --}}
                            <div class="orcha-kondisi-daftar">
                                @foreach ($daftarBagian as $kunci => $label)
                                    <div class="orcha-kondisi-baris">
                                        <span>{{ $label }}</span>
                                        <select class="form-select form-select-sm
                                            {{ in_array($kondisiIsian[$kunci] ?? 'baik', ['rusak', 'hilang']) ? 'awas' : '' }}"
                                            wire:model.live="kondisiIsian.{{ $kunci }}">
                                            @foreach ($daftarKondisi as $nilai => $teks)
                                                <option value="{{ $nilai }}">{{ $teks }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>

                            <label class="form-label small fw-semibold mt-3 mb-1">Catatan perbaikan</label>
                            <input type="text" class="form-control" wire:model="kondisiCatatan"
                                maxlength="500" placeholder="Mis. kaca diganti 17 Agu di bengkel Slamet.">
                            <div class="form-text">
                                Ditulis sekarang supaya enam bulan lagi masih ada yang bisa menjelaskan
                                kenapa unit ini pernah ditandai rusak lalu kembali baik.
                            </div>

                            <button type="button" class="orcha-btn orcha-btn-utama mt-3"
                                wire:click="simpanKondisi" wire:loading.attr="disabled"
                                wire:target="simpanKondisi">
                                <i class="bi bi-save"></i>
                                <span wire:loading.remove wire:target="simpanKondisi">Simpan Kondisi</span>
                                <span wire:loading wire:target="simpanKondisi">Menyimpan…</span>
                            </button>

                            <div class="text-muted mt-2" style="font-size:.76rem">
                                Menyimpan kondisi tidak menghapus catatan denda penyewaan sebelumnya —
                                denda melekat pada penyewaannya, bukan pada unitnya.
                            </div>
                        </div>
                    </div>
                @endif
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
    <style>
        .orcha-kondisi-daftar {
            display: grid;
            gap: .5rem 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(19rem, 1fr));
        }

        .orcha-kondisi-baris {
            display: grid;
            grid-template-columns: 1fr 9.5rem;
            align-items: center;
            gap: .6rem;
            font-size: .84rem;
        }

        /* Pilihan rusak/hilang diberi warna supaya baris bermasalah terlihat
           tanpa membaca seluruh daftar dua belas bagian. */
        .orcha-kondisi-baris select.awas {
            border-color: #f6c9cd;
            background-color: #fdecee;
            color: #9b2530;
            font-weight: 600;
        }
    </style>
</div>
