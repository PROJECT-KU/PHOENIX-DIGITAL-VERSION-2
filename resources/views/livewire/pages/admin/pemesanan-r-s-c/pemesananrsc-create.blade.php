@section('title')
Tambah Pesanan RSC || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pemesanan-r-s-c.partials.rsc-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Tambah Batch RSC</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">
                        @php
                            $breadcrumbs = [
                                ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                                ['name' => 'Pemesanan RSC', 'url' => route('admin.pesananrsc.index')],
                                ['name' => 'Tambah Batch'],
                            ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </span>
                    <span class="d-block">Isi data kategori, akun, dan peserta — atau impor peserta sekaligus dari Excel.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.pesananrsc.index') }}" class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form />
    </div>

    @include('livewire.layout.sweetalert')
</div>
