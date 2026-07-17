@section('title')
Log Aktivitas || lemon
@stop
<div>
    <style>
        .stat-icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .al-mono {
            font-family: 'Courier New', monospace;
            font-size: .78rem;
        }

        .al-msg {
            max-width: 340px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .al-url {
            max-width: 240px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #475569;
        }

        .al-trace {
            background: #0f172a;
            color: #e2e8f0;
            font-family: 'Courier New', monospace;
            font-size: .74rem;
            line-height: 1.5;
            padding: 1rem;
            border-radius: 10px;
            max-height: 340px;
            overflow: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Kartu versi mobile */
        .al-mcard {
            border: 1px solid #eef0f6;
            border-radius: 14px;
            padding: 1rem;
            background: #fff;
        }

        .al-mcard+.al-mcard {
            margin-top: .75rem;
        }

        .al-mcard .al-url {
            max-width: 100%;
            white-space: normal;
            word-break: break-all;
        }

        .al-detail-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 1055;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .al-detail-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 720px;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Log Aktivitas</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Log Aktivitas'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="header-action d-flex flex-column flex-sm-row align-items-stretch gap-2 justify-content-center justify-content-lg-end" style="flex: 1 1 auto; max-width: 560px;">
                        <!-- Search -->
                        <div class="form-group position-relative mb-0 flex-grow-1" style="min-width: 200px;">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.400ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari pesan, error, URL, user...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>

                        @if (auth()->user()->hasPermission('clear_activity_log'))
                        <button type="button" class="btn btn-outline-danger d-flex align-items-center justify-content-center px-3 flex-shrink-0 al-clear-btn">
                            <i class="bi bi-trash3"></i>
                            <span class="ms-2">Bersihkan</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== RINGKASAN STAT ==================-->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 46px; height: 46px; font-size: 1.25rem; border-radius: 14px; background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff;">
                            <i class="bi bi-bug-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.78rem;">Total Error</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalError) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 46px; height: 46px; font-size: 1.25rem; border-radius: 14px; background: linear-gradient(135deg,#f59e0b,#d97706); color:#fff;">
                            <i class="bi bi-hourglass-split"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.78rem;">Request Lambat</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalSlow) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 46px; height: 46px; font-size: 1.25rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-eye-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.78rem;">Total Kunjungan</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalVisit) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 p-lg-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 46px; height: 46px; font-size: 1.25rem; border-radius: 14px; background: linear-gradient(135deg,#16a34a,#22c55e); color:#fff;">
                            <i class="bi bi-calendar-check-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.78rem;">Log Hari Ini</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalHariIni) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== FILTER (pola sama dengan Cashflow) ==================-->
        <div class="card border-0 shadow-sm rounded-4 stat-card mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                            style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <span>Filter</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="filterType" class="form-select rounded-3" style="min-width: 160px;">
                            <option value="">Semua Tipe</option>
                            <option value="visit">Kunjungan</option>
                            <option value="error">Error</option>
                            <option value="slow">Request Lambat</option>
                            <option value="auth">Login/Auth</option>
                        </select>

                        <select wire:model.live="filterLevel" class="form-select rounded-3" style="min-width: 150px;">
                            <option value="">Semua Level</option>
                            <option value="error">Error</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                        </select>

                        <input type="date" wire:model.live="filterTanggal" class="form-control rounded-3" style="min-width: 160px;">

                        @if ($search || $filterType || $filterLevel || $filterTanggal)
                        <button wire:click="resetFilters" type="button"
                            class="btn btn-light-danger rounded-3 d-inline-flex align-items-center justify-content-center"
                            title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== DAFTAR ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-lg-4">

                {{-- ===== TABEL (desktop) ===== --}}
                <div class="table-responsive d-none d-lg-block">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Waktu</th>
                                <th>Tipe</th>
                                <th class="text-start">Pesan</th>
                                <th class="text-start">URL</th>
                                <th>Durasi</th>
                                <th class="text-start">User / Device</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                            <tr style="text-align: center;">
                                <td class="text-nowrap small text-muted">{{ $log->created_at?->format('d M Y') }}<br>{{ $log->created_at?->format('H:i:s') }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->typeColor() }}-subtle text-{{ $log->typeColor() }} border border-{{ $log->typeColor() }} rounded-pill px-3 py-2">
                                        {{ $log->typeLabel() }}
                                    </span>
                                    <div class="small text-muted mt-1">{{ $log->eventLabel() }}</div>
                                </td>
                                <td class="text-start">
                                    <div class="al-msg" title="{{ $log->message }}">{{ $log->message }}</div>
                                    @if ($log->exception_class)
                                    <div class="al-mono text-danger">{{ class_basename($log->exception_class) }}</div>
                                    @endif
                                </td>
                                <td class="text-start">
                                    @if ($log->url)
                                    <div class="al-url al-mono" title="{{ $log->method }} {{ $log->url }}">
                                        @if ($log->method)<span class="badge bg-secondary-subtle text-secondary border rounded-pill me-1" style="font-size:.58rem; vertical-align:middle;">{{ $log->method }}</span>@endif{{ $log->url }}
                                    </div>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->duration_ms !== null)
                                    <span class="badge bg-{{ $log->durationColor() }}-subtle text-{{ $log->durationColor() }} border border-{{ $log->durationColor() }} rounded-pill">
                                        {{ number_format($log->duration_ms) }} ms
                                    </span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-start">
                                    <div class="fw-semibold text-dark">{{ $log->user_name ?? '—' }}</div>
                                    @if ($log->deviceLabel())<div class="text-muted"><i class="bi bi-{{ str_contains($log->deviceLabel(), 'iPhone') || str_contains($log->deviceLabel(), 'Android') ? 'phone' : 'display' }} me-1"></i>{{ $log->deviceLabel() }}</div>@endif
                                    @if ($log->ip)<div class="al-mono text-muted">{{ $log->ip }}</div>@endif
                                </td>
                                <td class="text-center">
                                    <button type="button" wire:click="lihat({{ $log->id }})"
                                        class="btn btn-primary btn-sm p-2" title="Lihat detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    @include('livewire.pages.admin.activity-log._empty')
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ===== KARTU (mobile) ===== --}}
                <div class="d-lg-none">
                    @forelse ($logs as $log)
                    <div class="al-mcard">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="d-flex flex-wrap align-items-center gap-1">
                                <span class="badge bg-{{ $log->typeColor() }}-subtle text-{{ $log->typeColor() }} border border-{{ $log->typeColor() }} rounded-pill">{{ $log->typeLabel() }}</span>
                                <span class="small text-muted">{{ $log->eventLabel() }}</span>
                            </div>
                            @if ($log->duration_ms !== null)
                            <span class="badge bg-{{ $log->durationColor() }}-subtle text-{{ $log->durationColor() }} border border-{{ $log->durationColor() }} rounded-pill flex-shrink-0">{{ number_format($log->duration_ms) }} ms</span>
                            @endif
                        </div>

                        <div class="fw-semibold text-dark mb-1">{{ $log->message }}</div>
                        @if ($log->exception_class)
                        <div class="al-mono text-danger mb-1">{{ class_basename($log->exception_class) }}</div>
                        @endif

                        @if ($log->url)
                        <div class="al-url al-mono mb-2">
                            @if ($log->method)<span class="badge bg-secondary-subtle text-secondary border rounded-pill me-1" style="font-size:.58rem;">{{ $log->method }}</span>@endif{{ $log->url }}
                        </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-end gap-2">
                            <div class="small text-muted">
                                <div><i class="bi bi-clock me-1"></i>{{ $log->created_at?->format('d M Y H:i:s') }}</div>
                                <div><i class="bi bi-person me-1"></i>{{ $log->user_name ?? '—' }}
                                    @if ($log->deviceLabel())· {{ $log->deviceLabel() }}@endif
                                </div>
                                @if ($log->ip)<div class="al-mono"><i class="bi bi-hdd-network me-1"></i>{{ $log->ip }}</div>@endif
                            </div>
                            <button type="button" wire:click="lihat({{ $log->id }})" class="btn btn-primary btn-sm p-2 flex-shrink-0" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="py-5">
                        @include('livewire.pages.admin.activity-log._empty')
                    </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $logs->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== DETAIL ==================-->
    @if ($showDetail && $selected)
    <div class="al-detail-overlay" wire:click.self="tutupDetail">
        <div class="al-detail-card">
            <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $selected->typeColor() }}-subtle text-{{ $selected->typeColor() }} border border-{{ $selected->typeColor() }} rounded-pill">{{ $selected->typeLabel() }}</span>
                    {{ $selected->eventLabel() }}
                </h5>
                <button type="button" class="btn-close" wire:click="tutupDetail"></button>
            </div>
            <div class="p-4">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-muted">Waktu</dt>
                    <dd class="col-sm-9">{{ $selected->created_at?->format('d M Y H:i:s') }}</dd>

                    <dt class="col-sm-3 text-muted">Pesan</dt>
                    <dd class="col-sm-9">{{ $selected->message }}</dd>

                    @if ($selected->duration_ms !== null)
                    <dt class="col-sm-3 text-muted">Durasi</dt>
                    <dd class="col-sm-9">
                        <span class="badge bg-{{ $selected->durationColor() }}-subtle text-{{ $selected->durationColor() }} border border-{{ $selected->durationColor() }} rounded-pill">{{ number_format($selected->duration_ms) }} ms</span>
                    </dd>
                    @endif

                    @if ($selected->exception_class)
                    <dt class="col-sm-3 text-muted">Kelas Error</dt>
                    <dd class="col-sm-9 al-mono text-danger">{{ $selected->exception_class }}</dd>
                    @endif

                    @if ($selected->file)
                    <dt class="col-sm-3 text-muted">Lokasi</dt>
                    <dd class="col-sm-9 al-mono">{{ $selected->file }}@if ($selected->line):{{ $selected->line }}@endif</dd>
                    @endif

                    @if ($selected->status_code)
                    <dt class="col-sm-3 text-muted">HTTP Status</dt>
                    <dd class="col-sm-9">{{ $selected->status_code }}</dd>
                    @endif

                    @if ($selected->url)
                    <dt class="col-sm-3 text-muted">URL</dt>
                    <dd class="col-sm-9 al-mono" style="word-break: break-all;">{{ $selected->method }} {{ $selected->url }}</dd>
                    @endif

                    <dt class="col-sm-3 text-muted">User</dt>
                    <dd class="col-sm-9">{{ $selected->user_name ?? '—' }}@if ($selected->user_id) (ID: {{ $selected->user_id }})@endif</dd>

                    @if ($selected->deviceLabel())
                    <dt class="col-sm-3 text-muted">Device</dt>
                    <dd class="col-sm-9">{{ $selected->deviceLabel() }}</dd>
                    @endif

                    @if ($selected->ip)
                    <dt class="col-sm-3 text-muted">IP</dt>
                    <dd class="col-sm-9 al-mono">{{ $selected->ip }}</dd>
                    @endif

                    @if ($selected->user_agent)
                    <dt class="col-sm-3 text-muted">User Agent</dt>
                    <dd class="col-sm-9 small text-muted" style="word-break: break-all;">{{ $selected->user_agent }}</dd>
                    @endif
                </dl>

                @if ($selected->trace)
                <div class="mt-3">
                    <div class="fw-semibold small mb-2"><i class="bi bi-list-nested me-1"></i>Stack Trace</div>
                    <div class="al-trace">{{ $selected->trace }}</div>
                </div>
                @endif
            </div>
            <div class="p-3 border-top text-end">
                <button type="button" class="btn btn-secondary rounded-3" wire:click="tutupDetail">Tutup</button>
            </div>
        </div>
    </div>
    @endif

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')

    <!--================== SWAL: BERSIHKAN LOG LAMA (glossy, seragam) ==================-->
    <script>
        const glossyConfigActivityLog = {
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

        document.addEventListener('livewire:navigated', function () {
            document.body.addEventListener('click', function (event) {
                const btn = event.target.closest('.al-clear-btn');
                if (!btn) return;
                event.preventDefault();

                if (typeof Swal === 'undefined') return;

                Swal.fire({
                    title: 'Bersihkan log lama?',
                    input: 'number',
                    inputLabel: 'Hapus log yang lebih lama dari (hari):',
                    inputValue: 30,
                    inputAttributes: { min: 0 },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, bersihkan',
                    cancelButtonText: 'Batal',
                    ...glossyConfigActivityLog
                }).then(function (result) {
                    if (result.isConfirmed) {
                        const hari = parseInt(result.value ?? 30, 10);
                        const comp = btn.closest('[wire\\:id]');
                        if (comp) {
                            Livewire.find(comp.getAttribute('wire:id')).call('clearOld', isNaN(hari) ? 30 : hari);
                        }
                    }
                });
            });
        });
    </script>
</div>
