<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Lowongan Pekerjaan</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Lowongan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                        placeholder="ketik nama lowongan">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                @if (auth()->user()->hasPermission('create_lowongan'))
                <a wire:navigate href="{{ route('admin.lowongan.create') }}" class="px-4 btn btn-primary rounded-pill">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Lowongan</span>
                </a>
                @endif
            </div>

            <div class="table-responsive">
                <table id="productTable" class="table text-center align-middle" style="width:100%">
                    <thead class="align-middle table-light">
                        <tr>
                            <th style="width: 150px;">Nama Lowongan</th>
                            <th style="width: 150px;">Status Lowongan</th>
                            @if (auth()->user()->hasAnyPermission(['edit_lowongan', 'delete_lowongan']))
                            <th style="width: 100px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataLowongan as $lowongan)
                            <tr style="text-align: center;">
                                <td class="fw-semibold text-capitalize">
                                    {{ $lowongan->title }}
                                </td>
                                <td class="fw-semibold text-capitalize">
                                    <span
                                        class="px-3 py-1 rounded-2 {{ $lowongan->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                        {{ $lowongan->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_lowongan', 'delete_lowongan']))
                                <td>
                                    <div class="gap-2 d-flex justify-content-center">
                                        @if (auth()->user()->hasPermission('edit_lowongan'))
                                        <a href="{{ route('admin.lowongan.edit', $lowongan->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_lowongan'))
                                        <button type="button" class="btn btn-sm btn-danger"
                                            wire:click="$dispatch('will-delete-lowongan-data', {{ $lowongan }})"
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
                                <td colspan="3" class="py-3 text-center text-muted">
                                    Belum ada data lowongan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $dataLowongan->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>
