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

        .kry-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER + TOOLBAR (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Data Karyawan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Karyawan'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 header-action" style="flex: 1 1 auto; max-width: 620px;">
                        <!-- Search -->
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari nama atau email...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>

                        <!-- Filter Role -->
                        <select wire:model.live="filterRole" class="form-select text-capitalize" style="max-width: 180px;">
                            <option value="">Semua Role</option>
                            @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>

                        @if ($search || $filterRole)
                        <button wire:click="resetFilters" type="button"
                            class="btn btn-light d-inline-flex align-items-center justify-content-center px-3"
                            title="Reset filter">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                        @endif

                        @if (auth()->user()->hasPermission('create_karyawan'))
                        <a href="{{ route('admin.karyawan.create') }}" wire:navigate
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
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Karyawan</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $totalKaryawan }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Role</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $roles->count() }}</h4>
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
                                <th>Karyawan</th>
                                <th>Jabatan</th>
                                <th>Role</th>
                                <th>Info Bank</th>
                                @if (auth()->user()->hasAnyPermission(['edit_karyawan', 'delete_karyawan']))
                                <th width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $avatarGrad = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
                            @endphp
                            @forelse($users as $user)
                            <tr style="text-align: center;">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="kry-avatar" style="background: linear-gradient(135deg,{{ $avatarGrad[$loop->index % count($avatarGrad)] }});">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $user->detail->jabatan ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-gradient-blue rounded-pill px-3 py-2 text-capitalize">
                                        {{ $user->role->name ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->detail && $user->detail->nama_bank)
                                    <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $user->detail->nama_bank }}</span>
                                    <small class="d-block text-muted">{{ $user->detail->nomor_rekening }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_karyawan', 'delete_karyawan']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_karyawan'))
                                    <a href="{{ route('admin.karyawan.edit', $user) }}" wire:navigate
                                        class="btn btn-warning btn-sm text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_karyawan'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-karyawan-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}"
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
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Karyawan</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tambahkan karyawan baru atau ubah filter pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links('vendor.pagination') }}
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
    const glossyConfigKaryawan = {
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
            const button = event.target.closest('.delete-karyawan-btn');

            if (button) {
                event.preventDefault();
                const karyawanId = button.getAttribute('data-id');
                const karyawanName = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: 'Data karyawan ' + (karyawanName || '') + ' akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigKaryawan
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('delete', karyawanId);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->