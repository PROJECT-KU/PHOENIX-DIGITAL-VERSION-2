@section('title')
Master Harga || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         daftar RAB, penyusun, dan jejak audit. Kelas orcha-* hanya untuk
         hal yang memang khas Orcha (sakelar kartu, isian rupiah, paginasi). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')
    @include('livewire.pages.admin.orcha.partials.select-cari')

    @php
        $jumlahData = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = $this->adaSaringan();
        $formTerbuka = $tambah || $sunting;

        $perluKapasitas = $this->perluKapasitas();
        $sebutan = $this->sebutanUnit();
        $pilihanMuat = $this->pilihanKapasitas();
        $muatKini = (int) ($isian['kapasitas'] ?? 0);
        $namaRakitan = $this->namaOtomatis();
        $namaKini = trim((string) ($isian['nama'] ?? ''));
        $destinasiKini = trim((string) ($isian['destinasi'] ?? ''));

        /*
         | Warna & ikon per kategori biaya.
         |
         | Sepuluh kategori yang semuanya berlencana abu sama saja dengan tidak
         | berlencana: matanya harus membaca tulisannya satu per satu. Dengan
         | warna, "mana saja yang akomodasi" terjawab tanpa dibaca.
         |
         | Birunya #0369a1 — biru merek lama dilarang di layar Orcha
         | (OrchaTampilanSeragamTest), dan satu tempat yang terlewat cukup
         | untuk meninggalkan biru asing di tengah halaman ungu.
         */
        $rupaKategori = [
            'transportasi' => ['#0369a1', 'bi-bus-front'],
            'akomodasi' => ['#6d28d9', 'bi-building'],
            'konsumsi' => ['#e0912f', 'bi-cup-hot'],
            'tiket' => ['#1f7a44', 'bi-ticket-perforated'],
            'pemandu' => ['#0e7490', 'bi-person-badge'],
            'asuransi' => ['#be123c', 'bi-shield-check'],
            'dokumentasi' => ['#b45309', 'bi-camera'],
            'operasional' => ['#4f46e5', 'bi-briefcase'],
            'sopir' => ['#a16207', 'bi-person-gear'],
            'lainnya' => ['#64748b', 'bi-three-dots'],
        ];
        $rupaDari = fn ($kunci) => $rupaKategori[$kunci] ?? $rupaKategori['lainnya'];

        /*
         | Nama destinasi yang tidak ada di katalog.
         |
         | Penjodohan harga tiket ke itinerary memakai nama ini persis. Satu
         | huruf berbeda ("prambanan" alih-alih "Candi Prambanan") membuat
         | tiketnya tidak pernah ikut tertarik, dan RAB-nya kekurangan modal
         | tanpa satu pun tanda — kesalahan yang baru ketahuan saat uangnya
         | kurang. Jadi barisnya ditandai di layar, bukan dibiarkan diam.
         */
        $lepasDariKatalog = fn ($nama) => $nama && ! in_array($nama, $namaKatalog, true);
        $destinasiAsing = $lepasDariKatalog($destinasiKini);

        // Destinasi hanya ditawarkan untuk tiket — tetapi tetap ditampilkan
        // bila nilainya SUDAH ada, supaya mengganti kategori tidak diam-diam
        // menyembunyikan data yang masih tersimpan.
        $medanDestinasi = ($isian['kategori'] ?? '') === 'tiket' || $destinasiKini !== '';
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">Master Harga</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Harga tiket, armada, hotel, dan pemandu — ditulis sekali, dipakai seluruh RAB.</span>
                    <span class="d-block">Yang diisi di sini tidak perlu diketik ulang pada tiap penawaran.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-utama">
                    <i class="bi bi-calculator"></i><span>RAB &amp; Itinerary</span>
                </a>
                <a href="{{ route('admin.orcha.dashboard') }}" wire:navigate class="dsb-tombol is-telaah">
                    <i class="bi bi-speedometer2"></i><span>Dashboard Orcha</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ==================
             Satu baris, bukan empat kolom berjarak: kotak cari memanjang
             mengisi sisa lebarnya, jadi tidak ada ruang menganggur di
             kanan barisnya. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="mh-saring">
                            <div class="mh-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari nama, destinasi, atau daerah…',
                                ])
                            </div>

                            <select class="dsb-isian mh-pilih" wire:model.live="filterKategori" aria-label="Saring menurut kategori">
                                <option value="">Semua kategori</option>
                                @foreach ($kategori as $kunci => $label)
                                    <option value="{{ $kunci }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            {{-- wire:key memuat NILAINYA juga, bukan cuma daftarnya:
                                 di dalam wire:ignore, nilai yang diubah dari server
                                 (mis. "Tampilkan semua") tidak akan tercermin kecuali
                                 nodenya diganti utuh. --}}
                            <div class="mh-pilih" wire:key="f-prov-{{ $filterProvinsi }}" wire:ignore
                                x-data x-init="pasangSelectCari($el, 'filterProvinsi', { placeholder: 'Semua provinsi' })">
                                <select class="dsb-isian" aria-label="Saring menurut provinsi">
                                    <option value="">Semua provinsi</option>
                                    <option value="-" @selected($filterProvinsi === '-')>Berlaku umum (semua provinsi)</option>
                                    @foreach ($provinsi as $p)
                                        <option value="{{ $p }}" @selected($filterProvinsi === $p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mh-pilih" wire:key="f-ven-{{ $filterVendor }}-{{ count($vendor) }}" wire:ignore
                                x-data x-init="pasangSelectCari($el, 'filterVendor', { placeholder: 'Semua vendor' })">
                                <select class="dsb-isian" aria-label="Saring menurut vendor">
                                    <option value="">Semua vendor</option>
                                    {{-- "Belum ada vendor" adalah daftar pekerjaan yang
                                         tersisa, dan itu yang paling sering dicari
                                         sesudah daftar vendornya ada. --}}
                                    <option value="-" @selected($filterVendor === '-')>Belum ada vendor</option>
                                    @foreach ($vendor as $v)
                                        <option value="{{ $v['id'] }}" @selected((string) $filterVendor === (string) $v['id'])>{{ $v['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <select class="dsb-isian mh-pilih" wire:model.live="filterDipakai" aria-label="Saring menurut pemakaian">
                                <option value="">Dipakai &amp; belum</option>
                                <option value="ya">Sudah pernah dipakai</option>
                                {{-- Daftar baris yang boleh dirapikan tanpa menyentuh
                                     penawaran mana pun. --}}
                                <option value="belum">Belum pernah dipakai</option>
                            </select>

                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama">
                                <i class="bi bi-plus-lg"></i><span>Tambah Harga</span>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-awas" title="Kembali menampilkan seluruh harga">
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
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)"><i class="bi bi-tags"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">{{ $sunting ? 'Ubah Harga' : 'Harga Baru' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Harga <strong>dibekukan</strong> saat masuk ke sebuah RAB — perubahan di sini hanya berlaku untuk RAB berikutnya.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            {{-- Kotaknya melar mengisi barisnya (flex, bukan kolom
                                 tetap), karena jumlah kotak yang terlihat BERUBAH:
                                 destinasi hanya untuk tiket, kapasitas hanya untuk
                                 satuan yang menghitung unit. Dengan kolom tetap,
                                 setiap kotak yang disembunyikan meninggalkan
                                 lubang di tengah formulirnya. --}}
                            {{-- Tiga baris bermakna, bukan satu rak panjang:
                                 apa &amp; berapa, lalu di mana, lalu namanya.
                                 Urutannya bukan selera — destinasi harus sudah
                                 terpilih sebelum kotak nama, karena namanya
                                 dirakit dari destinasi itu. --}}
                            <div class="mh-medan-rak">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-kategori">Kategori <span class="dsb-wajib">*</span></label>
                                    <select id="mh-kategori" wire:model.live="isian.kategori" class="dsb-isian">
                                        @foreach ($kategori as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-satuan">
                                        Dihitung per <span class="dsb-wajib">*</span>
                                        <span class="dsb-label-ket">terisi dari kategori</span>
                                    </label>
                                    <select id="mh-satuan" wire:model.live="isian.satuan" class="dsb-isian">
                                        @foreach ($satuan as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-harga">Harga <span class="dsb-wajib">*</span></label>
                                    <div class="orcha-rupiah">
                                        <input id="mh-harga" type="text" inputmode="numeric" wire:model.blur="isian.harga"
                                            class="orcha-uang dsb-isian @error('isian.harga') is-galat @enderror" placeholder="50.000">
                                    </div>
                                    @error('isian.harga')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                {{-- Kapasitas hanya untuk satuan yang menghitung unit.
                                     Sebelumnya kotaknya selalu ada dengan petunjuk
                                     "Tidak perlu" — kotak yang hadir untuk tidak
                                     diisi tetap menuntut dibaca lebih dulu. --}}
                                @if ($perluKapasitas)
                                    <div class="dsb-medan is-lebar">
                                        <label class="dsb-label" for="mh-kapasitas">
                                            Muat berapa orang per {{ $sebutan }}? <span class="dsb-wajib">*</span>
                                            <span class="dsb-label-ket">bukan jumlah {{ $sebutan }}-nya</span>
                                        </label>

                                        <div class="mh-muat">
                                            <input id="mh-kapasitas" type="number" min="1" max="1000"
                                                wire:model.live.debounce.400ms="isian.kapasitas"
                                                class="dsb-isian mh-muat-angka @error('isian.kapasitas') is-galat @enderror"
                                                placeholder="{{ $sebutan === 'kamar' ? '2' : '14' }}">

                                            {{-- Pilihan bernama, bukan angka yang ditebakkan.
                                                 Transportasi tidak selalu Hiace — bisa mobil
                                                 pribadi, Elf, atau bus besar — dan angka yang
                                                 salah di sini tidak pernah terlihat salah:
                                                 yang meleset cuma jumlah unitnya. --}}
                                            <div class="mh-muat-pilih">
                                                @foreach ($pilihanMuat as [$namaKendaraan, $kursi])
                                                    <button type="button" wire:click="pakaiKapasitas({{ $kursi }})"
                                                        class="mh-muat-chip {{ $muatKini === $kursi ? 'is-aktif' : '' }}"
                                                        title="Isi dengan {{ $kursi }}">
                                                        {{ $namaKendaraan }}
                                                        <span class="mh-muat-kursi">{{ $kursi }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        @error('isian.kapasitas')
                                            <span class="dsb-galat">{{ $message }}</span>
                                        @else
                                            {{-- Dua angka yang selama ini sama-sama disebut
                                                 "kapasitas", dan karena itu tertukar: yang
                                                 diisi di sini muatan SATU buah; jumlah
                                                 buahnya dihitung sendiri dari peserta. --}}
                                            <span class="dsb-kartu-sub">
                                                @if ($muatKini)
                                                    Satu {{ $sebutan }} muat <strong>{{ $muatKini }} orang</strong>.
                                                    Jumlah {{ $sebutan }}-nya dihitung sendiri saat menyusun RAB —
                                                    peserta ÷ {{ $muatKini }}, dibulatkan ke atas, jadi
                                                    <strong>{{ $muatKini + 1 }} orang sudah butuh 2 {{ $sebutan }}</strong>.
                                                @else
                                                    Isi muatan <strong>satu</strong> {{ $sebutan }} saja. Jumlah
                                                    {{ $sebutan }}-nya dihitung sendiri saat menyusun RAB:
                                                    peserta ÷ muatan, dibulatkan ke atas.
                                                @endif
                                            </span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            <div class="mh-medan-rak">

                                {{-- Destinasi hanya ditawarkan untuk tiket — di sembilan
                                     kategori lain ia kotak yang tidak pernah diisi.
                                     Tetap ditampilkan bila nilainya SUDAH ada, supaya
                                     mengganti kategori tidak diam-diam menyembunyikan
                                     data yang masih tersimpan. --}}
                                @if ($medanDestinasi)
                                    <div class="dsb-medan">
                                        <label class="dsb-label" for="mh-destinasi">
                                            Destinasi
                                            <span class="dsb-label-ket">dijodohkan ke itinerary</span>
                                        </label>
                                        {{-- DIPILIH, bukan diketik: harga tiket dijodohkan
                                             ke itinerary lewat nama ini PERSIS. --}}
                                        {{-- Daftarnya puluhan dan IKUT BERUBAH mengikuti
                                             provinsi, jadi kuncinya memuat daftar beserta
                                             nilainya — node yang kuncinya berubah diganti
                                             utuh, dan Select2 dipasang ulang dengan
                                             pilihan yang baru. --}}
                                        <div wire:key="dst-{{ md5($destinasiKini.'|'.implode('|', $destinasi)) }}" wire:ignore
                                            x-data x-init="pasangSelectCari($el, 'isian.destinasi', { placeholder: 'Tidak terikat destinasi' })">
                                            <select id="mh-destinasi"
                                                class="dsb-isian @error('isian.destinasi') is-galat @enderror">
                                                <option value="">Tidak terikat destinasi</option>
                                                @if ($destinasiAsing)
                                                    {{-- Nilai lama yang tidak ada di katalog TETAP
                                                         ditawarkan, supaya menyunting baris lain
                                                         tidak diam-diam menghapusnya. --}}
                                                    <option value="{{ $destinasiKini }}" selected>{{ $destinasiKini }} — tidak ada di katalog</option>
                                                @endif
                                                @foreach ($destinasi as $d)
                                                    <option value="{{ $d }}" @selected($destinasiKini === $d)>{{ $d }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @unless ($destinasiBaru)
                                            <button type="button" wire:click="$set('destinasiBaru', true)" class="mh-tambah">
                                                <i class="bi bi-plus-circle"></i> Destinasinya belum ada di daftar
                                            </button>
                                        @endunless
                                    </div>
                                @endif

                                {{-- Dari siapa harga ini dibeli. Nomornya tidak
                                     diketik di sini — ia hidup di daftar vendor,
                                     supaya nomor yang berganti cukup diubah sekali
                                     dan seluruh harga yang memakainya ikut benar. --}}
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-vendor">
                                        Vendor
                                        <span class="dsb-label-ket">kontaknya ikut ke RAB</span>
                                    </label>
                                    <div wire:key="ven-{{ $isian['vendor_id'] ?? '' }}-{{ count($vendor) }}" wire:ignore
                                        x-data x-init="pasangSelectCari($el, 'isian.vendor_id', { placeholder: 'Belum ditentukan' })">
                                        <select id="mh-vendor" class="dsb-isian">
                                            <option value="">Belum ditentukan</option>
                                            @foreach ($vendor as $v)
                                                <option value="{{ $v['id'] }}" @selected((string) ($isian['vendor_id'] ?? '') === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['jenis'])) — {{ $v['jenis'] }}@endif</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @php($vendorKini = collect($vendor)->firstWhere('id', (int) ($isian['vendor_id'] ?? 0)))
                                    @if ($vendorKini && ! empty($vendorKini['kontak']))
                                        <span class="mh-vendor-kontak">
                                            <i class="bi bi-telephone"></i>{{ $vendorKini['kontak'] }}
                                            {{-- Buntu sebelumnya: nomornya terlihat, tetapi
                                                 tidak ada jalan ke rinciannya dari sini. --}}
                                            <a href="{{ route('admin.orcha.vendor.detail', $vendorKini['id']) }}" wire:navigate
                                                class="mh-vendor-tautan" title="Buka rincian {{ $vendorKini['nama'] }}">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </span>
                                    @elseif ($vendorKini)
                                        {{-- Vendor tanpa nomor tetap harus dicari saat
                                             dibutuhkan — jadi ketiadaannya ditandai. --}}
                                        <span class="mh-vendor-kosong">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            Vendor ini belum punya nomor.
                                            <a href="{{ route('admin.orcha.vendor') }}" wire:navigate>Isi di daftar vendor</a>
                                        </span>
                                    @elseif (! $vendorBaru)
                                        {{-- Dulu di sini ada tautan ke daftar vendor —
                                             yang MEMINDAHKAN halaman, dan seluruh isian
                                             harga yang sudah diketik hilang bersamanya. --}}
                                        <button type="button" wire:click="$set('vendorBaru', true)" class="mh-tambah">
                                            <i class="bi bi-plus-circle"></i> Vendornya belum ada di daftar
                                        </button>
                                    @endif
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-provinsi">Provinsi</label>
                                    <div wire:key="prov-{{ $isian['provinsi'] ?? '' }}" wire:ignore
                                        x-data x-init="pasangSelectCari($el, 'isian.provinsi', { placeholder: 'Berlaku umum (semua provinsi)' })">
                                        <select id="mh-provinsi" class="dsb-isian">
                                            <option value="">Berlaku umum (semua provinsi)</option>
                                            @foreach ($provinsi as $p)
                                                <option value="{{ $p }}" @selected(($isian['provinsi'] ?? '') === $p)>{{ $p }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-daerah">Daerah</label>
                                    <input id="mh-daerah" type="text" wire:model.live.debounce.400ms="isian.daerah"
                                        maxlength="80" class="dsb-isian" placeholder="Sleman">
                                </div>

                                @if ($vendorBaru)
                                    <div class="mh-baru is-vendor">
                                        <div class="mh-baru-judul">
                                            <i class="bi bi-shop"></i> Vendor baru
                                        </div>

                                        <input type="text" wire:model="namaVendorBaru" maxlength="120"
                                            wire:keydown.enter.prevent="tambahVendorKatalog"
                                            class="dsb-isian @error('namaVendorBaru') is-galat @enderror"
                                            placeholder="Nama vendor, mis. PO Sumber Jaya">

                                        <input type="text" wire:model="kontakVendorBaru" maxlength="120"
                                            wire:keydown.enter.prevent="tambahVendorKatalog"
                                            class="dsb-isian" placeholder="081234000111 (Pak Budi)">

                                        <button type="button" wire:click="tambahVendorKatalog"
                                            wire:loading.attr="disabled" wire:target="tambahVendorKatalog"
                                            class="dsb-tombol is-telaah">
                                            <i class="bi bi-plus-lg" wire:loading.remove wire:target="tambahVendorKatalog"></i>
                                            <span class="dsb-putar" wire:loading wire:target="tambahVendorKatalog"></span>
                                            <span>Tambah vendor</span>
                                        </button>

                                        <button type="button" wire:click="batalVendorBaru"
                                            class="dsb-tombol is-lembut">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>

                                        @error('namaVendorBaru')
                                            <span class="dsb-galat mh-baru-kaki">{{ $message }}</span>
                                        @else
                                            <span class="dsb-kartu-sub mh-baru-kaki">
                                                Langsung terpilih untuk harga ini, dan masuk ke
                                                <a href="{{ route('admin.orcha.vendor') }}" wire:navigate>daftar vendor</a>.
                                            </span>
                                        @enderror
                                    </div>
                                @endif

                                {{-- Selebar rak, bukan terjepit di dalam medan Destinasi.

                                     Di sepertiga lebar kartu, isian dan tombolnya tidak
                                     muat sebaris: "Batal" jatuh sendirian ke baris kedua
                                     dan menyisakan tiga perempat baris menganggur —
                                     sementara dua pertiga kartu di sebelahnya kosong.

                                     Kotak pilih hanya memuat yang sudah ada di katalog,
                                     sementara destinasi baru memang bermunculan. Tanpa
                                     jalan ini admin harus pindah menu dan kehilangan
                                     seluruh isian yang sudah diketiknya. --}}
                                @if ($destinasiBaru && $medanDestinasi)
                                    <div class="mh-baru">
                                        <div class="mh-baru-judul">
                                            <i class="bi bi-plus-circle"></i> Destinasi baru
                                        </div>

                                        <input type="text" wire:model="namaDestinasiBaru" maxlength="100"
                                            wire:keydown.enter.prevent="tambahDestinasiKatalog"
                                            class="dsb-isian @error('namaDestinasiBaru') is-galat @enderror"
                                            placeholder="Nama destinasi baru, mis. Tebing Breksi">

                                        <button type="button" wire:click="tambahDestinasiKatalog"
                                            wire:loading.attr="disabled" wire:target="tambahDestinasiKatalog"
                                            class="dsb-tombol is-aman">
                                            <i class="bi bi-plus-lg" wire:loading.remove wire:target="tambahDestinasiKatalog"></i>
                                            <span class="dsb-putar" wire:loading wire:target="tambahDestinasiKatalog"></span>
                                            <span>Tambah ke katalog</span>
                                        </button>

                                        <button type="button" wire:click="batalDestinasiBaru"
                                            class="dsb-tombol is-lembut">
                                            <i class="bi bi-x-lg"></i><span>Batal</span>
                                        </button>

                                        @error('namaDestinasiBaru')
                                            <span class="dsb-galat mh-baru-kaki">{{ $message }}</span>
                                        @else
                                            <span class="dsb-kartu-sub mh-baru-kaki">
                                                Namanya langsung masuk katalog Orcha, jadi bisa dipilih di itinerary juga.
                                                @if (trim((string) ($isian['provinsi'] ?? '')) !== '')
                                                    Provinsi <strong>{{ $isian['provinsi'] }}</strong> ikut terkirim.
                                                @endif
                                            </span>
                                        @enderror
                                    </div>
                                @endif


                            </div>

                            <div class="mh-medan-rak">
                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label" for="mh-nama">
                                        Nama <span class="dsb-wajib">*</span>
                                        @if ($namaKini === '' && $namaRakitan !== '')
                                            <span class="dsb-label-ket">boleh dibiarkan kosong</span>
                                        @endif
                                    </label>
                                    <input id="mh-nama" type="text" wire:model.blur="isian.nama" maxlength="150"
                                        class="dsb-isian @error('isian.nama') is-galat @enderror"
                                        placeholder="{{ $namaRakitan !== '' ? $namaRakitan : 'Sewa kendaraan + sopir + BBM' }}">
                                    @error('isian.nama')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan is-lebar">
                                    <label class="dsb-label" for="mh-catatan">
                                        Catatan internal
                                        <span class="dsb-label-ket">tidak pernah ikut ke PDF pelanggan</span>
                                    </label>
                                    <input id="mh-catatan" type="text" wire:model="isian.catatan" maxlength="500"
                                        class="dsb-isian" placeholder="Vendor, nomor kontak, harga berlaku sampai…">
                                </div>
                            </div>

                            {{-- Yang terisi sendiri diperlihatkan, bukan dikerjakan
                                 diam-diam: nama yang baru muncul sesudah menyimpan
                                 tidak bisa dikoreksi oleh orang yang tidak pernah
                                 melihatnya. --}}
                            @if ($namaKini === '' && $namaRakitan !== '')
                                <div class="mh-rakitan">
                                    <span class="dsb-ikon is-kecil" style="--c: var(--dsb-aman)"><i class="bi bi-magic"></i></span>
                                    <div class="mh-rakitan-teks">
                                        <span class="dsb-kartu-sub">Namanya terisi sendiri</span>
                                        <strong class="mh-rakitan-judul">{{ $namaRakitan }}</strong>
                                    </div>
                                </div>
                            @endif

                            @if ($destinasiAsing)
                                <div class="mh-peringatan">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        <strong>{{ $destinasiKini }}</strong> tidak ada di katalog destinasi.
                                        Tiket ini tidak akan ikut tertarik saat destinasinya dipilih di itinerary —
                                        pilih nama dari daftar, atau tambahkan destinasinya ke katalog.
                                    </span>
                                </div>
                            @elseif ($this->tiketTanpaDestinasi())
                                <div class="mh-peringatan">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        Tiket tanpa destinasi tidak pernah ikut tertarik ke RAB — penariknya mencari
                                        harga lewat nama destinasi di itinerary. Pilih destinasinya, atau tandai
                                        <strong>wajib</strong> supaya tetap masuk sendiri.
                                    </span>
                                </div>
                            @endif

                            <div class="mh-sakelar">
                                <label class="orcha-sakelar-kartu {{ $otomatis ? 'nyala' : '' }}">
                                    <span class="rupa"><i class="bi {{ $otomatis ? 'bi-magic' : 'bi-hand-index' }}"></i></span>
                                    <span class="isi">
                                        <span class="judul">{{ $otomatis ? 'Wajib — masuk sendiri' : 'Dipilih manual' }}</span>
                                        <span class="ket">
                                            {{ $otomatis
                                                ? 'Otomatis masuk ke setiap RAB baru di provinsinya.'
                                                : 'Ditambahkan ke RAB bila admin memilihnya.' }}
                                        </span>
                                    </span>
                                    <span class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" wire:model.live="otomatis">
                                    </span>
                                </label>

                                <label class="orcha-sakelar-kartu {{ $aktif ? 'nyala' : '' }}">
                                    <span class="rupa"><i class="bi {{ $aktif ? 'bi-check2-circle' : 'bi-pause-circle' }}"></i></span>
                                    <span class="isi">
                                        <span class="judul">{{ $aktif ? 'Aktif' : 'Dimatikan' }}</span>
                                        <span class="ket">
                                            {{ $aktif
                                                ? 'Muncul sebagai pilihan saat menyusun RAB.'
                                                : 'Tersimpan, tetapi tidak ditawarkan di RAB baru.' }}
                                        </span>
                                    </span>
                                    <span class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                    </span>
                                </label>
                            </div>

                            <div class="mh-kaki">
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

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterKategori,filterProvinsi,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-tags"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Harga tersimpan</h3>
                                <span class="dsb-kartu-sub">
                                    Tandai <strong>wajib</strong> untuk biaya yang selalu ada — makan, parkir, asuransi — dan ia masuk sendiri ke setiap RAB baru di provinsinya.
                                </span>
                            </div>
                        </div>
                        <span class="mh-kepala-aksi">
                            <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} harga</span>
                            {{-- Tombol unduh duduk di kepala TABELNYA, bukan di bilah
                                 saringan: yang diunduh memang isi tabel ini, dan bilah
                                 saringan yang memuat tujuh benda tidak pernah muat
                                 sebaris. --}}
                            <a href="{{ route('admin.orcha.master-harga.pdf', [
                                    'cari' => $cari, 'kategori' => $filterKategori,
                                    'provinsi' => $filterProvinsi, 'vendor' => $filterVendor,
                                    'dipakai' => $filterDipakai,
                                ]) }}"
                                class="dsb-tombol is-telaah" title="Unduh daftar harga sesuai saringan">
                                <i class="bi bi-file-earmark-pdf"></i><span>PDF</span>
                            </a>
                        </span>
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-tags"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada harga' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba ubah kata kuncinya, atau pilih kategori & provinsi lain.'
                                        : 'Mulai dari tiket destinasi yang paling sering diminta.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        {{-- Satu vendor biasanya memasok beberapa harga — Hiace, Elf,
                             dan bus besar dari pemilik armada yang sama. Lewat
                             formulir, memasangnya berarti membuka, menyimpan, dan
                             menutup sebanyak barisnya, untuk satu keputusan yang
                             sama. --}}
                        @if ($pilih !== [])
                            <div class="dsb-kartu-isi">
                                <div class="mh-pasang">
                                    <span class="mh-pasang-judul">
                                        <i class="bi bi-check2-square"></i>
                                        <strong>{{ count($pilih) }}</strong> harga dicentang
                                    </span>

                                    <div class="mh-pasang-pilih" wire:key="pasang-{{ $pasangKeVendor }}-{{ count($vendor) }}" wire:ignore
                                        x-data x-init="pasangSelectCari($el, 'pasangKeVendor', { placeholder: 'Pasangkan ke vendor…' })">
                                        <select class="dsb-isian @error('pasangKeVendor') is-galat @enderror">
                                            <option value="">Pasangkan ke vendor…</option>
                                            @foreach ($vendor as $v)
                                                <option value="{{ $v['id'] }}" @selected((string) $pasangKeVendor === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['kontak'])) — {{ $v['kontak'] }}@endif</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="button" wire:click="pasangVendor"
                                        wire:loading.attr="disabled" wire:target="pasangVendor"
                                        class="dsb-tombol is-aman">
                                        <i class="bi bi-link-45deg" wire:loading.remove wire:target="pasangVendor"></i>
                                        <span class="dsb-putar" wire:loading wire:target="pasangVendor"></span>
                                        <span>Pasangkan</span>
                                    </button>
                                    <button type="button" wire:click="batalPilih" class="dsb-tombol is-lembut">
                                        <i class="bi bi-x-lg"></i><span>Batal</span>
                                    </button>

                                    @error('pasangKeVendor')
                                        <span class="dsb-galat mh-pasang-kaki">{{ $message }}</span>
                                    @else
                                        <span class="dsb-kartu-sub mh-pasang-kaki">
                                            Hanya kolom vendornya yang berubah — harga, satuan, dan kapasitasnya tidak disentuh.
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel mh-tabel">
                                <thead>
                                    <tr>
                                        <th class="mh-centang"></th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Wilayah</th>
                                        {{-- Vendor dapat kolomnya sendiri: sebagai baris
                                             kecil di bawah nama, "mana yang belum ada
                                             vendornya" harus dipindai satu per satu. --}}
                                        <th>Dibeli dari</th>
                                        <th class="text-end">Harga</th>
                                        {{-- Dua angka yang menjawab pertanyaan yang sama:
                                             harga ini masih relevan atau tidak. --}}
                                        <th class="text-center">Dipakai</th>
                                        {{-- Tidak ada kolom status. Satu kolom berisi
                                             "Aktif" di hampir setiap baris tidak memberi
                                             tahu apa pun, dan yang satu-dua menyimpang
                                             tenggelam di antaranya — jadi yang menyimpang
                                             saja yang ditandai, di sebelah namanya. --}}
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php([$warnaKat, $ikonKat] = $rupaDari($baris['kategori']))
                                        @php($lepas = $lepasDariKatalog($baris['destinasi']))
                                        <tr wire:key="mh-{{ $baris['id'] }}" class="{{ $baris['aktif'] ? '' : 'is-mati' }}">
                                            <td class="mh-centang">
                                                <input type="checkbox" class="form-check-input mt-0"
                                                    wire:model.live="pilih" value="{{ $baris['id'] }}"
                                                    aria-label="Pilih {{ $baris['nama'] }}">
                                            </td>
                                            <td data-judul="Nama">
                                                <span class="mh-utama">
                                                    <span class="dsb-tabel-judul">
                                                        {{ $baris['nama'] }}
                                                        @unless ($baris['aktif'])
                                                            <span class="mh-tanda is-mati">DIMATIKAN</span>
                                                        @endunless
                                                        @if ($baris['otomatis'])
                                                            <span class="mh-tanda is-wajib">WAJIB</span>
                                                        @endif
                                                    </span>
                                                    @if ($baris['destinasi'])
                                                        <span class="{{ $lepas ? 'mh-lepas' : 'dsb-tabel-meta' }}"
                                                            @if ($lepas) title="Nama ini tidak ada di katalog destinasi, jadi tiketnya tidak akan ikut tertarik ke RAB" @endif>
                                                            <i class="bi {{ $lepas ? 'bi-exclamation-triangle-fill' : 'bi-geo-alt' }}"></i>
                                                            {{ $baris['destinasi'] }}
                                                            @if ($lepas)<span class="mh-lepas-ket">tidak cocok katalog</span>@endif
                                                        </span>
                                                    @endif
                                                    @if ($baris['catatan'])
                                                        <span class="dsb-tabel-meta mh-catatan">{{ $baris['catatan'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Kategori">
                                                <span class="dsb-lencana" style="--c: {{ $warnaKat }}">
                                                    <i class="bi {{ $ikonKat }}"></i>{{ $baris['kategori_label'] }}
                                                </span>
                                            </td>
                                            <td data-judul="Wilayah">
                                                <span class="mh-utama">
                                                    <span class="mh-wilayah">{{ $baris['provinsi'] ?: 'Semua provinsi' }}</span>
                                                    @if ($baris['daerah'])
                                                        <span class="dsb-tabel-meta">{{ $baris['daerah'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Dibeli dari">
                                                @if (! empty($baris['vendor']))
                                                    <a href="{{ route('admin.orcha.vendor.detail', $baris['vendor_id']) }}" wire:navigate
                                                        class="mh-utama mh-vendor-sel" title="Buka rincian {{ $baris['vendor'] }}">
                                                        <span>{{ $baris['vendor'] }}</span>
                                                        @if (! empty($baris['vendor_kontak']))
                                                            <span class="dsb-tabel-meta">{{ $baris['vendor_kontak'] }}</span>
                                                        @endif
                                                    </a>
                                                @else
                                                    {{-- Harga tanpa vendor berarti tidak ada nomor
                                                         yang bisa dihubungi saat menyiapkan
                                                         keberangkatan. --}}
                                                    <span class="mh-tanpa-vendor">
                                                        <i class="bi bi-dash-circle"></i>belum ada
                                                    </span>
                                                @endif
                                            </td>
                                            <td data-judul="Harga" class="text-end">
                                                <span class="mh-utama is-kanan">
                                                    <span class="dsb-tabel-judul dsb-tabel-angka">{{ $baris['harga_teks'] }}</span>
                                                    <span class="dsb-tabel-meta mh-satuan">{{ $baris['satuan_label'] }}</span>
                                                    @if ($baris['kapasitas'])
                                                        <span class="dsb-tabel-meta mh-satuan">kapasitas {{ $baris['kapasitas'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Dipakai" class="text-center">
                                                <span class="mh-utama is-tengah">
                                                    @if (($baris['jumlah_rab'] ?? 0) > 0)
                                                        {{-- Mengubah harga ini menyentuh sekian
                                                             penawaran — tetapi RAB yang sudah dibuat
                                                             TIDAK ikut berubah, karena harganya
                                                             dibekukan saat dimasukkan. --}}
                                                        <span class="mh-dipakai">{{ $baris['jumlah_rab'] }}&times;</span>
                                                    @else
                                                        <span class="mh-belum">belum</span>
                                                    @endif
                                                    @if (! empty($baris['diubah_teks']))
                                                        <span class="dsb-tabel-meta mh-diubah">{{ $baris['diubah_teks'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="dsb-tabel-aksi">
                                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-utama)"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                        title="Ubah harga ini">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: var(--dsb-bahaya)"
                                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                        data-title="Hapus {{ $baris['nama'] }}?"
                                                        data-text="RAB yang sudah memakainya tidak berubah."
                                                        data-confirm="Ya, hapus" data-icon="warning"
                                                        title="Hapus harga ini">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mh-halaman">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Saringan: satu baris di layar lebar, melipat sendiri saat sempit. */
        .mh-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        /* DUA baris yang disengaja, bukan satu baris yang kebetulan pecah.

           Layar ini punya empat kotak saring — satu lebih banyak daripada
           Vendor — dan dipaksakan sebaris, labelnya terpotong jadi "Semua
           kategor" dan "Dipakai & belun". Jadi kotak cari mengambil
           barisnya sendiri, dan kotak saring beserta tombolnya membagi
           habis baris kedua. */
        .mh-saring > .mh-medan-cari { flex: 1 1 100%; min-width: 0; }
        .mh-saring > .mh-pilih { flex: 1 1 150px; width: auto; min-width: 0; max-width: none; }
        .mh-saring .dsb-tombol { flex: 0 0 auto; }

        /* Kotak cari bersama memakai .form-control Bootstrap; yang disamakan
           rupanya saja — markup & perilakunya tetap milik partial. */
        .mh-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; box-shadow: none;
        }
        .mh-saring .form-control:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .mh-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        /* Isian yang melar, bukan kolom tetap.

           Jumlah kotak yang terlihat berubah-ubah — destinasi hanya untuk
           tiket, kapasitas hanya untuk satuan yang menghitung unit. Pada
           grid berkolom tetap, tiap kotak yang disembunyikan meninggalkan
           lubang; dengan flex-basis, kotak di baris terakhir melebar
           membagi habis lebarnya. */
        .mh-medan-rak { display: flex; flex-wrap: wrap; gap: 14px 16px; align-items: flex-start; }
        .mh-medan-rak > .dsb-medan { flex: 1 1 215px; min-width: 0; }
        .mh-medan-rak > .dsb-medan.is-lebar { flex-basis: 100%; }
        .mh-medan-rak .dsb-kartu-sub,
        .mh-medan-rak .dsb-galat { display: block; margin-top: 5px; }
        .mh-medan-rak .dsb-label-ket { margin-left: 4px; font-weight: 500; }
        .mh-medan-rak + .mh-medan-rak { margin-top: 14px; }

        /* "Rp" di dalam kotak harga.

           Aturan bersamanya menyasar .form-control, sedangkan isian di sini
           memakai .dsb-isian — tanpa padding kirinya, "Rp" duduk tepat di
           atas angka pertama dan yang terbaca jadi "Rp0.000". */
        .mh-medan-rak .orcha-rupiah .dsb-isian { padding-left: 2.4rem; font-weight: 600; }

        /* Kotak muatan: angkanya kecil di kiri, pilihan bernamanya di kanan.

           Angka telanjang "14" tidak mengatakan apa-apa; "Hiace · 14" sekaligus
           menjawab pertanyaan yang sebenarnya ada di kepala — ini muatan satu
           kendaraan, bukan jumlah kendaraannya. */
        .mh-muat { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .mh-muat-angka { flex: 0 0 104px; width: 104px; }

        /* Flex yang melar, bukan deret yang berhenti di tengah jalan.

           Dengan basis sama dan grow sama, lima chip di satu baris jadi
           sama lebar — dan yang penting, chip terakhir di baris manapun
           ikut melar menutup sisanya. Grid auto-fit tidak bisa begitu:
           di layar sempit, chip kelima duduk sendirian di kolom pertama
           dan kolom kedua menganggur. */
        .mh-muat-pilih {
            display: flex; flex-wrap: wrap; gap: 6px;
            flex: 1 1 260px; min-width: 0;
        }
        .mh-muat-pilih > .mh-muat-chip { flex: 1 1 118px; }
        .mh-muat-chip {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 7px 10px; border-radius: 10px; cursor: pointer; min-width: 0;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3);
            color: var(--dsb-tinta, #1c1f26); font-size: .79rem; font-weight: 600;
            white-space: nowrap;
        }
        .mh-muat-chip:hover { border-color: #c7d2fe; }
        .mh-muat-chip.is-aktif { border-color: #4f46e5; background: #f6f5ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .1); }
        .mh-muat-kursi {
            padding: 1px 6px 2px; border-radius: 6px;
            background: #f1f5f9; color: var(--dsb-redup, #6b7280); font-size: .7rem; font-weight: 700;
        }
        .mh-muat-chip.is-aktif .mh-muat-kursi { background: #e0e7ff; color: #4338ca; }

        /* Destinasi baru: satu blok selebar rak, bukan kotak terjepit.

           Judul & keterangannya selebar blok (flex-basis 100%), isiannya
           melar mengisi sisa baris, dua tombolnya menempel di kanan — jadi
           barisnya habis terpakai dan tidak ada tombol yang jatuh sendirian. */
        .mh-baru {
            flex: 1 1 100%;
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            padding: 12px 14px; border-radius: 13px;
            background: #f0fdfa; border: 1px solid #99f6e4;
        }
        .mh-baru-judul {
            flex: 1 1 100%;
            display: flex; align-items: center; gap: 7px;
            font-size: .82rem; font-weight: 700; color: #0f766e;
        }
        .mh-baru .dsb-isian { flex: 1 1 240px; min-width: 0; background: #fff; }
        .mh-baru .dsb-tombol { flex: 0 0 auto; background: #fff; }
        .mh-baru-kaki { flex: 1 1 100%; margin-top: 0 !important; }
        /* Vendor baru memakai kotak yang sama dengan destinasi baru, warnanya
           saja yang mengikuti warna vendor di seluruh layar. */
        .mh-baru.is-vendor { background: #f5fafd; border-color: #cfe6f3; }
        .mh-baru.is-vendor .mh-baru-judul { color: #0369a1; }
        .mh-tambah {
            display: inline-flex; align-items: center; gap: 5px; margin-top: 6px;
            padding: 0; border: 0; background: none;
            font-size: .76rem; font-weight: 600; color: #0f766e;
        }
        .mh-tambah:hover { text-decoration: underline; }

        /* Baris "terisi sendiri", sepasang dengan .rb-rakitan di daftar RAB. */
        .mh-rakitan {
            display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
            margin-top: 14px; padding: 11px 14px;
            background: #f6fbf8; border: 1px solid #d1fae5; border-radius: 13px;
        }
        .mh-rakitan-teks { display: flex; flex-direction: column; min-width: 0; flex: 1 1 240px; }
        .mh-rakitan-judul { font-size: .9rem; color: var(--dsb-tinta, #1c1f26); }

        /* Peringatan yang menyebut akibatnya, bukan cuma "tidak valid". */
        .mh-peringatan {
            display: flex; align-items: flex-start; gap: 8px;
            margin-top: 12px; padding: 11px 14px;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 13px;
            color: #92400e; font-size: .8rem; line-height: 1.5;
        }
        .mh-peringatan i { margin-top: 2px; flex-shrink: 0; }

        .mh-sakelar { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 14px; }
        .mh-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }

        /* Sel bertumpuk: judul tegas di atas, keterangannya di bawah. */
        .mh-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .mh-utama.is-kanan { align-items: flex-end; }
        .mh-catatan { font-style: italic; }
        .mh-status { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }

        .mh-vendor-kontak {
            display: inline-flex; align-items: center; gap: 6px; margin-top: 5px;
            font-size: .79rem; font-weight: 600; color: #0369a1;
        }
        .mh-vendor-kosong {
            display: flex; align-items: flex-start; gap: 6px; margin-top: 5px;
            font-size: .75rem; line-height: 1.45; color: #92400e;
        }
        .mh-vendor-tautan { display: inline-flex; align-items: center; gap: 5px; color: #0f766e; font-weight: 600; text-decoration: none; }
        .mh-vendor-tautan:hover { text-decoration: underline; }
        .mh-vendor-baris { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; }
        .mh-vendor-baris:hover { color: #0369a1; text-decoration: underline; }

        .mh-pasang {
            display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
            padding: 12px 14px; border-radius: 13px;
            background: #f6fbf8; border: 1px solid #d1fae5;
        }
        .mh-pasang-judul {
            flex: 1 1 100%; display: flex; align-items: center; gap: 7px;
            font-size: .84rem; font-weight: 700; color: #047857;
        }
        .mh-pasang .mh-pasang-pilih { flex: 1 1 240px; min-width: 0; }
        .mh-pasang .dsb-isian { background: #fff; }
        .mh-pasang .dsb-tombol { flex: 0 0 auto; }
        .mh-pasang-kaki { flex: 1 1 100%; margin-top: 0 !important; }

        .mh-centang { width: 34px; }
        .mh-centang .form-check-input { margin: 0; }
        .mh-utama.is-tengah { align-items: center; }

        /* Delapan kolom di layar laptop membuat beberapa sel pendek pecah di
           tempat yang salah — "DI / Yogyakarta", "2 bulan / yang lalu". Yang
           dipagari hanya potongan yang panjangnya memang terbatas; nama
           harga, daerah, dan nama vendor tetap boleh turun baris. */
        .mh-wilayah, .mh-diubah, .mh-satuan { white-space: nowrap; }
        .mh-vendor-sel { text-decoration: none; color: var(--dsb-tinta, #1c1f26); }
        .mh-vendor-sel:hover { color: #0369a1; text-decoration: underline; }
        .mh-tanpa-vendor {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .78rem; font-weight: 600; color: #b45309;
        }
        .mh-kepala-aksi { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }

        .mh-dipakai { font-weight: 700; font-size: .86rem; color: var(--dsb-utama, #4f46e5); }
        .mh-tanda {
            display: inline-block; margin-left: 5px; padding: 1px 6px 2px; border-radius: 7px;
            font-size: .62rem; font-weight: 800; letter-spacing: .04em;
        }
        .mh-tanda.is-mati { background: #e2e8f0; color: #64748b; }
        .mh-tanda.is-wajib { background: #fef3c7; color: #b45309; }
        .mh-tabel tbody tr.is-mati { background: #fbfcfd; }
        .mh-tabel tbody tr.is-mati .dsb-tabel-judul { color: var(--dsb-redup, #6b7280); }
        .mh-belum { font-size: .78rem; color: var(--dsb-redup, #6b7280); }

        .mh-lepas {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .76rem; font-weight: 600; color: #b45309;
        }
        .mh-lepas-ket {
            padding: 1px 7px 2px; border-radius: 7px;
            background: #fffbeb; border: 1px solid #fde68a; font-size: .68rem; font-weight: 700;
        }

        .mh-halaman { padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9; }
        .mh-halaman > * { margin-bottom: 0 !important; margin-top: 0 !important; }

        /* Keterangan label turun ke barisnya sendiri di layar sempit:
           di samping judulnya ia membelah dua-duanya jadi dua baris. */
        @media (max-width: 575.98px) {
            .mh-medan-rak .dsb-label-ket { display: block; margin-left: 0; margin-top: 2px; }
            /* Kotak angkanya ikut selebar baris: di layar sempit ia duduk
               sendirian, dan 104px menyisakan dua pertiga baris menganggur. */
            .mh-muat-angka { flex: 1 1 100%; width: auto; }

            /* Dua tombol destinasi baru berbagi satu baris; kalau tidak
               cukup, masing-masing melar selebar baris. Yang tidak boleh
               terjadi cuma satu: tombol sempit dengan baris kosong di
               kanannya. */
            .mh-baru .dsb-tombol { flex: 1 1 130px; justify-content: center; }
        }

        @media (max-width: 991.98px) {
            .mh-saring > .mh-medan-cari { flex: 1 1 100%; }
            .mh-saring > .mh-pilih { flex: 1 1 150px; max-width: none; }
            .mh-sakelar { grid-template-columns: 1fr; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. Tanpa ini
           kolom terakhir — tombol ubah & hapus — tidak pernah terlihat
           tanpa digeser lebih dulu. */
        @media (max-width: 767.98px) {
            .mh-tabel { min-width: 0; }
            .mh-tabel thead { display: none; }
            .mh-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            .mh-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .mh-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            /* Isi sel harus boleh menyempit; medan flex yang mempertahankan
               lebar isinya mendorong kartunya keluar layar. */
            .mh-tabel tbody td > * { min-width: 0; }
            .mh-utama, .mh-status { align-items: flex-end; text-align: right; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
