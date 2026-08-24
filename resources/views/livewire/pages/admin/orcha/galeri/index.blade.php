@section('title')
Galeri Perjalanan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        {{-- ============ JUDUL ============ --}}
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                    <div>
                        <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">Galeri Perjalanan</h1>
                        <p class="text-muted mb-0" style="font-size:.86rem">
                            Foto yang tampil di bagian <strong>“Momen perjalanan pelanggan kami”</strong>
                            pada beranda Orcha.
                        </p>
                    </div>

                    <span class="orcha-ganti-baru">
                        {{ $jumlahTampil }} dari {{ count($daftar) }} foto tampil
                    </span>
                </div>
            </div>
        </div>

        {{-- ============ UNGGAH ============
             Ditaruh paling atas, bukan di balik tombol "Tambah".

             Yang dilakukan admin di halaman ini sembilan dari sepuluh kali
             adalah menambah foto sepulang trip. Menyembunyikannya di balik satu
             ketukan lagi berarti menambah langkah pada pekerjaan yang paling
             sering dikerjakan. --}}
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-3 p-lg-4">
                <h2 class="fw-bold mb-1 orcha-judul-ikon" style="font-size:1.05rem">
                    <i class="bi bi-cloud-arrow-up text-primary"></i> Tambah foto
                </h2>
                <p class="text-muted mb-3" style="font-size:.82rem">
                    Bisa pilih <strong>banyak foto sekaligus</strong> — tahan Ctrl (atau ⌘ di Mac)
                    saat memilih. Format JPG atau PNG, maksimal 4 MB per foto.
                </p>

                <div class="orcha-surat-ttd">
                    <i class="bi bi-images"></i>

                    <div class="flex-grow-1">
                        <input type="file" class="form-control" wire:model="fotoBaru" multiple
                            accept="image/*">

                        @error('fotoBaru')
                            <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                        @enderror
                        @error('fotoBaru.*')
                            <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($fotoBaru)
                        <button type="button" class="orcha-btn orcha-btn-utama" wire:click="unggah"
                            wire:loading.attr="disabled" wire:target="unggah">
                            <i class="bi bi-upload"></i>
                            Unggah {{ count($fotoBaru) }} foto
                        </button>
                    @endif
                </div>

                <div wire:loading wire:target="fotoBaru,unggah" class="text-muted mt-2" style="font-size:.8rem">
                    <i class="bi bi-arrow-repeat"></i> Sedang memproses foto…
                </div>

                {{-- Pratayang sebelum dikirim: admin yang salah pilih berkas tahu
                     sebelum menunggu unggahannya selesai, bukan sesudah. --}}
                @if ($fotoBaru)
                    <div class="orcha-galeri-petak mt-3">
                        @foreach ($fotoBaru as $satu)
                            {{-- isPreviewable() dulu, baru temporaryUrl().

                                 Berkas yang bukan gambar membuat temporaryUrl()
                                 melempar galat, dan yang dilihat admin bukan pesan
                                 "harus berupa gambar" melainkan halaman error —
                                 padahal kesalahannya sepele dan pesannya sudah
                                 disiapkan tepat di atas. --}}
                            @if ($satu->isPreviewable())
                                <div class="orcha-galeri-kotak orcha-galeri-pratayang">
                                    <img src="{{ $satu->temporaryUrl() }}" alt="">
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ DAFTAR FOTO ============ --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                        <i class="bi bi-grid-3x3-gap text-primary"></i> Foto di galeri
                    </h2>
                    <span class="text-muted small">
                        Nomor kecil menentukan urutan tampil — makin kecil, makin depan
                    </span>
                </div>

                @forelse ($daftar as $foto)
                    @if ($sedangDiubah === (int) $foto['id'])
                        {{-- Formulir menggantikan kartunya di tempat, bukan membuka
                             jendela: yang disunting tetap terlihat di sebelah isian,
                             jadi admin tidak perlu mengingat foto mana yang tadi
                             diketuk. --}}
                        <div class="orcha-galeri-sunting mb-3">
                            <div class="orcha-galeri-kotak">
                                <img src="{{ $foto['foto'] }}" alt="">
                            </div>

                            <div class="flex-grow-1">
                                <div class="mb-2">
                                    <label class="orcha-label-kecil">Keterangan (boleh dikosongkan)</label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="keterangan" value="{{ $keterangan }}"
                                        placeholder="Misal: Rombongan SMA 1 di Kawah Ijen">
                                    @error('keterangan')
                                        <div class="text-danger mt-1" style="font-size:.76rem">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row g-2 align-items-end">
                                    <div class="col-6 col-md-4">
                                        <label class="orcha-label-kecil">Urutan tampil</label>
                                        <input type="number" min="0" class="form-control form-control-sm"
                                            wire:model="urutan" value="{{ $urutan }}">
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <label class="orcha-label-kecil d-block">Tampil di beranda</label>
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" wire:model="tampil">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4 d-flex gap-2 justify-content-md-end">
                                        <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                            wire:click="batal">Batal</button>
                                        <button type="button" class="orcha-btn orcha-btn-utama orcha-btn-kecil"
                                            wire:click="simpan">
                                            <i class="bi bi-check-lg"></i> Simpan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                @endforelse

                @if ($daftar !== [])
                    <div class="orcha-galeri-petak">
                        @foreach ($daftar as $foto)
                            <div class="orcha-galeri-kartu {{ ($foto['tampil'] ?? true) ? '' : 'orcha-galeri-sembunyi' }}">
                                <div class="orcha-galeri-kotak">
                                    <img src="{{ $foto['foto'] }}" alt="{{ $foto['keterangan'] ?? '' }}">

                                    <span class="orcha-galeri-nomor">{{ $foto['urutan'] ?? 0 }}</span>

                                    @unless ($foto['tampil'] ?? true)
                                        <span class="orcha-galeri-penanda">
                                            <i class="bi bi-eye-slash"></i> Disembunyikan
                                        </span>
                                    @endunless
                                </div>

                                <div class="orcha-galeri-isi">
                                    <div class="orcha-galeri-keterangan">
                                        {{ $foto['keterangan'] ?: 'Tanpa keterangan' }}
                                    </div>

                                    <div class="d-flex gap-1">
                                        <button type="button" class="orcha-hapus-baris orcha-tombol-ganti"
                                            wire:click="ubah({{ \Illuminate\Support\Js::from($foto) }})"
                                            title="Ubah keterangan dan urutannya">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button type="button" class="orcha-hapus-baris orcha-tombol-batal"
                                            wire:click="balikTampil({{ \Illuminate\Support\Js::from($foto) }})"
                                            title="{{ ($foto['tampil'] ?? true) ? 'Sembunyikan dari beranda' : 'Tampilkan lagi di beranda' }}">
                                            <i class="bi {{ ($foto['tampil'] ?? true) ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                        </button>

                                        <button type="button" class="orcha-hapus-baris"
                                            wire:click="hapus({{ $foto['id'] }})"
                                            wire:confirm="Hapus foto ini dari galeri? Berkasnya ikut terhapus."
                                            title="Hapus foto">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-images"></i></div>
                        <p class="text-muted mb-2">Belum ada foto di galeri.</p>
                        <p class="text-muted small mb-0">
                            Selama galeri masih kosong, beranda memakai foto destinasi sebagai penggantinya —
                            jadi bagian itu tidak pernah tampak kosong.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
