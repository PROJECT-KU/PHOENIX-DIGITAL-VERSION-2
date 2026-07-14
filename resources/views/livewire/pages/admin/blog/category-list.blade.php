
@section('title')
Kategori Blog || lemon
@stop
<div>
    <div class="container-fluid">
        {{-- Header (seragam dengan Data Banner) --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="title-wrapper text-center text-md-start w-100">
                        <h3 class="gradient-text fw-bold mb-1">Kategori Blog</h3>
                        <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                            @php $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Blog', 'url' => route('admin.blog.index')], ['name' => 'Kategori']]; @endphp
                            <x-breadcrumb :items="$breadcrumbs" />
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 w-100 header-action">
                        <div class="form-group position-relative flex-grow-1">
                            <div class="form-control-icon">
                                <i class="bi bi-search"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="form-control ps-5 pe-5" placeholder="Cari kategori...">
                            @if ($search)
                            <span wire:click="$set('search', '')"
                                class="position-absolute end-0 top-50 translate-middle-y pe-3"
                                style="cursor: pointer; z-index: 10;" title="Bersihkan pencarian">
                                <i class="bi bi-x-circle-fill text-secondary btn-clear-hover"></i>
                            </span>
                            @endif
                        </div>
                        @if (auth()->user()->hasPermission('create_blog'))
                        <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center px-4 add-category-btn">
                            <i class="bi bi-plus-lg"></i>
                            <span class="ms-2">Tambah Kategori</span>
                        </button>
                        @endif
                    </div>
                </div>
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
                                <th class="text-start">Nama Kategori</th>
                                <th>Jumlah Artikel</th>
                                @if (auth()->user()->hasAnyPermission(['edit_blog', 'delete_blog']))
                                <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $item)
                            <tr style="text-align: center;">
                                <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                                <td class="text-start">
                                    @if ($editingId === $item->id)
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="text" wire:model="editingName" wire:keydown.enter="saveEdit"
                                                class="form-control form-control-sm @error('editingName') is-invalid @enderror" style="max-width: 260px;">
                                            <button wire:click="saveEdit" class="btn btn-sm btn-success p-2 d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Simpan"><i class="bi bi-check-lg"></i></button>
                                            <button wire:click="cancelEdit" class="btn btn-sm btn-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Batal"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                        @error('editingName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @else
                                        <span class="fw-bold d-block">{{ $item->name }}</span>
                                        <a href="{{ route('blog.index', ['kategori' => $item->name]) }}" target="_blank" rel="noopener"
                                            class="d-inline-flex align-items-center gap-1 mt-1 text-decoration-none"
                                            style="font-size:.75rem; font-weight:600; color:#4e46e5;" title="Lihat kategori ini di blog publik">
                                            <i class="bi bi-box-arrow-up-right"></i> Lihat di blog
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-secondary px-3 py-2">
                                        <i class="bi bi-journal-text me-1"></i>{{ $counts[$item->name] ?? 0 }} artikel
                                    </span>
                                </td>
                                @if (auth()->user()->hasAnyPermission(['edit_blog', 'delete_blog']))
                                <td class="text-nowrap">
                                    @if ($editingId !== $item->id)
                                        @if (auth()->user()->hasPermission('edit_blog'))
                                        <button wire:click="startEdit({{ $item->id }})" class="btn btn-sm btn-warning text-white p-2" title="Ubah nama"><i class="bi bi-pencil-square"></i></button>
                                        @endif
                                        @if (auth()->user()->hasPermission('delete_blog'))
                                        <button type="button" class="btn btn-sm btn-danger delete-category-btn p-2"
                                            data-id="{{ $item->id }}" data-count="{{ $counts[$item->name] ?? 0 }}" title="Hapus"><i class="bi bi-trash"></i></button>
                                        @endif
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="empty-state-icon-wrapper mb-3"><i class="bi bi-tags"></i></div>
                                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">Belum Ada Kategori</h5>
                                        <p class="text-muted mb-0" style="font-size: 0.95rem;">Klik "Tambah Kategori" untuk membuat kategori baru.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $categories->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>

    <!--================== SWEET ALERT SUCCESS & ERROR ==================-->
    @include('livewire.layout.sweetalert')
    <!--================== END SWEET ALERT SUCCESS & ERROR ==================-->

    <style>
    .swal-cat-popup { border-radius: 24px !important; padding: 1.4rem 1.6rem 1.5rem !important; box-shadow: 0 30px 70px rgba(15, 23, 42, .28) !important; backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255, 255, 255, .6); }
    .swal-cat-icon { border: none !important; width: 74px !important; height: 74px !important; margin: .3rem auto 0 !important; background: linear-gradient(135deg, #7c3aed, #4e46e5); color: #fff; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 2rem; line-height: 1; border-radius: 50%; box-shadow: 0 12px 26px rgba(78, 70, 229, .4); }
    .swal-cat-icon .swal2-icon-content { display: flex !important; align-items: center; justify-content: center; width: 100%; height: 100%; line-height: 1; font-size: 32px !important; }
    .swal-cat-icon i { display: inline-flex; align-items: center; justify-content: center; line-height: 1; font-size: 32px !important; }
    .swal-cat-icon i::before { line-height: 1; font-size: 32px !important; }
    .swal-cat-title { font-weight: 800 !important; color: #1e293b !important; font-size: 1.4rem !important; margin-top: .8rem !important; }
    .swal-cat-html { margin: 0 !important; }
    .swal-cat-sub { color: #6b7280; font-size: .9rem; margin: -2px 0 8px; }
    .swal-cat-input { border-radius: 14px !important; border: 1.5px solid #e7e9f2 !important; padding: 12px 16px !important; font-size: 1rem !important; box-shadow: none !important; transition: .18s; }
    .swal-cat-input:focus { border-color: #7c3aed !important; box-shadow: 0 0 0 .2rem rgba(124, 58, 237, .16) !important; }
    .swal-cat-confirm { background: linear-gradient(135deg, #7c3aed, #4e46e5) !important; color: #fff !important; border: none !important; border-radius: 12px !important; padding: 10px 22px !important; font-weight: 700 !important; box-shadow: 0 10px 22px rgba(78, 70, 229, .35) !important; }
    .swal-cat-confirm:hover { filter: brightness(1.06); }
    .swal-cat-cancel { background: #f1f5f9 !important; color: #475569 !important; border: none !important; border-radius: 12px !important; padding: 10px 20px !important; font-weight: 700 !important; margin-right: .5rem; }
    .swal-cat-cancel:hover { background: #e2e8f0 !important; }
    </style>
</div>

<!--================== SCRIPTS ==================-->
<script>
    // SweetAlert hapus (glossy) — seragam dengan fitur lain.
    const glossyConfigCat = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: { popup: 'swal-glossy-popup', confirmButton: 'btn-glossy-confirm', cancelButton: 'btn-glossy-cancel', title: 'swal-glossy-title' },
        buttonsStyling: false
    };

    document.addEventListener('livewire:navigated', function () {
        document.body.addEventListener('click', function (event) {
            // ---- Tambah kategori: popup input glossy ----
            const addBtn = event.target.closest('.add-category-btn');
            if (addBtn) {
                event.preventDefault();
                const host = addBtn.closest('[wire\\:id]');
                Swal.fire({
                    iconHtml: '<i class="bi bi-tag-fill"></i>',
                    title: 'Tambah Kategori',
                    html: '<div class="swal-cat-sub">Beri nama kategori untuk mengelompokkan artikel blog.</div>',
                    input: 'text',
                    inputPlaceholder: 'Contoh: Tips & Panduan',
                    inputAttributes: { autocapitalize: 'off', autocorrect: 'off', maxlength: 60 },
                    showCancelButton: true,
                    reverseButtons: true,
                    focusConfirm: false,
                    confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Simpan Kategori',
                    cancelButtonText: 'Batal',
                    inputValidator: (v) => (!v || v.trim().length < 2) ? 'Nama kategori minimal 2 karakter.' : undefined,
                    background: 'rgba(255, 255, 255, 0.92)',
                    backdrop: 'rgba(78, 70, 229, 0.18)',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-cat-popup',
                        icon: 'swal-cat-icon',
                        title: 'swal-cat-title',
                        htmlContainer: 'swal-cat-html',
                        input: 'swal-cat-input',
                        confirmButton: 'swal-cat-confirm',
                        cancelButton: 'swal-cat-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed && host) {
                        Livewire.find(host.getAttribute('wire:id')).call('createCategory', result.value.trim());
                    }
                });
                return;
            }

            const button = event.target.closest('.delete-category-btn');
            if (button) {
                event.preventDefault();
                const id = button.getAttribute('data-id');
                const count = parseInt(button.getAttribute('data-count') || '0', 10);

                // Masih dipakai artikel → tidak boleh dihapus.
                if (count > 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak bisa dihapus',
                        text: `Kategori ini masih dipakai ${count} artikel. Ubah atau kosongkan kategori artikel tersebut dulu sebelum menghapus.`,
                        confirmButtonText: 'Mengerti',
                        ...glossyConfigCat
                    });
                    return;
                }

                Swal.fire({
                    title: 'Yakin hapus kategori?',
                    text: 'Kategori ini akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    ...glossyConfigCat
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
<!--================== END SCRIPTS ==================-->
