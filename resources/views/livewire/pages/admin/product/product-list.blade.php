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
                <table id="productTable" class="table align-middle text-center" style="width:100%">
                    <thead class="table-light align-middle">
                        <tr>
                            <th style="width: 150px;">Nama Akun</th>
                            <th style="width: 80px;">Image</th>
                            <th style="width: 120px;">Harga / Bulan</th>
                            <th style="width: 120px;">Harga / 5 Bulan</th>
                            <th style="width: 120px;">Harga / 10 Bulan</th>
                            <th style="width: 120px;">Harga / Tahun</th>
                            <th style="width: 220px;">Deskripsi</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($DataProduct as $item)
                            <tr>
                                <!-- Nama -->
                                <td class="fw-semibold text-capitalize">
                                    {{ $item->nama_akun }}
                                </td>

                                <!-- Image -->
                                <td>
                                    @if ($item->image)
                                        <img src="{{ asset('storage/' . $item->image) }}"
                                            alt="{{ $item->nama_akun }}"
                                            class="rounded shadow-sm"
                                            style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <span class="text-muted fst-italic">No Image</span>
                                    @endif
                                </td>

                                <!-- Harga -->
                                <td class="text-end">{{ 'Rp ' . number_format($item->harga_perbulan, 0, ',', '.') }}</td>
                                <td class="text-end">{{ 'Rp ' . number_format($item->harga_5_perbulan, 0, ',', '.') }}</td>
                                <td class="text-end">{{ 'Rp ' . number_format($item->harga_10_perbulan, 0, ',', '.') }}</td>
                                <td class="text-end">{{ 'Rp ' . number_format($item->harga_pertahun, 0, ',', '.') }}</td>

                                <!-- Deskripsi -->
                                <td class="text-truncate"
                                    style="max-width: 200px;"
                                    data-bs-toggle="tooltip"
                                    title="{{ $item->deskripsi }}">
                                    {{ $item->deskripsi ?? '-' }}
                                </td>

                                <!-- Action -->
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a wire:navigate 
                                            href="{{ route('admin.product.edit', $item) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-DataProduct-btn"
                                                data-id="{{ $item->id }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    Belum ada data produk
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 ">
                {{ $DataProduct->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>