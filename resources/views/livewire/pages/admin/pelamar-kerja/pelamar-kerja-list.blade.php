<div>
    <!--================== GLOSSY STYLE ==================-->
    <style>
        .stat-icon-wrapper {
            line-height: 1 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }

        .stat-icon-wrapper i {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .plm-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER + SEARCH (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Data Pelamar Kerja</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Pelamar'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="form-group position-relative mb-0 header-action" style="flex: 1 1 auto; max-width: 420px;">
                        <div class="form-control-icon"><i class="bi bi-search"></i></div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                            placeholder="Cari nama, email, telepon, atau posisi...">
                        @if ($search)
                        <span wire:click="$set('search', '')" class="position-absolute end-0 top-50 translate-middle-y pe-3"
                            style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                            <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!--================== FILTER ==================-->
        <div class="card border-0 shadow-sm rounded-4 stat-card overflow-hidden mb-4">
            <div class="card-body p-3 px-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 text-dark fw-semibold">
                        <span class="stat-icon-wrapper bg-gradient-purple flex-shrink-0"
                            style="width: 40px; height: 40px; font-size: 1.1rem; border-radius: 12px;">
                            <i class="bi bi-funnel"></i>
                        </span>
                        <span>Filter Pelamar</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="filterMonth" class="form-select rounded-3" style="min-width: 180px;">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterJob" class="form-select rounded-3 text-capitalize" style="min-width: 180px;">
                            <option value="">Semua Posisi</option>
                            @foreach ($jobList as $job)
                            <option value="{{ $job->id }}">{{ $job->title }}</option>
                            @endforeach
                        </select>
                        @if ($search || $filterMonth || $filterJob)
                        <button wire:click="resetFilters" type="button" class="btn btn-danger rounded-3" title="Reset filter">
                            <i class="bi bi-x-circle"></i>
                        </button>
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
                            <tr>
                                <th>Pelamar</th>
                                <th>Posisi</th>
                                <th>Tanggal Melamar</th>
                                <th class="text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $avatarGrad = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
                            @endphp
                            @forelse ($dataPelamar as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="plm-avatar" style="background: linear-gradient(135deg,{{ $avatarGrad[$loop->index % count($avatarGrad)] }});">
                                            {{ strtoupper(substr($item->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark text-capitalize">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-gradient-blue rounded-pill px-3 py-2 text-capitalize">
                                        {{ $item->job->title ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $item->created_at->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                                <td class="text-center text-nowrap">
                                    <a wire:navigate href="{{ route('admin.pelamar.detail', $item->id) }}"
                                        class="btn btn-warning btn-sm text-white p-2" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if (auth()->user()->hasPermission('delete_pelamar'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-pelamar-btn"
                                        data-id="{{ $item->id }}" data-name="{{ $item->name }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-person-lines-fill"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Pelamar</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada pelamar yang cocok dengan pencarian/filter.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $dataPelamar->links('vendor.pagination') }}
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
    const glossyConfigPelamar = {
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
            const button = event.target.closest('.delete-pelamar-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus pelamar?',
                    text: 'Data pelamar ' + (name || '') + ' (beserta berkas CV) akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigPelamar
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
