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

        /*
         | Keputusannya dirakit DI SINI, bukan di dalam @if-nya.
         |
         | Kondisi dengan kurung bersarang berulang membuat Livewire melewatkan
         | penanda morph pembukanya — penutupnya tetap bertanda, dan pasangan
         | yang timpang itu membuat morph memotong di tempat yang salah.
         | Dijaga PenandaMorphAdminTest.
         */
        $punyaRiwayat = fn ($b) => ($b['jumlah_rab'] ?? 0) > 0 || ! empty($b['diubah_teks']);
        $masihBerlaku = fn ($b) => ! empty($b['berlaku_sampai_teks']) && empty($b['kedaluwarsa']);
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
                    <span class="d-block">Daftar harga beli: tiket, sewa kendaraan, hotel, dan pemandu. Ditulis sekali di sini.</span>
                    <span class="d-block">Saat menghitung biaya trip (RAB), harganya tinggal dipilih — tidak perlu diketik ulang.</span>
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

        {{-- ================== RINGKASAN ==================

             Angka yang sama sudah lama dicetak di PDF-nya, tetapi tidak
             pernah ada di layar — jadi "berapa yang belum punya vendor"
             hanya bisa dijawab dengan mengunduh berkas.

             Semuanya bisa DIKLIK. Angka yang menunjuk pekerjaan tetapi
             tidak membawa ke sana cuma menambah beban; yang diklik memasang
             saringannya, dan menekan ubin yang sama lagi melepasnya. --}}
        @php
            $ubin = [
                ['kunci' => 'jumlah', 'label' => 'Harga tersimpan', 'ket' => 'di seluruh daftar',
                    'warna' => 'var(--dsb-utama)', 'saring' => null],
                ['kunci' => 'dipakai', 'label' => 'Sudah pernah dipakai', 'ket' => 'pernah masuk RAB',
                    'warna' => 'var(--dsb-aman)', 'saring' => ['filterDipakai', 'ya']],
                ['kunci' => 'tanpa_vendor', 'label' => 'Belum punya vendor', 'ket' => 'tak ada nomor dihubungi',
                    'warna' => 'var(--dsb-bahaya)', 'saring' => ['filterVendor', '-']],
                ['kunci' => 'mati', 'label' => 'Sudah dimatikan', 'ket' => 'tak muncul saat buat RAB',
                    'warna' => 'var(--dsb-netral)', 'saring' => ['filterStatus', 'mati']],
                ['kunci' => 'kedaluwarsa', 'label' => 'Masa berlakunya lewat', 'ket' => 'tanyakan ulang vendor',
                    'warna' => 'var(--dsb-awas)', 'saring' => ['filterStatus', 'kedaluwarsa']],
            ];
        @endphp

        @if ($ringkas !== [])
            <section class="dsb-bagian">
                <div class="mh-ubin-rak">
                    @foreach ($ubin as $u)
                        @php
                            $nilai = (int) ($ringkas[$u['kunci']] ?? 0);
                            $terpasang = $u['saring'] && $this->{$u['saring'][0]} === $u['saring'][1];
                        @endphp
                        <button type="button" class="mh-ubin {{ $terpasang ? 'is-nyala' : '' }} {{ $u['saring'] ? '' : 'is-diam' }}"
                            style="--c: {{ $u['warna'] }}"
                            @if ($u['saring'])
                                wire:click="$set('{{ $u['saring'][0] }}', '{{ $terpasang ? '' : $u['saring'][1] }}')"
                                title="{{ $terpasang ? 'Lepas saringannya' : 'Lihat yang '.strtolower($u['label']) }}"
                            @else
                                disabled
                            @endif>
                            <span class="mh-ubin-label">{{ $u['label'] }}</span>
                            <span class="mh-ubin-nilai">{{ number_format($nilai, 0, ',', '.') }}</span>
                            <span class="mh-ubin-ket">{{ $u['ket'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

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
                                    <option value="{{ $kunci }}" @selected($filterKategori === $kunci)>{{ $label }}</option>
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
                                <option value="">Semua harga</option>
                                <option value="ya" @selected($filterDipakai === 'ya')>Sudah pernah dipakai</option>
                                {{-- Daftar baris yang boleh dirapikan tanpa menyentuh
                                     penawaran mana pun. --}}
                                <option value="belum" @selected($filterDipakai === 'belum')>Belum pernah dipakai</option>
                            </select>

                            {{-- Harga mati tidak punya kolomnya sendiri — kolom yang
                                 isinya sama di hampir tiap baris tidak memberi tahu
                                 apa pun. Yang kosong tetap memuat semuanya; saringan
                                 ini yang memanggil yang dicari. --}}
                            <select class="dsb-isian mh-pilih" wire:model.live="filterStatus" aria-label="Saring menurut status">
                                <option value="">Aktif &amp; dimatikan</option>
                                @foreach ([
                                    'aktif' => 'Hanya yang aktif',
                                    'mati' => 'Hanya yang dimatikan',
                                    'kedaluwarsa' => 'Masa berlakunya lewat',
                                ] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($filterStatus === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian mh-pilih" wire:model.live="urut" aria-label="Urutkan daftar">
                                <option value="">Urut: kelompok</option>
                                @foreach ([
                                    'termahal' => 'Paling mahal dulu',
                                    'termurah' => 'Paling murah dulu',
                                    'terbaru' => 'Terakhir diubah',
                                    // Yang paling lama tidak disentuh: calon harga basi.
                                    'terlama' => 'Paling lama tak disentuh',
                                    'tersering' => 'Paling sering dipakai',
                                ] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($urut === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>

                            {{-- Didorong ke ujung kanan barisnya. Dengan enam kotak
                                 saringan, tombolnya kini melipat ke baris kedua —
                                 dan tombol tunggal yang menggantung di kiri terbaca
                                 seperti sisa, bukan seperti aksi utama. --}}
                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama mh-tambah-harga">
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
                                        Begitu harga ini dipakai di sebuah RAB, angkanya <strong>tersimpan di RAB itu</strong>. Mengubahnya di sini tidak mengubah RAB yang sudah jadi — hanya RAB yang dibuat sesudahnya.
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
                                    {{-- @selected() wajib, terutama di sini: membuka "Ubah"
                                         pada baris transportasi tanpa ini menggambarkan
                                         kategorinya sebagai "Tiket & Retribusi" — pilihan
                                         pertama daftar — padahal yang tersimpan bukan itu.
                                         Menekan Simpan lalu mengubah kategorinya diam-diam. --}}
                                    <select id="mh-kategori" wire:model.live="isian.kategori" class="dsb-isian">
                                        @foreach ($kategori as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected(($isian['kategori'] ?? '') === $kunci)>{{ $label }}</option>
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
                                            <option value="{{ $kunci }}" @selected(($isian['satuan'] ?? '') === $kunci)>{{ $label }}</option>
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
                                            <span class="dsb-label-ket">dicocokkan dengan tempat di jadwal perjalanan</span>
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
                                        <span class="dsb-label-ket">nomornya ikut terbawa ke RAB</span>
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
                                                Namanya langsung masuk ke daftar destinasi Orcha, jadi bisa dipakai saat menyusun jadwal perjalanan juga.
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

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-berlaku">
                                        Harga berlaku sampai
                                        <span class="dsb-label-ket">boleh dikosongkan</span>
                                    </label>
                                    <input id="mh-berlaku" type="date" wire:model="isian.berlaku_sampai"
                                        class="dsb-isian @error('isian.berlaku_sampai') is-galat @enderror">
                                    @error('isian.berlaku_sampai')
                                        <span class="dsb-galat">{{ $message }}</span>
                                    @else
                                        {{-- Dulu ini diketik sebagai kalimat di kolom catatan,
                                             jadi tidak ada apa pun yang bisa memperingatkan
                                             saat tanggalnya lewat. --}}
                                        <span class="dsb-kartu-sub">
                                            Lewat tanggal ini harganya tetap dipakai — barisnya saja yang ditandai supaya
                                            ditanyakan ulang ke vendornya.
                                        </span>
                                    @enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-catatan">
                                        Catatan untuk tim sendiri
                                        <span class="dsb-label-ket">tidak pernah terlihat pelanggan</span>
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
                                        <span class="dsb-kartu-sub">Nama ini akan terisi sendiri</span>
                                        <strong class="mh-rakitan-judul">{{ $namaRakitan }}</strong>
                                    </div>
                                </div>
                            @endif

                            @if ($destinasiAsing)
                                <div class="mh-peringatan">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        <strong>{{ $destinasiKini }}</strong> tidak ada di daftar destinasi.
                                        Karena namanya tidak cocok, harga tiket ini tidak akan ikut terbawa saat
                                        tempat itu dimasukkan ke jadwal perjalanan. Pilih namanya dari daftar,
                                        atau tambahkan destinasinya lebih dulu.
                                    </span>
                                </div>
                            @elseif ($this->tiketTanpaDestinasi())
                                <div class="mh-peringatan">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        Tiket yang tidak diisi destinasinya tidak akan pernah ikut terbawa ke RAB,
                                        karena sistem mencarinya lewat nama tempat di jadwal perjalanan. Pilih
                                        destinasinya, atau tandai <strong>wajib</strong> supaya tetap masuk sendiri.
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
                                                ? 'Ikut masuk sendiri ke setiap RAB baru di provinsinya.'
                                                : 'Hanya ikut kalau dipilih sendiri saat menyusun RAB.' }}
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
                                                : 'Tetap tersimpan, tetapi tidak muncul lagi saat menyusun RAB baru.' }}
                                        </span>
                                    </span>
                                    <span class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" wire:model.live="aktif">
                                    </span>
                                </label>
                            </div>

                            {{-- Penolakan yang menuntut KEPUTUSAN tinggal di
                                 formulirnya, bukan lewat sebagai toast. Keputusan
                                 tidak bisa diambil dari kalimat yang sudah lenyap. --}}
                            @if ($kembaranPesan !== '')
                                <div class="mh-kembar">
                                    <i class="bi bi-exclamation-octagon-fill"></i>
                                    <div class="mh-kembar-teks">
                                        <strong>{{ $kembaranPesan }}</strong>
                                        @if (! empty($kembaranBaris['nama']))
                                            <span class="dsb-kartu-sub">
                                                Yang sudah ada: <strong>{{ $kembaranBaris['nama'] }}</strong>
                                                @if (! empty($kembaranBaris['harga_teks'])) — {{ $kembaranBaris['harga_teks'] }} @endif
                                            </span>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="simpanPaksa"
                                        wire:loading.attr="disabled" wire:target="simpanPaksa"
                                        class="dsb-tombol is-awas">
                                        <i class="bi bi-check2" wire:loading.remove wire:target="simpanPaksa"></i>
                                        <span class="dsb-putar" wire:loading wire:target="simpanPaksa"></span>
                                        <span>Tetap simpan</span>
                                    </button>
                                </div>
                            @endif

                            <div class="mh-kaki">
                                {{-- Hanya saat menambah: "lalu tambah lagi" pada
                                     formulir sunting tidak berarti apa-apa. --}}
                                @unless ($sunting)
                                    <label class="mh-lanjut" title="Formulirnya tetap terbuka, kategori & provinsinya dipertahankan">
                                        <input type="checkbox" class="form-check-input mt-0" wire:model="lanjutTambah">
                                        <span>Tambah lagi setelah simpan</span>
                                    </label>
                                @endunless
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

                            {{-- ===== Tempel dari spreadsheet =====

                                 Memasukkan lima puluh tiket berarti lima puluh kali
                                 buka-isi-simpan, dan di tengah pengulangan sebanyak
                                 itu salah ketik berhenti terasa seperti kemungkinan.
                                 Kotak di atas yang menentukan kategori, satuan,
                                 provinsi, dan vendornya — yang ditempel hanya yang
                                 berbeda tiap baris. --}}
                            @unless ($sunting)
                                <div class="mh-tempel-bungkus">
                                    @if (! $tempelBuka)
                                        <button type="button" wire:click="bukaTempel" class="dsb-tombol is-telaah">
                                            <i class="bi bi-clipboard-plus"></i><span>Tempel banyak baris dari Excel</span>
                                        </button>
                                    @else
                                        @php($terbaca = $this->barisTempel())
                                        <div class="mh-tempel">
                                            <div class="mh-tempel-kepala">
                                                <strong>Tempel dari Excel</strong>
                                                <span class="dsb-kartu-sub">
                                                    Satu baris satu harga: <strong>nama</strong>, TAB,
                                                    <strong>harga</strong>, lalu boleh TAB lagi untuk destinasinya.
                                                    Kategori, satuan, provinsi, dan vendornya diambil dari kotak di atas.
                                                </span>
                                            </div>

                                            {{-- Nilainya DICETAK di dalam tagnya, bukan
                                                 diandalkan dari wire:model: isi textarea
                                                 adalah isi elemennya, dan yang dikosongkan
                                                 dari server harus benar-benar hilang di
                                                 layar setelah barisnya masuk. --}}
                                            <textarea wire:model.live="tempelIsi" rows="5"
                                                class="dsb-isian @error('tempelIsi') is-galat @enderror"
                                                placeholder="Tiket Borobudur&#9;50.000&#10;Tiket Dieng&#9;30.000">{{ $tempelIsi }}</textarea>

                                            @error('tempelIsi')<span class="dsb-galat">{{ $message }}</span>@enderror

                                            @if ($tempelGagal !== [])
                                                {{-- Yang dilewati tidak ikut hilang bersama
                                                     panelnya: ia pekerjaan yang masih tersisa. --}}
                                                <div class="mh-tempel-gagal">
                                                    <strong>{{ count($tempelGagal) }} baris dilewati:</strong>
                                                    @foreach ($tempelGagal as $g)
                                                        <span>{{ $g['nama'] }} — {{ $g['sebab'] }}</span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="mh-tempel-kaki">
                                                <span class="dsb-chip">
                                                    <i class="bi bi-list-ol"></i>{{ count($terbaca) }} baris terbaca
                                                </span>
                                                <button type="button" wire:click="tutupTempel" class="dsb-tombol is-lembut">
                                                    <i class="bi bi-x-lg"></i><span>Tutup</span>
                                                </button>
                                                <button type="button" wire:click="simpanTempel"
                                                    wire:loading.attr="disabled" wire:target="simpanTempel"
                                                    class="dsb-tombol is-aman">
                                                    <i class="bi bi-download" wire:loading.remove wire:target="simpanTempel"></i>
                                                    <span class="dsb-putar" wire:loading wire:target="simpanTempel"></span>
                                                    <span>Masukkan semua</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== TAWARAN BATALKAN ==================

             Menempel pada perintah yang baru saja jalan. Tawaran yang datang
             belakangan — di layar lain, di menu lain — tidak pernah dipakai,
             karena saat orang sadar salah, yang dicarinya tombol di tempat ia
             menekan tadi. --}}
        @if ($batchTerakhir !== null)
            <section class="dsb-bagian">
                <div class="mh-batal-pita">
                    <i class="bi bi-check2-circle"></i>
                    <span class="mh-batal-teks">{{ $batchPesan }}</span>
                    <button type="button" wire:click="batalkanBatch"
                        wire:loading.attr="disabled" wire:target="batalkanBatch"
                        class="dsb-tombol is-awas">
                        <i class="bi bi-arrow-counterclockwise" wire:loading.remove wire:target="batalkanBatch"></i>
                        <span class="dsb-putar" wire:loading wire:target="batalkanBatch"></span>
                        <span>Batalkan, kembalikan seperti semula</span>
                    </button>
                    <button type="button" wire:click="lupakanBatch" class="dsb-tombol is-lembut" title="Tutup tawaran ini">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </section>
        @endif

        {{-- ================== DIPAKAI DI RAB MANA ==================

             Kolom "Dipakai" memberi angkanya; angka yang tidak bisa dibuka
             menyuruh orang mencocokkan nama sendiri di daftar RAB. --}}
        @if ($dipakaiUntuk !== null)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-diagram-3"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Dipakai di — {{ $dipakaiMeta['nama'] ?? '' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Yang tertulis di tiap baris adalah harga yang <strong>dibekukan</strong> di RAB itu,
                                        bukan harga sekarang ({{ $dipakaiMeta['harga_kini'] ?? '-' }}).
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="tutupDipakai" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        @if ($dipakai === [])
                            <div class="dsb-kartu-isi">
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-diagram-3"></i></span>
                                    <p class="dsb-kosong-judul">Belum dipakai RAB mana pun</p>
                                    <p class="dsb-kosong-ket">Harga ini aman diubah atau dihapus tanpa menyentuh penawaran.</p>
                                </div>
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel">
                                    <thead>
                                        <tr>
                                            <th>RAB</th>
                                            <th>Tanggal</th>
                                            <th class="text-end">Harga di RAB itu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($dipakai as $d)
                                            <tr wire:key="pakai-{{ $d['rab_id'] }}">
                                                <td data-judul="RAB">
                                                    <span class="mh-utama">
                                                        <a href="{{ route('admin.orcha.rab.susun', $d['rab_id']) }}" wire:navigate
                                                            class="dsb-tabel-judul mh-vendor-sel">{{ $d['kode'] }}</a>
                                                        <span class="dsb-tabel-meta">{{ $d['judul'] }}</span>
                                                    </span>
                                                </td>
                                                <td data-judul="Tanggal">{{ $d['tanggal'] ?: '—' }}</td>
                                                <td data-judul="Harga di RAB itu" class="text-end">
                                                    <span class="mh-utama is-kanan">
                                                        <span class="dsb-tabel-judul dsb-tabel-angka">{{ $d['harga_beku'] }}</span>
                                                        @if ($d['beda'])
                                                            <span class="dsb-tabel-meta mh-satuan">{{ $d['beda'] }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== RIWAYAT HARGA ==================

             Yang tersimpan di baris hanya angka hari ini. "Naik berapa sejak
             kapan, dan siapa yang mengubahnya" adalah pertanyaan pertama saat
             sebuah harga terasa janggal — dan selama ini tidak terjawab. --}}
        @if ($riwayatUntuk !== null)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clock-history"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Riwayat harga — {{ $riwayatMeta['nama'] ?? '' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Sekarang <strong>{{ $riwayatMeta['harga_kini'] ?? '-' }}</strong>.
                                        RAB yang sudah jadi tetap memakai angka saat ia dibuat.
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="tutupRiwayat" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        @if ($riwayat === [])
                            <div class="dsb-kartu-isi">
                                <div class="dsb-kosong">
                                    <span class="dsb-kosong-ikon"><i class="bi bi-clock-history"></i></span>
                                    <p class="dsb-kosong-judul">Harganya belum pernah berubah</p>
                                    <p class="dsb-kosong-ket">
                                        Yang tercatat di sini hanya perubahan angkanya — mengganti nama, vendor,
                                        atau catatan tidak meninggalkan jejak.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel mh-riwayat">
                                    <thead>
                                        <tr>
                                            <th>Kapan</th>
                                            <th>Dari</th>
                                            <th>Jadi</th>
                                            <th class="text-end">Selisih</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayat as $r)
                                            <tr wire:key="riw-{{ $r['id'] }}">
                                                <td data-judul="Kapan">
                                                    <span class="mh-utama">
                                                        <span class="dsb-tabel-judul">{{ $r['pada'] }}</span>
                                                        <span class="dsb-tabel-meta mh-diubah">{{ $r['pada_teks'] }}</span>
                                                    </span>
                                                </td>
                                                <td data-judul="Dari"><span class="mh-riwayat-lama">{{ $r['harga_lama'] }}</span></td>
                                                <td data-judul="Jadi"><span class="dsb-tabel-judul">{{ $r['harga_baru'] }}</span></td>
                                                <td data-judul="Selisih" class="text-end">
                                                    <span class="mh-utama is-kanan">
                                                        <span class="mh-selisih {{ $r['naik'] ? 'is-naik' : 'is-turun' }}">
                                                            <i class="bi {{ $r['naik'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>{{ $r['selisih'] }}
                                                        </span>
                                                        @if ($r['persen'] !== null)
                                                            <span class="dsb-tabel-meta mh-satuan">{{ $r['naik'] ? '+' : '' }}{{ $r['persen'] }}%</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td data-judul="Oleh">
                                                    <span class="mh-utama">
                                                        <span>{{ $r['oleh'] ?: 'tidak tercatat' }}</span>
                                                        @if (! empty($r['catatan']))
                                                            <span class="dsb-tabel-meta">{{ $r['catatan'] }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterKategori,filterProvinsi,filterStatus,urut,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-tags"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Harga tersimpan</h3>
                                <span class="dsb-kartu-sub">
                                    Biaya yang selalu ada — makan, parkir, asuransi — sebaiknya ditandai <strong>wajib</strong>, supaya ikut masuk sendiri ke setiap RAB baru di provinsinya.
                                </span>
                            </div>
                        </div>
                        <span class="mh-kepala-aksi">
                            <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} harga</span>
                            {{-- "Apa saja yang berubah minggu ini" selama ini hanya
                                 bisa dijawab dengan membuka harga satu per satu. --}}
                            <button type="button" wire:click="{{ $riwayatSemuaBuka ? 'tutupRiwayatSemua' : 'bukaRiwayatSemua' }}"
                                class="dsb-tombol is-telaah" title="Perubahan harga terakhir di seluruh daftar">
                                <i class="bi bi-clock-history"></i><span>Perubahan terakhir</span>
                            </button>
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

                    @if ($riwayatSemuaBuka)
                        <div class="dsb-kartu-isi mh-riwayat-semua">
                            @if ($riwayatSemua === [])
                                <p class="dsb-kartu-sub">Belum ada harga yang pernah berubah angkanya.</p>
                            @else
                                <ol class="mh-jejak">
                                    @foreach ($riwayatSemua as $r)
                                        <li wire:key="jejak-{{ $r['id'] }}">
                                            <span class="mh-jejak-tanggal">{{ $r['pada_teks'] }}</span>
                                            <button type="button" wire:click="bukaRiwayat({{ $r['harga_id'] }})"
                                                class="mh-jejak-nama">{{ $r['harga_nama'] ?: 'harga yang sudah dihapus' }}</button>
                                            <span class="mh-jejak-angka">
                                                {{ $r['harga_lama'] }} &rarr; <strong>{{ $r['harga_baru'] }}</strong>
                                            </span>
                                            <span class="mh-selisih {{ $r['naik'] ? 'is-naik' : 'is-turun' }}">
                                                <i class="bi {{ $r['naik'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>{{ $r['selisih'] }}
                                            </span>
                                            @if ($r['oleh'])<span class="mh-jejak-oleh">oleh {{ $r['oleh'] }}</span>@endif
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </div>
                    @endif

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

                                    {{-- SATU daftar perintah, bukan dua bagian.

                                         Sebelumnya "pasangkan ke vendor" punya barisnya
                                         sendiri dan perintah lain menumpang di bawahnya —
                                         dua model yang harus dipelajari terpisah, dan satu
                                         baris yang separuhnya kosong. Keduanya kini verba
                                         dalam daftar yang sama, dengan satu tombol
                                         Jalankan. --}}
                                    <div class="mh-perintah">
                                        <select class="dsb-isian mh-perintah-aksi @error('aksiMassal') is-galat @enderror"
                                            wire:model.live="aksiMassal" aria-label="Mau diapakan">
                                            {{-- @selected() wajib: nilai yang dipegang
                                                 komponen TIDAK otomatis tergambar sebagai
                                                 pilihan terpilih. Tanpa ini kotaknya tetap
                                                 membaca "Mau diapakan?" meski perintahnya
                                                 sudah dipilih — dan kotak pelengkap yang
                                                 muncul di sebelahnya jadi tidak masuk akal. --}}
                                            <option value="">Mau diapakan?</option>
                                            @foreach ([
                                                'vendor' => 'Pasangkan ke vendor',
                                                'naik' => 'Naikkan harganya',
                                                'turun' => 'Turunkan harganya',
                                                'berlaku' => 'Setel masa berlakunya',
                                                'matikan' => 'Matikan',
                                                'hidupkan' => 'Hidupkan lagi',
                                                'hapus' => 'Hapus',
                                            ] as $kunci => $label)
                                                <option value="{{ $kunci }}" @selected($aksiMassal === $kunci)>{{ $label }}</option>
                                            @endforeach
                                        </select>

                                        {{-- Kotak pelengkapnya mengisi sisa baris, jadi tidak
                                             ada ruang menganggur di kanan perintahnya. --}}
                                        @if ($aksiMassal === 'vendor')
                                            <div class="mh-perintah-isi" wire:key="pasang-{{ $pasangKeVendor }}-{{ count($vendor) }}" wire:ignore
                                                x-data x-init="pasangSelectCari($el, 'pasangKeVendor', { placeholder: 'Pilih vendornya…' })">
                                                <select class="dsb-isian @error('pasangKeVendor') is-galat @enderror">
                                                    <option value="">Pilih vendornya…</option>
                                                    @foreach ($vendor as $v)
                                                        <option value="{{ $v['id'] }}" @selected((string) $pasangKeVendor === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['kontak'])) — {{ $v['kontak'] }}@endif</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif (in_array($aksiMassal, ['naik', 'turun'], true))
                                            <div class="mh-perintah-isi mh-persen">
                                                <input type="text" inputmode="decimal" wire:model="persenMassal"
                                                    class="dsb-isian @error('persenMassal') is-galat @enderror"
                                                    placeholder="10" aria-label="Berapa persen">
                                                <span class="mh-persen-tanda">%</span>
                                            </div>
                                        @elseif ($aksiMassal === 'berlaku')
                                            <div class="mh-perintah-isi">
                                                <input type="date" wire:model="berlakuMassal" class="dsb-isian"
                                                    aria-label="Berlaku sampai tanggal">
                                            </div>
                                        @endif

                                        {{-- Keterangannya duduk DI BARIS yang sama, mengisi
                                             sisa lebarnya. Sebagai baris sendiri ia
                                             meninggalkan ruang menganggur di kanan
                                             perintahnya; di sini ia justru yang mengisinya,
                                             dan kalimatnya berganti menurut perintahnya —
                                             apa yang TIDAK ikut berubah selalu jadi
                                             pertanyaan berikutnya. --}}
                                        <span class="mh-perintah-ket">
                                            @switch($aksiMassal)
                                                @case('vendor')
                                                    Hanya kolom vendornya yang berubah — harga, satuan, dan kapasitasnya tidak disentuh.
                                                    @break
                                                @case('naik')
                                                @case('turun')
                                                    Dibulatkan ke ratusan rupiah dan tercatat di riwayat harganya. RAB yang sudah jadi tidak ikut berubah.
                                                    @break
                                                @case('berlaku')
                                                    Lewat tanggal ini harganya tetap dipakai — barisnya saja yang ditandai.
                                                    @break
                                                @case('hapus')
                                                    RAB yang sudah memakainya tetap utuh — tetapi penghapusan tidak bisa dibatalkan.
                                                    @break
                                                @case('matikan')
                                                    Tetap tersimpan, hanya tidak muncul lagi saat menyusun RAB baru.
                                                    @break
                                                @case('hidupkan')
                                                    Muncul lagi sebagai pilihan saat menyusun RAB.
                                                    @break
                                                @default
                                                    Berlaku untuk {{ count($pilih) }} baris yang dicentang. RAB yang sudah jadi tidak pernah ikut berubah.
                                            @endswitch
                                        </span>

                                        {{-- Batal dan Jalankan satu kelompok, di ujung baris
                                             yang sama. Terpisah — Batal menggantung di pojok
                                             kartu, Jalankan di baris bawah — keduanya terbaca
                                             seperti dua perintah yang tidak berhubungan, dan
                                             mata harus melompat untuk menemukan keduanya. --}}
                                        <span class="mh-perintah-tombol">
                                            <button type="button" wire:click="batalPilih" class="dsb-tombol is-lembut"
                                                title="Lepas semua centangnya">
                                                <i class="bi bi-x-lg"></i><span>Batal</span>
                                            </button>

                                        @if ($aksiMassal === 'vendor')
                                            <button type="button" wire:click="pasangVendor"
                                                wire:loading.attr="disabled" wire:target="pasangVendor"
                                                class="dsb-tombol is-aman mh-perintah-jalan">
                                                <i class="bi bi-link-45deg" wire:loading.remove wire:target="pasangVendor"></i>
                                                <span class="dsb-putar" wire:loading wire:target="pasangVendor"></span>
                                                <span>Jalankan</span>
                                            </button>
                                        @elseif (in_array($aksiMassal, ['hapus', 'matikan'], true))
                                            {{-- Yang merusak dikonfirmasi; yang bisa dibatalkan
                                                 sendiri tidak — dialog yang muncul untuk
                                                 segalanya berhenti dibaca. --}}
                                            <button type="button"
                                                class="dsb-tombol mh-perintah-jalan {{ $aksiMassal === 'hapus' ? 'is-bahaya' : 'is-awas' }} pcek-konfirmasi"
                                                data-action="jalankanMassal"
                                                data-title="{{ $aksiMassal === 'hapus' ? 'Hapus' : 'Matikan' }} {{ count($pilih) }} harga?"
                                                data-text="RAB yang sudah memakainya tidak berubah."
                                                data-confirm="Ya, lanjutkan" data-icon="warning">
                                                <i class="bi {{ $aksiMassal === 'hapus' ? 'bi-trash' : 'bi-pause-circle' }}"></i><span>Jalankan</span>
                                            </button>
                                        @else
                                            {{-- is-utama, bukan is-telaah: ini aksi pokok
                                                 kartunya, bukan "melihat". Sebagai tombol
                                                 bergaris ia terbaca pucat — apalagi saat
                                                 nonaktif — dan tidak ada yang menarik mata
                                                 ke tempat perintahnya dijalankan. --}}
                                            <button type="button" wire:click="jalankanMassal"
                                                wire:loading.attr="disabled" wire:target="jalankanMassal"
                                                @disabled($aksiMassal === '')
                                                class="dsb-tombol is-utama mh-perintah-jalan">
                                                <i class="bi bi-lightning-charge" wire:loading.remove wire:target="jalankanMassal"></i>
                                                <span class="dsb-putar" wire:loading wire:target="jalankanMassal"></span>
                                                <span>Jalankan</span>
                                            </button>
                                        @endif
                                        </span>
                                    </div>

                                    @error('aksiMassal')
                                        <span class="dsb-galat mh-pasang-kaki">{{ $message }}</span>
                                    @enderror
                                    @error('persenMassal')
                                        <span class="dsb-galat mh-pasang-kaki">{{ $message }}</span>
                                    @enderror
                                    @error('pasangKeVendor')
                                        <span class="dsb-galat mh-pasang-kaki">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>
                        @endif

                        {{-- Kotak gulirnya sendiri hanya saat barisnya banyak —
                             lihat keterangan di .mh-tabel-tinggi. --}}
                        <div class="dsb-tabel-bungkus {{ count($daftar) > 12 ? 'mh-tabel-tinggi' : '' }}">
                            <table class="dsb-tabel mh-tabel">
                                <thead>
                                    <tr>
                                        <th class="mh-centang">
                                            {{-- Mencentang yang TERLIHAT saja, bukan seluruh
                                                 tabel: mencentang baris yang tidak kelihatan
                                                 adalah cara tercepat mematikan harga yang
                                                 tidak pernah dilihat orangnya. --}}
                                            <input type="checkbox" class="form-check-input mt-0"
                                                wire:click="pilihSemua({{ json_encode(collect($daftar)->pluck('id')->all()) }})"
                                                @checked(count($pilih) > 0 && count($pilih) === count($daftar))
                                                title="Centang semua yang terlihat"
                                                aria-label="Centang semua yang terlihat">
                                        </th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Wilayah</th>
                                        {{-- Vendor dapat kolomnya sendiri: sebagai baris
                                             kecil di bawah nama, "mana yang belum ada
                                             vendornya" harus dipindai satu per satu. --}}
                                        <th title="Vendor tempat kita membelinya, beserta nomornya">Dibeli dari</th>
                                        <th class="text-end">Harga</th>
                                        {{-- Dua angka yang menjawab pertanyaan yang sama:
                                             harga ini masih relevan atau tidak. --}}
                                        <th class="text-center" title="Berapa RAB yang sudah memakai harga ini. RAB yang sudah jadi tidak ikut berubah saat harganya diubah di sini.">Dipakai</th>
                                        {{-- Tidak ada kolom status. Satu kolom berisi
                                             "Aktif" di hampir setiap baris tidak memberi
                                             tahu apa pun, dan yang satu-dua menyimpang
                                             tenggelam di antaranya — jadi yang menyimpang
                                             saja yang ditandai, di sebelah namanya. --}}
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($kategoriTerakhir = null)
                                    @foreach ($daftar as $baris)
                                        @php([$warnaKat, $ikonKat] = $rupaDari($baris['kategori']))
                                        @php($lepas = $lepasDariKatalog($baris['destinasi']))

                                        {{-- Pita kategori, seperti di PDF-nya — tetapi HANYA
                                             pada urutan yang memang mengelompokkan. Pada
                                             urutan "paling mahal dulu" pita kategori akan
                                             muncul berulang-ulang untuk kategori yang sama
                                             dan justru memecah daftar yang seharusnya utuh. --}}
                                        @if ($urut === '' && $baris['kategori'] !== $kategoriTerakhir)
                                            @php($kategoriTerakhir = $baris['kategori'])
                                            <tr class="mh-pita" wire:key="pita-{{ $baris['kategori'] }}">
                                                <td colspan="8" style="--c: {{ $warnaKat }}">
                                                    <i class="bi {{ $ikonKat }}"></i>{{ $baris['kategori_label'] }}
                                                </td>
                                            </tr>
                                        @endif

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
                                                        @if (! empty($baris['kedaluwarsa']))
                                                            {{-- Sependek mungkin: tanda yang melipat jadi
                                                                 tiga baris memecah kolom namanya, dan yang
                                                                 dibaca orang pertama kali harus tetap nama
                                                                 harganya. Kalimat panjangnya di balik sorot. --}}
                                                            <span class="mh-tanda is-lewat"
                                                                title="Berlaku sampai {{ $baris['berlaku_sampai_teks'] }} — harganya masih dipakai apa adanya, tanda ini cuma mengingatkan untuk menanyakan ulang ke vendornya">LEWAT</span>
                                                        @endif
                                                    </span>
                                                    @if ($baris['destinasi'])
                                                        <span class="{{ $lepas ? 'mh-lepas' : 'dsb-tabel-meta' }}"
                                                            @if ($lepas) title="Nama ini tidak ada di daftar destinasi, jadi tiketnya tidak akan ikut terbawa ke RAB" @endif>
                                                            <i class="bi {{ $lepas ? 'bi-exclamation-triangle-fill' : 'bi-geo-alt' }}"></i>
                                                            {{ $baris['destinasi'] }}
                                                            @if ($lepas)<span class="mh-lepas-ket">nama tidak terdaftar</span>@endif
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
                                                    @if ($masihBerlaku($baris))
                                                        <span class="dsb-tabel-meta mh-satuan">s/d {{ $baris['berlaku_sampai_teks'] }}</span>
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
                                                        <button type="button" class="mh-dipakai mh-dipakai-btn"
                                                            wire:click="bukaDipakai({{ $baris['id'] }})"
                                                            title="Lihat RAB mana saja yang memakainya">{{ $baris['jumlah_rab'] }}&times;</button>
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
                                                    {{-- Hanya muncul bila memang ada yang bisa
                                                         dilihat: tombol yang selalu membuka
                                                         panel kosong mengajari orang untuk
                                                         tidak menekannya lagi. --}}
                                                    @if ($punyaRiwayat($baris))
                                                        <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-telaah)"
                                                            wire:click="bukaRiwayat({{ $baris['id'] }})"
                                                            title="Riwayat perubahan harga">
                                                            <i class="bi bi-clock-history"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-aman)"
                                                        wire:click="salin({{ json_encode($baris) }})"
                                                        title="Buat harga baru dari salinan baris ini">
                                                        <i class="bi bi-files"></i>
                                                    </button>
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
        /* Semua label setinggi dua baris, meski isinya satu baris: keterangan
           di sebelah label panjangnya berbeda-beda, dan label yang melipat
           mendorong kotaknya sendiri turun sementara tetangganya diam —
           barisnya lalu terlihat patah. Tingginya dipatok, bukan
           keterangannya yang dipendekkan, supaya kalimatnya boleh jelas. */
        .mh-medan-rak > .dsb-medan > .dsb-label { min-height: 36px; align-items: flex-start; }
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
        .mh-pasang .dsb-isian { background: #fff; }
        .mh-pasang .dsb-tombol { flex: 0 0 auto; }
        .mh-pasang-kaki { flex: 1 1 100%; margin-top: 0 !important; }
        /* Judulnya mengambil barisnya sendiri: ia keterangan, bukan kendali.
           Yang bisa ditekan semuanya berkumpul di baris perintah di bawahnya. */
        .mh-pasang-judul { flex: 1 1 100%; }

        /* Satu baris perintah: verba, pelengkapnya, lalu Jalankan.
           Pelengkapnya melar mengisi sisa lebar — baris yang separuhnya
           kosong terbaca seperti ada yang belum selesai dipasang. */
        .mh-perintah { flex: 1 1 100%; display: flex; flex-wrap: wrap; align-items: center; gap: 8px 10px; }
        .mh-perintah > .mh-perintah-aksi { flex: 0 1 215px; }
        /* Pelengkapnya selebar yang memang dibutuhkan — kotak persen yang
           melar sepanjang baris terbaca seperti kotak yang salah. Sisa
           lebarnya diisi keterangan, bukan dibiarkan kosong. */
        .mh-perintah > .mh-perintah-isi { flex: 0 1 260px; min-width: 0; }
        .mh-perintah > .mh-perintah-isi.mh-persen { flex: 0 0 125px; }
        .mh-perintah > .mh-perintah-ket {
            flex: 1 1 220px; min-width: 0;
            font-size: .79rem; line-height: 1.45; color: var(--dsb-redup, #6b7280);
        }
        /* Dua tombol sebagai satu blok: ia tidak pernah pecah ke dua baris
           berbeda, dan jaraknya ke kotak perintah tetap sama berapa pun
           panjang keterangannya. */
        .mh-perintah > .mh-perintah-tombol { flex: 0 0 auto; display: flex; align-items: center; gap: 8px; }
        .mh-perintah .mh-perintah-isi .dsb-isian { width: 100%; }

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
        /* Merah, bukan abu-abu seperti MATI: yang satu keputusan yang memang
           diambil, yang satu tanggal yang terlewat tanpa ada yang tahu. */
        .mh-tanda.is-lewat { background: #fee2e2; color: #b42318; }
        /* NAMA SENDIRI, bukan .mh-tambah — kelas itu sudah dipakai tombol
           kecil "Destinasinya belum ada di daftar", yang justru membuang
           padding dan latarnya. Dipakai ulang di sini, tombol besarnya
           kehilangan padding dan tulisannya menempel ke tepi. */
        .mh-tambah-harga { margin-left: auto; }

        /* ===== Ringkasan ===== */
        .mh-ubin-rak { display: flex; flex-wrap: wrap; gap: 12px; }
        .mh-ubin {
            flex: 1 1 170px; min-width: 0; display: flex; flex-direction: column; gap: 2px;
            padding: 13px 15px; text-align: left; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 15px;
            /* Warna perannya hanya segaris di atas, bukan seluruh kartunya:
               lima kartu berwarna penuh bersebelahan membuat tak satu pun
               terbaca lebih penting. */
            border-top: 3px solid var(--c);
            box-shadow: 0 10px 24px -20px rgba(15, 23, 42, .5);
            transition: transform .14s ease, box-shadow .14s ease, background .14s ease;
        }
        .mh-ubin:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 16px 30px -22px rgba(15, 23, 42, .55); }
        .mh-ubin.is-diam { cursor: default; }
        /* Yang sedang memasang saringan diberi latar warnanya — supaya
           "kenapa daftarnya cuma segini" terjawab tanpa mencari-cari. */
        .mh-ubin.is-nyala { background: color-mix(in srgb, var(--c) 9%, #fff); border-color: color-mix(in srgb, var(--c) 32%, #fff); }
        .mh-ubin-label { font-size: .74rem; font-weight: 700; color: var(--dsb-redup, #6b7280); letter-spacing: .02em; }
        .mh-ubin-nilai { font-size: 1.5rem; font-weight: 800; color: var(--c); line-height: 1.1; }
        .mh-ubin-ket { font-size: .72rem; color: var(--dsb-redup, #6b7280); }

        /* ===== Pita kategori ===== */
        .mh-tabel tbody tr.mh-pita td {
            padding: 7px 14px; background: #f8fafc; border-bottom: 1px solid #eef2f7;
            border-left: 3px solid var(--c); font-size: .76rem; font-weight: 800;
            letter-spacing: .03em; text-transform: uppercase; color: var(--c);
        }
        .mh-tabel tbody tr.mh-pita td i { margin-right: 6px; }
        .mh-tabel tbody tr.mh-pita:hover td { background: #f8fafc; }

        .mh-persen { position: relative; }
        .mh-persen .dsb-isian { background: #fff; padding-right: 30px; }
        .mh-persen-tanda {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            font-size: .82rem; font-weight: 700; color: var(--dsb-redup, #6b7280); pointer-events: none;
        }

        /* ===== Riwayat ===== */
        .mh-riwayat-lama { color: var(--dsb-redup, #6b7280); text-decoration: line-through; }
        .mh-selisih { display: inline-flex; align-items: center; gap: 4px; font-weight: 800; font-size: .86rem; }
        .mh-selisih i { font-size: .78rem; }
        /* Naik merah, turun hijau — dari sudut pandang yang membayar, bukan
           yang menjual. Harga beli yang naik adalah kabar buruk. */
        .mh-selisih.is-naik { color: var(--dsb-bahaya, #be123c); }
        .mh-selisih.is-turun { color: var(--dsb-aman, #1f7a44); }

        /* ===== Kepala tabel yang lengket =====

           position:sticky menempel pada KOTAK GULIR TERDEKAT, dan pembungkus
           tabel sudah punya overflow-x:auto — yang diam-diam membuat
           overflow-y ikut auto. Jadi kepalanya melekat pada pembungkus itu,
           yang tidak pernah bergulir tegak, dan aturannya tidak melakukan
           apa pun. Tidak ada galat; kepalanya hanya ikut hilang ke atas
           seperti sebelumnya.

           Maka pembungkusnya yang dijadikan kotak gulirnya sendiri — tetapi
           HANYA saat barisnya memang banyak. Daftar lima baris di dalam
           kotak setinggi 70vh meninggalkan ruang kosong di bawahnya. */
        .mh-tabel-tinggi { max-height: 70vh; overflow-y: auto; }
        .mh-tabel-tinggi thead th { position: sticky; top: 0; z-index: 3; }
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

        /* ===== Penolakan kembaran ===== */
        .mh-kembar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            margin-top: 14px; padding: 13px 15px; border-radius: 13px;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        }
        .mh-kembar > i { font-size: 1.05rem; color: var(--dsb-awas, #b45309); }
        .mh-kembar-teks { flex: 1 1 260px; display: flex; flex-direction: column; gap: 2px; min-width: 0; }

        /* ===== Tambah lagi & tempel ===== */
        .mh-lanjut {
            display: inline-flex; align-items: center; gap: 7px; margin-right: auto;
            font-size: .82rem; font-weight: 600; color: var(--dsb-redup, #6b7280); cursor: pointer;
        }
        .mh-tempel-bungkus { margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e5e9f0; }
        .mh-tempel { display: flex; flex-direction: column; gap: 10px; }
        .mh-tempel-kepala { display: flex; flex-direction: column; gap: 3px; }
        .mh-tempel textarea.dsb-isian {
            /* Monospace: yang ditempel berkolom, dan kolom hanya terlihat
               lurus bila tiap hurufnya selebar huruf lain. */
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .82rem; line-height: 1.6; padding: 11px 13px; height: auto;
        }
        .mh-tempel-kaki { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .mh-tempel-kaki .dsb-chip { margin-right: auto; }
        .mh-tempel-gagal {
            display: flex; flex-direction: column; gap: 2px;
            padding: 11px 13px; border-radius: 11px;
            background: #fff7ed; border: 1px solid #fed7aa;
            font-size: .78rem; color: #9a3412;
        }

        /* ===== Tawaran batalkan ===== */
        .mh-batal-pita {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            padding: 12px 15px; border-radius: 15px;
            background: #f0fdf4; border: 1px solid #bbf7d0;
        }
        .mh-batal-pita > i { color: var(--dsb-aman, #1f7a44); font-size: 1.05rem; }
        .mh-batal-teks { flex: 1 1 220px; font-size: .86rem; font-weight: 600; color: #166534; min-width: 0; }

        /* ===== Dipakai di & perubahan terakhir ===== */
        .mh-dipakai-btn {
            background: none; border: 0; padding: 0; cursor: pointer;
            border-bottom: 1px dashed currentColor;
        }
        .mh-riwayat-semua { padding-top: 0; }
        .mh-jejak { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; }
        .mh-jejak li {
            display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 10px;
            padding: 9px 0; border-bottom: 1px solid #f3f5f9; font-size: .84rem;
        }
        .mh-jejak li:last-child { border-bottom: 0; }
        .mh-jejak-tanggal { flex: 0 0 120px; color: var(--dsb-redup, #6b7280); font-size: .78rem; }
        .mh-jejak-nama {
            background: none; border: 0; padding: 0; cursor: pointer;
            font-weight: 700; color: var(--dsb-tinta, #1c1f26); text-align: left;
            border-bottom: 1px dashed #cbd5e1;
        }
        .mh-jejak-nama:hover { color: var(--dsb-utama, #4f46e5); }
        .mh-jejak-angka { color: var(--dsb-redup, #6b7280); }
        .mh-jejak-oleh { color: var(--dsb-redup, #6b7280); font-size: .78rem; }

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

        /* Lima ubin yang menumpuk satu-satu memakan satu layar penuh sebelum
           kotak carinya terlihat. Di laptop ia ringkasan; di ponsel ia
           penghalang. Dua kolom membuatnya tetap terbaca dalam tiga baris. */
        @media (max-width: 767.98px) {
            .mh-ubin-rak { gap: 9px; }
            .mh-ubin { flex: 1 1 calc(50% - 5px); padding: 10px 12px; }
            .mh-ubin-nilai { font-size: 1.25rem; }
            .mh-ubin-ket { font-size: .68rem; }

            /* Tombol tambah berhenti didorong ke kanan: di layar sempit ia
               berdiri sendiri di barisnya, dan "didorong ke ujung" hanya
               menyisakan lubang di kirinya.

               Dipilih lewat .mh-saring juga, bukan .mh-tambah saja: aturan
               .mh-saring .dsb-tombol di atas berbobot lebih tinggi, dan
               tanpa ini flex-nya tidak pernah berubah. */
            .mh-saring .mh-tambah-harga { margin-left: 0; flex: 1 1 100%; justify-content: center; }

            /* Baris perintah menumpuk utuh: dua kotak yang lebarnya berbeda
               sedikit, bertumpuk, terbaca seperti salah sejajar. */
            .mh-perintah > .mh-perintah-aksi,
            .mh-perintah > .mh-perintah-isi,
            .mh-perintah > .mh-perintah-isi.mh-persen,
            .mh-perintah > .mh-perintah-tombol { flex: 1 1 100%; }
            /* Kedua tombol membagi barisnya rata; yang menjalankan diberi
               porsi lebih besar karena itu yang dituju. */
            .mh-perintah > .mh-perintah-tombol .dsb-tombol { flex: 1 1 0; justify-content: center; }
            .mh-perintah > .mh-perintah-tombol .mh-perintah-jalan { flex: 2 1 0; }
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

            /* Pita kategori cuma satu sel tanpa data-judul, jadi aturan
               "label kiri, nilai kanan" melemparkan tulisannya ke tepi kanan
               — terbaca seperti nilai dari label yang hilang. */
            .mh-tabel tbody tr.mh-pita { padding: 0; }
            .mh-tabel tbody tr.mh-pita td {
                display: block; text-align: left !important;
                padding: 8px 15px; border-left-width: 3px;
            }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
