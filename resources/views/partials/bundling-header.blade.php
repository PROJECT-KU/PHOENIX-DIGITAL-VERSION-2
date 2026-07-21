{{-- Header kartu bundling yang dibuat OTOMATIS dari data paket, bukan dari
     gambar upload yang gaya/latar-nya beda-beda. Hasilnya seragam di semua
     kartu. Butuh: $produk (array nama akun), $nama (nama paket),
     opsional $nomor (urutan → "Paket 0N"). --}}
@php
    $__names = collect($produk ?? [])->filter(fn ($n) => trim((string) $n) !== '')->values();
    // Warna avatar deterministik dari nama, jadi satu produk selalu sama warnanya.
    $__pal = ['#22a06b', '#ef5f22', '#0ea5e9', '#7c3aed', '#0d9488', '#e11d48', '#d97706', '#2563eb'];
    $__warna = fn ($n) => $__pal[abs(crc32((string) $n)) % count($__pal)];
@endphp
<div class="bh">
    <div class="bh-top">
        <span class="bh-badge"><i class="bi bi-stars"></i> Bundling</span>
        @isset($nomor)
            <span class="bh-num">Paket {{ str_pad((string) $nomor, 2, '0', STR_PAD_LEFT) }}</span>
        @endisset
    </div>

    @if ($__names->isNotEmpty())
        <div class="bh-pills">
            @foreach ($__names as $__i => $__nm)
                @if ($__i > 0)<span class="bh-plus">+</span>@endif
                <span class="bh-pill">
                    <span class="bh-av" style="background: {{ $__warna($__nm) }}">{{ mb_strtoupper(mb_substr($__nm, 0, 1)) }}</span>
                    <span class="bh-pill-name">{{ $__nm }}</span>
                </span>
            @endforeach
        </div>
    @endif

    <div class="bh-name">{{ $nama ?? '' }}</div>
</div>
