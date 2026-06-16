<div>
    <div class="container-fluid">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Data Promo</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Promo']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="searchDataPromo"
                                type="text"
                                class="form-control ps-5 pe-5"
                                placeholder="Cari promo...">

                            @if($searchDataPromo)
                            <span wire:click="$set('searchDataPromo', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;"
                                title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        <a wire:navigate href="{{ route('admin.promo.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Data</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4" wire:poll.60s>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width: 200px;">Nama Promo</th>
                                <th style="width: 120px;">Kode Promo</th>
                                <th style="width: 100px;">Tipe Promo</th>
                                <th style="width: 120px;">Diskon Member</th>
                                <th style="width: 140px;">Diskon Non-Member</th>
                                <th style="width: 100px;">Status</th>
                                <th style="width: 150px;">Periode</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($promos as $item)
                            <tr style="text-align: center;">
                                <!-- Nama Promo -->
                                <td>
                                    {{ $item->nama_promo }}
                                    @if ($item->show_on_homepage) <br>
                                    <span class="badge bg-info">Homepage</span>
                                    @endif
                                </td>

                                <!-- Kode Promo -->
                                <td class="fw-semibold">
                                    @if ($item->kode_promo)
                                    <span class="badge bg-secondary">{{ $item->kode_promo }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <!-- Tipe Promo -->
                                <td>
                                    @php
                                    $tipeClass = match ($item->tipe_promo) {
                                    'flash_sale' => 'bg-danger',
                                    'kode_promo' => 'bg-primary',
                                    'referral_bonus' => 'bg-success',
                                    default => 'bg-secondary',
                                    };
                                    $tipeText = match ($item->tipe_promo) {
                                    'flash_sale' => 'Flash Sale',
                                    'kode_promo' => 'Kode Promo',
                                    'referral_bonus' => 'Referral',
                                    default => $item->tipe_promo,
                                    };
                                    @endphp
                                    <span class="badge {{ $tipeClass }}">{{ $tipeText }}</span>
                                </td>

                                <!-- Diskon Member -->
                                <td class="fw-semibold">
                                    @if ($item->tipe_diskon === 'persen')
                                    {{ $item->diskon_member_persen }}%
                                    @else
                                    Rp {{ number_format($item->diskon_member_nominal, 0, ',', '.') }}
                                    @endif
                                </td>

                                <!-- Diskon Non-Member -->
                                <td class="fw-semibold">
                                    @if ($item->tipe_diskon === 'persen')
                                    {{ $item->diskon_non_member_persen }}%
                                    @else
                                    Rp {{ number_format($item->diskon_non_member_nominal, 0, ',', '.') }}
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->is_active ? 'Active' : 'Non-active' }}
                                    </span>
                                </td>

                                <!-- Periode -->
                                <td class="small">
                                    <div class="text-dark">{{ $item->mulai_promo->translatedFormat('d F Y H:i') }}</div>
                                    <div class="text-muted small">s/d</div>
                                    <div class="text-dark">{{ $item->selesai_promo->translatedFormat('d F Y H:i') }}</div>
                                </td>

                                <!-- Action -->
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a wire:navigate href="{{ route('admin.promo.edit', $item->id) }}"
                                            class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-promo-btn" data-id="{{ $item->id }}" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-ticket"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                            Belum Ada Data Promo
                                        </h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                            Silakan klik tombol tambah data untuk memasukkan promo baru.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $promos->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>
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
            const button = event.target.closest('.delete-promo-btn');

            if (button) {
                event.preventDefault();
                const promoId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus data?',
                    text: "Data promo ini tidak bisa dikembalikan!",
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
                            Livewire.find(livewireComponentId).call('delete', promoId);
                        }
                    }
                });
            }
        });
    });

    // MENANGKAP EVENT SUKSES
    window.addEventListener('promoDeleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data promo berhasil dihapus.',
            icon: 'success',
            timer: 2500, // Otomatis tutup dalam 2.5 detik
            showConfirmButton: false, // Tanpa tombol
            ...glossyConfig
        });
    });

    // MENANGKAP EVENT GAGAL
    window.addEventListener('promoDeleteError', (e) => {
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