
@section('title')
Data Pesan Pelanggan || PT. Asthana Cipta Mandiri
@stop
<div>
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
    </style>

    <div class="container-fluid">
        <!--================== HEADER + SEARCH (SATU CARD) ==================-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-lg-start">
                        <h3 class="gradient-text fw-bold mb-1">Pesan Pelanggan (Helpdesk)</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                            @php
                            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Pesan Pelanggan (Helpdesk)']];
                            @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="form-group position-relative mb-0 header-action" style="flex: 1 1 auto; max-width: 420px;">
                        <div class="form-control-icon"><i class="bi bi-search"></i></div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control ps-5 pe-5"
                            placeholder="Cari tiket, nama, email, atau pesan...">
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
        <div class="card border-0 shadow-sm rounded-4" wire:poll.60s>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width: 50px;">No</th>
                            <th>Ticket</th>
                            <th>Status Ticket</th>
                            <th>Priority Ticket</th>
                            <th>Status Message</th>
                            <th>Nama Pelanggan</th>
                            <th>Email Pelanggan</th>
                            <th>Tanggal Pesan Dikirim</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $item)
                        <tr class="{{ is_null($item->read_at) ? 'table-warning' : '' }}" style="text-align: center;">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $item->ticket }}
                            </td>
                            <td class="align-middle">
                                <select wire:change="updateStatus({{ $item->id }}, $event.target.value)"
                                    class="form-select form-select-sm border-0 rounded-pill px-3 py-1 fw-bold 
        @switch($item->status)
            @case('open') bg-light-primary text-primary @break
            @case('pending') bg-light-warning text-warning @break
            @case('in_progress') bg-light-info text-info @break
            @case('resolved') bg-light-success text-success @break
            @case('closed') bg-light-secondary text-secondary @break
        @endswitch"
                                    style="cursor: pointer; width: 135px;">

                                    @if(in_array($item->status, ['resolved', 'closed']))
                                    {{-- Jika sudah selesai/tutup, hanya tampilkan pilihan yang relevan --}}
                                    <option value="resolved" {{ $item->status == 'resolved' ? 'selected' : '' }}>Selesai</option>
                                    <option value="closed" {{ $item->status == 'closed' ? 'selected' : '' }}>Ditutup</option>
                                    @else
                                    {{-- Jika belum selesai, tampilkan semua opsi --}}
                                    <option value="open" {{ $item->status == 'open' ? 'selected' : '' }}>Terbuka</option>
                                    <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Tertunda</option>
                                    <option value="in_progress" {{ $item->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $item->status == 'resolved' ? 'selected' : '' }}>Selesai</option>
                                    <option value="closed" {{ $item->status == 'closed' ? 'selected' : '' }}>Ditutup</option>
                                    @endif
                                </select>
                            </td>
                            <td class="align-middle">
                                <select wire:change="updatePriority({{ $item->id }}, $event.target.value)"
                                    class="form-select form-select-sm border-0 rounded-pill px-3 py-1 fw-bold
            @switch($item->priority)
                @case('low') bg-light-info text-info @break
                @case('medium') bg-light-secondary text-secondary @break
                @case('high') bg-light-warning text-warning @break
                @case('urgent') bg-danger text-white @break
            @endswitch"
                                    style="cursor: pointer; width: 120px;">
                                    <option value="low" {{ $item->priority == 'low' ? 'selected' : '' }}>Rendah</option>
                                    <option value="medium" {{ $item->priority == 'medium' ? 'selected' : '' }}>Sedang</option>
                                    <option value="high" {{ $item->priority == 'high' ? 'selected' : '' }}>Tinggi</option>
                                    <option value="urgent" {{ $item->priority == 'urgent' ? 'selected' : '' }}>Mendesak</option>
                                </select>
                            </td>
                            <td>
                                @if (is_null($item->read_at))
                                <span class="badge bg-light-warning text-warning px-2 py-1 rounded-pill d-inline-flex align-items-center">
                                    <i class="bi bi-envelope-fill me-1"></i> Belum Dibaca
                                </span>
                                @else
                                <span class="badge bg-light-success text-success px-2 py-1 rounded-pill d-inline-flex align-items-center">
                                    <i class="bi bi-envelope-open-fill me-1"></i> Sudah Dibaca
                                </span>
                                @endif
                            </td>
                            <td class="fw-bold">
                                {{ $item->name }}
                                @if (is_null($item->read_at))
                                <i class="text-primary bi bi-dot"></i>
                                @endif
                            </td>
                            <td>
                                {{ $item->email }}
                            </td>
                            <td class="text-muted small">
                                {{ $item->created_at->diffForHumans() }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a wire:navigate href="{{ route('admin.customer-message.detail', $item) }}" class="btn btn-sm btn-warning text-white p-2" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (auth()->user()->hasPermission('delete_customer_message'))
                                <button type="button" class="btn btn-sm btn-danger delete-CustomerMessage-btn p-2" data-id="{{ $item->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif

                                @if (!in_array($item->status, ['closed', 'resolved']))
                                @php
                                // Hitung antrian
                                $queue = $this->getQueuePositionForItem($item);

                                // Format pesan
                                $waText = "Halo {$item->name}, terima kasih telah menghubungi kami.\n\n" .
                                "Berikut informasi mengenai tiket pengaduan atau kendala Anda:\n" .
                                "• ID Tiket: *{$item->ticket}*\n" .
                                "• Status: *" . ucfirst($item->status) . "*\n" .
                                "• Prioritas: *" . ucfirst($item->priority) . "*\n\n" .
                                "Saat ini tiket Anda berada di urutan antrian ke-{$queue}. Mohon kesediaannya untuk menunggu, kami akan segera menghubungkan Anda dengan admin spesialis kami.\n\n" .
                                "Terima kasih atas pengertiannya.\n\n" .
                                "Salam hangat,\n*Phoenix Digital Warehouse*";

                                $waPhone = preg_replace('/^0/', '62', $item->no_telp);
                                $waLink = "https://wa.me/{$waPhone}?text=" . urlencode($waText);
                                @endphp

                                <a href="{{ $waLink }}"
                                    target="_blank"
                                    class="btn btn-sm btn-success text-white p-2"
                                    title="Kirim Update Antrian WA">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="empty-state-icon-wrapper mb-3">
                                        <i class="bi bi-messenger"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                        Belum Ada Data Pesan Pelanggan
                                    </h5>
                                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                        Saat ini belum ada pesan yang masuk dari pelanggan Anda.
                                    </p>
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
            const button = event.target.closest('.delete-CustomerMessage-btn');

            if (button) {
                event.preventDefault();
                const CustomerMessageId = button.getAttribute('data-id');

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
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('delete', CustomerMessageId);
                        }
                    }
                });
            }
        });
    });


    // MENANGKAP EVENT SUKSES
    window.addEventListener('CustomerMessage-deleted', () => {
        Swal.fire({
            title: 'Terhapus!',
            text: 'Data pesan pelanggan berhasil dihapus.',
            icon: 'success',
            timer: 2500, // Otomatis tutup dalam 2.5 detik
            showConfirmButton: false, // Tanpa tombol
            ...glossyConfig
        });
    });

    // MENANGKAP EVENT GAGAL
    window.addEventListener('CustomerMessage-deleteError', (e) => {
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

<!--================== SWEET ALERT UPDATE STATUS & PRIORITY TICKET ==================-->
<script>
    window.addEventListener('toast-success', (event) => {
        const message = event.detail.message || (event.detail[0] ? event.detail[0].message : '');

        Swal.fire({
            text: message,
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.style.background = 'rgba(255, 255, 255, 0.9)';
                toast.style.backdropFilter = 'blur(10px)';
                toast.style.border = '1px solid rgba(255, 255, 255, 0.3)';
                toast.style.borderRadius = '15px';
                toast.style.color = '#333';
            }
        });
    });
</script>
<!--================== SWEET ALERT UPDATE STATUS & PRIORITY TICKET ==================-->