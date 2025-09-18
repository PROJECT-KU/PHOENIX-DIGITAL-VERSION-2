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
    <section class="card">
        <div class="card-content">
            <div class="card-body">
                <h5 class="card-title">Manajemen Role</h5>
                <div class="row mt-4">
                    <div class="col-12 col-lg-6">
                        <form wire:submit="{{ $roleIdBeingEdited ? 'updateRole' : 'addRole' }}">
                            <div class="mb-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-medium">Nama Role</label>
                                    <input type="text" id="name" wire:model="name" class="form-control">
                                    @error('name')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-medium">Deskripsi Role</label>
                                    <input type="description" id="description" wire:model="description"
                                        class="form-control">
                                    @error('description')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateRole, addRole">
                                    {{ $roleIdBeingEdited ? 'Update Role' : 'Tambah Role' }}
                                </span>
                                <span wire:loading wire:target="updateRole, addRole">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                    menyimpan...
                                </span>
                            </button>
                        </form>
                    </div>
                    <div class="col-12 col-lg-6">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama Role</th>
                                    <th>Deskripsi Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description }}</td>
                                        <td>
                                            <button wire:click="editRole({{ $role->id }})"
                                                class="btn btn-secondary btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                wire:click="deleteRole({{ $role->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <div class="text-center">
                                        <p>role untuk user masih kosong!</p>
                                    </div>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="card">
        <div class="card-content">
            <div class="card-body">
                <h5 class="card-title">Manajemen User Role</h5>
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
                        <table class="table table-striped">
                            <thead>
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
                                                wire:click="confirmDelete({{ $user->id }})">
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
            </div>
        </div>

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
                            <button type="submit" wire:click="updateRoleUser" class="btn btn-danger">
                                Update Role User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if ($this->showDeleteModalStatus && $this->selectedUser)
            <div class="modal d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body p-4">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="modal-title fw-medium mb-2">Hapus Akun User</h5>
                                    <p class="text-muted mb-0">apakah kamu yakin ingin menghapus akun
                                        <strong>{{ $this->selectedUser->name }}</strong> ?
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="cancelModal()" class="btn btn-outline-secondary">
                                Batal
                            </button>
                            <button type="button" wire:click="deleteUser({{ $this->selectedUser->id }})"
                                class="btn btn-danger">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>
