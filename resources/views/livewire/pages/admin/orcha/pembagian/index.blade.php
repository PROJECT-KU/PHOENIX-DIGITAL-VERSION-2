@section('title')
Bagi Kursi & Kamar || lemon
@stop

<div>
    {{-- Partial gaya WAJIB disertakan, bukan diandaikan sudah ada.

         Layout templateindex tidak memuat bahasa rupa dsb-* sendiri; tiap layar
         yang memakainya menyertakannya. Tanpa tiga baris ini halaman tetap
         terbuka 200 dengan isi yang benar dan rupa yang hilang seluruhnya —
         galat yang tidak pernah muncul di log. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- <style> telanjang: layout templateindex tidak punya satu pun stack,
         jadi gaya yang didorong ke sana tidak pernah ikut tergambar. --}}
    <style>
        /* ===== Empat kartu angka berjajar =====
           Bahasa rupa dasbor hanya menyediakan .is-dua dan .is-tiga, dan
           .dsb-deret polos adalah satu kolom — empat kartu lalu menumpuk
           setinggi layar, sehingga angka yang harus dibandingkan satu sama lain
           ("8 dari 22 kursi") tidak pernah terlihat bersamaan. */
        .pbg-angka { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

        /* ===== Daftar peserta: kepingan yang bisa dipilih =====
           Dua ketukan, bukan seret-lepas: pilih orangnya, lalu ketuk kursi atau
           kamarnya. Seret-lepas tidak bisa dipakai di ponsel, dan pembagian ini
           justru sering dikerjakan sambil berdiri. */
        .pbg-orang-deret { display: flex; flex-wrap: wrap; gap: 8px; }

        .pbg-orang {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 12px; border-radius: 12px; cursor: pointer;
            background: #fff; border: 1px solid var(--dsb-tepi);
            font-size: .82rem; text-align: left; color: var(--dsb-tinta);
        }
        .pbg-orang-nama { font-weight: 600; }
        .pbg-orang-meta { font-size: .72rem; color: var(--dsb-redup); display: block; }

        /* Jenis kelamin diberi warna, bukan hanya huruf. Pembagian kamar
           seluruhnya bergantung medan ini, dan huruf "L"/"P" sebesar 11px
           tidak bisa dipindai mata pada daftar seratus nama. */
        .pbg-orang.is-laki { border-color: #bae6fd; background: #f0f9ff; }
        .pbg-orang.is-perempuan { border-color: #fbcfe8; background: #fdf2f8; }
        .pbg-orang.is-belum { border-color: #fde68a; background: #fffbeb; }
        .pbg-orang.is-terpilih {
            border-color: #7c3aed; background: #7c3aed; color: #fff;
            box-shadow: 0 8px 18px rgba(124, 58, 237, .26);
        }
        .pbg-orang.is-terpilih .pbg-orang-meta { color: rgba(255, 255, 255, .8); }

        /* Yang berubah tetapi belum tersimpan diberi titik, bukan warna lain:
           warnanya sudah dipakai jenis kelamin, dan dua arti pada satu warna
           membuat keduanya berhenti terbaca. */
        .pbg-belum-simpan { width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; flex-shrink: 0; }

        /* ===== Petak kursi ===== */
        .pbg-kendaraan { margin-bottom: 20px; }
        .pbg-kendaraan:last-child { margin-bottom: 0; }
        .pbg-kendaraan-kepala {
            display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 11px;
        }
        .pbg-kendaraan-nama { font-weight: 700; font-size: .92rem; color: var(--dsb-tinta); }

        /* SATU kisi untuk seluruh kendaraan, bukan satu kisi per baris.

           Kisi per baris menentukan lebar kolomnya sendiri-sendiri dari isinya,
           dan bangku belakang — yang berisi dua kursi lalu tiga lorong — jadi
           punya lebar kolom yang berbeda dari baris di atasnya. Kursi 22
           bergeser beberapa piksel dari kursi 18 yang seharusnya tepat di
           atasnya, dan petak yang tidak lurus terbaca sebagai petak yang salah.

           Lebar kolomnya ditulis di atribut style tiap kendaraan, karena posisi
           lorongnya berbeda antara tata 2-2 dan 2-3. */
        .pbg-petak { display: grid; gap: 6px; justify-content: start; }

        .pbg-kursi {
            width: 54px; height: 44px; border-radius: 9px; cursor: pointer;
            background: #fff; border: 1px dashed #cbd5e1; color: #94a3b8;
            font-size: .7rem; line-height: 1.15; padding: 3px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .pbg-kursi-nomor { font-weight: 700; font-size: .66rem; }
        .pbg-kursi-nama {
            display: block; max-width: 100%; font-size: .62rem; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .pbg-kursi.is-isi { border-style: solid; }
        .pbg-kursi.is-laki { border-color: #7dd3fc; background: #e0f2fe; color: #075985; }
        .pbg-kursi.is-perempuan { border-color: #f9a8d4; background: #fce7f3; color: #9d174d; }
        .pbg-kursi.is-belum { border-color: #fcd34d; background: #fef3c7; color: #92400e; }
        .pbg-kursi.is-bentrok { border-color: #f87171; background: #fee2e2; color: #991b1b; }
        /* Lorong: ruang kosong, BUKAN kursi kosong. Dua hal yang tidak boleh
           terbaca sama — yang satu bisa diisi orang, yang lain tidak. */
        .pbg-lorong { width: 22px; }

        @media (hover: hover) and (pointer: fine) {
            .pbg-kursi:hover { border-color: #7c3aed; color: #5b21b6; }
            .pbg-orang:hover { border-color: #a78bfa; }
            .pbg-orang.is-terpilih:hover { border-color: #fff; }
        }

        /* ===== Papan kamar ===== */
        .pbg-kamar-rak {
            display: grid; gap: 11px;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        }
        .pbg-kamar {
            padding: 11px 13px; border-radius: 13px; cursor: pointer; text-align: left;
            background: #fff; border: 1px solid var(--dsb-tepi);
        }
        .pbg-kamar-kepala {
            display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
            font-size: .84rem; font-weight: 700; color: var(--dsb-tinta);
        }
        .pbg-kamar-sisa { font-size: .7rem; font-weight: 600; color: var(--dsb-redup); }
        .pbg-kamar-isi { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; }
        .pbg-kamar-orang {
            display: flex; align-items: center; justify-content: space-between; gap: 6px;
            font-size: .76rem; color: var(--dsb-tinta);
        }
        .pbg-kamar-kosong { font-size: .74rem; font-style: italic; color: #a3aab5; margin-top: 8px; }
        .pbg-kamar.is-laki { border-color: #bae6fd; background: #f0f9ff; }
        .pbg-kamar.is-perempuan { border-color: #fbcfe8; background: #fdf2f8; }
        .pbg-kamar.is-campur { border-color: #fdba74; background: #fff7ed; }
        .pbg-kamar.is-penuh { opacity: .95; }

        .pbg-keluarkan {
            border: 0; background: none; padding: 0 2px; cursor: pointer;
            color: #cbd5e1; font-size: .72rem; line-height: 1;
        }
        @media (hover: hover) and (pointer: fine) {
            .pbg-keluarkan:hover { color: #dc2626; }
            .pbg-kamar:hover { border-color: #a78bfa; }
        }

        /* ===== Bilah belum tersimpan ===== */
        .pbg-simpan-bilah {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            padding: 12px 15px; border-radius: 13px;
            background: #fffbeb; border: 1px solid #fde68a; color: #78350f;
        }
        .pbg-simpan-teks { flex: 1 1 240px; min-width: 0; font-size: .84rem; line-height: 1.5; }
        .pbg-simpan-aksi { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; }

        /* ===== Peringatan ===== */
        .pbg-awas { display: flex; flex-direction: column; gap: 8px; }
        .pbg-awas-baris {
            display: flex; align-items: flex-start; gap: 9px;
            padding: 10px 12px; border-radius: 11px; font-size: .82rem; line-height: 1.5;
        }
        .pbg-awas-baris.is-merah { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .pbg-awas-baris.is-kuning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ===== Borang kendaraan & kamar ===== */
        .pbg-fasilitas-baris {
            display: grid; gap: 9px; align-items: end; margin-bottom: 9px;
            grid-template-columns: minmax(0, 2fr) minmax(0, 2fr) minmax(0, 1fr) auto;
        }
        .pbg-kamar-baris {
            display: grid; gap: 9px; align-items: end; margin-bottom: 9px;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) auto;
        }
        @media (max-width: 575.98px) {
            .pbg-fasilitas-baris, .pbg-kamar-baris { grid-template-columns: 1fr 1fr; }
        }
        .pbg-deret { display: grid; gap: 9px; align-items: end; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); }
    </style>

    <div class="dsb pbg-layar">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Bagi Kursi &amp; Kamar</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-suitcase-lg me-1"></i>{{ $keberangkatan['paket']['nama'] ?? 'Paket' }}
                    </span>
                    <span class="d-block">
                        <i class="bi bi-calendar-event me-1"></i>Rombongan
                        {{ $keberangkatan['rentang_tanggal'] ?? '—' }}
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran', array_filter([
                        'rombongan' => $keberangkatan['id'] ?? null,
                    ])) }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Pendaftaran</span>
                </a>

                <button type="button" class="dsb-tombol {{ $this->adaUbahan() ? 'is-utama' : 'is-lembut is-mati' }}"
                    wire:click="simpan" @disabled(! $this->adaUbahan())>
                    <i class="bi bi-check2-circle"></i><span>Simpan Pembagian</span>
                </button>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ===== BILAH BELUM TERSIMPAN =====

             Perubahan ditahan di layar sampai Simpan ditekan — menyimpan setiap
             ketukan berarti satu panggilan ke Orcha per orang untuk pekerjaan
             yang menyentuh seratus orang. Yang tidak boleh adalah admin menutup
             tab dan kehilangan satu jam kerja tanpa pernah tahu ia belum
             menyimpan, jadi keadaannya dinyatakan sejelas mungkin. --}}
        @if ($this->adaUbahan())
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="pbg-simpan-bilah">
                            <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            <div class="pbg-simpan-teks">
                                <strong>{{ count($ubahan) }} orang dipindahkan tetapi BELUM tersimpan.</strong>
                                Kalau halaman ini ditutup sekarang, pembagiannya kembali seperti semula.
                            </div>
                            <div class="pbg-simpan-aksi">
                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #64748b"
                                    wire:click="batalkanUbahan">
                                    <i class="bi bi-arrow-counterclockwise"></i><span>Buang perubahan</span>
                                </button>
                                <button type="button" class="dsb-tombol is-utama" wire:click="simpan">
                                    <i class="bi bi-check2-circle"></i><span>Simpan sekarang</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== ANGKA RINGKAS =====

             Yang paling penting di antaranya "belum diisi jenis kelamin": ia
             satu-satunya yang MENGHALANGI pembagian kamar. Tanpa angkanya di
             muka, admin baru tahu setelah menekan bagi otomatis dan mendapat
             hasil separuh. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi">
                    <div class="dsb-deret pbg-angka">
                        <div class="dsb-stat">
                            <span class="dsb-stat-label">Peserta</span>
                            <span class="dsb-stat-nilai">{{ $ringkasan['total_peserta'] }}</span>
                            <span class="dsb-stat-ket">dari semua pemesanan rombongan ini</span>
                        </div>

                        <div class="dsb-stat">
                            <span class="dsb-stat-label">Laki-laki / Perempuan</span>
                            <span class="dsb-stat-nilai">{{ $ringkasan['laki'] }} / {{ $ringkasan['perempuan'] }}</span>
                            <span class="dsb-stat-ket">
                                @if ($ringkasan['belum_jenis_kelamin'] > 0)
                                    <strong>{{ $ringkasan['belum_jenis_kelamin'] }} belum diisi</strong> —
                                    kamarnya belum bisa dibagi otomatis
                                @else
                                    semuanya sudah diisi
                                @endif
                            </span>
                        </div>

                        <div class="dsb-stat">
                            <span class="dsb-stat-label">Sudah dapat kursi</span>
                            <span class="dsb-stat-nilai">{{ $ringkasan['sudah_kursi'] }}</span>
                            <span class="dsb-stat-ket">
                                {{ $ringkasan['jumlah_kursi_tersedia'] }} kursi disiapkan
                            </span>
                        </div>

                        <div class="dsb-stat">
                            <span class="dsb-stat-label">Sudah dapat kamar</span>
                            <span class="dsb-stat-nilai">{{ $ringkasan['sudah_kamar'] }}</span>
                            <span class="dsb-stat-ket">
                                {{ $ringkasan['daya_tampung_kamar'] }} tempat tidur disiapkan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== PERINGATAN ===== --}}
        @if ($peringatan !== [])
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-exclamation-octagon-fill"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Perlu Diperiksa</h2>
                                <span class="dsb-kartu-sub">Dihitung dari yang sedang di layar, termasuk yang belum disimpan</span>
                            </span>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">
                        <div class="pbg-awas">
                            @foreach ($peringatan as $satu)
                                <div class="pbg-awas-baris {{ $satu['jenis'] === 'kursi_kembar' ? 'is-merah' : 'is-kuning' }}">
                                    <i class="bi {{ $satu['jenis'] === 'kursi_kembar' ? 'bi-x-octagon-fill' : 'bi-exclamation-triangle-fill' }}"></i>
                                    <span>{{ $satu['pesan'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== BAGI OTOMATIS ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-magic"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Bagi Otomatis</h2>
                            <span class="dsb-kartu-sub">
                                Kamar dibagi menurut jenis kelamin — perempuan tidak pernah disatukan
                                dengan laki-laki. Kursi diisi berurutan supaya satu pemesanan duduk berdekatan.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <button type="button" class="dsb-tombol is-utama" wire:click="bagiOtomatis('keduanya')">
                            <i class="bi bi-magic"></i><span>Bagi Kursi &amp; Kamar</span>
                        </button>
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                            wire:click="bagiOtomatis('kursi')">
                            <i class="bi bi-bus-front"></i><span>Kursi saja</span>
                        </button>
                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                            wire:click="bagiOtomatis('kamar')">
                            <i class="bi bi-door-closed"></i><span>Kamar saja</span>
                        </button>

                        {{-- Bawaannya TIDAK menimpa. Admin yang sudah memindahkan
                             tiga orang atas permintaan khusus lalu menekan bagi
                             otomatis untuk sisanya tidak boleh kehilangan ketiga
                             perpindahan itu — dan kehilangannya tidak kelihatan,
                             karena hasilnya tetap berupa pembagian yang rapi. --}}
                        <label class="d-inline-flex align-items-center gap-2 ms-auto"
                            style="font-size:.82rem;color:var(--dsb-redup);cursor:pointer">
                            <input type="checkbox" class="form-check-input m-0"
                                wire:model="timpa" @checked($timpa)>
                            <span>Timpa juga yang sudah saya atur sendiri</span>
                        </label>
                    </div>

                    @if (($laporan['catatan'] ?? []) !== [] || ($laporan['sisa'] ?? []) !== [])
                        <div class="pbg-awas mt-3">
                            @foreach ($laporan['catatan'] ?? [] as $catatan)
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>{{ $catatan }}</span>
                                </div>
                            @endforeach

                            @if (($laporan['sisa'] ?? []) !== [])
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-person-exclamation"></i>
                                    <span>
                                        Belum kebagian:
                                        {{ collect($laporan['sisa'])->pluck('nama')->implode(', ') }}.
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== KENDARAAN & KAMAR YANG DISIAPKAN =====

             Dipisahkan dari penempatan orangnya. Armada dan blok kamar
             ditentukan sekali saat dipesan ke vendor; penempatan orangnya
             berubah sampai hari terakhir. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0f766e"><i class="bi bi-sliders"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Kendaraan &amp; Kamar yang Disiapkan</h2>
                            <span class="dsb-kartu-sub">
                                {{ count($kendaraan) }} kendaraan
                                ({{ collect($kendaraan)->sum('jumlah_kursi') }} kursi) &middot;
                                {{ count($kamar) }} kamar
                                ({{ collect($kamar)->sum('kapasitas') }} tempat tidur)
                            </span>
                        </span>
                    </div>

                    <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0f766e"
                        wire:click="$toggle('bukaFasilitas')">
                        <i class="bi {{ $bukaFasilitas ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i>
                        <span>{{ $bukaFasilitas ? 'Tutup' : 'Atur' }}</span>
                    </button>
                </div>

                @if ($bukaFasilitas)
                    <div class="dsb-kartu-isi">
                        <h3 class="dsb-sub">Kendaraan</h3>

                        @forelse ($kendaraan as $urutan => $satu)
                            <div class="pbg-fasilitas-baris" wire:key="kendaraan-{{ $urutan }}">
                                <div>
                                    <label class="dsb-label">Nama kendaraan</label>
                                    <input type="text" class="dsb-isian" maxlength="60"
                                        wire:model="kendaraan.{{ $urutan }}.nama"
                                        value="{{ $satu['nama'] ?? '' }}" placeholder="Bus 1">
                                </div>
                                <div>
                                    <label class="dsb-label">Tata tempat duduk</label>
                                    <select class="dsb-isian" wire:model="kendaraan.{{ $urutan }}.tata">
                                        @foreach ($pilihanTata as $kunci => $label)
                                            <option value="{{ $kunci }}"
                                                @selected(($satu['tata'] ?? '2-2') === $kunci)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="dsb-label">Jumlah kursi</label>
                                    <input type="number" min="1" max="80" class="dsb-isian"
                                        wire:model="kendaraan.{{ $urutan }}.jumlah_kursi"
                                        value="{{ $satu['jumlah_kursi'] ?? '' }}">
                                </div>
                                <div>
                                    <button type="button" class="orcha-hapus-baris"
                                        wire:click="hapusKendaraan({{ $urutan }})"
                                        title="Hapus kendaraan ini dari daftar">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="orcha-kosong">Belum ada kendaraan. Tambahkan satu supaya petak kursinya tergambar.</p>
                        @endforelse

                        <button type="button" class="dsb-tombol is-lembut" style="--ikon: #0284c7"
                            wire:click="tambahKendaraan">
                            <i class="bi bi-plus-lg"></i><span>Tambah kendaraan</span>
                        </button>

                        <hr class="dsb-pisah">

                        <h3 class="dsb-sub">Kamar</h3>

                        {{-- Deret bernomor sekaligus.

                             Hotel memberi blok kamar berurutan ("201 sampai 215"),
                             dan mengetiknya satu per satu lima belas kali adalah
                             pekerjaan yang paling sering membuat fitur seperti ini
                             tidak dipakai sama sekali. --}}
                        <div class="pbg-deret mb-3">
                            <div>
                                <label class="dsb-label">Nomor awal</label>
                                <input type="text" class="dsb-isian" maxlength="20"
                                    wire:model="deretAwal" placeholder="201">
                            </div>
                            <div>
                                <label class="dsb-label">Berapa kamar</label>
                                <input type="number" min="1" max="60" class="dsb-isian" wire:model="deretJumlah">
                            </div>
                            <div>
                                <label class="dsb-label">Isi per kamar</label>
                                <input type="number" min="1" max="12" class="dsb-isian" wire:model="deretKapasitas">
                            </div>
                            <div>
                                <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                                    wire:click="tambahDeretKamar">
                                    <i class="bi bi-123"></i><span>Buat deretnya</span>
                                </button>
                            </div>
                        </div>

                        @forelse ($kamar as $urutan => $satu)
                            <div class="pbg-kamar-baris" wire:key="kamar-{{ $urutan }}">
                                <div>
                                    <label class="dsb-label">Nomor / nama kamar</label>
                                    <input type="text" class="dsb-isian" maxlength="60"
                                        wire:model="kamar.{{ $urutan }}.nama"
                                        value="{{ $satu['nama'] ?? '' }}" placeholder="201">
                                </div>
                                <div>
                                    <label class="dsb-label">Isi per kamar</label>
                                    <input type="number" min="1" max="12" class="dsb-isian"
                                        wire:model="kamar.{{ $urutan }}.kapasitas"
                                        value="{{ $satu['kapasitas'] ?? '' }}">
                                </div>
                                <div>
                                    <button type="button" class="orcha-hapus-baris"
                                        wire:click="hapusKamar({{ $urutan }})"
                                        title="Hapus kamar ini dari daftar">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="orcha-kosong">Belum ada kamar. Pakai "Buat deretnya" kalau hotel memberi blok bernomor.</p>
                        @endforelse

                        <div class="d-flex flex-wrap gap-2 justify-content-between mt-2">
                            <button type="button" class="dsb-tombol is-lembut" style="--ikon: #db2777"
                                wire:click="tambahKamar">
                                <i class="bi bi-plus-lg"></i><span>Tambah satu kamar</span>
                            </button>

                            <button type="button" class="dsb-tombol is-utama" wire:click="simpanFasilitas">
                                <i class="bi bi-check2"></i><span>Simpan Kendaraan &amp; Kamar</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== PESERTA =====

             Semua peserta ada di daftar ini, yang sudah ditempatkan maupun
             belum — bukan hanya yang belum. Memindahkan orang yang sudah
             ditempatkan adalah separuh pekerjaannya ("tukar kursi Budi dan
             Agus"), dan daftar yang menyembunyikannya memaksa admin mencarinya
             di dalam petak. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #16a34a"><i class="bi bi-people-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Peserta Rombongan Ini</h2>
                            <span class="dsb-kartu-sub">
                                Ketuk satu nama, lalu ketuk kursi atau kamarnya di bawah
                            </span>
                        </span>
                    </div>

                    @if ($terpilih !== '')
                        <span class="dsb-lencana is-ungu">
                            <i class="bi bi-hand-index-thumb"></i>
                            {{ collect($peserta)->firstWhere('kunci', $terpilih)['nama'] ?? 'Terpilih' }}
                            sedang dipegang
                        </span>
                    @endif
                </div>

                <div class="dsb-kartu-isi">
                    @if ($peserta === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-person-dash"></i></span>
                            <p class="dsb-kosong-judul">Belum ada peserta di rombongan ini</p>
                            <p class="dsb-kosong-ket">
                                Pendaftarannya mungkin belum dimasukkan, atau nama pesertanya belum didata.
                                Buka daftar pendaftaran lalu lengkapi namanya dulu.
                            </p>
                        </div>
                    @else
                        <div class="pbg-orang-deret">
                            @foreach ($peserta as $satu)
                                @php
                                    $kelamin = $satu['jenis_kelamin'] ?? null;
                                    $rupa = $kelamin === 'Laki-laki' ? 'is-laki'
                                        : ($kelamin === 'Perempuan' ? 'is-perempuan' : 'is-belum');
                                    $tempat = collect([
                                        filled($satu['kursi'] ?? null)
                                            ? trim(($satu['bus'] ?? '').' kursi '.$satu['kursi'])
                                            : null,
                                        filled($satu['kamar'] ?? null) ? 'kamar '.$satu['kamar'] : null,
                                    ])->filter()->implode(' · ');
                                @endphp

                                <button type="button" wire:key="orang-{{ $satu['kunci'] }}"
                                    class="pbg-orang {{ $terpilih === $satu['kunci'] ? 'is-terpilih' : $rupa }}"
                                    wire:click="pilih(@js($satu['kunci']))"
                                    title="{{ $satu['pemesan'] }} · {{ $satu['kode'] }}">
                                    @if ($satu['berubah'])
                                        <span class="pbg-belum-simpan" title="Belum tersimpan"></span>
                                    @endif
                                    <span>
                                        <span class="pbg-orang-nama">{{ $satu['nama'] }}</span>
                                        <span class="pbg-orang-meta">
                                            {{ $kelamin ?: 'L/P belum diisi' }}{{ $tempat !== '' ? ' · '.$tempat : '' }}
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>

                        {{-- Melengkapi jenis kelamin DARI SINI.

                             Pembagian kamar yang berhenti karena tiga orang belum
                             diisi memaksa admin berpindah ke tiga pendaftaran
                             berbeda lalu kembali — dan yang terjadi berikutnya
                             bukan ia melengkapi data, melainkan ia membagi
                             kamarnya dengan tangan. --}}
                        @php
                            $belumKelamin = collect($peserta)->filter(
                                fn ($satu) => ! in_array($satu['jenis_kelamin'] ?? null, ['Laki-laki', 'Perempuan'], true)
                            );
                        @endphp

                        @if ($belumKelamin->isNotEmpty())
                            <div class="pbg-awas mt-3">
                                <div class="pbg-awas-baris is-kuning">
                                    <i class="bi bi-gender-ambiguous"></i>
                                    <span>
                                        <strong>{{ $belumKelamin->count() }} peserta belum diisi jenis kelaminnya.</strong>
                                        Pembagian kamar otomatis tidak menebaknya dari nama — nama Indonesia
                                        sering dipakai keduanya, dan tebakan yang salah berarti satu orang
                                        tidur di kamar yang salah. Isi di bawah ini:
                                    </span>
                                </div>
                            </div>

                            <div class="pbg-orang-deret mt-2">
                                @foreach ($belumKelamin as $satu)
                                    <span class="pbg-orang is-belum" wire:key="kelamin-{{ $satu['kunci'] }}">
                                        <span>
                                            <span class="pbg-orang-nama">{{ $satu['nama'] }}</span>
                                            <span class="pbg-orang-meta">{{ $satu['kode'] }}</span>
                                        </span>
                                        <span class="d-inline-flex gap-1">
                                            <button type="button" class="dsb-tabel-btn"
                                                wire:click="setKelamin(@js($satu['kunci']), 'Laki-laki')"
                                                title="Laki-laki">L</button>
                                            <button type="button" class="dsb-tabel-btn"
                                                wire:click="setKelamin(@js($satu['kunci']), 'Perempuan')"
                                                title="Perempuan">P</button>
                                        </span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== PETAK KURSI ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-bus-front"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Tempat Duduk</h2>
                            <span class="dsb-kartu-sub">
                                Depan di atas, lorong di tengah. Ketuk kursi berpenghuni
                                untuk menukar dengan yang sedang dipegang.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($petak === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-bus-front"></i></span>
                            <p class="dsb-kosong-judul">Belum ada kendaraan yang didaftarkan</p>
                            <p class="dsb-kosong-ket">
                                Buka <strong>Kendaraan &amp; Kamar yang Disiapkan</strong> di atas,
                                tambahkan busnya, lalu petak kursinya tergambar di sini.
                            </p>
                        </div>
                    @else
                        @php
                            // Penghuni dipetakan sekali untuk SELURUH petak: mencari
                            // di dalam gelung kursi berarti seratus pencarian untuk
                            // satu bus empat puluh empat kursi.
                            $penghuniKursi = [];

                            foreach ($peserta as $satu) {
                                if (blank($satu['kursi'] ?? null)) {
                                    continue;
                                }

                                $kunciKursi = trim((string) ($satu['bus'] ?? '')).'|'.trim((string) $satu['kursi']);
                                $penghuniKursi[$kunciKursi][] = $satu;
                            }
                        @endphp

                        @foreach ($petak as $kendaraanPetak)
                            <div class="pbg-kendaraan" wire:key="petak-{{ $loop->index }}">
                                <div class="pbg-kendaraan-kepala">
                                    <span class="pbg-kendaraan-nama">{{ $kendaraanPetak['nama'] }}</span>
                                    <span class="dsb-lencana is-abu">
                                        {{ $kendaraanPetak['jumlah_kursi'] }} kursi · tata {{ $kendaraanPetak['tata'] }}
                                    </span>
                                    @php
                                        $terisiBus = collect($peserta)->filter(
                                            fn ($satu) => (string) ($satu['bus'] ?? '') === $kendaraanPetak['nama']
                                                && filled($satu['kursi'] ?? null)
                                        )->count();
                                    @endphp
                                    <span class="dsb-lencana {{ $terisiBus === 0 ? 'is-abu' : 'is-hijau' }}">
                                        {{ $terisiBus }} terisi
                                    </span>
                                </div>

                                @php
                                    /* Posisi lorong dibaca dari baris PERTAMA, yang
                                       selalu baris penuh — baris terakhir bisa berupa
                                       bangku belakang yang seluruh sisanya null. */
                                    $posisiLorong = array_search(null, $kendaraanPetak['baris'][0] ?? [], true);

                                    $kolomPetak = collect(range(0, max(0, ($kendaraanPetak['lebar'] ?? 5) - 1)))
                                        ->map(fn ($i) => $i === $posisiLorong ? '22px' : '54px')
                                        ->implode(' ');
                                @endphp

                                <div class="pbg-petak" style="grid-template-columns: {{ $kolomPetak }}">
                                    @foreach ($kendaraanPetak['baris'] as $baris)
                                            @foreach ($baris as $sel)
                                                @if ($sel === null)
                                                    <span class="pbg-lorong"></span>
                                                @else
                                                    @php
                                                        $isi = $penghuniKursi[$kendaraanPetak['nama'].'|'.$sel] ?? [];
                                                        $orang = $isi[0] ?? null;
                                                        $bentrok = count($isi) > 1;
                                                        $kelaminKursi = $orang['jenis_kelamin'] ?? null;
                                                        $rupaKursi = $bentrok ? 'is-bentrok'
                                                            : ($orang === null ? ''
                                                                : ($kelaminKursi === 'Laki-laki' ? 'is-isi is-laki'
                                                                    : ($kelaminKursi === 'Perempuan' ? 'is-isi is-perempuan' : 'is-isi is-belum')));
                                                    @endphp

                                                    <button type="button"
                                                        class="pbg-kursi {{ $rupaKursi }}"
                                                        wire:click="tempatkanKursi(@js($kendaraanPetak['nama']), @js($sel))"
                                                        title="{{ $orang
                                                            ? $orang['nama'].' — '.$orang['pemesan'].' ('.$orang['kode'].')'
                                                            : 'Kursi '.$sel.' masih kosong' }}">
                                                        <span class="pbg-kursi-nomor">{{ $sel }}</span>
                                                        @if ($orang)
                                                            <span class="pbg-kursi-nama">{{ $orang['nama'] }}</span>
                                                        @endif
                                                    </button>
                                                @endif
                                            @endforeach
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== PAPAN KAMAR ===== --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #db2777"><i class="bi bi-door-closed-fill"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Kamar</h2>
                            <span class="dsb-kartu-sub">
                                Biru laki-laki, merah muda perempuan, oranye tercampur.
                                Kamar tercampur tidak dilarang — pasangan suami-istri memang satu
                                kamar — hanya ditandai supaya yang tidak disengaja tidak lolos.
                            </span>
                        </span>
                    </div>
                </div>

                <div class="dsb-kartu-isi">
                    @if ($kamar === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-door-closed"></i></span>
                            <p class="dsb-kosong-judul">Belum ada kamar yang didaftarkan</p>
                            <p class="dsb-kosong-ket">
                                Buka <strong>Kendaraan &amp; Kamar yang Disiapkan</strong> di atas.
                                Kalau hotel memberi blok bernomor, pakai "Buat deretnya".
                            </p>
                        </div>
                    @else
                        <div class="pbg-kamar-rak">
                            @foreach ($kamar as $satuKamar)
                                @php
                                    $namaKamar = (string) ($satuKamar['nama'] ?? '');
                                    $kapasitas = (int) ($satuKamar['kapasitas'] ?? 0);
                                    $penghuni = collect($peserta)->filter(
                                        fn ($satu) => (string) ($satu['kamar'] ?? '') === $namaKamar
                                    )->values();
                                    $kelaminKamar = $penghuni->pluck('jenis_kelamin')->filter()->unique()->values();
                                    $rupaKamar = $penghuni->isEmpty() ? ''
                                        : ($kelaminKamar->count() > 1 ? 'is-campur'
                                            : ($kelaminKamar->first() === 'Laki-laki' ? 'is-laki'
                                                : ($kelaminKamar->first() === 'Perempuan' ? 'is-perempuan' : '')));
                                    $sisa = max(0, $kapasitas - $penghuni->count());
                                @endphp

                                <div class="pbg-kamar {{ $rupaKamar }} {{ $sisa === 0 ? 'is-penuh' : '' }}"
                                    wire:key="papan-kamar-{{ $namaKamar }}"
                                    wire:click="tempatkanKamar(@js($namaKamar))"
                                    title="{{ $sisa === 0
                                        ? 'Kamar '.$namaKamar.' sudah penuh'
                                        : 'Ketuk untuk menaruh yang sedang dipegang di kamar '.$namaKamar }}">
                                    <div class="pbg-kamar-kepala">
                                        <span>{{ $namaKamar }}</span>
                                        <span class="pbg-kamar-sisa">
                                            {{ $penghuni->count() }}/{{ $kapasitas }}
                                        </span>
                                    </div>

                                    @if ($penghuni->isEmpty())
                                        <div class="pbg-kamar-kosong">masih kosong</div>
                                    @else
                                        <div class="pbg-kamar-isi">
                                            @foreach ($penghuni as $orangKamar)
                                                <div class="pbg-kamar-orang">
                                                    <span>
                                                        @if ($orangKamar['berubah'])
                                                            <span class="pbg-belum-simpan d-inline-block me-1"></span>
                                                        @endif
                                                        {{ $orangKamar['nama'] }}
                                                    </span>
                                                    {{-- stopPropagation: tanpa itu, mengeluarkan
                                                         seseorang ikut terbaca sebagai ketukan pada
                                                         kamarnya — dan orang yang sedang dipegang
                                                         langsung masuk menggantikannya. --}}
                                                    <button type="button" class="pbg-keluarkan"
                                                        wire:click.stop="kosongkanKamar(@js($orangKamar['kunci']))"
                                                        title="Keluarkan {{ $orangKamar['nama'] }} dari kamar ini">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
