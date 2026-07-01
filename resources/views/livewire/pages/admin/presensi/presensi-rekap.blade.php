@section('title')
Rekap Presensi || PT. Asthana Cipta Mandiri
@stop

<div>
    <style>
        .pr-stat {
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(248, 249, 255, 0.85));
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.10);
        }

        .pr-stat-ic {
            width: 46px;
            height: 46px;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .pr-stat-ic i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.25rem;
        }

        .pr-act {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .pr-act i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .pr-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-weight: 600;
        }

        .pr-badge i.bi {
            line-height: 1;
        }
    </style>

    <div class="container-fluid">
        {{-- ===== Header ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Rekap Presensi</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Rekap Presensi']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari karyawan...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        <a href="{{ route('admin.presensi.index') }}"
                            class="btn btn-outline-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-fingerprint"></i>
                            <span class="ms-2">Presensi Saya</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Statistik ===== --}}
        <div class="row g-3 mb-4">
            @php
            $cards = [
            ['Total Presensi', $stats['total'], 'bi-collection', 'linear-gradient(135deg,#6c63ff,#4e46e5)'],
            ['Kehadiran', $stats['hadir'], 'bi-person-check-fill', 'linear-gradient(135deg,#10b981,#059669)'],
            ['Lembur', $stats['lembur'], 'bi-moon-stars-fill', 'linear-gradient(135deg,#f59e0b,#d97706)'],
            ['Total Jam', round($stats['menit'] / 60, 1) . ' jam', 'bi-stopwatch-fill', 'linear-gradient(135deg,#0ea5e9,#2563eb)'],
            ];
            @endphp
            @foreach ($cards as [$label, $val, $icon, $grad])
            <div class="col-6 col-lg-3">
                <div class="card pr-stat h-100">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <span class="pr-stat-ic" style="background: {{ $grad }};"><i class="bi {{ $icon }}"></i></span>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="fw-bold fs-5">{{ $val }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ===== Filter ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold flex-shrink-0">
                        <span class="pr-stat-ic" style="width:38px;height:38px;background:linear-gradient(135deg,#6c63ff,#4e46e5);">
                            <i class="bi bi-funnel-fill" style="font-size:1rem;"></i>
                        </span>
                        <span>Filter</span>
                    </div>
                    <div class="row g-2 flex-grow-1 w-100">
                        <div class="col-6 col-md-3">
                            <input type="date" wire:model.live="tanggalDari" class="form-control rounded-3"
                                title="Dari tanggal">
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="date" wire:model.live="tanggalSampai" class="form-control rounded-3"
                                title="Sampai tanggal">
                        </div>
                        <div class="col-8 col-md-4">
                            <select wire:model.live="filterTipe" class="form-select rounded-3">
                                <option value="">Semua Jenis</option>
                                <option value="hadir_offline">Hadir Offline</option>
                                <option value="hadir_online">Hadir Online</option>
                                <option value="lembur">Lembur</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="button" wire:click="resetFilter"
                                class="btn btn-danger rounded-3 w-100 d-inline-flex align-items-center justify-content-center gap-1"
                                title="Reset filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Tabel ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width:50px;">No</th>
                                <th class="text-start">Karyawan</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Masuk</th>
                                <th>Pulang</th>
                                <th>Durasi</th>
                                <th>Jarak</th>
                                <th>Status</th>
                                @if (auth()->user()->hasPermission('view_all_presensi'))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($presensis as $p)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold text-start">{{ $p->user->name ?? '—' }}</td>
                                <td>{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if ($p->tipe === 'hadir_offline')
                                    <span class="badge pr-badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-building-check"></i> Offline</span>
                                    @elseif ($p->tipe === 'hadir_online')
                                    <span class="badge pr-badge bg-info-subtle text-info border border-info"><i class="bi bi-globe2"></i> Online</span>
                                    @else
                                    <span class="badge pr-badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-moon-stars"></i> Lembur</span>
                                    @endif
                                </td>
                                <td>{{ $p->waktu_masuk->format('H:i') }}</td>
                                <td>{{ $p->waktu_pulang ? $p->waktu_pulang->format('H:i') : '—' }}</td>
                                <td>{{ $p->durasi_label }}</td>
                                <td>{{ $p->jarak_masuk_meter !== null ? $p->jarak_masuk_meter . ' m' : '—' }}</td>
                                <td>
                                    @if ($p->status === 'aktif')
                                    <span class="badge bg-success">Berjalan</span>
                                    @else
                                    <span class="badge bg-secondary">Selesai</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasPermission('view_all_presensi'))
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger pr-act delete-presensi-btn"
                                        data-id="{{ $p->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-clipboard-x"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Presensi
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada presensi yang cocok dengan filter saat ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $presensis->links('vendor.pagination') }}</div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT ==================-->

    <script>
        (function () {
            if (window.__presensiRekapBound) return;
            window.__presensiRekapBound = true;

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

            document.body.addEventListener('click', function (event) {
                const btn = event.target.closest('.delete-presensi-btn');
                if (!btn) return;
                event.preventDefault();
                const id = btn.getAttribute('data-id');
                Swal.fire({
                    title: 'Hapus presensi ini?',
                    text: 'Data presensi yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then(function (result) {
                    if (result.isConfirmed) {
                        const comp = btn.closest('[wire\\:id]');
                        if (comp) window.Livewire.find(comp.getAttribute('wire:id')).call('deletePresensi', id);
                    }
                });
            });

            window.addEventListener('presensi-deleted', function () {
                Swal.fire({
                    title: 'Terhapus!', text: 'Data presensi berhasil dihapus.', icon: 'success',
                    timer: 2200, showConfirmButton: false, ...glossyConfig
                });
            });
            window.addEventListener('presensi-deleteError', function (e) {
                Swal.fire({
                    title: 'Gagal!', text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.',
                    icon: 'error', timer: 2500, showConfirmButton: false, ...glossyConfig
                });
            });
        })();
    </script>
</div>
