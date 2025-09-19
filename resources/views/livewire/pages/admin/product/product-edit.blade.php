<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Product</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Product', 'url' => route('admin.product.index')],
                ['name' => 'Edit Data Product'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.product.product-form :product="$product" />
        </div>
    </div>
</div>
