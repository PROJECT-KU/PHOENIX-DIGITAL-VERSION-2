@section('title')
Detail Pesanan RSC || PT. Asthana Cipta Mandiri
@stop
<div class="container-fluid">
    <style>
        .of-section {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .of-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        .of-icon.green {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 6px 14px rgba(16, 185, 129, .35);
        }

        .of-icon.amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 6px 14px rgba(217, 119, 6, .35);
        }

        .of-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .dv-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0;
            border-bottom: 1px dashed #eef0f7;
        }

        .dv-row:last-child {
            border-bottom: 0;
        }

        .dv-label {
            color: #64748b;
            font-size: .84rem;
            flex-shrink: 0;
        }

        .dv-value {
            font-weight: 600;
            color: #1e293b;
            font-size: .9rem;
            text-align: right;
            word-break: break-word;
        }

        .dv-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border-radius: 1rem;
            height: 100%;
            background: linear-gradient(135deg, #ffffff, #f8f9ff);
            border: 1px solid #eef0f7;
            box-shadow: 0 6px 18px rgba(108, 99, 255, .06);
        }

        .dv-stat .ico {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .dv-stat .ico i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .ico.g-purple {
            background: linear-gradient(135deg, #7c3aed, #4e46e5);
        }

        .ico.g-green {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .ico.g-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .ico.g-blue {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
        }

        .dv-stat .lbl {
            font-size: .76rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .dv-stat .val {
            font-size: 1.12rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
        }

        .dv-copy {
            cursor: pointer;
            color: #94a3b8;
            transition: color .15s;
        }

        .dv-copy:hover {
            color: #6d28d9;
        }
    </style>

    {{-- Header --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Detail Pemesanan RSC</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Pemesanan RSC', 'url' => route('admin.pesananrsc.index')],
                        ['name' => 'Detail'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0 flex-wrap justify-content-center">
                    @if($batchData)
                    @if($metode_harga === 'per_akun')
                    <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill px-3 py-2"><i class="bi bi-collection-fill me-1"></i>Harga Per Akun</span>
                    @else
                    <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3 py-2"><i class="bi bi-people-fill me-1"></i>Harga Per Peserta</span>
                    @endif
                    @php $sc = $batchData->status === 'baru' ? 'success' : ($batchData->status === 'perpanjang' ? 'info' : ($batchData->status === 'pengganti' ? 'warning' : 'secondary')); @endphp
                    <span class="badge bg-{{ $sc }}-subtle text-{{ $sc }} border border-{{ $sc }} rounded-pill px-3 py-2 text-capitalize">
                        <i class="bi bi-circle-fill me-1" style="font-size:.5rem;vertical-align:middle;"></i>{{ $batchData->status }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($batchData)
    @php
    $durasi = ($batchData->tanggal_mulai_camp && $batchData->tanggal_akhir_camp)
    ? \Carbon\Carbon::parse($batchData->tanggal_mulai_camp)->diffInDays(\Carbon\Carbon::parse($batchData->tanggal_akhir_camp)) + 1
    : null;
    $tgl = fn ($d) => $d ? \Carbon\Carbon::parse($d)->translatedFormat('d M Y') : '-';
    @endphp

    {{-- Stat tiles --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="dv-stat"><span class="ico g-purple"><i class="bi bi-people-fill"></i></span>
                <div>
                    <div class="lbl">Total Peserta</div>
                    <div class="val">{{ $batchData->total_peserta }} orang</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dv-stat"><span class="ico g-green"><i class="bi bi-cash-stack"></i></span>
                <div>
                    <div class="lbl">Total Harga</div>
                    <div class="val">Rp {{ number_format($batchData->total_harga, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            @if($metode_harga === 'per_akun')
            <div class="dv-stat"><span class="ico g-amber"><i class="bi bi-collection-fill"></i></span>
                <div>
                    <div class="lbl">Harga {{ count($akunBreakdown) }} Akun</div>
                    <div class="val">Rp {{ number_format($sumHargaAkun, 0, ',', '.') }}</div>
                </div>
            </div>
            @else
            <div class="dv-stat"><span class="ico g-amber"><i class="bi bi-tag-fill"></i></span>
                <div>
                    <div class="lbl">Harga Satuan</div>
                    <div class="val">Rp {{ number_format($batchData->harga_satuan, 0, ',', '.') }}</div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-6 col-lg-3">
            <div class="dv-stat"><span class="ico g-blue"><i class="bi bi-calendar-range"></i></span>
                <div>
                    <div class="lbl">Durasi Camp</div>
                    <div class="val">{{ $durasi ?? '-' }} hari</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rincian harga per akun (mode Per Akun) --}}
    @if($metode_harga === 'per_akun')
    <div class="of-section p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="of-icon amber"><i class="bi bi-calculator-fill"></i></span>
            <div>
                <h6 class="fw-bold mb-0">Rincian Harga Per Akun</h6>
                <small class="text-muted">Total dihitung dari jumlah harga akun × durasi (bukan per peserta)</small>
            </div>
        </div>
        <div class="row g-2">
            @foreach($akunBreakdown as $ab)
            <div class="col-md-6 col-lg-4">
                <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2" style="background:#fff;">
                    <span class="small">
                        @if($ab['utama'])<i class="bi bi-star-fill text-warning me-1"></i>@else<i class="bi bi-collection me-1 text-primary"></i>@endif
                        {{ $ab['nama'] }}
                        @if($ab['utama'])<span class="badge bg-warning-subtle text-warning border border-warning rounded-pill ms-1" style="font-size:.58rem;">UTAMA</span>@endif
                    </span>
                    <span class="fw-semibold small">Rp {{ number_format($ab['harga'], 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 border-top gap-2">
            <span class="text-muted small">
                <i class="bi bi-calculator me-1"></i>{{ (int) $batchData->jumlah_pemesanan }} bulan × Rp {{ number_format($sumHargaAkun, 0, ',', '.') }}
                <span class="text-secondary">(jumlah harga {{ count($akunBreakdown) }} akun)</span>
            </span>
            <span class="fw-bold text-success fs-6">Rp {{ number_format($batchData->total_harga, 0, ',', '.') }}</span>
        </div>
    </div>
    @endif

    {{-- Info grid --}}
    <div class="row g-3 mb-4">
        {{-- Detail Batch --}}
        <div class="col-lg-4">
            <div class="of-section p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="of-icon"><i class="bi bi-folder2-open"></i></span>
                    <h6 class="fw-bold mb-0">Detail Batch</h6>
                </div>
                <div class="dv-row"><span class="dv-label">Nama Camp</span><span class="dv-value">{{ $batchData->nama_camp }}</span></div>
                <div class="dv-row"><span class="dv-label">Batch</span><span class="dv-value">#{{ $batchData->batch_camp }}</span></div>
                <div class="dv-row"><span class="dv-label">Tanggal Mulai</span><span class="dv-value">{{ $tgl($batchData->tanggal_mulai_camp) }}</span></div>
                <div class="dv-row"><span class="dv-label">Tanggal Akhir</span><span class="dv-value">{{ $tgl($batchData->tanggal_akhir_camp) }}</span></div>
                <div class="dv-row"><span class="dv-label">Durasi</span><span class="dv-value">{{ $durasi ?? '-' }} hari</span></div>
            </div>
        </div>

        {{-- Detail Akun --}}
        <div class="col-lg-4">
            <div class="of-section p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="of-icon green"><i class="bi bi-person-badge-fill"></i></span>
                    <h6 class="fw-bold mb-0">Detail Akun</h6>
                </div>
                <div class="dv-row"><span class="dv-label">Akun</span><span class="dv-value">{{ $batchData->dataakun->nama_akun ?? '-' }}</span></div>
                <div class="dv-row"><span class="dv-label">Username</span><span class="dv-value">{{ $batchData->username ?? '-' }}</span></div>
                <div class="dv-row">
                    <span class="dv-label">Password</span>
                    <span class="dv-value">
                        <span id="password-text">••••••••</span>
                        <button type="button" class="btn btn-sm btn-link p-0 ms-1" onclick="togglePassword()"><i class="bi bi-eye" id="eye-icon"></i></button>
                        <span class="d-none" id="password-real">{{ $batchData->password ?? '-' }}</span>
                    </span>
                </div>
                <div class="dv-row">
                    <span class="dv-label">Link Akses</span>
                    <span class="dv-value">
                        @if($batchData->link_akses)
                        <a href="{{ $batchData->link_akses }}" target="_blank" class="text-decoration-none">{{ Str::limit($batchData->link_akses, 26) }} <i class="bi bi-box-arrow-up-right" style="font-size:.75rem;"></i></a>
                        @else - @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Detail Lainnya --}}
        <div class="col-lg-4">
            <div class="of-section p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="of-icon amber"><i class="bi bi-sliders"></i></span>
                    <h6 class="fw-bold mb-0">Detail Lainnya</h6>
                </div>
                <div class="dv-row"><span class="dv-label">PIC</span><span class="dv-value">{{ $batchData->users->name ?? '-' }}</span></div>
                <div class="dv-row"><span class="dv-label">Status</span><span class="dv-value text-capitalize">{{ $batchData->status }}</span></div>
                <div class="dv-row"><span class="dv-label">Tanggal Pemesanan</span><span class="dv-value">{{ $tgl($batchData->tanggal_pemesanan) }}</span></div>
                <div class="dv-row"><span class="dv-label">Tanggal Berakhir</span><span class="dv-value">{{ $tgl($batchData->tanggal_berakhir) }}</span></div>
            </div>
        </div>
    </div>

    {{-- Akun Tambahan (kredensial saja) --}}
    @if($extraAkuns && $extraAkuns->count())
    <div class="of-section p-4 mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            <span class="of-icon"><i class="bi bi-collection-fill"></i></span>
            <h6 class="fw-bold mb-0">Akun Tambahan <span class="text-muted fw-normal">({{ $extraAkuns->count() }})</span></h6>
        </div>
        <div class="row g-3">
            @foreach($extraAkuns as $ea)
            <div class="col-md-6 col-lg-4" x-data="{ show: false }">
                <div class="border rounded-3 p-3 h-100" style="background:#fff;">
                    <div class="fw-bold text-dark mb-2"><i class="bi bi-person-badge-fill me-1 text-success"></i>{{ $ea->nama_akun ?? optional($ea->dataakun)->nama_akun ?? 'Akun' }}</div>
                    <div class="dv-row"><span class="dv-label">Username</span><span class="dv-value">{{ $ea->username ?? '-' }}</span></div>
                    <div class="dv-row">
                        <span class="dv-label">Password</span>
                        <span class="dv-value">
                            <span x-show="!show">••••••••</span>
                            <span x-show="show" x-cloak>{{ $ea->password ?? '-' }}</span>
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1" @click="show = !show"><i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                        </span>
                    </div>
                    <div class="dv-row">
                        <span class="dv-label">Link</span>
                        <span class="dv-value">
                            @if($ea->link_akses)
                            <a href="{{ $ea->link_akses }}" target="_blank" class="text-decoration-none">{{ Str::limit($ea->link_akses, 22) }} <i class="bi bi-box-arrow-up-right" style="font-size:.7rem;"></i></a>
                            @else - @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Deskripsi --}}
    @if($batchData->deskripsi)
    <div class="of-section p-4 mb-4">
        <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold"><i class="bi bi-card-text text-primary"></i> Deskripsi</div>
        <p class="mb-0 text-muted" style="font-size:.9rem; line-height:1.6;">{{ $batchData->deskripsi }}</p>
    </div>
    @endif

    {{-- Daftar Peserta --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="of-icon" style="width:36px;height:36px;font-size:1rem;"><i class="bi bi-people-fill"></i></span>
                <h6 class="fw-bold mb-0">Daftar Peserta <span class="text-muted fw-normal">({{ $pesertaList->count() }})</span></h6>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    @php $perAkun = $metode_harga === 'per_akun'; @endphp
                    <thead>
                        <tr style="color:#64748b;">
                            <th width="50">No</th>
                            <th>ID Transaksi</th>
                            <th>Nama Pembeli</th>
                            <th>No. Telp</th>
                            <th class="text-center">Durasi</th>
                            @unless($perAkun)
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Total</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesertaList as $index => $peserta)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $peserta->id_transaksi }}</span></td>
                            <td class="fw-semibold text-dark">{{ $peserta->nama_pembeli ?? '-' }}</td>
                            <td>{{ $peserta->telp_pembeli ?? '-' }}</td>
                            <td class="text-center">{{ $peserta->jumlah_pemesanan ?? '-' }} bln</td>
                            @unless($perAkun)
                            <td class="text-end">Rp {{ number_format($peserta->harga_satuan ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($peserta->total ?? 0, 0, ',', '.') }}</td>
                            @endunless
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $perAkun ? 5 : 7 }}" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-inbox"></i></div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Peserta</h6>
                                    <p class="text-muted mb-0" style="font-size:.9rem;">Tidak ada data peserta pada batch ini.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($pesertaList->count() > 0)
                    <tfoot>
                        <tr style="border-top:2px solid #eef0f7;">
                            @if($perAkun)
                            <th colspan="5" class="text-end fs-6 text-success">Total Batch (per akun): Rp {{ number_format($batchData->total_harga, 0, ',', '.') }}</th>
                            @else
                            <th colspan="6" class="text-end text-muted">TOTAL</th>
                            <th class="text-end fs-6 text-success">Rp {{ number_format($batchData->total_harga, 0, ',', '.') }}</th>
                            @endif
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    <script>
        function togglePassword() {
            const passwordText = document.getElementById('password-text');
            const passwordReal = document.getElementById('password-real');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordText.textContent === '••••••••') {
                passwordText.textContent = passwordReal.textContent;
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordText.textContent = '••••••••';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</div>