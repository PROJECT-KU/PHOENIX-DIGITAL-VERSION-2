@section('title')
    Beranda | Phoenix Digital
@endsection
<div>
    {{-- banner --}}
    @include('livewire.pages.public.homepage.partials.banner')
    {{-- end banner --}}
    {{-- produk terlaris --}}
    @include('livewire.pages.public.homepage.partials.produk-terlaris')
    {{-- end produk terlaris --}}
    {{-- flash sale --}}
    @include('livewire.pages.public.homepage.partials.flash-sale')
    {{-- end flash sale --}}
    {{-- produk-bundling --}}
    {{-- @include('livewire.pages.public.bundling.index') --}}
    {{-- Beranda hanya menampilkan 4 paket terbaru; selebihnya di halaman paket
         tersendiri. Komponennya sama dengan /bundling supaya kartu dan alur
         "tambah ke keranjang" tidak bercabang. --}}
    <livewire:pages.public.bundling.index :di-beranda="true" />
    {{-- end produk-bundling --}}
    {{-- testimoni --}}
    <livewire:components.testimonials />
    {{-- end testimoni --}}
</div>
