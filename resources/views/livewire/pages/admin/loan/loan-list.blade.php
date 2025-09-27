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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Data Peminjaman</h5>
                </div>

                <div class="card-body">
                    @include('livewire.pages.admin.loan.partials.filter')

                    <div class="table-responsive">
                        <table class="table table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Nama Peminjam</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Nominal</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th>Penginput</th>
                                    <th>Total Peminjaman</th>
                                    <th width="120">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
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
                                            {{ $loan->total_borrower_loan_formatted }}
                                        </td>
                                        <td>
                                            <div>
                                                <a href="{{ route('admin.loan.edit', $loan->id) }}"
                                                    wire:navigate
                                                    class="btn btn-sm btn-outline-secondary me-1"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger delete-Loan-btn"
                                                    data-id="{{ $loan->id }}"
                                                    title="Hapus">
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
        </div>
    </div>
</div>
