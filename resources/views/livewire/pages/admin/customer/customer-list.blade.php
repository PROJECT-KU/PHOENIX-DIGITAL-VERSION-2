<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Manajemen Data Pelanggan</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pelanggan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="form-group position-relative has-icon-left w-50 w-lg-25">
                    <input wire:model.live.debounce.300ms="searchCustomer" type="text" class="form-control"
                        placeholder="ketik nama, no hp atau email pelanggan">
                    <div class="form-control-icon">
                        <i class="bi bi-search" style="font-size: 14px;"></i>
                    </div>
                </div>
                <a wire:navigate href="{{ route('admin.customer.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Data Pelanggan</span>
                </a>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr style="text-align: center;">
                        <th>Nama Pelanggan</th>
                        <th>Email Pelanggan</th>
                        <th>Nomor Handphone</th>
                        <th>Status Member</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                    <tr style="text-align: center;">
                        <td>{{ $customer->nama }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->no_hp }}</td>
                        <td>
                            <span class="badge {{ $customer->status_member === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($customer->status_member) }}
                            </span>
                        </td>
                        <td>{{ $customer->created_at->translatedFormat('d F Y, H:i') }}</td>
                        <td>
                            <a wire:navigate href="{{ route('admin.customer.edit', $customer) }}"
                                class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button"
                                wire:click="$dispatch('will-delete-customer-data', {{ $customer }})"
                                class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">belum ada customer</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $customers->links('vendor.pagination') }}
            </div>
        </div>
    </div>
</div>