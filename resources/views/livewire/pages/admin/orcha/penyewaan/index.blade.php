@section('title')
Sewa Kendaraan Masuk || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon, patokan yang sama dengan layar
         Pesanan Toko. Kelas orcha-* hanya untuk yang memang khas Orcha —
         di layar ini tinggal paginasi API dan pil statusnya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    {{-- Kartu tab berangka, bahasa rupa yang sama dengan Pesanan Toko dan
         Pendaftaran Trip. Tiga daftar yang dikerjakan orang yang sama tidak
         perlu dipelajari tiga kali. --}}
    @include('livewire.pages.admin.partials.kartu-tab-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlah = (int) ($meta['total'] ?? count($daftar));

        /* Dibaca dari komponennya, bukan dihitung ulang di sini: layar ini
           punya tiga saringan (cari, status, belum kembali) dan salinan yang
           ketinggalan satu membuat tombol "tampilkan semua lagi" hilang persis
           saat ia paling dibutuhkan. */
        $adaSaringan = $this->adaSaringan();

        $warnaTab = $tabs[$tabAktif][2] ?? '#7c3aed';

        // Unit yang seharusnya sudah kembali tetapi belum. Inilah yang dicek
        // admin tiap pagi, jadi angkanya disebut di kepala halaman — bukan
        // ditemukan dengan membaca sepuluh baris satu per satu.
        $telat = collect($daftar)->filter(fn ($b) => ! empty($b['terlambat']) && empty($b['dikembalikan_pada']))->count();
    @endphp

    <style>
        /* Gaya khusus layar Sewa Kendaraan.

           Dasarnya bahasa rupa dasbor (partials/dasbor-gaya); di sini hanya
           yang tidak ada di sana. Inline, bukan lewat Vite: public/build masuk
           .gitignore dan tidak ikut ter-deploy. */

        /* ===== Saringan ===== */
        /* Pencarian melebar, tombol bersihkan di ujung. Statusnya sudah pindah
           ke tab, jadi bilah ini tinggal satu medan — dan medan tunggal yang
           tetap dibagi dua kolom menyisakan separuh kartu kosong. */
        .sw-saring { display: grid; gap: 12px; align-items: end; grid-template-columns: minmax(0, 1fr) auto; }
        .sw-medan-aksi { display: flex; align-items: flex-end; }
        .sw-medan-aksi:empty { display: none; }
        @media (max-width: 575.98px) {
            .sw-saring { grid-template-columns: minmax(0, 1fr); }
        }

        /* Kolom kendaraan diberi lebar terendah.

           Tanpanya "Avanza 2022 · AB 1234 CD" terbungkus jadi tiga baris
           sementara kolom di sebelahnya menyisakan ruang kosong — nama unit
           dan nomor polisinya justru yang dicari mata saat menyapu daftar. */
        .sw-tabel th.k-lebar, .sw-tabel td.k-lebar { min-width: 165px; }

        /* Kolom aksi tidak boleh terbungkus.

           165px pada kolom kendaraan sempat 190px, dan tabelnya melebihi
           kartunya sampai tombol di tepi kanan terpotong — terukur pada
           1440px. Dua patokan itu saling tarik; yang dipatok di sini kolom
           aksinya, supaya pelebaran berikutnya memakan ruang kolom lain
           dulu sebelum memakan tombol. */
        .sw-tabel td:last-child, .sw-tabel th:last-child { white-space: nowrap; }

        /* Penanda vendor di kolom kendaraan. Sengaja sekecil meta barisnya:
           ia keterangan, bukan tindakan — yang menuntut tindakan sudah punya
           tabnya sendiri di atas. */
        .sw-vendor { display: inline-flex; align-items: center; gap: 4px; font-weight: 600; }
        .sw-vendor i.bi { font-size: .9em; }
        .sw-vendor.is-kosong { color: #b45309; }

        /* Varian lembut untuk baris yang urusannya sudah selesai. */
        .sw-lanjut.is-lembut {
            background: #fff; border: 1px solid var(--dsb-tepi); color: #475569;
            box-shadow: none;
        }
        .sw-lanjut.is-lembut i.bi { color: #94a3b8; }
        @media (hover: hover) and (pointer: fine) {
            .sw-lanjut.is-lembut:hover { border-color: #cbd5e1; color: #1c1f26; }
        }

        /* ===== Tabel ===== */
        .sw-kaki { padding-top: 0; }
        .sw-tagihan { font-weight: 800; color: var(--dsb-tinta, #1c1f26); }
        .sw-denda { display: block; margin-top: 2px; font-size: .74rem; font-weight: 700; color: #b91c1c; }

        /* Pil status dibuat selebar kolomnya, bukan selebar isinya: sederet
           pil yang panjangnya berbeda-beda terbaca sebagai daftar yang
           berantakan, padahal isinya cuma satu kata. */
        .sw-tabel .orcha-status-ringkas { min-width: 128px; }

        /* ===== HP =====
           TIDAK ada kolom yang disembunyikan lewat :nth-child.

           Nomor kolom adalah patokan yang diam-diam berubah arti: begitu satu
           kolom dilebur atau disisipkan, nomornya bergeser dan aturan yang
           dulu menyembunyikan "Mulai" berpindah menyembunyikan "Aksi" — tanpa
           galat dan tanpa uji yang merah. Master Harga pernah kehilangan tombol
           ubah & hapus persis begitu.

           Jadi yang dipakai kelas kolomnya (.k-sedang), dan bahasa rupa dasbor
           sudah mengurusnya: tersembunyi di layar menengah, lalu kembali
           sebagai baris berlabel di ponsel. Di sini cukup memastikan salinan
           samarnya tidak ikut berdiri dua kali. */
        @media (max-width: 767.98px) {
            .sw-tabel .dsb-tabel-samar { display: none !important; }

            /* Sel ponsel bawaannya satu baris flex tanpa wrap, dan isinya di
               sini kerap lebih dari satu benda: tanggal + jam + lencana telat,
               atau tagihan + dendanya. Tanpa wrap keduanya didorong keluar
               tepi kanan — terukur di 390px: "+ denda Rp 150.0…" dan tombol
               "Serah terim…" terpotong. Labelnya tetap di kiri lewat
               margin-right:auto, nilainya mengalir ke kanan dan turun baris
               bila tidak muat. */
            .sw-tabel tbody td { flex-wrap: wrap; justify-content: flex-end; }
            .sw-tabel tbody td[data-judul]::before { margin-right: auto; }
            .sw-tabel tbody td:first-child { justify-content: flex-start; }

            /* Denda selalu turun baris sendiri: ia keterangan atas angka di
               atasnya, bukan angka kedua yang sejajar. */
            .sw-tabel .sw-denda { flex: 0 0 100%; text-align: right; }

            .sw-tabel .sw-aksi { width: 100%; justify-content: flex-end; flex-wrap: wrap; }
        }

        .sw-wa { display: inline-flex; align-items: center; gap: 5px; }

        .sw-aksi { display: inline-flex; align-items: center; gap: 7px; justify-content: flex-end; }
        .sw-lanjut {
            display: inline-flex; align-items: center; gap: 6px;
            height: 34px; padding: 0 12px;
            border: 0; border-radius: 10px;
            background: #7c3aed; color: #fff;
            font-size: .78rem; font-weight: 700; line-height: 1;
            text-decoration: none; white-space: nowrap;
        }
        .sw-lanjut:hover { background: #6d28d9; color: #fff; }
        .sw-lanjut > i.bi { font-size: .88rem; line-height: 1; }
        .sw-lanjut > i.bi::before { display: block; line-height: 1; }
    </style>

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <span class="dsb-kicker"><i class="bi bi-compass"></i> Mode Orcha Journey</span>
                <h1 class="dsb-salam">Sewa Kendaraan</h1>
                <p class="dsb-hero-ket">
                    {{-- Kalimatnya diperbarui: sejak ada tombol "Catat sewa",
                         daftar ini tidak lagi hanya berisi pesanan dari
                         website. Keterangan yang bertahan padahal isinya sudah
                         berubah membuat admin mengira pesanan teleponnya
                         hilang. --}}
                    Pemesanan sewa dari website Orcha, dan yang dicatat sendiri dari telepon atau WhatsApp.
                </p>

                <a href="{{ route('admin.orcha.penyewaan.baru') }}" wire:navigate
                    class="dsb-tombol mt-3" style="--ikon:#b45309;">
                    <i class="bi bi-plus-lg"></i> Catat sewa
                </a>

                {{-- Cip jumlah & telat DIBUANG dari sini.

                     Keduanya kini ada di kartu tab, lengkap dengan angkanya —
                     dan dua tempat untuk satu angka adalah dua angka yang
                     suatu saat berbeda. Yang di tab lebih benar pula: ia
                     dihitung dari seluruh data, sedangkan cip "telat" di sini
                     hanya menghitung baris di halaman yang sedang tampil. --}}
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== TAB ==================

             Dua kelompok, dan pemisahannya disengaja: yang atas cara MELIHAT
             daftar, yang bawah DAFTAR PEKERJAAN. Digabung, "Belum kembali"
             tenggelam jadi salah satu pilihan menonton — padahal ia
             satu-satunya yang uangnya bisa hilang kalau didiamkan.

             Menggantikan pemilih status yang lama. Pemilih menyembunyikan
             enam pilihan di balik satu kotak dan tidak pernah menyebut berapa
             isinya; kartu berangka menjawab "ada pekerjaan di sana atau
             tidak" tanpa ditekan dulu. --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
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
                                                {{-- Angkanya hilang, bukan jadi "0", bila Orcha
                                                     belum mengirimnya: nol yang keliru lebih buruk
                                                     daripada tidak ada angka sama sekali. --}}
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

                {{-- ================== SARINGAN ==================
                     Tinggal pencarian: statusnya sudah pindah ke tab. --}}
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="sw-saring">
                            <div class="dsb-medan">
                                <label class="dsb-label" for="sw-cari">Cari penyewa</label>
                                @include('livewire.pages.admin.orcha.partials.cari', [
                                    'petunjuk' => 'Nama, nomor WhatsApp, kode, atau unitnya',
                                    'rupa' => 'dsb',
                                ])
                            </div>

                            <div class="dsb-medan sw-medan-aksi">
                                @if ($adaSaringan)
                                    <button type="button" wire:click="bersihkanSaringan" class="dsb-tombol is-lembut">
                                        <i class="bi bi-arrow-counterclockwise"></i><span>Tampilkan semua lagi</span>
                                    </button>
                                @endif
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
                    {{-- Kepala daftar seperti Pesanan Toko: nama tab yang
                         sedang menyala, lalu berapa isinya. Judul tetap
                         ("Pemesanan Sewa") tidak memberi tahu apa pun yang
                         belum terbaca dari tabnya. --}}
                    <div class="pt-daftar-kepala" style="--c: {{ $warnaTab }}">
                        <div class="pt-daftar-judul">
                            <b>{{ $tabs[$tabAktif][0] ?? 'Semua' }}</b>
                            <span>
                                @if ($jumlah > 0)
                                    {{ number_format($jumlah, 0, ',', '.') }} pemesanan
                                    @if ($tabAktif === 'telat')
                                        — sudah lewat tenggat dan belum dicatat kembali.
                                        Dendanya berjalan terus, dan unitnya belum bisa disewakan lagi.
                                    @endif
                                @else
                                    Belum ada isinya
                                @endif
                            </span>
                        </div>
                    </div>

                    @if ($daftar === [])
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi {{ $adaSaringan ? 'bi-funnel' : 'bi-truck' }}"></i></span>
                            <p class="dsb-kosong-judul">
                                {{ $adaSaringan ? 'Tidak ada pemesanan yang cocok' : 'Belum ada pemesanan sewa' }}
                            </p>
                            <p class="dsb-kosong-ket">
                                {{ $adaSaringan
                                    ? 'Coba ubah kata kunci atau statusnya.'
                                    : 'Pemesanan yang dikirim lewat formulir di website Orcha akan muncul di sini.' }}
                            </p>
                        </div>
                    @else
                        <div class="dsb-tabel-bungkus">
                            <table class="dsb-tabel sw-tabel">
                                <thead>
                                    <tr>
                                        {{-- ENAM kolom, bukan tujuh.

                                             "Mulai" dan "Kembali" berdiri sendiri-sendiri
                                             membuat tabelnya lebih lebar daripada layar:
                                             terukur 1440px, tombol Aksi terpotong di tepi
                                             kanan dan judul kolomnya ikut hilang. Keduanya
                                             memang satu hal — rentang pemakaian — dan
                                             dibaca begitu juga oleh orang. --}}
                                        <th>Penyewa</th>
                                        <th class="k-lebar">Kendaraan</th>
                                        <th class="k-sedang">Dipakai</th>
                                        <th style="text-align: right;">Tagihan</th>
                                        <th>Keadaan</th>
                                        <th style="text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($daftar as $baris)
                                        @php
                                            $sudahKembali = ! empty($baris['dikembalikan_pada']);
                                            $telatBaris = ! $sudahKembali && ! empty($baris['terlambat']);

                                            // Warna ikonnya mengikuti KEADAAN unitnya, bukan
                                            // status administratifnya: yang dicari admin saat
                                            // menyapu daftar adalah unit mana yang belum pulang.
                                            $warnaIkon = $telatBaris ? '#dc2626' : ($sudahKembali ? '#16a34a' : '#0284c7');
                                            $urlDetail = route('admin.orcha.penyewaan.detail', $baris['id']);
                                            $tgl = fn ($t, $f = 'd M Y') => $t
                                                ? \Carbon\Carbon::parse($t)->locale('id')->translatedFormat($f)
                                                : null;
                                        @endphp
                                        <tr wire:key="penyewaan-{{ $baris['id'] }}"
                                            @class(['is-tanda' => $telatBaris])
                                            @if ($telatBaris) style="--c: #dc2626" @endif>
                                            <td>
                                                <a wire:navigate href="{{ $urlDetail }}" class="dsb-tabel-utama">
                                                    <span class="dsb-ikon is-kecil" style="--c: {{ $warnaIkon }}"><i class="bi bi-truck-front-fill"></i></span>
                                                    <span class="dsb-tabel-teks">
                                                        <span class="dsb-tabel-judul">{{ $baris['nama'] }}</span>
                                                        <span class="dsb-tabel-meta">
                                                            <span class="dsb-lencana is-abu">{{ $baris['kode'] }}</span>
                                                            {{-- Nomornya SELALU terlihat, bukan .dsb-tabel-samar.

                                                                 Samar sengaja disembunyikan di layar lebar — tempatnya
                                                                 untuk salinan kolom yang memang sudah tampil di
                                                                 sebelahnya. Nomor WhatsApp tidak punya kolom sendiri,
                                                                 jadi menyamarkannya berarti ia hilang sama sekali di
                                                                 desktop, padahal itu yang dipakai menghubungi penyewa. --}}
                                                            <span class="sw-wa"><i class="bi bi-whatsapp"></i>{{ $baris['whatsapp'] }}</span>
                                                            {{-- Salinan kolom Mulai & Kembali, yang menghilang di layar sempit. --}}
                                                            <span class="dsb-tabel-samar"><i class="bi bi-calendar-event"></i>{{ $tgl($baris['tanggal_mulai']) ?: '—' }} → {{ $tgl($baris['tanggal_selesai']) ?: '—' }}</span>
                                                        </span>
                                                    </span>
                                                </a>
                                            </td>

                                            <td class="k-lebar" data-judul="Kendaraan">
                                                <span class="dsb-tabel-teks">
                                                    <span class="dsb-tabel-judul">{{ $baris['kendaraan']['nama'] }}</span>
                                                    <span class="dsb-tabel-meta">
                                                        <span>{{ $baris['kendaraan']['transmisi'] }} · {{ $baris['durasi_label'] }} · {{ $baris['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }}</span>
                                                    </span>

                                                    {{-- Dari mana unitnya.

                                                         Daftar ini sudah punya tab "Belum ada
                                                         vendor", tetapi barisnya sendiri tidak
                                                         pernah menyebut vendor — jadi satu-
                                                         satunya cara tahu adalah membuka
                                                         pesanannya satu per satu, persis
                                                         pekerjaan yang hendak dihemat tabnya.

                                                         Unit milik sendiri tidak diberi penanda
                                                         apa pun: ia tidak punya vendor, dan
                                                         lencana "—" cuma menambah benda yang
                                                         harus diabaikan mata. --}}
                                                    @if (! ($baris['milik_sendiri'] ?? false))
                                                        <span class="dsb-tabel-meta">
                                                            @if (data_get($baris, 'vendor.nama'))
                                                                <span class="sw-vendor">
                                                                    <i class="bi bi-building"></i>
                                                                    {{ data_get($baris, 'vendor.nama') }}
                                                                </span>
                                                            @elseif (! in_array($baris['status'] ?? '', ['selesai', 'batal'], true))
                                                                <span class="sw-vendor is-kosong">
                                                                    <i class="bi bi-building-exclamation"></i>
                                                                    Vendor belum dicarikan
                                                                </span>
                                                            @endif
                                                        </span>
                                                    @endif
                                                </span>
                                            </td>

                                            {{-- Satu kolom untuk rentangnya.

                                                 Tenggat pengembalian tetap yang paling tebal
                                                 di dalamnya — itu yang dicek admin tiap pagi
                                                 untuk tahu unit mana yang seharusnya sudah
                                                 kembali — tetapi tanggal mulainya tidak
                                                 pantas memakan kolom sendiri selebar itu. --}}
                                            <td class="k-sedang" data-judul="Dipakai">
                                                @if ($baris['tanggal_selesai'])
                                                    <span class="dsb-tabel-angka">
                                                        {{ $tgl($baris['tanggal_mulai'], 'd M') ?: '—' }}
                                                        &rarr;
                                                        {{ $tgl($baris['tanggal_selesai']) }}
                                                    </span>
                                                    <span class="dsb-tabel-samar">
                                                        {{ $baris['jam_mulai'] ?: '—' }}–{{ $baris['jam_selesai'] ?: '—' }}
                                                    </span>
                                                    @if ($sudahKembali)
                                                        <span class="dsb-lencana is-hijau">
                                                            <i class="bi bi-check-circle-fill"></i>
                                                            kembali {{ $tgl($baris['dikembalikan_pada'], 'd M, H:i') }}
                                                        </span>
                                                    @elseif ($telatBaris)
                                                        <span class="dsb-lencana is-merah">
                                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                                            telat {{ (int) floor($baris['terlambat_menit'] / 60) }} jam
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="dsb-tabel-angka">{{ $tgl($baris['tanggal_mulai']) ?: '—' }}</span>
                                                    <span class="dsb-tabel-samar">tanggal kembalinya belum diisi</span>
                                                @endif
                                            </td>

                                            <td style="text-align: right;" data-judul="Tagihan">
                                                <span class="dsb-tabel-angka sw-tagihan">
                                                    {{ $baris['estimasi_biaya'] ? 'Rp '.number_format($baris['estimasi_biaya'], 0, ',', '.') : '—' }}
                                                </span>
                                                @if (($baris['total_denda'] ?? 0) > 0)
                                                    <span class="sw-denda">+ denda Rp {{ number_format($baris['total_denda'], 0, ',', '.') }}</span>
                                                @endif
                                            </td>

                                            <td data-judul="Status">
                                                {{-- Bila daftar statusnya TIDAK sampai, yang digambar
                                                     tulisannya — bukan kotak pilih kosong.

                                                     Daftar itu datang dari /rujukan milik Orcha, dan
                                                     saat Orcha sedang tidak bisa dihubungi
                                                     rujukan() mengembalikan senarai kosong. Kotak
                                                     pilih tanpa satu pun pilihan tergambar sebagai
                                                     pil berwarna yang benar-benar kosong: statusnya
                                                     lenyap dari layar tanpa satu pun kalimat yang
                                                     menjelaskan sebabnya, dan admin membacanya
                                                     sebagai data yang hilang.

                                                     Statusnya sendiri selalu ada — ia datang bersama
                                                     barisnya, bukan dari rujukan. --}}
                                                @if ($pilihanStatus === [])
                                                    <span class="orcha-status-diam" data-status="{{ $baris['status'] }}"
                                                        title="Daftar status belum bisa diambil dari Orcha, jadi statusnya belum bisa diubah dari sini.">
                                                        <i class="bi bi-wifi-off"></i>
                                                        {{ $baris['status_label'] ?? $baris['status'] }}
                                                    </span>
                                                @else
                                                    {{-- Konfirmasi dulu.

                                                         Pemilih ini duduk di jalur jempol saat
                                                         menggulir, dan "Batal" di dalamnya
                                                         melepas unitnya dari jadwal — tindakan
                                                         yang tidak bisa dibatalkan dengan
                                                         memilih ulang.

                                                         Nilainya dikembalikan saat dibatalkan;
                                                         pemilih yang tetap menampilkan pilihan
                                                         baru padahal tidak tersimpan adalah
                                                         kebohongan yang bertahan sampai halaman
                                                         dimuat ulang. Pola yang sama dipakai
                                                         Pendaftaran Trip. --}}
                                                    <select class="form-select form-select-sm orcha-status-ringkas"
                                                        data-status="{{ $baris['status'] }}"
                                                        aria-label="Ubah keadaan {{ $baris['kode'] }}"
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
                                                        ">
                                                        @foreach ($pilihanStatus as $kunci => $label)
                                                            <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </td>

                                            <td style="text-align: right;">
                                                <span class="sw-aksi">
                                                    {{-- Tombol ikon "lihat detail" DIBUANG dari sini.

                                                         Nama penyewanya di kolom pertama sudah
                                                         menjadi tautan ke halaman yang sama, jadi
                                                         ikon ini mengulang jalan yang sudah ada —
                                                         sambil memakan 44px yang membuat tombol
                                                         sebelahnya terpotong di tepi kartu. Dua
                                                         jalan ke satu tempat juga membuat admin
                                                         yang awam ragu apakah keduanya berbeda. --}}
                                                    {{-- Tautan biasa, bukan tombol Livewire. Lembarnya kini
                                                         halaman tersendiri, jadi membukanya tidak bergantung
                                                         pada JavaScript yang harus hidup lebih dulu di
                                                         halaman daftar. --}}
                                                    {{-- Tombolnya menyebut LANGKAH BERIKUTNYA baris ini,
                                                         bukan nama layarnya.

                                                         Sebelumnya "Serah terima" berwarna utama di SETIAP
                                                         baris — termasuk yang unitnya sudah pulang dan
                                                         urusannya selesai. Sederet tombol utama yang
                                                         semuanya menuntut perhatian sama saja dengan tidak
                                                         ada yang menuntut perhatian, dan untuk admin yang
                                                         tidak setiap hari memakai aplikasi, itu yang
                                                         membuat ia menekan yang salah.

                                                         Yang batal tidak diberi tombol sama sekali:
                                                         tidak ada unit yang diserahterimakan. --}}
                                                    @if (($baris['status'] ?? '') !== 'batal')
                                                        <a href="{{ route('admin.orcha.penyewaan.serah-terima', $baris['id']) }}"
                                                            wire:navigate
                                                            class="{{ $sudahKembali ? 'sw-lanjut is-lembut' : 'sw-lanjut' }}"
                                                            title="{{ $sudahKembali
                                                                ? 'Lihat atau betulkan catatan serah terima & dendanya'
                                                                : 'Catat unit ini kembali, sekalian dendanya kalau ada' }}">
                                                            <i class="bi {{ $sudahKembali ? 'bi-clipboard-check' : 'bi-box-arrow-in-left' }}"></i>
                                                            <span class="k-sedang">
                                                                {{ $sudahKembali ? 'Lihat catatannya' : 'Catat kembali' }}
                                                            </span>
                                                        </a>
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="dsb-kartu-isi sw-kaki">
                            @include('livewire.pages.admin.orcha.partials.paginasi')
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
