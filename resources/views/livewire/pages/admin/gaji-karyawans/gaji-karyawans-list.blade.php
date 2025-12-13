<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengeluaran</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Gaji Karyawan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Data Pengeluaran</h5>
                </div>

                <div class="card-body">
                    @include('livewire.pages.admin.gaji-karyawans.partials.filter')

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr style="text-align: center;">
                                    <th>ID Transaksi</th>
                                    <th>Nama Karyawan</th>
                                    <th>Tanggal Transaksi</th>
                                    <th>Gaji Pokok</th>
                                    <th>Total Gaji</th>
                                    <th>Status</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($gajikaryawan as $item)
                                <tr style="text-align: center;">
                                    <td>{{ $item->id_transaksi }}</td>
                                    <td>{{ $item->karyawan->name ?? '-' }}</td>
                                    <td>{{ $item->tanggal_transaksi_formatted }}</td>
                                    <td>{{ $item->gaji_pokok_formatted }}</td>
                                    <td>{{ $item->total_formatted }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ route('admin.gajikaryawan.edit', $item->id) }}"
                                                wire:navigate class="btn btn-sm btn-warning me-1"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-danger btn-sm delete-gajikaryawan-btn"
                                                data-id="{{ $item->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox mb-2 fs-1"></i>
                                            <p>Tidak ada data gaji yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $gajikaryawan->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--================== SWEET ALERT DELETE ==================-->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.delete-gajikaryawan-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const BannersId = button.getAttribute('data-id');

                Swal.fire({
                    title: 'Yakin hapus Data ini?',
                    text: "Data tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const livewireComponentId = button.closest('[wire\\:id]').getAttribute('wire:id');
                        Livewire.find(livewireComponentId).call('deletegajikaryawan', BannersId);
                    }
                });
            });
        });

        window.addEventListener('gajikaryawan-deleted', () => {
            Swal.fire({
                title: 'Terhapus!',
                text: 'Data berhasil dihapus.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        window.addEventListener('delete-error', (e) => {
            Swal.fire({
                title: 'Gagal!',
                text: e.detail.message,
                icon: 'error'
            });
        });

    });
</script>
<!--================== END SWEET ALERT DELETE ==================-->