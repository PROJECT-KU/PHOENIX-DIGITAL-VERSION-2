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
        // Yang tersembunyi di balik "Saringan lainnya" — kotak cari tidak
        // ikut dihitung karena ia tetap terlihat.
        $jumlahSaringLanjut = $this->jumlahSaringLanjut();

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
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ==================
             Bentuknya mengikuti Pesanan Toko: sapaan besar, lalu tanggal
             hari ini, lalu satu kalimat yang menjelaskan layarnya.

             Penanda "Mode Orcha Journey" TETAP ada — Pesanan Toko tidak
             punya karena datanya memang milik lemon sendiri. Di sini
             datanya milik situs lain, dan itu harus terbaca sekilas. --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-salam">Master Harga</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Daftar harga beli — tiket, sewa kendaraan, hotel, pemandu. Ditulis sekali di sini,
                        lalu tinggal dipilih saat menghitung biaya trip.
                    </span>
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

        {{-- ================== KARTU STATUS ==================

             Bentuknya SAMA dengan tab di Pesanan Toko: ikon berwarna, angka
             besar, label di bawahnya, dikelompokkan dengan judul kecil.
             Definisi rupanya satu, di partials/kartu-tab-gaya.

             Bedanya satu hal, dan disengaja: di Pesanan Toko kartunya tab —
             satu menyala, yang lain padam. Di sini kartunya SARINGAN, dan
             menekan kartu yang sedang menyala melepaskannya kembali. Angka
             yang menunjuk pekerjaan tetapi tidak membawa ke sana cuma
             menambah beban. --}}
        @include('livewire.pages.admin.partials.kartu-tab-gaya')

        @php
            $angka = fn ($k) => (int) ($ringkas[$k] ?? 0);
            /* "Belum dipakai" diturunkan, bukan diminta terpisah: selisih
               dua angka yang datang dari pembacaan yang sama tidak bisa
               berselisih dengan dirinya sendiri. */
            $belumDipakai = max(0, $angka('jumlah') - $angka('dipakai'));

            $kartu = [
                'jumlah' => ['Semua harga', 'bi-tags-fill', 'var(--dsb-utama)', $angka('jumlah'), null],
                'dipakai' => ['Sudah dipakai', 'bi-check2-circle', 'var(--dsb-aman)', $angka('dipakai'), ['filterDipakai', 'ya']],
                'belum' => ['Belum dipakai', 'bi-hourglass-split', 'var(--dsb-netral)', $belumDipakai, ['filterDipakai', 'belum']],
                'tanpa_vendor' => ['Belum ada vendor', 'bi-shop', 'var(--dsb-bahaya)', $angka('tanpa_vendor'), ['filterVendor', '-']],
                'kedaluwarsa' => ['Masa berlakunya lewat', 'bi-alarm-fill', 'var(--dsb-awas)', $angka('kedaluwarsa'), ['filterStatus', 'kedaluwarsa']],
                'mati' => ['Sudah dimatikan', 'bi-power', 'var(--dsb-netral)', $angka('mati'), ['filterStatus', 'mati']],
            ];

            $grupKartu = [
                ['Isi daftar', 'Berapa yang sudah pernah terpakai', ['jumlah', 'dipakai', 'belum']],
                ['Perlu dibereskan', 'Harga yang menunggu ditindaklanjuti', ['tanpa_vendor', 'kedaluwarsa', 'mati']],
            ];
        @endphp

        @if ($ringkas !== [])
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="k-12">
                        {{-- Kedua kelompok sama besar (3 kartu lawan 3), jadi
                             --grup-kolom ditimpa; bawaannya 7fr/3fr untuk
                             Pesanan Toko yang 7 tab lawan 3. --}}
                        <div class="pt-grup-deret mh-kartu-status" style="--grup-kolom: minmax(0, 1fr) minmax(0, 1fr)">
                            @foreach ($grupKartu as [$judulGrup, $ketGrup, $kunciGrup])
                                <nav class="pt-grup" aria-label="{{ $judulGrup }}">
                                    <div class="pt-grup-kepala">
                                        <span class="pt-grup-judul">{{ $judulGrup }}</span>
                                        <span class="pt-grup-ket">{{ $ketGrup }}</span>
                                    </div>
                                    <div class="pt-kartu-deret" style="--n: {{ count($kunciGrup) }}">
                                        @foreach ($kunciGrup as $kunci)
                                            @php
                                                [$label, $ikon, $warna, $nilai, $saring] = $kartu[$kunci];
                                                $nyala = $saring && (string) $this->{$saring[0]} === $saring[1];
                                            @endphp
                                            <button type="button" style="--c: {{ $warna }}"
                                                class="pt-kartu-tab {{ $nyala ? 'is-aktif' : '' }} {{ $nilai ? '' : 'is-nol' }} {{ $saring ? '' : 'mh-kartu-diam' }}"
                                                aria-pressed="{{ $nyala ? 'true' : 'false' }}"
                                                @if ($saring)
                                                    wire:click="$set('{{ $saring[0] }}', '{{ $nyala ? '' : $saring[1] }}')"
                                                    title="{{ $nyala ? 'Lepas saringannya' : 'Lihat yang '.mb_strtolower($label) }}"
                                                @else
                                                    disabled title="Seluruh harga yang tersimpan"
                                                @endif>
                                                <span class="pt-kartu-ikon"><i class="bi {{ $ikon }}"></i></span>
                                                <span class="pt-kartu-teks">
                                                    <span class="pt-kartu-angka">{{ number_format($nilai, 0, ',', '.') }}</span>
                                                    <span class="pt-kartu-label">{{ $label }}</span>
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </nav>
                            @endforeach
                        </div>
                    </div>
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
                        {{-- Cari dulu, sisanya dilipat — pola yang sama dengan
                             Pesanan Toko. Enam kotak saringan berjajar membuat
                             baris pertamanya penuh benda yang jarang disentuh,
                             dan daftarnya terdorong jauh ke bawah layar. --}}
                        <div x-data="{ buka: {{ $jumlahSaringLanjut ? 'true' : 'false' }} }">
                        <div class="mh-saring mh-saring-utama">
                            <div class="mh-medan-cari">
                                {{-- Partial bersama, rupa 'dsb' — layar ini memuat
                                     bahasa rupa dasbor, jadi kotak carinya bisa
                                     sebangun dengan Pesanan Toko. Memasangnya
                                     sendiri di sini akan membuatnya berbeda dari
                                     lima belas layar Orcha lainnya begitu salah
                                     satunya disentuh. --}}
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari nama, destinasi, atau daerah…',
                                    'rupa' => 'dsb',
                                ])
                            </div>

                            <button type="button" wire:click="bukaSalin" class="dsb-tombol is-telaah"
                                title="Salin seluruh harga satu provinsi ke provinsi lain">
                                <i class="bi bi-files"></i><span>Salin antar provinsi</span>
                            </button>
                            {{-- Halaman tersendiri, bukan kartu yang menyelip di
                                 dalam daftar. --}}
                            <a href="{{ route('admin.orcha.master-harga.tambah') }}" class="dsb-tombol is-utama" wire:navigate>
                                <i class="bi bi-plus-lg"></i><span>Tambah Harga</span>
                            </a>

                            {{-- Sebaris dengan tombol lain, bukan berdiri sendiri
                                 di baris berikutnya: tombol tunggal yang
                                 menggantung terbaca seperti sisa, bukan seperti
                                 pilihan yang setara. --}}
                            <button type="button" class="dsb-tombol is-lembut" x-on:click="buka = !buka" :aria-expanded="buka">
                                <i class="bi bi-sliders"></i><span>Saringan lainnya</span>
                                @if ($jumlahSaringLanjut)
                                    <span class="pt-tab-jumlah is-isi">{{ $jumlahSaringLanjut }}</span>
                                @endif
                                <i class="bi bi-chevron-down mh-lanjutan-panah" :class="buka && 'is-buka'"></i>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-awas" title="Kembali menampilkan seluruh harga">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>

                        {{-- Terbuka sendiri bila ada saringan yang sedang aktif:
                             saringan yang bekerja tetapi tersembunyi membuat
                             daftar yang "kurang" terlihat seperti data hilang. --}}
                        <div class="mh-saring mh-saring-lanjut" x-show="buka" x-cloak>
                            <select class="dsb-isian mh-pilih" wire:model.live="filterKategori" aria-label="Saring menurut kategori">
                                <option value="" @selected((string) $this->filterKategori === '')>Semua kategori</option>
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
                                        <option value="{{ $v['id'] }}" @selected((string) $this->filterVendor === (string) $v['id'])>{{ $v['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <select class="dsb-isian mh-pilih" wire:model.live="filterDipakai" aria-label="Saring menurut pemakaian">
                                <option value="" @selected((string) $this->filterDipakai === '')>Semua harga</option>
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
                                <option value="" @selected((string) $this->filterStatus === '')>Aktif &amp; dimatikan</option>
                                @foreach ([
                                    'aktif' => 'Hanya yang aktif',
                                    'mati' => 'Hanya yang dimatikan',
                                    'kedaluwarsa' => 'Masa berlakunya lewat',
                                ] as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($filterStatus === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian mh-pilih" wire:model.live="urut" aria-label="Urutkan daftar">
                                <option value="" @selected((string) $this->urut === '')>Urut: kelompok</option>
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

                        </div>{{-- /.mh-saring-lanjut --}}
                        </div>{{-- /x-data --}}
                    </div>
                </div>
            </div>
        </section>


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

                        @if (! empty($dipakaiMeta['dipotong']))
                            <div class="dsb-kartu-isi mh-jejak-potong">
                                Menampilkan 100 RAB terbaru dari
                                <strong>{{ number_format((int) $dipakaiMeta['jumlah'], 0, ',', '.') }}</strong> yang memakainya.
                            </div>
                        @endif

                        @if ($dipakai === [])
                            <div class="dsb-kartu-isi">
                                <p class="mh-jejak-nihil">
                                    <i class="bi bi-diagram-3"></i>
                                    Belum dipakai RAB mana pun — aman diubah atau dihapus tanpa menyentuh penawaran.
                                </p>
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

        {{-- ================== SALIN ANTAR PROVINSI ================== --}}
        @if ($salinBuka)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-files"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Salin harga antar provinsi</h3>
                                    <span class="dsb-kartu-sub">
                                        Seluruh harga provinsi asal disalin ke provinsi tujuan. Yang namanya
                                        <strong>sudah ada</strong> di tujuan dilewati — bukan ditimpa, supaya harga yang
                                        sudah disesuaikan tangan tidak hilang. Kolom daerah tidak ikut.
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="tutupSalin" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="mh-perintah">
                                <div class="mh-perintah-isi" wire:key="salin-dari-{{ $salinDari }}" wire:ignore
                                    x-data x-init="pasangSelectCari($el, 'salinDari', { placeholder: 'Disalin dari…' })">
                                    <select class="dsb-isian">
                                        <option value="">Disalin dari…</option>
                                        @foreach ($provinsi as $p)
                                            <option value="{{ $p }}" @selected($salinDari === $p)>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <span class="mh-perintah-panah"><i class="bi bi-arrow-right"></i></span>

                                <div class="mh-perintah-isi" wire:key="salin-ke-{{ $salinKe }}" wire:ignore
                                    x-data x-init="pasangSelectCari($el, 'salinKe', { placeholder: 'Disalin ke…' })">
                                    <select class="dsb-isian @error('salinKe') is-galat @enderror">
                                        <option value="">Disalin ke…</option>
                                        @foreach ($provinsi as $p)
                                            <option value="{{ $p }}" @selected($salinKe === $p)>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <span class="mh-perintah-tombol">
                                    <button type="button" wire:click="salinProvinsi"
                                        wire:loading.attr="disabled" wire:target="salinProvinsi"
                                        class="dsb-tombol is-utama">
                                        <i class="bi bi-files" wire:loading.remove wire:target="salinProvinsi"></i>
                                        <span class="dsb-putar" wire:loading wire:target="salinProvinsi"></span>
                                        <span>Salin</span>
                                    </button>
                                </span>
                            </div>

                            @error('salinKe')<span class="dsb-galat">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </section>
        @endif


        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat"
                    wire:target="cari,filterKategori,filterProvinsi,filterStatus,urut,bersihkanSaringan,bersihkanCari,keHalaman">
                    {{-- Redup saja tidak cukup: pada sambungan lambat layarnya
                         terbaca seperti membeku, dan orang menekan tombolnya
                         lagi. Pita ini mengatakan apa yang sedang terjadi. --}}
                    <div class="mh-pita-muat" wire:loading.flex
                        wire:target="cari,filterKategori,filterProvinsi,filterVendor,filterDipakai,filterStatus,urut,barisHalaman,bersihkanSaringan,bersihkanCari,keHalaman,pilihSemuaCocok">
                        <span class="dsb-putar"></span>
                        <span>Memuat daftarnya…</span>
                    </div>

                    <div class="dsb-kartu-kepala mh-daftar-kepala">
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
                            <a href="{{ route('admin.orcha.master-harga.riwayat-semua') }}"
                                class="dsb-tombol is-telaah" title="Perubahan harga terakhir di seluruh daftar" wire:navigate>
                                <i class="bi bi-clock-history"></i><span>Perubahan terakhir</span>
                            </a>
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
                                        {{-- Ditawarkan hanya bila memang ada yang belum
                                             tercentang di halaman lain; menawarkan "semua"
                                             saat semuanya sudah tercentang cuma menambah
                                             tombol yang tidak melakukan apa-apa. --}}
                                        @if ($jumlahData > count($pilih))
                                            <button type="button" wire:click="pilihSemuaCocok"
                                                wire:loading.attr="disabled" wire:target="pilihSemuaCocok"
                                                class="mh-pilih-semua">
                                                Centang semua {{ number_format($jumlahData, 0, ',', '.') }} yang cocok saringan
                                            </button>
                                        @endif
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
                                            <option value="" @selected((string) $this->aksiMassal === '')>Mau diapakan?</option>
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
                                                        <option value="{{ $v['id'] }}" @selected((string) $this->pasangKeVendor === (string) $v['id'])>{{ $v['nama'] }}@if (! empty($v['kontak'])) — {{ $v['kontak'] }}@endif</option>
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
                                        {{-- Kategori TIDAK punya kolomnya sendiri lagi.

                                             Satu kolom berisi satu lencana membuat enam
                                             kolom berebut lebar, dan yang kalah justru
                                             nama & vendornya — keduanya melipat jadi dua
                                             baris sementara kolom kategori duduk lapang.
                                             Kini ikonnya memimpin sel nama (pola yang sama
                                             dengan Pesanan Toko) dan namanya ikut sebagai
                                             lencana kecil di bawahnya. --}}
                                        <th>Nama</th>
                                        <th class="mh-k-wilayah">Berlaku di</th>
                                        {{-- Vendor dapat kolomnya sendiri: sebagai baris
                                             kecil di bawah nama, "mana yang belum ada
                                             vendornya" harus dipindai satu per satu. --}}
                                        <th class="k-sedang" title="Vendor tempat kita membelinya, beserta nomornya">Dibeli dari</th>
                                        {{-- Menekan judul kolom adalah yang dicoba orang
                                             lebih dulu, sebelum kotak "Urut" ditemukan.
                                             Panahnya hanya muncul pada kolom yang sedang
                                             dipakai — tanda pada semua kolom sekaligus
                                             berhenti menandai apa pun. --}}
                                        <th class="text-end">
                                            <button type="button" wire:click="urutkanLewatKolom('harga')"
                                                class="mh-urut {{ $this->arahUrut('harga') ? 'is-nyala' : '' }}"
                                                title="Urutkan menurut harga">
                                                Harga
                                                @if ($this->arahUrut('harga'))
                                                    <i class="bi bi-caret-{{ $this->arahUrut('harga') === 'turun' ? 'down' : 'up' }}-fill"></i>
                                                @endif
                                            </button>
                                        </th>
                                        {{-- Dua angka yang menjawab pertanyaan yang sama:
                                             harga ini masih relevan atau tidak. --}}
                                        <th class="text-center">
                                            <button type="button" wire:click="urutkanLewatKolom('dipakai')"
                                                class="mh-urut {{ $this->arahUrut('dipakai') ? 'is-nyala' : '' }}"
                                                title="Berapa RAB yang sudah memakai harga ini. RAB yang sudah jadi tidak ikut berubah saat harganya diubah di sini. Tekan untuk mengurutkan.">
                                                Dipakai
                                                @if ($this->arahUrut('dipakai'))
                                                    <i class="bi bi-caret-down-fill"></i>
                                                @endif
                                            </button>
                                        </th>
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
                                                <td colspan="7" style="--c: {{ $warnaKat }}">
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
                                                {{-- Sel jati diri, pola yang sama dengan Pesanan
                                                     Toko: ikon berwarna memimpin, nama di
                                                     sebelahnya, lalu satu baris lencana kecil. --}}
                                                <span class="dsb-tabel-utama">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warnaKat }}">
                                                        <i class="bi {{ $ikonKat }}"></i>
                                                    </span>
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>

                                                        <span class="dsb-tabel-meta">
                                                            <span class="dsb-lencana" style="--c: {{ $warnaKat }}">{{ $baris['kategori_label'] }}</span>

                                                            {{-- Kata biasa, bukan singkatan huruf besar.
                                                                 "WAJIB" tidak memberi tahu wajib apa;
                                                                 yang dimaksud harga ini ikut masuk sendiri
                                                                 ke tiap RAB baru tanpa dipilih. --}}
                                                            @unless ($baris['aktif'])
                                                                <span class="dsb-lencana is-abu" title="Tetap tersimpan, tetapi tidak muncul lagi saat menyusun RAB baru">
                                                                    <i class="bi bi-pause-circle"></i>Dimatikan
                                                                </span>
                                                            @endunless
                                                            @if ($baris['otomatis'])
                                                                <span class="dsb-lencana is-ungu" title="Ikut masuk sendiri ke setiap RAB baru di provinsinya, tanpa dipilih">
                                                                    <i class="bi bi-magic"></i>Masuk sendiri
                                                                </span>
                                                            @endif
                                                            @if (! empty($baris['kedaluwarsa']))
                                                                <span class="dsb-lencana is-kuning"
                                                                    title="Berlaku sampai {{ $baris['berlaku_sampai_teks'] }} — harganya masih dipakai apa adanya, tanda ini cuma mengingatkan untuk menanyakan ulang ke vendornya">
                                                                    <i class="bi bi-alarm"></i>Masa berlakunya lewat
                                                                </span>
                                                            @endif

                                                            @if ($baris['destinasi'])
                                                                <span class="{{ $lepas ? 'mh-lepas' : 'dsb-tabel-meta' }}"
                                                                    @if ($lepas) title="Nama ini tidak ada di daftar destinasi, jadi tiketnya tidak akan ikut terbawa ke RAB" @endif>
                                                                    <i class="bi {{ $lepas ? 'bi-exclamation-triangle-fill' : 'bi-geo-alt' }}"></i>
                                                                    {{ $baris['destinasi'] }}
                                                                    @if ($lepas)
                                                                        {{-- Tanda yang menunjuk pekerjaan tetapi tidak
                                                                             membawa ke sana cuma menambah beban. --}}
                                                                        <a class="mh-lepas-ket" href="{{ route('admin.orcha.master-harga.ubah', $baris['id']) }}" wire:navigate
                                                                            title="Nama ini tidak ada di daftar destinasi — buka dan pilih dari daftarnya">belum terdaftar</a>
                                                                    @endif
                                                                </span>
                                                            @endif

                                                            {{-- Salinan kolom yang disembunyikan di layar
                                                                 sempit — tanpa ini wilayah & vendornya hilang
                                                                 sama sekali, bukan cuma pindah tempat. --}}
                                                            <span class="dsb-tabel-samar">
                                                                <i class="bi bi-geo"></i>{{ $baris['provinsi'] ?: 'Semua provinsi' }}
                                                            </span>
                                                            <span class="dsb-tabel-samar">
                                                                <i class="bi bi-shop"></i>{{ $baris['vendor'] ?: 'vendor belum ada' }}
                                                            </span>
                                                        </span>

                                                        @if ($baris['catatan'])
                                                            <span class="dsb-tabel-meta mh-catatan">{{ $baris['catatan'] }}</span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="mh-k-wilayah" data-judul="Berlaku di">
                                                <span class="mh-utama">
                                                    <span class="mh-wilayah">{{ $baris['provinsi'] ?: 'Semua provinsi' }}</span>
                                                    @if ($baris['daerah'])
                                                        <span class="dsb-tabel-meta">{{ $baris['daerah'] }}</span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="k-sedang" data-judul="Dibeli dari">
                                                @if (! empty($baris['vendor']))
                                                    <a href="{{ route('admin.orcha.vendor.detail', $baris['vendor_id']) }}" wire:navigate
                                                        class="mh-utama mh-vendor-sel" title="{{ $baris['vendor'] }} — buka rinciannya">
                                                        {{-- Dipotong, nama utuhnya di balik sorot: nama
                                                             vendor memang panjang-panjang, dan dibiarkan
                                                             utuh satu sel memakan enam baris tinggi lalu
                                                             mendorong seluruh tabel ikut setinggi itu. --}}
                                                        <span>{{ \Illuminate\Support\Str::limit($baris['vendor'], 26) }}</span>
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
                                                    {{-- Harga akhir pekan di baris kedua sel yang
                                                         sama, bukan kolom sendiri: kolom yang kosong
                                                         di sembilan dari sepuluh baris memakan lebar
                                                         yang dibutuhkan nama dan vendor. --}}
                                                    @if (! empty($baris['dua_harga']))
                                                        <span class="mh-akhir-pekan"
                                                            title="Tarif ini dipakai pada Sabtu, Minggu, dan tanggal yang terdaftar di Hari Libur">
                                                            <i class="bi bi-calendar-event"></i>{{ $baris['harga_akhir_pekan_teks'] }} akhir pekan
                                                        </span>
                                                    @endif
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
                                                        <a class="dsb-tabel-btn" style="--c: var(--dsb-telaah)"
                                                            href="{{ route('admin.orcha.master-harga.riwayat', $baris['id']) }}"
                                                            title="Riwayat perubahan harga" wire:navigate>
                                                            <i class="bi bi-clock-history"></i>
                                                        </a>
                                                    @endif
                                                    <button type="button" class="dsb-tabel-btn" style="--c: var(--dsb-aman)"
                                                        wire:click="salin({{ $baris['id'] }})"
                                                        title="Buat harga baru dari salinan baris ini">
                                                        <i class="bi bi-files"></i>
                                                    </button>
                                                    <a class="dsb-tabel-btn" style="--c: var(--dsb-utama)"
                                                        href="{{ route('admin.orcha.master-harga.ubah', $baris['id']) }}" wire:navigate
                                                        title="Ubah harga ini">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
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
                        {{-- Sepuluh baris benar untuk membaca sekilas, dan salah
                             begitu daftarnya diurutkan: "mana yang paling mahal"
                             dijawab dengan menggulir dua puluh halaman. --}}
                        <label class="mh-baris-pilih">
                            <span>Tampilkan</span>
                            <select class="dsb-isian" wire:model.live="barisHalaman" aria-label="Jumlah baris per halaman">
                                @foreach ([10, 25, 50, 100] as $n)
                                    <option value="{{ $n }}" @selected((int) $barisHalaman === $n)>{{ $n }} baris</option>
                                @endforeach
                            </select>
                        </label>
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    @include('livewire.pages.admin.orcha.rab.partials.master-harga-gaya')


    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
