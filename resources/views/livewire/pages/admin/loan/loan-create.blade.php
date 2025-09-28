<div>
    {{-- The whole world belongs to you. --}}
        <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Peminjaman</h3>
        @php
            $breadcrumbs = [
                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                ['name' => 'Data Peminjaman', 'url' => route('admin.loan.index')],
                ['name' => 'Tambah Data Peminjaman'],
            ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    {{-- Stop trying to control. --}}
    <livewire:pages.admin.loan.loan-form />
</div>
