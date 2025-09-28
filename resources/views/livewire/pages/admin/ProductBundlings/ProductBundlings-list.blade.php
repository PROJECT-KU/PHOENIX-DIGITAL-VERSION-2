<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Paket Bundling</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Akun']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchProductBundlings" type="text" class="form-control"
                        placeholder="ketik Nama Paket, Akun, Status..">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.Bundlings.create') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Data Bundling</span>
                </a>
            </div>
            <div class="table-responsive">
                <table id="productTable" class="table table-striped align-middle nowrap" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Nama Paket</th>
                            <th>Gambar Banner</th>
                            <th>Harga Bundling</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ProductBundlings as $item)
                        <tr style="text-align: center;">
                            <td>{{ $item->nama_paket }}</td>

                            <td class="text-center">
                                @if ($item->gambar)
                                <img src="{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}"
                                    alt="Banner"
                                    class="img-thumbnail"
                                    style="width: 80px; cursor: pointer;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#bannerModal{{ $item->id }}">
                                @else
                                <span class="text-muted">Tidak ada gambar</span>
                                @endif

                                <!-- Modal Bootstrap -->
                                @if ($item->gambar)
                                <div class="modal fade" id="bannerModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-0">
                                                <img src="{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}"
                                                    alt="Banner {{ $item->judul }}"
                                                    class="img-fluid rounded">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>

                            <td class="text-truncate" style="max-width: 200px;">
                                {{ $item->harga_bundling }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="text-center">
                                <a wire:navigate href="{{ route('admin.Bundlings.edit', $item) }}"
                                    class="btn btn-warning btn-sm me-1"
                                    title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-danger btn-sm delete-ProductBundlings-btn"
                                    data-id="{{ $item->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Belum ada data bundling
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $ProductBundlings->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.delete-ProductBundlings-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const BannersId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus Data Bundling?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                        Livewire.find(livewireComponentId).call('deleteProductBundlings', BannersId);
                    }
                });
            });
        });

        window.addEventListener('ProductBundlings-deleted', () => {
            Swal.fire({
                title: 'Terhapus!',
                text: 'Data Bundling berhasil dihapus.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        window.addEventListener('delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: e.detail.message,
                icon: 'error'
            });
        });

    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->