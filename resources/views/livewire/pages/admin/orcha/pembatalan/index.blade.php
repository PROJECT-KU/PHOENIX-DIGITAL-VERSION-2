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
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari kode pendaftaran, nama, atau WhatsApp...">
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
                                <th>Pemohon</th>
                                <th>Alasan</th>
                                <th>Perkiraan Kembali</th>
                                <th>Rekening</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="pembatalan-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode_pendaftaran'] }}</span>
                                        {{-- Pembatalan kini datang dari dua jenis pesanan; jenisnya
                                             disebut supaya admin tahu ke mana harus memeriksa. --}}
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ $baris['jenis_label'] ?? 'Open Trip' }} ·
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M Y') }}
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
                                            <div class="fw-bold {{ $p['kembali'] > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $p['kembali_teks'] }}
                                            </div>
                                            <div class="text-muted" style="font-size:.74rem">
                                                dibayar {{ $p['dibayar_teks'] }} · potongan {{ $p['persen'] }}%
                                            </div>
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
                                        <span class="badge bg-light text-dark">{{ $baris['status_label'] }}</span>
                                        @if ($baris['catatan_admin'])
                                            <div class="text-muted" style="font-size:.75rem">
                                                {{ \Illuminate\Support\Str::limit($baris['catatan_admin'], 40) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        {{-- Detail dulu, baru tindak lanjut: keputusan pembatalan
                                             menyangkut uang yang dikirim balik, dan itu tidak
                                             pantas diambil dari satu baris tabel. --}}
                                        <a href="{{ route('admin.orcha.pembatalan.detail', $baris['id']) }}"
                                            wire:navigate class="btn btn-sm orcha-aksi orcha-aksi-lihat"
                                            title="Lihat detail lengkap beserta perhitungannya">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                            title="Ubah status pengajuan"
                                            wire:click="buka({{ $baris['id'] }}, '{{ $baris['status'] }}', '{{ addslashes((string) $baris['catatan_admin']) }}')">
                                            <i class="bi bi-check2-square"></i>
                                        </button>
                                    </td>
                                </tr>
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

    @if ($sedangDiubah)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,45,74,.35)">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold mb-0">Tindak Lanjut Pembatalan</h5>
                        <button type="button" class="btn-close" wire:click="tutup"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label small fw-semibold">Status</label>
                        <select wire:model="statusBaru" class="form-select mb-3">
                            @foreach ($pilihanStatus as $kunci => $label)
                                <option value="{{ $kunci }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <label class="form-label small fw-semibold">Catatan admin</label>
                        <textarea wire:model="catatanAdmin" class="form-control" rows="3"
                            placeholder="Mis. potongan 50%, dana dikirim 12 Agustus."></textarea>
                        <div class="form-text">Catatan ini tersimpan di Orcha dan terlihat di admin Orcha.</div>
                    </div>
                    {{-- Disebutkan sebelum tombolnya ditekan: menyetujui di sini ikut
                         membatalkan pesanannya, dan itu bukan akibat yang pantas
                         ditemukan sesudahnya. --}}
                    <div class="alert alert-info border-0 rounded-3 mx-3 mb-0" style="font-size:.8rem">
                        <i class="bi bi-info-circle"></i>
                        <strong>Disetujui</strong> dan <strong>Dana dikirim</strong> ikut menandai
                        pesanannya batal. Untuk melihat perhitungan pengembaliannya lebih dulu,
                        buka detailnya.
                    </div>

                    <div class="modal-footer border-0 d-flex justify-content-between gap-2">
                        <a href="{{ route('admin.orcha.pembatalan.detail', $sedangDiubah) }}" wire:navigate
                            class="orcha-btn orcha-btn-lembut">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                        <div class="d-flex gap-2">
                            <button type="button" class="orcha-btn orcha-btn-lembut" wire:click="tutup">
                                Batal
                            </button>
                            <button type="button" class="orcha-btn orcha-btn-utama" wire:click="simpan"
                                wire:loading.attr="disabled">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
