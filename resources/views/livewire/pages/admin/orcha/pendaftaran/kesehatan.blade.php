@section('title')
Riwayat Kesehatan || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

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
        @endphp

        {{-- ============ IDENTITAS ============ --}}
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-3 p-lg-4">
                <a href="{{ route('admin.orcha.pendaftaran.detail', $pendaftaranId) }}" wire:navigate
                    class="orcha-tautan-balik mb-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke detail pendaftaran
                </a>
                <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">Riwayat Kesehatan Peserta</h1>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="orcha-kode">{{ $pendaftaran['kode'] ?? '—' }}</span>
                    <span class="text-muted" style="font-size:.85rem">
                        {{ $pendaftaran['nama'] ?? '' }} ·
                        {{ data_get($pendaftaran, 'paket.nama') ?: 'Paket menyusul' }}
                        @if (! empty($pendaftaran['tanggal_berangkat']))
                            · berangkat
                            {{ \Carbon\Carbon::parse($pendaftaran['tanggal_berangkat'])->locale('id')->translatedFormat('d F Y') }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Data kesehatan bukan data biasa. Peringatannya berdiri sendiri di
             atas, bukan catatan kaki: yang membukanya harus tahu sebelum
             membaca, bukan sesudah. --}}
        <div class="alert alert-info border-0 rounded-4 d-flex gap-3 align-items-start mb-3">
            <i class="bi bi-shield-lock fs-5"></i>
            <div style="font-size:.86rem">
                <strong class="d-block">Data pribadi peserta</strong>
                Isinya hanya untuk keperluan keselamatan perjalanan. Pembukaan halaman ini tercatat
                di server Orcha beserta akun yang membukanya — jangan dibagikan ke luar tim.
            </div>
        </div>

        {{-- ============ RINGKASAN ============ --}}
        <div class="row g-3 mb-3">
            @php
                $kartu = [
                    ['Perlu perhatian', $tingkat['tinggi'], 'Menuntut kesiapan sebelum berangkat',
                        'bi-exclamation-triangle-fill', 'orcha-ikon-awas'],
                    ['Ada catatan', $tingkat['sedang'], 'Cukup diingat di lapangan',
                        'bi-journal-text', 'orcha-ikon-catat'],
                    ['Tanpa catatan', $tingkat['aman'], 'Tidak ada yang perlu disiapkan khusus',
                        'bi-check-circle-fill', 'orcha-ikon-aman'],
                    ['Sudah mengisi', $terisi.' / '.$jumlahPeserta,
                        $belumIsi ? 'Belum: '.implode(', ', $belumIsi) : 'Seluruh peserta sudah mengisi',
                        'bi-clipboard-check', 'orcha-ikon-omzet'],
                ];
            @endphp
            {{-- Ikonnya di samping teks, bukan di atasnya.

                 Bertumpuk, empat kartu ini menghabiskan hampir seluruh layar
                 pertama — dan isinya cuma empat angka. Yang dicari admin justru
                 ada di bawahnya: siapa yang perlu disiapkan, dan siapa yang
                 belum mengisi. Ringkasan tidak pantas mengusir isi. --}}
            @foreach ($kartu as [$label, $nilai, $keterangan, $ikon, $rupa])
                <div class="col-6 col-lg-3">
                    <div class="card orcha-kartu orcha-untung-kartu orcha-kartu-ringkas h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2">
                                <span class="orcha-ikon {{ $rupa }}"><i class="bi {{ $ikon }}"></i></span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-muted small">{{ $label }}</div>
                                    <div class="nilai">{{ $nilai }}</div>
                                </div>
                            </div>
                            <div class="keterangan text-muted">{{ $keterangan }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============ PENGGANTI YANG BELUM MENGISI ============

             Akibat langsung penggantian peserta, dan yang paling mudah
             terlewat: orang lamanya sudah mengisi, jadi rombongan terlihat
             lengkap sampai ada yang menghitung ulang. Yang berangkat orang
             baru, dan riwayat kesehatannya belum ada sama sekali. --}}
        @if ($penggantiBelumIsi !== [])
            <div class="card border-0 shadow-sm rounded-4 mb-3 orcha-kartu-tunggu">
                <div class="card-body p-3 p-lg-4">
                    <h2 class="fw-bold mb-2 orcha-judul-ikon" style="font-size:1.05rem;color:#8a5a09">
                        <i class="bi bi-hourglass-split"></i>
                        Peserta pengganti belum mengisi riwayat kesehatan
                    </h2>

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        @foreach ($penggantiBelumIsi as $nama)
                            <span class="orcha-ganti-baru">{{ $nama }}</span>
                        @endforeach
                    </div>

                    <p class="text-muted mb-0" style="font-size:.84rem">
                        Riwayat milik orang yang digantikan tidak berlaku untuk mereka — alergi dan
                        obat rutinnya berbeda. Mintalah mengisi di website Orcha memakai kode
                        <strong>{{ $pendaftaran['kode'] ?? '' }}</strong>, kode yang sama dengan
                        pendaftaran ini.
                    </p>
                </div>
            </div>
        @endif

        {{-- ============ YANG PERLU DISIAPKAN ============
             Nama-nama yang menuntut kesiapan dikumpulkan di depan, sebelum
             kartunya sendiri. Pada rombongan dua belas orang, menemukan yang
             merah berarti menggulung layar dan berharap tidak terlewat. --}}
        @if ($perluPerhatian->isNotEmpty())
            <div class="card border-0 shadow-sm rounded-4 mb-3 orcha-kartu-siaga">
                <div class="card-body p-3 p-lg-4">
                    <h2 class="fw-bold mb-2 orcha-judul-ikon" style="font-size:1.05rem;color:#b91c1c">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Perlu disiapkan sebelum berangkat
                    </h2>
                    @foreach ($perluPerhatian as $satu)
                        <div class="orcha-siaga-baris">
                            <strong>{{ $satu['nama_peserta'] ?? '—' }}</strong>
                            @if ($satu['golongan_darah'] ?? null)
                                <span class="text-muted" style="font-size:.8rem">
                                    (gol. darah {{ $satu['golongan_darah'] }})
                                </span>
                            @endif
                            <span style="font-size:.88rem">
                                — {{ implode('; ', $satu['alasan_perhatian'] ?? []) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ============ KARTU PER PESERTA ============ --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem">
                        <i class="bi bi-heart-pulse text-primary"></i> Rincian peserta yang berangkat
                    </h2>
                    <span class="text-muted small">
                        Yang perlu perhatian ditandai pita merah di sisi kiri kartunya
                    </span>
                </div>

                @forelse ($aktif as $peserta)
                    @include('livewire.pages.admin.orcha.partials.kartu-kesehatan', ['peserta' => $peserta])
                @empty
                    <div class="text-center py-5">
                        <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-heart-pulse"></i></div>
                        <p class="text-muted mb-2">Belum ada peserta yang mengisi riwayat kesehatan.</p>
                        <p class="text-muted small mb-0">
                            Peserta mengisinya sendiri lewat halaman Riwayat Kesehatan di website Orcha,
                            cukup dengan kode pendaftaran <strong>{{ $pendaftaran['kode'] ?? '' }}</strong>.
                        </p>
                    </div>
                @endforelse
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
            <div class="card border-0 shadow-sm rounded-4 mt-3 orcha-kartu-arsip">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <h2 class="fw-bold mb-0 orcha-judul-ikon" style="font-size:1.05rem;color:#64748b">
                            <i class="bi bi-archive"></i> Arsip — sudah digantikan, tidak berangkat
                        </h2>
                        <span class="orcha-lencana-arsip">{{ count($arsip) }} orang</span>
                    </div>

                    <p class="text-muted mb-3" style="font-size:.84rem">
                        Riwayatnya tidak dihapus supaya jejak pendaftaran tetap utuh, tetapi tidak ikut
                        dihitung di ringkasan atas dan tidak perlu disiapkan apa pun untuknya.
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

                        <div class="orcha-arsip-isi">
                            @include('livewire.pages.admin.orcha.partials.kartu-kesehatan', ['peserta' => $peserta])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
