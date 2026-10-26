@section('title')
Vendor || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         master harga dan daftar RAB. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlahData = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = $this->adaSaringan();
        $formTerbuka = $tambah || $sunting;
        $jenisKini = trim((string) ($isian['jenis'] ?? ''));

        /*
         | Warna & ikon per jenis vendor. Sama alasannya dengan kategori di
         | master harga: lencana abu yang semuanya sama menuntut tulisannya
         | dibaca satu per satu, padahal yang dicari mata cuma "mana yang
         | armada".
         */
        $rupaJenis = [
            'Armada' => ['#0369a1', 'bi-bus-front'],
            'Akomodasi' => ['#6d28d9', 'bi-building'],
            'Konsumsi' => ['#e0912f', 'bi-cup-hot'],
            'Tiket' => ['#1f7a44', 'bi-ticket-perforated'],
            'Pemandu' => ['#0e7490', 'bi-person-badge'],
        ];
        $rupaDari = fn ($j) => $rupaJenis[$j] ?? ['#64748b', 'bi-shop'];

        // Dihitung Orcha dari SELURUH tabel, bukan dari halaman ini.
        $ringkas = $meta['ringkas'] ?? [];
        $tanpaKontak = (int) ($ringkas['tanpa_kontak'] ?? 0);
        $nonaktif = (int) ($ringkas['nonaktif'] ?? 0);
        $jenisAda = collect($ringkas['jenis'] ?? [])->filter()->values();

        // Jenis yang diketik sendiri ditandai, sama seperti destinasi di luar
        // katalog pada master harga: nilai di luar daftar harus terlihat
        // sebagai di luar daftar.
        $jenisAsing = $jenisKini !== '' && ! in_array($jenisKini, $pilihanJenis, true);
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Vendor</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Dari siapa harga dibeli, dan ke nomor mana meneleponnya.</span>
                    <span class="d-block">Nomor yang berganti cukup diubah di sini — seluruh harga yang memakainya ikut benar.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate class="dsb-tombol is-awas">
                    <i class="bi bi-tags"></i><span>Master Harga</span>
                </a>
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-utama">
                    <i class="bi bi-calculator"></i><span>RAB &amp; Itinerary</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="vd-saring">
                            <div class="vd-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari nama vendor atau nomornya…',
                                ])
                            </div>

                            <select class="dsb-isian vd-pilih" wire:model.live="filterJenis" aria-label="Saring menurut jenis">
                                <option value="">Semua jenis</option>
                                <option value="-">Belum berjenis</option>
                                @foreach ($jenisAda as $j)
                                    <option value="{{ $j }}">{{ $j }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian vd-pilih" wire:model.live="filterProvinsi" aria-label="Saring menurut wilayah">
                                <option value="">Semua wilayah</option>
                                <option value="-">Tanpa wilayah</option>
                                @foreach ($provinsiAda as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian vd-pilih" wire:model.live="filterStatus2" aria-label="Saring menurut status">
                                <option value="">Aktif &amp; nonaktif</option>
                                <option value="aktif">Hanya aktif</option>
                                <option value="nonaktif">Hanya nonaktif</option>
                            </select>

                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama">
                                <i class="bi bi-plus-lg"></i><span>Vendor Baru</span>
                            </button>

                            {{-- Tautan biasa, bukan aksi Livewire: berkasnya dirakit
                                 Orcha dan diteruskan apa adanya, jadi tidak ada yang
                                 perlu ditunggu di layar ini.

                                 Saringannya ikut di alamatnya — berkas yang isinya
                                 berbeda dari yang terlihat di layar adalah berkas
                                 yang menyesatkan. --}}
                            <a href="{{ route('admin.orcha.vendor.pdf', [
                                    'cari' => $cari, 'jenis' => $filterJenis,
                                    'status' => $filterStatus2, 'provinsi' => $filterProvinsi,
                                ]) }}"
                                class="dsb-tombol is-telaah" title="Unduh daftar vendor sesuai saringan">
                                <i class="bi bi-file-earmark-pdf"></i><span>PDF</span>
                            </a>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-awas"
                                    title="Kembali menampilkan seluruh vendor">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== FORMULIR ================== --}}
        @if ($formTerbuka)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-shop"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Vendor' : 'Vendor Baru' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Nomornya dibaca <strong>hidup</strong> — diubah di sini, RAB lama pun ikut menunjuk nomor yang baru.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            @include('livewire.pages.admin.orcha.vendor.partials.medan')

                            <div class="vd-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-utama">
                                    <i class="bi bi-check2-circle" wire:loading.remove wire:target="simpan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                                    <span>Simpan</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ========= KONTAK LAMA YANG MASIH TERCECER =========

             Sebelum ada daftar ini, nomor ditulis di kolom catatan master
             harga — dan catatan itu tidak pernah ikut ke RAB. Sisa data lama
             tidak akan pindah sendiri; yang tidak terlihat tidak dikerjakan. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    @if (! $bukaTercecer)
                        <div class="dsb-kartu-isi vd-tercecer-ajak">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)"><i class="bi bi-box-seam"></i></span>
                            <div class="vd-tercecer-teks">
                                <strong>Nomor lama di kolom catatan</strong>
                                <span class="dsb-kartu-sub">Sebelum ada daftar vendor, nomor ditulis di catatan master harga — dan catatan tidak pernah ikut ke RAB.</span>
                            </div>
                            <button type="button" wire:click="lihatTercecer" wire:loading.attr="disabled" wire:target="lihatTercecer"
                                class="dsb-tombol is-awas">
                                <i class="bi bi-search" wire:loading.remove wire:target="lihatTercecer"></i>
                                <span class="dsb-putar" wire:loading wire:target="lihatTercecer"></span>
                                <span>Periksa</span>
                            </button>
                        </div>
                    @else
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)"><i class="bi bi-box-seam"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Nomor lama di kolom catatan</h3>
                                    <span class="dsb-kartu-sub">Jadikan vendor supaya nomornya ikut ke rincian biaya RAB.</span>
                                </div>
                            </div>
                            <button type="button" wire:click="$set('bukaTercecer', false)" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        <div class="dsb-kartu-isi">
                            @if ($tercecer === [])
                                {{-- Sesudah bersih, panelnya menutup diri. Panel yang
                                     menawarkan diri selamanya memaksa admin menekan
                                     "Periksa" hanya untuk diberi tahu tidak ada apa-apa. --}}
                                <div class="vd-tercecer-bersih">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Tidak ada nomor yang tertinggal di catatan master harga.</span>
                                    <button type="button" wire:click="$set('bukaTercecer', false)"
                                        class="dsb-tombol is-aman">
                                        <i class="bi bi-check2"></i><span>Selesai</span>
                                    </button>
                                </div>
                            @else
                                {{-- Centang beberapa baris yang vendornya sama, lalu
                                     jadikan SATU vendor sekaligus. Hiace, Elf, dan bus
                                     besar biasanya dari pemilik armada yang sama;
                                     menjadikannya satu per satu berarti mengulang
                                     keputusan yang sama lalu menggabungkannya
                                     belakangan. --}}
                                @if ($pilihTercecer !== [])
                                    <div class="vd-tercecer-bilah">
                                        <span><strong>{{ count($pilihTercecer) }}</strong> baris dicentang</span>
                                        <button type="button" wire:click="dariBeberapaCatatan"
                                            class="dsb-tombol is-aman">
                                            <i class="bi bi-arrow-right-circle"></i><span>Jadikan satu vendor</span>
                                        </button>
                                        <button type="button" wire:click="$set('pilihTercecer', [])"
                                            class="dsb-tombol is-lembut">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>
                                    </div>
                                @endif

                                <div class="vd-tercecer-deret">
                                    @foreach ($tercecer as $t)
                                        <label class="vd-tercecer-baris" wire:key="tc-{{ $t['id'] }}">
                                            <input type="checkbox" class="form-check-input mt-0"
                                                wire:model.live="pilihTercecer" value="{{ $t['id'] }}">
                                            <span class="vd-tercecer-isi">
                                                <strong>{{ $t['nama'] }}</strong>
                                                <span class="dsb-tabel-meta">{{ $t['catatan'] }}</span>
                                            </span>
                                            <button type="button" wire:click="dariCatatan({{ json_encode($t['catatan']) }})"
                                                class="dsb-tombol is-aman">
                                                <i class="bi bi-arrow-right-circle"></i><span>Jadikan vendor</span>
                                            </button>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-shop"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Daftar vendor</h3>
                                <span class="dsb-kartu-sub">Nomornya ikut ke rincian biaya RAB dan PDF internal.</span>
                            </div>
                        </div>
                        <span class="vd-chip-deret">
                            <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} vendor</span>
                            {{-- Dua angka yang sebelumnya harus dipindai baris per
                                 baris. Keduanya hanya muncul bila memang ada, dan
                                 keduanya bisa diklik — angka yang menunjuk pekerjaan
                                 tetapi tidak membawa ke sana cuma menambah beban. --}}
                            @if ($tanpaKontak > 0)
                                <button type="button" wire:click="$set('filterStatus2', '')" class="dsb-chip vd-chip-awas"
                                    title="Vendor yang belum punya nomor">
                                    <i class="bi bi-exclamation-triangle-fill"></i>{{ $tanpaKontak }} tanpa nomor
                                </button>
                            @endif
                            @if ($nonaktif > 0)
                                <button type="button" wire:click="$set('filterStatus2', 'nonaktif')" class="dsb-chip vd-chip-mati"
                                    title="Lihat vendor yang dimatikan">
                                    <i class="bi bi-pause-circle"></i>{{ $nonaktif }} nonaktif
                                </button>
                            @endif
                        </span>
                    </div>

                    {{-- ===== Menggabungkan dua baris yang ternyata sama =====

                         Pemeriksaan kembar saat menambah hanya menangkap nama yang
                         PERSIS sama sesudah dirapikan, jadi "PO Sumber Jaya" dan
                         "Sumber Jaya" lolos jadi dua baris. Begitu terlanjur,
                         keduanya membelah kontaknya — dan tanpa jalan ini
                         satu-satunya cara menyatukannya adalah membuka tiap harga
                         lalu memindahkannya satu per satu. --}}
                    @if ($gabungDari)
                        @php($asal = collect($daftar)->firstWhere('id', $gabungDari))
                        <div class="dsb-kartu-isi">
                            <div class="vd-gabung">
                                <div class="vd-gabung-judul">
                                    <i class="bi bi-union"></i>
                                    Gabungkan <strong>{{ $asal['nama'] ?? 'vendor ini' }}</strong> ke vendor lain
                                </div>

                                <select wire:model="gabungKe" class="dsb-isian @error('gabungKe') is-galat @enderror">
                                    <option value="">Pilih vendor tujuan…</option>
                                    @foreach ($daftar as $v)
                                        @if ($v['id'] !== $gabungDari)
                                            <option value="{{ $v['id'] }}">{{ $v['nama'] }}@if ($v['kontak']) — {{ $v['kontak'] }}@endif</option>
                                        @endif
                                    @endforeach
                                </select>

                                <button type="button" wire:click="gabungkan" wire:loading.attr="disabled" wire:target="gabungkan"
                                    class="dsb-tombol is-awas">
                                    <i class="bi bi-union" wire:loading.remove wire:target="gabungkan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="gabungkan"></span>
                                    <span>Gabungkan</span>
                                </button>
                                <button type="button" wire:click="tutupGabung" class="dsb-tombol is-lembut">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>

                                @error('gabungKe')
                                    <span class="dsb-galat vd-gabung-kaki">{{ $message }}</span>
                                @else
                                    <span class="dsb-kartu-sub vd-gabung-kaki">
                                        Seluruh harga &amp; biaya RAB yang menunjuk <strong>{{ $asal['nama'] ?? 'vendor ini' }}</strong>
                                        dipindahkan, lalu barisnya dihapus. Harganya sendiri tidak disentuh.
                                    </span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-shop"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada vendor' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba kata kunci lain — nama vendornya atau sebagian nomornya.'
                                        : 'Mulai dari pemilik armada yang paling sering dipakai.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel vd-tabel">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Jenis</th>
                                        <th>Kontak</th>
                                        <th class="text-end">Dipakai</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php([$warnaJ, $ikonJ] = $rupaDari($baris['jenis']))
                                        @php($nomorTel = $this->nomorDari($baris['kontak']))
                                        @php($nomorWa = $this->nomorWa($baris['kontak']))
                                        <tr wire:key="vd-{{ $baris['id'] }}" class="{{ $baris['aktif'] ? '' : 'is-mati' }}">
                                            <td data-judul="Vendor">
                                                <span class="vd-utama">
                                                    <a href="{{ route('admin.orcha.vendor.detail', $baris['id']) }}" wire:navigate
                                                        class="dsb-tabel-judul vd-tautan">{{ $baris['nama'] }}</a>
                                                    @if (! empty($baris['provinsi']))
                                                        <span class="dsb-tabel-meta"><i class="bi bi-geo-alt"></i> {{ $baris['provinsi'] }}</span>
                                                    @endif
                                                    @if ($baris['catatan'])
                                                        <span class="dsb-tabel-meta vd-catatan">{{ $baris['catatan'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Jenis">
                                                @if ($baris['jenis'])
                                                    <span class="dsb-lencana" style="--c: {{ $warnaJ }}">
                                                        <i class="bi {{ $ikonJ }}"></i>{{ $baris['jenis'] }}
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-meta">—</span>
                                                @endif
                                            </td>
                                            <td data-judul="Kontak">
                                                @if ($baris['kontak'])
                                                    <span class="vd-kontak">
                                                        <span class="vd-kontak-teks">
                                                            {{ $baris['kontak'] }}
                                                            @if ($this->nomorJanggal($baris['kontak']))
                                                                {{-- Bukan penolakan: nomor kantor memang
                                                                     berbeda panjang dari seluler. Tetapi
                                                                     salah ketik satu digit tersimpan
                                                                     dengan yakin, dan tombolnya mengarah
                                                                     ke nomor orang lain tanpa tanda. --}}
                                                                <span class="vd-nomor-janggal" title="Panjang nomornya di luar kebiasaan — mungkin ada digit yang terlewat">
                                                                    <i class="bi bi-question-circle"></i>periksa lagi
                                                                </span>
                                                            @endif
                                                        </span>
                                                        {{-- Tombolnya hanya muncul bila ada deretan
                                                             angka yang cukup panjang untuk sebuah
                                                             nomor. Tombol yang selalu ada lalu
                                                             menelepon entah ke mana lebih buruk
                                                             daripada tidak ada tombol. --}}
                                                        @if ($nomorWa)
                                                            <span class="vd-hubungi">
                                                                <a href="https://wa.me/{{ $nomorWa }}" target="_blank" rel="noopener"
                                                                    class="dsb-tabel-btn" style="--c: var(--dsb-aman)" title="WhatsApp {{ $baris['nama'] }}">
                                                                    <i class="bi bi-whatsapp"></i>
                                                                </a>
                                                                <a href="tel:{{ $nomorTel }}" class="dsb-tabel-btn" style="--c: var(--dsb-telaah)"
                                                                    title="Telepon {{ $baris['nama'] }}">
                                                                    <i class="bi bi-telephone"></i>
                                                                </a>
                                                            </span>
                                                        @endif
                                                    </span>
                                                @else
                                                    {{-- Vendor tanpa nomor adalah vendor yang
                                                         tetap harus dicari saat dibutuhkan —
                                                         jadi ketiadaannya ditandai, bukan
                                                         dibiarkan kosong. --}}
                                                    <span class="vd-kosong">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>belum ada nomor
                                                    </span>
                                                @endif
                                            </td>
                                            <td data-judul="Dipakai" class="text-end">
                                                @if ($baris['jumlah_harga'] > 0)
                                                    {{-- Angka yang menunjuk sesuatu tetapi tidak
                                                         membawa ke sana cuma menambah beban. --}}
                                                    <a href="{{ route('admin.orcha.master-harga', ['vendor' => $baris['id']]) }}" wire:navigate
                                                        class="vd-dipakai" title="Lihat harga milik {{ $baris['nama'] }}">
                                                        {{ $baris['jumlah_harga'] }} harga
                                                    </a>
                                                @else
                                                    <span class="vd-dipakai-nol" title="Belum dipakai harga mana pun">belum dipakai</span>
                                                @endif
                                            </td>
                                            <td data-judul="Status">
                                                @if ($baris['aktif'])
                                                    <span class="dsb-lencana" style="--c: var(--dsb-aman)"><i class="bi bi-check-circle"></i>Aktif</span>
                                                @else
                                                    <span class="dsb-lencana" style="--c: var(--dsb-netral)"><i class="bi bi-pause-circle"></i>Dimatikan</span>
                                                    @if ($baris['jumlah_harga'] > 0)
                                                        {{-- Dimatikan tetapi masih dipakai: kontaknya
                                                             tetap terbaca di RAB, hanya tidak bisa
                                                             dipilih untuk harga baru. Bukan salah,
                                                             tetapi pantas diketahui. --}}
                                                        <span class="vd-masih-dipakai" title="Masih dipakai {{ $baris['jumlah_harga'] }} harga">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>masih dipakai
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="dsb-tabel-aksi">
                                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-utama)"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                        title="Ubah vendor ini">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    {{-- Ikon union tidak langsung terbaca artinya,
                                                         jadi yang dipakai panah-menyatu plus
                                                         judul yang menyebut maksudnya. --}}
                                                    {{-- Biru, bukan jingga: menggabungkan MENATA, tidak
                                                         menahan apa pun. Harga & biaya RAB-nya selamat;
                                                         yang berpindah cuma ke siapa ia menunjuk. Sewarna
                                                         dengan "matikan" di sebelahnya, keduanya terbaca
                                                         sebagai peringatan yang sama. --}}
                                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-telaah)"
                                                        wire:click="bukaGabung({{ $baris['id'] }})"
                                                        title="Gabungkan ke vendor lain — untuk dua baris yang ternyata orang yang sama">
                                                        <i class="bi bi-arrows-angle-contract"></i>
                                                    </button>
                                                    @if ($baris['jumlah_harga'] > 0 && $baris['aktif'])
                                                        {{-- Yang hampir selalu dimaksud admin adalah
                                                             "jangan tawarkan lagi", dan itu namanya
                                                             mematikan. Menghapusnya menghilangkan
                                                             kontak dari seluruh RAB yang memakainya
                                                             tanpa bisa dibatalkan. --}}
                                                        <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: var(--dsb-awas)"
                                                            data-action="matikan" data-arg="{{ $baris['id'] }}"
                                                            data-title="Matikan {{ $baris['nama'] }}?"
                                                            data-text="Tidak ditawarkan lagi untuk harga baru. Kontaknya tetap terbaca di {{ $baris['jumlah_harga'] }} harga yang sudah memakainya."
                                                            data-confirm="Ya, matikan" data-icon="question"
                                                            title="Matikan vendor ini">
                                                            <i class="bi bi-pause-circle"></i>
                                                        </button>
                                                    @else
                                                        <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: var(--dsb-bahaya)"
                                                            data-action="{{ $baris['jumlah_harga'] > 0 ? 'hapusPaksa' : 'hapus' }}"
                                                            data-arg="{{ $baris['id'] }}"
                                                            data-title="Hapus {{ $baris['nama'] }}?"
                                                            data-text="{{ $baris['jumlah_harga'] > 0
                                                                ? $baris['jumlah_harga'].' harga akan kehilangan kontaknya. Harganya sendiri tetap ada, dan ini tidak bisa dibatalkan.'
                                                                : 'Vendor ini belum dipakai harga mana pun.' }}"
                                                            data-confirm="Ya, hapus" data-icon="warning"
                                                            title="Hapus vendor ini">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="vd-halaman">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .vd-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        /* Enam benda di satu baris: kotak cari yang mengalah duluan.

           Dengan basis 260px ia menolak menyempit, tombol terakhir terdorong
           ke baris kedua, dan barisnya menyisakan tiga perempat ruang kosong
           untuk satu tombol. */
        .vd-saring > .vd-medan-cari { flex: 1 1 180px; min-width: 0; }
        .vd-saring .dsb-tombol { flex: 0 0 auto; }

        /* Kotak cari bersama memakai .form-control Bootstrap; yang disamakan
           rupanya saja — markup & perilakunya tetap milik partial. */
        .vd-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; box-shadow: none;
        }
        .vd-saring .form-control:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .vd-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        .vd-medan-rak { display: flex; flex-wrap: wrap; gap: 14px 16px; align-items: flex-start; }
        .vd-medan-rak > .dsb-medan { flex: 1 1 240px; min-width: 0; }
        .vd-medan-rak > .dsb-medan.is-lebar { flex-basis: 100%; }
        .vd-medan-rak .dsb-kartu-sub,
        .vd-medan-rak .dsb-galat { display: block; margin-top: 5px; }
        .vd-medan-rak .dsb-label-ket { margin-left: 4px; font-weight: 500; }

        /* Pilihan jenis: chip melar berbagi baris dengan kotak ketiknya, jadi
           barisnya habis terpakai berapa pun jumlah chipnya. */
        .vd-jenis { display: flex; flex-wrap: wrap; gap: 6px; }
        .vd-jenis-chip {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            flex: 1 1 120px; padding: 8px 11px; border-radius: 10px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .79rem; font-weight: 600; white-space: nowrap;
        }
        .vd-jenis-chip i { color: var(--c, #64748b); }
        .vd-jenis-chip:hover { border-color: #c7d2fe; }
        .vd-jenis-chip.is-aktif { border-color: var(--c, #4f46e5); background: #f8fafc; box-shadow: 0 0 0 3px rgba(99, 102, 241, .08); }
        .vd-jenis-lain { display: flex; align-items: center; gap: 8px; margin-top: 7px; }
        .vd-jenis-lain .dsb-kartu-sub { flex: 0 0 auto; }
        .vd-jenis-isi { flex: 1 1 auto; min-width: 0; }

        .vd-sakelar { margin-top: 14px; }
        .vd-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }

        .vd-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .vd-catatan { font-style: italic; }
        /* Teks melar, tombolnya menempel di kanan kolom.

           Sebelumnya tombol mengikuti akhir teks kontaknya, jadi posisinya
           berbeda-beda tiap baris dan mata harus mencari tombol yang
           seharusnya selalu di tempat yang sama. */
        .vd-kontak {
            display: flex; align-items: center; gap: 8px;
            font-size: .84rem; font-weight: 600; color: var(--dsb-tinta, #1c1f26);
        }
        .vd-kontak .vd-kontak-teks { flex: 1 1 auto; }
        .vd-kosong {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .76rem; font-weight: 600; color: #b45309;
        }

        .vd-saring > .vd-pilih { flex: 0 1 auto; width: auto; min-width: 128px; max-width: 176px; }

        /* Ringkasan di kepala kartu: dua angka yang sebelumnya harus dipindai
           baris per baris, dan keduanya membawa ke tempatnya. */
        .vd-chip-deret { display: flex; flex-wrap: wrap; gap: 6px; }
        .vd-chip-awas { border-color: #fde68a !important; background: #fffbeb !important; color: #92400e !important; cursor: pointer; }
        .vd-chip-mati { cursor: pointer; }

        /* Kontak lama yang masih tercecer di catatan. */
        .vd-tercecer-ajak { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
        .vd-tercecer-teks { display: flex; flex-direction: column; gap: 2px; flex: 1 1 260px; min-width: 0; }
        .vd-tercecer-teks strong { font-size: .9rem; color: var(--dsb-tinta, #1c1f26); }
        .vd-tercecer-bersih {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            padding: 11px 14px; border-radius: 12px;
            background: #f6fbf8; border: 1px solid #d1fae5; color: #047857; font-size: .84rem; font-weight: 600;
        }
        .vd-tercecer-bersih > span { flex: 1 1 240px; }
        .vd-tercecer-deret { display: flex; flex-direction: column; gap: 6px; }
        .vd-tercecer-baris {
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            padding: 9px 12px; border-radius: 11px;
            background: #fffbeb; border: 1px solid #fde68a;
        }
        .vd-tercecer-isi { display: flex; flex-direction: column; gap: 1px; flex: 1 1 240px; min-width: 0; }
        .vd-tercecer-baris { cursor: pointer; }
        .vd-tercecer-baris .form-check-input { flex: 0 0 auto; }
        .vd-tercecer-bilah {
            display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;
            padding: 9px 12px; border-radius: 11px;
            background: #f6fbf8; border: 1px solid #d1fae5; color: #047857; font-size: .82rem; font-weight: 600;
        }
        .vd-tercecer-bilah > span { flex: 1 1 160px; }
        .vd-tercecer-isi strong { font-size: .85rem; color: var(--dsb-tinta, #1c1f26); }

        /* Penggabungan: sebaris penuh, tombolnya tidak jatuh sendirian. */
        .vd-gabung {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            padding: 12px 14px; border-radius: 13px;
            background: #fffbeb; border: 1px solid #fde68a;
        }
        .vd-gabung-judul {
            flex: 1 1 100%; display: flex; align-items: center; gap: 7px;
            font-size: .84rem; font-weight: 700; color: #92400e;
        }
        .vd-gabung .dsb-isian { flex: 1 1 240px; min-width: 0; background: #fff; }
        .vd-gabung .dsb-tombol { flex: 0 0 auto; background: #fff; }
        .vd-gabung-kaki { flex: 1 1 100%; margin-top: 0 !important; }

        /* Baris nonaktif diredupkan: lencana saja membuat barisnya tetap
           tampak sepenting yang masih dipakai. */
        .vd-tabel tbody tr.is-mati { background: #fbfcfd; }
        .vd-tabel tbody tr.is-mati .dsb-tabel-judul,
        .vd-tabel tbody tr.is-mati .vd-kontak-teks { color: var(--dsb-redup, #6b7280); }
        .vd-masih-dipakai {
            display: inline-flex; align-items: center; gap: 4px; margin-top: 3px;
            font-size: .72rem; font-weight: 700; color: #b45309;
        }

        .vd-kontak-teks { min-width: 0; }
        .vd-hubungi { display: inline-flex; gap: 4px; flex-shrink: 0; }
        .vd-dipakai { font-weight: 700; font-size: .84rem; color: #4f46e5; text-decoration: none; }
        .vd-dipakai:hover { text-decoration: underline; }
        .vd-dipakai-nol { font-size: .78rem; color: var(--dsb-redup, #6b7280); }
        .vd-tautan { text-decoration: none; }
        .vd-tautan:hover { color: #4338ca; }
        .vd-nomor-janggal {
            display: inline-flex; align-items: center; gap: 4px; margin-left: 6px;
            padding: 1px 7px 2px; border-radius: 7px;
            background: #fffbeb; border: 1px solid #fde68a;
            color: #92400e; font-size: .68rem; font-weight: 700;
        }
        .vd-jenis-asing { display: flex; align-items: flex-start; gap: 6px; margin-top: 6px; }

        .vd-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
        .vd-halaman > * { margin-bottom: 0 !important; margin-top: 0 !important; }

        @media (max-width: 575.98px) {
            .vd-medan-rak .dsb-label-ket { display: block; margin-left: 0; margin-top: 2px; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            .vd-tabel { min-width: 0; }
            .vd-tabel thead { display: none; }
            .vd-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .vd-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .vd-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            .vd-tabel tbody td > * { min-width: 0; }
            .vd-utama { align-items: flex-end; text-align: right; }

            /* Tiga baris berlabel untuk isi sependek "3 harga" dan "Aktif"
               membuat satu kartu vendor setinggi tiga kali perlunya. Ketiganya
               dipadatkan jadi satu baris kaki tanpa label. */
            .vd-tabel tbody tr {
                display: grid; grid-template-columns: 1fr auto; gap: 2px 10px; align-items: center;
            }
            .vd-tabel tbody td[data-judul="Vendor"] { grid-column: 1 / -1; }
            .vd-tabel tbody td[data-judul="Jenis"],
            .vd-tabel tbody td[data-judul="Kontak"] { grid-column: 1 / -1; }
            .vd-tabel tbody td[data-judul="Dipakai"],
            .vd-tabel tbody td[data-judul="Status"] { justify-content: flex-start; }
            .vd-tabel tbody td[data-judul="Dipakai"] { grid-column: 1; }
            .vd-tabel tbody td[data-judul="Status"] { grid-column: 1; }
            .vd-tabel tbody td[data-judul="Aksi"] { grid-column: 2; grid-row: 4 / 6; justify-content: flex-end; }
            .vd-tabel tbody td[data-judul="Dipakai"]::before,
            .vd-tabel tbody td[data-judul="Status"]::before,
            .vd-tabel tbody td[data-judul="Aksi"]::before { content: none; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
