<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Akun</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Akun']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchDataAkun" type="text" class="form-control ps-5 pe-5"
                                placeholder="ketik nama akun, username..">

                            @if ($searchDataAkun)
                            <span wire:click="$set('searchDataAkun', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        <a wire:navigate href="{{ route('admin.DataAkun.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Data</span>
                        </a>
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
                                <th>Name Akun</th>
                                <th>User Name</th>
                                <th>Password</th>
                                <th>Link Login</th>
                                <th>PJ Akun</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($DataAkun as $item)
                            <tr style="text-align: center;">
                                <td>{{ $item->nama_akun }}</td>
                                <td>{{ $item->username_akun }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-2 flex-nowrap">
                                        <span class="password-mask text-nowrap mb-0" data-password="{{ $item->password_akun }}">
                                            ••••••••
                                        </span>

                                        <button type="button" class="btn btn-sm btn-link text-decoration-none toggle-password p-0 border-0 lh-1">
                                            <i class="bi bi-eye fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-truncate" style="max-width: 180px;">
                                    <a href="{{ $item->link_login_akun }}" target="_blank">
                                        {{ $item->link_login_akun }}
                                    </a>
                                </td>
                                <td>{{ $item->pj?->name ?? '-' }}</td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $item->deskripsi }}
                                </td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                        <a wire:navigate href="{{ route('admin.DataAkun.edit', $item) }}"
                                            class="btn btn-sm btn-warning text-white p-2"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button"
                                            class="btn btn-danger btn-sm delete-DataAkun-btn"
                                            data-id="{{ $item->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    Belum ada data akun
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                <div class="mt-4">
                    {{ $DataAkun->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>