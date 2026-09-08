@section('title')
    Beranda | Phoenix Digital
@endsection
<div>
    {{-- banner --}}
    @include('livewire.pages.public.homepage.partials.banner')
    {{-- end banner --}}
    {{-- flash sale =====================================================
         Didahulukan atas Produk Terlaris: flash sale punya batas waktu,
         sedangkan produk terlaris ada setiap hari. Menaruh yang berbatas waktu
         di bawah berarti sebagian pengunjung menutup halaman sebelum sempat
         melihatnya, dan penawarannya keburu habis.

         Aman ditaruh paling atas: komponennya kosong tanpa latar apa pun saat
         tidak ada flash sale yang sedang berjalan, jadi tidak meninggalkan
         pita kosong di puncak beranda. --}}
    @include('livewire.pages.public.homepage.partials.flash-sale')
    {{-- end flash sale --}}
    {{-- produk terlaris --}}
    @include('livewire.pages.public.homepage.partials.produk-terlaris')
    {{-- end produk terlaris --}}
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
