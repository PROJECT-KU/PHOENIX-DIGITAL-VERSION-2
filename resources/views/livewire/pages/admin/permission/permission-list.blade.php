
@section('title')
Data Hak Akses || lemon
@stop
<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .perm-key-badge {
            font-family: 'Courier New', monospace;
            font-size: 0.78rem;
            background: #f1f5f9;
            color: #475569;
            padding: 3px 10px;
            border-radius: 8px;
        }

        .aksi-badge {
            font-size: 0.68rem;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 600;
        }
    </style>

    @php
    $aksiWarna = [
    'view' => ['#eff6ff', '#2563eb', 'Lihat'],
    'view_all' => ['#fef3c7', '#a16207', 'Lihat Semua'],
    'create' => ['#ecfdf5', '#059669', 'Tambah'],
    'edit' => ['#fffbeb', '#d97706', 'Edit'],
    'delete' => ['#fef2f2', '#e11d48', 'Hapus'],
    ];
    $aksiDari = function ($name) {
    if (\Illuminate\Support\Str::startsWith($name, 'view_all_')) return 'view_all';
    return \Illuminate\Support\Str::before($name, '_');
    };
    @endphp

    <div class="container-fluid">
        <!--================== HEADER + TOOLBAR (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Data Permission Akun</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Permission'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 header-action" style="flex: 1 1 auto; max-width: 640px;">
                        <!-- Search -->
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari permission...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>

                        <!-- Filter Group -->
                        <select wire:model.live="filterGroup" class="form-select text-capitalize" style="max-width: 180px;">
                            <option value="">Semua Group</option>
                            @foreach ($groups as $group)
                            <option value="{{ $group['value'] }}">{{ str_replace('_', ' ', $group['label']) }}</option>
                            @endforeach
                        </select>

                        @if ($search || $filterGroup)
                        <button wire:click="resetFilters" type="button"
                            class="btn btn-light d-inline-flex align-items-center justify-content-center px-3"
                            title="Reset filter">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        @endif

                        @if (auth()->user()->hasPermission('create_permission'))
                        <a wire:navigate href="{{ route('admin.account.permission.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4 flex-shrink-0">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== RINGKASAN STAT ==================-->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff;">
                            <i class="bi bi-key-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Permission</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalPermission }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-collection-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Group/Modul</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalGroup }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABEL ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Permission</th>
                                <th>Group</th>
                                <th>Jenis Aksi</th>
                                <th>Deskripsi</th>
                                @if (auth()->user()->hasAnyPermission(['edit_permission', 'delete_permission']))
                                <th class="text-center" width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $item)
                            @php
                            $aksi = $aksiDari($item->name);
                            $w = $aksiWarna[$aksi] ?? ['#f1f5f9', '#475569', ucfirst($aksi)];
                            @endphp
                            <tr style="text-align: center;">
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->display_name }}</div>
                                    <span class="perm-key-badge">{{ $item->name }}</span>
                                </td>
                                <td>
                                    @if ($item->group)
                                    <span class="badge bg-gradient-purple rounded-pill px-3 py-2 text-capitalize">
                                        {{ str_replace('_', ' ', $item->group) }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="aksi-badge" style="background: {{ $w[0] }}; color: {{ $w[1] }};">{{ $w[2] }}</span>
                                </td>
                                <td class="text-muted" style="max-width: 280px;">{{ $item->description ?: '-' }}</td>
                                @if (auth()->user()->hasAnyPermission(['edit_permission', 'delete_permission']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_permission'))
                                    <a href="{{ route('admin.account.permission.edit', $item) }}" wire:navigate
                                        class="btn btn-warning btn-sm text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_permission'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-permission-btn"
                                        data-id="{{ $item->id }}" data-name="{{ $item->display_name }}"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-key"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Tidak Ada Permission</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Belum ada data permission yang cocok.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $permissions->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigPermission = {
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

    document.addEventListener('livewire:navigated', function() {
        document.body.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-permission-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus permission?',
                    text: 'Permission "' + (name || '') + '" akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigPermission
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('delete', id);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->