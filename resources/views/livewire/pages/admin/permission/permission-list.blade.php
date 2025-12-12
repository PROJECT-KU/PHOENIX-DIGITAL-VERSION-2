<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Permission Role Akun</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Permission']
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Daftar Role -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Role</h5>
                    <button wire:click="openCreateModal" class="btn btn-primary rounded-pill px-4">
                        <div wire:target="openCreateModal" wire:loading class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span wire:target="openCreateModal" wire:loading.remove>
                            <i class="bi bi-plus-circle"></i> Tambah
                        </span>
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Tampilan Nama</th>
                                    <th>Group</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $item)
                                <tr>
                                    <td>{{ $item->display_name }}</td>
                                    <td>{{ $item->group }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('admin.pengembalian.edit', $item->id) }}"
                                                wire:navigate
                                                class="btn btn-sm btn-warning me-1"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-danger delete-pengembalian-btn"
                                                data-id="{{ $item->id }}"
                                                title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox mb-2 fs-1"></i>
                                            <p>Tidak ada data permission.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $pengembalian->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>