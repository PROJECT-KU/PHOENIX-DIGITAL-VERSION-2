<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Banner</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Akun']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchBanners" type="text"
                                class="form-control ps-5 pe-5" placeholder="Cari banner...">

                            @if ($searchBanners)
                                <span wire:click="$set('searchBanners', '')"
                                    class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                    style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                    <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                                </span>
                            @endif
                        </div>
                        <a wire:navigate href="{{ route('admin.Banners.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
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
                            <tr class="text-secondary">
                                <th style="width: 50px;">No</th>
                                <th>Judul Banner</th>
                                <th>Gambar</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($Banners as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $item->judul }}</td>
                                    <td>
                                        @if ($item->gambar && \Storage::disk('public')->exists('img/banners/' . $item->gambar))
                                            <!-- Thumbnail -->
                                            <img src="{{ asset('storage/img/banners/' . $item->gambar) }}"
                                                class="rounded shadow-sm"
                                                style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                data-bs-toggle="modal" data-bs-target="#imageModal{{ $item->id }}">

                                            <!-- Modal -->
                                            <div class="modal fade" id="imageModal{{ $item->id }}" tabindex="-1"
                                                aria-labelledby="imageModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-body text-center p-0">
                                                            <img src="{{ asset('storage/img/banners/' . $item->gambar) }}"
                                                                class="img-fluid rounded">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <img src="{{ asset('assets/img/no-image.jpg') }}" class="rounded-3"
                                                style="width: 60px; height: 40px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td class="text-truncate" style="max-width: 150px;">{{ $item->deskripsi }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a wire:navigate href="{{ route('admin.Banners.edit', $item) }}"
                                            class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-Banners-btn p-2"
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
                                                <i class="bi bi-images"></i>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                                Belum Ada Data Banner
                                            </h5>
                                            <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                                Silakan klik tombol tambah data untuk memasukkan banner baru.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $Banners->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    document.addEventListener('livewire:navigated', function() {

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

        // EVENT DELEGATION: Pasang listener di 'document'
        document.body.addEventListener('click', function(event) {
            // Cek apakah yang diklik adalah tombol delete atau ikon di dalam tombol
            const button = event.target.closest('.delete-Banners-btn');

            if (button) {
                event.preventDefault();
                const BannersId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfig
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mencari komponen Livewire terdekat
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('deleteBanners', BannersId);

                        }
                    }
                });
            }
        });
    });

    // Listener untuk sukses (gunakan window agar tetap menangkap event dari Livewire)
    window.addEventListener('Banners-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            background: 'rgba(255, 255, 255, 0.8)' // Tambahkan config jika perlu
        });
    });

    window.addEventListener('delete-error', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error',
            background: 'rgba(255, 255, 255, 0.8)'
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->
