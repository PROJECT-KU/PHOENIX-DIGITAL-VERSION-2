@section('title')
Update Pesanan RSC || lemon
@stop
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 header-action w-100">
                <div class="title-wrapper text-center text-md-start">
                    <h3 class="gradient-text fw-bold mb-1">Update Pemesanan RSC</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Pemesanan RSC', 'url' => route('admin.pesananrsc.index')],
                        ['name' => 'Update Data'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>

                <div class="text-center text-md-end flex-shrink-0">
                    <span class="badge bg-primary-subtle text-primary border border-primary rounded-pill px-3 py-2 text-nowrap"
                        style="font-size:.85rem;">
                        <i class="bi bi-folder2-open me-1"></i>{{ $nama_camp }} · Batch {{ $batch_camp }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="mt-2">
                <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form :pemesananBatch="$pemesananBatch" :pemesananrsc="$pemesananrsc" />
            </div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')
</div>