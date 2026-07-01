
@section('title')
Data Lowongan Pekerjaan || PT. Asthana Cipta Mandiri
@stop
<div>
    <div class="container-fluid">
        <!--================== HEADER + TOOLBAR (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Data Lowongan Pekerjaan</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Lowongan'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 header-action" style="flex: 1 1 auto; max-width: 520px;">
                        <div class="form-group position-relative mb-0 flex-grow-1">
                            <div class="form-control-icon"><i class="bi bi-search"></i></div>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                                placeholder="Cari nama lowongan...">
                            @if ($search)
                            <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_lowongan'))
                        <a wire:navigate href="{{ route('admin.lowongan.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4 flex-shrink-0">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Lowongan</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== TABEL ==================-->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th>Nama Lowongan</th>
                                <th class="text-center">Status</th>
                                @if (auth()->user()->hasAnyPermission(['edit_lowongan', 'delete_lowongan']))
                                <th class="text-center" width="120">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataLowongan as $lowongan)
                            <tr style="text-align: center;">
                                <td class="fw-semibold text-capitalize">{{ $lowongan->title }}</td>
                                <td class="text-center">
                                    @if ($lowongan->is_active === 'active')
                                    <span class="badge bg-gradient-green rounded-pill px-3 py-2">
                                        <i class="bi bi-check-circle me-1"></i>Aktif
                                    </span>
                                    @else
                                    <span class="badge rounded-pill px-3 py-2" style="background:#f1f5f9; color:#64748b;">
                                        <i class="bi bi-slash-circle me-1"></i>Tidak Aktif
                                    </span>
                                    @endif
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_lowongan', 'delete_lowongan']))
                                <td class="text-center text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_lowongan'))
                                    <a href="{{ route('admin.lowongan.edit', $lowongan->id) }}" wire:navigate
                                        class="btn btn-warning btn-sm text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_lowongan'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-lowongan-btn"
                                        data-id="{{ $lowongan->id }}" data-name="{{ $lowongan->title }}"
                                        title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-briefcase"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Lowongan</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tambahkan lowongan baru atau ubah pencarian.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $dataLowongan->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE (GLOSSY, SEPERTI BANNERS) ==================-->
<script>
    const glossyConfigLowongan = {
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
            const button = event.target.closest('.delete-lowongan-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus lowongan?',
                    text: 'Lowongan "' + (name || '') + '" akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigLowongan
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            Livewire.find(component.getAttribute('wire:id')).call('delete', id);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->