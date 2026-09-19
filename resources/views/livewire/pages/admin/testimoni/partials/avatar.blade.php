{{-- Avatar: foto bila ada, selain itu inisial berwarna tetap per nama. $item, $klik (bool). --}}
@php
    $avPalet = ['#7c3aed', '#0284c7', '#16a34a', '#ea580c', '#db2777', '#4f46e5', '#0d9488', '#d97706'];
    $avWarna = $avPalet[abs(crc32((string) $item->nama)) % count($avPalet)];
    $avAdaFoto = $item->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists('img/testimoni/'.$item->foto);
@endphp
<span class="tm-avatar" style="--av: {{ $avWarna }}">
    @if ($avAdaFoto)
        <img src="{{ asset('storage/img/testimoni/'.$item->foto) }}" alt="{{ $item->nama }}" loading="lazy">
    @else
        {{ mb_strtoupper(mb_substr(trim((string) $item->nama), 0, 1)) ?: '?' }}
    @endif
</span>
