<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Edit Data Pengeluaran</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.spending.index')],
        ['name' => 'Edit Data Pengeluaran']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <livewire:pages.admin.spending.spending-form :spending-id="$spendingId" />
</div>