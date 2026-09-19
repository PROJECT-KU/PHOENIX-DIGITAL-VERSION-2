@section('title')
Ubah Testimoni || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.testimoni.partials.testimoni-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Ubah Testimoni</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">{{ $testimoni->nama }}</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.testimoni.index') }}" class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.testimoni.testimoni-form :testimoni="$testimoni" />
    </div>

    @include('livewire.layout.sweetalert')
</div>
