@section('title')
Detail Sewa Kendaraan || lemon
@stop

@php
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
                                {{ $sewa['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }} ·
                                {{ ($sewa['luar_kota'] ?? false) ? 'luar kota' : 'dalam kota' }}
                            </span>
                        </div>

                        @include('livewire.pages.admin.orcha.penyewaan.partials.termasuk')
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

            {{-- ============ TINDAKAN ============
                 Ketiganya dulu berdesakan di pojok kanan kepala, berhimpit dengan
                 nama penyewa dan kodenya. Bentuknya memang seperti bilah penyaring,
                 dan di situlah kelirunya: dua di antaranya bukan penyaring melainkan
                 tindakan — satu membuka WhatsApp, satu mengunduh berkas — dan yang
                 ketiga MENGUBAH keadaan pesanan, bukan menyaring apa pun.

                 Dipisah ke kartunya sendiri dengan latar yang sedikit berbeda,
                 supaya terbaca sebagai bilah perkakas dan bukan kartu keterangan
                 yang menuntut dibaca.

                 Tingginya disamakan 34px. Sebelumnya isian statusnya memakai
                 .form-select polos, dan layout memaksa SETIAP .form-select setinggi
                 48px — jadi benda yang paling besar di baris itu justru yang paling
                 jarang disentuh. Warnanya kini mengikuti keadaan pesanan, sama
                 seperti di daftar sewa, supaya yang dilihat admin di kedua layar
                 benda yang sama. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 orcha-kartu-tindakan">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="orcha-label-kecil">
                                <i class="bi bi-lightning-charge"></i> Tindakan
                            </div>
                            <div class="text-muted" style="font-size:.8rem">
                                Hubungi penyewa, unduh notanya, atau ubah keadaan pesanannya.
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            {{-- Membuka pilihan pesan, bukan langsung melompat ke
                                 percakapan kosong.

                                 Tautannya pun tidak lagi wa.me: lewat wa.me, emoji
                                 dan sebagian tanda baca sering tidak terbaca di
                                 WhatsApp Web maupun Desktop — pesan sampai dalam
                                 keadaan rusak, dan admin tidak pernah tahu karena di
                                 layarnya sendiri tampak baik-baik saja. Pola yang
                                 sama sudah dipakai halaman pendaftaran open trip. --}}
                            <button type="button" class="orcha-btn orcha-btn-wa orcha-aksi-sewa"
                                onclick="orchaBukaLembar('pilihanWa')">
                                <i class="bi bi-whatsapp"></i> Hubungi Penyewa
                            </button>

                            {{-- Nota akhir bisa diunduh kapan saja: sebelum unit kembali
                                 isinya estimasi sewa, sesudahnya total termasuk denda. --}}
                            <a href="{{ route('admin.orcha.penyewaan.kwitansi', $penyewaanId) }}"
                                class="orcha-btn orcha-btn-lembut orcha-aksi-sewa" title="Kwitansi / nota akhir sewa">
                                <i class="bi bi-receipt"></i>
                                {{ $sewa['dikembalikan_pada'] ? 'Nota Akhir' : 'Kwitansi' }}
                            </a>

                            <select class="form-select orcha-status-ringkas"
                                data-status="{{ $sewa['status'] }}"
                                wire:change="ubahStatus($event.target.value)">
                                @foreach ($pilihanStatus as $kunci => $label)
                                    <option value="{{ $kunci }}" @selected($sewa['status'] === $kunci)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
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
                {{-- Nadanya diturunkan menyamai peringatan di lembar serah terima.
                     Sebagai pita merah/kuning pekat setinggi kartu, ia berteriak
                     lebih keras daripada angka tagihan di atasnya — padahal yang
                     dikabarkan cuma satu tombol Simpan yang belum ditekan. --}}
                <div class="orcha-alasan orcha-alasan-sedang d-flex gap-2 align-items-start mb-4">
                    <i class="bi bi-exclamation-circle-fill"></i>
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
                            Buka <a href="{{ route('admin.orcha.penyewaan.serah-terima', $penyewaanId) }}"
                                wire:navigate class="fw-bold text-decoration-none">lembar serah terima</a>,
                            periksa angkanya, lalu simpan agar ikut ditagihkan.
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============ KERUSAKAN BARU ============
                 Tiap bagian jadi barisnya sendiri: namanya di kiri, perubahan
                 kondisinya di kanan sebagai dua lencana.

                 Sebelumnya tiga kalimat berderet — "Bodi samping kiri : baik →
                 lecet / minor" — yang harus dibaca satu per satu sampai habis.
                 Nama bagian, kondisi awal, dan kondisi akhir bercampur dalam satu
                 baris tanpa kolom, jadi mata tidak bisa menyusurinya ke bawah;
                 padahal yang dicari admin cuma satu hal: bagian mana yang
                 memburuk, dan jadi apa. --}}
            @if ($kerusakan)
                <div class="orcha-alasan orcha-alasan-tinggi mb-4">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="orcha-label-kecil" style="color:#b91c1c">
                            <i class="bi bi-tools"></i> Kerusakan baru selama masa sewa
                        </span>
                        <span class="orcha-rusak-jumlah">{{ count($kerusakan) }} bagian</span>
                    </div>

                    <div class="orcha-rusak">
                        @foreach ($kerusakan as $satu)
                            <div class="orcha-rusak-baris">
                                <span class="orcha-rusak-bagian">{{ $satu['bagian'] }}</span>

                                {{-- Baris ketetapan lama belum tentu menyimpan perubahan
                                     kondisinya; namanya saja sudah cukup menjelaskan. --}}
                                @if (($satu['dari'] ?? null) && ($satu['jadi'] ?? null))
                                    <span class="orcha-rusak-ubah">
                                        <span class="orcha-cip-kondisi awal">{{ $satu['dari'] }}</span>
                                        <span class="panah"><i class="bi bi-arrow-right"></i></span>
                                        <span class="orcha-cip-kondisi akhir">{{ $satu['jadi'] }}</span>
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-2" style="font-size:.8rem">
                        Lecet yang sudah ada sejak unit diserahkan tidak ikut terhitung di sini.
                    </div>
                </div>
            @endif

            {{-- ============ UNIT & LAYANAN ============
                 Dipisah dari jadwalnya. Sebelumnya keduabelas keterangan — merek
                 unit, kapasitas, aturan sopir, pos biaya, tanggal, jam, lokasi —
                 berderet dalam satu kartu bernama "Jadwal & Lokasi", padahal
                 separuhnya bukan jadwal dan bukan lokasi. Admin yang mencari satu
                 hal harus memindai semuanya.

                 Bentuknya mengikuti lembar serah terima: kartu per urusan, kepala
                 berikon, dan medan berkotak. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-truck-front"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Unit &amp; Layanan</div>
                            <div class="orcha-bagian-sub">
                                Kendaraan yang dipesan, dan apa saja yang tercakup harganya.
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        {{-- Keterangan unitnya disebut selengkap surat penyewa.

                             Admin sebelumnya membaca "HiAce Commuter" sementara penyewa
                             memegang surat yang menyebut merek, tipe, tahun, kapasitas,
                             dan siapa menanggung BBM. Ketika penyewa menelepon menanyakan
                             salah satunya, admin tidak punya jawabannya di layar yang
                             sedang dibukanya. --}}
                        @foreach ([
                            ['Unit', $sewa['kendaraan']['sebutan'] ?? $sewa['kendaraan']['nama'] ?? null],
                            ['Transmisi', $sewa['kendaraan']['transmisi'] ?? null],
                            ['Kapasitas', ($sewa['kendaraan']['kapasitas'] ?? null)
                                ? $sewa['kendaraan']['kapasitas'].' penumpang'
                                    .(($sewa['kendaraan']['kursi_total'] ?? null) !== ($sewa['kendaraan']['kapasitas'] ?? null)
                                        ? ' ('.$sewa['kendaraan']['kursi_total'].' kursi)' : '')
                                : null],
                            ['Wilayah', ($sewa['luar_kota'] ?? false) ? 'Luar kota' : 'Dalam kota'],
                            ['Keterangan sopir', $sewa['kendaraan']['sopir_label'] ?? null],
                            ['BBM, tol, parkir', $sewa['kendaraan']['operasional_label'] ?? null],
                        ] as [$label, $nilai])
                            <div class="col-6 col-lg-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => $label, 'nilai' => $nilai, 'tautan' => null,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============ JADWAL & LOKASI ============ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-calendar-range"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Jadwal &amp; Lokasi</div>
                            <div class="orcha-bagian-sub">
                                Kapan unit berangkat, kapan ditunggu kembali, dan dari mana.
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        @foreach ([
                            ['Mulai', ($sewa['tanggal_mulai'] ?? null)
                                ? \Carbon\Carbon::parse($sewa['tanggal_mulai'])->locale('id')->translatedFormat('l, d F Y').' · '.$sewa['jam_mulai']
                                : null],
                            ['Ditunggu kembali', $tenggat
                                ? $tenggat->locale('id')->translatedFormat('l, d F Y').' · '.$tenggat->format('H:i')
                                : null],
                            ['Durasi', $sewa['durasi_label'] ?? null],
                            ['Kembali pada', ($sewa['dikembalikan_pada'] ?? null)
                                ? \Carbon\Carbon::parse($sewa['dikembalikan_pada'])->locale('id')->translatedFormat('d F Y, H:i')
                                : 'Belum kembali'],
                            {{-- Sebutannya mengikuti moda sewanya. Pada sewa bersopir unitnya
                                 tidak diserahkan ke penyewa, jadi "lokasi pengantaran unit"
                                 menyebut hal yang tidak terjadi. --}}
                            [($sewa['dengan_sopir'] ?? false) ? 'Titik penjemputan' : 'Lokasi pengantaran',
                                $sewa['lokasi_antar'] ?: null],
                            [($sewa['dengan_sopir'] ?? false) ? 'Tujuan perjalanan' : 'Lokasi pengembalian',
                                (($sewa['dengan_sopir'] ?? false) ? ($sewa['tujuan'] ?? null) : $sewa['lokasi_kembali']) ?: null],
                        ] as [$label, $nilai])
                            <div class="col-6 col-lg-4">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => $label, 'nilai' => $nilai, 'tautan' => null,
                                ])
                            </div>
                        @endforeach
                    </div>

                    @if ($sewa['catatan'])
                        <div class="orcha-alasan orcha-alasan-tenang mt-3">
                            <span class="orcha-label-kecil" style="color:#0f2d4a">
                                <i class="bi bi-chat-left-text"></i> Catatan dari penyewa
                            </span>
                            <div class="mt-1">{{ $sewa['catatan'] }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============ PENYEWA ============ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-person-vcard"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Penyewa</div>
                            <div class="orcha-bagian-sub">
                                Siapa yang memesan, dan apa yang ditahan selama masa sewa.
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        @foreach ([
                            ['Nama', $sewa['nama'] ?? null, null],
                            {{-- Lewat api.whatsapp.com juga, bukan wa.me — satu aturan
                                 untuk seluruh halaman, supaya tidak ada satu tautan
                                 tersisa yang diam-diam berperilaku lain. --}}
                            ['WhatsApp', $sewa['whatsapp'] ?? null, $this->tautanWa('')],
                            ['Email', $sewa['email'] ?: null, $sewa['email'] ? 'mailto:'.$sewa['email'] : null],
                            ['Jaminan dititipkan', $sewa['jaminan'] ?: null, null],
                        ] as [$label, $nilai, $tautan])
                            <div class="col-6 col-lg-3">
                                @include('livewire.pages.admin.orcha.partials.medan', [
                                    'label' => $label, 'nilai' => $nilai, 'tautan' => $tautan,
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============ SERAH TERIMA ============
                 Dua kolom berdampingan, persis seperti di lembar pengisiannya:
                 kiri keadaan waktu diserahkan, kanan waktu kembali. Sebagai enam
                 medan berderet, pasangan yang seharusnya dibandingkan terputus —
                 dan membandingkan itulah satu-satunya guna angka-angka ini. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-speedometer2"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Serah Terima</div>
                            <div class="orcha-bagian-sub">
                                Keadaan unit saat diserahkan dan saat kembali.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach ([
                            ['ya', 'Saat diserahkan', 'bi-box-arrow-right', '', [
                                ['Waktu', ($sewa['diserahkan_pada'] ?? null)
                                    ? \Carbon\Carbon::parse($sewa['diserahkan_pada'])->locale('id')->translatedFormat('d M Y, H:i')
                                    : null],
                                ['Kilometer', $sewa['kilometer_awal'] ? number_format((int) $sewa['kilometer_awal'], 0, ',', '.') : null],
                                ['Bahan bakar', $sewa['bahan_bakar_awal'] ?: null],
                            ]],
                            ['tidak', 'Saat kembali', 'bi-box-arrow-in-left', 'kembali', [
                                ['Waktu', ($sewa['dikembalikan_pada'] ?? null)
                                    ? \Carbon\Carbon::parse($sewa['dikembalikan_pada'])->locale('id')->translatedFormat('d M Y, H:i')
                                    : null],
                                ['Kilometer', $sewa['kilometer_akhir'] ? number_format((int) $sewa['kilometer_akhir'], 0, ',', '.') : null],
                                ['Bahan bakar', $sewa['bahan_bakar_akhir'] ?: null],
                            ]],
                        ] as [$penanda, $judulKolom, $ikon, $ragam, $medan])
                            <div class="col-12 col-lg-6">
                                <div class="orcha-keadaan {{ $ragam }}">
                                    <div class="orcha-keadaan-kepala">
                                        <i class="bi {{ $ikon }}"></i> {{ $judulKolom }}
                                    </div>
                                    <div class="row g-2">
                                        @foreach ($medan as $urut => [$label, $nilai])
                                            <div class="{{ $urut === 0 ? 'col-12' : 'col-6' }}">
                                                @include('livewire.pages.admin.orcha.partials.medan', [
                                                    'label' => $label, 'nilai' => $nilai, 'tautan' => null,
                                                ])
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Jarak tempuh dihitungkan; kedua angkanya sudah ada di layar. --}}
                        @php
                            $kmAwal = (int) ($sewa['kilometer_awal'] ?? 0);
                            $kmAkhir = (int) ($sewa['kilometer_akhir'] ?? 0);
                        @endphp
                        @if ($kmAwal > 0 && $kmAkhir > $kmAwal)
                            <div class="col-12">
                                <span class="orcha-selisih">
                                    <i class="bi bi-signpost-split"></i>
                                    {{ number_format($kmAkhir - $kmAwal, 0, ',', '.') }} km ditempuh
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- ============ RINCIAN TAGIHAN ============
                         Satu daftar untuk seluruh tagihannya: biaya sewa, tiap denda,
                         lalu uang yang sudah diterima sebagai pengurang.

                         Sebelumnya halaman ini berhenti di kartu "Total tagihan
                         Rp 2.150.000" — benar sebagai angka, tetapi tidak menyebut
                         bahwa penyewa sudah membayar uang muka. Admin yang membacakan
                         angka itu saat menagih menagih lebih dari yang seharusnya, dan
                         penyewa mendengar dirinya diminta membayar DP dua kali. --}}
                    @php
                        $tagihanSewa = $sewa['tagihan'] ?? [];
                        $diterimaSewa = (int) ($tagihanSewa['sudah'] ?? 0);
                        $totalSewa = (int) ($sewa['total_tagihan'] ?? 0);
                        $sisaSewa = max(0, $totalSewa - $diterimaSewa);
                    @endphp

                    <div class="orcha-alasan orcha-alasan-tenang mt-3">
                        <span class="orcha-label-kecil" style="color:#0f2d4a">
                            <i class="bi bi-receipt"></i> Rincian tagihan
                        </span>

                        <table class="orcha-rincian-sewa mt-1">
                            @forelse ($sewa['rincian_estimasi'] ?? [] as $pos)
                                <tr>
                                    <td>
                                        {{ $pos['label'] }}
                                        @if (! empty($pos['keterangan']))
                                            <span class="catatan">{{ $pos['keterangan'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $rp($pos['jumlah']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td>
                                        Biaya sewa
                                        <span class="catatan">{{ $sewa['durasi_label'] ?? '' }}</span>
                                    </td>
                                    <td>{{ $rp($sewa['estimasi_biaya'] ?? 0) }}</td>
                                </tr>
                            @endforelse

                            @foreach ([
                                ['Denda keterlambatan', $sewa['denda_keterlambatan'] ?? 0,
                                    ($sewa['terlambat'] ?? false)
                                        ? (int) floor(($sewa['terlambat_menit'] ?? 0) / 60).' jam '
                                            .(($sewa['terlambat_menit'] ?? 0) % 60).' menit lewat tenggat'
                                        : null],
                                ['Denda kerusakan', $sewa['denda_kerusakan'] ?? 0,
                                    collect($kerusakan)->pluck('bagian')->filter()->implode(', ') ?: null],
                                ['Denda lain', $sewa['denda_lain'] ?? 0, null],
                            ] as [$label, $nilai, $keterangan])
                                @if ($nilai > 0)
                                    <tr>
                                        <td style="color:#b91c1c">
                                            {{ $label }}
                                            @if ($keterangan)
                                                <span class="catatan">{{ $keterangan }}</span>
                                            @endif
                                        </td>
                                        <td style="color:#b91c1c">{{ $rp($nilai) }}</td>
                                    </tr>
                                @endif
                            @endforeach

                            <tr class="jumlah">
                                <td>Total tagihan</td>
                                <td>{{ $rp($totalSewa) }}</td>
                            </tr>

                            {{-- Hanya yang bukti transfernya sudah DITERIMA. Yang masih
                                 menunggu dicek belum uang, dan mengurangkannya berarti
                                 mengakui pembayaran berdasarkan gambar yang belum
                                 diperiksa siapa pun. --}}
                            @if ($diterimaSewa > 0)
                                @forelse ($sewa['pembayaran_diterima'] ?? [] as $bayar)
                                    <tr>
                                        <td style="color:#1f7a44">
                                            {{ $bayar['label'] }}
                                            @if ((int) ($bayar['berkas'] ?? 1) > 1)
                                                <span class="catatan">{{ $bayar['berkas'] }} bukti transfer</span>
                                            @endif
                                        </td>
                                        <td style="color:#1f7a44">&minus; {{ $rp($bayar['nominal']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td style="color:#1f7a44">Sudah dibayar</td>
                                        <td style="color:#1f7a44">&minus; {{ $rp($diterimaSewa) }}</td>
                                    </tr>
                                @endforelse

                                <tr class="jumlah">
                                    <td>{{ $sisaSewa <= 0 ? 'Lunas' : 'Sisa yang harus dibayar' }}</td>
                                    <td class="{{ $sisaSewa <= 0 ? '' : 'sisa' }}">
                                        {{ $sisaSewa <= 0 ? '—' : $rp($sisaSewa) }}
                                    </td>
                                </tr>
                            @endif
                        </table>

                        @if ($sewa['catatan_denda'])
                            <div class="mt-2" style="font-size:.82rem">{{ $sewa['catatan_denda'] }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============ PEMERIKSAAN FISIK ============
                 Dua kolom berdampingan, bukan dua daftar terpisah: yang dicari
                 admin adalah barisnya yang BERUBAH, dan itu hanya terlihat kalau
                 keduanya sejajar. --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-clipboard-check"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Pemeriksaan Fisik</div>
                            <div class="orcha-bagian-sub">
                                Bagian yang <strong>memburuk</strong> selama masa sewa ditandai merah.
                            </div>
                        </div>
                    </div>

                    @if (empty($sewa['kondisi_awal']) && empty($sewa['kondisi_akhir']))
                        <div class="orcha-alasan orcha-alasan-tenang mb-0">
                            Belum ada pemeriksaan yang dicatat. Isi lewat
                            <a href="{{ route('admin.orcha.penyewaan.serah-terima', $penyewaanId) }}" wire:navigate
                                class="fw-bold text-decoration-none">lembar serah terima</a>.
                        </div>
                    @else
                        <div class="orcha-gulung">
                            <table class="table table-sm align-middle orcha-tabel mb-0">
                                <thead>
                                    <tr>
                                        <th>Bagian</th>
                                        <th style="width:28%">Saat diserahkan</th>
                                        <th style="width:28%">Saat kembali</th>
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
                                                    <span class="orcha-lencana-awas ms-1">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> kerusakan baru
                                                    </span>
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
        @endif
    </div>

    {{-- ============ PILIHAN PESAN WHATSAPP ============

         Popup berdiri sendiri, bukan modal Bootstrap: kelasnya memang terpasang,
         tetapi JS-nya tidak pernah dimuat di repo ini, dan aset Vite pun tidak
         ikut ter-deploy. Tombol ber-data-bs-toggle karenanya diam saja di server.

         Pesannya disusun di komponen, bukan di sini: nominal, tenggat, dan aturan
         dendanya berasal dari rujukan Orcha yang sama dengan yang dipakai kwitansi,
         jadi angka di percakapan tidak pernah berbeda dari angka di berkas. --}}
    @if (! empty($sewa))
        <div class="orcha-lembar" id="pilihanWa" hidden>
            <div class="orcha-lembar-tirai" onclick="orchaTutupLembar('pilihanWa')"></div>

            <div class="orcha-lembar-isi" role="dialog" aria-modal="true" aria-label="Kirim pesan WhatsApp">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="fw-bold orcha-judul-ikon" style="font-size:1.05rem">
                            <i class="bi bi-whatsapp" style="color:#25d366"></i>
                            Kirim pesan ke {{ $sewa['nama'] ?? 'penyewa' }}
                        </div>
                        <div class="text-muted" style="font-size:.8rem">
                            Pesannya sudah terisi lengkap dengan angka dan jadwalnya — tinggal periksa lalu kirim.
                        </div>
                    </div>

                    <button type="button" class="orcha-hapus-baris" aria-label="Tutup"
                        onclick="orchaTutupLembar('pilihanWa')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                @foreach ($this->pilihanPesan() as $pilihan)
                    {{-- href memuat versi POLOS; data-wa-pesan memuat yang berpenanda.

                         Skrip di partial salin-wa merakit emojinya di peramban lalu
                         menyusun ulang tautannya saat diklik — emoji tidak pernah ikut
                         melewati respons server, dan justru di perjalanan itulah ia
                         berubah jadi tanda tanya. Bila skripnya tidak sempat jalan,
                         yang terkirim tetap kalimat utuh tanpa emoji. --}}
                    <a class="orcha-pilihan-wa"
                        href="{{ $this->tautanWa($pilihan['polos']) }}"
                        data-wa-pesan="{{ $pilihan['pesan'] }}"
                        target="_blank" rel="noopener">
                        <span class="orcha-ikon {{ $pilihan['rupa'] }}">
                            <i class="bi {{ $pilihan['ikon'] }}"></i>
                        </span>

                        <span class="flex-grow-1">
                            <span class="d-block fw-bold" style="font-size:.92rem;color:#0f2d4a">
                                {{ $pilihan['judul'] }}
                            </span>
                            <span class="d-block text-muted" style="font-size:.78rem">
                                {{ $pilihan['ringkas'] }}
                            </span>
                        </span>

                        <i class="bi bi-box-arrow-up-right text-muted"></i>
                    </a>
                @endforeach

                {{-- Selalu ada, di bawah pilihan yang sudah terisi: kadang yang perlu
                     disampaikan memang tidak ada di daftar mana pun. --}}
                <a class="orcha-pilihan-wa orcha-pilihan-wa-polos"
                    href="{{ $this->tautanWa('') }}"
                    target="_blank" rel="noopener">
                    <span class="orcha-ikon orcha-ikon-netral">
                        <i class="bi bi-chat-dots"></i>
                    </span>

                    <span class="flex-grow-1">
                        <span class="d-block fw-bold" style="font-size:.92rem;color:#0f2d4a">
                            Buka percakapan kosong
                        </span>
                        <span class="d-block text-muted" style="font-size:.78rem">
                            Menulis sendiri, tanpa pesan siap pakai
                        </span>
                    </span>

                    <i class="bi bi-box-arrow-up-right text-muted"></i>
                </a>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.salin-wa')
    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
