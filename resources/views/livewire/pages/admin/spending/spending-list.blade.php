<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengeluaran</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pengeluaran']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Tabs -->
        <ul class="nav nav-tabs" id="loanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'lainnya']) }}"
                    class="nav-link {{ request('jenisPengeluaran') !== 'pembelian_akun' ? 'active' : '' }}"
                    id="data-loan-tab" role="tab" aria-controls="data-loan">
                    Pengeluaran Lainnya
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="{{ route('admin.spending.index', ['jenisPengeluaran' => 'pembelian_akun']) }}"
                    class="nav-link {{ request('jenisPengeluaran') === 'pembelian_akun' ? 'active' : '' }}"
                    id="total-loan-tab" role="tab" aria-controls="total-loan">
                    Pengeluaran Pembelian Akun
                </a>
            </li>
        </ul>
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
                                <tr style="text-align: center;">
                                    <th>ID Transaksi</th>
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
                                    <tr style="text-align: center;">
                                        <td>{{ $spending->id_transaksi }}</td>
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
                                                    wire:navigate class="btn btn-sm btn-warning me-1" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button
                                                    wire:click="$dispatch('will-delete-spending-data', {{ $spending }})"
                                                    class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
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

                <!-- Table total per peminjam -->
                <div class="card">
                    <div class="card-header">
                        <h5>Total Pengeluaran Per Kategori</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>Kategori Pengeluaran</th>
                                        <th>Total pengeluaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($totalSpendings as $item)
                                        <tr>
                                            <td>
                                                @if ($item->jenisPengeluaran === 'pembelian_akun')
                                                    Pembelian Akun
                                                @elseif ($item->jenisPengeluaran === 'lainnya')
                                                    Pengeluaran Lainnya
                                                @else
                                                    Tidak Diketahui
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($item->total_pengeluaran, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox mb-2 fs-1"></i>
                                                    <p>Tidak ada data pengeluaran.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
