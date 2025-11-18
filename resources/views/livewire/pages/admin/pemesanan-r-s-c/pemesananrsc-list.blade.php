<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Pemesanan RSC</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pemesanan RSC']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    @include('livewire.pages.admin.pemesanan-r-s-c.partials.filter')

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr style="text-align: center;">
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
                                @forelse($pemesananrsc as $item)
                                <tr style="text-align: center;">
                                    <td>{{ $item->tanggal_transaksi_formatted }}</td>
                                    <td>{{ $item->nominal_formatted }}</td>
                                    <td>{{ Str::limit($item->deskripsi, 50) }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $item->status === 'completed' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'approved' ? 'info' : 'warning')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->namaPenginput }}</td>
                                    <td>{{ $item->namaPicPembeli }}</td>
                                    <td>{{ $item->created_at_formatted }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('admin.pesananrsc.edit', $item->id) }}"
                                                wire:navigate class="btn btn-sm btn-warning me-1"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button
                                                wire:click="$dispatch('will-delete-pemesananrsc-data', {{ $item }})"
                                                class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="py-4 text-center">
                                        <div class="text-muted">
                                            <i class="mb-2 bi bi-inbox fs-1"></i>
                                            <p>Tidak ada data pemesanan yang ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $pemesananrsc->links('vendor.pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>