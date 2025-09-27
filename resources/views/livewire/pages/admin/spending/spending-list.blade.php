<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengeluaran</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pengeluaran']];
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
                    @include('livewire.pages.admin.spending.partials.filter')

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu Transaksi</th>
                                    <th>Nominal</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Penginput</th>
                                    <th>PIC Pembeli</th>
                                    <th>Waktu Data Dibuat</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($spendings as $spending)
                                    <tr>
                                        <td>{{ $spending->tanggal_transaksi_formatted }}</td>
                                        <td>{{ $spending->nominal_formatted }}</td>
                                        <td>{{ Str::limit($spending->deskripsi, 50) }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $spending->status === 'completed' ? 'success' : ($spending->status === 'rejected' ? 'danger' : ($spending->status === 'approved' ? 'info' : 'warning')) }}">
                                                {{ ucfirst($spending->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $spending->namaPenginput }}</td>
                                        <td>{{ $spending->namaPicPembeli }}</td>
                                        <td>{{ $spending->created_at_formatted }}</td>
                                        <td>
                                            <div>
                                                <a href="{{ route('admin.spending.edit', $spending->id) }}"
                                                    wire:navigate class="btn btn-sm btn-outline-secondary me-1"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button
                                                    wire:click="$dispatch('will-delete-spending-data', {{ $spending }})"
                                                    class="btn btn-sm btn-outline-danger" title="Hapus">
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
                                                <p>Tidak ada data pengeluaran yang ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $spendings->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
