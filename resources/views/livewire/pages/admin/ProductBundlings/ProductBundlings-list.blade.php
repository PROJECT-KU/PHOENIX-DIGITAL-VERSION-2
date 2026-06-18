<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Paket Bundling</h3>
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
                            <input wire:model.live.debounce.300ms="searchProductBundlings" type="text" class="form-control ps-5 pe-5"
                                placeholder="ketik Nama Paket, Akun, Status..">

                            @if ($searchProductBundlings)
                            <span wire:click="$set('searchProductBundlings', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        <a wire:navigate href="{{ route('admin.Bundlings.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
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
                                <th>Nama Paket</th>
                                <th>Gambar Banner</th>
                                <th>Harga Awal</th>
                                <th>Harga Bundling</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ProductBundlings as $item)
                            <tr style="text-align: center;">
                                <td>{{ $item->nama_paket }}</td>

                                <td>
                                    @if ($item->gambar && \Storage::disk('public')->exists('img/ProductBundlings/' . $item->gambar))
                                    <img src="{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}"
                                        class="rounded shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                        onclick="showGlossyPreview('{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}')">
                                    @else
                                    <img src="{{ asset('assets/img/no-image.jpg') }}" class="rounded-3 shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover;">
                                    @endif
                                </td>

                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $item->harga_awal }}
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
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Produk
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Silakan klik tombol tambah data untuk memasukkan produk baru.
                                        </p>
                                    </div>
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

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    window.showGlossyPreview = function(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Preview Gambar',
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            padding: '1.25rem',
            background: 'rgba(255, 255, 255, 0.85)',
            backdrop: 'rgba(0, 0, 0, 0.5)',
            customClass: {
                popup: 'rounded-4 shadow-lg border border-white',
                image: 'rounded-3 shadow-sm m-0'
            },
            didOpen: () => {
                const img = Swal.getImage();
                if (img) {
                    img.style.maxHeight = '85vh';
                    img.style.maxWidth = '100%';
                    img.style.objectFit = 'contain';
                }
            }
        });
    };

    const glossyConfig = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            confirmButton: 'btn-glossy-confirm',
            cancelButton: 'btn-glossy-cancel',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    if (!window.__pbListDeleteBound) {
        window.__pbListDeleteBound = true;

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-ProductBundlings-btn');
            if (!button) return;

            event.preventDefault();
            const bundlingId = button.getAttribute('data-id');

            Swal.fire({
                title: 'Yakin hapus Data Bundling?',
                text: "Data tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                ...glossyConfig
            }).then((result) => {
                if (result.isConfirmed) {
                    const root = button.closest('[wire\\:id]');
                    if (root) {
                        Livewire.find(root.getAttribute('wire:id')).call('deleteProductBundlings', bundlingId);
                    }
                }
            });
        });

        window.addEventListener('ProductBundlings-deleted', () => {
            Swal.fire({
                title: 'Terhapus!',
                text: 'Data Bundling berhasil dihapus.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false,
                ...glossyConfig
            });
        });

        window.addEventListener('delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: e.detail.message,
                icon: 'error',
                timer: 2500,
                showConfirmButton: false,
                ...glossyConfig
            });
        });
    }
</script>
<!--================== END SWEET ALERT DELETE ==================-->