<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Product</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Product', 'url' => route('admin.product.index')],
        ['name' => 'Tambah Product'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.product.product-form />
        </div>
    </div>
</div>
