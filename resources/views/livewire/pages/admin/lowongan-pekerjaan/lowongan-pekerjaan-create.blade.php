<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Tambah Lowongan Pekerjaan</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Lowongan', 'url' => route('admin.lowongan.index')],
        ['name' => 'Tambah Data Lowongan'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-form/>
        </div>
    </div>
</div>
