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
            </div>
            <ul class="mt-3 mb-1 nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'all') active @endif" wire:click="setTab('all')">
                        <i class="bi bi-list-check me-1"></i>
                        <span>semua pelanggan</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link @if ($activeTab === 'member') active @endif"
                        wire:click="setTab('member')">
                        <i class="bi bi-person-check me-1"></i>
                        <span>pelanggan member</span>
                    </button>
                </li>
            </ul>
            <table class="table table-striped mt-2 mb-0 align-middle nowrap" style="width: 100%;">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Nama Pelanggan</th>
                        <th>Email Pelanggan</th>
                        <th>Nomor Handphone</th>
                        <th>Status Member</th>
                        <th>Tanggal Daftar</th>
                        <th>Jumlah Poin</th>
                        <th>Kode Referral</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                    <tr class="text-center">
                        <td>{{ $customer->nama }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->no_hp }}</td>
                        <td>
                            <span
                                class="badge {{ $customer->status_member === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($customer->status_member) }}
                            </span>
                        </td>
                        <td>{{ $customer->created_at->translatedFormat('d F Y, H:i') }}</td>
                        <td>{{ $customer->point }}</td>
                        <td>{{ $customer->kode_ref }}</td>
                        <td>
                            <a wire:navigate href="{{ route('admin.customer.edit', $customer) }}"
                                class="btn btn-warning btn-sm me-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <button type="button"
                                wire:click="$dispatch('will-delete-customer-data', {{ $customer }})"
                                class="btn btn-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-4 text-center">
                            <div class="text-muted">
                                <i class="mb-2 bi bi-inbox fs-1"></i>
                                <p>Tidak ada data customer ditemukan.</p>
                            </div>
                        </td>
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