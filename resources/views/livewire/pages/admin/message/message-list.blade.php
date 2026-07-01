
@section('title')
Data Pesan Masuk || PT. Asthana Cipta Mandiri
@stop
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

        .msg-avatar {
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

        .row-unread {
            background: #fffbeb;
        }
    </style>

    <div class="container-fluid">
        <!--================== HEADER + SEARCH (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Pesan Masuk</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [
                            ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                            ['name' => 'Data Pesan Masuk'],
                            ];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="form-group position-relative mb-0 header-action" style="flex: 1 1 auto; max-width: 420px;">
                        <div class="form-control-icon"><i class="bi bi-search"></i></div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                            placeholder="Cari nama, email, atau isi pesan...">
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

        <!--================== RINGKASAN STAT ==================-->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#2563eb,#0ea5e9); color:#fff;">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Total Pesan</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $messages->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <span class="stat-icon-wrapper flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.3rem; border-radius: 14px; background: linear-gradient(135deg,#d97706,#f59e0b); color:#fff;">
                            <i class="bi bi-envelope-exclamation-fill"></i>
                        </span>
                        <div>
                            <p class="text-muted fw-semibold mb-1" style="font-size: 0.8rem;">Belum Dibaca</p>
                            <h4 class="fw-bold mb-0 text-dark">{{ $unreadCount }}</h4>
                        </div>
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
                        <span>Filter Pesan</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                        <select wire:model.live="filterMonth" class="form-select rounded-3" style="min-width: 180px;">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterStatus" class="form-select rounded-3" style="min-width: 160px;">
                            <option value="">Semua Status</option>
                            <option value="unread">Belum Dibaca</option>
                            <option value="read">Sudah Dibaca</option>
                        </select>
                        @if ($search || $filterMonth || $filterStatus)
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
                                <th>Pengirim</th>
                                <th class="text-center">Status</th>
                                <th>Tanggal</th>
                                <th class="text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $avatarGrad = ['#7c3aed,#6d28d9', '#2563eb,#0ea5e9', '#059669,#10b981', '#e11d48,#f43f5e', '#d97706,#f59e0b'];
                            @endphp
                            @forelse ($messages as $item)
                            <tr class="{{ is_null($item->read_at) ? 'row-unread' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="msg-avatar" style="background: linear-gradient(135deg,{{ $avatarGrad[$loop->index % count($avatarGrad)] }});">
                                            {{ strtoupper(substr($item->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark text-capitalize">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if (is_null($item->read_at))
                                    <span class="badge rounded-pill px-3 py-2" style="background:#fef3c7; color:#a16207;">
                                        <i class="bi bi-dot"></i>Baru
                                    </span>
                                    @else
                                    <span class="badge rounded-pill px-3 py-2" style="background:#dcfce7; color:#15803d;">
                                        <i class="bi bi-check-circle me-1"></i>Dibaca
                                    </span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $item->created_at->diffForHumans() }}</td>
                                <td class="text-center text-nowrap">
                                    <a wire:navigate href="{{ route('admin.message.detail', $item) }}"
                                        class="btn btn-warning btn-sm text-white p-2" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if (auth()->user()->hasPermission('delete_message'))
                                    <button type="button" class="btn btn-danger btn-sm p-2 delete-message-btn"
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
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Pesan</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Tidak ada pesan yang cocok dengan pencarian/filter.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $messages->links('vendor.pagination') }}
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
    const glossyConfigMessage = {
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
            const button = event.target.closest('.delete-message-btn');

            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');

                Swal.fire({
                    title: 'Yakin hapus pesan?',
                    text: 'Pesan dari ' + (name || '') + ' akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigMessage
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
