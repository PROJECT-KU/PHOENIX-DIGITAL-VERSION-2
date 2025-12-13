<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Peminjaman</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Peminjaman']
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <ul class="nav nav-tabs mb-3" id="loanTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.loan.index') }}"
            class="nav-link {{ request()->routeIs('admin.loan.*') ? 'active' : '' }}"
            id="data-loan-tab" role="tab" aria-controls="data-loan">
                Peminjaman
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="{{ route('admin.pengembalian.index') }}"
            class="nav-link {{ request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}"
            id="total-loan-tab" role="tab" aria-controls="total-loan">
                Pengembalian
            </a>
        </li>
    </ul>

    <div class="row">
    <!-- Card Data Peminjaman -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Data Peminjaman</h5>
            </div>
            <div class="card-body">
                @include('livewire.pages.admin.loan.partials.filter')
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Nama Peminjam</th>
                                <th>Tanggal Pinjam</th>
                                <th>Nominal</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Penginput</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                                <tr>
                                    <td>{{ $loan->id_transaksi }}</td>
                                    <td>{{ $loan->nama_peminjam }}</td>
                                    <td>{{ $loan->tanggal_peminjam_formatted }}</td>
                                    <td>{{ $loan->nominal_formatted }}</td>
                                    <td>{{ Str::limit($loan->deskripsi, 50) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($loan->status === 'pending') bg-warning 
                                            @elseif($loan->status === 'berjalan') bg-info 
                                            @else bg-success @endif">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $loan->namaPenginput }}</td>
                                    <td>
                                        <div>
                                            <a href="{{ route('admin.loan.edit', $loan->id) }}"
                                                wire:navigate class="btn btn-sm btn-warning me-1"
                                                title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-danger delete-Loan-btn"
                                                data-id="{{ $loan->id }}"
                                                title="Delete">
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
                                            <p>Tidak ada data pinjaman.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $loans->links('vendor.pagination') }}
                </div>
            </div>
        </div>

        <!-- Table total per peminjam -->
        <div class="card">
            <div class="card-header">
                <h5>Total Peminjaman per Peminjam</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center">
                        <thead>
                            <tr>
                                <th>Nama Peminjam</th>
                                <th>Total Pinjaman</th>
                                <th>Total Pengembalian</th>
                                <th>Sisa Peminjaman</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($totalLoans as $item)
                                <tr>
                                    <td>{{ $item->nama_peminjam }}</td>
                                    <td>Rp {{ number_format($item->total_pinjaman, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($item->total_pengembalian, 0, ',', '.') }}</td>
                                    <td>
                                        <strong class="{{ $item->sisa_peminjaman <= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($item->sisa_peminjaman, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox mb-2 fs-1"></i>
                                            <p>Tidak ada data pinjaman.</p>
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