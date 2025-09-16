<div>
    <header class="header-content">
        <h3>Role User</h3>
    </header>
    <section class="card mt-4">
        <div class="row">
            <div class="col-12 col-lg-6 p-5">
                <form wire:submit="{{ $roleIdBeingEdited ? 'updateRole' : 'addRole' }}">
                    <div class="mb-4">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Nama Akun</label>
                            <input type="text" id="name" wire:model="name" class="form-control">
                            @error('name')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-medium">Deskripsi Akun</label>
                            <input type="description" id="description" wire:model="description" class="form-control">
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
            <div class="col-12 col-lg-6 p-5">
                <div class="table-responsive">
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
    </section>
    <p>role page</p>
</div>
