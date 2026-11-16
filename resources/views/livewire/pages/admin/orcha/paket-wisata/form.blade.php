@section('title')
{{ $ubah ? 'Ubah' : 'Tambah' }} Paket Wisata || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon, sama dengan daftar paketnya dan
         dengan Pesanan Toko. Kelas orcha-* hanya untuk yang memang khas Orcha:
         lencana penayangan dan angka untung. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $margin = $this->marginPerOrang();
        $lihatUrl = $tautanPratinjau ?: $tautanPublik;
    @endphp

    <style>
        /* Gaya khusus borang Paket Wisata. Dasarnya partials/dasbor-gaya;
           di sini hanya yang tidak ada di sana. Inline, bukan lewat Vite:
           public/build masuk .gitignore dan tidak ikut ter-deploy. */

        .pb-kisi { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .pb-kisi.is-tiga { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pb-kisi.is-satu { grid-template-columns: minmax(0, 1fr); }
        @media (max-width: 767.98px) {
            .pb-kisi, .pb-kisi.is-tiga { grid-template-columns: minmax(0, 1fr); }
        }

        .pb-ket { color: var(--dsb-redup, #64748b); font-size: .76rem; line-height: 1.45; }
        .pb-galat { color: #b91c1c; font-size: .76rem; font-weight: 600; }

        /* ===== Cip pilihan (tempat, fasilitas, tidak termasuk, titik jemput) ===== */
        .pb-cip-deret { display: flex; flex-wrap: wrap; gap: 7px; }
        .pb-cip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 10px; border: 1px solid var(--dsb-tepi, #e2e8f0); border-radius: 999px;
            background: #fff; font-size: .78rem; font-weight: 600; color: #475569;
        }
        .pb-cip.is-terpilih { background: #ede9fe; border-color: #ddd6fe; color: #5b21b6; }
        .pb-cip-tombol { border: 0; background: none; padding: 0; font: inherit; color: inherit; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .pb-cip-buang { border: 0; background: none; padding: 0; color: #94a3b8; cursor: pointer; line-height: 1; }
        .pb-cip-buang:hover { color: #b91c1c; }
        .pb-tambah { display: flex; gap: 8px; margin-top: 10px; }
        .pb-tambah .dsb-isian { flex: 1 1 auto; }

        /* ===== Tanggal keberangkatan ===== */
        .pb-kb { border: 1px solid var(--dsb-tepi, #e2e8f0); border-radius: 14px; overflow: hidden; }
        /* DUA kolom yang dipatok, bukan satu baris flex yang melipat.

           Sebagai flex-wrap, baris yang lencananya banyak mendorong tombolnya
           turun ke baris kedua — sementara baris di atas dan di bawahnya
           tetap sebaris. Deretan tombolnya jadi bertingkat-tingkat, dan yang
           terbaca bukan "baris ini isinya lebih banyak" melainkan "layarnya
           berantakan". Kini lencananya yang melipat di dalam kolomnya
           sendiri; tombolnya tidak pernah pindah tempat. */
        .pb-kb-baris {
            display: grid; grid-template-columns: minmax(0, 1fr) auto;
            align-items: center; gap: 10px;
            padding: 12px 14px; border-top: 1px solid #f1f5f9;
        }
        .pb-kb-isi { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; min-width: 0; }
        @media (max-width: 767.98px) {
            .pb-kb-baris { grid-template-columns: minmax(0, 1fr); }
            .pb-kb-aksi { margin-left: 0; }
        }
        .pb-kb-baris:first-child { border-top: 0; }
        .pb-kb-baris.is-sunting { background: #faf5ff; box-shadow: inset 3px 0 0 #7c3aed; }
        .pb-kb-tanggal { font-weight: 800; color: var(--dsb-tinta, #1c1f26); }

        /* Tombol baris: SEMUANYA setinggi 34px.

           Sebelumnya tiga ukuran berdiri bersebelahan — ikon pensil & tong
           sampah 34px, "Salin" & "Tutup" 44px bawaan .dsb-tombol — dan garis
           bawah keduanya tidak pernah sejajar. Terbaca sebagai barisan yang
           berantakan, bukan sebagai empat tombol yang setara. Sewa Kendaraan
           pernah punya cacat yang sama persis. */
        .pb-kb-aksi { margin-left: auto; display: inline-flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .pb-kb-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            height: 34px; min-width: 34px; padding: 0 10px;
            border: 1px solid var(--dsb-tepi, #e2e8f0); border-radius: 10px; background: #fff;
            font-size: .76rem; font-weight: 700; line-height: 1; color: #475569;
            cursor: pointer; white-space: nowrap;
            transition: border-color .18s ease, color .18s ease, background .18s ease;
        }
        .pb-kb-btn i.bi { font-size: .85rem; line-height: 1; color: var(--ikon, #7c3aed); }
        .pb-kb-btn i.bi::before { display: block; line-height: 1; }
        .pb-kb-btn:hover { border-color: #cbd5e1; color: #1c1f26; }
        .pb-kb-btn.is-bahaya { color: #dc2626; border-color: #fecaca; }
        .pb-kb-btn.is-bahaya i.bi { color: inherit; }
        .pb-kb-btn.is-bahaya:hover { background: #dc2626; color: #fff; border-color: transparent; }

        /* ===== Kotak isian tanggal ===== */
        .pb-kb-borang { padding: 16px 14px; background: #f8fafc; border-top: 1px solid var(--dsb-tepi, #e2e8f0); }
        .pb-kb-judul {
            display: inline-flex; align-items: center; gap: 7px; margin-bottom: 12px;
            font-size: .82rem; font-weight: 800; color: var(--dsb-tinta, #1c1f26);
        }
        .pb-kb-judul i.bi { color: #7c3aed; }

        /* Empat isian sederajat dalam satu baris, bukan tiga di atas dan satu
           menggantung di bawah. Keempatnya menggambarkan satu tanggal yang
           sama; memecahnya jadi dua baris membuat yang di bawah terbaca
           seperti bagian yang lain. */
        .pb-kb-isian {
            display: grid; gap: 12px; align-items: start;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        @media (max-width: 1199.98px) {
            .pb-kb-isian { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 575.98px) {
            .pb-kb-isian { grid-template-columns: minmax(0, 1fr); }
        }

        /* Tombolnya punya barisnya sendiri dan rata kanan — bukan menumpang di
           salah satu kolom isian, yang membuatnya mengambang tanpa segaris
           dengan apa pun. */
        .pb-kb-kaki {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            margin-top: 14px; padding-top: 14px; border-top: 1px solid #e8edf3;
        }
        .pb-kb-kaki-ket { color: var(--dsb-redup, #64748b); font-size: .76rem; max-width: 56ch; }
        .pb-kb-kaki-tombol { display: inline-flex; gap: 8px; margin-left: auto; }
        @media (max-width: 575.98px) {
            .pb-kb-kaki-tombol { width: 100%; }
            .pb-kb-kaki-tombol .dsb-tombol { flex: 1 1 0; }
        }

        /* ===== Jadwal kegiatan ===== */
        .pb-hari { border: 1px solid var(--dsb-tepi, #e2e8f0); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
        .pb-hari-kepala { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .pb-hari-nomor {
            width: 30px; height: 30px; border-radius: 10px; flex-shrink: 0;
            display: inline-flex; align-items: center; justify-content: center;
            background: #ede9fe; color: #6d28d9; font-weight: 800; font-size: .82rem;
        }
        .pb-hari-aksi { margin-left: auto; display: inline-flex; gap: 6px; }
        .pb-agenda {
            display: grid; gap: 8px; align-items: center; margin-bottom: 8px;
            grid-template-columns: 96px minmax(0, 1fr) auto;
        }
        @media (max-width: 575.98px) {
            .pb-agenda { grid-template-columns: 80px minmax(0, 1fr) auto; }
        }
        .pb-agenda-aksi { display: inline-flex; gap: 4px; }

        /* ===== Foto ===== */
        .pb-sampul { position: relative; border-radius: 14px; overflow: hidden; background: #f1f5f9; aspect-ratio: 16 / 10; }
        .pb-sampul img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .pb-sampul-label {
            position: absolute; left: 10px; bottom: 10px;
            padding: 3px 9px; border-radius: 999px;
            background: rgba(15, 23, 42, .72); color: #fff; font-size: .68rem; font-weight: 700;
        }
        .pb-galeri { display: grid; gap: 8px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .pb-galeri-kotak { position: relative; border-radius: 12px; overflow: hidden; background: #f1f5f9; aspect-ratio: 4 / 3; }
        .pb-galeri-kotak img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .pb-galeri-buang {
            position: absolute; top: 5px; right: 5px; z-index: 2;
            width: 24px; height: 24px; border: 0; border-radius: 50%;
            background: rgba(15, 23, 42, .72); color: #fff; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; font-size: .7rem;
        }
        .pb-galeri-buang:hover { background: #b91c1c; }

        /* ===== Bilah tindakan ===== */
        .pb-bilah { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }
        .pb-bilah .dsb-tombol { min-width: 150px; }
        @media (max-width: 575.98px) {
            .pb-bilah .dsb-tombol { flex: 1 1 100%; }
        }
    </style>

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-salam">{{ $ubah ? 'Ubah Paket Wisata' : 'Tambah Paket Wisata' }}</h1>
                <p class="dsb-hero-ket">
                    Begitu disimpan, perubahannya langsung tampil di website Orcha —
                    termasuk fotonya.
                </p>

                <div class="dsb-chip-deret mt-2">
                    <span class="badge orcha-lencana-{{ $statusTayang }}">{{ $statusTayangLabel }}</span>

                    @if ($margin === null)
                        <span class="dsb-chip" style="--c: #d97706">
                            <i class="bi bi-exclamation-triangle-fill"></i> modal belum diisi
                        </span>
                    @elseif ($margin < 0)
                        <span class="dsb-chip" style="--c: #dc2626">
                            <i class="bi bi-graph-down-arrow"></i>
                            rugi Rp {{ number_format(abs($margin), 0, ',', '.') }}/orang
                        </span>
                    @else
                        <span class="dsb-chip" style="--c: #16a34a">
                            <i class="bi bi-graph-up-arrow"></i>
                            untung Rp {{ number_format($margin, 0, ',', '.') }}/orang
                        </span>
                    @endif
                </div>
            </div>

            {{-- Tindakan di KEPALA halaman, bukan cuma di kaki.

                 Borang ini panjang — jadwal kegiatan saja bisa puluhan baris —
                 dan tombol Simpan yang hanya ada di ujung bawah berarti tiap
                 perubahan kecil menuntut menggulir seluruh halaman dulu. Di
                 ponsel jaraknya paling terasa. Tombolnya tetap diulang di kaki
                 untuk yang memang sedang berada di sana. --}}
            <div class="dsb-hero-aksi">
                @if ($ubah && $lihatUrl)
                    <a href="{{ $lihatUrl }}" target="_blank" rel="noopener"
                        class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                        title="{{ $statusTayang === 'tayang'
                            ? 'Buka halamannya di website Orcha'
                            : 'Lihat pratinjaunya — paket ini belum tampil di website' }}">
                        <i class="bi bi-box-arrow-up-right"></i><span>Lihat halamannya</span>
                    </a>
                @endif

                <button type="button" wire:click="simpan" class="dsb-tombol is-utama"
                    wire:loading.attr="disabled" wire:target="simpan">
                    <i class="bi bi-check-lg"></i>
                    <span>{{ $ubah ? 'Simpan Perubahan' : 'Tambah Paket' }}</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        <form wire:submit="simpan">
            {{-- ================== KETERANGAN & PENAYANGAN ================== --}}
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-8">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-map"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Keterangan Paket</h3>
                                    <span class="dsb-kartu-sub">Nama, jenis, dan lamanya trip</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <div class="pb-kisi">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-nama">
                                        Nama paket <span class="dsb-wajib">*</span>
                                    </label>
                                    <input id="pb-nama" type="text" class="dsb-isian" wire:model="nama"
                                        value="{{ $nama }}" placeholder="Open Trip Banyuwangi">
                                    @error('nama') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kategori">
                                        Kategori <span class="dsb-wajib">*</span>
                                    </label>
                                    <select id="pb-kategori" class="dsb-isian" wire:model="kategori">
                                        @foreach ($pilihanKategori as $kunci => $label)
                                            <option value="{{ $kunci }}" @selected((string) $this->kategori === (string) $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tanggal paket & lama trip.

                                 Keterangannya menyebut hubungan kedua tempat ini
                                 dengan daftar Tanggal Keberangkatan di bawahnya.
                                 Tanpa itu, dua tempat yang sama-sama meminta
                                 tanggal terbaca seperti salah satunya keliru. --}}
                            <div class="pb-kisi is-tiga mt-3">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-durasi">
                                        Lama trip
                                        @if ($durasiOtomatis)
                                            <span class="dsb-lencana is-abu">otomatis</span>
                                        @else
                                            <button type="button" class="pb-cip-tombol" wire:click="hitungDurasi"
                                                title="Kembalikan ke hasil hitungan">
                                                <i class="bi bi-arrow-repeat"></i> hitung ulang
                                            </button>
                                        @endif
                                    </label>
                                    <input id="pb-durasi" type="text" class="dsb-isian" wire:model.blur="durasi"
                                        value="{{ $durasi }}" placeholder="3 Hari 2 Malam">
                                    <span class="pb-ket">
                                        {{ $durasiOtomatis
                                            ? 'Terisi sendiri dari tanggal di sebelah. Boleh Anda ganti.'
                                            : 'Anda tulis sendiri, tidak lagi ikut tanggal.' }}
                                    </span>
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-berangkat">Tanggal berangkat</label>
                                    <input id="pb-berangkat" type="date" class="dsb-isian"
                                        wire:model.live="tanggalBerangkat" value="{{ $tanggalBerangkat }}">
                                    @error('tanggalBerangkat') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-pulang">Tanggal pulang</label>
                                    <input id="pb-pulang" type="date" class="dsb-isian"
                                        wire:model.live="tanggalPulang" value="{{ $tanggalPulang }}">
                                    @error('tanggalPulang') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @if ($ubah)
                                <p class="pb-ket mt-2">
                                    <i class="bi bi-info-circle"></i>
                                    Dua tanggal ini yang dipakai <strong>kartu katalog dan PDF</strong>.
                                    Tanggal yang bisa <strong>dipilih pendaftar</strong> diatur di
                                    kotak “Tanggal Keberangkatan” di bawah.
                                </p>
                            @endif

                            {{-- Minimal peserta & jumlah kursi BERSEBELAHAN.

                                 Keduanya saling memvalidasi — kursi yang lebih
                                 sedikit daripada minimal peserta berarti tripnya
                                 tidak akan pernah berangkat — dan dulu berdiri di
                                 dua sudut kartu yang berbeda, sehingga hubungan
                                 itu tidak pernah terlihat saat mengisinya. --}}
                            <div class="pb-kisi mt-3">
                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-minimal">
                                        Minimal peserta <span class="dsb-wajib">*</span>
                                    </label>
                                    <input id="pb-minimal" type="number" min="1" class="dsb-isian"
                                        wire:model="minimalPeserta" value="{{ $minimalPeserta }}">
                                    <span class="pb-ket">Kurang dari ini, tripnya tidak jadi berangkat.</span>
                                    @error('minimalPeserta') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>

                                <div class="dsb-medan">
                                    <label class="dsb-label" for="pb-kuota">Jumlah kursi</label>
                                    <input id="pb-kuota" type="number" min="1" class="dsb-isian"
                                        wire:model="kuota" placeholder="Kosongkan kalau belum tahu">
                                    <span class="pb-ket">
                                        Dikosongkan: pendaftaran tidak pernah ditutup sendiri.
                                        Diisi: berhenti otomatis saat kursinya habis.
                                    </span>
                                    @error('kuota') <span class="pb-galat">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="dsb-medan mt-3">
                                <label class="dsb-label" for="pb-promo">Catatan promo</label>
                                <input id="pb-promo" type="text" class="dsb-isian" wire:model="catatanPromo"
                                    value="{{ $catatanPromo }}" placeholder="Harga khusus sampai akhir bulan">
                            </div>
                        </div>
                    </div>

                    {{-- ================== TAMPIL DI WEBSITE ================== --}}
                    <div class="dsb-kartu k-4">
                        <div class="dsb-kartu-kepala">
                            <div class="dsb-kartu-kepala-kiri">
                                <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-broadcast"></i></span>
                                <div>
                                    <h3 class="dsb-kartu-judul">Tampil di Website</h3>
                                    <span class="dsb-kartu-sub">Diatur lewat tanggal, bukan tombol</span>
                                </div>
                            </div>
                        </div>

                        <div class="dsb-kartu-isi">
                            <p class="pb-ket">
                                Paketnya muncul dan berhenti sendiri, jadi tidak ada yang
                                perlu Anda ingat-ingat.
                            </p>

                            <div class="dsb-medan mt-3">
                                <label class="dsb-label" for="pb-status">
                                    Status <span class="dsb-wajib">*</span>
                                </label>
                                <select id="pb-status" class="dsb-isian" wire:model.live="status">
                                    @foreach ($pilihanStatusPaket as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected((string) $this->status === (string) $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="dsb-medan mt-3">
                                <label class="dsb-label" for="pb-tayang-mulai">Mulai tampil</label>
                                <input id="pb-tayang-mulai" type="datetime-local" class="dsb-isian"
                                    wire:model.live="tayangMulai" value="{{ $tayangMulai }}">
                                <span class="pb-ket">Dikosongkan berarti langsung tampil begitu statusnya Terbit.</span>
                                @error('tayangMulai') <span class="pb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="dsb-medan mt-3">
                                <label class="dsb-label" for="pb-tayang-sampai">Berhenti tampil</label>
                                <input id="pb-tayang-sampai" type="datetime-local" class="dsb-isian"
                                    wire:model.live="tayangSampai" value="{{ $tayangSampai }}">
                                <span class="pb-ket">Dikosongkan berarti tampil terus, tanpa batas waktu.</span>
                                @error('tayangSampai') <span class="pb-galat">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-check mt-3">
                                {{-- @checked() WAJIB, bukan cuma wire:model.

                                     wire:model TIDAK menulis atribut checked saat
                                     halamannya pertama digambar: kotaknya tampil kosong
                                     meski datanya sudah true, dan admin membacanya sebagai
                                     "paket ini belum ikut promo" lalu mencentangnya —
                                     yang justru MEMATIKANNYA. --}}
                                <input class="form-check-input" type="checkbox" id="pb-rombongan"
                                    wire:model.live="promoRombongan" @checked($promoRombongan)>
                                <label class="form-check-label small" for="pb-rombongan">Ikut promo rombongan</label>
                                <span class="pb-ket d-block">
                                    Potongan harga menurut jumlah peserta, diatur di halaman
                                    <a href="{{ route('admin.orcha.promo') }}" wire:navigate>Promo Rombongan</a>.
                                    Matikan kalau untung paket ini sudah tipis.
                                </span>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="pb-berakhir"
                                    wire:model.live="berakhirOtomatis" @checked($berakhirOtomatis)>
                                <label class="form-check-label small" for="pb-berakhir">
                                    Tutup pendaftaran saat hari keberangkatan
                                </label>
                                <span class="pb-ket d-block">
                                    @if ($tanggalBerangkat)
                                        Berhenti tampil
                                        <strong>{{ \Carbon\Carbon::parse($tanggalBerangkat)->locale('id')->translatedFormat('j M Y') }}</strong>.
                                    @else
                                        Berhenti tampil pada tanggal berangkat.
                                    @endif
                                    Matikan kalau tanggalnya cuma contoh.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @include('livewire.pages.admin.orcha.paket-wisata.partials.keberangkatan')

            @include('livewire.pages.admin.orcha.paket-wisata.partials.harga-foto')

            @include('livewire.pages.admin.orcha.paket-wisata.partials.daftar-pilihan')

            @include('livewire.pages.admin.orcha.paket-wisata.partials.jadwal')

            {{-- ================== BILAH TINDAKAN ================== --}}
            <section class="dsb-bagian">
                <div class="dsb-rak">
                    <div class="dsb-kartu k-12">
                        <div class="dsb-kartu-isi">
                            <div class="pb-bilah">
                                <a href="{{ route('admin.orcha.paket') }}" wire:navigate
                                    class="dsb-tombol is-bahaya">
                                    <i class="bi bi-x-lg"></i><span>Batal</span>
                                </a>

                                <button type="submit" class="dsb-tombol is-utama"
                                    wire:loading.attr="disabled" wire:target="simpan">
                                    <span wire:loading.remove wire:target="simpan">
                                        <i class="bi bi-check-lg"></i>
                                        {{ $ubah ? 'Simpan Perubahan' : 'Tambah Paket' }}
                                    </span>

                                    {{-- Pemintal, bukan sekadar tulisan yang berganti.

                                         Menyimpan di sini menembak Orcha, jadi jedanya
                                         terasa — dan tombol yang hanya berganti kalimat
                                         masih terlihat diam. Yang menekannya cenderung
                                         menekan lagi, dan paket yang terkirim dua kali
                                         bukan perkara tampilan. --}}
                                    <span wire:loading wire:target="simpan">
                                        <span class="spinner-border spinner-border-sm me-2"
                                            role="status" aria-hidden="true"></span>Menyimpan ke Orcha…
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
