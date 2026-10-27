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

        $judulRakitan = $this->judulOtomatis();
    @endphp

    <div class="dsb">
        {{-- ================== KEPALA ================== --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-judul">RAB &amp; Itinerary</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Susun itinerary private trip, hitung anggarannya, kirim penawarannya.</span>
                    <span class="d-block">Harga dirakit dari master harga — bukan diketik ulang tiap penawaran.</span>
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

        {{-- ================== RINGKASAN ==================

             Angkanya sudah ada di tiap baris; yang belum ada cara
             menjumlahkannya. "Berapa yang sudah dikirim tapi belum dijawab"
             adalah pertanyaan harian. Semuanya bisa diklik — angka yang
             menunjuk pekerjaan tetapi tidak membawa ke sana cuma menambah
             beban. --}}
        @if ($ringkas !== [])
            @php
                $ubin = [
                    ['kunci' => 'jumlah', 'label' => 'Penawaran', 'ket' => 'di seluruh daftar', 'warna' => 'var(--dsb-utama)'],
                    ['kunci' => 'draf', 'label' => 'Masih draf', 'ket' => 'belum dikirim', 'warna' => 'var(--dsb-netral)'],
                    ['kunci' => 'dikirim', 'label' => 'Sudah dikirim', 'ket' => 'menunggu jawaban', 'warna' => 'var(--dsb-telaah)'],
                    ['kunci' => 'disetujui', 'label' => 'Disetujui', 'ket' => 'jadi berangkat', 'warna' => 'var(--dsb-aman)'],
                    ['kunci' => 'kedaluwarsa', 'label' => 'Lewat masa berlaku', 'ket' => 'masih menunggu jawaban', 'warna' => 'var(--dsb-awas)'],
                ];
            @endphp

            <section class="dsb-bagian">
                <div class="rb-ubin-rak">
                    @foreach ($ubin as $u)
                        @php
                            $nilai = (int) ($ringkas[$u['kunci']] ?? 0);
                            $bisaDiklik = $u['kunci'] !== 'jumlah';
                            $terpasang = $bisaDiklik && $this->ubinNyala($u['kunci']);
                        @endphp
                        {{-- Keputusan saringannya dirakit di komponen, bukan di
                             dalam atribut wire: — isi atribut bukan kode PHP. --}}
                        <button type="button" class="rb-ubin {{ $terpasang ? 'is-nyala' : '' }} {{ $bisaDiklik ? '' : 'is-diam' }}"
                            style="--c: {{ $u['warna'] }}"
                            @if ($bisaDiklik)
                                wire:click="pasangUbin('{{ $u['kunci'] }}')"
                                title="{{ $terpasang ? 'Lepas saringannya' : 'Lihat yang '.strtolower($u['label']) }}"
                            @else
                                disabled
                            @endif>
                            <span class="rb-ubin-label">{{ $u['label'] }}</span>
                            <span class="rb-ubin-nilai">{{ number_format($nilai, 0, ',', '.') }}</span>
                            <span class="rb-ubin-ket">{{ $u['ket'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="rb-saring">
                            <div class="rb-medan-cari">
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Cari kode, pelanggan, judul, atau WhatsApp…',
                                ])
                            </div>

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

                            <button type="button" wire:click="bukaTambah" class="dsb-tombol is-utama rb-baru">
                                <i class="bi bi-plus-lg"></i><span>RAB Baru</span>
                            </button>

                            @if ($adaSaringan)
                                <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut"
                                    style="--ikon: #b45309" title="Kembali menampilkan seluruh RAB">
                                    <i class="bi bi-x-circle"></i><span>Tampilkan semua</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== RAB BARU ================== --}}
        @if ($tambah)
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-calculator"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">RAB Baru</h3>
                                    <span class="dsb-kartu-sub">Cukup yang dibutuhkan untuk mulai menghitung; destinasinya dipilih di langkah berikutnya.</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            {{-- Judul, kota, dan jumlah malam punya jawaban yang
                                 hampir selalu benar, jadi ketiganya terisi sendiri —
                                 tetapi kotaknya TETAP TERLIHAT. Menyembunyikannya di
                                 balik tombol menghemat ruang dengan menyembunyikan
                                 juga bahwa ketiganya boleh diubah. Baris rakitan di
                                 bawah memperlihatkan hasilnya, kotaknya menerima
                                 timpaan. --}}
                            {{-- Jenis perjalanan dipilih lebih dulu: ia menentukan
                                 isian mana yang berikutnya masuk akal. Private trip
                                 jumlah orangnya sudah pasti di kesepakatan; open trip
                                 harganya diumumkan lebih dulu dan jumlah orangnya baru
                                 ketahuan belakangan. --}}
                            <div class="rb-jenis">
                                @foreach ([
                                    'private' => ['Private Trip', 'Rombongan sudah pasti — kursi kosong bukan risiko kantor', 'bi-people-fill'],
                                    'open' => ['Open Trip', 'Harga diumumkan dulu, pesertanya menyusul', 'bi-megaphone-fill'],
                                ] as $kunci => [$label, $ket, $ikon])
                                    <button type="button" wire:click="$set('isian.jenis', '{{ $kunci }}')"
                                        class="rb-jenis-pilih {{ ($isian['jenis'] ?? 'private') === $kunci ? 'is-aktif' : '' }}">
                                        <span class="dsb-ikon is-kecil" style="--c: {{ $kunci === 'open' ? '#b45309' : '#4f46e5' }}">
                                            <i class="bi {{ $ikon }}"></i>
                                        </span>
                                        <span class="rb-jenis-teks">
                                            <strong>{{ $label }}</strong>
                                            <span class="dsb-kartu-sub">{{ $ket }}</span>
                                        </span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="rb-medan-rak">
                                <div class="dsb-medan rb-medan-lebar">
                                    <label class="dsb-label" for="rb-nama">Nama pelanggan <span class="rb-wajib">*</span></label>
                                    <input id="rb-nama" type="text" maxlength="120" wire:model="isian.nama_pelanggan"
                                        class="dsb-isian @error('isian.nama_pelanggan') is-galat @enderror"
                                        placeholder="Keluarga Bpk. Hendra">
                                    @error('isian.nama_pelanggan')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-wa">WhatsApp</label>
                                    <input id="rb-wa" type="text" maxlength="32" inputmode="tel" wire:model="isian.whatsapp"
                                        class="dsb-isian" placeholder="0812…">
                                    <span class="dsb-kartu-sub">Diperlukan nanti saat RAB dijadikan pendaftaran.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-prov">Provinsi tujuan <span class="rb-wajib">*</span></label>
                                    <select id="rb-prov" wire:model.live="isian.provinsi"
                                        class="dsb-isian @error('isian.provinsi') is-galat @enderror">
                                        <option value="" @selected((string) ($this->isian['provinsi'] ?? '') === '')>Pilih provinsi</option>
                                        @foreach ($provinsi as $p)
                                            <option value="{{ $p }}" @selected((string) ($this->isian['provinsi'] ?? '') === (string) $p)>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    @error('isian.provinsi')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-tgl">Tanggal berangkat</label>
                                    <input id="rb-tgl" type="date" wire:model.live="isian.tanggal_mulai" class="dsb-isian">
                                    <span class="dsb-kartu-sub">Boleh dikosongkan bila belum pasti.</span>
                                </div>

                                <div class="dsb-medan">
                                    {{-- Yang disepakati dengan pelanggan TANGGALNYA
                                         ("berangkat 10, pulang 12"), bukan jumlah harinya.
                                         Menyuruh admin menerjemahkannya sendiri adalah
                                         hitungan kepala yang mudah meleset satu — dan yang
                                         meleset satu hari menggeser seluruh modal
                                         akomodasi. --}}
                                    <label class="dsb-label" for="rb-tgl-pulang">Tanggal pulang</label>
                                    <input id="rb-tgl-pulang" type="date" wire:model.live="isian.tanggal_pulang"
                                        class="dsb-isian" min="{{ $isian['tanggal_mulai'] ?? '' }}">
                                    <span class="dsb-kartu-sub">Jumlah hari &amp; malam terisi sendiri dari rentangnya.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-hari">Jumlah hari <span class="rb-wajib">*</span></label>
                                    <input id="rb-hari" type="number" min="1" max="30"
                                        wire:model.live.debounce.400ms="isian.jumlah_hari"
                                        class="dsb-isian @error('isian.jumlah_hari') is-galat @enderror">
                                    @error('isian.jumlah_hari')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-peserta">
                                        {{ $this->openTrip() ? 'Target peserta' : 'Jumlah peserta' }} <span class="rb-wajib">*</span>
                                    </label>
                                    <input id="rb-peserta" type="number" min="1" max="500" wire:model.live.debounce.400ms="isian.jumlah_peserta"
                                        class="dsb-isian @error('isian.jumlah_peserta') is-galat @enderror">
                                    @error('isian.jumlah_peserta')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>

                                {{-- Angka yang menentukan harga open trip, dan satu-satunya
                                     yang benar-benar melindungi kantor: jumlah paling rendah
                                     yang masih mau diberangkatkan. Harga dipatok dari sini,
                                     sehingga yang datang di atasnya jadi untung — bukan
                                     penyelamat. --}}
                                @if ($this->openTrip())
                                    <div class="dsb-medan">
                                        <label class="dsb-label" for="rb-minimum">Peserta minimum <span class="rb-wajib">*</span></label>
                                        <input id="rb-minimum" type="number" min="1" max="500" wire:model.live.debounce.400ms="isian.peserta_minimum"
                                            class="dsb-isian @error('isian.peserta_minimum') is-galat @enderror">
                                        @error('isian.peserta_minimum')<span class="dsb-galat">{{ $message }}</span>@enderror
                                        <span class="dsb-kartu-sub">Harga dihitung dari angka ini, bukan dari target.</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Judul yang dirakit diperlihatkan, bukan dikerjakan
                                 diam-diam: yang tidak terlihat sebelum disimpan akan
                                 mengejutkan saat muncul di PDF penawaran. Baris ini
                                 tetap ada walau kotaknya terbuka — ia menjawab
                                 "kalau saya biarkan kosong, jadinya apa?". --}}
                            <div class="rb-rakitan">
                                <span class="dsb-ikon is-kecil" style="--c: #047857"><i class="bi bi-magic"></i></span>
                                <div class="rb-rakitan-teks">
                                    <span class="dsb-kartu-sub">Judul &amp; jumlah malam terisi sendiri</span>
                                    <strong class="rb-rakitan-judul">
                                        {{ trim((string) $isian['judul']) ?: $judulRakitan }}
                                        <span class="rb-rakitan-malam">· {{ (int) $isian['jumlah_malam'] }} malam</span>
                                    </strong>
                                </div>
                                <span class="rb-rakitan-ket">Boleh ditimpa di bawah</span>
                            </div>

                            <div class="rb-medan-rak rb-medan-sambung">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-judul">Judul perjalanan</label>
                                    <input id="rb-judul" type="text" maxlength="150" wire:model.live.debounce.400ms="isian.judul"
                                        class="dsb-isian @error('isian.judul') is-galat @enderror"
                                        placeholder="{{ $judulRakitan }}">
                                    @error('isian.judul')<span class="dsb-galat">{{ $message }}</span>@enderror
                                    <span class="dsb-kartu-sub">Kosong berarti pakai judul rakitan di atas.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-daerah">Kota / daerah</label>
                                    <input id="rb-daerah" type="text" maxlength="80" wire:model.live.debounce.400ms="isian.daerah"
                                        class="dsb-isian" placeholder="Yogyakarta">
                                    <span class="dsb-kartu-sub">Ikut terbaca di judul rakitan.</span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="rb-malam">Jumlah malam</label>
                                    <input id="rb-malam" type="number" min="0" max="30" wire:model="isian.jumlah_malam"
                                        class="dsb-isian @error('isian.jumlah_malam') is-galat @enderror">
                                    @error('isian.jumlah_malam')<span class="dsb-galat">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="rb-kaki">
                                <button type="button" wire:click="tutup" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </button>
                                <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                                    class="dsb-tombol is-lembut" style="--ikon: #4f46e5">
                                    <i class="bi bi-arrow-right-circle" wire:loading.remove wire:target="simpan"></i>
                                    <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                                    <span>Buat &amp; Susun</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

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
                                        <th>Perjalanan</th>
                                        <th>Tujuan</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Untung</th>
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
                                                <span class="rb-utama">
                                                    <a href="{{ route('admin.orcha.rab.susun', $baris['id']) }}" wire:navigate
                                                        class="dsb-tabel-judul rb-tautan">{{ $baris['judul'] }}</a>
                                                    <span class="dsb-tabel-meta">
                                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                                        {{ $baris['nama_pelanggan'] }} · {{ $baris['jumlah_peserta'] }} orang
                                                    </span>
                                                </span>
                                            </td>
                                            <td data-judul="Tujuan">
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
                                            <td data-judul="Untung" class="text-end">
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
        /* Pemilih jenis perjalanan: dua kartu, bukan kotak pilih. Keputusannya
           menentukan isian berikutnya, jadi ia pantas terlihat sebagai
           persimpangan — bukan sebagai satu baris di antara baris lain. */
        .rb-jenis { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px; }
        .rb-jenis-pilih {
            display: flex; align-items: flex-start; gap: 11px; text-align: left; cursor: pointer;
            padding: 12px 14px; border-radius: 13px; background: #fff;
            border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        .rb-jenis-pilih:hover { border-color: #c7d2fe; }
        .rb-jenis-pilih.is-aktif { border-color: #4f46e5; background: #f6f5ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .1); }
        .rb-jenis-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .rb-jenis-teks strong { color: var(--dsb-tinta, #1c1f26); font-size: .92rem; }

        .rb-medan-rak { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; align-items: start; }
        .rb-medan-rak > .dsb-medan { min-width: 0; }
        /* Tujuh medan di kisi tiga kolom menyisakan satu yang berdiri
           sendirian di baris terakhir. Nama pelanggan — yang memang paling
           butuh ruang — mengambil dua kolom, jadi barisnya terbagi 3/3/2. */
        .rb-medan-rak > .rb-medan-lebar { grid-column: span 2; }
        .rb-medan-rak .dsb-kartu-sub { display: block; margin-top: 5px; }
        .rb-medan-sambung { margin-top: 14px; padding-top: 14px; border-top: 1px solid #f1f5f9; }
        .rb-wajib { color: #dc2626; }

        /* Baris "terisi sendiri": memperlihatkan hasil rakitan sebelum
           disimpan. Yang dikerjakan diam-diam akan mengejutkan saat muncul
           di PDF penawaran. */
        .rb-rakitan {
            display: flex; align-items: center; flex-wrap: wrap; gap: 12px;
            margin-top: 14px; padding: 11px 14px;
            background: #f6fbf8; border: 1px solid #d1fae5; border-radius: 13px;
        }
        .rb-rakitan-teks { display: flex; flex-direction: column; min-width: 0; flex: 1 1 240px; }
        .rb-rakitan-judul { font-size: .9rem; color: var(--dsb-tinta, #1c1f26); }
        .rb-rakitan-malam { color: var(--dsb-redup, #6b7280); font-weight: 500; }
        .rb-rakitan-ket { font-size: .78rem; color: var(--dsb-redup, #94a3b8); white-space: nowrap; }

        .rb-kaki { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; }

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
        .rb-ubin-rak { display: flex; flex-wrap: wrap; gap: 12px; }
        .rb-ubin {
            flex: 1 1 170px; min-width: 0; display: flex; flex-direction: column; gap: 2px;
            padding: 13px 15px; text-align: left; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi, #e9edf3); border-radius: 15px;
            border-top: 3px solid var(--c);
            box-shadow: 0 10px 24px -20px rgba(15, 23, 42, .5);
            transition: transform .14s ease, box-shadow .14s ease, background .14s ease;
        }
        .rb-ubin:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 16px 30px -22px rgba(15, 23, 42, .55); }
        .rb-ubin.is-diam { cursor: default; background: #fbfcfe; box-shadow: none; }
        .rb-ubin.is-diam:hover { transform: none; box-shadow: none; }
        .rb-ubin.is-nyala { background: color-mix(in srgb, var(--c) 9%, #fff); border-color: color-mix(in srgb, var(--c) 32%, #fff); }
        .rb-ubin-label { font-size: .74rem; font-weight: 700; color: var(--dsb-redup, #6b7280); letter-spacing: .02em; }
        .rb-ubin-nilai { font-size: 1.5rem; font-weight: 800; color: var(--c); line-height: 1.1; }
        .rb-ubin-ket { font-size: .72rem; color: var(--dsb-redup, #6b7280); }

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
            .rb-medan-rak { grid-template-columns: repeat(2, 1fr); }
            .rb-medan-rak > .rb-medan-lebar { grid-column: span 1; }
            .rb-jenis { grid-template-columns: 1fr; }
        }

        /* Di ponsel tabel berhenti jadi tabel: tiap baris jadi kartu
           bertumpuk, aturan yang sama dengan daftar lemon lain. */
        @media (max-width: 767.98px) {
            .rb-medan-rak { grid-template-columns: 1fr; }
            .rb-tabel { min-width: 0; }
            .rb-tabel thead { display: none; }
            .rb-tabel tbody tr { display: block; padding: 13px 15px; border-bottom: 1px solid #f2f5f9; }
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
            .rb-tabel tbody td > * { min-width: 0; }
            .rb-utama, .rb-status-rak { align-items: flex-end; text-align: right; }
        }
    </style>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
