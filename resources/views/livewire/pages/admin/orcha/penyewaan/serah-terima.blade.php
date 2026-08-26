@section('title')
Serah Terima Kendaraan || lemon
@stop

@php
    // Berkas jaminan tersimpan di Orcha, jadi jalurnya dilengkapi asal servernya.
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;
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
                    <div class="empty-state-icon-wrapper mx-auto mb-2"><i class="bi bi-clipboard-check"></i></div>
                    <p class="text-muted mb-3">Lembar serah terima ini tidak bisa dibuka.</p>
                    <a href="{{ route('admin.orcha.penyewaan') }}" class="orcha-btn orcha-btn-utama">
                        <i class="bi bi-arrow-left"></i> Kembali ke daftar
                    </a>
                </div>
            </div>
        @else

            {{-- ============ LEMBAR SERAH TERIMA ============
                 Diisi dua kali untuk satu unit: saat diserahkan, dan saat kembali.
                 Bentuknya sengaja sama persis di kedua kolom supaya yang
                 dibandingkan adalah bagian yang sama — "baret di pintu kanan"
                 yang ditulis sebagai kalimat bebas tidak pernah bisa
                 dibandingkan, dan di situlah sengketa dengan penyewa bermula. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h4 class="gradient-text fw-bold mb-1">Serah Terima Kendaraan</h4>
                            <span class="text-muted small">
                                {{ $sewa['kode'] ?? '' }} · {{ data_get($sewa, 'kendaraan.nama') }} ·
                                {{ $sewa['nama'] ?? '' }}
                            </span>

                            {{-- Dipasang di lembar ini juga, bukan cuma di halaman
                                 detail. Saat unit kembali dan BBM-nya tinggal 1/4,
                                 pertanyaannya langsung: itu tanggungan siapa. Admin
                                 yang harus berpindah halaman untuk mengeceknya
                                 cenderung menebak. --}}
                            @include('livewire.pages.admin.orcha.penyewaan.partials.termasuk')
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                class="orcha-btn orcha-btn-lembut">
                                <i class="bi bi-arrow-left"></i> Kembali ke daftar
                            </a>
                            <a href="{{ route('admin.orcha.penyewaan.detail', $serahTerimaUntuk) }}" wire:navigate
                                class="orcha-btn orcha-btn-lembut">
                                <i class="bi bi-truck-front"></i> Detail sewa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tenggat & keterlambatan ditaruh paling atas: itu yang menentukan
                 ada tidaknya denda. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-3">
                        @php
                            $tenggat = ($sewa['jadwal_selesai'] ?? null)
                                ? \Carbon\Carbon::parse($sewa['jadwal_selesai'])
                                : null;
                        @endphp
                        <div class="col-12 col-md-4">
                            <div class="orcha-ringkas">
                                <div class="orcha-label-kecil"><i class="bi bi-calendar-check"></i> Ditunggu kembali</div>
                                <div class="angka" style="font-size:1.05rem">
                                    {{ $tenggat ? $tenggat->locale('id')->translatedFormat('d M Y, H:i') : '—' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="orcha-ringkas {{ ($sewa['terlambat'] ?? false) ? 'sisa' : 'lunas' }}">
                                <div class="orcha-label-kecil"><i class="bi bi-hourglass-split"></i> Keterlambatan</div>
                                <div class="angka" style="font-size:1.05rem">
                                    @if ($sewa['terlambat'] ?? false)
                                        {{ (int) floor(($sewa['terlambat_menit'] ?? 0) / 60) }} jam
                                        {{ ($sewa['terlambat_menit'] ?? 0) % 60 }} menit
                                    @else
                                        Tepat waktu
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="orcha-ringkas">
                                {{-- Sewa bersopir tidak "dikembalikan" ke mana pun: yang
                                     berguna dilihat sekilas adalah tujuannya. --}}
                                <div class="orcha-label-kecil">
                                    <i class="bi {{ ($sewa['dengan_sopir'] ?? false) ? 'bi-signpost-2' : 'bi-geo-alt' }}"></i>
                                    {{ ($sewa['dengan_sopir'] ?? false) ? 'Tujuan' : 'Dikembalikan di' }}
                                </div>
                                <div class="angka" style="font-size:1.05rem">
                                    {{ (($sewa['dengan_sopir'] ?? false) ? ($sewa['tujuan'] ?? null) : $sewa['lokasi_kembali']) ?: '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ POSISI PEMBAYARAN ============
                 Unit diserahkan di loket, dan yang paling sering ditanya di sana
                 justru tidak ada di lembar ini sama sekali: uangnya sudah masuk
                 atau belum.

                 Perkara yang membingungkan: status pesanan hanya maju ke "DP
                 Masuk" setelah bukti transfernya DITERIMA admin, bukan saat
                 penyewa mengunggahnya. Aturan itu memang disengaja — kalau
                 tidak, siapa pun bisa memajukan statusnya sendiri hanya dengan
                 mengunggah gambar. Akibatnya penyewa yang benar-benar sudah
                 mentransfer tetap berstatus "Baru", dan admin di loket menyimpulkan
                 orangnya belum bayar. Padahal buktinya ada, hanya belum dibuka.

                 Maka keduanya disebut terpisah, dengan namanya masing-masing. --}}
            @php
                $tagihan = $sewa['tagihan'] ?? [];
                $menunggu = $sewa['menunggu_dicek'] ?? ['nominal' => 0, 'berkas' => 0];
                $adaMenunggu = (int) ($menunggu['berkas'] ?? 0) > 0;
                $diterima = (int) ($tagihan['sudah'] ?? 0);
                $rp = fn ($angka) => 'Rp '.number_format((int) $angka, 0, ',', '.');

                /*
                 | Angka uang dihitung SEKALI di sini, lalu dipakai kartu Posisi
                 | Pembayaran di atas dan rincian tagihan di bawah.
                 |
                 | Dulu keduanya menghitung sendiri-sendiri dari sumber berbeda:
                 | yang atas dari angka tersimpan di Orcha, yang bawah dari isian
                 | denda yang sedang diketik. Hasilnya dua angka untuk satu
                 | tagihan yang sama, di layar yang sama — dan admin yang
                 | membacakannya ke penyewa harus menebak mana yang benar.
                 |
                 | Yang dipakai adalah angka DI LAYAR, karena itulah yang sedang
                 | diputuskan admin. Bila berbeda dengan yang tersimpan, lencana
                 | "belum tersimpan" sudah mengabarkannya.
                 */
                $angka = fn ($nilai) => (int) preg_replace('/\D/', '', (string) $nilai);
                $totalDenda = $angka($dendaKeterlambatan) + $angka($dendaKerusakan) + $angka($dendaLain);
                $biayaSewa = (int) ($sewa['estimasi_biaya'] ?? 0);
                $totalTagihan = $biayaSewa + $totalDenda;

                // Uang yang sudah benar-benar diterima mengurangi tagihannya.
                // Sebelumnya rinciannya berhenti di total, seolah penyewa belum
                // membayar sepeser pun — padahal DP-nya sudah masuk.
                $sisaBayar = max(0, $totalTagihan - $diterima);
                $lunasSemua = $totalTagihan > 0 && $sisaBayar <= 0;

                // Yang tersimpan di Orcha vs yang sedang tampil di layar. Angka
                // usulan terisi sendiri saat lembar ini dibuka, dan selama belum
                // disimpan, nota serta halaman detail masih memakai angka lama.
                $dendaTersimpan = (int) ($sewa['total_denda'] ?? 0);
                $belumTersimpan = $totalDenda !== $dendaTersimpan;
            @endphp

            @if ($tagihan)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="orcha-bagian-kepala">
                            <div class="orcha-bagian-nomor"><i class="bi bi-wallet2"></i></div>
                            <div>
                                <div class="orcha-bagian-judul">Posisi Pembayaran</div>
                                <div class="orcha-bagian-sub">
                                    Keadaan uangnya sebelum unit diserahkan.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="orcha-ringkas {{ $tagihan['lunas'] ? 'lunas' : '' }}">
                                    <div class="orcha-label-kecil">
                                        <i class="bi bi-check2-circle"></i> Sudah diterima
                                    </div>
                                    <div class="angka" style="font-size:1.05rem">{{ $rp($diterima) }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="orcha-ringkas {{ $lunasSemua ? 'lunas' : 'sisa' }}">
                                    <div class="orcha-label-kecil">
                                        <i class="bi bi-hourglass-bottom"></i> Sisa tagihan
                                    </div>
                                    <div class="angka" style="font-size:1.05rem">
                                        {{ $lunasSemua ? 'Lunas' : $rp($sisaBayar) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="orcha-ringkas">
                                    <div class="orcha-label-kecil">
                                        <i class="bi bi-tag"></i> Status pesanan
                                    </div>
                                    <div class="angka" style="font-size:1.05rem">
                                        {{ $sewa['status_label'] ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            {{-- Inilah keterangan yang dicari saat penyewa bersikeras
                                 sudah mentransfer padahal statusnya masih "Baru". --}}
                            @if ($adaMenunggu)
                                <div class="col-12">
                                    <div class="alert alert-warning border-0 rounded-3 mb-0 d-flex gap-2 align-items-start"
                                        style="font-size:.85rem">
                                        <i class="bi bi-clock-history"></i>
                                        <div>
                                            <strong>{{ $rp($menunggu['nominal']) }} sudah dilaporkan penyewa, tapi buktinya belum dicek.</strong>
                                            Selama belum diterima, angkanya tidak dihitung dan status pesanannya
                                            memang belum maju — bukan berarti penyewa belum membayar.
                                            <div class="mt-1">
                                                <a href="{{ route('admin.orcha.pembayaran') }}" wire:navigate
                                                    class="orcha-tautan-wa">
                                                    <i class="bi bi-box-arrow-up-right"></i>
                                                    Buka Bukti Pembayaran{{ (int) $menunggu['berkas'] > 1 ? ' ('.$menunggu['berkas'].' berkas)' : '' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($diterima <= 0)
                                <div class="col-12">
                                    <div class="alert alert-danger border-0 rounded-3 mb-0 d-flex gap-2 align-items-start"
                                        style="font-size:.85rem">
                                        <i class="bi bi-exclamation-octagon-fill"></i>
                                        <div>
                                            <strong>Belum ada pembayaran yang masuk.</strong>
                                            Tidak ada bukti transfer yang menunggu dicek. Pastikan dulu sebelum
                                            kunci diserahkan.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============ KEADAAN UNIT ============
                 Dulu keenam isiannya berderet menyamping — waktu, waktu, kilometer
                 awal, kilometer akhir, BBM awal, BBM akhir — sehingga pasangan
                 "saat diserahkan" dan "saat kembali" terputus dan yang sejajar di
                 layar justru dua hal yang tidak dibandingkan.

                 Sekarang bentuknya sama dengan tabel pemeriksaan di bawah: kiri
                 keadaan waktu diserahkan, kanan waktu kembali. --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-speedometer2"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Keadaan Unit</div>
                            <div class="orcha-bagian-sub">
                                Kolom kiri diisi saat unit diserahkan, kolom kanan saat unit kembali.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="orcha-keadaan">
                                <div class="orcha-keadaan-kepala">
                                    <i class="bi bi-box-arrow-right"></i> Saat diserahkan
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Waktu diserahkan</label>
                                        <input type="datetime-local" class="form-control" wire:model="diserahkanPada">
                                        {{-- Keterangan sepadan dengan kolom sebelah. Tanpa ini
                                             baris kilometer di kedua kolom tidak sejajar, dan
                                             dua kolom yang gunanya dibandingkan justru tidak
                                             bisa dibaca menyamping. --}}
                                        <div class="form-text">
                                            Diisi saat unit keluar, sebelum kunci diserahkan.
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Kilometer</label>
                                        <input type="number" min="0" class="form-control" wire:model="kilometerAwal">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Bahan bakar</label>
                                        <input type="text" class="form-control" placeholder="Mis. penuh / 1/2"
                                            wire:model="bahanBakarAwal">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="orcha-keadaan kembali">
                                <div class="orcha-keadaan-kepala">
                                    <i class="bi bi-box-arrow-in-left"></i> Saat kembali
                                </div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold">Waktu kembali</label>
                                        <div class="d-flex gap-2">
                                            <input type="datetime-local" class="form-control"
                                                wire:model.live="dikembalikanPada">
                                            {{-- Serah terima dicatat saat unitnya ada di depan admin,
                                                 jadi "sekarang" hampir selalu jawaban yang benar.
                                                 Mengetik sendiri hanya menambah peluang salah ketik —
                                                 dan salah ketik di sini berarti denda yang salah. --}}
                                            <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                                wire:click="kembaliSekarang" title="Isi dengan waktu sekarang">
                                                <i class="bi bi-clock"></i> Sekarang
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            Statusnya otomatis menjadi <strong>Selesai</strong> begitu ini terisi.
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Kilometer</label>
                                        <input type="number" min="0" class="form-control" wire:model.blur="kilometerAkhir">
                                        {{-- Jarak tempuh dihitungkan. Selama ini admin
                                             mengurangkannya sendiri di kepala, padahal kedua
                                             angkanya sudah ada di layar. --}}
                                        @php
                                            $kmAwal = (int) preg_replace('/\D/', '', (string) $kilometerAwal);
                                            $kmAkhir = (int) preg_replace('/\D/', '', (string) $kilometerAkhir);
                                        @endphp
                                        @if ($kmAwal > 0 && $kmAkhir > $kmAwal)
                                            <div class="mt-1">
                                                <span class="orcha-selisih">
                                                    <i class="bi bi-signpost-split"></i>
                                                    {{ number_format($kmAkhir - $kmAwal, 0, ',', '.') }} km ditempuh
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Bahan bakar</label>
                                        <input type="text" class="form-control" placeholder="Mis. 1/4"
                                            wire:model="bahanBakarAkhir">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- ============ JAMINAN ============ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-person-vcard"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Jaminan Penyewa</div>
                            <div class="orcha-bagian-sub">
                                Yang ditahan selama masa sewa, berikut fotonya sebagai bukti.
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Jaminan yang dititipkan</label>
                            <input type="text" class="form-control" placeholder="Mis. KTP asli + fotokopi KK"
                                wire:model="jaminan">
                        </div>

                        {{-- Tulisan "KTP asli" cukup untuk mengingat, tidak cukup untuk
                             membuktikan. Saat unit tidak kembali, yang dibutuhkan adalah
                             gambarnya: nama, alamat, dan nomor yang bisa dibaca. Bisa
                             dipotret langsung lewat kamera ponsel. --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Foto berkas jaminan (KTP/SIM)</label>
                            <div class="d-flex gap-2">
                                {{-- wire:ignore.self supaya isian berkasnya tidak digambar
                                     ulang saat Livewire menyegarkan bagian lain — kalau
                                     digambar ulang, berkas hasil jepretan yang baru
                                     dimasukkan ikut hilang sebelum sempat terunggah. --}}
                                <input type="file" id="orcha-jaminan" class="form-control" accept="image/*"
                                    wire:model="berkasJaminan">

                                {{-- Kamera dibuka sendiri lewat peramban. Isian berkas biasa
                                     hanya membuka pemilih berkas; di laptop tidak ada jalan
                                     ke kamera sama sekali, padahal admin di loket memang
                                     ingin memotret KTP saat itu juga. --}}
                                <button type="button" class="orcha-btn orcha-btn-lembut orcha-btn-kecil"
                                    data-kamera-untuk="orcha-jaminan" title="Ambil foto lewat kamera perangkat">
                                    <i class="bi bi-camera-fill"></i>
                                </button>

                                <button type="button" class="orcha-btn orcha-btn-utama orcha-btn-kecil"
                                    wire:click="simpanJaminan" wire:loading.attr="disabled"
                                    @disabled(! $berkasJaminan)>
                                    <i class="bi bi-upload"></i> Simpan
                                </button>
                            </div>
                            @error('berkasJaminan')
                                <div class="text-danger" style="font-size:.8rem">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                @if ($sewa['berkas_jaminan'] ?? null)
                                    <a href="{{ $tautanBukti($sewa['berkas_jaminan']) }}" target="_blank"
                                        rel="noopener" class="orcha-tautan-wa">
                                        <i class="bi bi-image"></i> Foto tersimpan — klik untuk melihat
                                    </a>
                                @else
                                    Data pribadi; pengunggahannya tercatat di Orcha.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ PEMERIKSAAN FISIK ============ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-clipboard-check"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Pemeriksaan Fisik</div>
                            <div class="orcha-bagian-sub">
                                Yang ditagihkan ke penyewa hanya bagian yang <strong>memburuk</strong> —
                                lecet yang sudah ada sejak awal tidak ikut terhitung.
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="orcha-gulung">
                            <table class="table table-sm align-middle orcha-tabel mb-0">
                                <thead>
                                    <tr>
                                        <th>Bagian</th>
                                        <th style="width:26%">Saat diserahkan</th>
                                        <th style="width:26%">Saat kembali</th>
                                        <th style="width:16%">Perubahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bagianPeriksa as $kunci => $label)
                                        @php
                                            $urutan = array_keys($pilihanKondisi);
                                            $awal = $kondisiAwal[$kunci] ?? 'baik';
                                            $akhir = $kondisiAkhir[$kunci] ?? null;
                                            $memburuk = $akhir !== null
                                                && array_search($akhir, $urutan, true) > array_search($awal, $urutan, true);
                                        @endphp
                                        <tr class="{{ $memburuk ? 'table-danger' : '' }}">
                                            <td class="small">{{ $label }}</td>
                                            <td>
                                                <select class="form-select form-select-sm"
                                                    wire:model="kondisiAwal.{{ $kunci }}">
                                                    @foreach ($pilihanKondisi as $k => $l)
                                                        <option value="{{ $k }}">{{ $l }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm"
                                                    wire:model.live="kondisiAkhir.{{ $kunci }}">
                                                    <option value="">— belum diperiksa —</option>
                                                    @foreach ($pilihanKondisi as $k => $l)
                                                        <option value="{{ $k }}">{{ $l }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                @if ($memburuk)
                                                    <span class="orcha-lencana-awas">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> kerusakan baru
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ DENDA & TAGIHAN ============
                 Angka usulan sistem sudah terisi, tetapi tetap bisa diubah: alasan
                 telat kadang di luar kuasa penyewa, dan yang memutuskan tetap
                 manusia. --}}
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3 p-lg-4">
                    <div class="orcha-bagian-kepala">
                        <div class="orcha-bagian-nomor"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <div class="orcha-bagian-judul">Denda &amp; Tagihan</div>
                            <div class="orcha-bagian-sub">
                                Angka di sini yang dibacakan ke penyewa saat menagih.
                            </div>
                        </div>
                    </div>

                    <div>
                        @if (($sewa['denda_keterlambatan_usulan'] ?? 0) > 0)
                            <div class="alert alert-warning border-0 rounded-3 mt-2" style="font-size:.84rem">
                                Usulan sistem untuk keterlambatan:
                                <strong>Rp {{ number_format($sewa['denda_keterlambatan_usulan'], 0, ',', '.') }}</strong>
                                — {{ config('orcha.denda_sewa.persen_tarif_harian_per_jam', 10) }}% tarif harian per jam,
                                setelah tenggang {{ config('orcha.denda_sewa.tenggang_menit', 30) }} menit.
                                Ubah bila keterlambatannya bukan kesalahan penyewa.
                            </div>
                        @endif

                        {{-- Usulan denda kerusakan dirinci per bagian, bukan satu angka
                             gelondongan: baris inilah yang ditunjukkan ke penyewa saat
                             menagih. Yang dihitung hanya SELISIH kondisinya — unit yang
                             diserahkan sudah lecet lalu kembali rusak tidak ditagih
                             seolah sebelumnya mulus. --}}
                        @if (! empty($sewa['rincian_denda_kerusakan']))
                            <div class="orcha-alasan orcha-alasan-tinggi mt-2">
                                <span class="orcha-label-kecil" style="color:#b91c1c">
                                    {{-- Sesudah unit diperiksa ulang, keadaan barunya jadi
                                         patokan dan tidak ada lagi selisih untuk diusulkan.
                                         Yang tampil sejak itu adalah ketetapan yang sudah
                                         ditagihkan — dan itu perlu disebut apa adanya. --}}
                                    @if ($dariKetetapan)
                                        Denda kerusakan yang sudah ditetapkan
                                    @else
                                        Usulan denda kerusakan — dari hasil pemeriksaan
                                    @endif
                                </span>
                                {{-- Tiap baris bisa disunting. Daftar tarif hanya perkiraan;
                                     harga bengkel berbeda tiap kejadian. Kalau admin hanya
                                     bisa mengubah totalnya, rincian yang ditunjukkan ke
                                     penyewa jadi tidak cocok dengan angka yang ditagih — dan
                                     rincian yang tidak cocok lebih buruk daripada tidak ada
                                     rincian sama sekali. --}}
                                <table class="table table-sm mb-0 mt-1" style="font-size:.82rem">
                                    @foreach ($sewa['rincian_denda_kerusakan'] as $satu)
                                        <tr>
                                            <td class="ps-0 border-0 align-middle">{{ $satu['bagian'] }}</td>
                                            <td class="border-0 text-muted align-middle">
                                                {{ strtolower($satu['dari']) }} → {{ strtolower($satu['jadi']) }}
                                            </td>
                                            <td class="pe-0 border-0" style="width:11rem">
                                                <div class="orcha-rupiah orcha-rupiah-kecil">
                                                    <input type="text" inputmode="numeric"
                                                        class="form-control form-control-sm text-end orcha-uang"
                                                        wire:model.blur="biayaKerusakan.{{ $satu['kunci'] ?? \Illuminate\Support\Str::slug($satu['bagian'], '_') }}">
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="2" class="ps-0 border-0 fw-bold align-middle">Total kerusakan</td>
                                        <td class="pe-0 border-0 text-end fw-bold align-middle">
                                            Rp {{ $dendaKerusakan }}
                                        </td>
                                    </tr>
                                </table>
                                <div class="mt-1" style="font-size:.78rem">
                                    @if ($dariKetetapan)
                                        Angka ini yang sudah tercatat dan ditagihkan ke penyewa.
                                        Masih bisa diperbaiki bila nota bengkelnya berbeda.
                                    @else
                                        Angka awal diambil dari daftar tarif — perkiraan, bukan tagihan.
                                        Sesuaikan tiap baris dengan nota bengkel; totalnya ikut sendiri.
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Denda keterlambatan</label>
                                <div class="orcha-rupiah">
                                    <input type="text" inputmode="numeric" class="form-control orcha-uang"
                                        wire:model.blur="dendaKeterlambatan">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Denda kerusakan</label>
                                <div class="orcha-rupiah">
                                    <input type="text" inputmode="numeric" class="form-control orcha-uang"
                                        wire:model.blur="dendaKerusakan">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Denda lain</label>
                                <div class="orcha-rupiah">
                                    <input type="text" inputmode="numeric" class="form-control orcha-uang"
                                        wire:model.blur="dendaLain">
                                </div>
                            </div>
                            {{-- Total ikut berubah begitu salah satu denda diubah.
                                 Angka inilah yang dibacakan ke penyewa; menjumlahkannya
                                 di kepala sambil orangnya menunggu di loket adalah cara
                                 paling mudah untuk salah. --}}
                            @if ($belumTersimpan)
                                <div class="col-12">
                                    <div class="alert alert-warning border-0 rounded-3 mb-0 d-flex gap-2 align-items-start"
                                        style="font-size:.85rem">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <div>
                                            <strong>Angka di bawah ini belum tersimpan.</strong>
                                            Yang tercatat di Orcha masih
                                            <strong>Rp {{ number_format($dendaTersimpan, 0, ',', '.') }}</strong>,
                                            dan itulah yang dipakai nota serta halaman detail.
                                            Tekan <strong>Simpan Serah Terima</strong> supaya dendanya benar-benar
                                            ditagihkan.
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-12">
                                <div class="orcha-ringkas {{ $totalDenda > 0 ? 'sisa' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <div class="orcha-label-kecil">
                                                <i class="bi bi-calculator"></i> Total tagihan penyewa
                                                @if ($belumTersimpan)
                                                    <span class="orcha-lencana-catat ms-1">belum tersimpan</span>
                                                @endif
                                            </div>
                                            <div class="angka">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</div>
                                        </div>
                                    </div>

                                    {{-- Satu daftar untuk seluruh tagihannya: biaya sewa
                                         dirinci baris demi baris, lalu tiap denda di
                                         bawahnya, ditutup satu total.

                                         Sebelumnya angka besar itu berdiri sendiri dengan
                                         keterangan "Biaya sewa + denda" di pojok — benar,
                                         tetapi tidak menjawab apa pun. Admin yang ditanya
                                         penyewa "kok segitu?" harus membuka tiga kotak
                                         berbeda di layar untuk menyusun jawabannya.

                                         Biaya sewanya bacaan, bukan isian: ditetapkan saat
                                         penyewa memesan dan tidak diubah dari sini. Yang
                                         boleh disunting di lembar ini hanya dendanya. --}}
                                    <div class="orcha-alasan orcha-alasan-tenang mt-2">
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
                                                    <td>Rp {{ number_format((int) $pos['jumlah'], 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                {{-- Pesanan lama, dibuat sebelum perinciannya
                                                     disimpan: satu baris seperti sedia kala. --}}
                                                <tr>
                                                    <td>
                                                        Biaya sewa
                                                        <span class="catatan">
                                                            {{ $sewa['durasi_label'] ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td>Rp {{ number_format((int) ($sewa['estimasi_biaya'] ?? 0), 0, ',', '.') }}</td>
                                                </tr>
                                            @endforelse

                                            {{-- Denda yang nol tidak ditampilkan: daftar penuh
                                                 baris "Rp 0" membuat yang benar-benar ditagih
                                                 jadi sulit ditemukan. --}}
                                            @foreach ([
                                                ['Denda keterlambatan', $angka($dendaKeterlambatan),
                                                    ($sewa['terlambat'] ?? false)
                                                        ? (int) floor(($sewa['terlambat_menit'] ?? 0) / 60).' jam '
                                                            .(($sewa['terlambat_menit'] ?? 0) % 60).' menit lewat tenggat'
                                                        : null],
                                                ['Denda kerusakan', $angka($dendaKerusakan),
                                                    collect($sewa['rincian_denda_kerusakan'] ?? [])
                                                        ->pluck('bagian')->filter()->implode(', ') ?: null],
                                                ['Denda lain', $angka($dendaLain), null],
                                            ] as [$label, $nilai, $keterangan])
                                                @if ($nilai > 0)
                                                    <tr>
                                                        <td style="color:#b91c1c">
                                                            {{ $label }}
                                                            @if ($keterangan)
                                                                <span class="catatan">{{ $keterangan }}</span>
                                                            @endif
                                                        </td>
                                                        <td style="color:#b91c1c">
                                                            Rp {{ number_format($nilai, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach

                                            <tr class="jumlah">
                                                <td>Total tagihan</td>
                                                <td>Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                                            </tr>

                                            {{-- Uang yang sudah masuk mengurangi tagihannya.

                                                 Tanpa dua baris ini rinciannya berhenti di total,
                                                 seolah penyewa belum membayar sepeser pun —
                                                 padahal DP-nya sudah diterima. Admin yang
                                                 membacakan angka itu saat menagih akan menagih
                                                 lebih dari yang seharusnya.

                                                 Yang dikurangkan hanya yang bukti transfernya
                                                 SUDAH DITERIMA. Yang masih menunggu dicek belum
                                                 uang — dan itu sudah disebut di kartu Posisi
                                                 Pembayaran di atas. --}}
                                            @if ($diterima > 0)
                                                {{-- Dipecah per jenis: uang muka dan pelunasan
                                                     disebut namanya masing-masing. Satu baris
                                                     "sudah dibayar" menjawab berapa, tetapi tidak
                                                     menjawab yang ditanyakan berikutnya — itu DP
                                                     atau pelunasan — dan jawabannya menentukan
                                                     kalimat yang dipakai admin saat menagih.

                                                     Pesanan lama yang jenisnya tidak terkirim
                                                     tetap dapat satu baris gabungan. --}}
                                                @forelse ($sewa['pembayaran_diterima'] ?? [] as $bayar)
                                                    <tr>
                                                        <td style="color:#1f7a44">
                                                            {{ $bayar['label'] }}
                                                            @if ((int) ($bayar['berkas'] ?? 1) > 1)
                                                                <span class="catatan">
                                                                    {{ $bayar['berkas'] }} bukti transfer
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td style="color:#1f7a44">
                                                            &minus; Rp {{ number_format((int) $bayar['nominal'], 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td style="color:#1f7a44">
                                                            Sudah dibayar
                                                            <span class="catatan">
                                                                bukti transfer yang sudah diterima
                                                            </span>
                                                        </td>
                                                        <td style="color:#1f7a44">
                                                            &minus; Rp {{ number_format($diterima, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforelse

                                                <tr class="jumlah">
                                                    <td>{{ $lunasSemua ? 'Lunas' : 'Sisa yang harus dibayar' }}</td>
                                                    <td class="{{ $lunasSemua ? '' : 'sisa' }}">
                                                        {{ $lunasSemua ? '—' : 'Rp '.number_format($sisaBayar, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>

                                        <div class="mt-1" style="font-size:.78rem">
                                            BBM, tol, dan parkir yang tidak tercantum di atas
                                            ditanggung penyewa dan tidak ikut terhitung di sini.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-semibold">Catatan denda</label>
                                <textarea rows="2" class="form-control" wire:model="catatanDenda"
                                    placeholder="Mis. kaca spion kanan retak, diganti di bengkel langganan."></textarea>
                                <div class="form-text">
                                    Catatan ini yang dibacakan ke penyewa saat menagih. Sebutkan bagian dan
                                    alasannya, bukan hanya angkanya.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tombol simpan diulang di bawah lembar: yang panjang begini
                     jarang digulung balik ke atas hanya untuk menyimpan. --}}
                <div class="card-footer bg-transparent border-0 p-3 p-lg-4 pt-0">
                    {{-- Dibagi dua sama lebar, bukan didorong ke kanan sebagai
                         sepasang tombol kecil. Ini penutup lembar yang panjang;
                         yang menutupnya harus terlihat sebagai penutup. --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('admin.orcha.penyewaan') }}" wire:navigate
                                class="orcha-btn orcha-btn-lembut orcha-tombol-lembar">
                                <i class="bi bi-x-lg"></i> Batal
                            </a>
                        </div>
                        <div class="col-6">
                            {{-- Ikonnya berganti pemintal selama disimpan.

                                 Menyimpan lembar ini menembak Orcha dengan seluruh
                                 isinya — kilometer, bahan bakar, hasil pemeriksaan tiap
                                 bagian, dan rincian dendanya — jadi jedanya terasa.
                                 Tombol yang tidak berubah apa-apa selama itu membuat
                                 admin mengira tekanannya tidak masuk lalu menekannya
                                 lagi, dan lembar sepanjang ini terkirim dua kali.

                                 Menyasar simpanSerahTerima() secara khusus: tanpa itu,
                                 tombol ini ikut memintal saat unggahan foto jaminan
                                 sedang berjalan.

                                 Pemintalnya disembunyikan lewat gaya di partial gaya
                                 ([wire:loading] { display: none }), bukan hanya oleh
                                 skrip Livewire — kalau tidak, ia tergambar dari awal
                                 sebelum tombolnya sempat ditekan. --}}
                            <button type="button" class="orcha-btn orcha-btn-utama orcha-tombol-lembar"
                                wire:click="simpanSerahTerima" wire:target="simpanSerahTerima"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="simpanSerahTerima">
                                    <i class="bi bi-save"></i> Simpan Serah Terima
                                </span>
                                <span wire:loading wire:target="simpanSerahTerima">
                                    <span class="spinner-border spinner-border-sm me-2"
                                        role="status" aria-hidden="true"></span>Menyimpan...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('livewire.pages.admin.orcha.partials.kamera')

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
