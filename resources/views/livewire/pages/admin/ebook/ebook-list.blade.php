<div>
    <style>
        .ebook-dl-btn {
            padding: .4rem .9rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
            color: #4e46e5;
            background: #eef0ff;
            border: 1px solid #e0e3ff;
            text-decoration: none;
            transition: all .2s ease;
        }

        .ebook-dl-btn:hover {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(78, 70, 229, 0.30);
        }

        .ebook-dl-btn i {
            font-size: .95rem;
            line-height: 1;
        }

        /* Tombol ikon (edit/hapus) persegi & ikon benar-benar di tengah */
        .icon-btn {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn i.bi {
            line-height: 1;
            display: block;
        }
    </style>

    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Ebook Bonus</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Ebook Bonus']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari judul ebook...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_ebook'))
                        <a wire:navigate href="{{ route('admin.ebook.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Ebook</span>
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
                                <th style="width: 50px;">No</th>
                                <th class="text-start">Judul Ebook</th>
                                <th class="text-start">Deskripsi</th>
                                <th>File</th>
                                <th>Status</th>
                                @if (auth()->user()->hasAnyPermission(['edit_ebook', 'delete_ebook']))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ebooks as $item)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-start fw-bold">{{ $item->judul }}</td>
                                <td class="text-start text-truncate" style="max-width: 240px;">
                                    {{ $item->deskripsi ?: '-' }}
                                </td>
                                <td>
                                    @if ($item->file)
                                    <a href="{{ $item->getAdminDownloadUrl() }}" target="_blank" title="Unduh ebook (admin)"
                                        class="ebook-dl-btn d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-arrow-down-fill"></i>
                                        <span>Unduh</span>
                                    </a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_ebook', 'delete_ebook']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_ebook'))
                                    <a wire:navigate href="{{ route('admin.ebook.edit', $item) }}"
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_ebook'))
                                    <button type="button" class="btn btn-sm btn-danger delete-ebook-btn p-2"
                                        data-id="{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Ebook
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tambahkan ebook agar bisa dipilih saat memproses pesanan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center">
                    {{ $ebooks->links('vendor.pagination') }}
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
    const ebookGlossy = {
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
            const button = event.target.closest('.delete-ebook-btn');
            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                Swal.fire({
                    title: 'Yakin hapus ebook?',
                    text: "File ebook ini akan dihapus permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...ebookGlossy
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('deleteEbook', id);
                        }
                    }
                });
            }
        });
    });

    window.addEventListener('Ebook-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Ebook berhasil dihapus.',
            icon: 'success',
            timer: 2200,
            showConfirmButton: false,
            ...ebookGlossy
        });
    });
    window.addEventListener('Ebook-deleteError', (e) => {
        Swal.fire({
            title: 'Gagal!',
            text: e.detail.message,
            icon: 'error',
            timer: 2500,
            showConfirmButton: false,
            ...ebookGlossy
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->