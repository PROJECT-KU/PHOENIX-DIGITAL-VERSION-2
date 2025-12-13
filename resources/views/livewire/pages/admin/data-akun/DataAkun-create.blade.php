<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Akun</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Akun', 'url' => route('admin.DataAkun.index')],
        ['name' => 'Tambah Data Akun'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.data-akun.DataAkun-form />
        </div>
    </div>
</div>