<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Pelamar Kerja</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pelamar']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                        placeholder="ketik nama pelamar">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.product.create') }}" class="px-4 btn btn-primary rounded-pill">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Product</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table text-center align-middle" style="width:100%">
                    <thead class="align-middle table-light">
                        <tr>
                            <th style="width: 150px;">Nama</th>
                            <th style="width: 150px;">Email</th>
                            <th style="width: 150px;">No HP</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataPelamar as $item)
                        <tr style="text-align: center;">
                            <!-- Nama -->
                            <td class="fw-semibold text-capitalize">
                                {{ $item->name }}
                            </td>
                            <td class="fw-semibold text-capitalize">
                                {{ $item->email }}
                            </td>
                            <td class="fw-semibold text-capitalize">
                                {{ $item->phone }}
                            </td>

                            <!-- Action -->
                            <td>
                                <div class="gap-2 d-flex justify-content-center">
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
                            <td colspan="8" class="py-3 text-center text-muted">
                                Belum ada data lowongan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 ">
                {{ $dataPelamar->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>