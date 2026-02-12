<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengembalian</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengembalian']
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
                <h5>Data Pengembalian</h5>
            </div>
            <div class="card-body">
                @include('livewire.pages.admin.pengembalian.partials.filter')
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Nama Pengembalian</th>
                                <th>Tanggal Pengembalian</th>
                                <th>Nominal</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Penginput</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengembalian as $item)
                                <tr>
                                    <td>{{ $item->id_transaksi }}</td>
                                    <td>{{ $item->nama_pengembalian }}</td>
                                    <td>{{ $item->TanggalPengembalianFormatted }}</td>
                                    <td>{{ $item->nominal_formatted }}</td>
                                    <td>{{ Str::limit($item->deskripsi, 50) }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($item->status === 'pending') bg-warning 
                                            @elseif($item->status === 'berjalan') bg-info 
                                            @else bg-success @endif">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ auth()->user()->name }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ route('admin.pengembalian.edit', $item->id) }}"
                                            wire:navigate
                                            class="btn btn-sm btn-warning me-1"
                                            title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger delete-pengembalian-btn"
                                                    data-id="{{ $item->id }}"
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
                                            <p>Tidak ada data pengembalian.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
                <div class="mt-3">
                    {{ $pengembalian->links('vendor.pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>