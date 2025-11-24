<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Promo</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Promo', 'url' => route('admin.promo.index')],
        ['name' => 'Tambah Promo'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.promo.promo-form />
        </div>
    </div>
</div>
