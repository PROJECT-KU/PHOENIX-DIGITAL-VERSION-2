<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Pengeluaran</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.pemesananrsc.index')],
        ['name' => 'Edit Data Pengeluaran']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form :pemesananrsc="$pemesananrsc" />
        </div>
    </div>
</div>