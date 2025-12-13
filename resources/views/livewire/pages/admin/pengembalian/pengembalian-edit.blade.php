<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengembalian</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.pengembalian.index')],
        ['name' => 'Edit Data Pengembalian']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <livewire:pages.admin.pengembalian.pengembalian-form :pengembalian-id="$pengembalianId" />
</div>