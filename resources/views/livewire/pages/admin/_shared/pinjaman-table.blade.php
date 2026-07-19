@php $showJenis = filled($search); @endphp
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr style="text-align: center;">
                @if ($showJenis)
                <th>Jenis</th>
                @endif
                <th>ID Transaksi</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Nominal</th>
                <th>Deskripsi</th>
                <th class="text-center">Status</th>
                <th>Penginput</th>
                <th>Waktu Data Dibuat</th>
                @if (auth()->user()->hasAnyPermission(['edit_loan', 'delete_loan']))
                <th class="text-center" width="120">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr style="text-align: center;">
                @if ($showJenis)
                <td>
                    <span class="badge bg-{{ $row['jenis'] === 'peminjaman' ? 'primary' : 'success' }}">
                        {{ $row['jenis_label'] }}
                    </span>
                </td>
                @endif
                <td class="fw-bold">{{ $row['id_transaksi'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['nominal_formatted'] }}</td>
                <td class="text-truncate" style="max-width: 200px;">{{ Str::limit($row['deskripsi'], 50) }}</td>
                <td class="text-center">
                    <span
                        class="badge bg-{{ $row['status'] === 'lunas' ? 'success' : ($row['status'] === 'berjalan' ? 'info' : 'warning') }}">
                        {{ ucfirst($row['status']) }}
                    </span>
                </td>
                <td>{{ $row['penginput'] }}</td>
                <td>{{ $row['created_at'] }}</td>
                @if (auth()->user()->hasAnyPermission(['edit_loan', 'delete_loan']))
                <td class="text-center text-nowrap">
                    @if (auth()->user()->hasPermission('edit_loan'))
                    <a href="{{ $row['edit_url'] }}" wire:navigate
                        class="btn btn-sm btn-warning text-white p-2" title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    @endif
                    @if (auth()->user()->hasPermission('delete_loan'))
                    <button type="button" class="btn btn-sm btn-danger p-2 {{ $row['delete_class'] }}"
                        data-id="{{ $row['id'] }}" title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $showJenis ? 10 : 9 }}" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <div class="empty-state-icon-wrapper mb-3">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                            Belum Ada Data
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 0.95rem;">
                            Tidak ada data peminjaman / pengembalian yang ditemukan.
                        </p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $rows->links('vendor.pagination') }}
</div>
