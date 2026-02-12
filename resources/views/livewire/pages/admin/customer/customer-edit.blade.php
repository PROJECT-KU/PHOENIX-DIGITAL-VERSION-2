<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pelanggan', 'url' => route('admin.customer.index')],
        ['name' => 'Edit Data Pelanggan'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.customer.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.customer.customer-form :customer="$customer" />
            </div>
        </div>
    </div>
</div>