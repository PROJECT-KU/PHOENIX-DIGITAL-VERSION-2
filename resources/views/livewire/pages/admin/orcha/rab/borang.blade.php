@section('title')
{{ $ubah ? 'Ubah RAB' : 'RAB Baru' }} || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan
         daftar RAB dan penyusunnya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Gayanya ditulis telanjang di badan komponen, bukan dititipkan ke
           tumpukan: templateindex tidak menyediakan satu pun tempat tumpuk,
           jadi yang dititipkan ke sana hilang tanpa galat dan halamannya
           tetap 200. Direktifnya sengaja tidak ditulis di komentar ini —
           Blade mengompilasinya juga di sini, dan halamannya mati. */

        /* TIDAK dipatok max-width: kartunya harus selebar kartu di daftar RAB
           dan selebar hero di atasnya. Dipersempit, ia terbaca seperti layar
           lain yang kebetulan menumpang di sini. Isian pendek — "Hari",
           "Malam", "Peserta" — dijaga tidak membentang oleh kisi tiga
           kolomnya, bukan oleh lebar halamannya. */

        /* Pemilih jenis perjalanan: dua kartu, bukan kotak pilih. Keputusannya
           menentukan isian berikutnya, jadi ia pantas terlihat sebagai
           persimpangan — bukan sebagai satu baris di antara baris lain. */
        .rb-jenis { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .rb-jenis-pilih {
            display: flex; align-items: flex-start; gap: 11px; text-align: left; cursor: pointer;
            padding: 12px 14px; border-radius: 13px; background: #fff;
            border: 1px solid var(--dsb-tepi, #e9edf3);
        }
        .rb-jenis-pilih:hover { border-color: #c7d2fe; }
        .rb-jenis-pilih.is-aktif { border-color: #4f46e5; background: #f6f5ff; box-shadow: 0 0 0 3px rgba(99, 102, 241, .1); }
        .rb-jenis-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .rb-jenis-teks strong { color: var(--dsb-tinta, #1c1f26); font-size: .92rem; }
        /* Pada RAB yang sudah jadi, jenisnya bukan lagi persimpangan: harga,
           modal, dan titik impas sudah terlanjur dihitung atas dasarnya. Yang
           tidak terpilih diredupkan, bukan disembunyikan — admin tetap berhak
           tahu pilihan apa yang pernah ada. */
        .rb-jenis-pilih:disabled { cursor: default; opacity: .45; }
        .rb-jenis-pilih:disabled.is-aktif { opacity: 1; }

        .rb-medan-rak { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px 16px; align-items: start; }
        .rb-medan-rak > .dsb-medan { min-width: 0; }
        /* Tujuh medan di kisi tiga kolom menyisakan satu yang berdiri
           sendirian di baris terakhir. Nama pelanggan — yang memang paling
           butuh ruang — mengambil dua kolom, jadi barisnya terbagi 3/3/2. */
        .rb-medan-rak > .rb-medan-lebar { grid-column: span 2; }
        .rb-medan-rak .dsb-kartu-sub { display: block; margin-top: 5px; }
        .rb-wajib { color: #dc2626; }

        /* Bagian: satu kartu per pertanyaan besar, dengan judul kecil yang
           menyebut apa yang sedang ditanyakan. Satu kartu panjang berisi
           lima belas isian terbaca seperti borang pajak. */
        .rb-bagian + .rb-bagian { margin-top: 14px; }
        .rb-bagian-kepala { display: flex; align-items: flex-start; gap: 11px; margin-bottom: 14px; }
        .rb-bagian-teks { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .rb-bagian-teks strong { font-size: .95rem; color: var(--dsb-tinta, #1c1f26); }

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

        /* Kaki dipakukan ke dasar layar: borang setinggi dua layar menyimpan
           tombol simpannya di tempat yang harus dicari dengan menggulung. */
        .rb-kaki {
            position: sticky; bottom: 0; z-index: 3;
            display: flex; align-items: center; flex-wrap: wrap; gap: 10px;
            margin-top: 16px; padding: 12px 16px; border-radius: 15px;
            background: rgba(255, 255, 255, .92); backdrop-filter: blur(6px);
            border: 1px solid var(--dsb-tepi, #e9edf3);
            box-shadow: 0 6px 22px rgba(15, 23, 42, .07);
        }
        .rb-kaki-ket { font-size: .8rem; color: var(--dsb-redup, #6b7280); margin-right: auto; }

        @media (max-width: 991.98px) {
            .rb-medan-rak { grid-template-columns: repeat(2, 1fr); }
            .rb-medan-rak > .rb-medan-lebar { grid-column: span 1; }
            .rb-jenis { grid-template-columns: 1fr; }
        }

        @media (max-width: 575.98px) {
            .rb-medan-rak { grid-template-columns: 1fr; }
            .rb-kaki { flex-direction: column; align-items: stretch; }
            .rb-kaki-ket { margin-right: 0; }
            .rb-kaki .dsb-tombol { justify-content: center; }
        }
    </style>

    <div class="dsb">
    @include('livewire.pages.admin.orcha.partials.galat-dsb')

    {{-- ================== KEPALA HALAMAN ==================

         Bentuknya mengikuti daftar RAB: .dsb-hero + .dsb-salam + .dsb-hero-ket,
         kelas yang memang ADA di dasbor-gaya. Nama kelas karangan sendiri tidak
         menimbulkan galat apa pun — teksnya cuma mewarisi gaya induknya, dan
         kalimat penjelas tercetak setinggi judul. --}}
    <header class="dsb-hero">
        <div class="dsb-hero-teks">
            <span class="dsb-kicker">
                <i class="bi bi-calculator"></i> {{ $ubah ? 'Ubah data perjalanan' : 'RAB baru' }}
            </span>
            <h1 class="dsb-salam">{{ $ubah ? ($kode ?: 'Data perjalanan') : 'Mulai dari data perjalanannya' }}</h1>
            <p class="dsb-hero-ket">
                @if ($ubah)
                    Yang diubah di sini kepala penawarannya — siapa, ke mana, kapan, berapa orang.
                    Destinasi dan biayanya tetap disusun di halaman penyusun.
                @else
                    Cukup yang dibutuhkan untuk mulai menghitung.
                    Destinasi, biaya, dan harganya dipilih di langkah berikutnya.
                @endif
            </p>
        </div>

        <div class="dsb-hero-aksi">
            <a href="{{ $this->tujuanKembali() }}" class="dsb-tombol is-lembut" style="--ikon: #64748b" wire:navigate>
                <i class="bi bi-arrow-left"></i>
                <span>{{ $ubah && $dari !== 'daftar' ? 'Kembali ke penyusun' : 'Daftar RAB' }}</span>
            </a>
        </div>
    </header>

    <div class="rb-borang">
        {{-- ================== JENIS PERJALANAN ==================

             Dipilih lebih dulu: ia menentukan isian mana yang berikutnya
             masuk akal. Private trip jumlah orangnya sudah pasti di
             kesepakatan; open trip harganya diumumkan lebih dulu dan
             jumlah orangnya baru ketahuan belakangan. --}}
        <section class="dsb-bagian rb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="rb-bagian-kepala">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-utama)"><i class="bi bi-signpost-split"></i></span>
                            <div class="rb-bagian-teks">
                                <strong>Jenis perjalanan</strong>
                                <span class="dsb-kartu-sub">
                                    @if ($ubah)
                                        Tidak bisa diganti di sini — harga, modal, dan titik impas sudah dihitung atas dasarnya.
                                    @else
                                        Menentukan atas dasar jumlah berapa harganya nanti dihitung.
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="rb-jenis">
                            @foreach ([
                                'private' => ['Private Trip', 'Rombongan sudah pasti — kursi kosong bukan risiko kantor', 'bi-people-fill'],
                                'open' => ['Open Trip', 'Harga diumumkan dulu, pesertanya menyusul', 'bi-megaphone-fill'],
                            ] as $kunci => [$label, $ket, $ikon])
                                <button type="button" @disabled($ubah)
                                    wire:click="$set('isian.jenis', '{{ $kunci }}')"
                                    class="rb-jenis-pilih {{ ($isian['jenis'] ?? 'private') === $kunci ? 'is-aktif' : '' }}">
                                    <span class="dsb-ikon is-kecil" style="--c: {{ $kunci === 'open' ? 'var(--dsb-tunggu)' : 'var(--dsb-utama)' }}">
                                        <i class="bi {{ $ikon }}"></i>
                                    </span>
                                    <span class="rb-jenis-teks">
                                        <strong>{{ $label }}</strong>
                                        <span class="dsb-kartu-sub">{{ $ket }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== PELANGGAN ================== --}}
        <section class="dsb-bagian rb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="rb-bagian-kepala">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-info)"><i class="bi bi-person-vcard"></i></span>
                            <div class="rb-bagian-teks">
                                <strong>{{ $this->openTrip() ? 'Penawaran' : 'Pelanggan' }}</strong>
                                <span class="dsb-kartu-sub">
                                    @if ($this->openTrip())
                                        Open trip belum punya pelanggan — harganya diumumkan dulu, pesertanya menyusul lewat halaman pendaftaran.
                                    @else
                                        Nama yang tercetak di penawaran, dan ke mana penawarannya dikirim.
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="rb-medan-rak">
                            {{-- Ketiganya tidak ada pada open trip. Menampilkannya
                                 abu-abu atau "boleh dikosongkan" tetap menyuruh admin
                                 memutuskan sesuatu yang tidak perlu diputuskan. --}}
                            @unless ($this->openTrip())
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

                            <div class="dsb-medan rb-medan-lebar">
                                <label class="dsb-label" for="rb-email">Email</label>
                                <input id="rb-email" type="email" maxlength="150" wire:model="isian.email"
                                    class="dsb-isian @error('isian.email') is-galat @enderror" placeholder="hendra@contoh.com">
                                @error('isian.email')<span class="dsb-galat">{{ $message }}</span>@enderror
                                <span class="dsb-kartu-sub">Boleh dikosongkan; penawaran tetap bisa dikirim lewat WhatsApp.</span>
                            </div>
                            @endunless

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rb-berlaku">Penawaran berlaku sampai</label>
                                <input id="rb-berlaku" type="date" wire:model="isian.berlaku_sampai" class="dsb-isian @error('isian.berlaku_sampai') is-galat @enderror">
                                @error('isian.berlaku_sampai')<span class="dsb-galat">{{ $message }}</span>@enderror
                                <span class="dsb-kartu-sub">Lewat tanggal ini, RAB ditandai kedaluwarsa.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== PERJALANAN ================== --}}
        <section class="dsb-bagian rb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="rb-bagian-kepala">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-selesai)"><i class="bi bi-geo-alt"></i></span>
                            <div class="rb-bagian-teks">
                                <strong>Ke mana dan kapan</strong>
                                <span class="dsb-kartu-sub">Tanggalnya menentukan harga tiket: akhir pekan dan tanggal merah sering lebih mahal.</span>
                            </div>
                        </div>

                        <div class="rb-medan-rak">
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
                                <label class="dsb-label" for="rb-daerah">Kota / daerah</label>
                                <input id="rb-daerah" type="text" maxlength="80" wire:model.live.debounce.400ms="isian.daerah"
                                    class="dsb-isian" placeholder="Yogyakarta">
                                <span class="dsb-kartu-sub">Ikut terbaca di judul rakitan.</span>
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
                                @if ($hariTerpakai > 0)
                                    <span class="dsb-kartu-sub">Itinerary sudah terisi sampai hari ke-{{ $hariTerpakai }}.</span>
                                @endif
                            </div>

                            <div class="dsb-medan">
                                <label class="dsb-label" for="rb-malam">Jumlah malam</label>
                                <input id="rb-malam" type="number" min="0" max="30" wire:model="isian.jumlah_malam"
                                    class="dsb-isian @error('isian.jumlah_malam') is-galat @enderror">
                                @error('isian.jumlah_malam')<span class="dsb-galat">{{ $message }}</span>@enderror
                                <span class="dsb-kartu-sub">Terisi sendiri dari jumlah hari; boleh ditimpa.</span>
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

                            <div class="dsb-medan rb-medan-lebar">
                                <label class="dsb-label" for="rb-judul">Judul perjalanan</label>
                                <input id="rb-judul" type="text" maxlength="150" wire:model.live.debounce.400ms="isian.judul"
                                    class="dsb-isian @error('isian.judul') is-galat @enderror"
                                    placeholder="{{ $judulRakitan }}">
                                @error('isian.judul')<span class="dsb-galat">{{ $message }}</span>@enderror
                                <span class="dsb-kartu-sub">Kosong berarti pakai judul rakitan di bawah.</span>
                            </div>
                        </div>

                        {{-- Judul yang dirakit diperlihatkan, bukan dikerjakan
                             diam-diam: yang tidak terlihat sebelum disimpan akan
                             mengejutkan saat muncul di PDF penawaran. Baris ini
                             tetap ada walau kotaknya terbuka — ia menjawab
                             "kalau saya biarkan kosong, jadinya apa?". --}}
                        <div class="rb-rakitan">
                            <span class="dsb-ikon is-kecil" style="--c: var(--dsb-selesai)"><i class="bi bi-magic"></i></span>
                            <div class="rb-rakitan-teks">
                                <span class="dsb-kartu-sub">Judul terisi sendiri bila dikosongkan</span>
                                <strong class="rb-rakitan-judul">
                                    {{ trim((string) $isian['judul']) ?: $judulRakitan }}
                                    <span class="rb-rakitan-malam">· {{ (int) $isian['jumlah_malam'] }} malam</span>
                                </strong>
                            </div>
                            <span class="rb-rakitan-ket">Boleh ditimpa di kotak di atas</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== CATATAN ==================

             Hanya saat mengubah: RAB yang baru dibuat belum punya apa pun
             untuk dicatat, dan dua kotak teks kosong di langkah pertama
             membuat borangnya terbaca lebih panjang daripada yang perlu. --}}
        @if ($ubah)
            <section class="dsb-bagian rb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi">
                            <div class="rb-bagian-kepala">
                                <span class="dsb-ikon is-kecil" style="--c: var(--dsb-redup)"><i class="bi bi-journal-text"></i></span>
                                <div class="rb-bagian-teks">
                                    <strong>Catatan</strong>
                                    <span class="dsb-kartu-sub">Yang kiri dibaca pelanggan, yang kanan tidak pernah keluar kantor.</span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label" for="rb-cat-tawar">
                                        Catatan di penawaran
                                        <span class="dsb-kartu-sub d-inline">(dibaca pelanggan)</span>
                                    </label>
                                    <textarea id="rb-cat-tawar" wire:model="isian.catatan_penawaran" rows="4" maxlength="3000"
                                        class="dsb-isian" placeholder="Harga belum termasuk tiket pesawat PP."></textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="dsb-label" for="rb-cat-dalam">
                                        Catatan internal
                                        <span class="dsb-kartu-sub d-inline">(hanya di PDF internal)</span>
                                    </label>
                                    <textarea id="rb-cat-dalam" wire:model="isian.catatan" rows="4" maxlength="3000"
                                        class="dsb-isian" placeholder="Vendor, hasil negosiasi, hal yang perlu dicek."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ================== KAKI ================== --}}
        <div class="rb-kaki">
            <span class="rb-kaki-ket">
                @if ($ubah)
                    Destinasi, biaya, dan harganya tetap diatur di halaman penyusun.
                @else
                    Setelah disimpan, Anda langsung dibawa ke halaman penyusunnya.
                @endif
            </span>
            <a href="{{ $this->tujuanKembali() }}" class="dsb-tombol is-lembut" style="--ikon: #64748b" wire:navigate>
                <i class="bi bi-x-lg"></i><span>Batal</span>
            </a>
            <button type="button" wire:click="simpan" wire:loading.attr="disabled" wire:target="simpan"
                class="dsb-tombol is-utama">
                <i class="bi {{ $ubah ? 'bi-check-lg' : 'bi-arrow-right-circle' }}" wire:loading.remove wire:target="simpan"></i>
                <span class="dsb-putar" wire:loading wire:target="simpan"></span>
                <span>{{ $ubah ? 'Simpan perubahan' : 'Buat & Susun' }}</span>
            </button>
        </div>
    </div>
    </div>
</div>
