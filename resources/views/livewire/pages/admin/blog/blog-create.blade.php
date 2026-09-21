@section('title')
Tulis Artikel || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.blog.partials.artikel-gaya')

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Tulis Artikel</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">Artikel baru tersimpan sebagai draf sampai Anda memilih untuk mempublikasikannya.</span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                <a wire:navigate href="{{ route('admin.blog.index') }}" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Artikel</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.blog.blog-form />
    </div>
</div>
