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
            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-3">Keterangan Unit</h6>

                            <div class="row g-3">
                                {{-- Merek dan nama unit dipilih lewat popup pencarian.

                                     Mengikuti pola picker yang sudah dipakai di Phoenix
                                     (lihat picker nama add-on di product-form): tombol yang
                                     bergaya seperti select, popup SweetAlert berisi kotak
                                     cari, daftar yang tersaring, dan pilihan "tulis sendiri"
                                     untuk yang belum ada di katalog.

                                     Dipilih daripada <select> biasa karena katalognya kini
                                     179 model — menggulung daftar sepanjang itu di dropdown
                                     bawaan lebih lambat daripada mengetik tiga huruf.

                                     Merek diletakkan lebih dahulu: daftar modelnya diambil
                                     dari mereknya. --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Merek <span class="text-danger">*</span></label>
                                    <button type="button" onclick="orchaPilihMerek(this)"
                                        class="form-select text-start orcha-picker @error('merek') is-invalid @enderror">
                                        @if (trim($merek) !== '')
                                            <span class="text-dark fw-semibold">{{ $merek }}</span>
                                        @else
                                            <span class="text-muted">— Pilih merek —</span>
                                        @endif
                                    </button>
                                    @error('merek') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-semibold">Nama unit <span class="text-danger">*</span></label>
                                    <span data-orcha-merek="{{ $merek }}" class="d-none"></span>
                                    <button type="button" onclick="orchaPilihUnit(this)"
                                        class="form-select text-start orcha-picker @error('nama') is-invalid @enderror"
                                        @disabled(trim($merek) === '')>
                                        @if (trim($nama) !== '')
                                            <span class="text-dark fw-semibold">{{ $nama }}</span>
                                        @else
                                            <span class="text-muted">
                                                {{ trim($merek) === '' ? '— Pilih merek dahulu —' : '— Pilih nama unit —' }}
                                            </span>
                                        @endif
                                    </button>
                                    @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                {{-- Tipe, tahun, dan cc disimpan sebagai isian tersendiri,
                                     bukan dijejalkan ke dalam nama unit menjadi "Agya tipe E
                                     tahun 2025 1200cc". Nama seperti itu tidak bisa disaring
                                     dan tidak bisa diurutkan menurut tahun; sebutan lengkapnya
                                     dirakit Orcha saat ditampilkan. --}}
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tipe</label>
                                    <button type="button" onclick="orchaPilihVarian(this)"
                                        class="form-select text-start orcha-picker"
                                        @disabled(trim($nama) === '')>
                                        @if (trim($varian) !== '')
                                            <span class="text-dark fw-semibold">{{ $varian }}</span>
                                        @else
                                            <span class="text-muted">
                                                {{ trim($nama) === '' ? '— pilih unit dahulu —' : '— pilih atau tulis tipe —' }}
                                            </span>
                                        @endif
                                    </button>
                                    @error('varian') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Tahun</label>
                                    <input type="number" class="form-control @error('tahun') is-invalid @enderror"
                                        wire:model.blur="tahun" min="1980" max="{{ date('Y') + 1 }}"
                                        placeholder="{{ date('Y') }}">
                                    @error('tahun')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        {{-- Tahun tidak bisa disimpulkan dari modelnya, jadi
                                             satu-satunya jalan memang diketik. --}}
                                        <div class="orcha-kursi-beda mt-1">
                                            <i class="bi bi-pencil"></i><span>Diisi manual.</span>
                                        </div>
                                    @enderror
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Isi silinder (cc)</label>
                                    <input type="number" class="form-control @error('cc') is-invalid @enderror"
                                        wire:model.live="cc" min="500" max="20000" placeholder="1200">
                                    @error('cc')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        @if ($ccOtomatisDari !== '')
                                            <div class="orcha-kursi-otomatis mt-1">
                                                <i class="bi bi-magic"></i>
                                                <span>Terisi dari {{ $ccOtomatisDari }}.</span>
                                            </div>
                                        @elseif ($ccDiubahManual && $ccDisarankan !== null && (int) $cc !== $ccDisarankan)
                                            <div class="orcha-kursi-beda mt-1">
                                                <i class="bi bi-info-circle"></i>
                                                <span>Umumnya {{ number_format($ccDisarankan, 0, ',', '.') }} cc.</span>
                                            </div>
                                        @endif
                                    @enderror
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Jenis <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model.live="jenis">
                                        @foreach ($pilihanJenis as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Nomor polisi</label>
                                    <input type="text" class="form-control" wire:model="nopol" value="{{ $nopol }}" placeholder="AB 1234 CD">
                                </div>

                                {{-- Kapasitas: terisi sendiri saat nama unit dipilih, tetap bisa diubah.

                                     Semi otomatis dan bukan otomatis, karena unit yang sama
                                     bisa dipasangi kursi berbeda — Gran Max niaga dua kursi
                                     sedangkan minibusnya delapan, dan HiAce yang kursinya
                                     dicabut untuk barang tidak lagi lima belas. Angkanya
                                     saran; yang menentukan tetap admin.

                                     wire:model.live dipakai supaya koreksi admin langsung
                                     tercatat sebagai koreksi — dengan .blur, mengganti nama
                                     unit sebelum berpindah fokus akan menimpa angka yang
                                     baru saja diperbaiki. --}}
                                <div class="col-6 col-md-4">
                                    <label class="form-label small fw-semibold">Kapasitas (kursi) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('kapasitas') is-invalid @enderror"
                                        wire:model.live="kapasitas" min="1">

                                    @error('kapasitas')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @else
                                        @if ($kursiOtomatisDari !== '')
                                            <div class="orcha-kursi-otomatis mt-1">
                                                <i class="bi bi-magic"></i>
                                                <span>
                                                    {{-- Kursi penumpang, bukan kursi total: kalau asal angkanya
                                                         tidak disebut, admin melihat 14 untuk unit yang
                                                         spesifikasinya 15 dan mengira ada yang salah. --}}
                                                    Kursi penumpang, terisi dari {{ $kursiOtomatisDari }}
                                                    @unless ($lepasKunci)
                                                        ({{ $kursiTotal }} kursi, satu untuk sopir)
                                                    @endunless
                                                    — ubah bila unit ini berbeda.
                                                </span>
                                            </div>
                                        @elseif ($kapasitasDiubahManual && $kursiDisarankan !== null && $kursiDisarankan !== $kapasitas)
                                            <div class="orcha-kursi-beda mt-1">
                                                <i class="bi bi-info-circle"></i>
                                                <span>Umumnya {{ $kursiDisarankan }} kursi.</span>
                                            </div>
                                        @endif
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Transmisi tersedia <span class="text-danger">*</span></label>

                                    {{-- Kotak centang kecil diganti kartu yang bisa ditekan.
                                         Sasaran kliknya jadi seluruh kartu, bukan kotak 16px —
                                         dan pilihan yang sedang aktif terbaca sekilas, tanpa
                                         perlu memicingkan mata ke tanda centangnya. --}}
                                    <div class="orcha-pilih-kartu" wire:key="transmisi">
                                        @foreach ([
                                            ['Manual', 'bi-gear-wide-connected', 'Tuas persneling'],
                                            ['Matic', 'bi-lightning-charge-fill', 'Tanpa kopling'],
                                        ] as [$pilihan, $ikon, $keterangan])
                                            <label class="orcha-kartu-pilihan {{ in_array($pilihan, $transmisi) ? 'aktif' : '' }}">
                                                <input type="checkbox" value="{{ $pilihan }}" wire:model.live="transmisi">
                                                <span class="tanda"><i class="bi bi-check-lg"></i></span>
                                                <i class="bi {{ $ikon }} rupa"></i>
                                                <span>
                                                    <span class="judul">{{ $pilihan }}</span>
                                                    <span class="ket">{{ $keterangan }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @error('transmisi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        Pilih keduanya bila unit ini tersedia dalam dua transmisi — daftar di
                                        website akan menulis "Manual &amp; Matic".
                                    </div>
                                </div>

                                {{-- Sakelar kecil diganti kartu yang warnanya ikut berubah.
                                     Ini menentukan unitnya tampil di website atau tidak —
                                     akibat yang terlalu besar untuk disampaikan oleh sakelar
                                     16px dengan satu baris teks abu-abu. --}}
                                <div class="col-12">
                                    {{-- Lepas kunci: menentukan dua hal sekaligus.

                                         Pertama, boleh tidaknya unit disewa tanpa sopir —
                                         HiAce dan bus tidak dilepas, dan sampai sekarang
                                         aturan itu hanya ada di kepala pemilik.

                                         Kedua, hitungan kursi penumpang. Unit yang selalu
                                         dengan sopir kehilangan satu kursi untuk sopirnya:
                                         HiAce 15 kursi berarti 14 penumpang. Angkanya
                                         ditampilkan di sini supaya akibatnya terlihat SEBELUM
                                         disimpan, bukan setelah ada rombongan yang dijanjikan
                                         muat. --}}
                                    <label class="orcha-sakelar-kartu {{ $lepasKunci ? 'nyala' : 'peringatan' }} mb-3">
                                        <span class="rupa">
                                            <i class="bi {{ $lepasKunci ? 'bi-key' : 'bi-person-badge' }}"></i>
                                        </span>
                                        <span class="isi">
                                            <span class="judul">
                                                {{ $lepasKunci ? 'Boleh lepas kunci' : 'Selalu dengan sopir' }}
                                            </span>
                                            <span class="ket">
                                                @if ($lepasKunci)
                                                    Penyewa menyetir sendiri, jadi
                                                    <strong>{{ $kapasitas }} kursi</strong> terpakai penumpang semua.
                                                @else
                                                    Satu kursi untuk sopir kami, dan kapasitas di atas
                                                    sudah dikurangi: <strong>{{ $kapasitas }} penumpang</strong>
                                                    dari {{ $kursiTotal }} kursi.
                                                @endif
                                            </span>
                                        </span>
                                        <span class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                wire:model.live="lepasKunci">
                                        </span>
                                    </label>

                                    <label class="orcha-sakelar-kartu {{ $tersedia ? 'nyala' : '' }}">
                                        <span class="rupa">
                                            <i class="bi {{ $tersedia ? 'bi-globe-americas' : 'bi-eye-slash' }}"></i>
                                        </span>
                                        <span class="isi">
                                            <span class="judul">
                                                {{ $tersedia ? 'Ditawarkan di website' : 'Disembunyikan dari website' }}
                                            </span>
                                            <span class="ket">
                                                {{ $tersedia
                                                    ? 'Pelanggan bisa melihat dan memesan unit ini.'
                                                    : 'Unit tetap tersimpan, tapi tidak muncul di daftar sewa.' }}
                                            </span>
                                        </span>
                                        <span class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch"
                                                wire:model.live="tersedia">
                                        </span>
                                    </label>
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
                    <div class="orcha-lengket orcha-lengket-armada">
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
            /* Kartu pilihan (transmisi): sasaran kliknya seluruh kartu, dan yang
           terpilih ditandai warna SEKALIGUS tanda centang — warna saja tidak
           cukup bagi yang sulit membedakannya. */
        .orcha-pilih-kartu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: .6rem;
        }

        .orcha-kartu-pilihan {
            position: relative;
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .75rem .9rem;
            border: 1.5px solid #dbe7f0;
            border-radius: .8rem;
            background: #fff;
            cursor: pointer;
            transition: all .15s ease;
        }

        .orcha-kartu-pilihan input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .orcha-kartu-pilihan:hover { border-color: #b9d0e2; }

        .orcha-kartu-pilihan.aktif {
            border-color: #1d6fa5;
            background: #f2f8fc;
        }

        .orcha-kartu-pilihan .rupa {
            font-size: 1.15rem;
            line-height: 1;
            color: #94a3b8;
        }

        .orcha-kartu-pilihan.aktif .rupa { color: #1d6fa5; }

        .orcha-kartu-pilihan .judul {
            display: block;
            font-weight: 700;
            font-size: .9rem;
            color: #0f2d4a;
        }

        .orcha-kartu-pilihan .ket {
            display: block;
            font-size: .76rem;
            color: #94a3b8;
        }

        .orcha-kartu-pilihan .tanda {
            position: absolute;
            top: .5rem;
            right: .6rem;
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            background: #1d6fa5;
            color: #fff;
            font-size: .62rem;
            opacity: 0;
            transform: scale(.6);
            transition: all .15s ease;
        }

        /* Cakramnya sudah memakai flex, tapi yang ditengahkan adalah KOTAK BARIS
           ikonnya, bukan glifnya: kotak itu setinggi line-height halaman dan
           bootstrap-icons masih menggeser glifnya turun (vertical-align: sub).
           Lebar kotaknya pun lebih besar daripada lebar glifnya, sehingga
           centangnya duduk di kiri-bawah dan menyisakan tempat kosong di
           kanan-atas — terukur 3px ke kiri dan 1,4px ke bawah pada cakram 18px.

           Ikonnya sendiri dijadikan wadah flex, mengikuti pola yang sudah
           dipakai .stat-icon-wrapper: kotaknya menyusut sepas glifnya dan
           vertical-align jadi tidak berlaku lagi. */
        .orcha-kartu-pilihan .tanda i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            vertical-align: middle;
        }

        .orcha-kartu-pilihan.aktif .tanda { opacity: 1; transform: scale(1); }

        /* Sakelar penayangan: kartunya sendiri ikut berubah warna, karena yang
           diputuskan di sini adalah unitnya terlihat pelanggan atau tidak. */
        .orcha-sakelar-kartu {
            display: flex;
            align-items: center;
            gap: .85rem;
            width: 100%;
            margin: 0;
            padding: .85rem 1rem;
            border: 1.5px solid #dbe7f0;
            border-radius: .8rem;
            background: #f8fafc;
            cursor: pointer;
            transition: all .15s ease;
        }

        .orcha-sakelar-kartu.nyala {
            border-color: #1a8a52;
            background: #f0faf5;
        }

        .orcha-sakelar-kartu .rupa {
            flex: 0 0 2.4rem;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: .7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8edf2;
            color: #64748b;
            font-size: 1.05rem;
        }

        .orcha-sakelar-kartu .rupa > i { line-height: 1; }

        .orcha-sakelar-kartu.nyala .rupa { background: #d7f0e2; color: #126b40; }

        .orcha-sakelar-kartu .isi { flex: 1 1 auto; }

        .orcha-sakelar-kartu .judul {
            display: block;
            font-weight: 700;
            font-size: .9rem;
            color: #0f2d4a;
        }

        .orcha-sakelar-kartu .ket {
            display: block;
            font-size: .78rem;
            color: #64748b;
        }

        .orcha-sakelar-kartu .form-check-input {
            width: 2.4rem;
            height: 1.3rem;
            cursor: pointer;
        }

        .orcha-sakelar-kartu.nyala .form-check-input:checked {
            background-color: #1a8a52;
            border-color: #1a8a52;
        }
        /* Kolom kanan yang lengket diberi jarak bawah sendiri.

           Tanpa ini tombol Batal berhenti persis menempel pada kartu Kondisi
           Unit saat halaman digulung — keduanya terbaca seperti satu tumpukan,
           dan tombol yang membatalkan pekerjaan tidak pantas tampak menyatu
           dengan isian di bawahnya.

           max-height menjaga kolom ini tetap muat di layar: bila isinya lebih
           tinggi dari jendela, bagian bawahnya digulung sendiri alih-alih
           menyeruduk keluar. */
        @media (min-width: 1200px) {
            .orcha-lengket-armada {
                padding-bottom: 2rem;
                max-height: calc(100vh - 2rem);
                overflow-y: auto;
                overscroll-behavior: contain;
            }
        }
</style>

{{-- ============ PICKER MEREK & NAMA UNIT ============
     Pola diambil dari picker nama add-on di Phoenix (product-form): data
     disegarkan tiap render DI LUAR guard, sedangkan pemasangan fungsinya
     dijaga sekali saja. Kalau datanya ikut di dalam guard, katalog yang
     berubah tidak akan pernah terbaca setelah render pertama. --}}
<script>
    window.__orchaKatalog = @json($katalog);
    window.__orchaKustom = @json($katalogKustom);
    window.__orchaVarian = @json($varianPilihan);

    if (!window.__orchaPickerTerpasang) {
        window.__orchaPickerTerpasang = true;

        const orchaEsc = (t) => String(t).replace(/[&<>"']/g, (m) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[m]));

        // Id entri kustom, dicari dengan kunci "merek" atau "merek|model".
        // Hanya entri inilah yang boleh punya tombol hapus: katalog bawaan ikut
        // versi kode, dan merek yang terbaca dari armada dipakai unit nyata.
        const orchaIdKustom = (merek, model) => {
            const cocok = (window.__orchaKustom || []).find((e) => e.merek === merek
                && ((model == null && e.model == null) || e.model === model));
            return cocok ? cocok.id : null;
        };

        const orchaBaris = (opsi) => opsi.daftar.length
            ? opsi.daftar.map((n) => {
                const id = opsi.tanpaHapus ? null
                    : (opsi.untukUnit ? orchaIdKustom(opsi.merek, n) : orchaIdKustom(n, null));
                return '<div class="orcha-pick-row">'
                    + '<button type="button" class="orcha-pick-item" data-nilai="' + orchaEsc(n)
                    + '" data-cari="' + orchaEsc(String(n).toLowerCase()) + '">'
                    + '<i class="bi ' + opsi.ikon + ' me-2" style="color:#1d6fa5;"><\/i>' + orchaEsc(n)
                    + '<\/button>'
                    + (id ? '<button type="button" class="orcha-pick-del" data-id="' + id
                        + '" title="Hapus dari daftar"><i class="bi bi-trash3"><\/i><\/button>' : '')
                    + '<\/div>';
            }).join('')
            : '<div class="orcha-pick-empty">' + orchaEsc(opsi.kosong) + '<\/div>';

        window.__orchaPicker = function (opsi) {
            if (typeof Swal === 'undefined') return;

            const wadah = opsi.tombol.closest('[wire\\:id]');
            if (!wadah) return;

            const cid = wadah.getAttribute('wire:id');
            const komponen = () => window.Livewire && window.Livewire.find(cid);
            const setNilai = (nilai) => komponen() && komponen().set(opsi.properti, nilai);

            // Dicatat supaya daftarnya bisa digambar ulang di tempat sesudah ada
            // entri ditambah atau dihapus — tanpa menutup popupnya.
            window.__orchaPickerAktif = opsi;

            const pasangPendengar = () => {
                const daftarEl = document.getElementById('orchaPickDaftar');
                if (!daftarEl) return;

                daftarEl.querySelectorAll('.orcha-pick-item').forEach((b) => {
                    b.addEventListener('click', () => { setNilai(b.dataset.nilai); Swal.close(); });
                });

                daftarEl.querySelectorAll('.orcha-pick-del').forEach((b) => {
                    b.addEventListener('click', (ev) => {
                        // Jangan sampai menghapus berarti sekaligus memilih.
                        ev.stopPropagation();
                        b.disabled = true;
                        komponen() && komponen().call('hapusKatalog', Number(b.dataset.id));
                    });
                });
            };

            const gambarUlang = () => {
                const daftarEl = document.getElementById('orchaPickDaftar');
                if (!daftarEl) return;
                opsi.daftar = opsi.tanpaDaftar
                    ? (window.__orchaVarian || [])
                    : (opsi.untukUnit
                        ? ((window.__orchaKatalog || {})[opsi.merek] || [])
                        : Object.keys(window.__orchaKatalog || {}));
                daftarEl.innerHTML = orchaBaris(opsi);
                pasangPendengar();
                const cari = document.getElementById('orchaPickCari');
                if (cari) cari.dispatchEvent(new Event('input'));
            };
            window.__orchaGambarUlang = gambarUlang;

            Swal.fire({
                title: opsi.judul,
                html: '<input id="orchaPickCari" class="form-control mb-2" placeholder="' + orchaEsc(opsi.petunjuk) + '">'
                    + '<div id="orchaPickDaftar" class="orcha-pick-list">' + orchaBaris(opsi) + '<\/div>'
                    + '<div id="orchaPickKosong" class="orcha-pick-empty" style="display:none">Tidak ada yang cocok. Pakai "Tulis sendiri" di bawah.<\/div>'
                    + '<button type="button" id="orchaPickManual" class="orcha-pick-item mt-2" style="border-style:dashed;">'
                    + '<i class="bi bi-plus-circle me-2" style="color:#64748b;"><\/i>Tulis sendiri &amp; tambahkan ke daftar…<\/button>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(29, 111, 165, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false, showConfirmButton: false, showCloseButton: true,
                width: 480, padding: '1.25rem',
                willClose: () => { window.__orchaPickerAktif = null; window.__orchaGambarUlang = null; },
                didOpen: () => {
                    const cari = document.getElementById('orchaPickCari');
                    const daftarEl = document.getElementById('orchaPickDaftar');
                    const kosong = document.getElementById('orchaPickKosong');

                    if (cari) {
                        cari.addEventListener('input', () => {
                            const q = cari.value.toLowerCase().trim();
                            let terlihat = 0;
                            daftarEl.querySelectorAll('.orcha-pick-row').forEach((baris) => {
                                const b = baris.querySelector('.orcha-pick-item');
                                const cocok = b.dataset.cari.includes(q);
                                baris.style.display = cocok ? '' : 'none';
                                if (cocok) terlihat++;
                            });
                            // Daftar kosong tanpa keterangan terbaca seperti halaman
                            // rusak, bukan seperti "tidak ada yang cocok".
                            kosong.style.display = terlihat === 0 && opsi.daftar.length ? '' : 'none';
                        });
                        setTimeout(() => cari.focus(), 100);
                    }

                    pasangPendengar();

                    const manual = document.getElementById('orchaPickManual');
                    if (manual) manual.addEventListener('click', () => {
                        Swal.fire({
                            title: opsi.judulManual,
                            input: 'text',
                            inputPlaceholder: opsi.contohManual,
                            background: 'rgba(255, 255, 255, 0.92)',
                            backdrop: 'rgba(29, 111, 165, 0.15)',
                            customClass: {
                                popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold',
                                confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel',
                            },
                            buttonsStyling: false, showCancelButton: true,
                            confirmButtonText: 'Tambahkan', cancelButtonText: 'Batal',
                            inputValidator: (v) => (v && v.trim() !== '') ? undefined : 'Masih kosong.',
                        }).then((h) => {
                            if (!h.isConfirmed || !h.value) return;
                            // Sekali ditulis langsung terdaftar, jadi unit sejenis
                            // berikutnya tinggal memilih.
                            if (opsi.tanpaDaftar) {
                                // Tipe bukan baris katalog tersendiri: ia ikut
                                // tersimpan pada unitnya, dan dari sana masuk
                                // daftar pilihan untuk unit sejenis berikutnya.
                                komponen() && komponen().set(opsi.properti, h.value.trim());

                                return;
                            }

                            komponen() && komponen().call('tambahKatalog', h.value.trim(), !!opsi.untukUnit);
                        });
                    });
                },
            });
        };

        // Daftar terbaru dari server: dipasang ke global, lalu popup yang sedang
        // terbuka digambar ulang di tempat.
        window.addEventListener('orcha-katalog-segar', function (e) {
            const d = e.detail || {};
            if (d.katalog) window.__orchaKatalog = d.katalog;
            if (d.kustom) window.__orchaKustom = d.kustom;
            if (window.__orchaGambarUlang) window.__orchaGambarUlang();
        });

        window.orchaPilihMerek = function (tombol) {
            window.__orchaPicker({
                tombol, properti: 'merek', ikon: 'bi-award', untukUnit: false,
                judul: 'Pilih Merek', petunjuk: 'Ketik untuk mencari merek…',
                daftar: Object.keys(window.__orchaKatalog || {}),
                kosong: 'Katalog belum termuat. Pakai "Tulis sendiri" di bawah.',
                judulManual: 'Tambah Merek', contohManual: 'mis. Esemka',
            });
        };

        // Tipe/varian: daftarnya melekat pada modelnya, dan boleh kosong —
        // banyak unit memang tidak punya tipe yang perlu disebut.
        window.orchaPilihVarian = function (tombol) {
            window.__orchaPicker({
                tombol, properti: 'varian', ikon: 'bi-tag', untukUnit: false, tanpaHapus: true,
                judul: 'Pilih Tipe',
                petunjuk: 'Ketik untuk mencari tipe…',
                daftar: window.__orchaVarian || [],
                kosong: 'Belum ada daftar tipe untuk unit ini. Pakai "Tulis sendiri" di bawah.',
                judulManual: 'Tulis Tipe', contohManual: 'mis. GR Sport',
                tanpaDaftar: true,
            });
        };

        window.orchaPilihUnit = function (tombol) {
            // Daftar model dibaca dari merek yang sedang terpilih, bukan dari
            // seluruh katalog: "Ertiga" tidak boleh muncul saat mereknya Toyota.
            const wadah = tombol.closest('[wire\\:id]');
            const merek = wadah ? (wadah.querySelector('[data-orcha-merek]')?.dataset.orchaMerek || '') : '';

            window.__orchaPicker({
                tombol, properti: 'nama', ikon: 'bi-truck-front', untukUnit: true, merek,
                judul: merek ? ('Pilih Unit ' + merek) : 'Pilih Nama Unit',
                petunjuk: 'Ketik untuk mencari unit…',
                daftar: (window.__orchaKatalog || {})[merek] || [],
                kosong: merek
                    ? ('Belum ada daftar unit untuk ' + merek + '. Pakai "Tulis sendiri" di bawah.')
                    : 'Pilih mereknya dahulu.',
                judulManual: merek ? ('Tambah Unit ' + merek) : 'Tambah Nama Unit',
                contohManual: 'mis. Bima 1.3',
            });
        };
    }
</script>

<style>
    /* Tombol yang menyamar sebagai select: tingginya mengikuti .form-select
       dari layout, jadi barisnya tetap rata dengan isian di sebelahnya. */
    .orcha-picker {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .orcha-picker:disabled {
        cursor: not-allowed;
        opacity: .65;
    }

    .orcha-pick-list {
        max-height: 340px;
        overflow-y: auto;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: .4rem;
        padding: .2rem;
    }

    /* Keterangan kecil di bawah isian kapasitas. Ikon dan tulisannya sejajar
       lewat flex, bukan ditambal geseran em. */
    .orcha-kursi-otomatis,
    .orcha-kursi-beda {
        display: flex;
        align-items: center;
        gap: .3rem;
        font-size: .74rem;
        line-height: 1.3;
    }

    .orcha-kursi-otomatis {
        color: #1a8a52;
    }

    .orcha-kursi-beda {
        color: #94a3b8;
    }

    .orcha-kursi-otomatis i,
    .orcha-kursi-beda i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        vertical-align: middle;
    }

    /* Kartu "selalu dengan sopir" bukan keadaan salah, jadi bukan merah —
       kuning kehijauan yang menandakan "perhatikan", bukan "perbaiki". */
    .orcha-sakelar-kartu.peringatan {
        border-color: #d9c48a;
        background: linear-gradient(135deg, rgba(217, 196, 138, .16), rgba(217, 196, 138, .05));
    }

    .orcha-sakelar-kartu.peringatan .rupa {
        background: #f6edd0;
        color: #8a6d1f;
    }

    .orcha-pick-row {
        display: flex;
        align-items: stretch;
        gap: .4rem;
    }

    .orcha-pick-row .orcha-pick-item {
        flex: 1 1 auto;
        min-width: 0;
    }

    /* Tombol hapus hanya muncul pada entri yang ditambahkan admin sendiri.
       Merahnya terlihat tanpa perlu disentuh dulu, karena yang dilakukannya
       memang membuang sesuatu. */
    .orcha-pick-del {
        flex: 0 0 auto;
        width: 40px;
        border: 1px solid #f3c9c9;
        background: #fff5f5;
        color: #c0392b;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: all .15s ease;
    }

    .orcha-pick-del:hover {
        background: #c0392b;
        border-color: #c0392b;
        color: #fff;
    }

    .orcha-pick-del:disabled {
        opacity: .5;
        cursor: wait;
    }

    .orcha-pick-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 1px solid #e6e8f2;
        background: #fff;
        border-radius: 12px;
        padding: .7rem .9rem;
        font-weight: 600;
        color: #1e293b;
        font-size: .92rem;
        transition: all .15s ease;
    }

    .orcha-pick-item:hover {
        border-color: #1d6fa5;
        background: linear-gradient(135deg, rgba(29, 111, 165, .10), rgba(15, 45, 74, .04));
        transform: translateY(-1px);
    }

    .orcha-pick-empty {
        text-align: center;
        color: #94a3b8;
        padding: 1.5rem;
        font-size: .9rem;
    }
</style>
</div>
