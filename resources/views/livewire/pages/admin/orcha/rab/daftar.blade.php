@section('title')
RAB & Itinerary || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         dasbor Orcha dan jejak audit. Kelas orcha-* hanya untuk hal yang
         memang khas Orcha (kode pesanan, paginasi API). --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlahData = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = $this->adaSaringan();

        /*
         | Warna per status: draf abu, dikirim biru, disetujui hijau, batal
         | merah. Bukan satu peta warna untuk seluruh Orcha — "baru" di
         | pendaftaran dan "draf" di RAB bukan keadaan yang sama, dan
         | menyamakan warnanya menyamakan artinya.
         */
        $rupaStatus = [
            'draf' => ['#64748b', 'bi-pencil'],
            'dikirim' => ['#2b7fb8', 'bi-send'],
            'disetujui' => ['#047857', 'bi-check-circle'],
            'batal' => ['#be123c', 'bi-x-circle'],
        ];
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                {{-- Bentuknya mengikuti Pesanan Toko: sapaan besar, tanggal
                     hari ini, lalu satu kalimat yang menjelaskan layarnya. --}}
                <h1 class="dsb-salam">RAB &amp; Itinerary</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">
                        Susun jadwal perjalanan, hitung anggarannya, lalu kirim penawarannya.
                        Harganya dirakit dari Master Harga — tidak diketik ulang tiap penawaran.
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.master-harga') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #b45309">
                    <i class="bi bi-tags"></i><span>Master Harga</span>
                </a>
                <a href="{{ route('admin.orcha.dashboard') }}" wire:navigate class="dsb-tombol is-lembut" style="--ikon: #2b7fb8">
                    <i class="bi bi-speedometer2"></i><span>Dashboard Orcha</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== KARTU STATUS ==================

             Bentuknya SAMA dengan tab di Pesanan Toko: ikon berwarna, angka
             besar, label di bawahnya, dikelompokkan dengan judul kecil.
             Definisi rupanya satu, di partials/kartu-tab-gaya.

             Kartunya SARINGAN, bukan tab: menekan yang sedang menyala
             melepaskannya kembali. Angka yang menunjuk pekerjaan tetapi
             tidak membawa ke sana cuma menambah beban. --}}
        @include('livewire.pages.admin.partials.kartu-tab-gaya')

        @if ($ringkas !== [])
            @php
                $angka = fn ($k) => (int) ($ringkas[$k] ?? 0);

                $kartu = [
                    'jumlah' => ['Semua penawaran', 'bi-journals', 'var(--dsb-utama)', $angka('jumlah'), false],
                    'draf' => ['Masih draf', 'bi-pencil-square', 'var(--dsb-netral)', $angka('draf'), true],
                    'dikirim' => ['Sudah dikirim', 'bi-send', 'var(--dsb-telaah)', $angka('dikirim'), true],
                    'disetujui' => ['Disetujui', 'bi-check2-circle', 'var(--dsb-aman)', $angka('disetujui'), true],
                    'kedaluwarsa' => ['Lewat masa berlaku', 'bi-alarm-fill', 'var(--dsb-awas)', $angka('kedaluwarsa'), true],
                ];

                $grupKartu = [
                    ['Sedang berjalan', 'Penawaran yang masih dikerjakan', ['jumlah', 'draf', 'dikirim']],
                    ['Perlu ditindaklanjuti', 'Yang menunggu jawaban atau keputusan', ['disetujui', 'kedaluwarsa']],
                ];
            @endphp

            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="k-12">
                        <div class="pt-grup-deret rb-kartu-status" style="--grup-kolom: minmax(0, 3fr) minmax(0, 2fr)">
                            @foreach ($grupKartu as [$judulGrup, $ketGrup, $kunciGrup])
                                <nav class="pt-grup" aria-label="{{ $judulGrup }}">
                                    <div class="pt-grup-kepala">
                                        <span class="pt-grup-judul">{{ $judulGrup }}</span>
                                        <span class="pt-grup-ket">{{ $ketGrup }}</span>
                                    </div>
                                    <div class="pt-kartu-deret" style="--n: {{ count($kunciGrup) }}">
                                        @foreach ($kunciGrup as $kunci)
                                            @php
                                                [$label, $ikon, $warna, $nilai, $bisaDiklik] = $kartu[$kunci];
                                                $nyala = $bisaDiklik && $this->ubinNyala($kunci);
                                            @endphp
                                            <button type="button" style="--c: {{ $warna }}"
                                                class="pt-kartu-tab {{ $nyala ? 'is-aktif' : '' }} {{ $nilai ? '' : 'is-nol' }} {{ $bisaDiklik ? '' : 'rb-kartu-diam' }}"
                                                aria-pressed="{{ $nyala ? 'true' : 'false' }}"
                                                @if ($bisaDiklik)
                                                    wire:click="pasangUbin('{{ $kunci }}')"
                                                    title="{{ $nyala ? 'Lepas saringannya' : 'Lihat yang '.mb_strtolower($label) }}"
                                                @else
                                                    disabled title="Seluruh penawaran yang tersimpan"
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

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        {{-- Cari dulu, sisanya dilipat — pola yang sama dengan
                             Pesanan Toko. Empat kotak saringan berjajar membuat
                             baris pertamanya penuh benda yang jarang disentuh,
                             dan daftarnya terdorong jauh ke bawah layar. --}}
                        <div x-data="{ buka: {{ $this->jumlahSaringLanjut() ? 'true' : 'false' }} }">
                        <div class="rb-saring rb-saring-utama">
                            <div class="rb-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari kode, pelanggan, judul, atau WhatsApp…',
                                    'rupa' => 'dsb',
                                ])
                            </div>

                            <a href="{{ route('admin.orcha.rab.tambah') }}" class="dsb-tombol is-utama" wire:navigate>
                                <i class="bi bi-plus-lg"></i><span>RAB Baru</span>
                            </a>

                            <button type="button" class="dsb-tombol is-lembut" x-on:click="buka = !buka" :aria-expanded="buka">
                                <i class="bi bi-sliders"></i><span>Saringan lainnya</span>
                                @if ($this->jumlahSaringLanjut())
                                    <span class="pt-tab-jumlah is-isi">{{ $this->jumlahSaringLanjut() }}</span>
                                @endif
                                <i class="bi bi-chevron-down rb-lanjutan-panah" :class="buka && 'is-buka'"></i>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-awas"
                                    title="Kembali menampilkan seluruh RAB">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>

                        {{-- Terbuka sendiri bila ada saringan yang aktif: saringan
                             yang bekerja tetapi tersembunyi membuat daftar yang
                             "kurang" terlihat seperti data hilang. --}}
                        <div class="rb-saring rb-saring-lanjut" x-show="buka" x-cloak>
                            <select class="dsb-isian rb-pilih" wire:model.live="filterStatus" aria-label="Saring menurut status">
                                <option value="" @selected((string) $this->filterStatus === '')>Semua status</option>
                                @foreach ($status as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected((string) $this->filterStatus === (string) $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian rb-pilih" wire:model.live="filterProvinsi" aria-label="Saring menurut provinsi">
                                <option value="" @selected((string) $this->filterProvinsi === '')>Semua provinsi</option>
                                @foreach ($provinsi as $p)
                                    <option value="{{ $p }}" @selected((string) $this->filterProvinsi === (string) $p)>{{ $p }}</option>
                                @endforeach
                            </select>

                            <select class="dsb-isian rb-pilih" wire:model.live="filterJenis" aria-label="Saring menurut jenis trip">
                                <option value="" @selected((string) $this->filterJenis === '')>Private &amp; open</option>
                                <option value="private" @selected((string) $this->filterJenis === 'private')>Private trip</option>
                                <option value="open" @selected((string) $this->filterJenis === 'open')>Open trip</option>
                            </select>

                            <select class="dsb-isian rb-pilih" wire:model.live="urut" aria-label="Urutkan daftar">
                                <option value="" @selected((string) $this->urut === '')>Urut: terbaru</option>
                                <option value="berangkat" @selected((string) $this->urut === 'berangkat')>Berangkat terdekat</option>
                                <option value="pelanggan" @selected((string) $this->urut === 'pelanggan')>Nama pelanggan</option>
                                <option value="lama" @selected((string) $this->urut === 'lama')>Paling lama</option>
                            </select>

                        </div>{{-- /.rb-saring-lanjut --}}
                        </div>{{-- /x-data --}}
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== SALIN PENAWARAN ==================

             Penawaran serupa untuk pelanggan lain adalah pekerjaan yang
             paling sering diulang di layar ini. Yang dikosongkan ikut
             nilai aslinya — menyalin biasanya berarti "sama, kecuali
             satu-dua hal". --}}
        @if ($salinDari !== null)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-awas)"><i class="bi bi-files"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Salin penawaran</h3>
                                    <span class="dsb-kartu-sub">
                                        Itinerary dan seluruh baris biayanya ikut tersalin. Salinannya berstatus
                                        <strong>draf</strong> dengan kode dan masa berlaku baru. Kotak yang dibiarkan
                                        kosong memakai isi penawaran aslinya.
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="tutupSalin" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="rb-salin">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-salin-nama">
                                        Nama pelanggan <span class="dsb-label-ket">boleh dikosongkan</span>
                                    </label>
                                    <input id="rb-salin-nama" type="text" maxlength="120"
                                        wire:model="salinIsian.nama_pelanggan"
                                        class="dsb-isian @error('salinIsian.nama_pelanggan') is-galat @enderror"
                                        placeholder="Ikut penawaran aslinya">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-salin-wa">WhatsApp</label>
                                    <input id="rb-salin-wa" type="text" maxlength="32" wire:model="salinIsian.whatsapp"
                                        class="dsb-isian" placeholder="Ikut penawaran aslinya">
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-salin-tgl">Tanggal berangkat</label>
                                    <input id="rb-salin-tgl" type="date" wire:model="salinIsian.tanggal_mulai"
                                        class="dsb-isian">
                                </div>

                                <span class="rb-salin-tombol">
                                    <button type="button" wire:click="salin"
                                        wire:loading.attr="disabled" wire:target="salin"
                                        class="dsb-tombol is-utama">
                                        <i class="bi bi-files" wire:loading.remove wire:target="salin"></i>
                                        <span class="dsb-putar" wire:loading wire:target="salin"></span>
                                        <span>Salin</span>
                                    </button>
                                </span>
                            </div>

                            @error('salinIsian.nama_pelanggan')<span class="dsb-galat">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== VERSI PENAWARAN ================== --}}
        @if ($versiUntuk !== null)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-telaah)"><i class="bi bi-clock-history"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Versi yang pernah dikirim — {{ $versiMeta['kode'] ?? '' }}</h3>
                                    <span class="dsb-kartu-sub">
                                        Direkam tiap kali status berpindah ke <strong>dikirim</strong>.
                                        Sekarang {{ $versiMeta['harga_kini_teks'] ?? '-' }}.
                                    </span>
                                </div>
                            </div>
                            <button type="button" wire:click="tutupVersi" class="dsb-tombol is-lembut">
                                <i class="bi bi-x-lg"></i><span>Tutup</span>
                            </button>
                        </div>

                        @if ($versi === [])
                            <div class="dsb-kartu-isi">
                                <p class="rb-nihil">
                                    <i class="bi bi-clock-history"></i>
                                    Penawaran ini belum pernah dikirim, jadi belum ada versi yang direkam.
                                </p>
                            </div>
                        @else
                            <div class="dsb-tabel-bungkus">
                                <table class="dsb-tabel">
                                    <thead>
                                        <tr>
                                            <th>Versi</th>
                                            <th class="text-end">Harga saat itu</th>
                                            <th class="text-end">Untung</th>
                                            <th>Oleh</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($versi as $v)
                                            <tr wire:key="versi-{{ $v['id'] }}">
                                                <td data-judul="Versi">
                                                    <span class="rb-utama">
                                                        <span class="dsb-tabel-judul">Versi {{ $v['urutan'] }}</span>
                                                        <span class="dsb-tabel-meta">{{ $v['pada'] }} · {{ $v['pada_teks'] }}</span>
                                                    </span>
                                                </td>
                                                <td data-judul="Harga saat itu" class="text-end">
                                                    <span class="rb-utama is-kanan">
                                                        <span class="dsb-tabel-judul dsb-tabel-angka">{{ $v['harga_total_teks'] }}</span>
                                                        <span class="dsb-tabel-meta dsb-tabel-angka">{{ $v['harga_per_orang_teks'] }}/org · {{ $v['jumlah_peserta'] }} peserta</span>
                                                        @if ($v['beda_kini'])
                                                            {{-- Yang dicari saat pelanggan menyebut angka
                                                                 yang tidak lagi ada di layar. --}}
                                                            <span class="rb-beda">{{ $v['beda_kini'] }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td data-judul="Untung" class="text-end">
                                                    <span class="rb-utama is-kanan">
                                                        <span class="dsb-tabel-angka rb-untung is-{{ $this->rupaUntung($v['persen_untung'] ?? null) }}">{{ $v['untung_teks'] }}</span>
                                                        @isset($v['persen_untung'])
                                                            <span class="dsb-tabel-meta dsb-tabel-angka">{{ str_replace('.', ',', (string) $v['persen_untung']) }}%</span>
                                                        @endisset
                                                    </span>
                                                </td>
                                                <td data-judul="Oleh">{{ $v['oleh'] ?: 'tidak tercatat' }}</td>
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
                    wire:target="cari,filterStatus,bersihkanSaringan,bersihkanCari,keHalaman">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-calculator"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Rencana anggaran</h3>
                                <span class="dsb-kartu-sub">Terbaru di atas · satu baris = satu penawaran</span>
                            </div>
                        </div>
                        <span class="dsb-chip"><i class="bi bi-list-check"></i>{{ number_format($jumlahData, 0, ',', '.') }} RAB</span>
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kartu-isi">
                            <div class="dsb-kosong">
                                <span class="dsb-kosong-ikon"><i class="bi bi-calculator"></i></span>
                                <p class="dsb-kosong-judul">{{ $adaSaringan ? 'Tidak ada yang cocok' : 'Belum ada RAB' }}</p>
                                <p class="dsb-kosong-ket">
                                    {{ $adaSaringan
                                        ? 'Coba ubah kata kuncinya atau pilih status lain.'
                                        : 'Tekan "RAB Baru" saat ada pelanggan meminta private trip.' }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel rb-tabel">
                                <thead>
                                    <tr>
                                        {{-- Ikon status memimpin sel pertama (pola Pesanan
                                             Toko), jadi kolom Status tidak lagi menanggung
                                             sendirian. Yang paling bisa dilepas di layar
                                             sempit: Berangkat ke dan Untung — keduanya
                                             punya salinan di baris lencana. --}}
                                        <th>Perjalanan</th>
                                        <th class="k-lebar">Berangkat ke</th>
                                        <th class="text-end">Harga</th>
                                        <th class="k-sedang text-end">Untung</th>
                                        <th>Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            [$warna, $ikon] = $rupaStatus[$baris['status']] ?? ['#64748b', 'bi-circle'];
                                            $bisaDihapus = empty($baris['kode_pendaftaran']);
                                            $tanggal = $baris['tanggal_mulai']
                                                ? \Illuminate\Support\Carbon::parse($baris['tanggal_mulai'])->locale('id')->translatedFormat('j M Y')
                                                : 'Tanggal belum pasti';
                                        @endphp
                                        <tr wire:key="rab-{{ $baris['id'] }}">
                                            <td data-judul="Perjalanan">
                                                <span class="dsb-tabel-utama">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warna }}">
                                                        <i class="bi {{ $ikon }}"></i>
                                                    </span>
                                                    <span class="dsb-tabel-teks">
                                                        <a href="{{ route('admin.orcha.rab.susun', $baris['id']) }}" wire:navigate
                                                            class="dsb-tabel-judul rb-tautan">{{ $baris['judul'] }}</a>

                                                        <span class="dsb-tabel-meta">
                                                            <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                                            <span>{{ $baris['nama_pelanggan'] }}</span>
                                                            <span>{{ $baris['jumlah_peserta'] }} orang</span>

                                                            {{-- Salinan kolom yang disembunyikan di layar
                                                                 sempit — tanpa ini tujuan & untungnya
                                                                 hilang sama sekali, bukan cuma pindah. --}}
                                                            <span class="dsb-tabel-samar">
                                                                <i class="bi bi-geo-alt"></i>{{ $baris['daerah'] ?: $baris['provinsi'] }}
                                                            </span>
                                                            <span class="dsb-tabel-samar">
                                                                <i class="bi bi-graph-up-arrow"></i>{{ $baris['untung_teks'] }}
                                                            </span>
                                                        </span>
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="k-lebar" data-judul="Berangkat ke">
                                                <span class="rb-utama">
                                                    <span>{{ $baris['daerah'] ?: $baris['provinsi'] }}</span>
                                                    <span class="dsb-tabel-meta">{{ $tanggal }} · {{ $baris['jumlah_hari'] }}H{{ $baris['jumlah_malam'] }}M</span>
                                                </span>
                                            </td>
                                            <td data-judul="Harga" class="text-end">
                                                <span class="rb-utama is-kanan">
                                                    <span class="dsb-tabel-judul dsb-tabel-angka">{{ $baris['harga_total_teks'] }}</span>
                                                    <span class="dsb-tabel-meta dsb-tabel-angka">
                                                        {{ $baris['harga_per_orang_teks'] }}/org
                                                        {{-- Harga hasil kesepakatan tidak bergerak saat margin
                                                             diubah. Tanpa tanda ini, baris yang "tidak ikut naik"
                                                             saat harga massal dinaikkan terbaca seperti kelewat. --}}
                                                        @if (! empty($baris['harga_nego']))
                                                            <span class="rb-nego-cap" title="Harga disepakati, bukan dihitung dari margin">nego</span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Untung" class="k-sedang text-end">
                                                <span class="rb-utama is-kanan">
                                                    {{-- Warnanya ikut tipisnya margin. Selalu hijau
                                                         membuat penawaran yang merugi pun tergambar
                                                         meyakinkan — padahal ini satu-satunya angka
                                                         yang bisa mencegahnya terkirim. --}}
                                                    <span class="dsb-tabel-angka rb-untung is-{{ $this->rupaUntung($baris['persen_untung'] ?? null) }}">
                                                        {{ $baris['untung_teks'] }}
                                                    </span>
                                                    @isset($baris['persen_untung'])
                                                        <span class="dsb-tabel-meta dsb-tabel-angka">{{ str_replace('.', ',', $baris['persen_untung']) }}%</span>
                                                    @endisset
                                                </span>
                                            </td>
                                            <td data-judul="Status">
                                                <span class="rb-status-rak">
                                                    <span class="dsb-lencana" style="--c: {{ $warna }}">
                                                        <i class="bi {{ $ikon }}"></i>{{ $baris['status_label'] }}
                                                    </span>
                                                    @if ($baris['kedaluwarsa'])
                                                        <span class="dsb-lencana" style="--c: #b45309">
                                                            <i class="bi bi-hourglass-bottom"></i>Lewat masa berlaku
                                                        </span>
                                                    @endif
                                                    @if ($baris['kode_pendaftaran'])
                                                        <span class="dsb-tabel-meta">
                                                            <i class="bi bi-link-45deg"></i> {{ $baris['kode_pendaftaran'] }}
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>
                                            <td data-judul="Aksi" class="text-end">
                                                <span class="dsb-tabel-aksi">
                                                    <a href="{{ route('admin.orcha.rab.susun', $baris['id']) }}" wire:navigate
                                                        class="dsb-tabel-btn" style="--c: #4f46e5" title="Buka penyusun">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <a href="{{ route('admin.orcha.rab.ubah', ['rab' => $baris['id'], 'dari' => 'daftar']) }}" wire:navigate
                                                        class="dsb-tabel-btn" style="--c: var(--dsb-telaah)" title="Ubah data perjalanan">
                                                        <i class="bi bi-sliders"></i>
                                                    </a>
                                                    <a href="{{ route('admin.orcha.rab.pdf', ['rab' => $baris['id'], 'jenis' => 'penawaran']) }}"
                                                        class="dsb-tabel-btn" style="--c: #0369a1" title="Unduh PDF penawaran">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </a>
                                                    {{-- Nomornya sudah tersimpan dan PDF-nya sudah ada,
                                                         tetapi mengirimnya masih manual di SETIAP
                                                         penawaran: unduh, buka WA, cari nomor, ketik. --}}
                                                    @php($wa = $this->tautanWa($baris))
                                                    @if ($wa)
                                                        <a href="{{ $wa }}" target="_blank" rel="noopener"
                                                            class="dsb-tabel-btn" style="--c: var(--dsb-aman)"
                                                            title="Kirim penawarannya lewat WhatsApp">
                                                            <i class="bi bi-whatsapp"></i>
                                                        </a>
                                                    @endif
                                                    {{-- Hanya bila pernah dikirim: tombol yang selalu
                                                         membuka panel kosong mengajari orang untuk
                                                         tidak menekannya lagi. --}}
                                                    @if (($baris['status'] ?? '') !== 'draf')
                                                        <button type="button" wire:click="bukaVersi({{ $baris['id'] }})"
                                                            class="dsb-tabel-btn" style="--c: var(--dsb-telaah)"
                                                            title="Versi penawaran yang pernah dikirim">
                                                            <i class="bi bi-clock-history"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" wire:click="bukaSalin({{ $baris['id'] }}, {{ json_encode($baris) }})"
                                                        class="dsb-tabel-btn" style="--c: var(--dsb-awas)"
                                                        title="Buat penawaran baru dari salinan ini">
                                                        <i class="bi bi-files"></i>
                                                    </button>
                                                    @if ($bisaDihapus)
                                                        <button type="button" class="dsb-tabel-btn pcek-konfirmasi" style="--c: #be123c"
                                                            data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                            data-title="Hapus RAB {{ $baris['kode'] }}?"
                                                            data-text="Itinerary dan seluruh rincian biayanya ikut terhapus."
                                                            data-confirm="Ya, hapus" data-icon="warning" title="Hapus RAB ini">
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

                    <div class="rb-halaman">
                        <label class="rb-baris-pilih">
                            <span>Tampilkan</span>
                            <select class="dsb-isian" wire:model.live="barisHalaman" aria-label="Jumlah baris per halaman">
                                @foreach ([10, 25, 50, 100] as $n)
                                    <option value="{{ $n }}" @selected((int) $this->barisHalaman === $n)>{{ $n }} baris</option>
                                @endforeach
                            </select>
                        </label>
                        @include('livewire.pages.admin.orcha.partials.paginasi')
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Saringan: satu baris di layar lebar, melipat sendiri saat sempit. */
        .rb-saring { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
        .rb-saring > .rb-medan-cari { flex: 1 1 260px; min-width: 0; }
        /* Baris utama: lebar dasarnya dikecilkan supaya cari + tiga tombol
           muat sebaris, bukan menyisakan separuh baris menganggur di kanan.
           Angkanya sama dengan Master Harga, yang kartunya menyempit sampai
           756px pada layar 1200px. */
        .rb-saring-utama > .rb-medan-cari { flex: 1 1 200px; }
        .rb-saring > .rb-pilih { flex: 0 1 auto; width: auto; min-width: 160px; max-width: 220px; }
        .rb-saring .dsb-tombol { flex: 0 0 auto; }

        /* Kotak cari bersama memakai .form-control Bootstrap; yang disamakan
           rupanya saja — markup & perilakunya tetap milik partial. */
        .rb-saring .form-control {
            width: 100%; min-height: 42px; padding: 9px 14px 9px 40px;
            border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 12px;
            background: #fff; color: var(--dsb-tinta, #1c1f26); font-size: .88rem; box-shadow: none;
        }
        .rb-saring .form-control:focus { border-color: #c7d2fe; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); outline: 0; }
        .rb-saring .form-control-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; pointer-events: none; z-index: 2;
        }

        /* Isian RAB baru: grid yang membagi habis lebar kartunya, bukan flex
           yang menyisakan ruang kosong di kanan baris terakhir. */
        /* Sel bertumpuk: judul tegas di atas, keterangannya di bawah. */
        .rb-utama { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .rb-utama.is-kanan { align-items: flex-end; }
        .rb-tautan { text-decoration: none; }
        .rb-tautan:hover { color: #4338ca; }
        /* Warna untung mengikuti tipisnya margin — lihat rupaUntung() di
           komponennya. Yang merugi tidak boleh tergambar seperti yang
           sehat. */
        .rb-nego-cap {
            display: inline-block; margin-left: 4px; padding: 0 6px; border-radius: 999px;
            background: #eef2ff; color: #4338ca; font-size: .68rem; font-weight: 700;
            letter-spacing: .03em; text-transform: uppercase; vertical-align: 1px;
        }
        .rb-untung { font-weight: 700; }
        .rb-untung.is-sehat { color: var(--dsb-aman, #047857); }
        .rb-untung.is-tipis { color: var(--dsb-awas, #b45309); }
        .rb-untung.is-rugi { color: var(--dsb-bahaya, #be123c); }
        .rb-untung.is-netral { color: var(--dsb-redup, #6b7280); }

        /* ===== Ringkasan ===== */
        /* Label BOLEH melipat di layar ini: bawaannya nowrap, dan itu ukuran
           kata bahasa Inggris. "Lewat masa berlaku" tidak muat, dan ia
           terpotong di tengah kata tanpa ada yang terlihat salah. */
        .rb-kartu-status .pt-kartu-label { white-space: normal; }
        .rb-kartu-status .pt-kartu-tab { min-height: 118px; }

        /* Kartu "Semua penawaran" bukan saringan, cuma jumlah seluruhnya —
           dibuat tidak terlihat bisa ditekan supaya tidak ada yang menunggu
           sesuatu terjadi. */
        .rb-kartu-diam { cursor: default; }
        .rb-kartu-diam .pt-kartu-ikon { background: #f1f5f9; color: #94a3b8; }

        /* Lebar kolom DIPATOK, dan table-layout: fixed supaya dipatuhi.

           Dengan layout auto, width cuma saran: peramban membaginya menurut
           isi terpanjang, dan yang menang kolom yang isinya tidak bisa
           melipat — empat tombol aksi dan lencana status. Diukur: kolom
           Perjalanan cuma 20,8% sementara Aksi 21,4%, padahal di sanalah
           nama, kode, pelanggan, dan jumlah pesertanya harus muat.

           Angkanya berjumlah 100% dan diturunkan dari isi sebenarnya: aksi
           butuh 154px pada tabel 1038px. */
        .rb-tabel { table-layout: fixed; }
        .rb-tabel thead th { white-space: normal; }
        .rb-tabel th:nth-child(1), .rb-tabel td:nth-child(1) { width: 24%; }
        .rb-tabel th:nth-child(2), .rb-tabel td:nth-child(2) { width: 13%; }
        .rb-tabel th:nth-child(3), .rb-tabel td:nth-child(3) { width: 13%; }
        .rb-tabel th:nth-child(4), .rb-tabel td:nth-child(4) { width: 11%; }
        .rb-tabel th:nth-child(5), .rb-tabel td:nth-child(5) { width: 16%; }
        .rb-tabel th:nth-child(6), .rb-tabel td:nth-child(6) { width: 23%; }

        /* Tombol aksi dirapatkan DI SEMUA LEBAR, bukan cuma di layar kecil.

           Diukur: jumlah kebutuhan minimum keenam kolom 1075px pada tabel
           1038px — tiga puluh tujuh piksel lebih panjang daripada tempatnya.
           Baris yang statusnya "dikirim" punya LIMA tombol, dan lima tombol
           34px butuh 222px; dirapatkan jadi 30px, cukup 194px. Selisihnya
           persis yang kurang. */
        .rb-tabel .dsb-tabel-aksi { gap: 3px; }
        .rb-tabel .dsb-tabel-aksi .dsb-tabel-btn { width: 28px; height: 28px; }

        /* Yang BOLEH melipat di dalam selnya.

           Dengan lebar yang dipatok, isi ber-nowrap meluber keluar sel dan
           menindih tetangganya tanpa satu pun tanda bahwa ada yang salah:
           dua lencana status berdampingan butuh 159px di kolom 138px, dan
           "Rp 558.000/org · NEGO" butuh 168px di kolom 156px. */
        .rb-tabel .rb-utama .dsb-tabel-meta,
        /* Lencana statusnya juga. "Dikirim ke pelanggan" adalah KALIMAT,
           bukan kata: 159px sendirian, lebih lebar daripada kolom mana pun
           yang masuk akal diberikan kepadanya. Hanya di tabel ini — lencana
           di tempat lain memang pendek dan nowrap-nya menolong. */
        .rb-tabel .rb-status-rak .dsb-lencana { white-space: normal; }

        /* Lebar yang ditinggalkan kolom tersembunyi diberikan ke sel jati
           diri, bukan dibagi rata: di sanalah isinya paling banyak. */
        /* Di laptop kecil tombolnya dikecilkan lagi, dan kolom Aksi justru
           DILEBARKAN. Lima tombol tetap lima tombol betapa pun sempit
           layarnya; yang bisa mengalah kolom lain, bukan tombol yang hilang
           di balik tepi kartu. */
        @media (max-width: 1399.98px) {
            .rb-tabel .dsb-tabel-aksi { gap: 2px; }
            .rb-tabel .dsb-tabel-aksi .dsb-tabel-btn { width: 26px; height: 26px; }
            /* Tidak boleh lebih sempit dari ini: kode "RAB-2609-R24J" selebar
               95px dan tidak bisa dipatahkan — memecahnya di tengah membuat
               nomor rujukan yang justru paling sering disalin jadi tak
               terbaca. Satu persen sisanya diambil dari Status, yang
               lencananya memang boleh melipat. */
            .rb-tabel th:nth-child(1), .rb-tabel td:nth-child(1) { width: 21%; }
            .rb-tabel th:nth-child(5), .rb-tabel td:nth-child(5) { width: 14%; }
            /* Nama daerah sering SATU KATA yang panjang — "Yogyakarta" 81px di
               1200 — dan satu kata tidak bisa dipatahkan tanpa memotongnya di
               tengah. Jadi kolomnya yang diberi ruang, bukan katanya yang
               dipotong; yang dua persen itu diambil dari Perjalanan, satu-
               satunya kolom yang isinya memang boleh melipat. */
            .rb-tabel th:nth-child(2), .rb-tabel td:nth-child(2) { width: 15%; }
            .rb-tabel th:nth-child(6), .rb-tabel td:nth-child(6) { width: 26%; }
        }

        /* Berangkat ke tersembunyi: lebarnya dibagi antara sel jati diri dan
           kolom aksi, bukan diberikan semuanya ke salah satunya. */
        /* Ikon status di sel jati diri dilepas lebih dulu, sebelum satu
           kolom pun dipersempit lagi: ia memakan 46px untuk mengulang apa
           yang kolom Status sudah katakan dengan kata-kata. Tanpa ini kode
           "RAB-2609-R24J" kurang 4px dari muat, dan 4px itu hanya bisa
           diambil dari kolom yang isinya tidak boleh melipat. */
        @media (max-width: 1299.98px) {
            .rb-tabel tbody .dsb-tabel-utama > .dsb-ikon { display: none; }
        }

        @media (max-width: 1199.98px) {
            .rb-tabel th:nth-child(1), .rb-tabel td:nth-child(1) { width: 35%; }
            .rb-tabel th:nth-child(6), .rb-tabel td:nth-child(6) { width: 28%; }
        }
        @media (max-width: 991.98px) {
            .rb-tabel th:nth-child(1), .rb-tabel td:nth-child(1) { width: 44%; }
            .rb-tabel th:nth-child(5), .rb-tabel td:nth-child(5) { width: 18%; }
        }

        /* Saringan yang dilipat, pola yang sama dengan Master Harga. */
        .rb-lanjutan-panah { transition: transform .15s ease; }
        .rb-lanjutan-panah.is-buka { transform: rotate(180deg); }
        .rb-saring-lanjut {
            margin-top: 12px; padding-top: 12px;
            border-top: 1px dashed var(--dsb-tepi, #e9edf3);
        }

        /* ===== Salin & versi ===== */
        .rb-salin { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px 14px; }
        .rb-salin > .dsb-medan { flex: 1 1 200px; min-width: 0; }
        .rb-salin-tombol { flex: 0 0 auto; }
        .rb-beda { font-size: .74rem; color: var(--dsb-awas, #b45309); }
        .rb-nihil {
            display: flex; align-items: center; gap: 8px; margin: 0;
            font-size: .84rem; color: var(--dsb-redup, #6b7280);
        }
        .rb-baris-pilih {
            display: inline-flex; align-items: center; gap: 7px; margin-right: auto;
            font-size: .8rem; color: var(--dsb-redup, #6b7280);
        }
        .rb-baris-pilih .dsb-isian { width: auto; height: 36px; padding: 0 30px 0 11px; font-size: .8rem; }
        .rb-baru { margin-left: auto; }
        .rb-status-rak { display: flex; flex-direction: column; align-items: flex-start; gap: 4px; }

        .rb-halaman {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px;
            padding: 12px clamp(14px, 2vw, 20px); border-top: 1px solid #f1f5f9;
        }
        .rb-halaman > * { margin-bottom: 0 !important; }

        @media (max-width: 991.98px) {
            .rb-saring > .rb-medan-cari { flex: 1 1 100%; }
            .rb-saring > .rb-pilih { flex: 1 1 160px; max-width: none; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            /* table-layout: fixed TERUS BERLAKU walau barisnya sudah jadi
               blok: kotak tabelnya masih ada, jadi persentase kolom di atas
               tetap mengunci lebar sel. Sel "Perjalanan" dapat 24% dari 546px
               — 131px — dan judul perjalanan meluap keluar kartunya tanpa
               satu pun tanda. Kotak tabelnya ikut dibubarkan di sini, dan
               lebar selnya dikembalikan ke isi. */
            .rb-tabel { min-width: 0; display: block; table-layout: auto; }
            .rb-tabel tbody { display: block; }
            .rb-tabel thead { display: none; }
            .rb-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
            /* !important: aturan persentase di atas memakai :nth-child, dan
               bobotnya lebih tinggi daripada pemilih mana pun yang masuk akal
               di sini. */
            .rb-tabel tbody td { width: auto !important; }
            .rb-tabel tbody td {
                display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                padding: 4px 0; border: 0; text-align: left !important;
            }
            .rb-tabel tbody td[data-judul]::before {
                content: attr(data-judul);
                color: var(--dsb-redup, #6b7280); font-size: .72rem; font-weight: 700;
                letter-spacing: .03em; text-transform: uppercase; flex-shrink: 0;
            }
            /* Isi sel harus boleh menyempit; medan flex yang mempertahankan
               lebar isinya mendorong kartunya keluar layar. */
            .rb-tabel tbody td > * { min-width: 0; overflow-wrap: anywhere; }
            .rb-utama, .rb-status-rak { align-items: flex-end; text-align: right; }
        }

        @media (max-width: 575.98px) {
            .rb-tabel tbody td { flex-direction: column; align-items: stretch; gap: 2px; }
            .rb-tabel tbody td .rb-utama,
            .rb-tabel tbody td .rb-status-rak { align-items: flex-start; text-align: left; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
