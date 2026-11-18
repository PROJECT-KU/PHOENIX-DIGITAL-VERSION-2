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
    {{-- Bahasa rupa dsb-* milik dasbor lemon — patokan yang sama dengan layar
         Pesanan Toko dan daftar sewa. Kelas orcha-* disisakan untuk yang memang
         khas Orcha: chip cakupan, medan berkotak, rincian tagihan, lencana
         kondisi, dan popup pilihan WhatsApp. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.orcha.partials.gaya')

    <style>
        /* Gaya khusus layar Detail Sewa. Inline, bukan lewat Vite: public/build
           masuk .gitignore dan tidak ikut ter-deploy. */

        /* Pemilih keadaan duduk sebaris dengan tombol di kepala, jadi lebarnya
           mengikuti isinya — bukan melar memenuhi baris seperti .dsb-isian
           bawaan yang dipakai di dalam borang. */
        .sw-detail .sw-status { width: auto; min-width: 150px; flex: 0 0 auto; }

        /* Medan berkotak Orcha dipakai di dalam kartu dsb. Disamakan tingginya
           supaya baris yang isinya pendek tidak lebih cebol dari tetangganya. */
        .sw-detail .orcha-medan { height: 100%; }

        /* Empat ubin angka jadi 2x2 di bawah 1400px.

           Ubin layar ini memuat rupiah penuh (Rp 10.000.000), bukan cacahan
           satu-dua digit seperti di dasbor. Berempat sebaris pada 1280px,
           tiap kartu menyisakan sekitar 116px untuk nilainya — dan karena
           .dsb-stat ber-overflow:hidden, angkanya terpotong di tengah
           ("Rp 10.000.00") tanpa satu pun tanda bahwa ada digit yang hilang.
           Terukur pada 1280px; pada 1440px keempatnya masih muat. */
        @media (max-width: 1399.98px) {
            .sw-detail .dsb-rak > .dsb-stat { grid-column: span 6; }
        }
    </style>

    <div class="dsb sw-detail">

        @if ($galat)
            <div class="dsb-galat">{{ $galat }}</div>
        @endif

        @if (empty($sewa))
            <div class="dsb-kartu">
                <div class="dsb-kartu-isi dsb-kosong">
                    <span class="dsb-kosong-ikon"><i class="bi bi-truck"></i></span>
                    <p class="dsb-kosong-judul">Data penyewaan tidak bisa ditampilkan</p>
                    <p class="dsb-kosong-ket">
                        Nomornya mungkin sudah dihapus, atau alamatnya disalin dari catatan lama.
                    </p>
                    <a href="{{ route('admin.orcha.penyewaan') }}" class="dsb-tombol is-lembut mt-3">
                        <i class="bi bi-arrow-left"></i><span>Kembali ke daftar</span>
                    </a>
                </div>
            </div>
        @else

            {{-- ============ KEPALA ============
                 Tombol tindakannya kembali ke kanan kepala, tempat yang sama
                 dengan Pesanan Toko dan Detail Pendaftaran Trip.

                 Dulu mereka memang dipindah KELUAR dari kepala, dan alasannya
                 benar untuk kerangka yang lama: di kartu polos mereka berhimpit
                 dengan nama penyewa, dan layout global memaksa setiap
                 .form-select setinggi 48px sehingga benda yang paling jarang
                 disentuh justru paling besar. Kerangka dsb menghapus kedua
                 sebab itu — .dsb-hero memang dibuat menampung sebaris tombol,
                 dan .dsb-isian tidak kena paksaan 48px.

                 Yang tersisa dari pemisahan itu hanyalah akibatnya: separuh
                 kanan kepala menganga di sepanjang layar. --}}
            <header class="dsb-hero">
                <div class="dsb-hero-teks">
                    <h1 class="dsb-salam">{{ $sewa['nama'] }}</h1>
                    <p class="dsb-hero-ket">
                        <span class="d-block">
                            <span class="orcha-kode">{{ $sewa['kode'] }}</span>
                            {{ data_get($sewa, 'kendaraan.nama') }} ·
                            {{ data_get($sewa, 'kendaraan.transmisi') }} ·
                            {{ $sewa['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }} ·
                            {{ ($sewa['luar_kota'] ?? false) ? 'luar kota' : 'dalam kota' }}
                        </span>
                    </p>

                    @include('livewire.pages.admin.orcha.penyewaan.partials.termasuk')
                </div>

                <div class="dsb-hero-aksi">
                    <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                        class="dsb-tombol is-lembut" style="--ikon: #64748b">
                        <i class="bi bi-arrow-left"></i><span>Kembali</span>
                    </a>

                    {{-- Membuka pilihan pesan, bukan langsung melompat ke
                         percakapan kosong. Tautannya pun bukan wa.me: lewat
                         wa.me, emoji dan sebagian tanda baca sampai dalam
                         keadaan rusak tanpa admin pernah tahu. --}}
                    <button type="button" class="dsb-tombol is-aman orcha-aksi-sewa"
                        onclick="orchaBukaLembar('pilihanWa')">
                        <i class="bi bi-whatsapp"></i><span>Hubungi Penyewa</span>
                    </button>

                    {{-- Hanya muncul bila vendornya sudah dicatat: tombol yang
                         membuka daftar kosong mengajari admin bahwa menekannya
                         tidak ada gunanya, dan sesudah itu ia tidak ditekan lagi
                         pada hari ia berisi. --}}
                    @if (data_get($sewa, 'vendor.kontak'))
                        <button type="button" class="dsb-tombol is-lembut orcha-aksi-sewa" style="--ikon: #b45309"
                            onclick="orchaBukaLembar('pilihanWaVendor')">
                            <i class="bi bi-building"></i><span>Hubungi Vendor</span>
                        </button>
                    @endif

                    {{-- Nota akhir bisa diunduh kapan saja: sebelum unit kembali
                         isinya estimasi sewa, sesudahnya total termasuk denda. --}}
                    <a href="{{ route('admin.orcha.penyewaan.kwitansi', $penyewaanId) }}"
                        class="dsb-tombol is-lembut orcha-aksi-sewa" style="--ikon: #0284c7"
                        title="Kwitansi / nota akhir sewa">
                        <i class="bi bi-receipt"></i>
                        <span>{{ $sewa['dikembalikan_pada'] ? 'Nota Akhir' : 'Kwitansi' }}</span>
                    </a>

                    {{-- Jalan ke jejak pesanan INI, bukan ke seluruh jejak:
                         kodenya dibawa di alamat, jadi yang terbuka sudah
                         tersaring. --}}
                    <a href="{{ route('admin.orcha.jejak-audit', ['cari' => $sewa['kode']]) }}"
                        class="dsb-tombol is-lembut orcha-aksi-sewa" style="--ikon: #7c3aed"
                        title="Siapa mengubah apa pada pesanan ini">
                        <i class="bi bi-clock-history"></i><span>Jejak</span>
                    </a>

                    {{-- Warnanya mengikuti keadaan pesanan, sama seperti di
                         daftar sewa, supaya yang dilihat admin di kedua layar
                         benda yang sama. --}}
                    <select class="dsb-isian sw-status orcha-status-ringkas"
                        data-status="{{ $sewa['status'] }}"
                        wire:change="ubahStatus($event.target.value)">
                        @foreach ($pilihanStatus as $kunci => $label)
                            <option value="{{ $kunci }}" @selected($sewa['status'] === $kunci)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </header>

            {{-- Enam angka yang menentukan percakapan dengan penyewa — dan,
                 sejak modal dicatat, juga percakapan dengan diri sendiri soal
                 apakah sewa ini pantas diambil lagi lain kali.

                 Barisnya k-4, tiga per baris: dua baris penuh tanpa lubang.
                 Dengan k-3 yang lama, enam ubin berarti empat di baris pertama
                 dan dua di baris kedua, menyisakan separuh baris kosong. --}}
            @php
                $adaModal = ($sewa['modal'] ?? null) !== null;
                $untung = $sewa['untung'] ?? null;
            @endphp
            <div class="dsb-rak dsb-bagian">
                @foreach ([
                    ['Biaya sewa', $rp($sewa['estimasi_biaya']), 'bi-cash', '#0f766e'],

                    /*
                     | Modal dan untung yang BELUM DICATAT disebut apa adanya,
                     | bukan ditulis "Rp 0". Nol berarti gratis, dan untung
                     | sebesar seluruh harga sewa adalah angka yang akan
                     | dipercaya karena tampil seperti hasil hitungan.
                     */
                    ['Modal ke vendor', $adaModal ? $rp($sewa['modal']) : 'Belum dicatat',
                        'bi-wallet2', $adaModal ? '#b45309' : '#94a3b8'],
                    ['Untung kotor', $untung === null
                        ? 'Belum diketahui'
                        : $rp($untung).(($sewa['margin_persen'] ?? null) !== null
                            ? ' · '.$sewa['margin_persen'].'%' : ''),
                        'bi-graph-up-arrow',
                        $untung === null ? '#94a3b8' : ($untung < 0 ? '#b91c1c' : '#047857')],

                    ['Total denda', $rp($sewa['total_denda'] ?? 0), 'bi-exclamation-triangle',
                        ($sewa['total_denda'] ?? 0) > 0 ? '#b45309' : '#64748b'],
                    ['Total tagihan', $rp($sewa['total_tagihan'] ?? 0), 'bi-receipt', '#4f46e5'],
                    ['Keterlambatan', ($sewa['terlambat'] ?? false)
                        ? floor(($sewa['terlambat_menit'] ?? 0) / 60) . ' jam ' . (($sewa['terlambat_menit'] ?? 0) % 60) . ' mnt'
                        : 'Tepat waktu', 'bi-hourglass-split',
                        ($sewa['terlambat'] ?? false) ? '#b91c1c' : '#047857'],
                ] as [$label, $nilai, $ikon, $warna])
                    <article class="dsb-stat k-4" style="--c: {{ $warna }}">
                        <span class="dsb-ikon"><i class="bi {{ $ikon }}"></i></span>
                        <span class="dsb-stat-label">{{ $label }}</span>
                        <span class="dsb-stat-nilai">{{ $nilai }}</span>

                        {{-- Pembanding, hanya di ubin untung dan hanya bila ADA
                             pembandingnya. Persentase sendirian tidak bisa dinilai:
                             28,6% itu bagus atau pas-pasan tergantung biasanya berapa.

                             Diam saat rata-ratanya belum ada — menampilkan "0%"
                             sebagai patokan justru membuat sewa apa pun terbaca
                             luar biasa. --}}
                        @if ($label === 'Untung kotor'
                            && $marginRataRata !== null
                            && ($sewa['margin_persen'] ?? null) !== null)
                            @php
                                $selisih = round($sewa['margin_persen'] - $marginRataRata, 1);
                            @endphp
                            <span class="dsb-stat-ket">
                                <i class="bi {{ $selisih >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                <span>
                                    {{ $selisih >= 0 ? '+' : '' }}{{ $selisih }} poin dari rata-rata
                                    ({{ $marginRataRata }}%)
                                </span>
                            </span>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- ============ VENDOR & MODAL ============
                 Orcha belum punya kendaraan sendiri: unitnya dicarikan ke
                 vendor begitu ada yang memesan. Sampai sekarang kenyataan itu
                 tidak ada di layar mana pun — admin yang ditanya "unit ini
                 dari siapa" harus bertanya ke orang, dan saat unit kembali
                 lecet tidak ada catatan siapa yang perlu dikabari. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-building"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Vendor &amp; Modal</h2>
                                <span class="dsb-kartu-sub">
                                    Dari mana unitnya dicarikan, dan berapa yang dibayarkan ke sana.
                                </span>
                            </div>
                        </div>

                        @if (! ($sewa['milik_sendiri'] ?? false) && ! $borangVendor)
                            <button type="button" class="dsb-tombol is-lembut" style="--ikon: #b45309"
                                wire:click="bukaBorangVendor">
                                <i class="bi bi-pencil-square"></i><span>Catat</span>
                            </button>
                        @endif
                    </div>

                    <div class="dsb-kartu-isi">
                        @if ($sewa['milik_sendiri'] ?? false)
                            {{-- Unit milik sendiri tidak punya vendor, dan layar ini
                                 tidak boleh terus menagih admin mengisinya. --}}
                            <div class="orcha-alasan orcha-alasan-tenang mb-0">
                                <i class="bi bi-house-check"></i>
                                Unit ini milik Orcha sendiri, jadi tidak ada vendor yang perlu dicatat.
                            </div>
                        @elseif ($borangVendor)
                            <div class="row g-3">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-semibold">Vendor pemasok unit</label>
                                    <select class="form-select" wire:model="vendorId">
                                        <option value="" @selected($this->vendorId === '')>— belum ditentukan —</option>
                                        {{-- Dikelompokkan, bukan disaring: vendor transportasi
                                             yang jenisnya terlanjur diisi "Lainnya" tetap bisa
                                             dipilih, cuma tidak lagi berebut tempat dengan yang
                                             memang armada. --}}
                                        @php
                                            $vArmada = collect($daftarVendor)->where('armada', true);
                                            $vLain = collect($daftarVendor)->where('armada', false);
                                        @endphp
                                        @if ($vArmada->isNotEmpty())
                                            <optgroup label="Vendor armada">
                                                @foreach ($vArmada as $v)
                                                    <option value="{{ $v['id'] }}" @selected((string) $this->vendorId === (string) $v['id'])>
                                                        {{ $v['nama'] }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if ($vLain->isNotEmpty())
                                            <optgroup label="Vendor lain">
                                                @foreach ($vLain as $v)
                                                    <option value="{{ $v['id'] }}" @selected((string) $this->vendorId === (string) $v['id'])>
                                                        {{ $v['nama'] }}{{ ! empty($v['jenis']) ? ' · '.$v['jenis'] : '' }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                    <div class="form-text">
                                        Belum ada di daftar? Tambahkan dulu lewat menu Vendor.
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-semibold">Modal dibayarkan ke vendor</label>
                                    <div class="orcha-rupiah">
                                        <input type="text" inputmode="numeric" class="form-control orcha-uang"
                                            wire:model.blur="modal" placeholder="Kosongkan bila belum tahu">
                                    </div>
                                    {{-- Kosong dan nol dibedakan, dan bedanya disebut
                                         supaya admin tidak mengisi 0 untuk "belum tahu". --}}
                                    <div class="form-text">
                                        Dikosongkan berarti <strong>belum dicatat</strong>; diisi 0 berarti
                                        unitnya benar-benar gratis.
                                    </div>
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-2">
                                    <button type="button" class="dsb-tombol is-utama"
                                        wire:click="simpanVendor" wire:target="simpanVendor"
                                        wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="simpanVendor">
                                            <i class="bi bi-save"></i><span>Simpan</span>
                                        </span>
                                        <span wire:loading wire:target="simpanVendor">
                                            <span class="spinner-border spinner-border-sm me-2"
                                                role="status" aria-hidden="true"></span>Menyimpan…
                                        </span>
                                    </button>
                                    <button type="button" class="dsb-tombol is-lembut"
                                        wire:click="tutupBorangVendor">
                                        <i class="bi bi-x-lg"></i><span>Batal</span>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="dsb-data">
                                @include('livewire.pages.admin.orcha.partials.baris-data', [
                                    'label' => 'Vendor pemasok',
                                    'nilai' => data_get($sewa, 'vendor.nama'),
                                    'tautan' => null,
                                ])
                                @include('livewire.pages.admin.orcha.partials.baris-data', [
                                    'label' => 'Kontak vendor',
                                    'nilai' => data_get($sewa, 'vendor.kontak'),
                                    'tautan' => null,
                                ])
                                @include('livewire.pages.admin.orcha.partials.baris-data', [
                                    'label' => 'Modal dibayarkan',
                                    'nilai' => $adaModal ? $rp($sewa['modal']) : null,
                                    'tautan' => null,
                                ])
                                @include('livewire.pages.admin.orcha.partials.baris-data', [
                                    'label' => 'Untung kotor',
                                    'nilai' => $untung === null ? null : $rp($untung),
                                    'tautan' => null,
                                ])
                            </div>

                            @unless ($sewa['vendor_ditentukan'] ?? false)
                                {{-- Pesanan yang unitnya tidak pernah dicarikan berakhir
                                     dibatalkan di hari H. Disebut di sini, bukan hanya
                                     dihitung di tab daftar. --}}
                                <div class="orcha-alasan orcha-alasan-sedang mt-3 mb-0 d-flex gap-2 align-items-start">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <div>
                                        <strong>Unitnya belum dicarikan ke vendor mana pun.</strong>
                                        <div style="font-size:.88rem" class="mt-1">
                                            Selama ini belum dicatat, pesanan ini muncul di tab
                                            <strong>Belum ada vendor</strong> di daftar sewa, dan untungnya
                                            tidak bisa dihitung.
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            @if ($untung !== null && $untung < 0)
                                <div class="orcha-alasan orcha-alasan-tinggi mt-3 mb-0 d-flex gap-2 align-items-start">
                                    <i class="bi bi-graph-down-arrow"></i>
                                    <div>
                                        <strong>Modalnya lebih besar daripada harga sewanya.</strong>
                                        <div style="font-size:.88rem" class="mt-1">
                                            Bisa memang begitu — unit pengganti dadakan sering lebih mahal.
                                            Disebut di sini supaya tidak lewat tanpa disadari.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
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
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #4f46e5"><i class="bi bi-truck-front"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Unit &amp; Layanan</h2>
                                <span class="dsb-kartu-sub">Kendaraan yang dipesan, dan apa saja yang tercakup harganya.</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">

                    <div class="dsb-data">
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
                            @include('livewire.pages.admin.orcha.partials.baris-data', [
                                'label' => $label, 'nilai' => $nilai, 'tautan' => null,
                            ])
                        @endforeach
                    </div>
                    </div>
                </div>

            {{-- ============ JADWAL & LOKASI ============ --}}
                <div class="dsb-kartu k-6">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0284c7"><i class="bi bi-calendar-range"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Jadwal &amp; Lokasi</h2>
                                <span class="dsb-kartu-sub">Kapan unit berangkat, kapan ditunggu kembali, dan dari mana.</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">

                    <div class="dsb-data">
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
                            @include('livewire.pages.admin.orcha.partials.baris-data', [
                                'label' => $label, 'nilai' => $nilai, 'tautan' => null,
                            ])
                        @endforeach
                    </div>

                    </div>
                </div>
            </div>

            {{-- ============ PENYEWA ============ --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #0f766e"><i class="bi bi-person-vcard"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Penyewa</h2>
                                <span class="dsb-kartu-sub">Siapa yang memesan, dan apa yang ditahan selama masa sewa.</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">

                    <div class="dsb-data">
                        @foreach ([
                            ['Nama', $sewa['nama'] ?? null, null],
                            {{-- Lewat api.whatsapp.com juga, bukan wa.me — satu aturan
                                 untuk seluruh halaman, supaya tidak ada satu tautan
                                 tersisa yang diam-diam berperilaku lain. --}}
                            ['WhatsApp', $sewa['whatsapp'] ?? null, $this->tautanWa('')],
                            ['Email', $sewa['email'] ?: null, $sewa['email'] ? 'mailto:'.$sewa['email'] : null],
                            ['Jaminan dititipkan', $sewa['jaminan'] ?: null, null],
                        ] as [$label, $nilai, $tautan])
                            @include('livewire.pages.admin.orcha.partials.baris-data', [
                                'label' => $label, 'nilai' => $nilai, 'tautan' => $tautan,
                            ])
                        @endforeach
                    </div>
                    @if ($sewa['catatan'])
                        <div class="orcha-alasan orcha-alasan-tenang mt-3">
                            <span class="orcha-label-kecil" style="color: var(--orc-tinta)">
                                <i class="bi bi-chat-left-text"></i> Catatan dari penyewa
                            </span>
                            <div class="mt-1">{{ $sewa['catatan'] }}</div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            {{-- ============ SERAH TERIMA ============
                 Dua kolom berdampingan, persis seperti di lembar pengisiannya:
                 kiri keadaan waktu diserahkan, kanan waktu kembali. Sebagai enam
                 medan berderet, pasangan yang seharusnya dibandingkan terputus —
                 dan membandingkan itulah satu-satunya guna angka-angka ini. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #b45309"><i class="bi bi-speedometer2"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Serah Terima</h2>
                                <span class="dsb-kartu-sub">Keadaan unit saat diserahkan dan saat kembali.</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">

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
                        <span class="orcha-label-kecil" style="color: var(--orc-tinta)">
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
            </div>

            {{-- ============ PEMERIKSAAN FISIK ============
                 Dua kolom berdampingan, bukan dua daftar terpisah: yang dicari
                 admin adalah barisnya yang BERUBAH, dan itu hanya terlihat kalau
                 keduanya sejajar. --}}
            <div class="dsb-rak dsb-bagian">
                <div class="dsb-kartu k-12">
                    <div class="dsb-kartu-kepala">
                        <div class="dsb-kartu-kepala-kiri">
                            <span class="dsb-ikon is-kecil" style="--c: #7c3aed"><i class="bi bi-clipboard-check"></i></span>
                            <div>
                                <h2 class="dsb-kartu-judul">Pemeriksaan Fisik</h2>
                                <span class="dsb-kartu-sub">Bagian yang <strong>memburuk</strong> selama masa sewa ditandai merah.</span>
                            </div>
                        </div>
                    </div>
                    <div class="dsb-kartu-isi">

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
                            <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
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
                        <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
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

    {{-- ============ PILIHAN PESAN KE VENDOR ============

         Terpisah dari popup penyewa, bukan digabung jadi satu daftar panjang.
         Keduanya menuju NOMOR yang berbeda, dan satu salah kirim berarti
         harga modal terkirim ke penyewa. --}}
    @if (! empty($sewa) && data_get($sewa, 'vendor.kontak'))
        <div class="orcha-lembar" id="pilihanWaVendor" hidden>
            <div class="orcha-lembar-tirai" onclick="orchaTutupLembar('pilihanWaVendor')"></div>

            <div class="orcha-lembar-isi" role="dialog" aria-modal="true" aria-label="Kirim pesan ke vendor">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <div class="fw-bold orcha-judul-ikon" style="font-size:1.05rem">
                            <i class="bi bi-building" style="color:#b45309"></i>
                            Kirim pesan ke {{ data_get($sewa, 'vendor.nama') }}
                        </div>
                        <div class="text-muted" style="font-size:.8rem">
                            Nama dan nomor penyewa tidak ikut dikirim — vendor cukup tahu unit,
                            waktu, dan titik jemputnya.
                        </div>
                    </div>

                    <button type="button" class="orcha-hapus-baris" aria-label="Tutup"
                        onclick="orchaTutupLembar('pilihanWaVendor')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                @foreach ($this->pilihanPesanVendor() as $pilihan)
                    <a class="orcha-pilihan-wa"
                        href="{{ $this->tautanWaVendor($pilihan['polos']) }}"
                        data-wa-pesan="{{ $pilihan['pesan'] }}"
                        target="_blank" rel="noopener">
                        <span class="orcha-ikon {{ $pilihan['rupa'] }}">
                            <i class="bi {{ $pilihan['ikon'] }}"></i>
                        </span>

                        <span class="flex-grow-1">
                            <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
                                {{ $pilihan['judul'] }}
                            </span>
                            <span class="d-block text-muted" style="font-size:.78rem">
                                {{ $pilihan['ringkas'] }}
                            </span>
                        </span>

                        <i class="bi bi-box-arrow-up-right text-muted"></i>
                    </a>
                @endforeach

                <a class="orcha-pilihan-wa orcha-pilihan-wa-polos"
                    href="{{ $this->tautanWaVendor('') }}"
                    target="_blank" rel="noopener">
                    <span class="orcha-ikon orcha-ikon-netral">
                        <i class="bi bi-chat-dots"></i>
                    </span>

                    <span class="flex-grow-1">
                        <span class="d-block fw-bold" style="font-size:.92rem;color: var(--orc-tinta)">
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
