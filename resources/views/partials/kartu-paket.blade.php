{{-- Kartu paket bundling, seragam dengan kartu produk satuan.

     Memakai kerangka .fs-card yang sama persis dengan kartu produk di halaman
     Shop dan etalase flash sale — gambar, judul, harga, lalu dua tombol.
     Disatukan di sini supaya beranda dan halaman paket tidak bisa berbeda
     tampilan: dua salinan markup pasti menyimpang cepat atau lambat.

     Variabel:
       $item        paket bundling
                    Kosong = tombolnya jadi tautan ke halaman daftar paket,
                    dipakai halaman yang tidak punya jendela detail.
--}}
{{-- @once: partial ini dipanggil di dalam perulangan, jadi tanpa penjaga ini
     blok gaya tercetak sebanyak jumlah paket.

     Ditulis INLINE, bukan di resources/css: berkas hasil Vite di server beku di
     versi yang terakhir diunggah, sehingga kelas baru di sana tidak akan pernah
     tampil sampai ada yang membangun & mengunggahnya ulang. --}}
@once
    <style>
        .kp-isi {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin: 6px 0 2px;
            font-size: .78rem;
            line-height: 1.35;
            color: #64748b;
        }

        .kp-isi i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        .kp-kode {
            margin-top: 4px;
            font-size: .74rem;
            font-weight: 600;
            color: var(--ph-orange, #fb8c00);
        }

        .kp-kode b {
            letter-spacing: .3px;
        }
    </style>
@endonce

@php
    // Harga tayang dari satu sumber yang sama dengan keranjang — lihat
    // App\Support\HargaPaket.
    $hp = \App\Support\HargaPaket::untuk($item);
    $isiPaket = collect([1, 2, 3, 4, 5])
        ->map(fn ($i) => $item->{'product'.$i})
        ->filter();
@endphp

<div class="fs-card">
    <div class="fs-card-media">
        @if ($item->gambar)
            <img loading="lazy" src="{{ asset('storage/img/ProductBundlings/' . basename($item->gambar)) }}"
                alt="{{ $item->nama_paket }}">
        @else
            <img loading="lazy" src="{{ asset('storage/img/phoenix-mark.png') }}" alt="{{ $item->nama_paket }}">
        @endif

        @if ($hp['potongan'] > 0)
            <span class="fs-badge fs-badge-flash">
                <i class="bi bi-lightning-charge-fill"></i>
                Hemat Rp{{ number_format($hp['potongan'], 0, ',', '.') }}
            </span>
        @else
            <span class="fs-badge">Paket Hemat</span>
        @endif
    </div>

    <div class="fs-card-body">
        <span class="fs-name">{{ $item->nama_paket }}</span>

        <div class="fs-price">
            <span class="fs-price-sale">Rp{{ number_format($hp['bayar'], 0, ',', '.') }}</span>
            @if ($hp['coret'] > $hp['bayar'])
                <span class="fs-price-orig">Rp{{ number_format($hp['coret'], 0, ',', '.') }}</span>
            @endif
            <small>/ paket</small>
        </div>

        @if ($hp['butuh_kode'])
            {{-- Promo berkode tidak berlaku sendiri. Kodenya WAJIB terlihat,
                 kalau tidak pembeli mengira harga ini otomatis lalu kecewa saat
                 checkout menagih harga penuh. --}}
            <div class="kp-kode">pakai kode <b>{{ $hp['promo']->kode_promo }}</b></div>
        @endif

        @if ($isiPaket->isNotEmpty())
            <div class="kp-isi">
                <i class="bi bi-box-seam"></i>
                {{ $isiPaket->map->nama_akun->join(' + ') }}
            </div>
        @endif

        <div class="fs-actions">
            <button type="button" wire:click="addToCart('{{ $item->id }}')" wire:loading.attr="disabled"
                wire:target="addToCart('{{ $item->id }}')" class="fs-btn-cart">
                <span wire:loading.remove wire:target="addToCart('{{ $item->id }}')"><i class="bi bi-cart-plus"></i>
                    Keranjang</span>
                <span wire:loading wire:target="addToCart('{{ $item->id }}')"><span
                        class="spinner-border spinner-border-sm"></span></span>
            </button>

            <a href="{{ route('bundling.detail', $item->id) }}" class="fs-btn-view">Lihat</a>
        </div>
    </div>
</div>
