<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Pelamar Kerja</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pelamar']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <!-- Filter Section -->
            <div class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Bulan</label>
                        <select wire:model.live="filterMonth" class="form-select">
                            <option value="">Semua Bulan</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter Posisi</label>
                        <select wire:model.live="filterJob" class="form-select">
                            <option value="">Semua Posisi</option>
                            @foreach ($jobList as $job)
                                <option value="{{ $job->id }}">{{ $job->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button wire:click="resetFilters" class="btn btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table text-center align-middle" style="width:100%">
                    <thead class="align-middle table-light">
                        <tr>
                            <th>Posisi</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Melamar</th>
                            <th style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataPelamar as $item)
                            <tr>
                                <td class="fw-semibold text-capitalize">
                                    {{ $item->job->title }}
                                </td>
                                <td class="fw-semibold text-capitalize">
                                    {{ $item->name }}
                                </td>
                                <td>
                                    {{ $item->email }}
                                </td>
                                <td>
                                    {{ $item->created_at->locale('id')->isoFormat('D MMMM YYYY') }}
                                </td>
                                <td>
                                    <div class="gap-2 d-flex justify-content-center">
                                        <a wire:navigate href="{{ route('admin.pelamar.detail', $item->id) }}"
                                            class="text-black btn btn-sm btn-warning" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" onclick="confirmDelete({{ $item->id }})"
                                            class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-3 text-center text-muted">
                                    Belum ada data pelamar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $dataPelamar->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('application-deleted', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data pelamar berhasil dihapus',
                    confirmButtonColor: '#435ebe'
                });
            });

            @this.on('application-error', (data) => {
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
                title: 'Hapus Data Pelamar?',
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
