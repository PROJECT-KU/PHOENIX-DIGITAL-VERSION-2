
@section('title')
Data Data Akun || lemon
@stop
<div wire:poll.60s>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Akun</h3>
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

                            <input wire:model.live.debounce.300ms="searchDataAkun" type="text" class="form-control ps-5 pe-5"
                                placeholder="ketik nama akun, username..">

                            @if ($searchDataAkun)
                            <span wire:click="$set('searchDataAkun', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_dataakun'))
                        <a wire:navigate href="{{ route('admin.DataAkun.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
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
                                <th>Name Akun</th>
                                <th>User Name</th>
                                <th>Password</th>
                                <th>Link Login</th>
                                <th>PJ Akun</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                @if (auth()->user()->hasAnyPermission(['edit_dataakun', 'delete_dataakun']))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($DataAkun as $item)
                            <tr style="text-align: center;">
                                <td>{{ $item->nama_akun }}</td>
                                <td>{{ $item->username_akun }}</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-2 flex-nowrap">
                                        <span class="password-mask text-nowrap mb-0" data-password="{{ $item->password_akun }}">
                                            ••••••••
                                        </span>

                                        <button type="button" class="btn btn-sm btn-link text-decoration-none toggle-password p-0 border-0 lh-1">
                                            <i class="bi bi-eye fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-truncate" style="max-width: 180px;">
                                    <a href="{{ $item->link_login_akun }}" target="_blank">
                                        {{ $item->link_login_akun }}
                                    </a>
                                </td>
                                <td>{{ $item->pj?->name ?? '-' }}</td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    {{ $item->deskripsi }}
                                </td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_dataakun', 'delete_dataakun']))
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                        @if (auth()->user()->hasPermission('edit_dataakun'))
                                        <a wire:navigate href="{{ route('admin.DataAkun.edit', $item) }}"
                                            class="btn btn-sm btn-warning text-white p-2"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_dataakun'))
                                        <button type="button"
                                            class="btn btn-sm btn-danger delete-DataAkun-btn"
                                            data-id="{{ $item->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ auth()->user()->hasAnyPermission(['edit_dataakun', 'delete_dataakun']) ? 8 : 7 }}" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-{{ $searchDataAkun ? 'search' : 'person-badge' }}"></i>
                                        </div>
                                        @if ($searchDataAkun)
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Akun Tidak Ditemukan
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Tidak ada akun yang cocok dengan pencarian "{{ $searchDataAkun }}".
                                        </p>
                                        @else
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Akun
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Silakan klik tombol tambah data untuk memasukkan akun baru.
                                        </p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                <div class="mt-4">
                    {{ $DataAkun->links('vendor.pagination') }}
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
            const button = event.target.closest('.delete-DataAkun-btn');

            if (button) {
                event.preventDefault();
                const promoId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data Akun ini tidak bisa dikembalikan!",
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
                            Livewire.find(livewireComponentId).call('deleteDataAkun', promoId);
                        }
                    }
                });
            }
        });
    });

    // MENANGKAP EVENT SUKSES
    window.addEventListener('DataAkunDeleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data Akun berhasil dihapus.',
            icon: 'success',
            timer: 2500, // Otomatis tutup dalam 2.5 detik
            showConfirmButton: false, // Tanpa tombol
            ...glossyConfig
        });
    });

    // MENANGKAP EVENT GAGAL
    window.addEventListener('DataAkunDeleteError', (e) => {
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