@section('title')
Tambah Pesanan || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $perpanjang ? 'Pesanan Perpanjangan' : 'Tambah Pesanan' }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block">Isi pelanggan, akun yang dibeli, diskon, dan metode pembayaran. Proses tiap akun dilakukan di Detail Pesanan.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.pesanantoko.index') }}" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.order.order-form :perpanjang="$perpanjang" />
    </div>

    @include('livewire.layout.sweetalert')
</div>
