@section('title')
{{ $info[0] }} · Situs Asthana || lemon
@stop

@php
    [$judul, $ikonJudul, $warna, $kelasModel, $kolomGambar, $keterangan] = $info;
    $pakaiGambar = $kolomGambar !== null;
    $isKlien = $jenis === 'klien';
    $isTesti = $jenis === 'testimoni';
    $isTim = $jenis === 'tim';
    $kosong = $daftar->isEmpty();
    $jumlahBaris = $daftar->count();
    $labelGambar = $isKlien ? 'Logo' : 'Foto';
    $ketGambar = $isKlien
        ? 'PNG berlatar bening paling rapi. Maksimal 4 MB.'
        : 'Foto persegi, wajah di tengah. Maksimal 4 MB.';
    $adaGambarBaru = (bool) $gambar;
    $urlGambarLama = $gambarLama ? $kelasModel::urlGambar($gambarLama) : null;
    $urlPratinjau = $adaGambarBaru ? \App\Support\PratinjauUnggahan::url($gambar) : $urlGambarLama;
    $adaPratinjau = (bool) $urlPratinjau;
    $judulJendela = ($sedangDiubah ? 'Ubah ' : 'Tambah ').strtolower($judul);
@endphp

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.asthana.partials.gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Isi Situs Asthana</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Dikelola dari sini, tampil di situs profil PT. Asthana Cipta Mandiri.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <button type="button" class="dsb-tombol is-utama" wire:click="tambah">
                    <i class="bi bi-plus-lg"></i><span>Tambah {{ strtolower($judul) }}</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.asthana.partials.navigasi', ['aktif' => $jenis])

        <section class="dsb-bagian">
            <div class="dsb-kartu">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}"><i class="bi {{ $ikonJudul }}"></i></span>
                    <span class="dsb-jendela-teks">
                        <h2 class="dsb-jendela-judul">{{ $judul }}</h2>
                        <span class="dsb-kartu-sub">{{ $keterangan }} Urutan di sini = urutan di situs.</span>
                    </span>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($kosong)
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi {{ $ikonJudul }}"></i></span>
                            <p class="dsb-kosong-judul">Belum ada {{ strtolower($judul) }}</p>
                            <p class="dsb-kosong-ket">Bagian ini disembunyikan di situs sampai ada isinya.</p>
                            <button type="button" wire:click="tambah" class="dsb-tombol is-utama" style="margin-top: 14px;">
                                <i class="bi bi-plus-lg"></i><span>Tambah yang pertama</span>
                            </button>
                        </div>
                    @else
                        <div class="ast-kisi">
                            @foreach ($daftar as $baris)
                                @php
                                    $urlBaris = $pakaiGambar ? $kelasModel::urlGambar($baris->{$kolomGambar}) : null;
                                    $punyaRupa = (bool) $urlBaris;
                                    $pertama = $loop->first;
                                    $terakhir = $loop->last;
                                    $namaBaris = $baris->nama;
                                    $ketBaris = match ($jenis) {
                                        'klien' => $baris->tautan ?: 'Tanpa tautan',
                                        'testimoni' => '“'.$baris->isi.'”',
                                        default => $baris->jabatan,
                                    };
                                    $subTesti = collect([$baris->jabatan ?? null, $baris->perusahaan ?? null])->filter()->implode(' · ');
                                @endphp
                                <article class="dsb-kartu ast-kartu {{ $baris->tampil ? '' : 'is-sembunyi' }}" wire:key="ast-{{ $jenis }}-{{ $baris->id }}" style="--c: {{ $warna }}">
                                    <div class="ast-kartu-rupa {{ $isKlien ? 'is-logo' : 'is-foto' }}">
                                        @if ($punyaRupa)
                                            <img src="{{ $urlBaris }}" alt="{{ $namaBaris }}" loading="lazy">
                                        @else
                                            <span class="ast-kosong"><i class="bi bi-image"></i></span>
                                        @endif
                                        <span class="ast-kartu-tanda dsb-lencana {{ $baris->tampil ? 'is-hijau' : 'is-abu' }}">
                                            <i class="bi {{ $baris->tampil ? 'bi-eye' : 'bi-eye-slash' }}"></i>{{ $baris->tampil ? 'Tampil' : 'Disembunyikan' }}
                                        </span>
                                    </div>
                                    <div class="ast-kartu-badan">
                                        <p class="ast-kartu-nama">{{ $namaBaris }}</p>
                                        @if ($isTesti)
                                            <span class="dsb-kartu-sub">{{ str_repeat('★', (int) $baris->bintang) }} {{ $subTesti }}</span>
                                        @endif
                                        <p class="ast-kartu-ket">{{ $ketBaris }}</p>

                                        <div class="ast-kartu-aksi">
                                            <button type="button" class="ast-ikon-btn" title="Naikkan" wire:click="geser({{ $baris->id }}, -1)" @disabled($pertama)>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="ast-ikon-btn" title="Turunkan" wire:click="geser({{ $baris->id }}, 1)" @disabled($terakhir)>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="ast-ikon-btn" title="{{ $baris->tampil ? 'Sembunyikan dari situs' : 'Tampilkan di situs' }}" wire:click="alihTampil({{ $baris->id }})">
                                                <i class="bi {{ $baris->tampil ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                            </button>
                                            <button type="button" class="dsb-tombol is-lembut is-mungil" wire:click="ubah({{ $baris->id }})">
                                                <i class="bi bi-pencil"></i><span>Ubah</span>
                                            </button>
                                            <button type="button" class="dsb-tombol is-bahaya is-mungil pcek-konfirmasi"
                                                data-action="hapus" data-arg="{{ $baris->id }}"
                                                data-title="Hapus {{ strtolower($judul) }}?"
                                                data-text="{{ $namaBaris }} akan dihapus dari situs."
                                                data-confirm="Ya, hapus" data-icon="warning">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @if ($formTerbuka)
        <div class="ts-modal-back" wire:click="tutup"></div>
        <div class="ts-modal">
            <form class="ts-modal-card dsb is-datar" role="dialog" aria-modal="true" aria-label="{{ $judulJendela }}"
                wire:submit="simpan" x-on:keydown.escape.window="$wire.tutup()">
                <div class="dsb-jendela-kepala">
                    <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}"><i class="bi {{ $ikonJudul }}"></i></span>
                    <span class="dsb-jendela-teks">
                        <h5 class="dsb-jendela-judul">{{ ucfirst($judulJendela) }}</h5>
                        <span class="dsb-kartu-sub">{{ $keterangan }}</span>
                    </span>
                    <button type="button" class="dsb-jendela-tutup" wire:click="tutup" title="Tutup"><i class="bi bi-x-lg"></i></button>
                </div>

                <div class="dsb-jendela-isi ast-tumpuk">
                        <div class="dsb-medan">
                            <label class="dsb-label" for="ast-nama">{{ $isKlien ? 'Nama klien / mitra' : 'Nama' }} <span class="dsb-wajib">*</span></label>
                            <input id="ast-nama" type="text" class="dsb-isian @error('nama') is-galat @enderror" wire:model="nama">
                            @error('nama') <span class="dsb-galat">{{ $message }}</span> @enderror
                        </div>

                        @if ($isTim)
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-jabatan">Jabatan <span class="dsb-wajib">*</span></label>
                                <input id="ast-jabatan" type="text" class="dsb-isian @error('jabatan') is-galat @enderror" wire:model="jabatan" placeholder="Direktur Utama">
                                @error('jabatan') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if ($isTesti)
                            <div class="ast-isian-deret">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ast-jabatan">Jabatan</label>
                                    <input id="ast-jabatan" type="text" class="dsb-isian" wire:model="jabatan" placeholder="Pemilik">
                                </div>
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="ast-perusahaan">Perusahaan / instansi</label>
                                    <input id="ast-perusahaan" type="text" class="dsb-isian" wire:model="perusahaan">
                                </div>
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-isi">Isi testimoni <span class="dsb-wajib">*</span></label>
                                <textarea id="ast-isi" rows="4" class="dsb-isian @error('isi') is-galat @enderror" wire:model="isi"></textarea>
                                @error('isi') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-bintang">Bintang</label>
                                <select id="ast-bintang" class="dsb-isian" wire:model="bintang">
                                    @foreach ([5, 4, 3, 2, 1] as $nBintang)
                                        <option value="{{ $nBintang }}" @selected((int) $this->bintang === $nBintang)>{{ str_repeat('★', $nBintang) }} ({{ $nBintang }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($isKlien)
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-tautan">Situs web <span class="dsb-label-ket">opsional — logonya jadi tautan</span></label>
                                <input id="ast-tautan" type="url" class="dsb-isian @error('tautan') is-galat @enderror" wire:model="tautan" placeholder="https://">
                                @error('tautan') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if ($isTim)
                            <div class="dsb-medan">
                                <label class="dsb-label" for="ast-tautan">LinkedIn <span class="dsb-label-ket">opsional</span></label>
                                <input id="ast-tautan" type="url" class="dsb-isian @error('tautan') is-galat @enderror" wire:model="tautan" placeholder="https://www.linkedin.com/in/...">
                                @error('tautan') <span class="dsb-galat">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="dsb-medan">
                            <span class="dsb-label">{{ $labelGambar }} <span class="dsb-label-ket">opsional</span></span>
                            <div class="ast-unggah">
                                <div class="ast-unggah-rupa {{ $isKlien ? 'is-logo' : '' }}">
                                    @if ($adaPratinjau)
                                        <img src="{{ $urlPratinjau }}" alt="">
                                    @else
                                        <i class="bi bi-image"></i>
                                    @endif
                                    {{-- Ditumpangkan, bukan ditukar: elemen yang ditukar butuh
                                         display sendiri dan mengalahkan penyembunyi Livewire. --}}
                                    <span wire:loading wire:target="gambar" class="ast-naik"><span class="ast-naik-isi"><span class="spinner-border spinner-border-sm"></span></span></span>
                                </div>
                                <div class="ast-unggah-isi">
                                    <input type="file" accept="image/*" wire:model="gambar">
                                    <span class="ast-catatan">{{ $ketGambar }} Kosong berarti gambar lama tetap dipakai.</span>
                                    @error('gambar') <span class="dsb-galat">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                    <label class="ast-saklar">
                        <input type="checkbox" wire:model="tampil" @checked($this->tampil)>
                        Tampilkan di situs
                    </label>
                </div>

                <div class="dsb-jendela-kaki">
                    <span class="dsb-jendela-kaki-ket"><i class="bi bi-globe2"></i> Langsung berlaku di situs.</span>
                    <div class="dsb-jendela-aksi">
                        <button type="button" class="dsb-tombol is-lembut" wire:click="tutup"><span>Batal</span></button>
                        <button type="submit" class="dsb-tombol is-utama" wire:loading.attr="disabled" wire:target="simpan,gambar">
                            <i class="bi bi-check2"></i>
                            <span wire:loading.remove wire:target="simpan,gambar">Simpan</span>
                            <span wire:loading wire:target="gambar">Mengunggah…</span>
                            <span wire:loading wire:target="simpan">Menyimpan…</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
