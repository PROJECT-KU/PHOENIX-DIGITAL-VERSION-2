<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Tambah Data Pemesanan</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Pemesanan', 'url' => route('admin.pesanantoko.index')],
                ['name' => 'Tambah Data Pemesanan Toko'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <a href="{{ route('admin.pesanantoko.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.order.order-form />
            </div>
        </div>
    </div>

</div>
