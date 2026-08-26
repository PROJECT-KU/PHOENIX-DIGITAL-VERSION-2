@section('title')
Pembatalan Orcha || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Pengajuan Pembatalan',
            'keterangan' => 'Permintaan pembatalan beserta rekening pengembalian dananya.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari kode pendaftaran, nama, atau WhatsApp...'])
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
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-batal mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Pemohon</th>
                                {{-- Urutannya mengikuti selnya: perkiraan pengembalian lebih
                                     dulu, baru alasan. Sebelumnya kedua judul ini tertukar —
                                     "Alasan" berdiri di atas kolom rupiah dan "Perkiraan
                                     Kembali" di atas kalimat alasannya. Tidak terlihat selama
                                     judulnya rata kiri dan selnya panjang-panjang; begitu
                                     judulnya ditengahkan, salah pasangnya langsung kentara. --}}
                                <th>Perkiraan Kembali</th>
                                <th>Alasan</th>
                                <th>Rekening</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                /*
                                 | Dikelompokkan menurut jenis pesanannya.
                                 |
                                 | Empat jenis bercampur dalam satu daftar — open trip,
                                 | private trip, study tour, sewa kendaraan — dan
                                 | kebijakan potongannya berbeda-beda. Admin yang
                                 | membacanya berurutan harus memeriksa kolom "Kode" tiap
                                 | baris hanya untuk tahu aturan mana yang berlaku.
                                 |
                                 | Pengelompokan hanya berlaku dalam satu halaman, sama
                                 | seperti di daftar bukti pembayaran: yang terlempar ke
                                 | halaman berikutnya tidak ikut terhitung, jadi jumlah di
                                 | kepala kelompok disebut apa adanya.
                                 */
                                $urutanJenis = ['open_trip', 'private_trip', 'study_tour', 'sewa_kendaraan'];

                                $kelompok = collect($daftar)
                                    ->groupBy(fn ($satu) => $satu['jenis'] ?? 'open_trip')
                                    ->sortBy(fn ($isi, $jenis) => array_search($jenis, $urutanJenis, true));

                                $ikonJenis = [
                                    'open_trip' => 'bi-signpost-split',
                                    'private_trip' => 'bi-people',
                                    'study_tour' => 'bi-mortarboard',
                                    'sewa_kendaraan' => 'bi-truck',
                                ];
                            @endphp

                            @forelse ($kelompok as $jenis => $barisJenis)
                                <tr class="orcha-grup" wire:key="jenis-{{ $jenis }}">
                                    <td colspan="7">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span class="orcha-label-kecil mb-0" style="color:#1d6fa5">
                                                <i class="bi {{ $ikonJenis[$jenis] ?? 'bi-signpost-split' }}"></i>
                                                {{ $barisJenis->first()['jenis_label'] ?? 'Open Trip' }}
                                            </span>
                                            <span class="text-muted" style="font-size:.78rem">
                                                {{ $barisJenis->count() }} pengajuan di halaman ini
                                            </span>
                                        </div>
                                    </td>
                                </tr>

                                @foreach ($barisJenis as $baris)
                                    <tr wire:key="pembatalan-{{ $baris['id'] }}">
                                        <td>
                                            <span class="orcha-kode">{{ $baris['kode_pendaftaran'] }}</span>
                                            {{-- Pembatalan kini datang dari dua jenis pesanan; jenisnya
                                                 disebut supaya admin tahu ke mana harus memeriksa. --}}
                                            <div class="text-muted" style="font-size:.75rem">
                                                {{ $baris['jenis_label'] ?? 'Open Trip' }} ·
                                                {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $baris['nama_pemohon'] }}</div>
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $baris['whatsapp'] }}
                                                {{-- "1 peserta" pada sewa kendaraan menyesatkan: yang
                                                     dibatalkan unitnya, bukan orangnya. --}}
                                                @if (($baris['jenis'] ?? 'open_trip') !== 'sewa_kendaraan')
                                                    · {{ $baris['jumlah_dibatalkan'] }} peserta
                                                @else
                                                    · 1 unit
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{-- Perkiraan potongan menurut tangga yang berlaku,
                                                 dihitung Orcha. Pertanyaan pertama pada tiap
                                                 pengajuan selalu "kembalinya berapa", dan sebelum
                                                 ini jawabannya dihitung tangan satu per satu. --}}
                                            @if ($baris['perkiraan'] ?? null)
                                                @php $p = $baris['perkiraan']; @endphp

                                                {{-- Nol ditulis sebagai kalimat, bukan "Rp 0".

                                                     Angka nol di kolom rupiah terbaca seperti data yang
                                                     belum terisi, dan admin yang awam menyangka
                                                     perhitungannya gagal. Yang sebenarnya terjadi:
                                                     potongannya sebesar seluruh pembayaran, jadi memang
                                                     tidak ada yang dikirim balik. --}}
                                                @if ($p['kembali'] > 0)
                                                    <div class="fw-bold text-success">{{ $p['kembali_teks'] }}</div>
                                                    <div class="text-muted" style="font-size:.74rem">
                                                        dari {{ $p['dibayar_teks'] }} yang sudah dibayar,
                                                        dipotong {{ $p['persen'] }}%
                                                    </div>
                                                @else
                                                    <div class="fw-bold text-danger">Tidak ada pengembalian</div>
                                                    <div class="text-muted" style="font-size:.74rem">
                                                        {{ $p['dibayar_teks'] }} yang sudah dibayar dipotong
                                                        {{ $p['persen'] }}% — habis
                                                    </div>
                                                @endif

                                                <div class="text-muted" style="font-size:.72rem">{{ $p['batas'] }}</div>
                                            @else
                                                {{-- Tanpa tanggal berangkat tidak ada jarak yang bisa
                                                     dihitung; menebak angka pengembalian lebih buruk
                                                     daripada mengosongkannya. --}}
                                                <span class="text-muted" style="font-size:.78rem">belum bisa dihitung</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">{{ $baris['alasan_label'] }}</div>
                                            @if ($baris['penjelasan'])
                                                <div class="text-muted" style="font-size:.78rem">
                                                    {{ \Illuminate\Support\Str::limit($baris['penjelasan'], 60) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <div class="fw-semibold">{{ $baris['rekening']['bank'] }}</div>
                                            <div class="text-muted" style="font-size:.78rem">
                                                {{ $baris['rekening']['nomor'] }}<br>
                                                a.n. {{ $baris['rekening']['atas_nama'] }}
                                            </div>
                                        </td>
                                        <td>
                                            {{-- Warnanya mengikuti maknanya. Sebelumnya semuanya
                                                 lencana abu-abu yang sama: "Diajukan", "Disetujui",
                                                 dan "Ditolak" terlihat serupa, padahal ketiganya
                                                 menuntut perbuatan yang sama sekali berbeda. --}}
                                            <span class="orcha-status-batal" data-status="{{ $baris['status'] }}">
                                                <i class="bi {{ [
                                                    'diajukan' => 'bi-hourglass-split',
                                                    'diproses' => 'bi-arrow-repeat',
                                                    'disetujui' => 'bi-check2-circle',
                                                    'dana_dikirim' => 'bi-send-check',
                                                    'ditolak' => 'bi-x-circle',
                                                ][$baris['status']] ?? 'bi-circle' }}"></i>
                                                {{ $baris['status_label'] }}
                                            </span>
                                            @if ($baris['catatan_admin'])
                                                <div class="text-muted" style="font-size:.75rem">
                                                    {{ \Illuminate\Support\Str::limit($baris['catatan_admin'], 40) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">


                                            {{-- Satu tombol, berlabel.

                                                 Sempat ada dua ikon bersebelahan — mata dan ceklis —
                                                 yang keduanya menuju halaman yang sama. Admin yang
                                                 awam menyangka keduanya berbeda lalu menebak-nebak
                                                 mana yang benar. Labelnya mengikuti keadaan
                                                 pengajuannya: yang belum diputuskan mengajak
                                                 menindaklanjuti, yang sudah tinggal dilihat.

                                                 Tautan biasa, bukan jendela di atas daftar: yang
                                                 menentukan keputusan adalah angka pengembaliannya,
                                                 dan angka itu hanya ada di detail. Admin yang
                                                 memutuskan dari daftar memutuskan tanpa melihat
                                                 uangnya. --}}
                                            @php
                                                $belumDiputuskan = in_array($baris['status'], ['diajukan', 'diproses'], true);
                                            @endphp
                                            <a href="{{ route('admin.orcha.pembatalan.detail', $baris['id']) }}"
                                                wire:navigate
                                                class="orcha-btn {{ $belumDiputuskan ? 'orcha-btn-utama' : 'orcha-btn-lembut' }} orcha-aksi-sewa"
                                                title="Buka perhitungan lengkapnya">
                                                <i class="bi {{ $belumDiputuskan ? 'bi-check2-square' : 'bi-eye' }}"></i>
                                                {{ $belumDiputuskan ? 'Tindak lanjuti' : 'Lihat' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="empty-state-icon-wrapper mx-auto mb-2">
                                                <i class="bi bi-x-circle"></i>
                                            </div>
                                            <p class="text-muted mb-0">Belum ada pengajuan pembatalan yang cocok.</p>
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


    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
