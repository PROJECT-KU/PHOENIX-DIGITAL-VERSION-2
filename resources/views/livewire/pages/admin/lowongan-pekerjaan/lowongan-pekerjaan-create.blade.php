@section('title')
Buka Lowongan Baru || lemon
@stop

<div>
    {{-- Kerangka mengikuti Pesanan Toko (bahasa rupa dsb-*). Isi borangnya
         sendiri hidup di komponen bersama lowongan-pekerjaan-form. --}}
    @include('livewire.pages.admin.partials.dasbor-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Buka Lowongan Baru</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Isi keterangannya, lalu tentukan mau langsung tayang di situs atau disimpan dulu.</span>
                </p>
            </div>

            <div class="dsb-hero-aksi">
                <a href="{{ route('admin.lowongan.index') }}" wire:navigate
                    class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.lowongan-pekerjaan.lowongan-pekerjaan-form />
    </div>
</div>
