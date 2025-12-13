<div>
    {{-- The whole world belongs to you. --}}
        <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Pengembalian</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Pengembalian', 'url' => route('admin.pengembalian.index')],
                ['name' => 'Tambah Data Pengembalian'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    {{-- Stop trying to control. --}}
    <livewire:pages.admin.pengembalian.pengembalian-form />
</div>
