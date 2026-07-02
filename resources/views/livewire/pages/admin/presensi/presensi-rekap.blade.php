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

        /* ===== Modal Presensi Manual ===== */
        .pr-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1080;
            background: rgba(30, 41, 59, .45);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 3vh 1rem;
            overflow-y: auto;
            animation: prFade .18s ease;
        }

        @keyframes prFade {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .pr-modal {
            width: 100%;
            max-width: 560px;
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, .98), rgba(248, 249, 255, .98));
            box-shadow: 0 24px 60px rgba(30, 41, 59, .28);
            border: 1px solid rgba(108, 99, 255, .15);
            overflow: hidden;
            animation: prPop .2s ease;
        }

        @keyframes prPop {
            from { transform: translateY(-12px) scale(.98); opacity: 0; }
            to { transform: none; opacity: 1; }
        }

        .pr-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid #eef0f6;
        }

        .pr-modal-body {
            padding: 1.35rem;
        }

        .pr-modal-foot {
            display: flex;
            gap: .6rem;
            padding: 1rem 1.35rem 1.35rem;
        }

        .pr-modal-hint {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: rgba(108, 99, 255, .06);
            border: 1px dashed rgba(108, 99, 255, .28);
            border-radius: 10px;
            padding: .6rem .8rem;
            color: #64748b;
            font-size: .82rem;
        }

        .pr-modal-hint i.bi {
            color: #6c63ff;
        }

        /* ===== Popup detail presensi manual ===== */
        .pr-pop {
            text-align: left;
            margin-top: .25rem;
        }

        .pr-pop-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .6rem 0;
            border-bottom: 1px solid #f1f2f7;
        }

        .pr-pop-ic {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: rgba(108, 99, 255, .10);
            color: #6c63ff;
            font-size: 1rem;
        }

        .pr-pop-ic i.bi {
            line-height: 1;
        }

        .pr-pop-label {
            font-size: .68rem;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pr-pop-val {
            color: #1e293b;
            font-weight: 600;
            line-height: 1.3;
        }

        .pr-pop-note {
            margin-top: .6rem;
            background: rgba(108, 99, 255, .06);
            border: 1px dashed rgba(108, 99, 255, .28);
            border-radius: 12px;
            padding: .75rem .85rem;
            color: #334155;
            font-weight: 500;
            line-height: 1.45;
            white-space: pre-wrap;
        }

        .pr-pop-note-head {
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .68rem;
            color: #6c63ff;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            margin-bottom: .35rem;
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
                        @if (auth()->user()->hasPermission('create_presensi_manual'))
                        <button type="button" wire:click="openManual"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-person-plus-fill"></i>
                            <span class="ms-2">Presensikan Manual</span>
                        </button>
                        @endif
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
                    <div class="row g-2 flex-grow-1 w-100 align-items-stretch">
                        <div class="col-6 col-md-3">
                            <input type="date" wire:model.live="tanggalDari" class="form-control rounded-3 h-100"
                                title="Dari tanggal">
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="date" wire:model.live="tanggalSampai" class="form-control rounded-3 h-100"
                                title="Sampai tanggal">
                        </div>
                        <div class="col-8 col-md-4">
                            <select wire:model.live="filterTipe" class="form-select rounded-3 h-100">
                                <option value="">Semua Jenis</option>
                                <option value="hadir_offline">Hadir Offline</option>
                                <option value="hadir_online">Hadir Online</option>
                                <option value="lembur">Lembur</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="button" wire:click="resetFilter"
                                class="btn btn-danger rounded-3 w-100 h-100 d-inline-flex align-items-center justify-content-center gap-1"
                                title="Reset filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span class="d-none d-md-inline">Reset</span>
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
                                <td class="fw-bold text-start">
                                    {{ $p->user->name ?? '—' }}
                                    @if ($p->is_manual)
                                    <div class="mt-1">
                                        <button type="button"
                                            class="badge pr-badge manual-info-btn bg-secondary-subtle text-secondary border border-secondary fw-normal"
                                            style="cursor: pointer;"
                                            data-karyawan="{{ $p->user->name ?? '—' }}"
                                            data-tanggal="{{ $p->tanggal->translatedFormat('d M Y') }}"
                                            data-oleh="{{ $p->dibuatOleh->name ?? '—' }}"
                                            data-alasan="{{ $p->catatan }}"
                                            title="Klik untuk melihat alasan">
                                            <i class="bi bi-pencil-square"></i>
                                            Manual{{ $p->dibuatOleh ? ' • ' . $p->dibuatOleh->name : '' }}
                                            <i class="bi bi-info-circle ms-1"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
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

    {{-- ===== Modal Presensi Manual ===== --}}
    @if (auth()->user()->hasPermission('create_presensi_manual'))
    @if ($showManual)
    <div class="pr-modal-overlay" wire:key="pr-manual-modal">
        <div class="pr-modal">
            <div class="pr-modal-head">
                <div class="d-flex align-items-center gap-2">
                    <span class="pr-stat-ic" style="width:42px;height:42px;background:linear-gradient(135deg,#6c63ff,#4e46e5);">
                        <i class="bi bi-person-plus-fill"></i>
                    </span>
                    <div>
                        <h5 class="fw-bold mb-0">Presensikan Manual</h5>
                        <small class="text-muted">Tanpa batas jarak &amp; waktu — tercatat sebagai entri manual.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" wire:click="closeManual" aria-label="Tutup"></button>
            </div>

            <form wire:submit="saveManual">
                <div class="pr-modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Karyawan</label>
                            <select wire:model="manualUserId"
                                class="form-select rounded-3 @error('manualUserId') is-invalid @enderror">
                                <option value="">— Pilih karyawan —</option>
                                @foreach ($karyawanList as $k)
                                <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                            @error('manualUserId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal</label>
                            <input type="date" wire:model="manualTanggal"
                                class="form-control rounded-3 @error('manualTanggal') is-invalid @enderror">
                            @error('manualTanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis</label>
                            <select wire:model="manualTipe"
                                class="form-select rounded-3 @error('manualTipe') is-invalid @enderror">
                                <option value="hadir_offline">Hadir Offline</option>
                                <option value="hadir_online">Hadir Online</option>
                                <option value="lembur">Lembur</option>
                            </select>
                            @error('manualTipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Masuk</label>
                            <input type="time" wire:model="manualJamMasuk"
                                class="form-control rounded-3 @error('manualJamMasuk') is-invalid @enderror">
                            @error('manualJamMasuk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jam Pulang <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="time" wire:model="manualJamPulang"
                                class="form-control rounded-3 @error('manualJamPulang') is-invalid @enderror">
                            @error('manualJamPulang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Kosongkan bila sesi masih berjalan.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Alasan / Keterangan</label>
                            <textarea wire:model="manualCatatan" rows="2"
                                class="form-control rounded-3 @error('manualCatatan') is-invalid @enderror"
                                placeholder="mis. GPS error, sudah konfirmasi via WhatsApp"></textarea>
                            @error('manualCatatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="pr-modal-hint">
                                <i class="bi bi-shield-check"></i>
                                <span>Entri ini dicatat atas nama <b>{{ auth()->user()->name }}</b> dan diberi label
                                    <b>Manual</b> di rekap untuk transparansi.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pr-modal-foot">
                    <button type="button" wire:click="closeManual"
                        class="btn btn-danger rounded-3 px-4 d-inline-flex align-items-center justify-content-center gap-2"
                        style="height: 48px;">
                        <i class="bi bi-x-lg"></i> <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="btn btn-primary rounded-3 px-4 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                        style="height: 48px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i> <span>Simpan Presensi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endif

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT ==================-->

    @push('scripts')
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

            document.addEventListener('click', function (event) {
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

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.manual-info-btn');
                if (!btn) return;
                event.preventDefault();

                function row(icon, label, val) {
                    const r = document.createElement('div');
                    r.className = 'pr-pop-row';
                    const ic = document.createElement('span');
                    ic.className = 'pr-pop-ic';
                    ic.innerHTML = '<i class="bi ' + icon + '"></i>';
                    const txt = document.createElement('div');
                    const l = document.createElement('div');
                    l.className = 'pr-pop-label';
                    l.textContent = label;
                    const v = document.createElement('div');
                    v.className = 'pr-pop-val';
                    v.textContent = val || '—';
                    txt.appendChild(l);
                    txt.appendChild(v);
                    r.appendChild(ic);
                    r.appendChild(txt);
                    return r;
                }

                const wrap = document.createElement('div');
                wrap.className = 'pr-pop';
                wrap.appendChild(row('bi-person-fill', 'Karyawan', btn.getAttribute('data-karyawan')));
                wrap.appendChild(row('bi-calendar-event', 'Tanggal', btn.getAttribute('data-tanggal')));
                wrap.appendChild(row('bi-person-badge', 'Diinput oleh', btn.getAttribute('data-oleh')));

                const note = document.createElement('div');
                note.className = 'pr-pop-note';
                const nh = document.createElement('div');
                nh.className = 'pr-pop-note-head';
                nh.innerHTML = '<i class="bi bi-chat-left-text-fill"></i> Alasan / Keterangan';
                const nb = document.createElement('div');
                nb.textContent = btn.getAttribute('data-alasan') || '—';
                note.appendChild(nh);
                note.appendChild(nb);
                wrap.appendChild(note);

                Swal.fire({
                    title: 'Detail Presensi Manual',
                    html: wrap,
                    confirmButtonText: 'Tutup',
                    ...glossyConfig
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
            window.addEventListener('presensi-manualSaved', function () {
                Swal.fire({
                    title: 'Tersimpan!', text: 'Presensi manual berhasil dicatat.', icon: 'success',
                    timer: 2200, showConfirmButton: false, ...glossyConfig
                });
            });
            window.addEventListener('presensi-manualError', function (e) {
                Swal.fire({
                    title: 'Gagal!', text: (e.detail && (e.detail.message || (e.detail[0] && e.detail[0].message))) || 'Terjadi kesalahan.',
                    icon: 'error', timer: 2500, showConfirmButton: false, ...glossyConfig
                });
            });
        })();
    </script>
    @endpush
</div>
