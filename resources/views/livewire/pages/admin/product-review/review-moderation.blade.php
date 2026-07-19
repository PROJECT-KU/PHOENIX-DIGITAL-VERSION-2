@section('title')
Moderasi Ulasan Produk || lemon
@stop
<div>
    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Moderasi Ulasan Produk</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Moderasi Ulasan Produk']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>

                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="form-control ps-5 pe-5" placeholder="Cari produk, nama, atau isi ulasan...">

                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs (seragam dengan Penjualan Toko) --}}
        <style>
            .customer-glossy-tabs {
                display: flex;
                width: 100%;
                gap: .5rem;
                padding: .5rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.55);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.6);
                box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12);
                overflow-x: auto;
            }
            .customer-glossy-tab {
                flex: 1;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .6rem;
                border: none;
                background: transparent;
                color: #6b7280;
                font-weight: 600;
                font-size: 1.05rem;
                line-height: 1;
                padding: .95rem 1.5rem;
                border-radius: 999px;
                cursor: pointer;
                transition: all .25s ease;
                text-transform: capitalize;
                white-space: nowrap;
            }
            .customer-glossy-tab i { font-size: 1.25rem; line-height: 1; display: inline-flex; align-items: center; }
            .customer-glossy-tab:hover:not(.active) { color: #4e46e5; background: rgba(108, 99, 255, 0.10); }
            .customer-glossy-tab.active { color: #fff; background: linear-gradient(135deg, #6c63ff, #4e46e5); box-shadow: 0 6px 16px rgba(78, 70, 229, 0.45); transform: translateY(-1px); }
            .customer-glossy-tab .tab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 1.75rem; height: 1.75rem; padding: 0 .55rem; font-size: .82rem; font-weight: 800; line-height: 1; border-radius: 999px; color: #fff; background: linear-gradient(135deg, #7c73ff, #4e46e5); border: 1px solid rgba(255, 255, 255, 0.45); box-shadow: 0 4px 10px rgba(78, 70, 229, 0.40), inset 0 1px 1px rgba(255, 255, 255, 0.45); transition: all .25s ease; }
            .customer-glossy-tab:hover:not(.active) .tab-count { transform: scale(1.08); }
            .customer-glossy-tab.active .tab-count { color: #4e46e5; background: linear-gradient(135deg, #ffffff, #eef0ff); border-color: rgba(255, 255, 255, 0.9); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.18), inset 0 1px 1px rgba(255, 255, 255, 0.9); }
            @media (max-width: 575.98px) {
                .customer-glossy-tab { flex: 0 0 auto; justify-content: center; padding: .6rem .9rem; font-size: .9rem; }
            }
        </style>

        <div class="mt-3 mb-3">
            <div class="customer-glossy-tabs">
                <button type="button" class="customer-glossy-tab @if ($filter === 'pending') active @endif" wire:click="setFilter('pending')">
                    <i class="bi bi-hourglass-split"></i>
                    <span>Menunggu</span>
                    <span class="tab-count">{{ $tabCounts['pending'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($filter === 'approved') active @endif" wire:click="setFilter('approved')">
                    <i class="bi bi-check-circle"></i>
                    <span>Disetujui</span>
                    <span class="tab-count">{{ $tabCounts['approved'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($filter === 'hidden') active @endif" wire:click="setFilter('hidden')">
                    <i class="bi bi-eye-slash"></i>
                    <span>Disembunyikan</span>
                    <span class="tab-count">{{ $tabCounts['hidden'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($filter === 'all') active @endif" wire:click="setFilter('all')">
                    <i class="bi bi-list-check"></i>
                    <span>Semua</span>
                    <span class="tab-count">{{ $tabCounts['all'] }}</span>
                </button>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width: 50px;">No</th>
                                <th>Produk</th>
                                <th>Nama</th>
                                <th>Rating</th>
                                <th>Ulasan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviews as $item)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration + ($reviews->currentPage() - 1) * $reviews->perPage() }}</td>
                                <td class="fw-bold text-start">{{ $item->product->nama_akun ?? '—' }}</td>
                                <td>{{ $item->nama }}</td>
                                <td class="text-warning text-nowrap">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= (int) $item->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </td>
                                <td class="text-start text-truncate" style="max-width: 260px;">{{ $item->ulasan }}</td>
                                <td class="text-nowrap">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    @if ($item->status === 'pending')
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @elseif ($item->status === 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @else
                                        <span class="badge bg-secondary">Disembunyikan</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if ($item->status !== 'approved')
                                        <button type="button" wire:click="approve('{{ $item->id }}')" class="btn btn-sm btn-success p-2" title="Setujui">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    @endif
                                    @if ($item->status !== 'hidden')
                                        <button type="button" wire:click="reject('{{ $item->id }}')" class="btn btn-sm btn-secondary p-2" title="Sembunyikan">
                                            <i class="bi bi-eye-slash"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger delete-review-btn p-2" data-id="{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-star"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Tidak Ada Ulasan</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Belum ada ulasan pada filter ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $reviews->links('vendor.pagination') }}
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
    const glossyConfigReview = {
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
            const button = event.target.closest('.delete-review-btn');

            if (button) {
                event.preventDefault();
                const reviewId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus ulasan?',
                    text: "Ulasan ini akan dihapus permanen dan tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigReview
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('remove', reviewId);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->
