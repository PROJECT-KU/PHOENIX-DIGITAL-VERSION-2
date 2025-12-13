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
</div>
