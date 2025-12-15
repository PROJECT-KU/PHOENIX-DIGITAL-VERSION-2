<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Gaji Karyawan</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Gaji Karyawan', 'url' => route('admin.gajikaryawan.index')],
        ['name' => 'Edit Data Gaji Karyawan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>

    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.gajikaryawan.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.gaji-karyawans.gaji-karyawans-form :gajikaryawan="$gajikaryawan" />
            </div>
        </div>
    </div>
</div>