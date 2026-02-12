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
            <a href="{{route('admin.promo.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.promo.promo-form />
            </div>
        </div>
    </div>
</div>