<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Banner</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Akun']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchBanners" type="text" class="form-control"
                        placeholder="ketik Judul Banner, Status..">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.Banners.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Data Banner</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table table-striped align-middle nowrap" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Judul Banner</th>
                            <th>Gambar Banner</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($Banners as $item)
                        <tr>
                            <td>{{ $item->judul }}</td>
                            <td>{{ $item->gambar }}</td>
                            <td class="text-truncate" style="max-width: 200px;">
                                {{ $item->deskripsi }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a wire:navigate href="{{ route('admin.DataAkun.edit', $item) }}"
                                    class="btn btn-outline-secondary btn-sm me-1"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-outline-danger btn-sm delete-DataAkun-btn"
                                    data-id="{{ $item->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Belum ada data banner
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $Banners->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>