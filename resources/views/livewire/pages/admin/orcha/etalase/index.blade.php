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
                        <button type="button" class="btn btn-primary rounded-3 orcha-tombol orcha-tombol-tambah" wire:click="tambah">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah {{ $judul }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($daftar as $baris)
                <div class="col-12 col-md-6 col-xl-4" wire:key="{{ $jenis }}-{{ $baris['id'] }}">
                    <div class="card orcha-kartu h-100">
                        @php $foto = $tautanGambar($baris['foto'] ?? $baris['logo'] ?? null); @endphp

                        @if ($foto)
                            <img src="{{ $foto }}" alt="{{ $baris['nama'] }}"
                                class="rounded-top-4" style="height: 150px; object-fit: cover">
                        @endif

                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <h6 class="fw-bold mb-0">{{ $baris['nama'] }}</h6>

                                @if ($jenis === 'testimoni')
                                    <span class="text-warning small text-nowrap">
                                        {{ str_repeat('★', (int) $baris['rating']) }}
                                    </span>
                                @elseif ($jenis === 'destinasi')
                                    <span class="badge bg-light text-dark">{{ $baris['wilayah_label'] ?? '' }}</span>
                                @endif
                            </div>

                            @if ($jenis === 'destinasi')
                                <div class="text-muted small mb-2">{{ $baris['provinsi'] ?: '—' }}</div>
                                <p class="small text-muted mb-0">
                                    {{ \Illuminate\Support\Str::limit($baris['deskripsi'] ?? '', 110) }}
                                </p>
                            @elseif ($jenis === 'testimoni')
                                <p class="small text-muted mb-0">
                                    {{ \Illuminate\Support\Str::limit($baris['isi'] ?? '', 140) }}
                                </p>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-light border rounded-3 orcha-tombol"
                                    wire:click='ubah(@json($baris))'>
                                    <i class="bi bi-pencil"></i>
                                    <span>Ubah</span>
                                </button>

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

                            {{-- Gambar tambahan — hanya destinasi yang punya.

                                 Kartu destinasi di halaman publik menampilkannya di
                                 bawah keterangan, tetapi jendela ini dulu hanya
                                 mengenal satu foto: admin yang mengurus destinasi dari
                                 sini tidak punya cara menambah maupun menghapusnya, dan
                                 harus membuka admin bawaan Orcha — yang justru
                                 dihindari dengan adanya halaman ini. --}}
                            @if ($jenis === 'destinasi')
                                <div class="orcha-sub-foto mt-3">
                                    <div class="orcha-sub-kepala">
                                        <span class="judul">
                                            <i class="bi bi-images"></i>
                                            Gambar tambahan
                                        </span>
                                        <span class="ket">
                                            Tampil di kartu destinasi —
                                            sisa {{ $this->sisaSubFoto() }} dari {{ $batasSubFoto }}
                                        </span>
                                    </div>

                                    @if ($subFotoTetap || $subFoto)
                                        <div class="orcha-sub-petak">
                                            @foreach ($subFotoTetap as $jalur)
                                                <div class="petak" wire:key="sub-tetap-{{ md5($jalur) }}">
                                                    <img src="{{ $tautanGambar($jalur) }}" alt="">
                                                    {{-- Berkasnya baru dibuang di Orcha saat
                                                         perubahan disimpan, jadi menutup jendela
                                                         tanpa menyimpan tidak menghilangkan apa
                                                         pun. --}}
                                                    <button type="button" class="buang"
                                                        wire:click="hapusSubFoto('{{ $jalur }}')"
                                                        title="Keluarkan gambar ini">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach

                                            @foreach ($subFoto as $urutan => $berkas)
                                                <div class="petak baru" wire:key="sub-baru-{{ $urutan }}">
                                                    <img src="{{ $berkas->temporaryUrl() }}" alt="">
                                                    <span class="tanda">Baru</span>
                                                    <button type="button" class="buang"
                                                        wire:click="batalkanSubFoto({{ $urutan }})"
                                                        title="Batalkan gambar ini">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="orcha-sub-kosong">
                                            <i class="bi bi-image"></i>
                                            Belum ada gambar tambahan.
                                        </p>
                                    @endif

                                    @if ($this->sisaSubFoto() > 0)
                                        <input type="file" multiple
                                            class="form-control form-control-sm mt-2 @error('subFoto.0') is-invalid @enderror"
                                            wire:model="subFoto" accept="image/*">
                                        <div class="form-text">
                                            Bisa pilih beberapa sekaligus — maksimal 2 MB per gambar.
                                        </div>
                                    @else
                                        <p class="orcha-sub-penuh mt-2">
                                            <i class="bi bi-check-circle"></i>
                                            Sudah {{ $batasSubFoto }} gambar — hapus salah satu untuk menambah.
                                        </p>
                                    @endif

                                    <div wire:loading wire:target="subFoto" class="text-muted small mt-1">Mengunggah…</div>
                                    @error('subFoto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @error('subFoto.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            @endif
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
        /* Pratinjau dan pemilih berkas sebagai satu kotak: keduanya satu
           keputusan, dan gambar yang melayang tanpa pembatas tidak terbaca
           sebagai pasangan isiannya. */
        .orcha-foto-kotak {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: .75rem;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            background: #fbfdff;
        }

        .orcha-foto-kotak.galat {
            border-color: #f6c9cd;
            background: #fdf7f8;
        }

        .orcha-foto-rupa {
            width: 5.5rem;
            height: 4rem;
            flex-shrink: 0;
            border-radius: 10px;
            overflow: hidden;
            background: #eef2f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .orcha-foto-rupa img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Ikon dibungkus kotak yang memusatkan sendiri isinya — ikon telanjang
           tingginya ditentukan kotak barisnya, bukan oleh font-size-nya. */
        .orcha-foto-kosong {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: #94a3b8;
            font-size: 1.15rem;
        }

        .orcha-foto-isi { flex: 1; min-width: 0; }

        .orcha-foto-isi .form-text { margin-bottom: 0; }

        .orcha-sub-foto {
            padding: .85rem;
            border: 1px solid #e3e8ef;
            border-radius: 14px;
            background: #fbfdff;
        }

        .orcha-sub-kepala {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: .1rem .6rem;
            margin-bottom: .6rem;
        }

        .orcha-sub-kepala .judul {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .82rem;
            font-weight: 700;
            color: #0f2d4a;
        }

        .orcha-sub-kepala .ket { font-size: .74rem; color: #64748b; }

        /* Petak seukuran sama supaya deretannya rapi berapa pun rasio
           gambarnya — kartu di website pun memotongnya begitu. */
        .orcha-sub-petak {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(6.5rem, 1fr));
            gap: .6rem;
        }

        .orcha-sub-petak .petak {
            position: relative;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e3e8ef;
            background: #eef2f6;
        }

        .orcha-sub-petak .petak img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Yang baru dipilih ditandai: sebelum disimpan, keduanya terlihat sama
           padahal yang satu belum tersimpan di mana pun. */
        .orcha-sub-petak .petak.baru { border-color: #9fd0b4; }

        .orcha-sub-petak .tanda {
            position: absolute;
            left: .35rem;
            bottom: .35rem;
            padding: .05rem .35rem;
            border-radius: 5px;
            background: rgba(26, 138, 82, .92);
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
        }

        .orcha-sub-petak .buang {
            position: absolute;
            top: .3rem;
            right: .3rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.4rem;
            height: 1.4rem;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: rgba(15, 45, 74, .78);
            color: #fff;
            font-size: .62rem;
            line-height: 1;
        }

        .orcha-sub-petak .buang:hover { background: #c2323c; }

        .orcha-sub-kosong,
        .orcha-sub-penuh {
            display: flex;
            align-items: center;
            gap: .4rem;
            margin: 0;
            font-size: .78rem;
            color: #64748b;
        }

        .orcha-sub-penuh { color: #1a6b43; }
    </style>
</div>
