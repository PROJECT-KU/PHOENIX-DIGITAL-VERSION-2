@section('title')
Sewa Kendaraan Masuk || lemon
@stop

<div>
    {{-- Bahasa rupa dsb-* milik dasbor lemon, patokan yang sama dengan layar
         Pesanan Toko. Kelas orcha-* hanya untuk yang memang khas Orcha —
         di layar ini tinggal paginasi API dan pil statusnya. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    @php
        $jumlah = (int) ($meta['total'] ?? count($daftar));
        $adaSaringan = trim((string) $cari) !== '' || (string) $filterStatus !== '';

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
        .sw-saring { display: grid; gap: 12px; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        @media (max-width: 767.98px) {
            .sw-saring { grid-template-columns: minmax(0, 1fr); }
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
                    Pemesanan sewa yang dikirim lewat formulir di website Orcha.
                </p>

                <div class="dsb-chip-deret mt-2">
                    <span class="dsb-chip" style="--c: #7c3aed">
                        <i class="bi bi-truck"></i> {{ $jumlah }} pemesanan
                    </span>
                    @if ($telat)
                        {{-- Disebut hanya saat ada. Cip "0 telat" yang selalu
                             berdiri membuat mata berhenti membacanya, dan pada
                             hari yang benar-benar ada telatnya ia tidak lagi
                             menarik perhatian siapa pun. --}}
                        <span class="dsb-chip" style="--c: #dc2626">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $telat }} belum kembali
                        </span>
                    @endif
                </div>
            </div>
        </header>

        @include('livewire.pages.admin.orcha.partials.galat-dsb')

        {{-- ================== SARINGAN ================== --}}
        <section class="dsb-bagian">
            <div class="dsb-rak">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-isi">
                        <div class="sw-saring">
                            @include('livewire.pages.admin.orcha.partials.cari', [
                                'petunjuk' => 'Cari kode, nama, WhatsApp, atau unit…',
                                'rupa' => 'dsb',
                            ])

                            <div class="dsb-medan">
                                <select wire:model.live="filterStatus" class="dsb-isian" aria-label="Saring menurut status">
                                    <option value="" @selected((string) $this->filterStatus === '')>Semua status</option>
                                    @foreach ($pilihanStatus as $kunci => $label)
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
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-truck"></i></span>
                            <div>
                                <h3 class="dsb-kartu-judul">Pemesanan Sewa</h3>
                                <span class="dsb-kartu-sub">Satu baris = satu pemesanan · tenggat kembali ada di kolomnya sendiri</span>
                            </div>
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
                                        <th>Penyewa</th>
                                        <th class="k-lebar">Kendaraan</th>
                                        <th class="k-sedang">Mulai</th>
                                        <th class="k-sedang">Kembali</th>
                                        <th style="text-align: right;">Tagihan</th>
                                        <th>Status</th>
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
                                                </span>
                                            </td>

                                            <td class="k-sedang" data-judul="Mulai">
                                                <span class="dsb-tabel-angka">{{ $tgl($baris['tanggal_mulai']) ?: '—' }}</span>
                                                <span class="dsb-tabel-samar">{{ $baris['jam_mulai'] ?: '—' }}</span>
                                            </td>

                                            {{-- Tenggat pengembalian berdiri sendiri, bukan diselipkan
                                                 di kolom mulai: inilah yang dicek admin tiap pagi untuk
                                                 tahu unit mana yang seharusnya sudah kembali. --}}
                                            <td class="k-sedang" data-judul="Kembali">
                                                @if ($baris['tanggal_selesai'])
                                                    <span class="dsb-tabel-angka">{{ $tgl($baris['tanggal_selesai']) }}</span>
                                                    <span class="dsb-tabel-samar">{{ $baris['jam_selesai'] }}</span>
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
                                                    <span class="dsb-tabel-samar">—</span>
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
                                                    <select class="form-select form-select-sm orcha-status-ringkas"
                                                        data-status="{{ $baris['status'] }}"
                                                        aria-label="Ubah status {{ $baris['kode'] }}"
                                                        wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)">
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
                                                    <a wire:navigate href="{{ $urlDetail }}"
                                                        class="dsb-tabel-btn" style="--c: #0284c7"
                                                        title="Lihat detail penyewaan">
                                                        <i class="bi bi-truck-front"></i>
                                                    </a>
                                                    {{-- Tautan biasa, bukan tombol Livewire. Lembarnya kini
                                                         halaman tersendiri, jadi membukanya tidak bergantung
                                                         pada JavaScript yang harus hidup lebih dulu di
                                                         halaman daftar. --}}
                                                    <a href="{{ route('admin.orcha.penyewaan.serah-terima', $baris['id']) }}"
                                                        wire:navigate class="sw-lanjut"
                                                        title="Catat serah terima & denda">
                                                        <i class="bi bi-clipboard-check"></i>
                                                        <span class="k-sedang">Serah terima</span>
                                                    </a>
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
