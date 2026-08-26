@section('title')
Sewa Kendaraan Masuk || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Sewa Kendaraan Masuk',
            'keterangan' => 'Pemesanan sewa yang dikirim lewat formulir di website Orcha.',
        ])

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => 'Cari kode, nama, WhatsApp, atau unit...'])
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
                    <table class="table table-hover align-middle orcha-tabel orcha-tabel-sewa mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Penyewa</th>
                                <th>Kendaraan</th>
                                <th>Mulai</th>
                                <th>Kembali</th>
                                <th class="text-end">Tagihan</th>
                                <th>Status</th>
                                <th class="text-end">Serah terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="penyewaan-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->locale('id')->translatedFormat('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">{{ $baris['whatsapp'] }}</div>
                                    </td>
                                    <td>
                                        <div class="small">{{ $baris['kendaraan']['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['kendaraan']['transmisi'] }} · {{ $baris['durasi_label'] }} ·
                                            {{ $baris['dengan_sopir'] ? 'dengan sopir' : 'lepas kunci' }}
                                        </div>
                                    </td>
                                    <td class="small text-nowrap">
                                        {{ $baris['tanggal_mulai'] ? \Carbon\Carbon::parse($baris['tanggal_mulai'])->locale('id')->translatedFormat('d M Y') : '—' }}
                                        <div class="text-muted" style="font-size:.78rem">{{ $baris['jam_mulai'] ?: '—' }}</div>
                                    </td>
                                    {{-- Tenggat pengembalian berdiri sendiri, bukan diselipkan
                                         di kolom mulai: inilah yang dicek admin tiap pagi untuk
                                         tahu unit mana yang seharusnya sudah kembali. --}}
                                    <td class="small text-nowrap">
                                        @if ($baris['tanggal_selesai'])
                                            {{ \Carbon\Carbon::parse($baris['tanggal_selesai'])->locale('id')->translatedFormat('d M Y') }}
                                            <div class="text-muted" style="font-size:.78rem">{{ $baris['jam_selesai'] }}</div>
                                            @if ($baris['dikembalikan_pada'])
                                                <span class="orcha-lencana-aman mt-1">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    kembali {{ \Carbon\Carbon::parse($baris['dikembalikan_pada'])->locale('id')->translatedFormat('d M, H:i') }}
                                                </span>
                                            @elseif ($baris['terlambat'])
                                                <span class="orcha-lencana-awas mt-1">
                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                    telat {{ (int) floor($baris['terlambat_menit'] / 60) }} jam
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-nowrap">
                                        {{ $baris['estimasi_biaya'] ? 'Rp ' . number_format($baris['estimasi_biaya'], 0, ',', '.') : '—' }}
                                        @if (($baris['total_denda'] ?? 0) > 0)
                                            <div class="text-danger" style="font-size:.75rem">
                                                + denda Rp {{ number_format($baris['total_denda'], 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{-- Ringkas dan berwarna menurut statusnya: dalam
                                             daftar sepuluh baris, yang dicari admin adalah yang
                                             belum ditangani — dan itu harus terbaca tanpa
                                             membaca tulisannya satu per satu. --}}
                                        {{-- Bila daftar statusnya TIDAK sampai, yang digambar
                                             tulisannya — bukan kotak pilih kosong.

                                             Daftar itu datang dari /rujukan milik Orcha, dan
                                             saat Orcha sedang tidak bisa dihubungi
                                             rujukan() mengembalikan senarai kosong. Kotak
                                             pilih tanpa satu pun pilihan tergambar sebagai
                                             pil berwarna yang benar-benar kosong: statusnya
                                             lenyap dari layar tanpa satu pun kalimat yang
                                             menjelaskan sebabnya, dan admin membacanya
                                             sebagai data yang hilang.

                                             Statusnya sendiri selalu ada — ia datang bersama
                                             barisnya, bukan dari rujukan. Jadi yang benar:
                                             tetap tunjukkan statusnya, dan katakan bahwa yang
                                             belum bisa dilakukan hanya mengubahnya. --}}
                                        @if ($pilihanStatus === [])
                                            <span class="orcha-status-diam" data-status="{{ $baris['status'] }}"
                                                title="Daftar status belum bisa diambil dari Orcha, jadi statusnya belum bisa diubah dari sini.">
                                                <i class="bi bi-wifi-off"></i>
                                                {{ $baris['status_label'] ?? $baris['status'] }}
                                            </span>
                                        @else
                                            <select class="form-select form-select-sm orcha-status-ringkas"
                                                data-status="{{ $baris['status'] }}"
                                                wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)">
                                                @foreach ($pilihanStatus as $kunci => $label)
                                                    <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.orcha.penyewaan.detail', $baris['id']) }}"
                                            class="btn btn-sm orcha-aksi orcha-aksi-lihat orcha-aksi-sewa ikon-saja"
                                            title="Lihat detail penyewaan">
                                            <i class="bi bi-truck-front"></i>
                                        </a>
                                        {{-- Tautan biasa, bukan tombol Livewire. Lembarnya kini
                                             halaman tersendiri, jadi membukanya tidak bergantung
                                             pada JavaScript yang harus hidup lebih dulu di
                                             halaman daftar. --}}
                                        <a href="{{ route('admin.orcha.penyewaan.serah-terima', $baris['id']) }}"
                                            wire:navigate
                                            class="orcha-btn orcha-btn-utama orcha-aksi-sewa"
                                            title="Catat serah terima & denda">
                                            <i class="bi bi-clipboard-check"></i> Serah terima
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-truck"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada pemesanan sewa yang cocok.</p>
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
