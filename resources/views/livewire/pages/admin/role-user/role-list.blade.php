
@section('title')
Data Role || PT. Asthana Cipta Mandiri
@stop
<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .role-tabs {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            gap: 6px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.08);
        }

        .role-tab {
            display: inline-flex;
            flex: 1 1 0;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border: none;
            background: transparent;
            border-radius: 13px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b;
            white-space: nowrap;
            transition: 0.3s;
        }

        .role-tab i {
            font-size: 1.05rem;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .role-tab:hover {
            color: #7c3aed;
            background: rgba(139, 92, 246, 0.08);
        }

        .role-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 6px 16px rgba(124, 58, 237, 0.3);
        }

        .stat-icon-wrapper {
            line-height: 1 !important;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        @media (max-width: 575px) {
            .role-tab {
                flex: 1 1 auto;
                padding: 10px 14px;
            }
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER + TOOLBAR (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Manajemen Role & Akun</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Manajemen Role & Akun'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 header-action" style="flex: 1 1 auto; max-width: 560px;">
                        @if ($activeTab === 'tab-role')
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="searchRole" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari nama atau deskripsi role...">
                            @if ($searchRole)
                            <span wire:click="$set('searchRole', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_roles'))
                        <button class="btn btn-primary d-flex align-items-center justify-content-center px-4 flex-shrink-0"
                            wire:click="showModalFormRole">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Role</span>
                        </button>
                        @endif
                        @else
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="searchUser" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari nama atau email user...">
                            @if ($searchUser)
                            <span wire:click="$set('searchUser', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABS ==================-->
        <div class="role-tabs mb-4">
            <button type="button" class="role-tab @if ($activeTab === 'tab-role') active @endif" wire:click="setTab('tab-role')">
                <i class="bi bi-shield-lock"></i>
                <span>Data Role Akun</span>
            </button>
            <button type="button" class="role-tab @if ($activeTab === 'tab-user') active @endif" wire:click="setTab('tab-user')">
                <i class="bi bi-person-badge"></i>
                <span>Data Role User</span>
            </button>
        </div>

        @if ($activeTab === 'tab-role')
        <!--================== TAB ROLE ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama Role</th>
                                <th>Deskripsi Role</th>
                                <th class="text-center">Jumlah Permission</th>
                                @if (auth()->user()->hasAnyPermission(['edit_roles', 'delete_roles']))
                                <th class="text-center" width="160">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                            <tr style="text-align: center;">
                                <td class="fw-semibold text-capitalize">{{ $role->name }}</td>
                                <td class="text-muted">{{ $role->description ?: '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-gradient-purple rounded-pill px-3 py-2">
                                        {{ $role->permissions_count }} Permission
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_roles', 'delete_roles']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_roles'))
                                    <a href="{{route('admin.account.role.permission', $role)}}"
                                        class="btn btn-sm btn-primary p-2" title="Kelola Permission">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                    <button wire:click="showModalFormRole({{ $role->id }})"
                                        class="btn btn-warning btn-sm text-white p-2" title="Edit Role">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_roles'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-role-btn"
                                        data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                        title="Hapus Role">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-shield-lock"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Role</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tambahkan role baru untuk mulai mengatur akses.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $roles->links('vendor.pagination') }}
                </div>
            </div>
        </div>
        @endif

        @if($activeTab === 'tab-user')
        <!--================== TAB USER ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama User</th>
                                <th>Email User</th>
                                <th class="text-center">Role User</th>
                                @if (auth()->user()->hasAnyPermission(['edit_roles', 'delete_roles']))
                                <th class="text-center" width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                            <tr style="text-align: center;">
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td class="text-muted">{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-gradient-blue rounded-pill px-3 py-2 text-capitalize">{{ $user->role->name ?? '-' }}</span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_roles', 'delete_roles']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_roles'))
                                    <button type="button" wire:click="showModalEdit({{ $user->id }})"
                                        class="btn btn-warning btn-sm text-white p-2" title="Ubah Role">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_roles'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-user-btn"
                                        data-id="{{ $user->id }}" data-name="{{ $user->name }}" title="Hapus User">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada User</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada user yang ditemukan.</p>
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
        @endif
    </div>

    <!--================== MODAL CREATE & EDIT ROLE ==================-->
    @if ($this->showCreateRoleModalStatus || $this->roleIdBeingEdited !== null)
    <div class="modal d-block" style="background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(3px);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                            style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                            <i class="bi bi-shield-lock-fill"></i>
                        </span>
                        <h5 class="fw-bold mb-0">{{ $roleIdBeingEdited ? 'Edit Data Role' : 'Tambah Role' }}</h5>
                    </div>
                    <form wire:submit.prevent="{{ $roleIdBeingEdited ? 'updateRole' : 'addRole' }}">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Role</label>
                            <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                placeholder="mis. finance, customer-service" wire:model="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-2">
                            <label for="description" class="form-label fw-semibold">Deskripsi Role</label>
                            <input type="text" id="description" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Deskripsi singkat role" wire:model="description">
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" wire:click="cancelModal" class="btn btn-danger rounded-pill px-3">Batal</button>
                    <button type="button" wire:click="{{ $roleIdBeingEdited ? 'updateRole' : 'addRole' }}"
                        class="btn btn-primary rounded-pill px-3 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        {{ $roleIdBeingEdited ? 'Simpan Perubahan' : 'Tambah Role' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!--================== MODAL EDIT USER ==================-->
    @if ($this->showEditModalStatus && $this->selectedUser)
    <div class="modal d-block" style="background-color: rgba(15, 23, 42, 0.5); backdrop-filter: blur(3px);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="stat-icon-wrapper bg-gradient-blue flex-shrink-0"
                            style="width: 42px; height: 42px; font-size: 1.15rem; border-radius: 13px;">
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        <h5 class="fw-bold mb-0">Ubah Role Akun</h5>
                    </div>
                    <form wire:submit.prevent="updateRoleUser">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Nama</label>
                            <input type="text" disabled id="username" class="form-control bg-light" wire:model="username">
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label fw-semibold">Email</label>
                            <input type="text" disabled id="userEmail" class="form-control bg-light" wire:model="userEmail">
                        </div>
                        <div class="mb-2">
                            <label for="userRole" class="form-label fw-semibold">Role User</label>
                            <select id="userRole" wire:model="userRole" class="form-select @error('userRole') is-invalid @enderror">
                                @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            @error('userRole') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" wire:click="cancelModal" class="btn btn-danger rounded-pill px-3">Batal</button>
                    <button type="button" wire:click="updateRoleUser"
                        class="btn btn-primary rounded-pill px-3 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2-circle"></i>
                        Update Role User
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigRole = {
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
            const roleBtn = event.target.closest('.delete-role-btn');
            const userBtn = event.target.closest('.delete-user-btn');

            // Hapus ROLE
            if (roleBtn) {
                event.preventDefault();
                const id = roleBtn.getAttribute('data-id');
                const name = roleBtn.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus role?',
                    text: 'Role ' + (name || '') + ' akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigRole
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = roleBtn.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('deleteRole', id);
                        }
                    }
                });
            }

            // Hapus USER
            if (userBtn) {
                event.preventDefault();
                const id = userBtn.getAttribute('data-id');
                const name = userBtn.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus user?',
                    text: 'Data user ' + (name || '') + ' akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigRole
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = userBtn.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('deleteUser', id);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->