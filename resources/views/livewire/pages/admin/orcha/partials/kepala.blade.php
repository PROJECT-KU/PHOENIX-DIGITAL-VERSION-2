{{--
    Kepala halaman Orcha: judul, breadcrumb, penanda "mode Orcha", dan pesan
    galat bila API-nya tidak terjangkau.

    Dipakai semua halaman Orcha supaya bentuknya seragam.

    Variabel: $judul, $galat, dan (opsional) $keterangan.
--}}
@php
    $breadcrumbs = [
        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
        ['name' => 'Orcha Journey', 'url' => route('admin.orcha.dashboard')],
        ['name' => $judul],
    ];
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="title-wrapper text-center text-lg-start">
                <div class="d-inline-flex align-items-center gap-2 mb-2 orcha-lencana">
                    <i class="bi bi-water"></i>
                    <span>Mode Orcha Journey</span>
                </div>
                <h3 class="gradient-text fw-bold mb-1">{{ $judul }}</h3>
                @isset($keterangan)
                    <p class="text-muted small mb-2">{{ $keterangan }}</p>
                @endisset
                <div class="breadcrumb-custom d-flex justify-content-center justify-content-lg-start">
                    <x-breadcrumb :items="$breadcrumbs" />
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center">
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="btn btn-light border rounded-3 orcha-tombol">
                    <i class="bi bi-arrow-left-circle"></i>
                    <span>Kembali ke lemon</span>
                </a>
            </div>
        </div>
    </div>
</div>

@if ($galat)
    <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-start gap-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong class="d-block">Data Orcha belum bisa ditampilkan</strong>
            <span class="small">{{ $galat }}</span>
        </div>
    </div>
@endif
