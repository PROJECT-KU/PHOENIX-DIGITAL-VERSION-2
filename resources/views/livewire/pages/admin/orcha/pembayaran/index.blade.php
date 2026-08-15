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
                        <tbody>
                            @forelse ($daftar as $baris)
                                <tr wire:key="bayar-{{ $baris['id'] }}">
                                    <td>
                                        <span class="orcha-kode">{{ $baris['kode'] }}</span>
                                        @if ($baris['pesanan'])
                                            <div class="text-muted" style="font-size:.75rem">
                                                {{ $baris['pesanan']['keterangan'] }}
                                            </div>
                                        @else
                                            {{-- Kode salah ketik tetap masuk; ditandai supaya dicocokkan manual --}}
                                            <div class="text-danger" style="font-size:.74rem">
                                                <i class="bi bi-exclamation-triangle"></i> kode tak dikenal
                                            </div>
                                        @endif
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
                                        @if ($baris['bukti'])
                                            <a href="{{ $tautanBukti($baris['bukti']) }}" target="_blank" rel="noopener"
                                                class="btn btn-sm orcha-aksi orcha-aksi-lihat" title="Lihat bukti transfer">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-sm orcha-aksi orcha-aksi-ubah"
                                            wire:click='buka(@json($baris))' title="Cek pembayaran">
                                            <i class="bi bi-check2-square"></i>
                                        </button>
                                    </td>
                                </tr>
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
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4">
                    <div class="modal-header border-0">
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Cek Pembayaran</h5>
                            <span class="text-muted small">{{ $terpilih['kode'] ?? '' }}</span>
                        </div>
                        <button type="button" class="btn-close" wire:click="tutup"></button>
                    </div>

                    <div class="modal-body">
                        @if ($terpilih)
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <div class="row g-3">
                                        @foreach ([['Pengirim', $terpilih['atas_nama_pengirim']], ['Bank', $terpilih['bank_pengirim']], ['Jenis', $terpilih['jenis_label']], ['Nominal', $terpilih['nominal_formatted']], ['Tanggal transfer', \Carbon\Carbon::parse($terpilih['tanggal_transfer'])->translatedFormat('j F Y')], ['Pemesan', $terpilih['pesanan']['nama'] ?? '— kode tak dikenal —'], ['Pesanan', $terpilih['pesanan']['keterangan'] ?? '—']] as [$label, $nilai])
                                            <div class="col-6">
                                                <p class="text-xs tracking-wider text-uppercase text-muted mb-0"
                                                    style="font-size:.68rem">{{ $label }}</p>
                                                <p class="fw-bold mb-0">{{ $nilai }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if ($terpilih['catatan'])
                                        <div class="mt-3">
                                            <p class="text-uppercase text-muted mb-1" style="font-size:.68rem">Catatan
                                                pelanggan</p>
                                            <div class="p-3 rounded-3 bg-light small">{{ $terpilih['catatan'] }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 col-md-6">
                                    <p class="text-uppercase text-muted mb-1" style="font-size:.68rem">Bukti transfer</p>
                                    @if ($terpilih['bukti'])
                                        <a href="{{ $tautanBukti($terpilih['bukti']) }}" target="_blank" rel="noopener">
                                            <img src="{{ $tautanBukti($terpilih['bukti']) }}" alt="Bukti transfer"
                                                class="img-fluid rounded-3 border">
                                        </a>
                                        <p class="form-text">Klik untuk membuka ukuran penuh.</p>
                                    @else
                                        <p class="text-muted small">Tidak ada berkas bukti.</p>
                                    @endif
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold">Status</label>
                                    <select wire:model="statusBaru" class="form-select">
                                        @foreach ($pilihanStatus as $kunci => $label)
                                            <option value="{{ $kunci }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-7">
                                    <label class="form-label small fw-semibold">Catatan admin</label>
                                    <input type="text" class="form-control" wire:model="catatanAdmin"
                                        placeholder="Mis. cocok dengan mutasi rekening 15 Agu.">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn orcha-bahaya" wire:click="tutup">
                            <i class="bi bi-x-lg"></i> Batal
                        </button>
                        <button type="button" class="btn btn-primary rounded-3" wire:click="simpan"
                            wire:loading.attr="disabled">
                            Simpan Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.pages.admin.orcha.partials.skrip')
</div>
