@section('title')
Paket Wisata Orcha || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon, patokan yang sama dengan layar
         Pesanan Toko dan Sewa Kendaraan. Kelas orcha-* hanya untuk yang memang
         khas Orcha — di layar ini tinggal lencana penayangan (lima keadaan yang
         tidak ada padanannya di lemon), angka untung, dan paginasi API. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlah = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = trim((string) $cari) !== '' || (string) $filterStatus !== '';

        $sedangTayang = collect($daftar)->where('status_tayang', 'tayang')->count();

        // Paket yang modalnya belum diisi. Inilah yang diam-diam merugikan:
        // harganya sudah tayang dan orang sudah bisa mendaftar, tetapi tidak
        // ada seorang pun yang tahu paket itu untung atau tidak. Angkanya
        // disebut di kepala halaman, bukan ditemukan dengan membaca kolom
        // harga satu per satu.
        $tanpaModal = collect($daftar)
            ->filter(fn ($b) => ($b['margin_per_orang'] ?? null) === null)
            ->count();
    @endphp

    <style>
        /* Gaya khusus layar Paket Wisata.

           Dasarnya bahasa rupa dasbor (partials/dasbor-gaya); di sini hanya
           yang tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
           .gitignore dan tidak ikut ter-deploy. */

        /* ===== Saringan ===== */
        .pw-saring { display: grid; gap: 12px; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        @media (max-width: 767.98px) {
            .pw-saring { grid-template-columns: minmax(0, 1fr); }
        }

        /* ===== Tabel ===== */
        .pw-kaki { padding-top: 0; }
        .pw-harga { font-weight: 800; color: var(--dsb-tinta, #1c1f26); }

        /* Harga asli dicoret, dan sengaja TIDAK memakai .dsb-tabel-samar:
           samar disembunyikan di layar lebar, sementara harga sebelum diskon
           justru paling berguna di sana — ia yang menjelaskan kenapa angka di
           atasnya semurah itu. */
        .pw-harga-asli {
            display: block; margin-top: 2px;
            font-size: .74rem; font-weight: 600; color: #94a3b8;
            text-decoration: line-through;
        }

        /* Lencana penayangan dibuat selebar kolomnya, bukan selebar isinya:
           sederet pil yang panjangnya berbeda-beda ("Tayang" vs "Terjadwal")
           terbaca sebagai daftar yang berantakan, padahal isinya satu kata. */
        .pw-tabel .orcha-lencana { min-width: 104px; justify-content: center; }

        /* ===== Keterangan kecil di bawah angka =====

           SELALU terlihat, dan sengaja BUKAN .dsb-tabel-samar.

           Samar disembunyikan di layar lebar — tempatnya memang untuk salinan
           kolom yang sudah tampil di sebelahnya. "mulai 3 Des", "pelunasan sd
           …", dan "7 pendaftar · min 4" tidak punya kolom lain, jadi
           menyamarkannya berarti ketiganya hilang sama sekali di desktop,
           padahal di sanalah layar ini dipakai. Sewa Kendaraan pernah
           kehilangan nomor WhatsApp-nya persis begitu. */
        .pw-ket {
            display: block; margin-top: 2px;
            font-size: .74rem; font-weight: 600; color: #94a3b8;
        }

        /* Yang DILARANG pecah hanya keterangan kursi: "3 pendaftar · min 4"
           yang patah jadi "3 pendaftar · min" + "4" terbaca sebagai dua
           keterangan, dan angka 4 yang berdiri sendiri di baris bawah sempat
           terbaca sebagai sisa kursi.

           Keterangan lain ("pelunasan sd 20 Nov 2026") dibiarkan turun baris:
           melarang semuanya membuat tabelnya melebihi lebar layar, dan kolom
           Aksi — tiga tombol yang paling sering dipakai — terdorong keluar
           sampai tidak terlihat sama sekali. */
        .pw-kursi .pw-ket { white-space: nowrap; }

        /* Angka untung ikut jadi keterangan atas harga di atasnya: ia perlu
           baris sendiri dan ukuran kecil, warnanya sudah diatur partials/gaya. */
        .pw-tabel .orcha-untung-nilai {
            display: block; margin-top: 3px; font-size: .74rem;
            /* Sama alasannya: "untung Rp" + "300.000/orang" di dua baris
               memisahkan angka dari satuannya. */
            white-space: nowrap;
        }

        /* Kolom Paket diberi ruang minimum, HANYA di layar lebar.

           Sesudah kolom Harga & Kursi dipaksa tidak memotong kalimatnya,
           kolom pertamalah yang menyusut: nama paketnya pecah tiga baris dan
           lencana kategori, durasi, "pilihan terbaik", serta diskonnya berdiri
           sendiri-sendiri ke bawah — terbaca sebagai daftar, bukan sebagai satu
           keterangan. Di ponsel aturannya tidak berlaku: di sana selnya memang
           bukan kolom lagi, dan min-width hanya akan membuat halamannya
           bisa digeser ke samping. */
        @media (min-width: 768px) {
            .pw-tabel th:first-child,
            .pw-tabel td:first-child { min-width: 248px; }
        }

        /* ===== Kursi =====
           Garis kemajuan, bukan dua angka yang harus dibandingkan di kepala. */
        .pw-kursi { display: inline-flex; flex-direction: column; gap: 2px; min-width: 112px; }
        .pw-kursi > .dsb-kemajuan { width: 100%; margin-top: 6px; }

        /* ===== HP =====
           TIDAK ada kolom yang disembunyikan lewat :nth-child.

           Nomor kolom adalah patokan yang diam-diam berubah arti: begitu satu
           kolom dilebur atau disisipkan, nomornya bergeser dan aturan yang
           dulu menyembunyikan "Jadwal" berpindah menyembunyikan "Aksi" — tanpa
           galat dan tanpa uji yang merah. Master Harga pernah kehilangan tombol
           ubah & hapus persis begitu.

           Jadi yang dipakai kelas kolomnya (.k-sedang), dan bahasa rupa dasbor
           sudah mengurusnya: tersembunyi di layar menengah, lalu kembali
           sebagai baris berlabel di ponsel. */
        @media (max-width: 767.98px) {
            .pw-tabel .dsb-tabel-samar { display: none !important; }

            /* Sel ponsel bawaannya satu baris flex tanpa wrap, dan isinya di
               sini kerap lebih dari satu benda: harga + harga asli + untung,
               atau kursi + garis kemajuannya. Tanpa wrap semuanya didorong
               keluar tepi kanan. Labelnya tetap di kiri lewat
               margin-right:auto, nilainya mengalir ke kanan dan turun baris
               bila tidak muat. */
            .pw-tabel tbody td { flex-wrap: wrap; justify-content: flex-end; }
            .pw-tabel tbody td[data-judul]::before { margin-right: auto; }
            .pw-tabel tbody td:first-child { justify-content: flex-start; }

            /* Keduanya keterangan atas angka di atasnya, bukan angka kedua
               yang sejajar — jadi selalu turun baris sendiri. */
            .pw-tabel .pw-harga-asli,
            .pw-tabel .pw-ket,
            .pw-tabel .orcha-untung-nilai { flex: 0 0 100%; text-align: right; }

            /* Nilainya duduk SEJAJAR labelnya, sama seperti Harga di atasnya.
               Dengan lebar penuh ia terdorong ke baris berikutnya, dan "KURSI"
               berdiri sendirian tanpa angka — satu-satunya baris di kartu ini
               yang berperilaku lain dari yang lain. */
            .pw-tabel .pw-kursi { align-items: flex-end; }
            .pw-tabel .pw-kursi > .dsb-kemajuan { width: 150px; }
            .pw-tabel .dsb-tabel-aksi { width: 100%; justify-content: flex-end; flex-wrap: wrap; }
        }
    </style>

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-salam">Paket Wisata</h1>
                <p class="dsb-hero-ket">
                    Paket yang tayang di website Orcha — open trip, private trip, dan study tour.
                </p>

                <div class="dsb-chip-deret mt-2">
                    <span class="dsb-chip" style="--c: #7c3aed">
                        <i class="bi bi-map-fill"></i> {{ $jumlah }} paket
                    </span>

                    @if ($sedangTayang)
                        <span class="dsb-chip" style="--c: #16a34a">
                            <i class="bi bi-broadcast"></i> {{ $sedangTayang }} sedang tayang
                        </span>
                    @endif

                    @if ($tanpaModal)
                        {{-- Disebut hanya saat ada. Cip "0 tanpa modal" yang
                             selalu berdiri membuat mata berhenti membacanya,
                             dan pada hari yang benar-benar ada masalahnya ia
                             tidak lagi menarik perhatian siapa pun. --}}
                        <span class="dsb-chip" style="--c: #d97706">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $tanpaModal }} modal belum diisi
                        </span>
                    @endif
                </div>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.paket.tambah') }}" wire:navigate
                    class="dsb-tombol is-utama" title="Tambah paket wisata baru">
                    <i class="bi bi-plus-lg"></i><span>Tambah Paket</span>
                </a>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="pw-saring">
                            @include('livewire.pages.admin.orcha.partials.cari', [
                                'petunjuk' => 'Cari nama paket…',
                                'rupa' => 'dsb',
                            ])

                            <div class="dsb-medan">
                                <select wire:model.live="filterStatus" class="dsb-isian"
                                    aria-label="Saring menurut kategori">
                                    <option value="" @selected((string) $this->filterStatus === '')>Semua kategori</option>
                                    @foreach ($pilihan as $kunci => $label)
                                        <option value="{{ $kunci }}" @selected((string) $this->filterStatus === (string) $kunci)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ================== DAFTAR ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-map"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Daftar Paket</h3>
                                <span class="dsb-kartu-sub">Satu baris = satu paket · kursi terisi ada di kolomnya sendiri</span>
                            </div>
                        </div>
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-map' }}"></i></span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada paket yang cocok' : 'Belum ada paket wisata' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ubah kata kunci atau kategorinya.'
                                    : 'Paket yang Anda tambahkan di sini akan tayang di website Orcha.' }}
                            </p>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel pw-tabel">
                                <thead>
                                    <tr>
                                        <th>Paket</th>
                                        <th>Penayangan</th>
                                        <th class="k-sedang">Jadwal</th>
                                        <th style="text-align: right;">Harga</th>
                                        <th class="k-sedang">Kursi</th>
                                        <th style="text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            /*
                                             | TIDAK ada tanda lebih-besar telanjang di blok ini:
                                             | perbandingannya sengaja dibalik jadi "0 kurang dari
                                             | kuota", bukan sebaliknya.
                                             |
                                             | Penyisip penanda morph Livewire memutuskan sebuah
                                             | direktif berada di dalam tag HTML dengan melihat apakah
                                             | ada tanda tutup tag sebelum tanda buka tag berikutnya.
                                             | Satu tanda lebih-besar di sini membuat perulangan di
                                             | atas kehilangan penanda PEMBUKANYA sementara
                                             | penutupnya tetap terpasang — penanda timpang, dan
                                             | setiap pembaruan Livewire akan merusak baris tabelnya
                                             | tanpa satu pun galat. Panah fungsi, panah objek, dan
                                             | lebih-besar-sama-dengan aman karena huruf sebelum
                                             | tandanya dikecualikan; spasi tidak.
                                             |
                                             | Komentar ini pun ditulis tanpa tanda itu dan tanpa
                                             | menyebut nama direktifnya berawalan at: keduanya ikut
                                             | terbaca pemindai, dan komentar yang MENJELASKAN
                                             | jebakan ini sempat menjadi jebakan itu sendiri.
                                             |
                                             | Dijaga PenandaMorphAdminTest, yang sejak sekarang ikut
                                             | memindai folder ini.
                                             */
                                            $statusTayang = $baris['status_tayang'] ?? 'tayang';
                                            $urlUbah = route('admin.orcha.paket.ubah', $baris['id']);

                                            $tgl = fn ($t, $f = 'd M Y') => $t
                                                ? \Carbon\Carbon::parse($t)->locale('id')->translatedFormat($f)
                                                : null;

                                            // Warna ikonnya mengikuti KEADAAN penayangannya: yang
                                            // dicari admin saat menyapu daftar adalah paket mana
                                            // yang sudah tidak terlihat orang lagi.
                                            $warnaIkon = match ($statusTayang) {
                                                'tayang' => '#16a34a',
                                                'terjadwal' => '#0284c7',
                                                'berakhir' => '#b45309',
                                                default => '#64748b',
                                            };

                                            $kuota = (int) ($baris['kuota'] ?? 0);
                                            $terpakai = (int) ($baris['kursi_terpakai'] ?? 0);
                                            $pendaftar = (int) ($baris['jumlah_pendaftar'] ?? 0);
                                            $minimal = (int) ($baris['minimal_peserta'] ?? 0);

                                            // Kuota kosong berarti BELUM DITETAPKAN, bukan nol —
                                            // paket tanpa kuota tidak pernah penuh. Membaginya
                                            // sebagai nol akan menampilkan garis penuh untuk
                                            // paket yang justru masih kosong melompong.
                                            $persen = 0 < $kuota ? min(100, (int) round($terpakai / $kuota * 100)) : null;
                                            $penuh = 0 < $kuota && $kuota <= $terpakai;

                                            // Penuh itu KABAR BAIK bagi admin, jadi hijau — bukan
                                            // merah seperti kuota yang terlampaui di layar lain.
                                            $warnaKursi = $penuh ? '#16a34a' : '#0284c7';

                                            $margin = $baris['margin_per_orang'] ?? null;
                                            $diskon = (int) ($baris['diskon_persen'] ?? 0);
                                        @endphp
                                        <tr wire:key="paket-{{ $baris['id'] }}"
                                            @class(['is-tanda' => $margin === null])
                                            @if ($margin === null) style="--c: #d97706" @endif>
                                            <td>
                                                <a wire:navigate href="{{ $urlUbah }}" class="dsb-tabel-utama">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warnaIkon }}"><i class="bi bi-map-fill"></i></span>
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            <span class="dsb-lencana is-abu">{{ $baris['kategori_label'] }}</span>
                                                            <span><i class="bi bi-clock"></i>{{ $baris['durasi'] }}</span>

                                                            @if ($baris['pilihan_terbaik'] ?? false)
                                                                <span class="dsb-lencana is-ungu">
                                                                    <i class="bi bi-star-fill"></i>pilihan terbaik
                                                                </span>
                                                            @endif

                                                            @if (0 < $diskon)
                                                                <span class="dsb-lencana is-merah">-{{ $diskon }}%</span>
                                                            @endif

                                                            {{-- Salinan kolom Jadwal, yang menghilang di layar sempit. --}}
                                                            <span class="dsb-tabel-samar">
                                                                <i class="bi bi-calendar-event"></i>{{ $baris['jadwal_label'] ?: 'jadwal belum diisi' }}
                                                            </span>
                                                        </span>
                                                    </span>
                                                </a>
                                            </td>

                                            <td data-judul="Penayangan">
                                                <span class="badge orcha-lencana-{{ $statusTayang }}">
                                                    {{ $baris['status_tayang_label'] ?? '—' }}
                                                </span>
                                                @if ($statusTayang === 'terjadwal' && $baris['tayang_mulai'])
                                                    <span class="pw-ket">
                                                        mulai {{ $tgl($baris['tayang_mulai'], 'd M Y, H:i') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="k-sedang" data-judul="Jadwal">
                                                <span class="dsb-tabel-angka">{{ $baris['jadwal_label'] ?: '—' }}</span>
                                                @if ($baris['batas_pelunasan'])
                                                    <span class="pw-ket">
                                                        pelunasan sd {{ $tgl($baris['batas_pelunasan']) }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td style="text-align: right;" data-judul="Harga">
                                                <span class="dsb-tabel-angka pw-harga">
                                                    Rp {{ number_format((float) $baris['harga'], 0, ',', '.') }}
                                                </span>

                                                @if ($baris['harga_asli'])
                                                    <span class="pw-harga-asli">
                                                        Rp {{ number_format((float) $baris['harga_asli'], 0, ',', '.') }}
                                                    </span>
                                                @endif

                                                {{-- Untung per peserta ikut di kolom harga, bukan kolom
                                                     sendiri: yang ditanya admin selalu "dijual segini,
                                                     untungnya berapa" — dua angka yang sebaiknya
                                                     terbaca dalam satu tarikan mata. --}}
                                                <span class="orcha-untung-nilai {{ $margin === null ? 'kosong' : ((int) $margin < 0 ? 'rugi' : '') }}">
                                                    @if ($margin === null)
                                                        modal belum diisi
                                                    @else
                                                        untung {{ $baris['margin_per_orang_teks'] }}/orang
                                                    @endif
                                                </span>
                                            </td>

                                            <td class="k-sedang" data-judul="Kursi">
                                                <span class="pw-kursi">
                                                    @if (0 < $kuota)
                                                        <span class="dsb-tabel-angka">{{ $terpakai }} / {{ $kuota }}</span>
                                                    @else
                                                        <span class="dsb-tabel-angka">{{ $terpakai }}</span>
                                                    @endif

                                                    <span class="pw-ket">
                                                        {{ $pendaftar }} pendaftar · min {{ $minimal }}
                                                    </span>

                                                    @if ($penuh)
                                                        <span class="dsb-lencana is-hijau">
                                                            <i class="bi bi-check-circle-fill"></i>penuh
                                                        </span>
                                                    @endif

                                                    @if ($persen !== null)
                                                        <span class="dsb-kemajuan" style="--c: {{ $warnaKursi }}"
                                                            role="img"
                                                            aria-label="{{ $terpakai }} dari {{ $kuota }} kursi terisi">
                                                            <span style="width: {{ $persen }}%"></span>
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>

                                            <td style="text-align: right;">
                                                <span class="dsb-tabel-aksi">
                                                    <a href="{{ $baris['tautan_publik'] }}" target="_blank" rel="noopener"
                                                        class="dsb-tabel-btn" style="--c: #0284c7"
                                                        title="Lihat halamannya di website Orcha">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>

                                                    <a wire:navigate href="{{ $urlUbah }}"
                                                        class="dsb-tabel-btn" style="--c: #7c3aed"
                                                        title="Ubah paket ini">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <button type="button" class="dsb-tabel-btn pcek-konfirmasi"
                                                        style="--c: var(--dsb-bahaya)"
                                                        data-action="hapus" data-arg="{{ $baris['id'] }}"
                                                        data-title="Hapus paket ini?"
                                                        data-text="{{ $baris['nama'] }} akan hilang dari website Orcha."
                                                        data-confirm="Ya, hapus" data-icon="warning"
                                                        title="Hapus paket ini">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="dsb-kartu-isi pw-kaki">
                            @include('livewire.pages.admin.orcha.partials.paginasi')
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
