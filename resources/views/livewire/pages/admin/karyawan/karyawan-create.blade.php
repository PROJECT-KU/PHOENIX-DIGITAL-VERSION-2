<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Tambah Data Karyawan</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Karyawan', 'url'=>route('admin.karyawan.index')], ['name'=>'Tambah Data Karyawan']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.karyawan.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.karyawan.karyawan-form />
            </div>
        </div>
    </div>
</div>