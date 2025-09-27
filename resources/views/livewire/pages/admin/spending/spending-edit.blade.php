<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Pengeluaran</h3>
        @php
            $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')], ['name' => 'Data Pengeluaran']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <livewire:pages.admin.spending.spending-form :spending-id="$spendingId" />
</div>
