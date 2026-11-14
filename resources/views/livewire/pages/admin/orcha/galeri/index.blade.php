@section('title')
Galeri Perjalanan || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon, patokan yang sama dengan layar
         Pesanan Toko. Kelas orcha-* hanya untuk yang memang khas Orcha —
         di layar ini tinggal saklar tampil/sembunyi. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        /* Foto disimpan di Orcha, bukan di lemon.

           Yang dikirim API berupa jalur relatif "/storage/galeri/x.webp", dan
           memasangnya apa adanya membuat peramban mencarinya di alamat lemon —
           hasilnya kotak gambar rusak. Pola ini sudah dipakai halaman etalase;
           dipakai ulang supaya keduanya tidak lama-lama berbeda. */
        $asalGambar = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
        $tautanGambar = fn ($jalur) => $jalur
            ? (str_starts_with($jalur, 'http') ? $jalur : $asalGambar . $jalur)
            : null;

        $jumlahFoto = count($daftar);
        $jumlahSembunyi = $jumlahFoto - $jumlahTampil;
    @endphp

    <style>
        /* Gaya khusus layar Galeri Perjalanan.

           Dasarnya bahasa rupa dasbor (partials/dasbor-gaya); di sini hanya
           yang tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
           .gitignore dan tidak ikut ter-deploy. */

        /* ===== Tempat menjatuhkan berkas ===== */
        .gal-jatuhkan {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 6px; width: 100%; min-height: 168px; padding: 26px 18px; margin: 0;
            border: 2px dashed #ddd6fe; border-radius: 16px;
            background: linear-gradient(180deg, #faf9ff 0%, #fff 100%);
            cursor: pointer; text-align: center;
            transition: border-color .18s ease, background .18s ease;
        }
        .gal-jatuhkan:hover { border-color: #a78bfa; background: #f8f6ff; }
        .gal-jatuhkan.is-sibuk { border-color: #c4b5fd; cursor: progress; }
        .gal-jatuhkan-ikon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 54px; height: 54px; margin-bottom: 6px;
            border-radius: 16px; border: 1px solid #ddd6fe;
            background: #f5f3ff; color: #7c3aed; font-size: 1.5rem;
        }
        .gal-jatuhkan-ikon i.bi::before { display: block; line-height: 1; }
        .gal-jatuhkan.is-sibuk .gal-jatuhkan-ikon { animation: gal-denyut 1.1s ease-in-out infinite; }
        @keyframes gal-denyut { 50% { opacity: .45; } }
        @media (prefers-reduced-motion: reduce) {
            .gal-jatuhkan.is-sibuk .gal-jatuhkan-ikon { animation: none; }
        }
        .gal-jatuhkan-judul { font-size: .92rem; font-weight: 800; color: var(--dsb-tinta, #1c1f26); }
        .gal-jatuhkan-ket { font-size: .78rem; color: var(--dsb-redup, #6b7280); }

        /* ===== Petak foto ===== */
        .gal-petak {
            display: grid; gap: clamp(12px, 1.4vw, 16px);
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        }
        @media (max-width: 575.98px) {
            .gal-petak { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); }
        }

        .gal-kartu {
            display: flex; flex-direction: column; overflow: hidden;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 14px; background: #fff;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        @media (hover: hover) and (pointer: fine) {
            .gal-kartu:hover { border-color: #dfe5ee; box-shadow: 0 10px 24px rgba(15, 23, 42, .06); }
        }
        /* Foto yang sedang tidak dipajang tampil pudar, bukan hilang: admin
           tetap bisa melihat apa yang disembunyikannya. */
        .gal-kartu.is-sembunyi { background: #f8fafc; }
        .gal-kartu.is-sembunyi .gal-kotak img { filter: grayscale(1); opacity: .55; }

        .gal-kotak { position: relative; aspect-ratio: 4 / 3; background: #f1f5f9; }
        .gal-kotak img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .gal-nomor {
            position: absolute; top: 8px; left: 8px; min-width: 26px; height: 26px; padding: 0 8px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 9px; background: rgba(15, 23, 42, .72); color: #fff;
            font-size: .72rem; font-weight: 800; backdrop-filter: blur(2px);
        }
        .gal-tanda {
            position: absolute; bottom: 8px; left: 8px; right: 8px;
            display: inline-flex; align-items: center; justify-content: center; gap: 5px;
            padding: 4px 9px; border-radius: 999px;
            background: rgba(15, 23, 42, .78); color: #fff; font-size: .68rem; font-weight: 700;
        }

        .gal-isi { display: flex; flex-direction: column; gap: 9px; padding: 11px 12px 12px; flex: 1 1 auto; }
        .gal-ket {
            font-size: .8rem; color: var(--dsb-tinta, #1c1f26); line-height: 1.45;
            overflow-wrap: anywhere; flex: 1 1 auto;
        }
        .gal-ket.is-kosong { color: #94a3b8; font-style: italic; }
        .gal-aksi { display: flex; gap: 6px; }

        /* ===== Baris isian di bawah pratayang ===== */
        .gal-baris {
            display: grid; gap: 12px; align-items: center;
            grid-template-columns: minmax(0, 1fr) auto auto;
            margin-top: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9;
        }
        .gal-samakan { display: flex; gap: 8px; min-width: 0; }
        .gal-samakan .dsb-isian { flex: 1 1 auto; }
        @media (max-width: 991.98px) {
            .gal-baris { grid-template-columns: minmax(0, 1fr); }
            .gal-baris .orcha-saklar { justify-self: start; }
        }

        /* ===== Formulir sunting di tempat ===== */
        .gal-sunting {
            display: flex; gap: clamp(12px, 1.6vw, 18px); align-items: flex-start;
            padding: clamp(13px, 1.6vw, 17px); margin-bottom: 16px;
            border: 1px solid #ddd6fe; border-radius: 15px; background: #faf9ff;
        }
        .gal-sunting .gal-kotak { flex: 0 0 132px; width: 132px; border-radius: 12px; overflow: hidden; }
        .gal-sunting-isi { flex: 1 1 auto; min-width: 0; }
        .gal-sunting-judul {
            display: flex; align-items: center; gap: 7px;
            margin: 0 0 14px; font-size: .95rem; font-weight: 800;
            color: var(--dsb-tinta, #1c1f26);
        }
        .gal-sunting-judul i.bi { color: #7c3aed; }
        .gal-sunting-medan { display: grid; gap: 12px; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        .gal-sunting-aksi {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
            margin-top: 14px;
        }
        .gal-sunting-aksi .orcha-saklar { margin-right: auto; }
        @media (max-width: 767.98px) {
            .gal-sunting { flex-direction: column; }
            .gal-sunting .gal-kotak { flex: 0 0 auto; width: 100%; }
            .gal-sunting-medan { grid-template-columns: minmax(0, 1fr); }
            .gal-sunting-aksi .orcha-saklar { margin-right: 0; width: 100%; }
            .gal-sunting-aksi .dsb-tombol { flex: 1 1 0; }
        }
    </style>

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Galeri Perjalanan</h1>
                <p class="dsb-hero-ket">
                    Foto yang tampil di bagian <b>&ldquo;Momen perjalanan pelanggan kami&rdquo;</b>
                    pada beranda Orcha.
                </p>

                <div class="dsb-chip-deret mt-2">
                    <span class="dsb-chip" style="--c: #16a34a">
                        <i class="bi bi-eye-fill"></i> {{ $jumlahTampil }} tampil di beranda
                    </span>
                    @if ($jumlahSembunyi)
                        <span class="dsb-chip" style="--c: #64748b">
                            <i class="bi bi-eye-slash-fill"></i> {{ $jumlahSembunyi }} disembunyikan
                        </span>
                    @endif
                </div>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ============ UNGGAH ============
             Ditaruh paling atas, bukan di balik tombol "Tambah".

             Yang dilakukan admin di halaman ini sembilan dari sepuluh kali
             adalah menambah foto sepulang trip. Menyembunyikannya di balik satu
             ketukan lagi berarti menambah langkah pada pekerjaan yang paling
             sering dikerjakan. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Tambah foto</h3>
                                <span class="dsb-kartu-sub">
                                    Bisa pilih banyak foto sekaligus &mdash; tahan Ctrl (atau &#8984; di Mac) saat memilih.
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="dsb-kartu-isi">
                        {{-- Ikonnya berdenyut selama berkas diproses.

                             Unggahan foto besar memakan beberapa detik, dan selama itu
                             layar tidak berubah sama sekali. Yang dilakukan admin
                             berikutnya bisa ditebak: mengetuk lagi, lalu bertanya kenapa
                             fotonya masuk dua kali. --}}
                        <label class="gal-jatuhkan" wire:loading.class="is-sibuk" wire:target="fotoBaru,unggah">
                            <input type="file" wire:model="fotoBaru" multiple accept="image/*" hidden>

                            <span class="gal-jatuhkan-ikon"><i class="bi bi-images"></i></span>

                            <span class="gal-jatuhkan-judul">
                                <span wire:loading.remove wire:target="fotoBaru,unggah">Pilih foto dari komputer</span>
                                <span wire:loading wire:target="fotoBaru">Membaca foto&hellip;</span>
                                <span wire:loading wire:target="unggah">Mengunggah ke Orcha&hellip;</span>
                            </span>

                            <span class="gal-jatuhkan-ket">
                                <span wire:loading.remove wire:target="fotoBaru,unggah">
                                    JPG, PNG, atau WebP &mdash; ukuran berapa pun, boleh banyak sekaligus
                                </span>
                                <span wire:loading wire:target="fotoBaru,unggah">Mohon tunggu, jangan menutup halaman</span>
                            </span>
                        </label>

                        @error('fotoBaru')
                            <p class="dsb-galat mt-2 mb-0">{{ $message }}</p>
                        @enderror
                        @error('fotoBaru.*')
                            <p class="dsb-galat mt-2 mb-0">{{ $message }}</p>
                        @enderror

                        @if ($fotoBaru)
                            {{-- Tiap foto punya isian keterangannya sendiri.

                                 Satu keterangan untuk seluruh unggahan benar selama
                                 fotonya serombongan dari acara yang sama &mdash; dan salah
                                 begitu admin memilih foto dari beberapa trip sekaligus.
                                 Yang serombongan tetap terlayani: satu isian di bawah
                                 menyamakan semuanya sekali tekan. --}}
                            <div class="gal-petak mt-3">
                                @foreach ($fotoBaru as $urutan => $satu)
                                    @if ($satu->isPreviewable())
                                        <div class="gal-kartu">
                                            <div class="gal-kotak">
                                                <img src="{{ \App\Support\PratinjauUnggahan::url($satu) }}" alt="">
                                                <span class="gal-nomor">{{ $urutan + 1 }}</span>
                                            </div>

                                            <div class="gal-isi">
                                                <input type="text" class="dsb-isian"
                                                    wire:model="keteranganPer.{{ $urutan }}"
                                                    value="{{ $keteranganPer[$urutan] ?? '' }}"
                                                    placeholder="Keterangan foto ini">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            @error('keteranganPer.*')
                                <p class="dsb-galat mt-2 mb-0">{{ $message }}</p>
                            @enderror

                            <div class="gal-baris">
                                <div class="gal-samakan">
                                    <input type="text" class="dsb-isian"
                                        wire:model="keteranganBaru" value="{{ $keteranganBaru }}"
                                        placeholder="Keterangan yang sama untuk semua foto">
                                    <button type="button" class="dsb-tombol is-lembut"
                                        wire:click="samakanKeterangan" title="Isikan ke seluruh foto di atas">
                                        <i class="bi bi-arrow-down-up"></i>
                                        <span class="d-none d-sm-inline">Terapkan ke semua</span>
                                    </button>
                                </div>

                                <label class="orcha-saklar">
                                    <input type="checkbox" wire:model="tampilBaru">
                                    <span class="alur"></span>
                                    <span class="tulisan">Langsung tampil di beranda</span>
                                </label>

                                <button type="button" class="dsb-tombol is-utama"
                                    wire:click="unggah" wire:loading.attr="disabled" wire:target="unggah">
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                    Unggah {{ count($fotoBaru) }} foto
                                </button>
                            </div>

                            <p class="dsb-kartu-sub mt-2 mb-0">
                                Urutan tampilnya diberikan otomatis di belakang foto yang sudah ada &mdash;
                                bisa diubah nanti lewat tombol pensil.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ DAFTAR FOTO ============ --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0ea5e9"><i class="bi bi-images"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Foto di galeri</h3>
                                <span class="dsb-kartu-sub">
                                    Nomor kecil menentukan urutan tampil &mdash; makin kecil, makin depan.
                                </span>
                            </div>
                        </div>

                        @if ($jumlahFoto)
                            <span class="dsb-chip" style="--c: #7c3aed">
                                <i class="bi bi-images"></i> {{ $jumlahFoto }} foto
                            </span>
                        @endif
                    </div>

                    <div class="dsb-kartu-isi">
                        @foreach ($daftar as $foto)
                            @if ($sedangDiubah === (int) $foto['id'])
                                {{-- Formulir menggantikan kartunya di tempat, bukan membuka
                                     jendela: yang disunting tetap terlihat di sebelah isian,
                                     jadi admin tidak perlu mengingat foto mana yang tadi
                                     diketuk. --}}
                                <div class="gal-sunting">
                                    <div class="gal-kotak">
                                        <img src="{{ $tautanGambar($foto['foto']) }}" alt="">
                                        <span class="gal-nomor">{{ $foto['urutan'] ?? 0 }}</span>
                                    </div>

                                    <div class="gal-sunting-isi">
                                        <p class="gal-sunting-judul">
                                            <i class="bi bi-pencil-square"></i> Ubah foto ini
                                        </p>

                                        <div class="gal-sunting-medan">
                                            <div class="dsb-medan">
                                                <label class="dsb-label">Keterangan (boleh dikosongkan)</label>
                                                <input type="text" class="dsb-isian"
                                                    wire:model="keterangan" value="{{ $keterangan }}"
                                                    placeholder="Misal: Rombongan SMA 1 di Kawah Ijen">
                                                @error('keterangan')
                                                    <p class="dsb-galat mb-0">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="dsb-medan">
                                                <label class="dsb-label">Urutan tampil</label>
                                                <input type="number" min="0" class="dsb-isian"
                                                    wire:model="urutan" value="{{ $urutan }}">
                                                @error('urutan')
                                                    <p class="dsb-galat mb-0">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="gal-sunting-aksi">
                                            <label class="orcha-saklar">
                                                <input type="checkbox" wire:model="tampil">
                                                <span class="alur"></span>
                                                <span class="tulisan">Tampil di beranda</span>
                                            </label>

                                            <button type="button" class="dsb-tombol is-lembut" wire:click="batal">
                                                <i class="bi bi-x-lg"></i> Batal
                                            </button>

                                            <button type="button" class="dsb-tombol is-utama"
                                                wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan">
                                                <i class="bi bi-check-lg"></i> Simpan
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($jumlahFoto)
                            <div class="gal-petak">
                                @foreach ($daftar as $foto)
                                    <div class="gal-kartu {{ ($foto['tampil'] ?? true) ? '' : 'is-sembunyi' }}">
                                        <div class="gal-kotak">
                                            <img src="{{ $tautanGambar($foto['foto']) }}" alt="{{ $foto['keterangan'] ?? '' }}">

                                            <span class="gal-nomor">{{ $foto['urutan'] ?? 0 }}</span>

                                            @unless ($foto['tampil'] ?? true)
                                                <span class="gal-tanda">
                                                    <i class="bi bi-eye-slash-fill"></i> Disembunyikan
                                                </span>
                                            @endunless
                                        </div>

                                        <div class="gal-isi">
                                            <div class="gal-ket {{ $foto['keterangan'] ? '' : 'is-kosong' }}">
                                                {{ $foto['keterangan'] ?: 'Tanpa keterangan' }}
                                            </div>

                                            <div class="gal-aksi">
                                                <button type="button" class="dsb-tabel-btn" style="--c: #7c3aed"
                                                    wire:click="ubah({{ \Illuminate\Support\Js::from($foto) }})"
                                                    title="Ubah keterangan dan urutannya">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button type="button" class="dsb-tabel-btn" style="--c: #0284c7"
                                                    wire:click="balikTampil({{ \Illuminate\Support\Js::from($foto) }})"
                                                    title="{{ ($foto['tampil'] ?? true) ? 'Sembunyikan dari beranda' : 'Tampilkan lagi di beranda' }}">
                                                    <i class="bi {{ ($foto['tampil'] ?? true) ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                                </button>

                                                {{-- Konfirmasinya lewat SweetAlert, bukan wire:confirm.

                                                     Dialog bawaan peramban menampilkan alamat server
                                                     di atas kalimatnya, terbaca seperti peringatan
                                                     sistem yang bocor, bukan bagian dari aplikasi. Pola
                                                     .pcek-konfirmasi sudah dipakai halaman Orcha lain;
                                                     dipakai ulang supaya admin tidak melihat dua bentuk
                                                     konfirmasi untuk tindakan yang sama berbahayanya. --}}
                                                <button type="button" class="dsb-tabel-btn is-bahaya pcek-konfirmasi"
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
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-images"></i></span>
                                <p class="dsb-kosong-judul">Belum ada foto di galeri</p>
                                <p class="dsb-kosong-ket">
                                    Selama galeri masih kosong, beranda memakai foto destinasi sebagai penggantinya,
                                    jadi bagian itu tidak pernah tampak kosong.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
