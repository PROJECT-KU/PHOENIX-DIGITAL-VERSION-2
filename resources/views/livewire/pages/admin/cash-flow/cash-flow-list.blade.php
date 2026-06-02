<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Laporan Cashflow</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Cashflow']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-light-success">
                        <div class="card-body">
                            <h5>Pemasukan</h5>
                            <h3>Rp {{ number_format($summary['income']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-danger">
                        <div class="card-body">
                            <h5>Pengeluaran</h5>
                            <h3>Rp {{ number_format($summary['expense']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-primary">
                        <div class="card-body">
                            <h5>Net Cashflow</h5>
                            <h3>Rp {{ number_format($summary['net']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table text-center table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Sumber</th>
                        <th class="text-end">Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $item)
                    <tr>
                        <td>{{ $item->transaction_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $item->type == 'income' ? 'success' : 'danger' }}">
                                {{ ucfirst($item->category) }}
                            </span>
                        </td>
                        <td>{{ $item->description }}</td>
                        <td>
                            @if($item->sourceable_type === 'App\Models\Order')
                            order #{{ $item->sourceable->order_number ?? '-' }}
                            @elseif($item->sourceable_type === 'App\Models\Loan')
                            Pinjaman {{ $item->sourceable->nama_peminjam ?? 'peminjam' }}
                            @elseif($item->sourceable_type === 'App\Models\Pengembalian')
                            pengembalian {{ $item->sourceable->nama_pengembalian }}
                            @elseif($item->sourceable_type === 'App\Models\GajiKaryawans')
                            gaji {{ $item->sourceable->karyawan->name ?? 'User' }}
                            @elseif($item->sourceable_type === 'App\Models\Spending')
                            pengeluaran {{ $item->sourceable->jenis_pengeluaran }}
                            @elseif($item->sourceable_type === 'App\Models\PemesananRsc')
                            Pesanan Rumah Scopus
                            @endif
                        </td>
                        <td class="text-end {{ $item->type == 'income' ? 'text-success' : 'text-danger' }}">
                            {{ $item->type == 'income' ? '+' : '-' }}
                            Rp {{ number_format($item->amount) }}
                        </td>
                        <td>
                            <button wire:click="$dispatch('openDetail', { id: '{{ $item->id }}' })"
                                class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $reports->links('vendor.pagination') }}
            </div>
        </div>
        <livewire:pages.admin.cashflow.cashflow-detail />
    </div>
</div>