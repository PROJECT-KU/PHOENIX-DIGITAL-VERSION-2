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
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari kode pesanan, nama pengirim, atau bank...'])
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
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-bayar mb-0">
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
                                        {{ $baris['tanggal_transfer'] ? \Carbon\Carbon::parse($baris['tanggal_transfer'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                        <div class="text-muted" style="font-size:.74rem">
                                            dikirim
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M, H:i') }}
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
                                        {{-- Tautan biasa, bukan tombol Livewire. Lembar
                                             ceknya kini halaman tersendiri, jadi membukanya
                                             tidak bergantung pada JavaScript yang harus hidup
                                             lebih dulu di halaman daftar.

                                             Sebelumnya seluruh baris dititipkan sebagai JSON
                                             di dalam wire:click — kode, nominal, nama
                                             pengirim, catatan bebas pelanggan, sampai jalur
                                             berkas buktinya — disalin utuh untuk SETIAP baris
                                             di halaman. Itu data pribadi, dan tidak satu pun
                                             darinya perlu berkeliling ke peramban hanya
                                             supaya admin bisa membuka satu lembar. --}}
                                        <a href="{{ route('admin.orcha.pembayaran.cek', $baris['id']) }}"
                                            wire:navigate
                                            class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                            title="Cek pembayaran">
                                            <i class="bi bi-check2-square"></i>
                                        </a>
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

    @include("livewire.pages.admin.orcha.partials.salin-wa")
    @include("livewire.pages.admin.orcha.partials.pratinjau-bukti")
    @include('livewire.pages.admin.orcha.partials.skrip')

    @include('livewire.pages.admin.orcha.pembayaran.partials.gaya-cek')
</div>
