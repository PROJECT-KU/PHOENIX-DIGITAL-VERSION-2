<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Manajemen Role & Akun</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Manajemen Role & Akun'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'tab-role') active @endif" wire:click="setTab('tab-role')">
                        <i class="bi bi-shield-lock me-1"></i>
                        <span>Data Role akun</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'tab-user') active @endif"
                        wire:click="setTab('tab-user')">
                        <i class="bi bi-person-badge me-1"></i>
                        <span>Data Role User</span>
                    </button>
                </li>
            </ul>

            @if ($activeTab === 'tab-role')
            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="w-25">
                        <div class="form-group position-relative has-icon-left">
                            <input wire:model.live.debounce.300ms="searchRole" type="text" class="form-control"
                                placeholder="masukan nama role">
                            <div class="form-control-icon">
                                <i class="bi bi-search" style="font-size: 14px;"></i>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" wire:click="showModalFormRole">
                        <i class="bi bi-plus"></i>
                        <span>Tambah Role</span>
                    </button>
                </div>
                <table class="table text-center table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Role</th>
                            <th>Deskripsi Role</th>
                            <th>Jumlah Permission</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                        <tr>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->description }}</td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $role->permissions_count }} Permission
                                </span>
                            </td>
                            <td>
                                <a href="{{route('admin.account.role.permission', $role)}}"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="tooltip"
                                    title="Kelola Permission">
                                    <i class="bi bi-gear"></i>
                                </a>
                                <button wire:click="showModalFormRole({{ $role->id }})"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="tooltip"
                                    title="Edit Role">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm"
                                    wire:click="$dispatch('will-delete-role-data', { id: {{ $role->id }} })"
                                    data-bs-toggle="tooltip"
                                    title="Hapus Role">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                <p class="text-muted mb-0">Role untuk user masih kosong!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif

            @if($activeTab === 'tab-user')
            <div class="row mt-4">
                <div class="col-12 ">
                    <div class="w-25 mb-2">
                        <div class="form-group position-relative has-icon-left">
                            <input wire:model.live.debounce.300ms="searchUser" type="text" class="form-control"
                                placeholder="masukan nama atau email user">
                            <div class="form-control-icon">
                                <i class="bi bi-search" style="font-size: 14px;"></i>
                            </div>
                        </div>
                    </div>
                    <table class="table text-center table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Nama User</th>
                                <th>Email User</th>
                                <th>Role User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name }}</td>
                                <td>
                                    <button type="button" wire:click="showModalEdit({{ $user->id }})"
                                        class="btn btn-secondary btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm"
                                        wire:click="$dispatch('will-delete-user-data', { userId: {{ $user->id }} })">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="text-center">
                                <td colspan="4">role untuk user masih kosong!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- pagination --}}
                    <div class="mt-4">
                        {{ $users->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Modal Create & Edit role -->
        @if ($this->showCreateRoleModalStatus || $this->roleIdBeingEdited !== null)
        <div class="modal d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h5 class="modal-title fw-medium mb-2">{{ $roleIdBeingEdited ? 'Edit Data Role' : 'Tambah Role' }}</h5>
                                <form>
                                    <div class="form-group">
                                        <label for="name">Nama Role</label>
                                        <input type="text" id="name" class="form-control"
                                            placeholder="nama role" wire:model="name">
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Deskripsi Role</label>
                                        <input type="text" id="description" class="form-control"
                                            placeholder="deskripsi role" wire:model="description">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancelModal" class="btn btn-outline-secondary">
                            Batal
                        </button>
                        <button type="submit" wire:click="{{ $roleIdBeingEdited ? 'updateRole' : 'addRole' }}" class="btn btn-primary">
                            {{ $roleIdBeingEdited ? 'Simpan Perubahan Role' : 'Tambah Role' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Modal Edit User --}}
        @if ($this->showEditModalStatus && $this->selectedUser)
        <div class="modal d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h5 class="modal-title fw-medium mb-2">Edit Role Akun</h5>
                                <form>
                                    <div class="form-group">
                                        <label for="username">nama</label>
                                        <input type="text" disabled id="username" class="form-control"
                                            placeholder="nama user" wire:model="username">
                                    </div>
                                    <div class="form-group">
                                        <label for="userEmail">email</label>
                                        <input type="text" disabled id="userEmail" class="form-control"
                                            placeholder="nama user" wire:model="userEmail">
                                    </div>
                                    <div class="form-group">
                                        <label for="userRole">role user</label>
                                        <select id="userRole" wire:model="userRole" class="form-select">
                                            @foreach ($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancelModal" class="btn btn-outline-secondary">
                            Batal
                        </button>
                        <button type="submit" wire:click="updateRoleUser" class="btn btn-primary">
                            Update Role User
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>