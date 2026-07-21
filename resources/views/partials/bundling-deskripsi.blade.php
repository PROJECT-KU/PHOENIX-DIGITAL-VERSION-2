{{-- Deskripsi bundling rapi: paragraf pembuka + poin bercentang.
     Memakai DeskripsiProduk::pisah — hanya teks bertanda (✅ dsb) yang jadi
     poin bercentang, baris biasa tetap paragraf. Butuh var $teks. --}}
@php $__d = \App\Support\DeskripsiProduk::pisah($teks ?? ''); @endphp
@if ($__d['paragraf'] || $__d['poin'])
    <div class="bdesk">
        @foreach ($__d['paragraf'] as $__p)
            <p class="bdesk-p">{{ $__p }}</p>
        @endforeach

        @if ($__d['poin'])
            <ul class="bdesk-list">
                @foreach ($__d['poin'] as $__i => $__poin)
                    <li style="--i: {{ $__i }}"><i class="bi bi-check-circle-fill"></i><span>{{ $__poin }}</span></li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
