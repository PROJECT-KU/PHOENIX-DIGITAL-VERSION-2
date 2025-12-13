<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Akun</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Akun']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchDataAkun" type="text" class="form-control"
                        placeholder="ketik nama akun, username..">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.DataAkun.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Data Akun</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table table-striped align-middle text-center nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Name Akun</th>
                            <th>User Name</th>
                            <th style="width: 100px;">Password</th>
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
                                <span class="password-mask" data-password="{{ $item->password_akun }}">
                                    ••••••••
                                </span>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none toggle-password">
                                    <i class="bi bi-eye"></i>
                                </button>
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
                                <a wire:navigate href="{{ route('admin.DataAkun.edit', $item) }}"
                                    class="btn btn-warning btn-sm me-1"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-danger btn-sm delete-DataAkun-btn"
                                    data-id="{{ $item->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
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