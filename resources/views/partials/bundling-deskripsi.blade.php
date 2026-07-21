{{-- Deskripsi bundling rapi: paragraf pembuka + poin bercentang.
     Memakai DeskripsiProduk::pisah — hanya teks bertanda (✅ dsb) yang jadi
     poin bercentang, baris biasa tetap paragraf. Butuh var $teks. --}}
@php $__d = \App\Support\DeskripsiProduk::pisah($teks ?? ''); @endphp
@if ($__d['paragraf'] || $__d['poin'] || $__d['ekstra'])
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

        {{-- Catatan (📌 Yang kamu dapat / 🎯 Cocok untuk / ⚡): baris tersendiri,
             ikon aslinya dipertahankan, jadi tidak lagi kebablasan ke poin. --}}
        @if ($__d['ekstra'])
            <div class="bdesk-notes">
                @foreach ($__d['ekstra'] as $__e)
                    <p class="bdesk-note"><span class="bdesk-note-ic">{{ $__e['ikon'] }}</span><span>{{ $__e['teks'] }}</span></p>
                @endforeach
            </div>
        @endif
    </div>
@endif
