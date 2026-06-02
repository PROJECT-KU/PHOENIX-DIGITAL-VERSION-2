<div>
    {{-- If you look to others for fulfillment, you will never truly be fulfilled. --}}
    <div class="d-flex mb-2 align-items-center justify-content-between">
        <h3>Data Peminjaman</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.loan.index')],
        ['name' => 'Edit Data Peminjaman']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <a href="{{route('admin.loan.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.loan.loan-form :loan-id="$loanId" />
            </div>
        </div>
    </div>
</div>