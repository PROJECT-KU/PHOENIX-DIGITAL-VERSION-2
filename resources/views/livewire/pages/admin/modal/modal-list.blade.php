@section('title')
Modal || lemon
@stop

<div>
    <style>
        .md-stat {
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(248, 249, 255, 0.85));
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.10);
        }

        .md-stat-ic {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .md-stat-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.3rem;
        }

        .md-act {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .md-act i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .md-hint {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: rgba(14, 165, 233, .07);
            border: 1px dashed rgba(14, 165, 233, .3);
            border-radius: 10px;
            padding: .6rem .85rem;
            color: #475569;
            font-size: .82rem;
        }

        .md-hint i.bi {
            color: #0ea5e9;
        }

        /* modal overlay */
        .md-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            background: rgba(30, 41, 59, .45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 4vh 1rem;
            overflow-y: auto;
            animation: mdFade .18s ease;
        }

        @keyframes mdFade {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .md-modal {
            width: 100%;
            max-width: 480px;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(248, 249, 255, .98));
            box-shadow: 0 24px 60px rgba(30, 41, 59, .28);
            border: 1px solid rgba(108, 99, 255, .15);
            overflow: hidden;
            animation: mdPop .2s ease;
        }

        @keyframes mdPop {
            from {
                transform: translateY(-12px) scale(.98);
                opacity: 0;
            }

            to {
                transform: none;
                opacity: 1;
            }
        }

        .md-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid #eef0f6;
        }

        .md-modal-body {
            padding: 1.35rem;
        }

        .md-modal-foot {
            display: flex;
            gap: .6rem;
            padding: 1rem 1.35rem 1.35rem;
        }

        .md-rp-field {
            position: relative;
        }

        .md-rp-prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a9bd;
            font-weight: 600;
            pointer-events: none;
            z-index: 2;
        }

        .md-rp-input {
            width: 100%;
            border: 1.5px solid #e7e9f2;
            border-radius: 12px;
            background: #fff;
            padding: 12px 14px 12px 40px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            transition: .18s;
        }

        .md-rp-input:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 .18rem rgba(124, 58, 237, .12);
        }

        .md-rp-input::-webkit-outer-spin-button,
        .md-rp-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .md-rp-input[type=number] {
            -moz-appearance: textfield;
        }

        /* Mobile: tombol Target & Isi ke Target penuh selebar & isi di tengah agar rapi. */
        @media (max-width: 575.98px) {
            .md-target-actions {
                width: 100%;
                flex-direction: column;
            }

            .md-target-actions .btn {
                width: 100%;
                padding-top: .5rem;
                padding-bottom: .5rem;
            }
        }
    </style>

    <div class="container-fluid">
        {{-- ===== Header ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Modal</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Modal']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari modal operasional...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_modal'))
                        <button type="button" wire:click="openCreate"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Top-up Manual</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Filter periode (atas) ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold flex-shrink-0">
                        <span class="md-stat-ic" style="width:38px;height:38px;background:linear-gradient(135deg,#6c63ff,#4e46e5);">
                            <i class="bi bi-funnel-fill" style="font-size:1rem;"></i>
                        </span>
                        <span>Periode</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="bulan" class="form-select rounded-3" style="min-width: 160px;">
                            <option value="">Semua Bulan</option>
                            @foreach ($daftarBulan as $num => $nama)
                            <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="tahun" class="form-select rounded-3" style="min-width: 130px;">
                            <option value="">Semua Tahun</option>
                            @foreach ($daftarTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                            @endforeach
                        </select>
                        @if ($search || $bulan !== '' || $tahun !== '')
                        <button type="button" wire:click="resetFilter"
                            class="btn btn-light-danger rounded-3 d-inline-flex align-items-center justify-content-center"
                            title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="md-hint mt-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><b>Sisa</b> modal operasional otomatis bergulir jadi <b>Saldo Awal</b> bulan berikutnya, sehingga
                        finance cukup <b>top-up kekurangannya</b> menuju target. <b>Terpakai</b> = pengeluaran
                        "Lainnya" <b>+ Pembelian Akun</b> (kas nyata). <b>Modal Pembelian Akun</b> (untuk omset) tidak terpengaruh.</span>
                </div>
            </div>
        </div>

        {{-- ===== Ringkasan Modal Operasional (bergulir) ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <span class="md-stat-ic" style="width:34px;height:34px;background:linear-gradient(135deg,#6c63ff,#4e46e5);">
                            <i class="bi bi-arrow-repeat" style="font-size:1rem;"></i>
                        </span>
                        <span>Modal Operasional (Bergulir)</span>
                    </h6>
                    @if (auth()->user()->hasPermission('create_modal'))
                    <div class="md-target-actions d-flex flex-wrap gap-2">
                        <button type="button" wire:click="openTarget"
                            class="btn btn-sm btn-outline-primary rounded-3 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-bullseye"></i>
                            <span>Target: Rp {{ number_format($target, 0, ',', '.') }}</span>
                        </button>
                        @if ($saranTopUp > 0)
                        <button type="button" wire:click="isiKeTarget"
                            class="btn btn-sm btn-success rounded-3 d-inline-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Isi ke Target (Rp {{ number_format($saranTopUp, 0, ',', '.') }})</span>
                        </button>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="row g-3">
                    @php
                    $miniCards = [
                    ['Saldo Awal', $saldoAwal, 'bi-piggy-bank-fill', 'linear-gradient(135deg,#6c63ff,#4e46e5)', 'dari sisa bulan lalu'],
                    ['Top-up Bulan Ini', $setoranBulan, 'bi-plus-circle-fill', 'linear-gradient(135deg,#10b981,#059669)', 'setoran finance'],
                    ['Terpakai', $terpakai, 'bi-dash-circle-fill', 'linear-gradient(135deg,#ef4444,#dc2626)', 'pengeluaran Lainnya + Pembelian Akun'],
                    ['Sisa', $sisa, 'bi-arrow-right-circle-fill', 'linear-gradient(135deg,#0ea5e9,#2563eb)', '→ ke bulan depan'],
                    ];
                    @endphp
                    @foreach ($miniCards as [$label, $val, $icon, $grad, $note])
                    <div class="col-6 col-lg-3">
                        <div class="card md-stat h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="md-stat-ic" style="width:38px;height:38px;background: {{ $grad }};"><i class="bi {{ $icon }}" style="font-size:1rem;"></i></span>
                                    <div class="text-muted small">{{ $label }}</div>
                                </div>
                                <div class="fw-bold fs-5">Rp {{ number_format($val, 0, ',', '.') }}</div>
                                <div class="text-muted" style="font-size:.72rem;">{{ $note }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Modal Pembelian Akun (otomatis) + rincian per produk ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="md-stat-ic" style="background: linear-gradient(135deg,#0ea5e9,#2563eb);"><i class="bi bi-bag-check-fill"></i></span>
                    <div>
                        <div class="text-muted small">Modal Pembelian Akun <span class="badge bg-info-subtle text-info border border-info fw-normal">otomatis dari pengeluaran</span></div>
                        <div class="fw-bold fs-4 text-primary">Rp {{ number_format($pembelianAkun, 0, ',', '.') }}</div>
                    </div>
                </div>

                <p class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2" style="font-size: .85rem;">
                    <i class="bi bi-box-seam text-primary"></i>
                    <span>Rincian Modal per Produk (periode ini)</span>
                </p>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 text-center">
                        <thead>
                            <tr style="font-size:.78rem;" class="text-muted">
                                <th style="width:40px;">No</th>
                                <th class="text-center">Produk / Akun</th>
                                <th>Tipe</th>
                                <th>Durasi</th>
                                <th>Modal Satuan</th>
                                <th>Order</th>
                                <th>Total Modal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($akunItems as $i => $row)
                            <tr>
                                <td>{{ ($akunPage - 1) * $akunPerPage + $i + 1 }}</td>
                                <td class="fw-semibold">
                                    <span class="badge bg-primary-subtle text-primary border border-primary">
                                        <i class="bi bi-box-seam me-1"></i>{{ $row['nama'] }}
                                    </span>
                                </td>
                                <td>
                                    @if ($row['tipe'] === 'private')
                                    <span class="badge bg-warning-subtle text-warning border border-warning">Private</span>
                                    @else
                                    <span class="badge bg-info-subtle text-info border border-info">Sharing</span>
                                    @endif
                                </td>
                                <td>{{ $row['durasi'] ?? '—' }}</td>
                                <td>{{ $row['satuan'] !== null ? 'Rp ' . number_format($row['satuan'], 0, ',', '.') : '—' }}</td>
                                <td>{{ $row['jumlah'] !== null ? $row['jumlah'] . '×' : '—' }}</td>
                                <td class="fw-bold text-primary">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Modal</h5>
                                        <p class="text-muted mb-0" style="font-size:0.95rem;">Tidak ada modal pembelian akun pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mt-2 mb-0" style="font-size:.75rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    Private: modal = satuan &times; jumlah order (durasi cocok). Sharing: total pembelian akun periode.
                </p>

                @if ($akunTotalPages > 1)
                @php
                $aFirst = ($akunPage - 1) * $akunPerPage + 1;
                $aLast = $aFirst + count($akunItems) - 1;
                $aStart = max($akunPage - 2, 1);
                $aEnd = min($akunPage + 2, $akunTotalPages);
                @endphp
                <nav class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-center gap-2 mt-3">
                    <div class="small text-muted text-center text-sm-start order-2 order-sm-1">
                        Menampilkan <span class="fw-semibold">{{ $aFirst }}</span> sampai
                        <span class="fw-semibold">{{ $aLast }}</span> dari
                        <span class="fw-semibold">{{ $akunTotal }}</span> data
                    </div>
                    <ul class="pagination mb-0 flex-wrap justify-content-center order-1 order-sm-2">
                        @if ($akunPage <= 1)
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">@lang('pagination.previous')</span></li>
                            @else
                            <li class="page-item"><button type="button" class="page-link" wire:click="akunPrev" rel="prev">@lang('pagination.previous')</button></li>
                            @endif

                            @if ($aStart > 1)
                            <li class="page-item"><button type="button" class="page-link" wire:click="akunGoto(1)">1</button></li>
                            @if ($aStart > 2)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                            @endif

                            @for ($i = $aStart; $i <= $aEnd; $i++)
                                @if ($i==$akunPage)
                                <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                                @else
                                <li class="page-item"><button type="button" class="page-link" wire:click="akunGoto({{ $i }})">{{ $i }}</button></li>
                                @endif
                                @endfor

                                @if ($aEnd < $akunTotalPages)
                                    @if ($aEnd < $akunTotalPages - 1)<li class="page-item disabled"><span class="page-link">...</span></li>@endif
                                    <li class="page-item"><button type="button" class="page-link" wire:click="akunGoto({{ $akunTotalPages }})">{{ $akunTotalPages }}</button></li>
                                    @endif

                                    @if ($akunPage < $akunTotalPages)
                                        <li class="page-item"><button type="button" class="page-link" wire:click="akunNext" rel="next">@lang('pagination.next')</button></li>
                                        @else
                                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">@lang('pagination.next')</span></li>
                                        @endif
                    </ul>
                </nav>
                @endif
            </div>
        </div>

        {{-- ===== Tabel Modal Operasional ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-primary d-inline-flex align-items-center" style="line-height:1;"></i>
                    <span>Rincian Top-up Modal Operasional</span>
                </h6>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width:50px;">No</th>
                                <th>Tanggal</th>
                                <th class="text-center">Keterangan</th>
                                <th>Nominal</th>
                                <th>Diinput</th>
                                @if (auth()->user()->hasPermission('edit_modal') || auth()->user()->hasPermission('delete_modal'))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modals as $m)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration + ($modals->currentPage() - 1) * $modals->perPage() }}</td>
                                <td>{{ $m->tanggal->translatedFormat('d M Y') }}</td>
                                <td class="text-center">
                                    {{ $m->deskripsi ?: '—' }}
                                    @php($mFoto = $m->images)
                                    @if (count($mFoto))
                                    <a href="javascript:void(0)" role="button" class="modal-bukti-trigger d-inline-block ms-1 align-middle position-relative" title="Lihat bukti top-up"
                                        data-bukti='@json(collect($mFoto)->map(fn ($p) => \Illuminate\Support\Facades\Storage::url($p))->values())'>
                                        <img src="{{ Storage::url($mFoto[0]) }}" alt="bukti" style="width:26px; height:26px; object-fit:cover; border-radius:6px; border:1px solid #e6e8f2; cursor:zoom-in;">
                                        @if (count($mFoto) > 1)
                                        <span class="badge bg-primary position-absolute top-0 start-100 translate-middle" style="font-size:.5rem;">+{{ count($mFoto) - 1 }}</span>
                                        @endif
                                    </a>
                                    @endif
                                </td>
                                <td class="fw-bold">Rp {{ number_format($m->nominal, 0, ',', '.') }}</td>
                                <td>{{ $m->penginput->name ?? '—' }}</td>
                                @if (auth()->user()->hasPermission('edit_modal') || auth()->user()->hasPermission('delete_modal'))
                                <td>
                                    <div class="d-inline-flex gap-1">
                                        @if (auth()->user()->hasPermission('edit_modal'))
                                        <button type="button" wire:click="openEdit('{{ $m->id }}')"
                                            class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_modal'))
                                        <button type="button" class="btn btn-sm btn-danger p-2 delete-modal-btn"
                                            data-id="{{ $m->id }}" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color:#1e293b !important;">Belum Ada Modal Operasional</h5>
                                        <p class="text-muted mb-0" style="font-size:0.95rem;">Tambahkan setoran modal operasional untuk periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $modals->links('vendor.pagination') }}</div>
            </div>
        </div>
    </div>

    {{-- ===== Modal Form (Tambah/Edit) ===== --}}
    @if (auth()->user()->hasPermission('create_modal') || auth()->user()->hasPermission('edit_modal'))
    @if ($showForm)
    <div class="md-modal-overlay" wire:key="md-form-modal">
        <div class="md-modal">
            <div class="md-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <span class="md-stat-ic" style="width:42px;height:42px;background:linear-gradient(135deg,#6c63ff,#4e46e5);">
                        <i class="bi bi-wallet2"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $editingId ? 'Update' : 'Tambah' }} Modal Operasional</h5>
                        <small class="text-muted">Setoran / dana operasional (manual).</small>
                    </div>
                </div>
                <button type="button" class="btn-close" wire:click="closeForm" aria-label="Tutup"></button>
            </div>

            <form wire:submit="save">
                <div class="md-modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" wire:model="formTanggal"
                                class="form-control rounded-3 @error('formTanggal') is-invalid @enderror">
                            @error('formTanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nominal</label>
                            <div class="md-rp-field">
                                <span class="md-rp-prefix">Rp</span>
                                <input type="text" inputmode="numeric" wire:model="formNominal"
                                    class="md-rp-input rp-money @error('formNominal') is-invalid @enderror" placeholder="0">
                            </div>
                            @error('formNominal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea wire:model="formDeskripsi" rows="2"
                                class="form-control rounded-3 @error('formDeskripsi') is-invalid @enderror"
                                placeholder="mis. Setoran modal operasional bulan ini"></textarea>
                            @error('formDeskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- ===== Bukti top-up: upload gambar / foto kamera (bisa banyak) ===== --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">Bukti Top-up <span class="text-muted fw-normal">(opsional, bisa lebih dari satu)</span></label>
                            <div wire:loading.class="opacity-50" wire:target="tempUpload" class="d-flex flex-column flex-sm-row gap-2">
                                {{-- Pilih dari galeri/file (bisa banyak) --}}
                                <div class="flex-fill" style="border:1.5px dashed #d6d9e6; border-radius:12px; padding:14px; text-align:center; position:relative; background:#fbfcff;">
                                    <input type="file" wire:model="tempUpload" accept="image/*" multiple class="md-gambar-input" style="position:absolute; inset:0; opacity:0; cursor:pointer;">
                                    <i class="bi bi-image" style="font-size:1.5rem; color:#7c3aed;"></i>
                                    <div class="fw-semibold text-dark" style="font-size:.88rem;">Pilih gambar</div>
                                    <div class="text-muted" style="font-size:.74rem;">Bisa banyak · JPG/PNG · maks 4 MB/foto</div>
                                </div>
                                {{-- Ambil foto dari kamera (HP & laptop) --}}
                                <div class="flex-fill" x-data="modalCamera()" wire:ignore>
                                    <div @click="open()" class="h-100" style="border:1.5px dashed #d6d9e6; border-radius:12px; padding:14px; text-align:center; cursor:pointer; background:#fbfcff;">
                                        <i class="bi bi-camera" style="font-size:1.5rem; color:#7c3aed;"></i>
                                        <div class="fw-semibold text-dark" style="font-size:.88rem;">Ambil foto</div>
                                        <div class="text-muted" style="font-size:.74rem;">Langsung dari kamera</div>
                                    </div>
                                    <template x-teleport="body">
                                        <div x-show="showModal" x-cloak @keydown.escape.window="close()">
                                            <div @click="close()" style="position:fixed; inset:0; z-index:1200; background:rgba(0,0,0,.6);"></div>
                                            <div style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1210; width:min(94vw,440px); max-height:92vh; overflow:auto; background:#fff; border-radius:14px; padding:16px; box-shadow:0 12px 40px rgba(0,0,0,.3);">
                                                <div class="fw-bold mb-2 d-flex align-items-center gap-2"><i class="bi bi-camera" style="line-height:1;"></i><span>Ambil Foto</span></div>
                                                <template x-if="error"><div class="alert alert-danger py-2 small mb-2" x-text="error"></div></template>
                                                <video x-ref="video" autoplay playsinline muted x-show="!error" style="width:100%; border-radius:10px; background:#000; aspect-ratio:4/3; object-fit:cover;"></video>
                                                <canvas x-ref="canvas" class="d-none"></canvas>
                                                <div class="d-flex gap-2 mt-3">
                                                    <button type="button" class="btn btn-danger flex-fill" @click="close()">Batal</button>
                                                    <button type="button" class="btn btn-primary flex-fill d-inline-flex align-items-center justify-content-center gap-1" @click="capture()" x-show="!error"><i class="bi bi-camera-fill"></i> <span>Jepret</span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div wire:loading wire:target="tempUpload" class="text-primary small mt-1"><span class="spinner-border spinner-border-sm me-1"></span>Mengunggah...</div>
                            @error('tempUpload.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                            @if(count($fotosLama) || count($fotosBaru))
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($fotosLama as $idx => $path)
                                <div class="position-relative" wire:key="mfl-{{ $idx }}">
                                    <img src="{{ Storage::url($path) }}" style="width:78px; height:78px; object-fit:cover; border-radius:10px; border:1px solid #e6e8f2;">
                                    <button type="button" wire:click="removeFotoLama({{ $idx }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 py-0 px-1" title="Hapus"><i class="bi bi-x"></i></button>
                                </div>
                                @endforeach
                                @foreach($fotosBaru as $idx => $file)
                                <div class="position-relative" wire:key="mfb-{{ $idx }}">
                                    <img src="{{ $file->temporaryUrl() }}" style="width:78px; height:78px; object-fit:cover; border-radius:10px; border:1px solid #7c3aed;">
                                    <span class="badge bg-primary position-absolute bottom-0 start-0 m-1" style="font-size:.55rem;">baru</span>
                                    <button type="button" wire:click="removeFotoBaru({{ $idx }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 py-0 px-1" title="Hapus"><i class="bi bi-x"></i></button>
                                </div>
                                @endforeach
                            </div>
                            <div class="text-muted small mt-1">Total {{ count($fotosLama) + count($fotosBaru) }} gambar. Klik ✕ untuk menghapus.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="md-modal-foot">
                    <button type="button" wire:click="closeForm"
                        class="btn btn-danger rounded-3 px-4 d-inline-flex align-items-center justify-content-center gap-2"
                        style="height: 48px;">
                        <i class="bi bi-x-lg"></i> <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-3 px-4 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                        style="height: 48px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> <span>Simpan Modal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif

    {{-- ===== Modal Atur Target ===== --}}
    @if (auth()->user()->hasPermission('create_modal'))
    @if ($showTarget)
    <div class="md-modal-overlay" wire:key="md-target-modal">
        <div class="md-modal" style="max-width:420px;">
            <div class="md-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <span class="md-stat-ic" style="width:42px;height:42px;background:linear-gradient(135deg,#0ea5e9,#2563eb);">
                        <i class="bi bi-bullseye"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Target Modal Operasional</h5>
                        <small class="text-muted">Acuan top-up otomatis tiap bulan.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" wire:click="closeTarget" aria-label="Tutup"></button>
            </div>
            <form wire:submit="simpanTarget">
                <div class="md-modal-body">
                    <label class="form-label fw-semibold">Target per bulan</label>
                    <div class="md-rp-field">
                        <span class="md-rp-prefix">Rp</span>
                        <input type="text" inputmode="numeric" wire:model="targetInput" class="md-rp-input rp-money" placeholder="0">
                    </div>
                </div>
                <div class="md-modal-foot">
                    <button type="button" wire:click="closeTarget"
                        class="btn btn-danger rounded-3 px-4 d-inline-flex align-items-center justify-content-center gap-2" style="height:48px;">
                        <i class="bi bi-x-lg"></i> <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-3 px-4 flex-grow-1 d-inline-flex align-items-center justify-content-center" style="height:48px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> <span>Simpan Target</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif

    @include('livewire.layout.sweetalert')

    @push('scripts')
    <script>
        (function() {
            if (window.__modalListBound) return;
            window.__modalListBound = true;

            // Format ribuan (rupiah) live pada input .rp-money
            function formatRibuan(digits) {
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            document.addEventListener('input', function(e) {
                var el = e.target.closest && e.target.closest('.rp-money');
                if (!el) return;
                var before = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
                var formatted = formatRibuan(el.value.replace(/\D/g, ''));
                el.value = formatted;
                var count = 0,
                    i = 0;
                for (; i < formatted.length && count < before; i++) {
                    if (/\d/.test(formatted[i])) count++;
                }
                try {
                    el.setSelectionRange(i, i);
                } catch (err) {}
            });

            const glossyConfig = {
                background: 'rgba(255, 255, 255, 0.8)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: {
                    popup: 'swal-glossy-popup',
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                    title: 'swal-glossy-title'
                },
                buttonsStyling: false
            };

            document.addEventListener('click', function(event) {
                const btn = event.target.closest('.delete-modal-btn');
                if (!btn) return;
                event.preventDefault();
                const id = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'Hapus modal ini?',
                    text: 'Data modal operasional yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then(function(result) {
                    if (result.isConfirmed) {
                        const comp = btn.closest('[wire\\:id]');
                        if (comp) window.Livewire.find(comp.getAttribute('wire:id')).call('deleteModal', id);
                    }
                });
            });

            window.addEventListener('modal-saved', function() {
                Swal.fire({
                    title: 'Tersimpan!',
                    text: 'Data modal berhasil disimpan.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    ...glossyConfig
                });
            });
            window.addEventListener('modal-deleted', function() {
                Swal.fire({
                    title: 'Terhapus!',
                    text: 'Data modal berhasil dihapus.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    ...glossyConfig
                });
            });
            window.addEventListener('modal-error', function(e) {
                Swal.fire({
                    title: 'Gagal!',
                    text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.',
                    icon: 'error',
                    timer: 2500,
                    showConfirmButton: false,
                    ...glossyConfig
                });
            });
            window.addEventListener('modal-deleteError', function(e) {
                Swal.fire({
                    title: 'Gagal!',
                    text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.',
                    icon: 'error',
                    timer: 2500,
                    showConfirmButton: false,
                    ...glossyConfig
                });
            });
        })();

        // ===== Kamera untuk "Ambil foto" (HP & laptop via getUserMedia) =====
        window.modalCamera = function () {
            return {
                showModal: false,
                stream: null,
                error: '',
                async open() {
                    this.error = '';
                    this.showModal = true;
                    await this.$nextTick();
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        this.error = 'Browser tidak mendukung kamera. Buka via HTTPS atau localhost.';
                        return;
                    }
                    try {
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                        } catch (e) {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                        }
                        this.$refs.video.srcObject = this.stream;
                    } catch (e) {
                        this.error = 'Tidak bisa mengakses kamera: ' + (e.message || e.name) + '. Pastikan izin kamera diberikan.';
                    }
                },
                capture() {
                    const v = this.$refs.video, c = this.$refs.canvas;
                    if (!v || !v.videoWidth) { this.error = 'Kamera belum siap, coba lagi.'; return; }
                    c.width = v.videoWidth; c.height = v.videoHeight;
                    c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
                    c.toBlob((blob) => {
                        if (!blob) { this.error = 'Gagal mengambil gambar.'; return; }
                        if (blob.size > 4 * 1024 * 1024) {
                            this.close();
                            window.mdShowUploadError('Ukuran foto terlalu besar', 'Maksimal 4 MB. Hasil foto ' + (blob.size / 1024 / 1024).toFixed(1) + ' MB.');
                            return;
                        }
                        const file = new File([blob], 'kamera-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        this.$wire.uploadMultiple('tempUpload', [file], () => {}, () => {}, () => {});
                        this.close();
                    }, 'image/jpeg', 0.9);
                },
                close() {
                    if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
                    this.showModal = false;
                },
            };
        };

        // ===== SweetAlert error seragam (gaya glossy) =====
        window.mdShowUploadError = function (title, text) {
            if (typeof Swal === 'undefined') { alert(title + '\n' + text); return; }
            Swal.fire({
                icon: 'error', title: title, text: text,
                background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold', confirmButton: 'btn-glossy-confirm' },
                buttonsStyling: false, confirmButtonText: 'Mengerti',
            });
        };

        // Validasi gambar SEBELUM Livewire mengunggah (capture phase → lebih dulu).
        if (!window.__mdGambarGuard) {
            window.__mdGambarGuard = true;
            document.addEventListener('change', function (e) {
                const input = e.target.closest && e.target.closest('.md-gambar-input');
                if (!input) return;
                const files = input.files ? Array.from(input.files) : [];
                if (!files.length) return;
                const bukanGambar = files.find(f => !f.type.startsWith('image/'));
                if (bukanGambar) {
                    e.stopImmediatePropagation(); e.preventDefault(); input.value = '';
                    window.mdShowUploadError('Format tidak didukung', 'Semua file harus berupa gambar (JPG/PNG).');
                    return;
                }
                const kebesaran = files.find(f => f.size > 4 * 1024 * 1024);
                if (kebesaran) {
                    e.stopImmediatePropagation(); e.preventDefault(); input.value = '';
                    window.mdShowUploadError('Ukuran gambar terlalu besar', 'Maksimal 4 MB/foto. File "' + kebesaran.name + '" ' + (kebesaran.size / 1024 / 1024).toFixed(1) + ' MB.');
                    return;
                }
            }, true);
        }

        // ===== Popup lihat bukti top-up (glossy). >1 gambar = slider manual, tanpa auto-slide. =====
        window.mdShowBukti = function (images) {
            if (!images || !images.length) return;
            if (typeof Swal === 'undefined') { window.open(images[0], '_blank'); return; }
            const glossy = {
                background: 'rgba(255, 255, 255, 0.92)', backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false, showCloseButton: true, width: 'auto', padding: '1rem',
            };
            if (images.length === 1) {
                Swal.fire(Object.assign({
                    html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + images[0] + '" alt="Bukti" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                }, glossy));
                return;
            }
            let idx = 0;
            const html =
                '<div style="position:relative; max-width:80vw;">' +
                '  <img id="mdBuktiImg" src="' + images[0] + '" style="max-width:100%; max-height:70vh; border-radius:12px; object-fit:contain;">' +
                '  <button type="button" id="mdBuktiPrev" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; left:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-left"></i></button>' +
                '  <button type="button" id="mdBuktiNext" class="btn btn-light rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center" style="position:absolute; top:50%; right:8px; transform:translateY(-50%); width:40px; height:40px;"><i class="bi bi-chevron-right"></i></button>' +
                '  <div id="mdBuktiCounter" class="mt-2 fw-semibold text-muted">1 / ' + images.length + '</div>' +
                '</div>';
            Swal.fire(Object.assign({
                html: html,
                didOpen: function () {
                    const img = document.getElementById('mdBuktiImg');
                    const counter = document.getElementById('mdBuktiCounter');
                    const show = function (i) { idx = (i + images.length) % images.length; img.src = images[idx]; counter.textContent = (idx + 1) + ' / ' + images.length; };
                    document.getElementById('mdBuktiPrev').addEventListener('click', function () { show(idx - 1); });
                    document.getElementById('mdBuktiNext').addEventListener('click', function () { show(idx + 1); });
                    const onKey = function (e) { if (!document.getElementById('mdBuktiImg')) { document.removeEventListener('keydown', onKey); return; } if (e.key === 'ArrowLeft') show(idx - 1); if (e.key === 'ArrowRight') show(idx + 1); };
                    document.addEventListener('keydown', onKey);
                },
            }, glossy));
        };
        if (!window.__mdBuktiBound) {
            window.__mdBuktiBound = true;
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest && e.target.closest('.modal-bukti-trigger');
                if (!trigger) return;
                e.preventDefault();
                let images = [];
                try { images = JSON.parse(trigger.getAttribute('data-bukti') || '[]'); } catch (_) { images = []; }
                window.mdShowBukti(images);
            });
        }
    </script>
    @endpush
</div>