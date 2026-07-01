<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Product</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Product']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchDataProduct" type="text" class="form-control ps-5 pe-5" placeholder="Cari product...">

                            @if ($searchDataProduct)
                            <span wire:click="$set('searchDataProduct', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_product'))
                        <a wire:navigate href="{{ route('admin.product.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Data</span>
                        </a>
                        @endif
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
                                <th style="width: 150px;">Nama Akun</th>
                                <th style="width: 80px;">Image</th>
                                <th style="width: 120px;">Harga Awal</th>
                                <th style="width: 120px;">Harga / Bulan</th>
                                <th style="width: 120px;">Harga / 5 Bulan</th>
                                <th style="width: 120px;">Harga / 10 Bulan</th>
                                <th style="width: 120px;">Harga / Tahun</th>
                                <th style="width: 220px;">Deskripsi</th>
                                @if (auth()->user()->hasAnyPermission(['edit_product', 'delete_product']))
                                <th style="width: 100px;">Action</th>
                                @endif
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
                                <td>
                                    @if ($item->image && \Storage::disk('public')->exists('img/Product/' . $item->image))
                                    <img src="{{ asset('storage/img/Product/' . $item->image) }}"
                                        class="rounded shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                        onclick="showGlossyPreview('{{ asset('storage/img/Product/' . $item->image) }}')">
                                    @else
                                    <img src="{{ asset('assets/img/no-image.jpg') }}" class="rounded-3 shadow-sm"
                                        style="width: 60px; height: 60px; object-fit: cover;">
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
                                @if (auth()->user()->hasAnyPermission(['edit_product', 'delete_product']))
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        @if (auth()->user()->hasPermission('edit_product'))
                                        <a wire:navigate
                                            href="{{ route('admin.product.edit', $item) }}"
                                            class="btn btn-sm btn-warning"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_product'))
                                        <button type="button"
                                            class="btn btn-sm btn-danger delete-DataProduct-btn"
                                            data-id="{{ $item->id }}"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
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

                <div class="mt-4 ">
                    {{ $DataProduct->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== END SWEET ALERT PREVIEW IMAGE ==================-->
<script>
    function showGlossyPreview(imageUrl) {
        Swal.fire({
            imageUrl: imageUrl,
            imageAlt: 'Preview Gambar',
            showConfirmButton: false, // Hilangkan tombol OK
            showCloseButton: true, // Tampilkan tombol X di sudut
            width: 'auto', // Sesuaikan lebar dengan gambar
            padding: '1em',
            background: 'rgba(255, 255, 255, 0.65)', // Warna dasar semi-transparan
            backdrop: 'rgba(0, 0, 0, 0.4)', // Latar belakang layar agak gelap
            didOpen: () => {
                // Menyuntikkan efek Glossy Clean (Glassmorphism) langsung ke popup Swal
                const popup = Swal.getPopup();
                popup.style.backdropFilter = 'blur(15px)';
                popup.style.WebkitBackdropFilter = 'blur(15px)';
                popup.style.border = '1px solid rgba(255, 255, 255, 0.4)';
                popup.style.borderRadius = '20px';
                popup.style.boxShadow = '0 8px 32px 0 rgba(0, 0, 0, 0.2)';

                // Merapikan sedikit gambar di dalamnya
                const swalImage = Swal.getImage();
                swalImage.style.borderRadius = '12px';
                swalImage.style.maxHeight = '80vh'; // Agar tidak melebihi tinggi layar
                swalImage.style.objectFit = 'contain';
            }
        });
    }
</script>
<!--================== END SWEET ALERT PREVIEW IMAGE ==================-->

<!--================== SWEET ALERT DELETE ==================-->
<script>
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

    document.addEventListener('livewire:navigated', function() {
        document.body.addEventListener('click', function(event) {
            // 1. UBAH CLASS BUTTON DI SINI
            const button = event.target.closest('.delete-DataProduct-btn');

            if (button) {
                event.preventDefault();
                const promoId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data produk ini tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            // 2. UBAH NAMA METHOD LIVEWIRE DI SINI
                            Livewire.find(livewireComponentId).call('deleteDataProduct', promoId);
                        }
                    }
                });
            }
        });
    });

    // 3. UBAH NAMA EVENT SUKSES DI SINI
    window.addEventListener('product-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data produk berhasil dihapus.',
            icon: 'success',
            timer: 2500, // Otomatis tutup dalam 2.5 detik
            showConfirmButton: false, // Tanpa tombol
            ...glossyConfig
        });
    });

    // 4. UBAH NAMA EVENT GAGAL DI SINI
    window.addEventListener('delete-product-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error',
            timer: 2500, // Otomatis tutup dalam 2.5 detik
            showConfirmButton: false, // Tanpa tombol
            ...glossyConfig
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->