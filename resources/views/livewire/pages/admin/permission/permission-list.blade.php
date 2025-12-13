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
                <!-- Filter Section -->
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="flex-fill d-flex align-items-center gap-3">
                            <div class="form-group mb-0 position-relative has-icon-left w-50 w-lg-25">
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                                    placeholder="ketik nama lowongan">
                                <div class="form-control-icon">
                                    <i class="bi bi-search" style="font-size: 14px;"></i>
                                </div>
                            </div>
                            <select style="width: fit-content;" wire:model.live="filterGroup" class="form-select">
                                <option value="">Semua Group</option>
                                @foreach ($groups as $group)
                                <option value="{{ $group['value'] }}">{{ $group['label'] }}</option>
                                @endforeach
                            </select>
                            <button wire:click="resetFilters" class="btn btn-secondary" style="width: fit-content;">
                                <i class="bi bi-arrow-clockwise"></i> Reset Filter
                            </button>
                        </div>
                        <div class="">
                            <a wire:navigate href="{{ route('admin.account.permission.create') }}" class="px-4 btn btn-primary rounded-pill">
                                <i class="bi bi-plus-lg"></i>
                                <span>Tambah Permission</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Permission</th>
                                    <th>Group Permission</th>
                                    <th>Deskripsi</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $item)
                                <tr>
                                    <td>{{ $item->display_name }}</td>
                                    <td>{{ $item->group }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('admin.account.permission.edit', $item) }}"
                                                wire:navigate
                                                class="btn btn-sm btn-warning me-1"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                wire:click="$dispatch('will-delete-permission-data', {{ $item }})"
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
                        {{ $permissions->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>