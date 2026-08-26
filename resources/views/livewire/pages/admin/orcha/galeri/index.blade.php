@section('title')
Galeri Perjalanan || lemon
@stop

@php
    /* Foto disimpan di Orcha, bukan di lemon.

       Yang dikirim API berupa jalur relatif "/storage/galeri/x.webp", dan
       memasangnya apa adanya membuat peramban mencarinya di alamat lemon —
       hasilnya kotak gambar rusak, persis yang terlihat waktu dicoba. Pola ini
       sudah dipakai halaman etalase; dipakai ulang supaya keduanya tidak
       lama-lama berbeda. */
    $asalGambar = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanGambar = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalGambar . $jalur)
        : null;
@endphp

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

                <div class="orcha-unggah-galeri">
                    {{-- Ikonnya berputar selama berkas diproses.

                         Unggahan foto besar memakan beberapa detik, dan selama itu
                         layar tidak berubah sama sekali. Yang dilakukan admin
                         berikutnya bisa ditebak: mengetuk lagi, lalu bertanya kenapa
                         fotonya masuk dua kali. --}}
                    <label class="orcha-jatuhkan" wire:loading.class="sibuk" wire:target="fotoBaru,unggah">
                        <input type="file" wire:model="fotoBaru" multiple accept="image/*" hidden>

                        <span class="orcha-jatuhkan-ikon"><i class="bi bi-images"></i></span>

                        <span class="orcha-jatuhkan-judul">
                            <span wire:loading.remove wire:target="fotoBaru,unggah">Pilih foto dari komputer</span>
                            <span wire:loading wire:target="fotoBaru">Membaca foto…</span>
                            <span wire:loading wire:target="unggah">Mengunggah ke Orcha…</span>
                        </span>

                        <span class="orcha-jatuhkan-catatan">
                            <span wire:loading.remove wire:target="fotoBaru,unggah">
                                JPG atau PNG, maksimal 4 MB per foto — boleh banyak sekaligus
                            </span>
                            <span wire:loading wire:target="fotoBaru,unggah">Mohon tunggu, jangan menutup halaman</span>
                        </span>
                    </label>

                    @error('fotoBaru')
                        <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                    @enderror
                    @error('fotoBaru.*')
                        <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                    @enderror

                    @if ($fotoBaru)
                        {{-- Tiap foto punya isian keterangannya sendiri.

                             Satu keterangan untuk seluruh unggahan benar selama
                             fotonya serombongan dari acara yang sama — dan salah
                             begitu admin memilih foto dari beberapa trip sekaligus.
                             Yang serombongan tetap terlayani: satu isian di bawah
                             menyamakan semuanya sekali tekan. --}}
                        <div class="orcha-galeri-petak mt-3">
                            @foreach ($fotoBaru as $urutan => $satu)
                                @if ($satu->isPreviewable())
                                    <div class="orcha-pratayang-kartu">
                                        <div class="orcha-galeri-kotak">
                                            <img src="{{ $satu->temporaryUrl() }}" alt="">
                                            <span class="orcha-galeri-nomor">{{ $urutan + 1 }}</span>
                                        </div>

                                        <div class="orcha-pratayang-isi">
                                            <input type="text" class="form-control"
                                                wire:model="keteranganPer.{{ $urutan }}"
                                                value="{{ $keteranganPer[$urutan] ?? '' }}"
                                                placeholder="Keterangan foto ini">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @error('keteranganPer.*')
                            <div class="text-danger mt-2" style="font-size:.78rem">{{ $message }}</div>
                        @enderror

                        <div class="orcha-baris-unggah mt-3">
                            <div class="orcha-samakan">
                                <input type="text" class="form-control form-control-sm"
                                    wire:model="keteranganBaru" value="{{ $keteranganBaru }}"
                                    placeholder="Keterangan yang sama untuk semua foto">
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                    wire:click="samakanKeterangan" title="Isikan ke seluruh foto di atas">
                                    <i class="bi bi-arrow-down-up"></i> Terapkan ke semua
                                </button>
                            </div>

                            <label class="orcha-saklar">
                                <input type="checkbox" wire:model="tampilBaru">
                                <span class="alur"></span>
                                <span class="tulisan">Langsung tampil di beranda</span>
                            </label>

                            <button type="button" class="orcha-btn orcha-btn-utama"
                                wire:click="unggah" wire:loading.attr="disabled" wire:target="unggah">
                                <i class="bi bi-upload"></i>
                                Unggah {{ count($fotoBaru) }} foto
                            </button>
                        </div>

                        <p class="text-muted mb-0 mt-2" style="font-size:.76rem">
                            Urutan tampilnya diberikan otomatis di belakang foto yang sudah ada —
                            bisa diubah nanti lewat tombol pensil.
                        </p>
                    @endif
                </div>
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
                                <img src="{{ $tautanGambar($foto['foto']) }}" alt="">
                                <span class="orcha-galeri-nomor">{{ $foto['urutan'] ?? 0 }}</span>
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-bold mb-2 orcha-judul-ikon" style="font-size:.92rem">
                                    <i class="bi bi-pencil-square text-primary"></i> Ubah foto ini
                                </div>

                                {{-- Susunannya menyalin baris unggah di atas: isian,
                                     saklar, dan tombol sama tinggi dan sesudut, jadi
                                     admin membaca dua tempat dengan satu kebiasaan. --}}
                                <div class="row g-2">
                                    <div class="col-12 col-lg-8">
                                        <label class="orcha-label-kecil">Keterangan (boleh dikosongkan)</label>
                                        <input type="text" class="form-control"
                                            wire:model="keterangan" value="{{ $keterangan }}"
                                            placeholder="Misal: Rombongan SMA 1 di Kawah Ijen">
                                        @error('keterangan')
                                            <div class="text-danger mt-1" style="font-size:.76rem">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="orcha-label-kecil">Urutan tampil</label>
                                        <input type="number" min="0" class="form-control"
                                            wire:model="urutan" value="{{ $urutan }}">
                                        @error('urutan')
                                            <div class="text-danger mt-1" style="font-size:.76rem">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Tiga kolom sama lebar, bukan flex yang saling mendorong.

                                     Sebelumnya saklar didorong ke kiri, Batal ke kanan, dan
                                     Simpan ke kanan lagi — tiga benda dengan jurang di
                                     antaranya, dan lebar jurangnya berubah-ubah mengikuti
                                     lebar layar. Dibagi rata, ketiganya punya tempat tetap. --}}
                                <div class="orcha-baris-aksi mt-3">
                                    <label class="orcha-saklar">
                                        <input type="checkbox" wire:model="tampil">
                                        <span class="alur"></span>
                                        <span class="tulisan">Tampil di beranda</span>
                                    </label>

                                    <button type="button" class="orcha-btn orcha-btn-lembut"
                                        wire:click="batal">
                                        <i class="bi bi-x-lg"></i> Batal
                                    </button>

                                    <button type="button" class="orcha-btn orcha-btn-utama"
                                        wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan">
                                        <i class="bi bi-check-lg"></i> Simpan
                                    </button>
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
                                    <img src="{{ $tautanGambar($foto['foto']) }}" alt="{{ $foto['keterangan'] ?? '' }}">

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

                                        {{-- Konfirmasinya lewat SweetAlert, bukan wire:confirm.

                                             Dialog bawaan peramban menampilkan "127.0.0.1:8001
                                             says" di atas kalimatnya — terbaca seperti peringatan
                                             sistem yang bocor, bukan bagian dari aplikasi. Pola
                                             .pcek-konfirmasi sudah dipakai halaman Orcha lain;
                                             dipakai ulang supaya admin tidak melihat dua bentuk
                                             konfirmasi untuk tindakan yang sama berbahayanya. --}}
                                        <button type="button" class="orcha-hapus-baris pcek-konfirmasi"
                                            data-action="hapus" data-arg="{{ $foto['id'] }}"
                                            data-title="Hapus foto ini?"
                                            data-text="{{ addslashes($foto['keterangan'] ?: 'Foto tanpa keterangan') }} akan dihapus dari galeri, dan berkasnya ikut terhapus dari server."
                                            data-confirm="Ya, hapus" data-icon="warning"
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
