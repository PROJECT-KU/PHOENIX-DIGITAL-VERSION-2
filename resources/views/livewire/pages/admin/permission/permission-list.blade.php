<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Permission Role Akun</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Permission']
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="form-control ps-5 pe-5" placeholder="ketik nama lowongan">

                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        <select style="width: fit-content;" wire:model.live="filterGroup" class="form-select">
                            <option value="">Semua Group</option>
                            @foreach ($groups as $group)
                            <option value="{{ $group['value'] }}">{{ $group['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        @if (auth()->user()->hasPermission('create_permission'))
                        <a wire:navigate href="{{ route('admin.account.permission.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Permission</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama Permission</th>
                                <th>Group Permission</th>
                                <th>Deskripsi</th>
                                @if (auth()->user()->hasAnyPermission(['edit_permission', 'delete_permission']))
                                <th width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $item)
                            <tr style="text-align: center;">
                                <td>{{ $item->display_name }}</td>
                                <td>{{ $item->group }}</td>
                                <td>{{ $item->description }}</td>
                                @if (auth()->user()->hasAnyPermission(['edit_permission', 'delete_permission']))
                                <td>
                                    <div class="d-flex justify-content-center">
                                        @if (auth()->user()->hasPermission('edit_permission'))
                                        <a href="{{ route('admin.account.permission.edit', $item) }}"
                                            wire:navigate
                                            class="btn btn-sm btn-warning me-1"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_permission'))
                                        <button type="button" class="btn btn-sm btn-danger"
                                            wire:click="$dispatch('will-delete-permission-data', {{ $item }})"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
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

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>