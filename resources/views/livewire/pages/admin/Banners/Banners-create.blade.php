<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Banner</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Banner', 'url' => route('admin.Banners.index')],
        ['name' => 'Tambah Data Banner'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.Banners.Banners-form />
        </div>
    </div>
</div>