@section('title')
Sunting Artikel || lemon
@stop
<div>
    @include('livewire.pages.admin.partials.dasbor-gaya')
    @include('livewire.pages.admin.blog.partials.artikel-gaya')

    @php [$keadaan, $lencana, $warna, $ikonKeadaan] = $post->keadaan(); @endphp

    <div class="dsb">
        <header class="dsb-hero">
            <div class="dsb-hero-teks">
                <h1 class="dsb-salam">Sunting Artikel</h1>
                <p class="dsb-hero-ket">
                    <span class="d-block">{{ \Illuminate\Support\Str::limit($post->title, 90) }}</span>
                    <span class="d-block">
                        <span class="dsb-lencana {{ $lencana }}"><i class="bi {{ $ikonKeadaan }}"></i>{{ $keadaan }}</span>
                        <span class="dsb-lencana is-abu"><i class="bi bi-eye"></i>{{ number_format($post->views, 0, ',', '.') }} dibaca</span>
                        <span class="dsb-lencana is-abu"><i class="bi bi-hourglass"></i>{{ $post->lamaBaca() }} menit baca</span>
                    </span>
                </p>
            </div>
            <div class="dsb-hero-aksi">
                @if ($keadaan === 'Terbit')
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="dsb-tombol is-lembut" style="--ikon: #2563eb">
                        <i class="bi bi-box-arrow-up-right"></i><span>Lihat publik</span>
                    </a>
                @endif
                <a wire:navigate href="{{ route('admin.blog.index') }}" class="dsb-tombol is-lembut" style="--ikon: #64748b">
                    <i class="bi bi-arrow-left"></i><span>Daftar Artikel</span>
                </a>
            </div>
        </header>

        <livewire:pages.admin.blog.blog-form :post="$post" />
    </div>
</div>
