<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Tambah Data Pemesanan</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pemesanan', 'url' => route('admin.pesananrsc.index')],
        ['name' => 'Tambah Data Pemesanan'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
    <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form />
        </div>
    </div>

</div>