@section('title')
Data Kesehatan Peserta || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko & layar pendaftaran lainnya (bahasa
         rupa dsb-*): satu kisi 12 kolom, kartu putih datar berkepala ikon. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    {{-- Gaya khusus layar ini, ditulis INLINE: public/build di-gitignore, jadi
         berkas hasil build tidak pernah sampai ke server. --}}
    <style>
    /* ===== Lencana di kepala ===== */
    .ks-lencana-kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-top: 7px; }

    /* ===== Pita pemberitahuan =====
       Satu baris mendatar, bukan kartu setinggi kotak. Isinya satu kalimat,
       dan kartu selebar layar untuk satu kalimat meninggalkan petak putih
       sebesar dirinya sendiri. */
    .ks-pita { display: flex; align-items: center; gap: 13px; flex-wrap: wrap; }
    .ks-pita > .dsb-ikon { flex-shrink: 0; }
    .ks-pita-teks { flex: 1 1 280px; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .ks-pita-teks > strong { font-size: .92rem; color: var(--dsb-tinta); }
    .ks-pita-teks > span { font-size: .82rem; color: var(--dsb-redup); line-height: 1.55; }

    /* Kartu yang punya "warna urusan": pita tipis di tepi kiri, bukan bingkai
       berwarna seluruh kartu. Yang perlu ditandai urgensinya, bukan isinya. */
    .ks-kartu-siaga { border-left: 4px solid #dc2626; }
    .ks-kartu-tunggu { border-left: 4px solid #d97706; }
    .ks-kartu-arsip { border-left: 4px solid #94a3b8; background: #fcfcfd; }

    /* ===== Daftar nama yang perlu disiapkan =====
       Berubin, bukan bertumpuk satu kolom: pada rombongan dua belas orang,
       satu kolom nama di kartu selebar layar menyisakan dua pertiga lebar
       kosong di kanannya. */
    .ks-siaga-rak { display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 10px; }
    .ks-siaga {
        display: flex; gap: 10px; padding: 11px 13px; border-radius: 12px;
        background: #fef2f2; border: 1px solid #fecaca; min-width: 0;
    }
    .ks-siaga > i.bi { color: #dc2626; line-height: 1.4; flex-shrink: 0; }
    .ks-siaga-teks { min-width: 0; font-size: .83rem; line-height: 1.55; color: #7f1d1d; }
    .ks-siaga-nama { display: block; font-weight: 800; color: #991b1b; }
    .ks-siaga-gol { font-weight: 600; color: #b45309; }

    /* ===== Nama pengganti yang belum mengisi ===== */
    .ks-nama-rak { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 11px; }

    /* ===== Arsip ===== */
    .ks-arsip-isi { opacity: .72; }
    .ks-arsip-isi .orcha-kesehatan { background: #fbfcfd; }

    .ks-kesehatan .orcha-kesehatan:last-child { margin-bottom: 0; }

    /* Di HP keempat ubin angka tetap BERDUA sebaris, bukan satu per baris.

       Bawaan rak melebarkan k-3 jadi selebar layar di bawah 768px — masuk akal
       untuk kartu berisi daftar, tetapi ubin yang isinya satu angka lalu
       memakan empat layar penuh sebelum isi halaman yang dicari orang. */
    @media (max-width: 767.98px) {
        .ks-kesehatan .dsb-rak > .dsb-stat.k-3 { grid-column: span 6; }
        .ks-kesehatan .dsb-stat { padding: 15px; column-gap: 11px; }
        .ks-kesehatan .dsb-stat > .dsb-ikon { width: 38px; height: 38px; border-radius: 11px; font-size: 1rem; }
    }
    .ks-ket { color: var(--dsb-redup); font-size: .82rem; line-height: 1.6; margin: 0 0 14px; }
    </style>

    @php
        $belumIsi = $pendaftaran['peserta_belum_isi'] ?? [];
        $jumlahPeserta = (int) ($pendaftaran['jumlah_peserta'] ?? 0);

        // Hanya yang berangkat yang dihitung. Riwayat milik peserta yang
        // sudah diganti tetap tersimpan, dan ikut menghitungnya membuat
        // rombongan terlihat lengkap padahal orang barunya belum mengisi
        // sama sekali — persis kekeliruan yang paling mahal di sini.
        $terisi = count($aktif);

        // Yang sudah diganti tidak ikut dihitung: ia tidak berangkat, dan
        // menyiapkan kebutuhan khususnya hanya membuang perhatian tim.
        $perluPerhatian = collect($riwayat)
            ->filter(fn ($satu) => ($satu['peserta_aktif'] ?? true) !== false)
            ->filter(fn ($satu) => ($satu['tingkat_perhatian'] ?? '') === 'tinggi')
            ->values();

        $lengkap = $jumlahPeserta > 0 && $terisi >= $jumlahPeserta;
    @endphp

    <div class="dsb ks-kesehatan">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        {{-- ============ KEPALA ============

             Kartu kepala yang lama menyisakan sepertiga lebar kosong di kanan
             judulnya. Sisi itu kini dipakai tindakannya, dan "berapa yang sudah
             mengisi" naik jadi lencana — itu pertanyaan pertama yang dibawa
             orang saat membuka halaman ini. --}}
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Data Kesehatan Peserta</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        <i class="bi bi-person-vcard me-1"></i>{{ $pendaftaran['nama'] ?? '' }}
                        · {{ data_get($pendaftaran, 'paket.nama') ?: 'Paket menyusul' }}
                        @if (! empty($pendaftaran['tanggal_berangkat']))
                            · berangkat
                            {{ \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->locale('id')->translatedFormat('d F Y') }}
                        @endif
                    </span>
                    <span class="d-block ks-lencana-kepala">
                        <span class="orcha-kode">{{ $pendaftaran['kode'] ?? '—' }}</span>
                        <span class="dsb-lencana {{ $lengkap ? 'is-hijau' : 'is-kuning' }}">
                            <i class="bi bi-clipboard-check"></i>
                            {{ $terisi }} dari {{ $jumlahPeserta }} sudah mengisi
                        </span>
                        @if ($perluPerhatian->isNotEmpty())
                            <span class="dsb-lencana is-merah">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                {{ $perluPerhatian->count() }} orang perlu disiapkan
                            </span>
                        @endif
                    </span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        {{-- ============ PEMBERITAHUAN KERAHASIAAN ============

             Data kesehatan bukan data biasa. Peringatannya berdiri sendiri di
             atas, bukan catatan kaki: yang membukanya harus tahu sebelum
             membaca, bukan sesudah. Dibuat PITA setinggi satu baris — isinya
             satu kalimat, dan kartu penuh untuk satu kalimat hanya menggeser
             isi yang dicari orang ke bawah layar. --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-isi ks-pita">
                    <span class="dsb-ikon is-kecil" style="--c: #0369a1"><i class="bi bi-shield-lock"></i></span>
                    <div class="ks-pita-teks">
                        <strong>Ini data pribadi peserta</strong>
                        <span>
                            Isinya hanya untuk keselamatan perjalanan. Setiap kali halaman ini dibuka,
                            Orcha mencatat siapa yang membukanya — jangan dibagikan ke luar tim.
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ RINGKASAN ============

             Empat ubin, masing-masing seperempat lebar, jadi barisnya terisi
             penuh tanpa sisa. Ikonnya di samping teks, bukan di atasnya:
             bertumpuk, empat kartu ini menghabiskan hampir seluruh layar
             pertama — dan isinya cuma empat angka, sedangkan yang dicari admin
             justru ada di bawahnya. --}}
        @php
            $kartu = [
                ['Perlu disiapkan', $tingkat['tinggi'], '#dc2626', 'bi-exclamation-triangle-fill',
                    'Obat atau penanganan khusus'],
                ['Ada catatan', $tingkat['sedang'], '#d97706', 'bi-journal-text',
                    'Cukup diingat di lapangan'],
                ['Tanpa catatan', $tingkat['aman'], '#16a34a', 'bi-check-circle-fill',
                    'Tidak perlu persiapan khusus'],
                ['Sudah mengisi', $terisi.' / '.$jumlahPeserta, '#7c3aed', 'bi-clipboard-check',
                    $belumIsi ? 'Belum: '.implode(', ', $belumIsi) : 'Semua peserta sudah mengisi'],
            ];
        @endphp
        <div class="dsb-rak dsb-bagian">
            @foreach ($kartu as [$label, $nilai, $warna, $ikon, $keterangan])
                <div class="dsb-stat k-3" style="--c: {{ $warna }}">
                    <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                    <p class="dsb-stat-label">{{ $label }}</p>
                    <p class="dsb-stat-nilai">{{ $nilai }}</p>
                    <p class="dsb-stat-ket">{{ $keterangan }}</p>
                </div>
            @endforeach
        </div>

        {{-- ============ PENGGANTI YANG BELUM MENGISI ============

             Akibat langsung penggantian peserta, dan yang paling mudah
             terlewat: orang lamanya sudah mengisi, jadi rombongan terlihat
             lengkap sampai ada yang menghitung ulang. Yang berangkat orang
             baru, dan data kesehatannya belum ada sama sekali. --}}
        @if ($penggantiBelumIsi !== [])
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12 ks-kartu-tunggu">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #d97706"><i class="bi bi-hourglass-split"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Peserta Pengganti Belum Mengisi</h2>
                                <span class="dsb-kartu-sub">Mereka berangkat, tetapi data kesehatannya belum ada</span>
                            </span>
                        </div>
                        <span class="dsb-lencana is-kuning">{{ count($penggantiBelumIsi) }} orang</span>
                    </div>

                    <div class="dsb-kartu-isi">
                        <div class="ks-nama-rak">
                            @foreach ($penggantiBelumIsi as $nama)
                                <span class="orcha-ganti-baru">{{ $nama }}</span>
                            @endforeach
                        </div>

                        <p class="ks-ket mb-0">
                            Data milik orang yang digantikan tidak berlaku buat mereka — alergi dan obat
                            rutinnya beda. Minta mereka mengisi di website Orcha memakai kode
                            <strong>{{ $pendaftaran['kode'] ?? '' }}</strong>, kode yang sama dengan
                            pendaftaran ini.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ YANG PERLU DISIAPKAN ============
             Nama-nama yang menuntut kesiapan dikumpulkan di depan, sebelum
             kartunya sendiri. Pada rombongan dua belas orang, menemukan yang
             merah berarti menggulung layar dan berharap tidak terlewat. --}}
        @if ($perluPerhatian->isNotEmpty())
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12 ks-kartu-siaga">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #dc2626"><i class="bi bi-exclamation-triangle-fill"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Perlu Disiapkan Sebelum Berangkat</h2>
                                <span class="dsb-kartu-sub">Obat, alat bantu, atau penanganan khusus yang harus dibawa</span>
                            </span>
                        </div>
                        <span class="dsb-lencana is-merah">{{ $perluPerhatian->count() }} orang</span>
                    </div>

                    <div class="dsb-kartu-isi">
                        <div class="ks-siaga-rak">
                            @foreach ($perluPerhatian as $satu)
                                <div class="ks-siaga">
                                    <i class="bi bi-person-exclamation"></i>
                                    <span class="ks-siaga-teks">
                                        <span class="ks-siaga-nama">
                                            {{ $satu['nama_peserta'] ?? '—' }}
                                            @if ($satu['golongan_darah'] ?? null)
                                                <span class="ks-siaga-gol">· gol. darah {{ $satu['golongan_darah'] }}</span>
                                            @endif
                                        </span>
                                        {{ implode('; ', $satu['alasan_perhatian'] ?? []) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============ KARTU PER PESERTA ============ --}}
        <div class="dsb-rak dsb-bagian">
            <div class="dsb-kartu k-12">
                <div class="dsb-kartu-kepala">
                    <div class="dsb-kartu-kepala-kiri">
                        <span class="dsb-ikon is-kecil" style="--c: #e11d48"><i class="bi bi-heart-pulse"></i></span>
                        <span>
                            <h2 class="dsb-kartu-judul">Peserta yang Berangkat</h2>
                            <span class="dsb-kartu-sub">Yang perlu disiapkan bergaris merah di tepi kiri kartunya</span>
                        </span>
                    </div>
                    <span class="dsb-lencana is-abu">{{ count($aktif) }} orang</span>
                </div>

                <div class="dsb-kartu-isi">
                    @forelse ($aktif as $peserta)
                        @include('livewire.pages.admin.orcha.partials.kartu-kesehatan', ['peserta' => $peserta])
                    @empty
                        <div class="dsb-kosong">
                            <span class="dsb-kosong-ikon"><i class="bi bi-heart-pulse"></i></span>
                            <p class="dsb-kosong-judul">Belum ada yang mengisi data kesehatan</p>
                            <p class="dsb-kosong-ket">
                                Peserta mengisinya sendiri lewat website Orcha, cukup dengan kode
                                pendaftaran <strong>{{ $pendaftaran['kode'] ?? '' }}</strong>.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ============ ARSIP PESERTA YANG DIGANTIKAN ============

             Bagian tersendiri di paling bawah, bukan berjajar di antara peserta
             yang berangkat.

             Sebelumnya keduanya satu daftar, dibedakan sebaris penanda di atas
             kartunya — sementara kartunya sendiri tetap selengkap milik peserta
             yang ikut: alergi, obat rutin, kontak darurat. Tim lapangan yang
             membacanya sambil berjalan tidak punya cara membedakan siapa yang
             benar-benar ada di kendaraan, dan salah satu arah kekeliruannya
             berbahaya: menyiapkan obat untuk orang yang tidak datang, sambil
             mengira sudah menyiapkan semuanya. --}}
        @if ($arsip !== [])
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12 ks-kartu-arsip">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #64748b"><i class="bi bi-archive"></i></span>
                            <span>
                                <h2 class="dsb-kartu-judul">Sudah Diganti — Tidak Ikut Berangkat</h2>
                                <span class="dsb-kartu-sub">Disimpan sebagai catatan; tidak perlu disiapkan apa pun</span>
                            </span>
                        </div>
                        <span class="dsb-lencana is-abu">{{ count($arsip) }} orang</span>
                    </div>

                    <div class="dsb-kartu-isi">
                        <p class="ks-ket">
                            Datanya tidak dihapus supaya jejak pendaftaran tetap utuh, tetapi tidak ikut
                            dihitung di angka-angka atas.
                        </p>

                        @foreach ($arsip as $peserta)
                            @php
                                $namaArsip = trim((string) ($peserta['nama_peserta'] ?? ''));
                                $pengganti = $penggantiPer[mb_strtolower($namaArsip)] ?? null;
                            @endphp

                            <div class="orcha-arsip-penanda">
                                <i class="bi bi-arrow-left-right"></i>
                                <span>
                                    {{-- Digantikan oleh siapa disebut di sini juga. Tanpa itu admin
                                         membuka halaman detail hanya untuk satu nama yang sebenarnya
                                         sudah ada di tangan. --}}
                                    <span class="orcha-ganti-lama">{{ $namaArsip ?: 'Peserta ini' }}</span>
                                    @if ($pengganti)
                                        <i class="bi bi-arrow-right" style="color:#14a06a"></i>
                                        <span class="orcha-ganti-baru">{{ $pengganti }}</span>
                                    @else
                                        <span style="font-size:.84rem">sudah digantikan orang lain</span>
                                    @endif
                                </span>
                            </div>

                            <div class="ks-arsip-isi">
                                @include('livewire.pages.admin.orcha.partials.kartu-kesehatan', ['peserta' => $peserta])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
