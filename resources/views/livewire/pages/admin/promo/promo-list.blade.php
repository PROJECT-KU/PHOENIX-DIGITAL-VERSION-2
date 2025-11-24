<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Promo</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Promo']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchDataPromo" type="text" class="form-control"
                        placeholder="ketik nama promo">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.promo.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Promo</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table align-middle text-center" style="width:100%">
                    <thead class="table-light align-middle">
                        <tr>
                            <th style="width: 150px;">Nama Promo</th>
                            <th style="width: 80px;">Diskon Rupiah</th>
                            <th style="width: 120px;">Diskon Persen</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($Promo as $item)
                        <tr style="text-align: center;">
                            <!-- Nama -->
                            <td class="fw-semibold text-capitalize">
                                {{ $item->nama_promo }}
                            </td>
                            <td class="fw-semibold text-capitalize">
                                {{ $item->formatted('diskon_rupiah') }}
                            </td>
                            <td class="fw-semibold text-capitalize">
                                {{ $item->percentFormatted($item->diskon_persen) }}
                            </td>

                            <!-- Action -->
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a wire:navigate
                                        href="{{ route('admin.promo.edit', $item) }}"
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
                                Belum ada data promo
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 ">
                {{ $Promo->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>