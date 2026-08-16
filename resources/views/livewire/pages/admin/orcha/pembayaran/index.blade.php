@php
    $asalOrcha = rtrim(str_replace('/api/v1', '', config('orcha.url')), '/');
    $tautanBukti = fn ($jalur) => $jalur
        ? (str_starts_with($jalur, 'http') ? $jalur : $asalOrcha . $jalur)
        : null;
@endphp

@section('title')
Bukti Pembayaran Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Bukti Pembayaran',
            'keterangan' => 'Bukti transfer yang dikirim pelanggan lewat formulir di website.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari kode pesanan, nama pengirim, atau bank...">
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">Semua status</option>
                            @foreach ($pilihanStatus as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">
                <div class="orcha-gulung">
                    <table class="table table-hover align-middle orcha-tabel mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pengirim</th>
                                <th>Jenis</th>
                                <th class="text-end">Nominal</th>
                                <th>Transfer</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        @php
                            // Satu pesanan hampir selalu berbuah lebih dari satu bukti: DP dulu,
                            // pelunasan menyusul, kadang ditambah kiriman ulang karena yang pertama
                            // buram. Berderet menurut waktu kirim, ketiganya terpisah jauh di layar
                            // padahal pertanyaannya selalu satu — pesanan ini sudah masuk berapa.
                            //
                            // Pengelompokan hanya berlaku dalam satu halaman. Bukti yang terlempar
                            // ke halaman berikutnya tidak ikut terjumlah, jadi angka di kepala
                            // kelompok disebut apa adanya: yang tampil di halaman ini.
                            $kelompok = collect($daftar)
                                ->groupBy('kode')
                                ->map(fn ($bukti) => $bukti->sortBy('dibuat_pada')->values());
                        @endphp

                        <tbody>
                            @forelse ($kelompok as $kode => $bukti)
                                @php
                                    $utama = $bukti->first();
                                    $diterima = $bukti->where('status', 'diterima')->sum('nominal');
                                    $menunggu = $bukti->where('status', 'menunggu')->count();
                                @endphp

                                <tr class="orcha-grup" wire:key="grup-{{ $kode }}">
                                    <td colspan="7">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <span class="orcha-kode">{{ $kode }}</span>
                                                @if ($utama['pesanan'])
                                                    <span class="fw-semibold">{{ $utama['pesanan']['nama'] }}</span>
                                                    <span class="text-muted" style="font-size:.78rem">
                                                        {{ $utama['pesanan']['keterangan'] }}
                                                    </span>
                                                @else
                                                    {{-- Kode salah ketik tetap masuk; ditandai supaya dicocokkan manual --}}
                                                    <span class="text-danger orcha-ikon-teks" style="font-size:.78rem">
                                                        <i class="bi bi-exclamation-triangle"></i> kode tak dikenal
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center gap-2"
                                                style="font-size:.78rem">
                                                <span class="text-muted">
                                                    {{ $bukti->count() }} bukti di halaman ini
                                                </span>
                                                {{-- Yang menentukan pesanan sudah dibayar hanya bukti
                                                     yang diterima; yang menunggu belum uang. Nol
                                                     ditulis sebagai kalimat — "diterima Rp 0"
                                                     berwarna hijau membaca seperti kabar baik. --}}
                                                @if ($diterima > 0)
                                                    <span class="fw-bold text-success">
                                                        diterima Rp {{ number_format($diterima, 0, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">belum ada yang diterima</span>
                                                @endif
                                                @if ($menunggu > 0)
                                                    <span class="badge orcha-lencana-bayar-menunggu">
                                                        {{ $menunggu }} menunggu dicek
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @foreach ($bukti as $baris)
                                <tr wire:key="bayar-{{ $baris['id'] }}" class="orcha-anggota">
                                    <td class="text-muted text-nowrap" style="font-size:.78rem">
                                        <span class="orcha-ikon-teks">
                                            <i class="bi bi-arrow-return-right"></i>
                                            Bukti {{ $loop->iteration }} dari {{ $bukti->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['atas_nama_pengirim'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">{{ $baris['bank_pengirim'] }}
                                        </div>
                                    </td>
                                    <td class="small">{{ $baris['jenis_label'] }}</td>
                                    <td class="text-end fw-semibold text-nowrap">{{ $baris['nominal_formatted'] }}</td>
                                    <td class="small text-nowrap">
                                        {{ $baris['tanggal_transfer'] ? \Carbon\Carbon::parse($baris['tanggal_transfer'])->translatedFormat('d M Y') : '—' }}
                                        <div class="text-muted" style="font-size:.74rem">
                                            dikirim
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M, H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge orcha-lencana-bayar-{{ $baris['status'] }}">
                                            {{ $baris['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        {{-- Kabar lewat WhatsApp.

                                             Surat sudah dikirim otomatis tiap status berubah,
                                             tapi tidak semua pelanggan membuka kotak suratnya.
                                             Pesannya dibuka dulu di WhatsApp, tidak langsung
                                             terkirim — yang menekan kirim tetap admin. --}}
                                        @php $wa = $this->tautanWa($baris); @endphp
                                        @if ($wa)
                                            <a href="{{ $wa }}" target="_blank" rel="noopener"
                                                class="btn btn-sm orcha-aksi orcha-aksi-wa"
                                                data-wa-pesan="{{ $this->pesanWa($baris) }}"
                                                title="Kabari pelanggan lewat WhatsApp — pesannya sekaligus disalin">
                                                <i class="bi bi-whatsapp"></i>
                                            </a>
                                        @endif

                                        @if ($baris['bukti'])
                                            {{-- Menumpang di halaman ini, bukan tab baru: daftar yang
                                                 sudah digulung tidak ikut kembali ke atas. --}}
                                            <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-lihat"
                                                title="Lihat bukti transfer"
                                                data-bukti="{{ $tautanBukti($baris['bukti']) }}"
                                                data-bukti-keterangan="{{ $baris['kode'] }} · {{ $baris['nominal_formatted'] }} · {{ $baris['bank_pengirim'] }} a.n. {{ $baris['atas_nama_pengirim'] }}">
                                                <i class="bi bi-receipt"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                            wire:click='buka(@json($baris))' title="Cek pembayaran">
                                            <i class="bi bi-check2-square"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-cash-coin"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada bukti pembayaran yang cocok.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('livewire.pages.admin.orcha.partials.paginasi')
            </div>
        </div>
    </div>

    @if ($sedangDicek)
        @php $terpilih = collect($daftar)->firstWhere('id', $sedangDicek); @endphp

        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,45,74,.35)">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 orcha-cek">
                    {{-- Kepala berwarna: yang dikerjakan di sini adalah memutuskan uang
                         orang lain, dan kode pesanannya harus terbaca tanpa dicari. --}}
                    <div class="orcha-cek-kepala">
                        <div>
                            <div class="orcha-cek-judul">
                                <i class="bi bi-check2-square"></i> Cek Pembayaran
                            </div>
                            <div class="orcha-cek-kode">
                                {{ $terpilih['kode'] ?? '' }}
                                @if ($terpilih)
                                    <span class="orcha-cek-pisah">·</span> {{ $terpilih['jenis_label'] }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if ($terpilih)
                                <span class="badge orcha-lencana-bayar-{{ $terpilih['status'] }}">
                                    {{ $terpilih['status_label'] }}
                                </span>
                            @endif
                            <button type="button" class="orcha-cek-tutup" wire:click="tutup" aria-label="Tutup">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body p-3 p-lg-4">
                        @if ($terpilih)
                            <div class="row g-4">
                                <div class="col-12 col-lg-7">
                                    {{-- Nominal dibesarkan sendiri. Inilah angka yang dicocokkan
                                         dengan mutasi rekening, dan salah baca satu digit di sini
                                         berarti salah menyatakan pesanan sudah lunas. --}}
                                    <div class="orcha-cek-nominal">
                                        <div class="orcha-label-kecil">Nominal yang dikirim</div>
                                        <div class="angka">{{ $terpilih['nominal_formatted'] }}</div>
                                        <div class="orcha-cek-tanggal">
                                            <i class="bi bi-calendar-event"></i>
                                            {{ $terpilih['tanggal_transfer']
                                                ? \Carbon\Carbon::parse($terpilih['tanggal_transfer'])->translatedFormat('j F Y')
                                                : 'tanggal transfer tidak diisi' }}
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        @foreach ([
                                            ['bi-person-badge', 'Pengirim', $terpilih['atas_nama_pengirim'], null],
                                            ['bi-bank', 'Bank pengirim', $terpilih['bank_pengirim'], null],
                                            ['bi-person-circle', 'Pemesan', $terpilih['pesanan']['nama'] ?? '—', $terpilih['pesanan']['whatsapp'] ?? null],
                                            ['bi-signpost-split', 'Pesanan', $terpilih['pesanan']['keterangan'] ?? '—', null],
                                        ] as [$ikon, $label, $nilai, $tambahan])
                                            <div class="col-6">
                                                <div class="orcha-cek-fakta">
                                                    <span class="orcha-cek-ikon"><i class="bi {{ $ikon }}"></i></span>
                                                    <div>
                                                        <div class="orcha-label-kecil">{{ $label }}</div>
                                                        <div class="fw-bold">{{ $nilai }}</div>
                                                        @if ($tambahan)
                                                            <div class="text-muted" style="font-size:.76rem">
                                                                {{ $tambahan }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @unless ($terpilih['pesanan'])
                                        {{-- Kode salah ketik tetap masuk. Yang tidak boleh terjadi
                                             adalah admin menerimanya seolah pesanannya jelas. --}}
                                        <div class="orcha-alasan orcha-alasan-tinggi mt-3">
                                            <span class="orcha-label-kecil orcha-ikon-teks" style="color:#b91c1c">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Kode tidak dikenal
                                            </span>
                                            <div style="font-size:.84rem" class="mt-1">
                                                Kode <strong>{{ $terpilih['kode'] }}</strong> tidak cocok dengan
                                                pesanan mana pun. Cocokkan dulu dengan pemesannya sebelum
                                                menerima — uang yang diakui ke pesanan yang salah lebih sulit
                                                diurai daripada bukti yang ditunda.
                                            </div>
                                        </div>
                                    @endunless

                                    @if ($terpilih['catatan'])
                                        <div class="mt-3">
                                            <div class="orcha-label-kecil mb-1">Catatan pelanggan</div>
                                            <div class="orcha-cek-catatan">{{ $terpilih['catatan'] }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 col-lg-5">
                                    <div class="orcha-label-kecil mb-1">Bukti transfer</div>
                                    @if ($terpilih['bukti'])
                                        <div class="orcha-cek-bukti"
                                            data-bukti="{{ $tautanBukti($terpilih['bukti']) }}"
                                            data-bukti-keterangan="{{ $terpilih['kode'] }} · {{ $terpilih['nominal_formatted'] }} · {{ $terpilih['bank_pengirim'] }} a.n. {{ $terpilih['atas_nama_pengirim'] }}">
                                            <img src="{{ $tautanBukti($terpilih['bukti']) }}" alt="Bukti transfer">
                                            <span class="orcha-cek-perbesar">
                                                <i class="bi bi-arrows-fullscreen"></i> Klik untuk memperbesar
                                            </span>
                                        </div>
                                    @else
                                        <div class="orcha-cek-kosong">
                                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                                <i class="bi bi-image"></i>
                                            </div>
                                            <p class="text-muted mb-0" style="font-size:.84rem">
                                                Pelanggan tidak melampirkan berkas bukti.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="orcha-cek-putus">
                                <div class="orcha-label-kecil mb-2">Keputusan</div>

                                {{-- Ditampilkan sebagai pilihan berdampingan, bukan daftar
                                     turun. Menerima dan menolak adalah dua tindakan yang
                                     berbeda akibatnya, jadi keduanya pantas terlihat
                                     sekaligus — bukan bersembunyi di balik satu klik. --}}
                                <div class="orcha-cek-pilihan">
                                    @foreach ($pilihanStatus as $kunci => $label)
                                        <label class="orcha-cek-status orcha-cek-status-{{ $kunci }}">
                                            {{-- @checked ditulis sendiri: tanpa itu tidak ada satu
                                                 pun pilihan yang tersorot saat lembar ini dibuka,
                                                 dan admin tidak bisa melihat status yang berlaku
                                                 sekarang — padahal itu titik tolak keputusannya. --}}
                                            <input type="radio" wire:model="statusBaru"
                                                value="{{ $kunci }}" @checked($statusBaru === $kunci)>
                                            <span>
                                                <i
                                                    class="bi {{ ['menunggu' => 'bi-hourglass-split', 'diterima' => 'bi-check-circle-fill', 'ditolak' => 'bi-x-circle-fill'][$kunci] ?? 'bi-circle' }}"></i>
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <label class="form-label small fw-semibold mb-1">
                                        Catatan admin
                                        <span class="text-muted fw-normal">(ikut terbaca oleh admin lain)</span>
                                    </label>
                                    <input type="text" class="form-control" wire:model="catatanAdmin"
                                        placeholder="Mis. cocok dengan mutasi rekening 15 Agu.">
                                    <div class="form-text">
                                        Bila ditolak, tuliskan alasannya — kalimat inilah yang dikirim ke
                                        pelanggan supaya ia tahu apa yang perlu diperbaiki.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer border-0 px-3 px-lg-4 pb-3 pb-lg-4 pt-0
                        d-flex justify-content-between align-items-center gap-2">
                        {{-- Disebutkan sebelum tombolnya ditekan, bukan sesudah: yang
                             menerima email adalah pelanggan, dan email tidak bisa ditarik. --}}
                        <span class="text-muted orcha-ikon-teks" style="font-size:.78rem">
                            <i class="bi bi-envelope"></i>
                            Pelanggan otomatis dikabari lewat email setelah status disimpan.
                        </span>
                        <div class="d-flex gap-2">
                            {{-- Diletakkan di sini, bersebelahan dengan Simpan: begitu
                                 statusnya diputuskan, kabar ke pelanggan adalah langkah
                                 berikutnya yang wajar — bukan sesuatu yang harus dicari
                                 lagi di daftar.

                                 Isinya mengikuti status yang TERSIMPAN, bukan yang baru
                                 dipilih di layar. Mengabari "sudah diterima" untuk sesuatu
                                 yang belum disimpan berarti menjanjikan yang belum tercatat. --}}
                            {{-- Kaki lembar tetap tergambar walau barisnya tidak ketemu di
                                 halaman ini (mis. daftarnya sudah tersaring ulang), jadi
                                 $terpilih perlu diperiksa dulu. --}}
                            @php $waPopup = $terpilih ? $this->tautanWa($terpilih) : null; @endphp
                            @if ($waPopup)
                                {{-- Tombol salin berdiri sendiri, tidak hanya menumpang di
                                     tombol WA. Bila emojinya berantakan di aplikasi WhatsApp,
                                     inilah jalan yang pasti: menempel memindahkan karakter
                                     yang sama persis, tanpa sandi yang perlu dibaca ulang. --}}
                                <button type="button" class="orcha-btn orcha-btn-lembut"
                                    data-wa-pesan="{{ $this->pesanWa($terpilih) }}"
                                    title="Salin teks pesannya untuk ditempel di WhatsApp">
                                    <i class="bi bi-clipboard"></i> Salin Pesan
                                </button>

                                <a href="{{ $waPopup }}" target="_blank" rel="noopener"
                                    class="orcha-btn orcha-btn-wa"
                                    data-wa-pesan="{{ $this->pesanWa($terpilih) }}">
                                    <i class="bi bi-whatsapp"></i> Kabari via WA
                                </a>
                            @endif

                            <button type="button" class="orcha-btn orcha-btn-lembut" wire:click="tutup">
                                Batal
                            </button>
                            <button type="button" class="orcha-btn orcha-btn-utama" wire:click="simpan"
                                wire:loading.attr="disabled">
                                <i class="bi bi-save"></i> Simpan Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include("livewire.pages.admin.orcha.partials.salin-wa")
    @include("livewire.pages.admin.orcha.partials.pratinjau-bukti")
    @include('livewire.pages.admin.orcha.partials.skrip')

    {{-- Gaya khusus lembar cek pembayaran. Ditulis di sini, bukan di partial
         gaya bersama, karena hanya halaman ini yang memakainya — dan inline,
         bukan lewat Vite: public/build tidak ikut ter-deploy. --}}
    <style>
        .orcha-cek-kepala {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            background: linear-gradient(135deg, #1d6fa5, #0f2d4a);
            color: #fff;
            border-radius: 1rem 1rem 0 0;
        }

        /* Ikon diratakan lewat flex, bukan vertical-align — lihat catatan pada
           .orcha-ikon-teks di partial gaya. */
        .orcha-cek-judul {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.2;
        }

        .orcha-cek-judul > i { line-height: 1; }

        .orcha-cek-kode {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: .82rem;
            color: #cfe4f2;
            margin-top: .2rem;
        }

        .orcha-cek-pisah { opacity: .55; }

        .orcha-cek-tutup {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            font-size: .85rem;
            line-height: 1;
            transition: background .15s ease;
        }

        .orcha-cek-tutup:hover { background: rgba(255, 255, 255, .3); }

        /* Angka yang dicocokkan dengan mutasi rekening. Diberi ruang sendiri
           supaya tidak perlu dicari di antara keterangan lain. */
        .orcha-cek-nominal {
            padding: .9rem 1.1rem;
            border-radius: .9rem;
            background: linear-gradient(135deg, #f4f8fb, #e8f1f8);
            border-left: 4px solid #1d6fa5;
        }

        .orcha-cek-nominal .angka {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f2d4a;
            line-height: 1.15;
            letter-spacing: -.01em;
        }

        .orcha-cek-tanggal {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .8rem;
            color: #5b7186;
            margin-top: .15rem;
        }

        .orcha-cek-tanggal > i { line-height: 1; }

        /* Ikonnya ditengahkan terhadap seluruh pasangan label-nilai, bukan
           digantung di baris pertama: yang dilihat mata sebagai satu kesatuan
           adalah kotak keterangannya, bukan barisnya satu per satu. */
        .orcha-cek-fakta {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .88rem;
        }

        .orcha-cek-ikon {
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border-radius: .6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #eef5fa;
            color: #1d6fa5;
            font-size: .9rem;
        }

        .orcha-cek-catatan {
            padding: .75rem .9rem;
            border-radius: .7rem;
            background: #f7f9fb;
            border-left: 3px solid #cfdbe6;
            font-size: .85rem;
            color: #3c5468;
        }

        /* Bingkai bukti: tingginya dibatasi supaya struk yang panjang tidak
           mendorong tombol keputusan keluar layar. Utuhnya dilihat lewat
           pratinjau, yang memang untuk itu. */
        .orcha-cek-bukti {
            position: relative;
            border-radius: .9rem;
            overflow: hidden;
            border: 1px solid #e3ecf3;
            background: #f7f9fb;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 12rem;
            max-height: 26rem;
        }

        .orcha-cek-bukti img {
            max-width: 100%;
            max-height: 26rem;
            object-fit: contain;
        }

        .orcha-cek-perbesar {
            position: absolute;
            left: 50%;
            bottom: .65rem;
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .7rem;
            border-radius: 2rem;
            background: rgba(15, 45, 74, .78);
            color: #fff;
            font-size: .74rem;
            white-space: nowrap;
        }

        .orcha-cek-kosong {
            border: 1px dashed #d5e1ea;
            border-radius: .9rem;
            padding: 2rem 1rem;
            text-align: center;
            background: #fafcfd;
        }

        .orcha-cek-putus {
            margin-top: 1.25rem;
            padding-top: 1.1rem;
            border-top: 1px solid #eef2f6;
        }

        .orcha-cek-pilihan {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .orcha-cek-status { margin: 0; }

        .orcha-cek-status input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .orcha-cek-status span {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .5rem .95rem;
            border-radius: .7rem;
            border: 1.5px solid #dbe7f0;
            background: #fff;
            color: #5b7186;
            font-size: .86rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s ease;
        }

        .orcha-cek-status span > i { line-height: 1; }

        .orcha-cek-status span:hover { border-color: #b9d0e2; }

        /* Warnanya baru muncul saat dipilih. Sebelum admin memutuskan, tidak
           ada pilihan yang pantas terlihat seperti sudah dipilih. */
        .orcha-cek-status-menunggu input:checked+span {
            border-color: #d99a19;
            background: #fdf6e7;
            color: #8a6110;
        }

        .orcha-cek-status-diterima input:checked+span {
            border-color: #1a8a52;
            background: #e9f7f0;
            color: #126b40;
        }

        .orcha-cek-status-ditolak input:checked+span {
            border-color: #c2323c;
            background: #fdecee;
            color: #9b2530;
        }

        .orcha-cek-status input:focus-visible+span {
            outline: 2px solid #1d6fa5;
            outline-offset: 2px;
        }

        @media (max-width: 575.98px) {
            .orcha-cek-nominal .angka { font-size: 1.45rem; }

            .orcha-cek-pilihan { flex-direction: column; }

            .orcha-cek-status span { justify-content: center; }
        }
    </style>
</div>
