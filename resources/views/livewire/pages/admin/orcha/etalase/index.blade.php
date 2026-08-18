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

    // "12,4k" — sama dengan yang dipakai formulir dan kartu publik. Angka
    // pengunjung enam digit memaksa barisnya membungkus, dan baris meta yang
    // membungkus membuat tinggi kartu berbeda-beda.
    $ringkas = function ($angka) {
        $angka = (int) $angka;

        return $angka >= 1000
            ? rtrim(rtrim(number_format($angka / 1000, 1, ',', '.'), '0'), ',') . 'k'
            : (string) $angka;
    };
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
                        {{-- Destinasi punya halaman formulir sendiri: isiannya —
                             keterangan panjang, foto utama, dan tiga gambar tambahan
                             beserta pratinjaunya — terlalu banyak untuk jendela yang
                             isinya harus digulung sendiri. Testimoni dan partner
                             isiannya sedikit, jadi tetap lewat jendela. --}}
                        @if ($jenis === 'destinasi')
                            <a href="{{ route('admin.orcha.destinasi.tambah') }}" wire:navigate
                                class="btn btn-primary rounded-3 orcha-tombol orcha-tombol-tambah">
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambah {{ $judul }}</span>
                            </a>
                        @else
                            <button type="button" class="btn btn-primary rounded-3 orcha-tombol orcha-tombol-tambah" wire:click="tambah">
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambah {{ $judul }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($daftar as $baris)
                <div class="col-12 col-md-6 col-xl-4" wire:key="{{ $jenis }}-{{ $baris['id'] }}">
                    <div class="card orcha-kartu orcha-etalase h-100">
                        @php $foto = $tautanGambar($baris['foto'] ?? $baris['logo'] ?? null); @endphp

                        {{-- Kotak gambarnya SELALU ada, juga ketika fotonya belum
                             diunggah. Dulu gambarnya hanya dipasang bila ada,
                             sehingga kartu tanpa foto dimulai langsung dari
                             namanya — dan sebarisnya jadi tidak sejajar dengan
                             tetangganya. --}}
                        <div class="orcha-etalase-foto {{ $jenis === 'partner' ? 'logo' : '' }} {{ $foto ? '' : 'tanpa' }}">
                            @if ($foto)
                                <img src="{{ $foto }}" alt="{{ $baris['nama'] }}">
                            @else
                                <span class="kosong"><i class="bi bi-image"></i></span>
                            @endif

                            @if ($jenis === 'destinasi' && ($baris['wilayah_label'] ?? ''))
                                <span class="orcha-etalase-tanda">{{ $baris['wilayah_label'] }}</span>
                            @elseif ($jenis === 'testimoni')
                                <span class="orcha-etalase-tanda bintang">
                                    {{ str_repeat('★', (int) $baris['rating']) }}
                                </span>
                            @endif
                        </div>

                        <div class="card-body p-3 p-lg-4 d-flex flex-column">
                            <h6 class="fw-bold mb-1 orcha-etalase-nama">{{ $baris['nama'] }}</h6>

                            @if ($jenis === 'destinasi')
                                {{-- Alamat lengkapnya, bukan provinsinya saja. Daerah
                                     itu yang dicari pengunjung — "Karimunjawa", bukan
                                     "Jawa Tengah" — dan tanpa ditampilkan di sini admin
                                     tidak punya cara tahu destinasi mana yang daerahnya
                                     masih kosong tanpa membuka satu per satu. --}}
                                @php
                                    // Dirakit sendiri bila Orcha belum mengirim
                                    // alamat_singkat. Orcha dipasang terpisah dan boleh
                                    // tertinggal sekian rilis dari lemon; halaman yang
                                    // menganggap medan terbaru pasti ada akan galat di
                                    // server yang belum diperbarui — bukan menampilkan
                                    // lebih sedikit, melainkan tidak menampilkan
                                    // apa-apa.
                                    $alamat = trim((string) ($baris['alamat_singkat'] ?? '')) ?: collect([
                                        $baris['daerah'] ?? null,
                                        $baris['provinsi'] ?? null,
                                    ])->filter()->implode(', ');
                                @endphp

                                <p class="orcha-etalase-alamat mb-2">
                                    <i class="bi bi-geo-alt"></i>
                                    <span>{{ $alamat ?: 'Lokasi belum diisi' }}</span>
                                </p>
                            @endif

                            @php
                                $keterangan = $jenis === 'testimoni'
                                    ? trim((string) ($baris['isi'] ?? ''))
                                    : trim((string) ($baris['deskripsi'] ?? ''));
                            @endphp

                            {{-- Dipotong dengan CSS, bukan Str::limit: batas huruf
                                 menghasilkan satu sampai tiga baris tergantung
                                 kalimatnya, dan tinggi yang berbeda-beda itulah yang
                                 membuat sebaris kartu terlihat berantakan. --}}
                            <p class="orcha-etalase-ket mb-3 {{ $keterangan === '' ? 'kosong' : '' }}">
                                {{ $keterangan !== '' ? $keterangan : 'Keterangan belum ditulis.' }}
                            </p>

                            @if ($jenis === 'destinasi')
                                @php
                                    $jumlahSub = count($baris['sub_foto'] ?? []);
                                    $batasSub = $baris['batas_sub_foto'] ?? 3;
                                @endphp

                                <div class="orcha-etalase-meta mb-3">
                                    <span>
                                        <i class="bi bi-people-fill"></i>
                                        {{ $ringkas($baris['total_pengunjung'] ?? 0) }} pengunjung
                                    </span>
                                    <span class="{{ $jumlahSub === 0 ? 'sepi' : '' }}">
                                        <i class="bi bi-images"></i>
                                        {{ $jumlahSub }}/{{ $batasSub }} gambar
                                    </span>
                                </div>
                            @endif

                            {{-- mt-auto: tombolnya rata di dasar semua kartu sebaris,
                                 berapa pun panjang keterangannya. Tanpa itu tombolnya
                                 mengambang di tengah kartu yang isinya pendek. --}}
                            <div class="orcha-etalase-aksi mt-auto">
                                @if ($jenis === 'destinasi')
                                    <a href="{{ route('admin.orcha.destinasi.ubah', $baris['id']) }}" wire:navigate
                                        class="orcha-etalase-ubah">
                                        <i class="bi bi-pencil"></i>
                                        <span>Ubah</span>
                                    </a>
                                @else
                                    <button type="button" class="orcha-etalase-ubah"
                                        wire:click='ubah(@json($baris))'>
                                        <i class="bi bi-pencil"></i>
                                        <span>Ubah</span>
                                    </button>
                                @endif

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
                                {{ $jenis === 'partner' ? 'Logo' : ($jenis === 'testimoni' ? 'Foto orangnya' : 'Foto utama') }}
                            </label>

                            {{-- Pratinjau dan pemilih berkas dijadikan satu kotak.
                                 Sebelumnya gambarnya melayang di atas isian tanpa
                                 pembatas, sehingga tidak terbaca sebagai pasangan. --}}
                            <div class="orcha-foto-kotak @error('gambar') galat @enderror">
                                <div class="orcha-foto-rupa">
                                    @if ($gambar)
                                        <img src="{{ $gambar->temporaryUrl() }}" alt="">
                                    @elseif ($gambarLama && $tautanGambar($gambarLama))
                                        <img src="{{ $tautanGambar($gambarLama) }}" alt="">
                                    @else
                                        <span class="orcha-foto-kosong"><i class="bi bi-image"></i></span>
                                    @endif
                                </div>

                                <div class="orcha-foto-isi">
                                    <input type="file" class="form-control form-control-sm @error('gambar') is-invalid @enderror"
                                        wire:model="gambar" accept="image/*">
                                    <div class="form-text">Maksimal 4 MB. Kosong berarti gambar lama tetap dipakai.</div>
                                    <div wire:loading wire:target="gambar" class="text-muted small">Mengunggah…</div>
                                    @error('gambar') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>
                            </div>

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

    <style>
        /* Kartu etalase: satu bentuk untuk destinasi, testimoni, dan partner.
           Yang membedakan isinya, bukan susunannya — sebaris kartu yang
           anatominya berbeda-beda terbaca berantakan walaupun tiap kartunya
           sendiri rapi. */
        .orcha-etalase-foto {
            position: relative;
            /* Nisbah, bukan tinggi tetap: tinggi tetap membuat gambar terpotong
               berbeda-beda pada tiap lebar layar. */
            aspect-ratio: 16 / 10;
            border-radius: 1rem 1rem 0 0;
            overflow: hidden;
            background: linear-gradient(135deg, #eef4f9, #dce8f2);
        }

        /* Tanpa foto, kotaknya tidak ikut setinggi nisbah itu. Nisbah gambar
           untuk kotak yang tidak bergambar hanya menyisakan bidang abu setinggi
           dua ratus piksel lebih — persis "bagian yang kosong" yang mestinya
           dihindari. Cukup sepotong pita, tetap ada supaya lencananya punya
           tempat dan anatominya tidak berubah. */
        .orcha-etalase-foto.tanpa {
            aspect-ratio: auto;
            height: 5.25rem;
        }

        .orcha-etalase-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Logo partner dimuat UTUH, bukan dipotong penuh kotak. Dipotong seperti
           foto, logo yang lebar kehilangan tulisannya dan yang tinggi kehilangan
           lambangnya — dan logo yang terpotong tidak lagi mengenalkan siapa pun. */
        .orcha-etalase-foto.logo {
            background: #fff;
            border-bottom: 1px solid #eef4f9;
        }

        .orcha-etalase-foto.logo img {
            object-fit: contain;
            padding: 1.1rem;
        }

        .orcha-etalase-foto .kosong {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a9c0d4;
            font-size: 1.6rem;
        }

        /* Ditempel di atas gambar, bukan di sebelah nama: di sebelah nama ia
           ikut mendorong nama yang panjang sampai membungkus, dan tinggi
           kartunya jadi tidak sama lagi. */
        .orcha-etalase-tanda {
            position: absolute;
            top: .6rem;
            right: .6rem;
            padding: .2rem .6rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            color: #0f2d4a;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .01em;
            box-shadow: 0 2px 6px rgba(15, 45, 74, .12);
        }

        .orcha-etalase-tanda.bintang { color: #d98a00; letter-spacing: .05em; }

        .orcha-etalase-nama {
            color: #0f2d4a;
            /* Nama sepanjang apa pun berhenti di dua baris. */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .orcha-etalase-alamat {
            display: flex;
            align-items: baseline;
            gap: .35rem;
            margin: 0;
            color: #5b7182;
            font-size: .78rem;
            line-height: 1.4;
        }

        .orcha-etalase-alamat > i { color: #1d6fa5; font-size: .78rem; }

        .orcha-etalase-ket {
            color: #64748b;
            font-size: .8rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            /* Dipatok setinggi dua baris supaya kartu berketerangan pendek tetap
               sejajar dengan tetangganya. */
            min-height: 2.4rem;
        }

        .orcha-etalase-ket.kosong { color: #a3b2c2; font-style: italic; }

        .orcha-etalase-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem .8rem;
            padding-top: .7rem;
            border-top: 1px dashed #e6eef5;
            color: #64748b;
            font-size: .74rem;
        }

        .orcha-etalase-meta span {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
        }

        .orcha-etalase-meta i { color: #9db4c7; font-size: .8rem; }

        /* Belum ada gambar tambahan sama sekali — bukan galat, tetapi memang
           yang paling sering perlu dikerjakan berikutnya. */
        .orcha-etalase-meta .sepi,
        .orcha-etalase-meta .sepi i { color: #b8791f; }

        .orcha-etalase-aksi {
            display: flex;
            gap: .5rem;
        }

        /* Keduanya sama lebar: dua tombol berbeda lebar di dasar tiap kartu
           membuat sebaris kartu terlihat tidak sejajar padahal kartunya sejajar. */
        .orcha-etalase-aksi > * {
            flex: 1;
            justify-content: center;
        }

        .orcha-etalase-ubah {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            padding: .42rem .8rem;
            border: 1px solid #d8e6f1;
            border-radius: .7rem;
            background: #f4f9fd;
            color: #14588a;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .15s ease;
        }

        .orcha-etalase-ubah:hover {
            background: #e6f2fb;
            border-color: #9fc7e4;
            color: #0f2d4a;
        }

        .orcha-etalase-ubah i { font-size: .85em; line-height: 1; }
    </style>
</div>
