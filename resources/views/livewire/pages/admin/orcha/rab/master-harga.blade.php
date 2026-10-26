@section('title')
Master Harga || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         daftar RAB, penyusun, dan jejak audit. Kelas orcha-* hanya untuk
         hal yang memang khas Orcha (sakelar kartu, isian rupiah, paginasi). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

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
                <a href="{{ route('admin.orcha.rab') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                    <i class="bi bi-calculator"></i><span>RAB &amp; Itinerary</span>
                </a>
                <a href="{{ route('admin.orcha.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #0369a1">
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

                            <select class="dsb-isian mh-pilih" wire:model.live="filterProvinsi" aria-label="Saring menurut provinsi">
                                <option value="">Semua provinsi</option>
                                <option value="-">Berlaku umum (semua provinsi)</option>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                @endforeach
                            </select>

                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                <i class="bi bi-plus-lg"></i><span>Tambah Harga</span>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut"
                                    style="--ikon: #b45309" title="Kembali menampilkan seluruh harga">
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
                                <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-tags"></i></span>
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
                                @if (($isian['kategori'] ?? '') === 'tiket' || $destinasiKini !== '')
                                    <div class="dsb-medan">
                                        <label class="dsb-label" for="mh-destinasi">
                                            Destinasi
                                            <span class="dsb-label-ket">dijodohkan ke itinerary</span>
                                        </label>
                                        {{-- DIPILIH, bukan diketik: harga tiket dijodohkan
                                             ke itinerary lewat nama ini PERSIS. --}}
                                        <select id="mh-destinasi" wire:model.live="isian.destinasi"
                                            class="dsb-isian @error('isian.destinasi') is-galat @enderror">
                                            <option value="">Tidak terikat destinasi</option>
                                            @if ($destinasiAsing)
                                                {{-- Nilai lama yang tidak ada di katalog TETAP
                                                     ditawarkan, supaya menyunting baris lain
                                                     tidak diam-diam menghapusnya. --}}
                                                <option value="{{ $destinasiKini }}">{{ $destinasiKini }} — tidak ada di katalog</option>
                                            @endif
                                            @foreach ($destinasi as $d)
                                                <option value="{{ $d }}">{{ $d }}</option>
                                            @endforeach
                                        </select>

                                        @if ($destinasiBaru)
                                            {{-- Kotak pilih hanya memuat yang sudah ada di
                                                 katalog, sementara destinasi baru memang
                                                 bermunculan. Tanpa jalan ini admin harus
                                                 pindah menu dan kehilangan isiannya. --}}
                                            <div class="mh-baru">
                                                <input type="text" wire:model="namaDestinasiBaru" maxlength="100"
                                                    wire:keydown.enter.prevent="tambahDestinasiKatalog"
                                                    class="dsb-isian @error('namaDestinasiBaru') is-galat @enderror"
                                                    placeholder="Nama destinasi baru, mis. Tebing Breksi">
                                                <button type="button" wire:click="tambahDestinasiKatalog"
                                                    wire:loading.attr="disabled" wire:target="tambahDestinasiKatalog"
                                                    class="dsb-tombol is-lembut" style="--ikon: #1f7a44">
                                                    <i class="bi bi-plus-lg" wire:loading.remove wire:target="tambahDestinasiKatalog"></i>
                                                    <span class="dsb-putar" wire:loading wire:target="tambahDestinasiKatalog"></span>
                                                    <span>Tambah</span>
                                                </button>
                                                <button type="button" wire:click="batalDestinasiBaru"
                                                    class="dsb-tombol is-lembut" style="--ikon: #64748b">Batal</button>
                                            </div>
                                            @error('namaDestinasiBaru')
                                                <span class="dsb-galat">{{ $message }}</span>
                                            @else
                                                <span class="dsb-kartu-sub">Namanya langsung masuk katalog Orcha, jadi bisa dipilih di itinerary juga.</span>
                                            @enderror
                                        @else
                                            <button type="button" wire:click="$set('destinasiBaru', true)" class="mh-tambah">
                                                <i class="bi bi-plus-circle"></i> Destinasinya belum ada di daftar
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-provinsi">Provinsi</label>
                                    <select id="mh-provinsi" wire:model.live="isian.provinsi" class="dsb-isian">
                                        <option value="">Berlaku umum (semua provinsi)</option>
                                        @foreach ($provinsi as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="mh-daerah">Daerah</label>
                                    <input id="mh-daerah" type="text" wire:model.live.debounce.400ms="isian.daerah"
                                        maxlength="80" class="dsb-isian" placeholder="Sleman">
                                </div>


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
                                    <span class="dsb-ikon is-kecil" style="--c: #1f7a44"><i class="bi bi-magic"></i></span>
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
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
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
                            <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-tags"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Harga tersimpan</h3>
                                <span class="dsb-kartu-sub">
                                    Tandai <strong>wajib</strong> untuk biaya yang selalu ada — makan, parkir, asuransi — dan ia masuk sendiri ke setiap RAB baru di provinsinya.
                                </span>
                            </div>
                        </div>
                        <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} harga</span>
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
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel mh-tabel">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Wilayah</th>
                                        <th class="text-end">Harga</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php([$warnaKat, $ikonKat] = $rupaDari($baris['kategori']))
                                        @php($lepas = $lepasDariKatalog($baris['destinasi']))
                                        <tr wire:key="mh-{{ $baris['id'] }}">
                                            <td data-judul="Nama">
                                                <span class="mh-utama">
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
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
                                                    <span>{{ $baris['provinsi'] ?: 'Semua provinsi' }}</span>
                                                    @if ($baris['daerah'])
                                                        <span class="dsb-tabel-meta">{{ $baris['daerah'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Harga" class="text-end">
                                                <span class="mh-utama is-kanan">
                                                    <span class="dsb-tabel-judul dsb-tabel-angka">{{ $baris['harga_teks'] }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        {{ $baris['satuan_label'] }}@if ($baris['kapasitas']) · kapasitas {{ $baris['kapasitas'] }}@endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Status">
                                                <span class="mh-status">
                                                    @if ($baris['aktif'])
                                                        <span class="dsb-lencana" style="--c: #1f7a44"><i class="bi bi-check-circle"></i>Aktif</span>
                                                    @else
                                                        <span class="dsb-lencana" style="--c: #64748b"><i class="bi bi-pause-circle"></i>Dimatikan</span>
                                                    @endif
                                                    @if ($baris['otomatis'])
                                                        <span class="dsb-lencana" style="--c: #b45309"><i class="bi bi-magic"></i>Wajib</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="dsb-tabel-aksi">
                                                    <button type="button" class="dsb-tabel-btn" style="--c: #4f46e5"
                                                        wire:click="bukaSunting({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                        title="Ubah harga ini">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: #be123c"
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
        .mh-saring > .mh-medan-cari { flex: 1 1 240px; min-width: 0; }
        .mh-saring > .mh-pilih { flex: 0 1 auto; width: auto; min-width: 150px; max-width: 210px; }
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

        /* Destinasi baru: sebaris isian + dua tombol, di dalam medannya. */
        .mh-baru {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; padding: 8px;
            border-radius: 11px; background: #f0fdfa; border: 1px solid #99f6e4;
        }
        .mh-baru .dsb-isian { flex: 1 1 170px; min-width: 0; }
        .mh-baru .dsb-tombol { flex: 0 0 auto; }
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
