
@section('title')
Tambah Karyawan || lemon
@stop
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Tambah Data Karyawan</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Karyawan', 'url' => route('admin.karyawan.index')],
                        ['name' => 'Tambah Data Karyawan'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <livewire:pages.admin.karyawan.karyawan-form />
        </div>
    </div>
</div>