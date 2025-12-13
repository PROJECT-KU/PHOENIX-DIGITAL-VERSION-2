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
                            <th style="width: 120px;">Harga Awal</th>
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
                        <tr style="text-align: center;">
                            <!-- Nama -->
                            <td class="fw-semibold text-capitalize">
                                {{ $item->nama_akun }}
                            </td>

                            <!-- Image -->
                            <td class="text-center">
                                @if ($item->image)
                                    <!-- Thumbnail -->
                                    <img src="{{ asset('storage/img/Product/' . $item->image) }}"
                                        alt="{{ $item->nama_akun }}"
                                        class="rounded shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal{{ $item->id }}">

                                    <!-- Modal -->
                                    <div class="modal fade" id="imageModal{{ $item->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $item->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-body text-center p-0">
                                                    <img src="{{ asset('storage/img/Product/' . $item->image) }}"
                                                        alt="{{ $item->nama_akun }}"
                                                        class="img-fluid rounded">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">No Image</span>
                                @endif
                            </td>

                            <!-- Harga -->
                            <td>{{ $item->formatted('harga_awal') }}</td>
                            <td>{{ $item->formatted('harga_perbulan') }}</td>
                            <td>{{ $item->formatted('harga_5_perbulan') }}</td>
                            <td>{{ $item->formatted('harga_10_perbulan') }}</td>
                            <td>{{ $item->formatted('harga_pertahun') }}</td>

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
                                        class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-danger delete-DataProduct-btn"
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