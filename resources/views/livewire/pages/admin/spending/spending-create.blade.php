<div>
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Tambah Data Pengeluaran</h3>
        @php
        $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.spending.index')],
        ['name' => 'Tambah Data Pengeluaran'],
        ];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    {{-- Stop trying to control. --}}
    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.spending.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.spending.spending-form />
            </div>
        </div>
    </div>
</div>