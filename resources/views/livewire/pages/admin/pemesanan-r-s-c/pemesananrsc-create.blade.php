@section('title')
Tambah Pesanan RSC || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.pemesanan-r-s-c.partials.rsc-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">{{ $salin ? 'Salin Batch RSC' : 'Tambah Batch RSC' }}</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block"><i class="bi bi-calendar3 me-1"></i>{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
                    @if ($salin)
                        <span class="d-block rsc-lencana-kepala">
                            <span class="dsb-lencana is-biru"><i class="bi bi-files"></i>Disalin dari {{ str_replace('|', ' #', $salin) }}</span>
                        </span>
                        <span class="d-block">Kategori, akun, PIC, dan peserta sudah terisi. Lengkapi nomor batch, jadwal camp, dan durasi.</span>
                    @else
                        <span class="d-block">Isi data kategori, akun, dan peserta — atau impor peserta sekaligus dari Excel.</span>
                    @endif
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.pesananrsc.index') }}" class="dsb-tombol is-lembut">
                    <i class="bi bi-arrow-left"></i><span>Kembali</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.pemesanan-r-s-c.pemesananrsc-form :salin-dari="$salin" :key="'rsc-form-'.md5((string) $salin)" />
    </div>

    @include('livewire.layout.sweetalert')
</div>
