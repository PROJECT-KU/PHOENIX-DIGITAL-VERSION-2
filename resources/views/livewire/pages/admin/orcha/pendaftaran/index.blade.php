@section('title')
Pendaftaran Open Trip || lemon
@stop

<div>
    @include('livewire.pages.admin.orcha.partials.gaya')

    <div class="container-fluid">
        @include('livewire.pages.admin.orcha.partials.kepala', [
            'judul' => 'Pendaftaran Open Trip',
            'keterangan' => 'Peserta yang mendaftar lewat website Orcha Journey.',
        ])

        {{-- Pencarian & saringan --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-lg-4">
                <div class="row g-2">
                    <div class="col-12 col-lg-8">
                        <div class="form-group position-relative mb-0">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="cari" type="text" class="form-control ps-5"
                                placeholder="Cari kode, nama, WhatsApp, atau paket...">
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
                                <th>Peserta</th>
                                <th>Paket</th>
                                <th>Berangkat</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="pendaftaran-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ \Carbon\Carbon::parse($baris['dibuat_pada'])->translatedFormat('d M Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $baris['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            {{ $baris['whatsapp'] }} · {{ $baris['jumlah_peserta'] }} peserta
                                        </div>

                                        {{-- Kelengkapan riwayat kesehatan harus terlihat jauh sebelum
                                             hari berangkat, bukan saat rombongan sudah berkumpul. --}}
                                        @php
                                            $terisi = $baris['kesehatan_terisi'] ?? 0;
                                            $lengkap = $baris['kesehatan_lengkap'] ?? false;
                                        @endphp
                                        <span class="badge mt-1 {{ $lengkap ? 'orcha-lencana-bayar-diterima' : 'orcha-lencana-bayar-menunggu' }}"
                                            @if (! empty($baris['peserta_belum_isi']))
                                                title="Belum mengisi: {{ implode(', ', $baris['peserta_belum_isi']) }}"
                                            @endif>
                                            <i class="bi {{ $lengkap ? 'bi-heart-pulse-fill' : 'bi-heart-pulse' }}"></i>
                                            {{ $terisi }}/{{ $baris['jumlah_peserta'] }} riwayat kesehatan
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">{{ $baris['paket']['nama'] }}</div>
                                        <div class="text-muted" style="font-size:.78rem">
                                            Jemput: {{ $baris['titik_jemput'] ?: '—' }}
                                        </div>
                                        {{-- Rombongan sering berangkat dari kota berbeda; yang
                                             dibaca sopir adalah pengelompokan ini. --}}
                                        @if (! empty($baris['jemput_per_titik']))
                                            <div class="mt-1">
                                                @foreach ($baris['jemput_per_titik'] as $titik => $orang)
                                                    <div style="font-size:.74rem">
                                                        <span class="fw-semibold text-dark">{{ $titik }}:</span>
                                                        <span class="text-muted">{{ implode(', ', $orang) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif (! empty($baris['peserta']))
                                            <div class="text-muted" style="font-size:.74rem">
                                                {{ collect($baris['peserta'])->pluck('nama')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="small text-nowrap">
                                        {{ $baris['tanggal_berangkat'] ? \Carbon\Carbon::parse($baris['tanggal_berangkat'])->translatedFormat('d M Y') : '—' }}
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm"
                                            wire:change="ubahStatus({{ $baris['id'] }}, $event.target.value)">
                                            @foreach ($pilihanStatus as $kunci => $label)
                                                <option value="{{ $kunci }}" @selected($baris['status'] === $kunci)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        @if (! auth()->user()->hasPermission('view_orcha_kesehatan'))
                                            <span class="text-muted small">—</span>
                                        @elseif (($baris['jumlah_riwayat_kesehatan'] ?? 0) > 0)
                                            <button type="button" class="btn btn-sm btn-light border rounded-3 orcha-tombol"
                                                wire:click="bukaRiwayat({{ $baris['id'] }}, '{{ addslashes($baris['nama']) }}')"
                                                wire:loading.attr="disabled">
                                                <i class="bi bi-heart-pulse"></i>
                                                <span>Riwayat ({{ $baris['jumlah_riwayat_kesehatan'] }})</span>
                                            </button>
                                        @else
                                            <span class="text-muted small">Belum isi riwayat</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state-icon-wrapper mx-auto mb-2">
                                            <i class="bi bi-clipboard-x"></i>
                                        </div>
                                        <p class="text-muted mb-0">Belum ada pendaftaran yang cocok.</p>
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

    {{-- Riwayat kesehatan: data sensitif, jadi hanya dimuat saat dibuka dan
         tidak pernah ikut di tabel daftar. --}}
    @if ($riwayatUntuk)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15,45,74,.35)">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Riwayat Kesehatan</h5>
                            <span class="text-muted small">{{ $riwayatNama }}</span>
                        </div>
                        <button type="button" class="btn-close" wire:click="tutupRiwayat"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 rounded-3 small">
                            Data kesehatan bersifat pribadi. Pembukaannya tercatat di server Orcha
                            beserta akun yang membukanya.
                        </div>

                        @forelse ($riwayat as $peserta)
                            <div class="border rounded-4 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold">{{ $peserta['nama_peserta'] ?? '—' }}</div>
                                        <div class="text-muted small">
                                            {{ ($peserta['usia'] ?? null) ? $peserta['usia'] . ' tahun' : '' }}
                                            {{ ($peserta['jenis_kelamin'] ?? null) ? '· ' . $peserta['jenis_kelamin'] : '' }}
                                            {{ ($peserta['golongan_darah'] ?? null) ? '· Gol. ' . $peserta['golongan_darah'] : '' }}
                                        </div>
                                    </div>
                                    @if ($peserta['ada_catatan_khusus'] ?? false)
                                        <span class="badge bg-warning text-dark">Perlu perhatian</span>
                                    @endif
                                </div>

                                <div class="row g-2 small">
                                    @foreach ([
                                        'Riwayat penyakit' => $peserta['riwayat_penyakit'] ?? null,
                                        'Alergi' => $peserta['alergi'] ?? null,
                                        'Obat rutin' => $peserta['obat_rutin'] ?? null,
                                        'Riwayat operasi' => $peserta['riwayat_operasi'] ?? null,
                                        'Pantangan makanan' => $peserta['pantangan_makanan'] ?? null,
                                        'Pantangan kegiatan' => $peserta['pantangan_kegiatan'] ?? null,
                                        'Kemampuan renang' => $peserta['kemampuan_renang'] ?? null,
                                        'Asuransi' => $peserta['asuransi'] ?? null,
                                    ] as $label => $nilai)
                                        @if ($nilai)
                                            <div class="col-12 col-md-6">
                                                <span class="text-muted">{{ $label }}:</span>
                                                <span class="fw-semibold">{{ $nilai }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                @if (! empty($peserta['kondisi_khusus']))
                                    <div class="mt-2">
                                        <span class="text-muted small">Kondisi khusus:</span>
                                        @foreach ($peserta['kondisi_khusus'] as $kondisi)
                                            <span class="badge bg-light text-dark border">{{ $kondisi }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-3 pt-2 border-top small">
                                    <span class="text-muted">Kontak darurat:</span>
                                    <span class="fw-semibold">
                                        {{ data_get($peserta, 'kontak_darurat.nama') ?: '—' }}
                                        ({{ data_get($peserta, 'kontak_darurat.hubungan') ?: '—' }})
                                        · {{ data_get($peserta, 'kontak_darurat.hp') ?: '—' }}
                                    </span>
                                </div>

                                @if (data_get($peserta, 'catatan_tambahan'))
                                    <div class="mt-2 small">
                                        <span class="text-muted">Catatan:</span>
                                        <span>{{ $peserta['catatan_tambahan'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada riwayat kesehatan yang diisi.</p>
                        @endforelse
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light border rounded-3" wire:click="tutupRiwayat">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
