@section('title')
Detail Sewa Kendaraan || lemon
@stop

@php
    $wa = fn ($nomor) => 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $nomor));
    $rp = fn ($angka) => 'Rp ' . number_format((int) $angka, 0, ',', '.');

    $tenggat = ($sewa['jadwal_selesai'] ?? null) ? \Carbon\Carbon::parse($sewa['jadwal_selesai']) : null;
    // Perbandingan kondisi awal-akhir kosong lagi setelah unit diperiksa
    // ulang: keadaan barunya sudah jadi patokan. Rincian yang sudah ditetapkan
    // admin tetap ada, dan itulah yang masih ditagihkan — jadi dipakai sebagai
    // gantinya supaya halaman ini tidak menampilkan denda tanpa sebab.
    $kerusakan = $sewa['kerusakan_baru'] ?: ($sewa['rincian_denda'] ?? []);
@endphp

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">

        @if ($galat)
            <div class="alert alert-warning border-0 rounded-4">{{ $galat }}</div>
        @endif

        @if (empty($sewa))
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-truck"></i></div>
                    <p class="text-muted mb-3">Data penyewaan tidak bisa ditampilkan.</p>
                    <a href="{{ route('admin.orcha.penyewaan') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <a href="{{ route('admin.orcha.penyewaan') }}" class="orcha-tautan-balik mb-2">
                                <i class="bi bi-arrow-left"></i> Semua penyewaan
                            </a>
                            <h1 class="gradient-text fw-bold mb-1" style="font-size:1.6rem">{{ $sewa['nama'] }}</h1>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="orcha-kode">{{ $sewa['kode'] }}</span>
                                <span class="text-muted" style="font-size:.82rem">
                                    {{ data_get($sewa, 'kendaraan.nama') }} ·
                                    {{ data_get($sewa, 'kendaraan.transmisi') }} ·
                                    {{ $sewa['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ $wa($sewa['whatsapp']) }}" target="_blank" rel="noopener"
                                class="orcha-btn orcha-btn-wa">
                                <i class="bi bi-whatsapp"></i> Hubungi Penyewa
                            </a>

                            {{-- Nota akhir bisa diunduh kapan saja: sebelum unit kembali
                                 isinya estimasi sewa, sesudahnya total termasuk denda. --}}
                            <a href="{{ route('admin.orcha.penyewaan.kwitansi', $penyewaanId) }}"
                                class="orcha-btn orcha-btn-lembut" title="Kwitansi / nota akhir sewa">
                                <i class="bi bi-receipt"></i>
                                {{ $sewa['dikembalikan_pada'] ? 'Nota Akhir' : 'Kwitansi' }}
                            </a>

                            <select class="form-select" style="width:auto"
                                wire:change="ubahStatus($event.target.value)">
                                @foreach ($pilihanStatus as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($sewa['status'] === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        @foreach ([
                            ['Biaya sewa', $rp($sewa['estimasi_biaya']), '', 'bi-cash'],
                            ['Total denda', $rp($sewa['total_denda'] ?? 0), ($sewa['total_denda'] ?? 0) > 0 ? 'sisa' : '', 'bi-exclamation-triangle'],
                            ['Total tagihan', $rp($sewa['total_tagihan'] ?? 0), '', 'bi-receipt'],
                            ['Keterlambatan', ($sewa['terlambat'] ?? false)
                                ? floor(($sewa['terlambat_menit'] ?? 0) / 60) . ' jam ' . (($sewa['terlambat_menit'] ?? 0) % 60) . ' mnt'
                                : 'Tepat waktu', ($sewa['terlambat'] ?? false) ? 'sisa' : 'lunas', 'bi-hourglass-split'],
                        ] as [$label, $nilai, $kelas, $ikon])
                            <div class="col-6 col-lg-3">
                                <div class="orcha-ringkas {{ $kelas }}">
                                    <div class="orcha-label-kecil"><i class="bi {{ $ikon }}"></i> {{ $label }}</div>
                                    <div class="angka">{{ $nilai }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Unit sudah kembali, sistem punya usulan denda, tetapi tidak ada
                 satu rupiah pun yang ditetapkan. Tanpa penanda ini, angka di
                 lembar serah terima terlihat berbeda dengan nota dan halaman
                 ini — dan yang mengira ada yang rusak adalah admin, padahal
                 yang kurang cuma satu tekanan tombol Simpan. --}}
            @php
                $usulanBelumDitetapkan = ($sewa['dikembalikan_pada'] ?? null)
                    && ($sewa['total_denda'] ?? 0) === 0
                    && (($sewa['denda_keterlambatan_usulan'] ?? 0) + ($sewa['denda_kerusakan_usulan'] ?? 0)) > 0;
            @endphp

            @if ($usulanBelumDitetapkan)
                <div class="alert alert-warning border-0 rounded-4 d-flex gap-3 align-items-start">
                    <i class="bi bi-exclamation-circle-fill fs-4"></i>
                    <div>
                        <strong>Ada usulan denda yang belum ditetapkan</strong>
                        <div style="font-size:.88rem" class="mt-1">
                            Sistem menghitung
                            @if (($sewa['denda_keterlambatan_usulan'] ?? 0) > 0)
                                keterlambatan {{ $rp($sewa['denda_keterlambatan_usulan']) }}
                            @endif
                            @if (($sewa['denda_keterlambatan_usulan'] ?? 0) > 0 && ($sewa['denda_kerusakan_usulan'] ?? 0) > 0)
                                dan
                            @endif
                            @if (($sewa['denda_kerusakan_usulan'] ?? 0) > 0)
                                kerusakan {{ $rp($sewa['denda_kerusakan_usulan']) }}
                            @endif
                            — tapi yang tercatat masih Rp 0, jadi nota dan tagihan belum memuatnya.
                        </div>
                        <div style="font-size:.85rem" class="mt-1">
                            Buka <strong>Serah terima</strong> di daftar penyewaan, periksa angkanya,
                            lalu simpan agar ikut ditagihkan.
                        </div>
                    </div>
                </div>
            @endif

            @if ($kerusakan)
                <div class="alert alert-danger border-0 rounded-4 d-flex gap-3 align-items-start">
                    <i class="bi bi-tools fs-4"></i>
                    <div>
                        <strong>Kerusakan baru selama masa sewa</strong>
                        <div style="font-size:.88rem" class="mt-1">
                            @foreach ($kerusakan as $satu)
                                <div>
                                    {{ $satu['bagian'] }}
                                    {{-- Baris ketetapan lama belum tentu menyimpan perubahan
                                         kondisinya; namanya saja sudah cukup menjelaskan. --}}
                                    @if (($satu['dari'] ?? null) && ($satu['jadi'] ?? null))
                                        : {{ strtolower($satu['dari']) }} →
                                        <strong>{{ strtolower($satu['jadi']) }}</strong>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div style="font-size:.82rem" class="mt-1">
                            Lecet yang sudah ada sejak unit diserahkan tidak ikut terhitung di sini.
                        </div>
                    </div>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-12 col-lg-7">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-calendar-range text-primary"></i> Jadwal &amp; Lokasi
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    {{-- Keterangan unitnya disebut di sini juga.

                                         Admin sebelumnya membaca "HiAce Commuter" sementara
                                         penyewa memegang surat yang menyebut merek, tipe, tahun,
                                         kapasitas, dan siapa menanggung BBM. Ketika penyewa
                                         menelepon menanyakan salah satunya, admin tidak punya
                                         jawabannya di layar yang sedang dibukanya. --}}
                                    ['Unit', $sewa['kendaraan']['sebutan'] ?? $sewa['kendaraan']['nama'] ?? '—'],
                                    ['Kapasitas', ($sewa['kendaraan']['kapasitas'] ?? null)
                                        ? $sewa['kendaraan']['kapasitas'].' penumpang'
                                            .(($sewa['kendaraan']['kursi_total'] ?? null) !== ($sewa['kendaraan']['kapasitas'] ?? null)
                                                ? ' ('.$sewa['kendaraan']['kursi_total'].' kursi)' : '')
                                        : '—'],
                                    ['Keterangan sopir', $sewa['kendaraan']['sopir_label'] ?? '—'],
                                    ['BBM, tol, parkir', $sewa['kendaraan']['operasional_label'] ?? '—'],
                                    ['Wilayah', ($sewa['luar_kota'] ?? false) ? 'Luar kota' : 'Dalam kota'],
                                    ['Mulai', ($sewa['tanggal_mulai'] ?? null)
                                        ? \Carbon\Carbon::parse($sewa['tanggal_mulai'])->locale('id')->translatedFormat('l, d F Y') . ' · ' . $sewa['jam_mulai']
                                        : '—'],
                                    ['Ditunggu kembali', $tenggat ? $tenggat->locale('id')->translatedFormat('l, d F Y') . ' · ' . $tenggat->format('H:i') : '—'],
                                    ['Durasi', $sewa['durasi_label']],
                                    ['Kembali pada', ($sewa['dikembalikan_pada'] ?? null)
                                        ? \Carbon\Carbon::parse($sewa['dikembalikan_pada'])->locale('id')->translatedFormat('d F Y, H:i')
                                        : 'Belum kembali'],
                                    {{-- Sebutannya mengikuti moda sewanya. Pada sewa bersopir
                                         unitnya tidak diserahkan ke penyewa, jadi "lokasi
                                         pengantaran unit" menyebut hal yang tidak terjadi. --}}
                                    [($sewa['dengan_sopir'] ?? false) ? 'Titik penjemputan' : 'Lokasi pengantaran',
                                        $sewa['lokasi_antar'] ?: '—'],
                                    [($sewa['dengan_sopir'] ?? false) ? 'Tujuan perjalanan' : 'Lokasi pengembalian',
                                        (($sewa['dengan_sopir'] ?? false) ? ($sewa['tujuan'] ?? null) : $sewa['lokasi_kembali']) ?: '—'],
                                ] as [$label, $nilai])
                                    <div class="col-12 col-md-6">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        <div class="fw-bold">{{ $nilai }}</div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($sewa['catatan'])
                                <div class="mt-3 p-3 rounded-3 bg-light">
                                    <div class="orcha-label-kecil">Catatan dari penyewa</div>
                                    <div style="font-size:.9rem">{{ $sewa['catatan'] }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ============ PEMERIKSAAN FISIK ============
                         Dua kolom berdampingan, bukan dua daftar terpisah: yang
                         dicari admin adalah barisnya yang BERUBAH, dan itu hanya
                         terlihat kalau keduanya sejajar. --}}
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-clipboard-check text-primary"></i> Pemeriksaan Fisik
                            </h2>

                            @if (empty($sewa['kondisi_awal']) && empty($sewa['kondisi_akhir']))
                                <p class="text-muted mb-0" style="font-size:.9rem">
                                    Belum ada pemeriksaan yang dicatat. Isi lewat tombol
                                    <strong>Serah terima</strong> di daftar penyewaan.
                                </p>
                            @else
                                <div class="orcha-gulung">
                                    <table class="table table-sm align-middle orcha-tabel mb-0">
                                        <thead>
                                            <tr>
                                                <th>Bagian</th>
                                                <th>Saat diserahkan</th>
                                                <th>Saat kembali</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bagianPeriksa as $kunci => $label)
                                                @php
                                                    $urutan = array_keys($pilihanKondisi);
                                                    $awal = data_get($sewa, "kondisi_awal.$kunci");
                                                    $akhir = data_get($sewa, "kondisi_akhir.$kunci");
                                                    $memburuk = $awal && $akhir
                                                        && array_search($akhir, $urutan, true) > array_search($awal, $urutan, true);
                                                @endphp
                                                <tr class="{{ $memburuk ? 'table-danger' : '' }}">
                                                    <td class="small">{{ $label }}</td>
                                                    <td class="small">{{ $pilihanKondisi[$awal] ?? '—' }}</td>
                                                    <td class="small fw-semibold">
                                                        {{ $pilihanKondisi[$akhir] ?? '—' }}
                                                        @if ($memburuk)
                                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-5">

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-person-vcard text-primary"></i> Penyewa
                            </h2>

                            <div class="d-flex flex-column gap-3">
                                @foreach ([
                                    ['Nama', $sewa['nama'], null],
                                    ['WhatsApp', $sewa['whatsapp'], $wa($sewa['whatsapp'])],
                                    ['Email', $sewa['email'] ?: '—', $sewa['email'] ? 'mailto:' . $sewa['email'] : null],
                                    ['Jaminan dititipkan', $sewa['jaminan'] ?: 'Belum dicatat', null],
                                ] as [$label, $nilai, $tautan])
                                    <div>
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        @if ($tautan)
                                            <a href="{{ $tautan }}" target="_blank" rel="noopener"
                                                class="fw-bold text-decoration-none">{{ $nilai }}</a>
                                        @else
                                            <div class="fw-bold">{{ $nilai }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3 p-lg-4">
                            <h2 class="fw-bold mb-3 orcha-judul-ikon" style="font-size:1.05rem">
                                <i class="bi bi-speedometer2 text-primary"></i> Serah Terima
                            </h2>

                            <div class="row g-3">
                                @foreach ([
                                    ['Diserahkan', ($sewa['diserahkan_pada'] ?? null)
                                        ? \Carbon\Carbon::parse($sewa['diserahkan_pada'])->locale('id')->translatedFormat('d M Y, H:i')
                                        : '—'],
                                    ['Kembali', ($sewa['dikembalikan_pada'] ?? null)
                                        ? \Carbon\Carbon::parse($sewa['dikembalikan_pada'])->locale('id')->translatedFormat('d M Y, H:i')
                                        : '—'],
                                    ['Kilometer awal', $sewa['kilometer_awal'] ?: '—'],
                                    ['Kilometer akhir', $sewa['kilometer_akhir'] ?: '—'],
                                    ['BBM saat diserahkan', $sewa['bahan_bakar_awal'] ?: '—'],
                                    ['BBM saat kembali', $sewa['bahan_bakar_akhir'] ?: '—'],
                                ] as [$label, $nilai])
                                    <div class="col-6">
                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                        <div class="fw-bold" style="font-size:.9rem">{{ $nilai }}</div>
                                    </div>
                                @endforeach
                            </div>

                            @if (($sewa['total_denda'] ?? 0) > 0)
                                <div class="orcha-alasan orcha-alasan-tinggi mt-3">
                                    <span class="orcha-label-kecil" style="color:#b91c1c">Rincian denda</span>
                                    @foreach ([
                                        ['Keterlambatan', $sewa['denda_keterlambatan'] ?? 0],
                                        ['Kerusakan', $sewa['denda_kerusakan'] ?? 0],
                                        ['Lain-lain', $sewa['denda_lain'] ?? 0],
                                    ] as [$label, $nilai])
                                        @if ($nilai > 0)
                                            <div class="d-flex justify-content-between mt-1">
                                                <span>{{ $label }}</span>
                                                <span class="fw-bold">{{ $rp($nilai) }}</span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if ($sewa['catatan_denda'])
                                        <div class="mt-2" style="font-size:.82rem">{{ $sewa['catatan_denda'] }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
