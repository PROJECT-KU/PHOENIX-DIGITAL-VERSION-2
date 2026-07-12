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
    <livewire:pages.public.bundling.index />
    {{-- end produk-bundling --}}
    {{-- testimoni --}}
    <livewire:components.testimonials />
    {{-- end testimoni --}}
</div>
