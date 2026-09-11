{{-- Deskripsi produk / paket yang dirapikan otomatis.

     Dipakai halaman detail produk, halaman detail paket, DAN pratinjau di
     form admin — satu sumber, jadi yang dilihat admin saat mengetik sama
     persis dengan yang dilihat pembeli.

     Butuh $teks (deskripsi mentah) atau $blok (hasil DeskripsiProduk::blok).
     Warna aksen mengikuti --c dari induknya (warna kategori di halaman
     produk), jatuh ke jingga merek bila tidak ada. --}}
@php
    $__blok = $blok ?? \App\Support\DeskripsiProduk::blok($teks ?? '');
    $__lead = true;
@endphp

@once
<style>
    /* Inline: public/build masuk .gitignore dan tidak ikut terdeploy. */
    .dr { --dr-c: var(--c, #f26522); color: #4b5563; }
    .dr > * { margin: 0; }
    .dr > * + * { margin-top: 14px; }

    /* Slogan dari baris judul ("Akun Siap Pakai"). Nama produknya sendiri
       sudah tampil besar di atas halaman, jadi yang ditonjolkan di sini hanya
       janjinya — mencetak ulang namanya berarti mengumumkan hal yang sama dua
       kali. */
    .dr-tagline {
        display: inline-flex; align-items: center; gap: 9px; max-width: 100%;
        padding: 6px 14px 6px 6px; border-radius: 999px;
        background: color-mix(in srgb, var(--dr-c) 10%, #fff);
        border: 1px solid color-mix(in srgb, var(--dr-c) 22%, #fff);
        color: var(--dr-c);
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 700; font-size: .84rem; line-height: 1.35;
    }
    .dr-tagline i.bi {
        flex: 0 0 auto; width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: var(--dr-c); color: #fff; font-size: .72rem; line-height: 1;
    }
    .dr-tagline i.bi::before { display: block; line-height: 1; }

    /* Paragraf pertama lebih besar dan lebih gelap: ia pembuka yang menjual.
       Sisanya abu dan lebih kecil, supaya mata tidak membaca semuanya sebagai
       sama penting. */
    .dr-lead { font-size: 1rem; line-height: 1.72; color: #1f2937; }
    .dr-p { font-size: .93rem; line-height: 1.72; color: #6b7280; }
    .dr strong { color: #111827; font-weight: 700; }

    .dr-sub {
        display: flex; align-items: center; gap: 10px;
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 800; font-size: .78rem; letter-spacing: .12em;
        text-transform: uppercase; color: #1c1f26; line-height: 1.3;
    }
    .dr-sub::before {
        content: ""; flex: 0 0 auto; width: 18px; height: 3px; border-radius: 3px;
        background: var(--dr-c);
    }
    /* Kepala daftar yang ditulis dengan emoji ("📌 Yang kamu dapat:"):
       emojinya menggantikan garis aksen, bukan ditambahkan di sebelahnya. */
    .dr-sub.dr-sub-ikon::before { display: none; }
    .dr-sub-ikon > span { font-size: 1rem; letter-spacing: 0; line-height: 1; }
    .dr > .dr-sub { margin-top: 24px; }
    .dr > .dr-sub:first-child { margin-top: 0; }
    .dr > .dr-sub + * { margin-top: 12px; }

    /* Poin: centang hijau di dalam bulatan. Kolomnya menyesuaikan lebar
       wadah (minimal 220px per kolom), bukan dipatok dua — di pratinjau admin
       yang sempit ia otomatis jadi satu kolom. */
    .dr-poin {
        list-style: none; padding: 0;
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px 20px;
    }
    .dr-poin li {
        display: flex; align-items: flex-start; gap: 10px;
        font-size: .92rem; line-height: 1.55; color: #1f2937; min-width: 0;
    }
    .dr-poin li i.bi {
        flex: 0 0 auto; width: 20px; height: 20px; border-radius: 50%; margin-top: 1px;
        display: flex; align-items: center; justify-content: center;
        background: #e8f7ee; color: #16a34a; font-size: .74rem; line-height: 1;
        animation: drDenyut 2.4s ease-in-out infinite;
        animation-delay: calc(var(--i, 0) * 200ms);
    }
    .dr-poin li i.bi::before { display: block; line-height: 1; }
    @keyframes drDenyut {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        50% { transform: scale(1.1); box-shadow: 0 0 0 4px rgba(22, 163, 74, .12); }
    }

    /* Poin yang ditulis dengan emoji ("🎮 Membuat kuis"): emojinya jadi ikon
       di ubin lembut bernada aksen, menggantikan centang. Ubin 26px sedikit
       dinaikkan supaya pusatnya sejajar dengan baris teks pertama. */
    .dr-emoji {
        flex: 0 0 auto; width: 26px; height: 26px; border-radius: 8px; margin-top: -2px;
        display: flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--dr-c) 9%, #fff);
        font-size: .9rem; line-height: 1;
    }

    /* Langkah bernomor, angkanya berwarna aksen. */
    .dr-langkah { list-style: none; padding: 0; display: grid; gap: 10px; counter-reset: dr; }
    .dr-langkah li {
        counter-increment: dr; display: flex; align-items: flex-start; gap: 12px;
        font-size: .92rem; line-height: 1.55; color: #1f2937;
    }
    .dr-langkah li::before {
        content: counter(dr); flex: 0 0 auto; width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--dr-c) 12%, #fff); color: var(--dr-c);
        font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
        font-weight: 800; font-size: .76rem; line-height: 1;
    }

    /* Catatan (📌 🎯 ⚡ 💡): kotak tipis bernada aksen, ikonnya dipertahankan. */
    .dr-catatan {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 11px 14px; border-radius: 12px;
        background: color-mix(in srgb, var(--dr-c) 6%, #fff);
        border: 1px solid color-mix(in srgb, var(--dr-c) 16%, #fff);
        font-size: .88rem; line-height: 1.55; color: #374151;
    }
    .dr > .dr-catatan + .dr-catatan { margin-top: 8px; }
    .dr-catatan-ikon { flex: 0 0 auto; font-size: 1rem; line-height: 1.4; }

    @media (prefers-reduced-motion: reduce) {
        .dr-poin li i.bi { animation: none; }
    }
</style>
@endonce

@if ($__blok)
    <div class="dr">
        @foreach ($__blok as $__b)
            @if ($__b['jenis'] === 'judul')
                <p class="dr-tagline"><i class="bi bi-stars"></i><span>{{ $__b['tagline'] ?? $__b['teks'] }}</span></p>
            @elseif ($__b['jenis'] === 'paragraf')
                <p class="{{ $__lead ? 'dr-lead' : 'dr-p' }}">{!! \App\Support\DeskripsiProduk::inline($__b['teks']) !!}</p>
                @php $__lead = false; @endphp
            @elseif ($__b['jenis'] === 'subjudul')
                <h4 class="dr-sub {{ isset($__b['ikon']) ? 'dr-sub-ikon' : '' }}">@isset($__b['ikon'])<span>{{ $__b['ikon'] }}</span>@endisset{{ $__b['teks'] }}</h4>
            @elseif ($__b['jenis'] === 'poin')
                <ul class="dr-poin">
                    @foreach ($__b['butir'] as $__i => $__x)
                        <li style="--i: {{ $__i }}">@if ($__b['ikon'][$__i] ?? null)<span class="dr-emoji">{{ $__b['ikon'][$__i] }}</span>@else<i class="bi bi-check-lg"></i>@endif<span>{!! \App\Support\DeskripsiProduk::inline($__x) !!}</span></li>
                    @endforeach
                </ul>
            @elseif ($__b['jenis'] === 'langkah')
                <ol class="dr-langkah">
                    @foreach ($__b['butir'] as $__x)
                        <li><span>{!! \App\Support\DeskripsiProduk::inline($__x) !!}</span></li>
                    @endforeach
                </ol>
            @elseif ($__b['jenis'] === 'catatan')
                <p class="dr-catatan"><span class="dr-catatan-ikon">{{ $__b['ikon'] }}</span><span>{!! \App\Support\DeskripsiProduk::inline($__b['teks']) !!}</span></p>
            @endif
        @endforeach
    </div>
@endif
