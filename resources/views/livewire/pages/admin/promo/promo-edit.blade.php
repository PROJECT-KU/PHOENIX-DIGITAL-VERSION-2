<div>
    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Promo</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Promo', 'url' => route('admin.promo.index')],
        ['name' => 'Edit Promo'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.promo.promo-form :dataPromo="$promo" />
        </div>
    </div>
</div>
