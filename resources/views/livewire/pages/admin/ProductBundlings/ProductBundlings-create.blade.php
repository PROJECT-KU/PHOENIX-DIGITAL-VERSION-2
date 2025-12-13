<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Paket Bundling</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Bundling', 'url' => route('admin.Bundlings.index')],
        ['name' => 'Tambah Data Bundling'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.ProductBundlings.ProductBundlings-form />
        </div>
    </div>
</div>