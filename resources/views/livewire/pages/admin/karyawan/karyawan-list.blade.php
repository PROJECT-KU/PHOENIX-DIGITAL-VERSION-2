<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Data Karyawan</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Karyawan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                        placeholder="ketik nama atau email karyawan">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a href="{{route('admin.karyawan.create')}}" wire:navigate class="btn btn-primary">
                    <i class="bi bi-plus me-1"></i>
                    Tambah Karyawan
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>Bank Info</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $user->name }}</div>
                            </td>
                            <td>
                                {{ $user->email }}
                            </td>
                            <td>{{ $user->detail->jabatan ?? '-' }}</td>
                            <td>
                                @if($user->detail && $user->detail->nama_bank)
                                <small>{{ $user->detail->nama_bank }} - {{ $user->detail->nomor_rekening }}</small>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.karyawan.edit', $user) }}" title="edit data" class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>

                                <button type="button" class="btn btn-sm btn-danger"
                                    wire:click="$dispatch('will-delete-karyawan-data', {{ $user }})"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $users->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>