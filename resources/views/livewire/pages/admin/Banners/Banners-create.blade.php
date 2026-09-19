@section('title')
Tambah Banner || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.Banners.partials.banner-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Tambah Banner</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Unggah banner baru untuk beranda toko.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.Banners.index') }}" class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.Banners.Banners-form />
    </div>

    @include('livewire.layout.sweetalert')
</div>
