@section('title')
Isi Tarif Musim || lemon
@stop

<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')


    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">{{ $musim['nama'] ?? 'Musim' }}</h1>
                <p class="dsb-hero-ket">
                    @if ($musim)
                        <span class="d-block">
                            <strong>{{ $musim['rentang_teks'] }}</strong> — {{ $musim['jumlah_hari'] }} hari.
                        </span>
                    @endif
                    <span class="d-block">
                        Isi hanya harga yang <strong>benar-benar naik</strong> di musim ini. Yang dibiarkan
                        kosong tetap memakai tarif hari biasa — dan itu memang jawaban yang benar
                        untuk tiket masuk.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.musim-harga') }}" wire:navigate class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Daftar Musim</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ========= SARINGAN & PENGISI CEPAT ========= --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="mi-saring">
                            {{-- Kotak cari lewat partial bersama, bukan ditulis
                                 sendiri: tombol pengosongnya, jeda ketikannya, dan
                                 rupanya harus satu di seluruh daftar Orcha. Layar
                                 ini memuat dasbor-gaya, jadi rupanya 'dsb'. --}}
                            <div class="dsb-medan mi-medan-cari">
                                <label class="orcha-label-kecil">Cari harga</label>
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'nama atau destinasi',
                                    'rupa' => 'dsb',
                                ])
                            </div>

                            @if (count($kategoriAda) > 1)
                                <div class="dsb-medan mi-medan-pilih">
                                    <label class="orcha-label-kecil" for="mi-kategori">Kategori</label>
                                    <select id="mi-kategori" wire:model.live="filterKategori" class="dsb-isian">
                                        <option value="" @selected($this->filterKategori === '')>Semua kategori</option>
                                        @foreach ($kategoriAda as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected($this->filterKategori === (string) $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if (count($provinsiAda) > 1)
                                <div class="dsb-medan mi-medan-pilih">
                                    <label class="orcha-label-kecil" for="mi-provinsi">Provinsi</label>
                                    <select id="mi-provinsi" wire:model.live="filterProvinsi" class="dsb-isian">
                                        <option value="" @selected($this->filterProvinsi === '')>Semua provinsi</option>
                                        @foreach ($provinsiAda as $prov)
                                            <option value="{{ $prov }}" @selected($this->filterProvinsi === (string) $prov)>{{ $prov }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if ($this->adaSaringan())
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut is-kecil">
                                    <i class="bi bi-x-circle"></i><span>Bersihkan</span>
                                </button>
                            @endif
                        </div>

                        {{-- ===== PENGISI CEPAT =====

                             Vendor menyebut ANGKA, bukan persen. Jadi persen di
                             sini cuma titik mulai yang ditulis ke tiap kotak dan
                             masih bisa disunting — bukan aturan yang tersimpan.
                             Menyimpan persen berarti harga yang tidak pernah
                             dinegosiasi siapa pun dipakai sebagai modal. --}}
                        <div class="mi-cepat">
                            {{-- Petunjuknya DI LUAR label, bukan di dalamnya:
                                 orcha-label-kecil menulis isinya huruf besar semua,
                                 dan kalimat penjelas yang ikut jadi huruf besar
                                 terbaca sebagai judul yang berteriak. --}}
                            <span class="orcha-label-kecil mi-cepat-label">Isi cepat dari harga hari biasa</span>
                            <span class="dsb-kartu-sub d-block mi-cepat-sub">lalu sunting yang tidak cocok</span>
                            <div class="mi-cepat-rak">
                                @foreach ([25, 50, 75, 100] as $persen)
                                    <button type="button" wire:click="isiPersen({{ $persen }}, @js($calon))"
                                        class="mi-chip">+{{ $persen }}%</button>
                                @endforeach
                                <button type="button" wire:click="kosongkanSemua(@js($calon))"
                                    class="mi-chip is-bahaya">Kosongkan semua</button>
                            </div>
                            <span class="dsb-kartu-sub d-block mi-cepat-ket">
                                Dihitung dari <strong>harga hari biasa</strong>, dibulatkan ke ribuan ke atas.
                                Hanya baris yang sedang terlihat yang terisi.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR HARGA ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="filterKategori,filterProvinsi,cari">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-cash-coin"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Tarif musim</h3>
                                <span class="dsb-kartu-sub">
                                    {{ $rekap['terisi'] }} dari {{ count($calon) }} harga terisi.
                                    @if ($rekap['turun'])
                                        {{-- Tarif musim yang lebih MURAH hampir selalu salah ketik:
                                             nol yang kurang, atau angka yang tertukar dengan harga
                                             biasa. Disebut di sini sebelum disimpan. --}}
                                        <strong class="mi-tegas">{{ $rekap['turun'] }} lebih murah daripada harga biasa — periksa lagi.</strong>
                                    @elseif ($rekap['naik'])
                                        {{ $rekap['naik'] }} di atas harga biasa.
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    @if (count($calon) === 0)
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-search"></i></span>
                                <p class="dsb-kosong-judul">
                                    {{ $this->adaSaringan() ? 'Tidak ada yang cocok saringan' : 'Belum ada harga aktif' }}
                                </p>
                                <p class="dsb-kosong-ket">
                                    @if ($this->adaSaringan())
                                        Coba bersihkan saringannya.
                                    @else
                                        Tambahkan dulu baris harga di
                                        <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate>Master Harga</a>.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel mi-tabel">
                                <thead>
                                    <tr>
                                        <th>Harga</th>
                                        <th>Hari biasa</th>
                                        <th>Akhir pekan</th>
                                        <th>Tarif musim ini</th>
                                        <th>Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Angka sudah DIHITUNG di komponen.

                                         Perbandingan di dalam blok @php menaruh ">"
                                         di tempat yang membuat penjaga penanda morph
                                         Livewire kehilangan hitungan pasangan
                                         @foreach-nya — dan akibatnya di peramban
                                         adalah bagian halaman yang tidak ikut
                                         diperbarui, tanpa satu pun galat. --}}
                                    @foreach ($siap as $b)
                                        <tr wire:key="mi-{{ $b['id'] }}" class="{{ $b['ada'] ? 'is-terisi' : '' }}">
                                            <td data-judul="Harga">
                                                <span class="mi-utama">
                                                    <span class="dsb-tabel-judul">{{ $b['nama'] }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        {{ $b['kategori_label'] }}
                                                        @if ($b['vendor']) &middot; {{ $b['vendor'] }} @endif
                                                        &middot; {{ $b['satuan_label'] }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Hari biasa">
                                                <span class="dsb-tabel-meta">{{ $b['harga_teks'] }}</span>
                                            </td>
                                            <td data-judul="Akhir pekan">
                                                <span class="dsb-tabel-meta">{{ $b['akhir_pekan_teks'] }}</span>
                                            </td>
                                            <td data-judul="Tarif musim ini">
                                                {{-- value DITULIS, walau wire:model sudah ada.

                                                     Livewire mengisi kotaknya lewat JS sesudah
                                                     halaman hidup; sampai saat itu lima puluh
                                                     kotak di layar ini terlihat KOSONG padahal
                                                     angkanya sudah tersimpan — dan yang mulai
                                                     mengetik di detik itu menimpa tarif yang
                                                     belum sempat muncul. --}}
                                                <div class="orcha-rupiah mi-kotak">
                                                    <input type="text" inputmode="numeric"
                                                        wire:model.blur="nilai.{{ $b['id'] }}"
                                                        value="{{ $nilai[$b['id']] ?? '' }}"
                                                        class="orcha-uang dsb-isian" placeholder="tarif biasa">
                                                </div>
                                            </td>
                                            <td data-judul="Selisih">
                                                @if ($b['arah'] === 'naik')
                                                    <span class="dsb-lencana" style="--c: var(--dsb-aman)">
                                                        <i class="bi bi-arrow-up"></i>{{ $b['persen_teks'] }}
                                                    </span>
                                                @elseif ($b['arah'] === 'turun')
                                                    <span class="dsb-lencana" style="--c: var(--dsb-bahaya)">
                                                        <i class="bi bi-arrow-down"></i>{{ $b['persen_teks'] }}
                                                    </span>
                                                @elseif ($b['arah'] === 'sama')
                                                    <span class="dsb-lencana" style="--c: var(--dsb-netral)">
                                                        <i class="bi bi-dash"></i>sama
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-meta">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="mi-kaki">
                                <span class="dsb-kartu-sub mi-kaki-ket">
                                    Mengosongkan kotak yang sudah terisi <strong>menghapus</strong> tarif musimnya.
                                    Nol tetap tersimpan sebagai harga nol — keduanya bukan hal yang sama.
                                </span>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-utama">
                                    <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                                    <span>Simpan Tarif</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- <style> telanjang: layout templateindex tidak punya stack, jadi gaya
         yang didorong ke sana tidak pernah tergambar. --}}
    <style>
        .mi-saring { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
        .mi-medan-cari { flex: 1 1 220px; min-width: 0; }
        .mi-medan-pilih { flex: 0 1 190px; min-width: 0; }

        .mi-cepat { margin-top: 14px; padding-top: 13px; border-top: 1px solid var(--dsb-tepi, #e9edf3); }
        .mi-cepat-label { display: block; }
        .mi-cepat-rak { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
        .mi-cepat-ket { margin-top: 7px; }
        .mi-chip {
            padding: 6px 13px; border-radius: 999px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .78rem; font-weight: 600;
            white-space: nowrap;
        }
        .mi-chip:hover { border-color: #c7d2fe; }
        .mi-chip.is-bahaya { color: var(--dsb-bahaya, #b42318); border-color: #f3c7c2; }
        .mi-chip.is-bahaya:hover { border-color: #e4867c; }

        .mi-utama { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .mi-kotak { max-width: 190px; }
        .mi-cepat-sub { margin-top: 2px; }
        .mi-tegas { display: block; margin-top: 3px; color: var(--dsb-awas, #b45309); }
        /* Baris yang sudah terisi diberi penanda tipis: dari dua puluh baris,
           yang belum diisi harus bisa ditemukan tanpa membaca tiap kotak. */
        .mi-tabel tr.is-terisi { background: #f7fbf8; }

        .mi-kaki { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .mi-kaki-ket { flex: 1 1 260px; min-width: 0; line-height: 1.5; }

        @media (max-width: 720px) {
            .mi-kotak { max-width: 100%; }
        }
    </style>
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
