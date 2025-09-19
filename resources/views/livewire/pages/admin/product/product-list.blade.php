<div>
<div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Product</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Product']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchProduct" type="text" class="form-control"
                        placeholder="ketik nama product, username..">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.product.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Data Product</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table table-striped table-bordered align-middle nowrap" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Name Akun</th>
                            <th>User Name</th>
                            <th style="width: 100px;">Password</th> <!-- kasih width -->
                            <th>Link Login</th>
                            <th>PJ Akun</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product as $item)
                            <tr>
                                <td>{{ $item->nama_akun }}</td>
                                <td>{{ $item->username_akun }}</td>
                                <td class="text-center">
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
                                <td>{{ $item->pj_akun }}</td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $item->deskripsi }}
                                </td>
                                <td>{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                <td>{{ $item->created_at->format('d-m-Y') }}</td>
                                <td class="text-center">
                                    <a wire:navigate href="{{ route('admin.product.edit', $item) }}"
                                    class="btn btn-outline-secondary btn-sm me-1"
                                    title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    Belum ada produk
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $product->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>
