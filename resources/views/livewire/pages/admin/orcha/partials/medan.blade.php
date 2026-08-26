{{-- Satu pasang label–nilai.

     Dipisah jadi partial karena halaman ini memakainya dua puluh kali lebih;
     mengubah bentuknya di satu tempat lebih murah daripada memburu dua puluh
     salinan yang lambat laun berbeda-beda sendiri. --}}
<div class="orcha-medan">
    <div class="orcha-label-kecil">{{ $label }}</div>
    <div class="isi {{ blank($nilai ?? null) || $nilai === '—' ? 'kosong' : '' }}">
        @if (! empty($tautan ?? null) && filled($nilai))
            <a href="{{ $tautan }}" target="_blank" rel="noopener">{{ $nilai }}</a>
        @else
            {{ filled($nilai ?? null) ? $nilai : '—' }}
        @endif
    </div>
</div>
