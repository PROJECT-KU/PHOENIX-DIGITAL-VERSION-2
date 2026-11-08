{{-- Pindah antar-bagian isi situs Asthana. $aktif = kunci bagian yang sedang dibuka. --}}
@php
    $bagianSitus = [
        'portofolio' => ['Portofolio', 'bi-grid-1x2', '#2455e6', route('admin.asthana.portofolio')],
        'berita' => ['Berita', 'bi-newspaper', '#16a34a', route('admin.asthana.berita')],
        'klien' => ['Klien', 'bi-buildings', '#4f46e5', route('admin.asthana.daftar', 'klien')],
        'testimoni' => ['Testimoni', 'bi-chat-quote', '#7b5cff', route('admin.asthana.daftar', 'testimoni')],
        'tim' => ['Tim', 'bi-people', '#0ea5c6', route('admin.asthana.daftar', 'tim')],
        'angka' => ['Angka', 'bi-bar-chart', '#ff8a3d', route('admin.asthana.daftar', 'angka')],
    ];
@endphp
<nav class="ast-nav" aria-label="Bagian isi situs Asthana">
    @foreach ($bagianSitus as $kunciBagian => $bagian)
        <a wire:navigate href="{{ $bagian[3] }}" style="--c: {{ $bagian[2] }}"
            class="ast-nav-item {{ $aktif === $kunciBagian ? 'is-aktif' : '' }}"
            @if ($aktif === $kunciBagian) aria-current="page" @endif>
            <i class="bi {{ $bagian[1] }}"></i><span>{{ $bagian[0] }}</span>
        </a>
    @endforeach
</nav>
