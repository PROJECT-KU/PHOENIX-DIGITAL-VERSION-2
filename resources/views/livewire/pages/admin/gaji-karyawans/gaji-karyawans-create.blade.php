<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Gaji Karyawan</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Gaji Karyawan', 'url' => route('admin.gajikaryawan.index')],
        ['name' => 'Tambah Data Gaji Karyawan'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />

    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.gaji-karyawans.gaji-karyawans-form />
        </div>
    </div>
</div>