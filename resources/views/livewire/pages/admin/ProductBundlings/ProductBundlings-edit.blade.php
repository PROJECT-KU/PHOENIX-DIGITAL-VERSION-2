<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Paket Bundling</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Bundling', 'url' => route('admin.Bundlings.index')],
        ['name' => 'Edit Data Bundling'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>

    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.Bundlings.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.ProductBundlings.ProductBundlings-form :product_bundlings="$ProductBundlings" />
            </div>
        </div>
    </div>
</div>