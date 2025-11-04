<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Lowongan Pekerjaan</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Lowongan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-2 d-flex align-items-center justify-content-between">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                        placeholder="ketik nama lowongan">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <button wire:click="$dispatch('open-create-form')" class="px-4 btn btn-primary rounded-pill">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Lowongan</span>
                </button>
            </div>

            <div class="table-responsive">
                <table id="productTable" class="table text-center align-middle" style="width:100%">
                    <thead class="align-middle table-light">
                        <tr>
                            <th style="width: 150px;">Nama Lowongan</th>
                            <th style="width: 150px;">Status Lowongan</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataLowongan as $item)
                            <tr style="text-align: center;">
                                <td class="fw-semibold text-capitalize">
                                    {{ $item->title }}
                                </td>
                                <td class="fw-semibold text-capitalize">
                                    <span
                                        class="px-3 py-1 rounded-2 {{ $item->is_active ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                        {{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="gap-2 d-flex justify-content-center">
                                        <button wire:click="$dispatch('open-edit-form', { id: {{ $item->id }} })"
                                            class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete({{ $item->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-3 text-center text-muted">
                                    Belum ada data lowongan
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
@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('show-create-modal', () => {
                Swal.fire({
                    title: 'Tambah Lowongan',
                    html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <label class="form-label">Nama Lowongan <span class="text-danger">*</span></label>
                                <input type="text" id="create-title" class="form-control" placeholder="Masukkan nama lowongan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="create-status" class="form-select">
                                    <option value="1" selected>Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#435ebe',
                    cancelButtonColor: '#6c757d',
                    width: '500px',
                    preConfirm: () => {
                        const title = document.getElementById('create-title').value;
                        const is_active = document.getElementById('create-status').value;

                        if (!title) {
                            Swal.showValidationMessage('Nama lowongan harus diisi');
                            return false;
                        }

                        return {
                            title,
                            is_active
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.set('title', result.value.title);
                        @this.set('is_active', result.value.is_active == 1);
                        @this.call('store');
                    }
                });
            });

            @this.on('show-edit-modal', (data) => {
                Swal.fire({
                    title: 'Edit Lowongan',
                    html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <label class="form-label">Nama Lowongan <span class="text-danger">*</span></label>
                                <input type="text" id="edit-title" class="form-control" value="${data[0].title}" placeholder="Masukkan nama lowongan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="edit-status" class="form-select">
                                    <option value="1" ${data[0].is_active ? 'selected' : ''}>Aktif</option>
                                    <option value="0" ${!data[0].is_active ? 'selected' : ''}>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Update',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#435ebe',
                    cancelButtonColor: '#6c757d',
                    width: '500px',
                    preConfirm: () => {
                        const title = document.getElementById('edit-title').value;
                        const is_active = document.getElementById('edit-status').value;

                        if (!title) {
                            Swal.showValidationMessage('Nama lowongan harus diisi');
                            return false;
                        }

                        return {
                            title,
                            is_active
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.set('title', result.value.title);
                        @this.set('is_active', result.value.is_active == 1);
                        @this.call('update');
                    }
                });
            });

            @this.on('job-created', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Lowongan berhasil ditambahkan',
                    confirmButtonColor: '#435ebe'
                });
            });

            @this.on('job-updated', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Lowongan berhasil diupdate',
                    confirmButtonColor: '#435ebe'
                });
            });

            @this.on('job-deleted', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Lowongan berhasil dihapus',
                    confirmButtonColor: '#435ebe'
                });
            });

            @this.on('job-error', (data) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data[0].message,
                    confirmButtonColor: '#435ebe'
                });
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Lowongan?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.dispatch('confirm-delete', {
                        id: id
                    });
                }
            });
        }
    </script>
@endpush
