@section('title')
Pendaftaran Trip || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*), supaya dua
         daftar yang dikerjakan orang yang sama tidak perlu dipelajari dua
         kali. Kartu tab dipakai bersama lewat partial — satu rupa, satu
         definisi. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.partials.kartu-tab-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $bolehKesehatan = auth()->user()->hasPermission('view_orcha_kesehatan');
        $adaSaringan = $this->adaSaringan();
        $warnaTab = $tabs[$tabAktif][2] ?? '#7c3aed';
        $targetMuat = 'cari,filterPaket,setTab,bersihkanSaringan,keHalaman';
    @endphp

    <div class="dsb pd-daftar">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Pendaftaran Trip</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="d-block">
                        Semua orang yang mendaftar trip — dari website maupun yang Anda masukkan sendiri.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran.daftarkan') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                    title="Masukkan rombongan yang sudah sepakat lewat WhatsApp">
                    <i class="bi bi-person-plus"></i><span>Daftarkan Rombongan</span>
                </a>

                {{-- Jalan ke daftar tunggu, dari tempat pertanyaannya muncul.

                     Antrean sudah punya layarnya sendiri, tetapi tidak ada
                     jalan ke sana dari sini — padahal saat yang orang teringat
                     daftar tunggu adalah saat ia baru membatalkan satu
                     pendaftaran dan satu kursi menganggur. Saringannya ikut
                     dibawa supaya yang terbuka antrean rombongan yang sama. --}}
                {{-- Tanggal rombongan ikut dibawa saat ada yang disaring.

                     Saat yang orang teringat daftar tunggu adalah saat ia baru
                     membatalkan satu pendaftaran dan satu kursi menganggur —
                     dan yang ia butuhkan pada saat itu bukan daftar antrean,
                     melainkan kalimat yang siap dikirim lengkap dengan
                     tanggalnya. --}}
                @php
                    $rombonganTerpilih = collect($this->keberangkatanPaket())
                        ->firstWhere('id', (int) $this->filterKeberangkatan);
                @endphp

                <a href="{{ route('admin.orcha.daftar-tunggu', array_filter([
                        'paket_id' => $this->filterPaket,
                        'tawarkan' => $rombonganTerpilih['rentang_tanggal'] ?? null,
                    ])) }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #7c3aed"
                    title="{{ $rombonganTerpilih
                        ? 'Tawarkan kursi rombongan '.$rombonganTerpilih['rentang_tanggal'].' ke yang mengantre'
                        : 'Orang yang menunggu kursi — tawarkan ke mereka begitu ada yang membatalkan' }}">
                    <i class="bi bi-hourglass-split"></i>
                    <span>{{ $rombonganTerpilih ? 'Tawarkan ke Daftar Tunggu' : 'Daftar Tunggu' }}</span>
                </a>

                {{-- Pembagian kursi & kamar, muncul hanya setelah satu
                     rombongan dipilih.

                     Satuannya rombongan: tanpa saringan itu, tombol ini tidak
                     punya satuan untuk dibagi — dan tombol yang menanyakan
                     "rombongan mana?" setelah ditekan lebih buruk daripada
                     tombol yang belum ada. --}}
                @if ($this->filterKeberangkatan !== '')
                    <a href="{{ route('admin.orcha.pembagian', $this->filterKeberangkatan) }}"
                        wire:navigate class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                        title="Bagi tempat duduk bus dan kamar hotel untuk rombongan yang sedang disaring">
                        <i class="bi bi-grid-3x3-gap"></i><span>Bagi Kursi &amp; Kamar</span>
                    </a>
                @endif

                {{-- Rekap kantor, bukan manifes lapangan. Tidak memuat data
                     kesehatan, jadi tidak ikut dijaga izin itu. --}}
                <a href="{{ route('admin.orcha.pendaftaran.rekap', $this->saringanTampil()) }}"
                    class="dsb-tombol is-lembut" style="--ikon: #16a34a"
                    title="Satu baris per pemesanan berisi angka uang — untuk rekap di kantor">
                    <i class="bi bi-file-earmark-spreadsheet"></i><span>Unduh Rekap (Excel)</span>
                </a>

                @if ($bolehKesehatan)
                    <a href="{{ route('admin.orcha.pendaftaran.manifes', $this->saringanTampil()) }}"
                        class="dsb-tombol is-utama"
                        title="Unduh daftar peserta untuk tour leader — isinya mengikuti yang sedang tampil">
                        <i class="bi bi-filetype-pdf"></i><span>Unduh Daftar Peserta</span>
                    </a>
                @endif
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== TAB ==================

             Dua kelompok, dan pemisahannya disengaja: yang atas cara MELIHAT
             daftar, yang bawah DAFTAR PEKERJAAN. Digabung, "Perlu ditelepon"
             tenggelam jadi salah satu pilihan menonton — padahal ia
             satu-satunya yang uangnya bisa hangus kalau didiamkan. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="k-12">
                <div class="pt-grup-deret">
                    @foreach ($grupTab as [$judulGrup, $ketGrup, $kunciGrup])
                        <nav class="pt-grup" aria-label="{{ $judulGrup }}">
                            <div class="pt-grup-kepala">
                                <span class="pt-grup-judul">{{ $judulGrup }}</span>
                                <span class="pt-grup-ket">{{ $ketGrup }}</span>
                            </div>
                            <div class="pt-kartu-deret" style="--n: {{ count($kunciGrup) }}">
                                @foreach ($kunciGrup as $kunci)
                                    @php
                                        [$label, $ikon, $warna] = $tabs[$kunci];
                                        $angka = $jumlahTab[$kunci] ?? null;
                                        $iniAktif = $tabAktif === $kunci;
                                    @endphp
                                    <button type="button" wire:click="setTab('{{ $kunci }}')" style="--c: {{ $warna }}"
                                        class="pt-kartu-tab {{ $iniAktif ? 'is-aktif' : '' }} {{ $angka === 0 ? 'is-nol' : '' }}"
                                        aria-pressed="{{ $iniAktif ? 'true' : 'false' }}">
                                        <span class="pt-kartu-ikon"><i class="bi {{ $ikon }}"></i></span>
                                        <span class="pt-kartu-teks">
                                            {{-- Angkanya hilang, bukan jadi "0", bila Orcha belum
                                                 mengirimnya: nol yang keliru lebih buruk daripada
                                                 tidak ada angka sama sekali. --}}
                                            <span class="pt-kartu-angka">
                                                {{ $angka === null ? '–' : (999 < $angka ? '999+' : number_format($angka, 0, ',', '.')) }}
                                            </span>
                                            <span class="pt-kartu-label">{{ $label }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </nav>
                    @endforeach
                </div>
            </div>

            {{-- ================== SARINGAN ================== --}}
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="pd-saring">
                        <div class="dsb-medan">
                            <label class="dsb-label">Cari orangnya</label>
                            @include('livewire.pages.admin.orcha.partials.cari', [
                                'petunjuk' => 'Nama, nomor WhatsApp, atau kode pendaftaran',
                                'rupa' => 'dsb',
                            ])
                        </div>

                        <div class="dsb-medan">
                            <label class="dsb-label" for="pd-paket">Keberangkatan</label>
                            <select id="pd-paket" wire:model.live="filterPaket" class="dsb-isian">
                                <option value="" @selected((string) $this->filterPaket === '')>Semua keberangkatan</option>
                                @foreach ($pilihanPaket as $paket)
                                    <option value="{{ $paket['id'] }}" @selected((string) $this->filterPaket === (string) $paket['id'])>
                                        {{ $paket['nama'] }}@if ($paket['tanggal_berangkat'])
                                            · {{ \Carbon\Carbon::parse($paket['tanggal_berangkat'])->locale('id')->translatedFormat('d M Y') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Saringan rombongan, satu tingkat di bawah paket.

                             Muncul hanya sesudah paketnya dipilih dan memang punya
                             lebih dari satu rombongan: menawarkan seluruh rombongan
                             dari semua paket sekaligus menghasilkan daftar panjang
                             yang tanggalnya tidak bisa dibedakan satu sama lain. --}}
                        @php $pilihanKb = $this->keberangkatanPaket(); @endphp

                        @if (1 < count($pilihanKb))
                            <div class="dsb-medan">
                                <label class="dsb-label" for="pd-rombongan">Rombongan</label>
                                <select id="pd-rombongan" wire:model.live="filterKeberangkatan" class="dsb-isian">
                                    <option value="" @selected($this->filterKeberangkatan === '')>Semua rombongan</option>
                                    @foreach ($pilihanKb as $kb)
                                        <option value="{{ $kb['id'] }}" @selected($this->filterKeberangkatan === (string) $kb['id'])>
                                            {{ $kb['rentang_tanggal'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Pemilih urutan, berlabel dan bertulisan penuh.

                             Kepala kolom yang bisa diklik juga ada (lihat
                             tabelnya), tetapi tidak terlihat bisa diklik sampai
                             dicoba — dan admin yang belum pernah mencobanya
                             tidak akan menemukan urutan sama sekali. --}}
                        <div class="dsb-medan">
                            <label class="dsb-label" for="pd-urut">Urutkan</label>
                            <select id="pd-urut" wire:model.live="urut" class="dsb-isian">
                                @foreach ($pilihanUrut as $kunci => $pilihan)
                                    <option value="{{ $kunci }}" @selected($urut === $kunci)>{{ $pilihan[0] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="dsb-medan pd-medan-aksi">
                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua lagi</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($adaSaringan && $bolehKesehatan)
                        {{-- Akibat yang tidak terlihat dari layar: berkas yang
                             diunduh mengikuti saringan yang sedang hidup. Tour
                             leader yang membawa daftar hasil saringan lupa akan
                             berdiri di parkiran dengan daftar yang kurang satu
                             rombongan. --}}
                        <div class="pd-catatan">
                            <i class="bi bi-funnel-fill"></i>
                            <span>
                                Daftar sedang disaring. <strong>Unduh Daftar Peserta</strong> ikut tersaring juga —
                                kosongkan dulu kalau tour leader butuh daftar lengkapnya.
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================== DAFTARNYA ================== --}}
        <div class="dsb-rak">
            <div class="dsb-kartu k-12" wire:loading.class="dsb-sedang-muat" wire:target="{{ $targetMuat }}">
                <div class="pt-daftar-kepala" style="--c: {{ $warnaTab }}">
                    <div class="pt-daftar-judul">
                        <b>{{ $tabs[$tabAktif][0] ?? 'Semua' }}</b>
                        <span>
                            @if (($meta['total'] ?? 0) > 0)
                                {{ number_format($meta['total'], 0, ',', '.') }} pendaftaran
                                @if ($tabAktif === 'perlu_ditagih')
                                    — sudah bayar DP, belum lunas, dan tanggalnya sudah dekat. Yang paling mepet di atas.
                                @endif
                            @else
                                Belum ada isinya
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Urutan yang DITIMPA oleh tab pekerjaan.

                     Di tab "Perlu ditelepon", Orcha memaksa yang paling mepet
                     di atas apa pun yang dipilih di pemilih urutan — daftar
                     pekerjaan yang urutannya bisa diacak berhenti jadi daftar
                     pekerjaan. Disebut di sini supaya pemilih yang tampak
                     diabaikan tidak terbaca sebagai fitur yang rusak. --}}
                @if ($tabAktif === 'perlu_ditagih' && $urut !== 'terbaru')
                    <div class="dsb-kartu-isi pb-0">
                        <div class="pd-catatan">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>
                                Di tab <strong>Perlu ditelepon</strong>, yang berangkatnya paling dekat
                                selalu di atas — pilihan urutan Anda dipakai sebagai pemisah saja.
                            </span>
                        </div>
                    </div>
                @endif

                {{-- ===== TINDAKAN UNTUK YANG DICENTANG =====

                     Mengabari dua puluh pendaftar satu rombongan sebelumnya
                     berarti membuka dua puluh halaman dan menyalin dua puluh
                     pesan. Yang disiapkan di sini bukan pengiriman massal —
                     WhatsApp tidak mengizinkannya dari peramban — melainkan
                     satu pesan yang sama beserta daftar nomornya, sehingga yang
                     tersisa menekan kirim satu per satu. --}}
                @if ($dipilih !== [])
                    <div class="dsb-kartu-isi pb-0">
                        <div class="pd-massal">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-check2-square"></i></span>

                            <div class="pd-massal-teks">
                                <strong>{{ count($dipilih) }} pendaftaran dicentang</strong>
                                @if (count($barisDipilih) < count($dipilih))
                                    {{-- Selisihnya DISEBUT. Admin yang mencentang lima
                                         lalu melihat tiga tautan perlu tahu dua sisanya
                                         ada di halaman sebelumnya — bukan menduga
                                         tautannya gagal dirakit. --}}
                                    <span>
                                        {{ count($barisDipilih) }} di halaman ini;
                                        {{ count($dipilih) - count($barisDipilih) }} lagi di halaman lain
                                        dan nomornya belum terbaca dari sini.
                                    </span>
                                @else
                                    <span>Semuanya ada di halaman ini.</span>
                                @endif
                            </div>

                            <div class="pd-massal-aksi">
                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                    wire:click="kosongkanPilihan">
                                    <i class="bi bi-x-circle"></i><span>Kosongkan</span>
                                </button>

                                {{-- Menggabungkan menuntut TEPAT DUA.

                                     Tombolnya tetap terlihat di luar itu supaya
                                     admin tahu fiturnya ada — yang ditolak
                                     adalah penekanannya, lengkap dengan
                                     sebabnya. Tombol yang baru muncul pada
                                     jumlah centangan tertentu tidak pernah
                                     ditemukan orang yang tidak mencarinya. --}}
                                @if (count($dipilih) === 2)
                                    <button type="button" class="dsb-tombol is-lembut" style="--ikon: #b45309"
                                        wire:click="bukaFormulirGabung"
                                        title="Satukan dua pemesanan yang sebenarnya satu rombongan">
                                        <i class="bi bi-union"></i><span>Gabungkan</span>
                                    </button>
                                @endif

                                <button type="button" class="dsb-tombol is-aman" wire:click="bukaKirimMassal">
                                    <i class="bi bi-whatsapp"></i><span>Siapkan pesan WhatsApp</span>
                                </button>
                            </div>
                        </div>

                        {{-- Menandai keadaan beberapa sekaligus.

                             "Batal" tidak ada di daftar ini: membatalkan melepas
                             kursi dan mengabari daftar tunggu, dan dua puluh
                             pembatalan dari satu salah klik tidak bisa
                             dibatalkan. Yang itu tetap satu per satu. --}}
                        @if ($pilihanKeadaanMassal !== [])
                            <div class="pd-massal-keadaan">
                                <span><i class="bi bi-tag"></i> Tandai semuanya sebagai</span>

                                <select wire:model="keadaanMassal" class="dsb-isian">
                                    <option value="" @selected($keadaanMassal === '')>— pilih keadaan —</option>
                                    @foreach ($pilihanKeadaanMassal as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected($this->keadaanMassal === $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>

                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #7c3aed"
                                    x-data="{ tanya: @js('Keadaan '.count($dipilih).' pendaftaran yang dicentang akan diubah.') }"
                                    x-on:click="
                                        if (await window.orchaKonfirmasi(tanya, { judul: 'Tandai sekaligus?', tombol: 'Ya, tandai' })) {
                                            $wire.ubahKeadaanMassal();
                                        }
                                    ">
                                    <i class="bi bi-check2-all"></i><span>Terapkan</span>
                                </button>
                            </div>
                        @endif

                        {{-- Panel gabung: admin memilih mana yang BERTAHAN.

                             Tidak ditebak dari yang lebih tua atau yang lebih
                             banyak pesertanya — yang menentukan biasanya
                             kwitansi mana yang sudah terlanjur dikirim ke
                             pemesan, dan itu tidak terbaca dari data. --}}
                        @if ($bukaGabung && count($barisDipilih) === 2)
                            <div class="pd-massal-panel">
                                <div class="orcha-bagian-kepala">
                                    <div class="orcha-bagian-nomor"><i class="bi bi-union"></i></div>
                                    <div>
                                        <div class="orcha-bagian-judul">Pilih pendaftaran yang BERTAHAN</div>
                                        <div class="orcha-bagian-sub">
                                            Kode pemesanannya yang dipakai seterusnya. Pembayaran, riwayat
                                            kesehatan, dan nama peserta dari yang satunya dipindahkan ke sini,
                                            lalu barisnya dihapus. Kode yang hilang dicatat di catatan internal.
                                        </div>
                                    </div>
                                </div>

                                <div class="pd-gabung-pilih">
                                    @foreach ($barisDipilih as $satu)
                                        <button type="button" class="pd-gabung-kartu"
                                            x-data="{ tanya: @js('Pendaftaran yang satunya digabungkan ke '.$satu['kode'].' ('.$satu['nama'].'), lalu barisnya dihapus. Ini tidak bisa dibatalkan.') }"
                                            x-on:click="
                                                if (await window.orchaKonfirmasi(tanya, { judul: 'Gabungkan?', tombol: 'Ya, gabungkan' })) {
                                                    $wire.gabungkan({{ (int) $satu['id'] }});
                                                }
                                            ">
                                            <span class="orcha-kode">{{ $satu['kode'] }}</span>
                                            <strong>{{ $satu['nama'] }}</strong>
                                            <span>pakai kode ini</span>
                                        </button>
                                    @endforeach
                                </div>

                                <button type="button" class="dsb-tautan mt-2" wire:click="tutupFormulirGabung">
                                    <i class="bi bi-x"></i> Batal menggabungkan
                                </button>
                            </div>
                        @endif

                        @if ($bukaPesanMassal)
                            <div class="pd-massal-panel">
                                <label class="dsb-label">Pesan yang sama untuk semuanya</label>
                                <textarea rows="4" wire:model.live.debounce.400ms="pesanMassal"
                                    class="dsb-isian"></textarea>
                                <p class="pd-massal-ket">
                                    Tiap nama di bawah membuka percakapan WhatsApp-nya sendiri dengan pesan
                                    ini sudah terisi. Yang sudah Anda buka berubah warna, jadi tidak ada
                                    yang terlewat atau terkirim dua kali.
                                </p>

                                <div class="pd-massal-nomor">
                                    @foreach ($barisDipilih as $satu)
                                        @php
                                            $nomor = preg_replace('/^0/', '62', preg_replace('/\D/', '', $satu['whatsapp']));
                                        @endphp
                                        @if ($nomor !== '')
                                            <a class="pd-nomor" target="_blank" rel="noopener"
                                                href="https://api.whatsapp.com/send?phone={{ $nomor }}&text={{ rawurlencode($pesanMassal) }}"
                                                x-on:click="$el.classList.add('is-dibuka')">
                                                <i class="bi bi-whatsapp"></i>
                                                <span>{{ $satu['nama'] }}</span>
                                                <small>{{ $satu['kode'] }}</small>
                                            </a>
                                        @else
                                            <span class="pd-nomor is-mati" title="Nomor WhatsApp-nya belum terisi">
                                                <i class="bi bi-telephone-x"></i>
                                                <span>{{ $satu['nama'] }}</span>
                                                <small>tanpa nomor</small>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if (count($daftar) === 0)
                    <div class="dsb-kartu-isi">
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon">
                                <i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-inbox' }}"></i>
                            </span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada pendaftaran di sini' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ganti kata pencarian atau pilih keberangkatan lain.'
                                    : 'Pendaftaran yang masuk lewat website akan muncul di sini sendiri.' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="dsb-tabel-bungkus">
                        <table class="dsb-tabel pd-tabel">
                            <thead>
                                <tr>
                                    {{-- Kolom centang.

                                         Lebarnya dipatok supaya tidak ikut
                                         melebar bersama nama: kolom centang
                                         yang melebar meninggalkan kotaknya
                                         jauh dari barisnya, dan yang diklik
                                         lalu bukan kotaknya. --}}
                                    <th class="pd-th-centang">
                                        <input type="checkbox" class="form-check-input"
                                            aria-label="Centang semua yang tampil"
                                            title="Centang semua yang tampil di halaman ini"
                                            @checked($idTampil !== [] && count(array_diff($idTampil, $dipilih)) === 0)
                                            wire:click="pilihSemuaTampil(@js($idTampil))">
                                    </th>

                                    {{-- Kepala yang bisa diurut.

                                         Memakai .dsb-tabel-urut milik bahasa
                                         rupa dasbor, yang sudah menyediakan
                                         keadaan "aktif" beserta panahnya —
                                         bukan tombol yang digambar ulang di
                                         sini dan lama-lama berbeda rupanya dari
                                         layar lain. --}}
                                    <th>
                                        <button type="button" wire:click="urutkanKolom('nama', 'nama_z')"
                                            class="dsb-tabel-urut {{ in_array($urut, ['nama', 'nama_z'], true) ? 'aktif' : '' }}"
                                            title="Urutkan menurut nama pemesan">
                                            Yang mendaftar
                                            <i class="bi {{ $urut === 'nama_z' ? 'bi-sort-alpha-up' : 'bi-sort-alpha-down' }}"></i>
                                        </button>
                                    </th>
                                    <th>Ikut trip</th>
                                    <th>
                                        <button type="button" wire:click="urutkanKolom('berangkat_dekat', 'berangkat_jauh')"
                                            class="dsb-tabel-urut {{ in_array($urut, ['berangkat_dekat', 'berangkat_jauh'], true) ? 'aktif' : '' }}"
                                            title="Urutkan menurut tanggal keberangkatan">
                                            Berangkat
                                            <i class="bi {{ $urut === 'berangkat_jauh' ? 'bi-sort-down-alt' : 'bi-sort-down' }}"></i>
                                        </button>
                                    </th>
                                    <th>Data kesehatan</th>
                                    <th>Keadaan</th>
                                    <th class="text-end">Buka</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($daftar as $baris)
                                    @php
                                        $berangkat = $baris['tanggal_berangkat']
                                            ? \Carbon\Carbon::parse($baris['tanggal_berangkat'])->startOfDay()
                                            : null;
                                        $selisihHari = $berangkat ? now()->startOfDay()->diffInDays($berangkat, false) : null;
                                        $terisi = (int) ($baris['kesehatan_terisi'] ?? 0);
                                        $totalPeserta = max(1, (int) $baris['jumlah_peserta']);
                                        $lengkap = $baris['kesehatan_lengkap'] ?? false;
                                        $titikJemput = $baris['jemput_per_titik'] ?? [];
                                        $angsuran = $baris['angsuran'] ?? null;
                                        $temponBerikutnya = ! empty($angsuran['berikutnya'])
                                            ? \Carbon\Carbon::parse($angsuran['berikutnya'])->locale('id')->translatedFormat('d M')
                                            : null;
                                        $urlDetail = route('admin.orcha.pendaftaran.detail', $baris['id']);
                                    @endphp
                                    <tr wire:key="pendaftaran-{{ $baris['id'] }}"
                                        @class(['pd-baris-dipilih' => in_array((string) $baris['id'], $dipilih, true)])>
                                        {{-- ===== Centang ===== --}}
                                        <td class="pd-td-centang" data-judul="Pilih">
                                            <input type="checkbox" class="form-check-input"
                                                value="{{ $baris['id'] }}" wire:model.live="dipilih"
                                                aria-label="Centang {{ $baris['nama'] }}">
                                        </td>

                                        {{-- ===== Yang mendaftar ===== --}}
                                        <td data-judul="Yang mendaftar">
                                            <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-utama pd-tautan">
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                                        <span><i class="bi bi-whatsapp"></i>{{ $baris['whatsapp'] }}</span>
                                                    </span>
                                                    <span class="dsb-tabel-samar">
                                                        <i class="bi bi-clock"></i>daftar
                                                        {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}
                                                    </span>
                                                </span>
                                            </a>

                                            <span class="pd-cip-deret">
                                                <span class="dsb-lencana is-abu">
                                                    <i class="bi bi-people-fill"></i>{{ $baris['jumlah_peserta'] }} orang
                                                </span>

                                                {{-- Rombongan tanpa nama peserta tidak masuk daftar
                                                     panggil-nama. Ditandai di sini supaya ketemu
                                                     sebelum hari berangkat, bukan di lapangan.
                                                     Cipnya sekaligus jalan keluarnya. --}}
                                                @if (empty($baris['peserta']))
                                                    <a href="{{ route('admin.orcha.pendaftaran.peserta', $baris['id']) }}"
                                                        wire:navigate class="dsb-lencana is-kuning text-decoration-none"
                                                        title="Nama pesertanya belum ditulis — klik untuk mengisinya">
                                                        <i class="bi bi-person-dash"></i>nama belum ditulis
                                                    </a>
                                                @endif

                                                {{-- Hanya di daftar telepon: yang mengangkat gagang
                                                     perlu tahu mana yang sudah dikirimi pengingat dan
                                                     tetap diam, dan mana yang memang belum pernah
                                                     dihubungi. Dua keadaan itu menuntut kalimat
                                                     pembuka yang berbeda. --}}
                                                @if ($perluDitagih)
                                                    @if (! empty($baris['pengingat_pelunasan_pada']))
                                                        <span class="dsb-lencana is-biru">
                                                            <i class="bi bi-envelope-check"></i>sudah diingatkan
                                                            {{ \Carbon\Carbon::parse($baris['pengingat_pelunasan_pada'])->locale('id')->diffForHumans() }}
                                                        </span>
                                                    @else
                                                        <span class="dsb-lencana is-abu">
                                                            <i class="bi bi-envelope"></i>belum pernah diingatkan
                                                        </span>
                                                    @endif
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Ikut trip ===== --}}
                                        <td data-judul="Ikut trip">
                                            <span class="dsb-tabel-teks">
                                                <span class="dsb-tabel-judul">{{ $baris['paket']['nama'] }}</span>
                                                @if (! empty($titikJemput))
                                                    <span class="dsb-tabel-meta"
                                                        title="@foreach ($titikJemput as $titik => $orang){{ $titik }}: {{ implode(', ', $orang) }}&#10;@endforeach">
                                                        <i class="bi bi-geo-alt-fill"></i>dijemput di {{ count($titikJemput) }} tempat
                                                    </span>
                                                @elseif ($baris['titik_jemput'])
                                                    <span class="dsb-tabel-meta">
                                                        <i class="bi bi-geo-alt-fill"></i>{{ $baris['titik_jemput'] }}
                                                    </span>
                                                @else
                                                    <span class="dsb-tabel-samar">tempat jemput belum diisi</span>
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Berangkat ===== --}}
                                        <td data-judul="Berangkat">
                                            @if ($berangkat)
                                                <span class="dsb-tabel-teks">
                                                    {{-- RENTANGNYA, bukan satu tanggal.

                                                         "11 Des 2026" tidak memberi tahu tripnya sampai
                                                         kapan, dan pada paket yang menjual beberapa
                                                         rombongan berdekatan dua baris bertanggal mirip
                                                         terbaca seperti salah satunya salah ketik.
                                                         Kalimat yang dipakai sama persis dengan yang
                                                         dibaca pelanggan di website dan yang tertulis di
                                                         borang paket — satu rombongan, satu sebutan.

                                                         Pendaftaran lama yang belum punya keberangkatan
                                                         tetap memakai tanggalnya sendiri. --}}
                                                    <span class="dsb-tabel-angka">
                                                        {{ $baris['keberangkatan']['rentang_tanggal']
                                                            ?? $berangkat->locale('id')->translatedFormat('d M Y') }}
                                                    </span>

                                                    {{-- Pendaftaran yang belum punya rombongan DITANDAI.

                                                         Ia peninggalan dari masa sebelum satu paket boleh
                                                         dijual untuk beberapa tanggal, dan batas
                                                         pelunasannya dihitung lewat jalur mundur yang
                                                         berbeda. Tanpa penanda, barisnya terlihat sama
                                                         saja dengan yang normal — dan admin tidak punya
                                                         cara tahu kenapa tenggatnya tidak seperti
                                                         tetangganya. --}}
                                                    @if (empty($baris['keberangkatan_id']))
                                                        <span class="dsb-lencana is-kuning"
                                                            title="Belum terhubung ke salah satu tanggal keberangkatan. Buka detailnya lalu pakai Pindah Rombongan.">
                                                            <i class="bi bi-link-45deg"></i>belum punya rombongan
                                                        </span>
                                                    @endif
                                                    {{-- Hitungan mundur, bukan tanggal saja: yang
                                                         menentukan tindakan hari ini adalah jarak ke
                                                         keberangkatan, dan menghitungnya sendiri dari
                                                         kalender itu pekerjaan yang berulang belasan
                                                         kali sehari. --}}
                                                    @if ($selisihHari > 0)
                                                        <span class="dsb-lencana {{ $selisihHari <= 5 ? 'is-merah' : 'is-abu' }}">
                                                            {{ $selisihHari <= 5 ? 'tinggal' : '' }} {{ $selisihHari }} hari lagi
                                                        </span>
                                                    @elseif ($selisihHari === 0)
                                                        <span class="dsb-lencana is-merah"><i class="bi bi-airplane"></i>berangkat hari ini</span>
                                                    @else
                                                        <span class="dsb-lencana is-abu">sudah berangkat</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="dsb-tabel-samar">tanggalnya belum ditentukan</span>
                                            @endif
                                        </td>

                                        {{-- ===== Data kesehatan ===== --}}
                                        <td data-judul="Data kesehatan">
                                            {{-- Harus terlihat jauh sebelum hari berangkat, bukan
                                                 saat rombongan sudah berkumpul. --}}
                                            <span class="dsb-tabel-teks">
                                                <span class="dsb-lencana {{ $lengkap ? 'is-hijau' : ($terisi === 0 ? 'is-merah' : 'is-kuning') }}"
                                                    @if (! empty($baris['peserta_belum_isi']))
                                                        title="Belum mengisi: {{ implode(', ', $baris['peserta_belum_isi']) }}"
                                                    @endif>
                                                    <i class="bi {{ $lengkap ? 'bi-heart-pulse-fill' : 'bi-heart-pulse' }}"></i>
                                                    {{ $terisi }} dari {{ $baris['jumlah_peserta'] }} orang
                                                </span>
                                                <span class="pd-batang {{ $lengkap ? 'is-lengkap' : '' }}">
                                                    <span style="width: {{ min(100, $terisi / $totalPeserta * 100) }}%"></span>
                                                </span>
                                            </span>
                                        </td>

                                        {{-- ===== Keadaan ===== --}}
                                        <td data-judul="Keadaan">
                                            <span class="dsb-tabel-teks">
                                                {{-- Bila daftar statusnya TIDAK sampai, yang digambar
                                                     tulisannya — bukan kotak pilih kosong. Daftar itu
                                                     datang dari Orcha, dan saat Orcha sedang tidak
                                                     bisa dihubungi kotak pilih tanpa satu pun pilihan
                                                     tergambar benar-benar kosong: statusnya lenyap
                                                     tanpa satu pun kalimat yang menjelaskan sebabnya.
                                                     Statusnya sendiri selalu ada — ia datang bersama
                                                     barisnya. --}}
                                                @if ($pilihanStatus === [])
                                                    <span class="dsb-lencana is-abu"
                                                        title="Daftar pilihan belum bisa diambil dari Orcha, jadi keadaannya belum bisa diubah dari sini.">
                                                        <i class="bi bi-wifi-off"></i>{{ $baris['status_label'] ?? $baris['status'] }}
                                                    </span>
                                                @else
                                                    {{-- Konfirmasi dulu.

                                                         Pemilih ini duduk di jalur jempol saat
                                                         menggulir, dan "Batal" di dalamnya melepas
                                                         kursi orang lalu mengabari daftar tunggu —
                                                         tindakan yang menyentuh orang ketiga dan
                                                         tidak bisa dibatalkan dengan memilih ulang.

                                                         Nilainya dikembalikan saat dibatalkan;
                                                         pemilih yang tetap menampilkan pilihan baru
                                                         padahal tidak tersimpan adalah kebohongan
                                                         yang bertahan sampai halaman dimuat ulang. --}}
                                                    <select class="dsb-isian pd-pilih-status status-{{ $baris['status'] }}"
                                                        x-data="{ semula: @js($baris['status']) }"
                                                        x-on:change="
                                                            const pilih = $event.target;
                                                            const label = pilih.options[pilih.selectedIndex].text.trim();
                                                            if (await window.orchaKonfirmasi(@js($baris['nama']) + ' diubah keadaannya menjadi ' + label + '.', { judul: 'Ubah keadaan?', tombol: 'Ya, ubah' })) {
                                                                semula = pilih.value;
                                                                $wire.ubahStatus(@js((int) $baris['id']), pilih.value);
                                                            } else {
                                                                pilih.value = semula;
                                                            }
                                                        "
                                                        title="Ubah keadaan pendaftaran ini">
                                                        @foreach ($pilihanStatus as $kunci => $label)
                                                            <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif

                                                {{-- Pesanan yang belum lunas menjelang berangkat berarti
                                                     dua hal yang sangat berbeda: ia menunggak, atau ia
                                                     sedang menjalani jadwal yang kita sendiri berikan.
                                                     Menelepon orang yang membayar tepat waktu dengan
                                                     nada penagihan merusak kesepakatan yang baru
                                                     dibuat. Tiga keadaan, tiga kalimat. --}}
                                                @if ($angsuran)
                                                    @if ($angsuran['telat'] > 0)
                                                        <span class="dsb-lencana is-merah"
                                                            title="Dicicil {{ $angsuran['jumlah_termin'] }} kali. {{ $angsuran['telat'] }} cicilan sudah lewat tanggalnya dan belum dibayar.">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            telat bayar cicilan {{ $angsuran['telat'] }}×
                                                        </span>
                                                    @elseif ($angsuran['selesai'])
                                                        <span class="dsb-lencana is-hijau" title="Semua cicilan sudah dibayar.">
                                                            <i class="bi bi-calendar2-check-fill"></i>cicilan lunas
                                                        </span>
                                                    @else
                                                        <span class="dsb-lencana is-biru"
                                                            title="Dicicil {{ $angsuran['jumlah_termin'] }} kali. Cicilan berikutnya jatuh tempo {{ $temponBerikutnya }}.">
                                                            <i class="bi bi-calendar2-range-fill"></i>
                                                            cicilan {{ $angsuran['lunas'] }} dari {{ $angsuran['jumlah_termin'] }}
                                                            @if ($temponBerikutnya) · berikutnya {{ $temponBerikutnya }} @endif
                                                        </span>
                                                    @endif
                                                @endif
                                            </span>
                                        </td>

                                        {{-- ===== Buka ===== --}}
                                        <td data-judul="Buka" class="text-end">
                                            <span class="dsb-tabel-aksi">
                                                <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-btn"
                                                    title="Lihat semuanya: pembayaran, peserta, pembatalan">
                                                    <i class="bi bi-person-lines-fill"></i>
                                                </a>

                                                {{-- Bagi kursi & kamar, LANGSUNG dari baris ini.

                                                     Pembagiannya milik rombongan, bukan milik satu
                                                     pemesanan — tetapi admin selalu tiba di sini
                                                     lewat orangnya, bukan lewat tanggalnya. Sebelum
                                                     tombol ini ada, ia harus membuka detail satu
                                                     pendaftaran dulu hanya untuk sampai ke papan
                                                     pembagian rombongannya.

                                                     Baris yang belum punya rombongan tidak punya
                                                     satuan untuk dibagi, jadi tombolnya memang tidak
                                                     ada — penandanya sudah ada di kolom Berangkat. --}}
                                                @if (! empty($baris['keberangkatan_id']))
                                                    <a wire:navigate
                                                        href="{{ route('admin.orcha.pembagian', $baris['keberangkatan_id']) }}"
                                                        class="dsb-tabel-btn" style="--c: #0284c7"
                                                        title="Bagi tempat duduk & kamar rombongan {{ $baris['keberangkatan']['rentang_tanggal'] ?? '' }}">
                                                        <i class="bi bi-grid-3x3-gap"></i>
                                                    </a>
                                                @endif

                                                @if ($bolehKesehatan && ($baris['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                                    <a wire:navigate href="{{ route('admin.orcha.pendaftaran.kesehatan', $baris['id']) }}"
                                                        class="dsb-tabel-btn pd-btn-sehat"
                                                        title="Lihat data kesehatan {{ $baris['jumlah_riwayat_kesehatan'] }} peserta">
                                                        <i class="bi bi-heart-pulse"></i>
                                                    </a>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="dsb-kartu-isi pd-kaki">
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- <style> telanjang: layout templateindex tidak punya satu pun stack,
         jadi gaya yang didorong ke sana tidak pernah ikut tergambar. --}}
    <style>
        /* Saringan: pencarian melebar, keberangkatan tetap, tombol di ujung.
           Grid, bukan flex bebas — dengan flex tombolnya sempat terlempar ke
           baris kedua persis saat saringan dinyalakan, yaitu saat tangan
           sedang di sana. */
        /* auto-fit, bukan tiga kolom tetap.

           Jumlah saringannya sekarang BERUBAH-UBAH: Rombongan hanya muncul
           setelah paketnya dipilih, dan tombol bersihkan hanya saat ada
           saringan hidup. Kolom tetap membuat salah satu keadaan itu selalu
           salah — entah ada medan yang terlempar ke baris sendiri selebar
           layar, entah empat medan berdesakan jadi masing-masing 120px. */
        .pd-saring {
            display: grid; gap: 12px; align-items: end;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        }
        /* Kotak cari dua kali lebih lebar: yang diketik di sana nama orang,
           bukan pilihan dari daftar. */
        @media (min-width: 992px) {
            .pd-saring > .dsb-medan:first-child { grid-column: span 2; }
        }
        @media (max-width: 575.98px) {
            .pd-saring { grid-template-columns: 1fr; }
        }
        .pd-medan-aksi { display: flex; align-items: flex-end; }
        .pd-medan-aksi:empty { display: none; }

        .pd-catatan {
            display: flex; align-items: flex-start; gap: 8px; margin-top: 12px;
            padding: 9px 11px; border-radius: 10px; font-size: .8rem; line-height: 1.5;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
        }

        /* ===== Kolom centang =====
           Lebarnya dipatok. Kolom centang yang ikut melebar bersama nama
           meninggalkan kotaknya jauh dari barisnya, dan yang diklik lalu bukan
           kotaknya melainkan ruang kosong di sebelahnya. */
        .pd-th-centang, .pd-td-centang { width: 34px; padding-right: 0; }
        .pd-td-centang .form-check-input { margin: 0; cursor: pointer; }
        .pd-baris-dipilih { background: #faf5ff; }

        @media (max-width: 991.98px) {
            /* Di kartu ponsel, kolom centang jadi baris pertama yang berlabel
               — kotak centang telanjang tanpa label tidak memberi tahu apa
               yang sedang dicentang. */
            .pd-td-centang { width: auto; }
        }

        /* ===== Tindakan untuk yang dicentang ===== */
        .pd-massal {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 12px 14px; border-radius: 13px;
            background: #faf5ff; border: 1px solid #e9d5ff;
        }
        .pd-massal-teks { flex: 1 1 240px; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .pd-massal-teks > strong { font-size: .9rem; color: #5b21b6; }
        .pd-massal-teks > span { font-size: .78rem; line-height: 1.5; color: #6d28d9; }
        .pd-massal-aksi { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

        .pd-massal-keadaan {
            display: flex; align-items: center; gap: 9px; flex-wrap: wrap;
            margin-top: 10px; padding: 10px 12px;
            border-radius: 12px; background: #fff; border: 1px solid var(--dsb-tepi);
            font-size: .82rem; color: var(--dsb-redup);
        }
        .pd-massal-keadaan .dsb-isian { width: auto; min-width: 170px; }

        /* Dua kartu berdampingan, dan yang ditekan menentukan kode mana yang
           bertahan. Bukan pemilih: dua pilihan yang akibatnya berbeda jauh
           pantas terlihat berdampingan dengan ukuran yang sama, bukan
           bersembunyi di dalam daftar yang harus dibuka dulu. */
        .pd-gabung-pilih {
            display: grid; gap: 10px; margin-top: 12px;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        }
        .pd-gabung-kartu {
            display: flex; flex-direction: column; gap: 4px; align-items: flex-start;
            padding: 12px 14px; border-radius: 13px; cursor: pointer; text-align: left;
            background: #fff; border: 1px solid var(--dsb-tepi);
        }
        .pd-gabung-kartu strong { font-size: .9rem; color: var(--dsb-tinta); }
        .pd-gabung-kartu > span:last-child { font-size: .76rem; color: #7c3aed; font-weight: 600; }
        @media (hover: hover) and (pointer: fine) {
            .pd-gabung-kartu:hover { border-color: #7c3aed; background: #faf5ff; }
        }

        .pd-massal-panel {
            margin-top: 11px; padding: 14px;
            border-radius: 13px; background: #fff; border: 1px solid var(--dsb-tepi);
        }
        .pd-massal-ket { margin: 8px 0 0; font-size: .78rem; line-height: 1.5; color: var(--dsb-redup); }
        .pd-massal-nomor { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }

        .pd-nomor {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 11px; border-radius: 999px; text-decoration: none;
            background: #fff; border: 1px solid #bbf7d0; color: #15803d;
            font-size: .8rem; font-weight: 600;
        }
        .pd-nomor small { font-weight: 500; opacity: .7; font-size: .72rem; }
        /* Yang sudah dibuka berubah warna, supaya tidak ada yang terlewat atau
           terkirim dua kali. Ditandai di peramban, bukan disimpan: daftarnya
           hidup selama satu sesi mengabari, dan menyimpannya berarti penanda
           yang masih menyala besok untuk pesan yang berbeda. */
        .pd-nomor.is-dibuka { background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; }
        .pd-nomor.is-mati { background: #fff1f2; border-color: #fecdd3; color: #9f1239; cursor: not-allowed; }
        @media (hover: hover) and (pointer: fine) {
            .pd-nomor:hover { background: #15803d; border-color: transparent; color: #fff; }
            .pd-nomor.is-dibuka:hover { background: #e2e8f0; color: #475569; }
            .pd-nomor.is-mati:hover { background: #fff1f2; border-color: #fecdd3; color: #9f1239; }
        }

        /* Baris tabel: nama di atas, keterangan menumpuk di bawahnya. */
        .pd-tautan { text-decoration: none; color: inherit; display: block; }
        .pd-tautan:hover .dsb-tabel-judul { color: #4f46e5; }
        .pd-cip-deret { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }

        /* Batang kemajuan data kesehatan. Angka saja ("7 dari 11") menuntut
           pembagian di kepala untuk tahu seberapa jauh; batangnya menjawab
           itu sebelum angkanya dibaca. */
        .pd-batang {
            display: block; height: 4px; border-radius: 999px;
            background: #eef2f7; overflow: hidden; margin-top: 5px; max-width: 150px;
        }
        .pd-batang > span { display: block; height: 100%; background: #f59e0b; border-radius: 999px; }
        .pd-batang.is-lengkap > span { background: #16a34a; }

        /* Kotak pilih keadaan: selebar isinya, bukan selebar sel — kotak
           sepanjang kolom membuat lencana di bawahnya terlihat menggantung. */
        .pd-pilih-status { max-width: 170px; font-size: .8rem; padding-top: 5px; padding-bottom: 5px; }
        .pd-btn-sehat { color: #be123c; }

        /* Kaki kartu: penomoran halaman menempel ke tepi bawah tanpa ini,
           dan kartunya terbaca seperti terpotong. */
        .pd-kaki { padding-top: 4px; }
    </style>
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
