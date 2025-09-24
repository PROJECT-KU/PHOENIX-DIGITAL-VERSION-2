<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Product</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Product']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchDataProduct" type="text" class="form-control"
                        placeholder="ketik nama product">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.product.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Product</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table table-striped align-middle nowrap" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Nama Akun</th>
                            <th>Image</th>
                            <th>Harga / 5 Bulan</th>
                            <th>Harga / 10 Bulan</th>
                            <th>Harga / Tahun</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($DataProduct as $item)
                            <tr>
                                <td>{{ $item->nama_akun }}</td>
                                <td class="text-center">
                                    @if ($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}" 
                                            alt="{{ $item->nama_akun }}" 
                                            class="img-thumbnail" style="max-width: 60px;">
                                    @else
                                        <span class="text-muted">No Image</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($item->harga_5_perbulan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->harga_10_perbulan, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->harga_pertahun, 0, ',', '.') }}</td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $item->deskripsi ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <a wire:navigate 
                                    href="{{ route('admin.product.edit', $item) }}"
                                    class="btn btn-outline-secondary btn-sm me-1"
                                    title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm delete-DataProduct-btn"
                                            data-id="{{ $item->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada data produk
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div class="mt-4">
                {{ $DataProduct->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>