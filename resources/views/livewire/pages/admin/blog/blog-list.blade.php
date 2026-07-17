
@section('title')
Blog || lemon
@stop
<div>
    <div class="container-fluid">
        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Blog</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Blog']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="form-control ps-5 pe-5" placeholder="Cari judul, kategori, atau ringkasan...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_blog'))
                        <a wire:navigate href="{{ route('admin.blog.create') }}"
                            class="btn btn-primary d-flex align-items-center justify-content-center px-4">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tulis Artikel</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs (seragam dengan Penjualan Toko) --}}
        <style>
            .customer-glossy-tabs {
                display: flex; width: 100%; gap: .5rem; padding: .5rem; border-radius: 999px;
                background: rgba(255, 255, 255, 0.55); backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.6);
                box-shadow: 0 8px 24px rgba(108, 99, 255, 0.12); overflow-x: auto;
            }
            .customer-glossy-tab {
                flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
                border: none; background: transparent; color: #6b7280; font-weight: 600; font-size: 1.05rem;
                line-height: 1; padding: .95rem 1.5rem; border-radius: 999px; cursor: pointer;
                transition: all .25s ease; text-transform: capitalize; white-space: nowrap;
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
                <button type="button" class="customer-glossy-tab @if ($filter === 'all') active @endif" wire:click="setFilter('all')">
                    <i class="bi bi-list-ul"></i>
                    <span>Semua</span>
                    <span class="tab-count">{{ $tabCounts['all'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($filter === 'published') active @endif" wire:click="setFilter('published')">
                    <i class="bi bi-globe2"></i>
                    <span>Terbit</span>
                    <span class="tab-count">{{ $tabCounts['published'] }}</span>
                </button>
                <button type="button" class="customer-glossy-tab @if ($filter === 'draft') active @endif" wire:click="setFilter('draft')">
                    <i class="bi bi-pencil-square"></i>
                    <span>Draf</span>
                    <span class="tab-count">{{ $tabCounts['draft'] }}</span>
                </button>
            </div>
        </div>

        @if ($category !== '')
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="text-muted small">Kategori:</span>
            <span class="badge rounded-pill d-inline-flex align-items-center gap-2 px-3 py-2" style="background:#eef0ff;color:#4e46e5;">
                <i class="bi bi-tag-fill"></i> {{ $category }}
                <button type="button" wire:click="clearCategory" class="btn-close btn-close-sm" style="font-size:.6rem;" title="Hapus filter kategori"></button>
            </span>
        </div>
        @endif

        {{-- Tabel --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="text-align: center;">
                                <th style="width: 50px;">No</th>
                                <th>Sampul</th>
                                <th class="text-start">Judul</th>
                                <th>Kategori</th>
                                <th>Dilihat</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($posts as $item)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration + ($posts->currentPage() - 1) * $posts->perPage() }}</td>
                                <td>
                                    @if ($item->cover && \Storage::disk('public')->exists('img/blog/' . $item->cover))
                                    <a href="javascript:void(0)" role="button" class="ts-img-zoom d-inline-block"
                                        data-img-url="{{ asset('storage/img/blog/' . $item->cover) }}" title="Perbesar gambar">
                                        <img src="{{ asset('storage/img/blog/' . $item->cover) }}"
                                            class="rounded-3 shadow-sm"
                                            style="width: 64px; height: 44px; object-fit: cover; cursor: zoom-in;">
                                    </a>
                                    @else
                                    <span class="rounded-3 d-inline-flex align-items-center justify-content-center shadow-sm"
                                        style="width: 64px; height: 44px; background: linear-gradient(135deg, #fff3e6, #ffe1c4); color: #ef8b3d;"
                                        title="Belum ada sampul">
                                        <i class="bi bi-card-image d-inline-flex align-items-center justify-content-center" style="font-size: 1.15rem; line-height: 1;"></i>
                                    </span>
                                    @endif
                                </td>
                                <td class="fw-bold text-start" style="max-width: 320px;">
                                    {{ $item->title }}
                                    <br>
                                    <small class="text-muted fw-normal">/blog/{{ $item->slug }}</small>
                                </td>
                                <td>@if ($item->category)<span class="badge bg-info text-white">{{ $item->category }}</span>@else <span class="text-muted">—</span> @endif</td>
                                <td class="text-nowrap"><i class="bi bi-eye text-muted me-1"></i>{{ number_format($item->views) }}</td>
                                <td class="text-nowrap">{{ optional($item->published_at ?? $item->created_at)->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if ($item->status === 'published')
                                        @if ($item->published_at && $item->published_at->isFuture())
                                            <span class="badge bg-info text-white"><i class="bi bi-clock-history me-1"></i>Terjadwal</span>
                                            <br><small class="text-muted text-nowrap">{{ $item->published_at->translatedFormat('d M Y, H:i') }}</small>
                                        @else
                                            <span class="badge bg-success">Terbit</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark">Draf</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if (auth()->user()->hasPermission('edit_blog'))
                                    <button type="button" wire:click="togglePublish('{{ $item->id }}')"
                                        class="btn btn-sm p-2 {{ $item->status === 'published' ? 'btn-secondary' : 'btn-success' }}"
                                        title="{{ $item->status === 'published' ? 'Kembalikan ke draf' : 'Publikasikan' }}">
                                        <i class="bi {{ $item->status === 'published' ? 'bi-eye-slash' : 'bi-globe2' }}"></i>
                                    </button>
                                    <a wire:navigate href="{{ route('admin.blog.edit', $item) }}"
                                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    @endif
                                    @if ($item->status === 'published')
                                    <a href="{{ route('blog.show', $item->slug) }}" target="_blank"
                                        class="btn btn-sm btn-info text-white p-2" title="Lihat di publik">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    @endif
                                    @if (auth()->user()->hasPermission('delete_blog'))
                                    <button type="button" class="btn btn-sm btn-danger delete-blog-btn p-2"
                                        data-id="{{ $item->id }}" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3">
                                            <i class="bi bi-journal-text"></i>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Artikel</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Silakan klik "Tulis Artikel" untuk membuat artikel blog baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $posts->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->
</div>

<!--================== SWEET ALERT DELETE + PREVIEW ==================-->
<script>
    // Popup glossy untuk memperbesar gambar (seragam dengan Task Saya) — center & tanpa scroll.
    if (!window.__tsImgZoomBound) {
        window.__tsImgZoomBound = true;
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest && e.target.closest('.ts-img-zoom');
            if (!trigger) return;
            e.preventDefault();
            const url = trigger.getAttribute('data-img-url');
            if (!url) return;
            if (typeof Swal === 'undefined') { window.open(url, '_blank'); return; }
            Swal.fire({
                html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + url + '" alt="Gambar" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            });
        });
    }

    const glossyConfigBlog = {
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
            const button = event.target.closest('.delete-blog-btn');
            if (button) {
                event.preventDefault();
                const blogId = button.getAttribute('data-id');
                Swal.fire({
                    title: 'Yakin hapus artikel?',
                    text: "Artikel ini akan dihapus permanen dan tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigBlog
                }).then((result) => {
                    if (result.isConfirmed) {
                        const component = button.closest('[wire\\:id]');
                        if (component) {
                            const livewireComponentId = component.getAttribute('wire:id');
                            Livewire.find(livewireComponentId).call('delete', blogId);
                        }
                    }
                });
            }
        });
    });
</script>
<!--================== END SWEET ALERT DELETE + PREVIEW ==================-->
