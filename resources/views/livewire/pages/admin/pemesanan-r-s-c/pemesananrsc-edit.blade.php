
@section('title')
Edit Pesanan RSC || PT. Asthana Cipta Mandiri
@stop
<div>
    <div class="mb-2 d-flex align-items-center justify-content-between">
        <h3>Edit Data Pesanan {{$nama_camp}} Batch {{$batch_camp}}</h3>
        @php
        $breadcrumbs = [['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Data Pengeluaran', 'url' => route('admin.pesananrsc.index')],
        ['name' => 'Edit Data Pengeluaran']];
        @endphp
        <x-breadcrumb :items="$breadcrumbs" />
    </div>
    <div class="card">
        <div class="card-body">
            <a wire:navigate href="{{route('admin.pesananrsc.index')}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                <span>Kembali</span>
            </a>
            <div class="mt-4">
                <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form :pemesananBatch="$pemesananBatch" :pemesananrsc="$pemesananrsc" />
            </div>
        </div>
    </div>
</div>