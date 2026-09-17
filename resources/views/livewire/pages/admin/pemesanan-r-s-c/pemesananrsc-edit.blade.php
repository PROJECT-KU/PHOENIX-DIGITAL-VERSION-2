@section('title')
Update Pesanan RSC || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pemesanan-r-s-c.partials.rsc-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Edit Batch RSC</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    <span class="d-block rsc-lencana-kepala">
                        <span class="dsb-lencana is-ungu"><i class="bi bi-folder2-open"></i>{{ $nama_camp }}</span>
                        <span class="dsb-lencana is-nila">Batch #{{ $batch_camp }}</span>
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.pesananrsc.detail', ['nama_camp' => $nama_camp, 'batch_camp' => $batch_camp]) }}"
                    class="dsb-tombol is-lembut">
                    <i class="bi bi-eye"></i><span>Lihat Detail</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form :pemesananBatch="$pemesananBatch" :pemesananrsc="$pemesananrsc" />
    </div>

    @include('livewire.layout.sweetalert')
</div>
