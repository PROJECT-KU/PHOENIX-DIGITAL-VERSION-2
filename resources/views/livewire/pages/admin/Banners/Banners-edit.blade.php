<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Banner</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Banner', 'url' => route('admin.Banners.index')],
        ['name' => 'Edit Data Banner'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.Banners.Banners-form :Banners="$Banners" />
        </div>
    </div>
</div>