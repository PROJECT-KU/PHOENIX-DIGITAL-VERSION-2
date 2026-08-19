<div class="phoenix-search" x-data="{ open: false }" @click.outside="open = false">
    <form wire:submit.prevent="search" class="ps-form" role="search">
        <i class="bi bi-search ps-icon"></i>
        <input
            type="text"
            wire:model.live.debounce.350ms="searchQuery"
            @focus="open = true"
            @input="open = true"
            class="ps-input"
            placeholder="Cari produk..."
            autocomplete="off">
        <span class="ps-loading" wire:loading wire:target="searchQuery">
            <span class="spinner-border spinner-border-sm"></span>
        </span>
        <button type="button" class="ps-clear" x-show="$wire.searchQuery.length"
            @click="$wire.set('searchQuery', ''); open = false" aria-label="Bersihkan">
            <i class="bi bi-x"></i>
        </button>
    </form>

    @if (trim($searchQuery) !== '')
    <div class="ps-dropdown" x-show="open" x-transition.opacity style="display:none;">
        @if ($results->isEmpty() && $bundlings->isEmpty())
        <div class="ps-empty">
            <i class="bi bi-search"></i>
            <span>Tidak ada hasil cocok dengan "<strong>{{ $searchQuery }}</strong>"</span>
        </div>
        @else
        {{-- Produk --}}
        @if ($results->isNotEmpty())
        <div class="ps-group-label">Produk</div>
        @foreach ($results as $item)
        @php
            // Produk JASA harga per bulannya 0 — harganya ada di paket
            // pengecekan. Tanpa ini pencarian menampilkan "Rp 0/bln".
            // Pola yang sama dipakai kartu produk di halaman shop.
            $isJasa = (bool) $item->butuh_file;
            $perHalaman = $isJasa && $item->jasaPerHalaman();
            $satuanCari = $perHalaman ? '/halaman' : ($isJasa ? '/cek' : '/bln');
            $hargaCari = $perHalaman
                ? (int) $item->hargaPerHalaman()
                : ($isJasa ? (int) ($item->hargaSekali() ?? 0) : (int) $item->harga_perbulan);
        @endphp
        <a href="{{ route('shop.detail-product', $item->id) }}" class="ps-item">
            <span class="ps-thumb">
                @if ($item->image)
                <img src="{{ asset('storage/img/Product/' . $item->image) }}" alt="{{ $item->nama_akun }}">
                @else
                <i class="bi bi-box-seam"></i>
                @endif
            </span>
            <span class="ps-info">
                <span class="ps-name">{{ $item->nama_akun }}</span>
                <span class="ps-price">Rp {{ number_format($hargaCari, 0, ',', '.') }}<small>{{ $satuanCari }}</small></span>
            </span>
            <i class="bi bi-arrow-right-short ps-go"></i>
        </a>
        @endforeach
        @endif

        {{-- Paket Bundling --}}
        @if ($bundlings->isNotEmpty())
        <div class="ps-group-label">Paket Bundling</div>
        @foreach ($bundlings as $b)
        @php $hpCari = \App\Support\HargaPaket::untuk($b); @endphp
        <a href="{{ route('bundling.detail', $b->id) }}" class="ps-item">
            <span class="ps-thumb ps-thumb-bundle">
                @if ($b->gambar)
                <img src="{{ asset('storage/img/ProductBundlings/' . $b->gambar) }}" alt="{{ $b->nama_paket }}">
                @else
                <i class="bi bi-box2-heart-fill"></i>
                @endif
            </span>
            <span class="ps-info">
                <span class="ps-name">{{ $b->nama_paket }}</span>
                {{-- HargaPaket: sumber harga yang sama dengan kartu, halaman detail,
                     dan keranjang — supaya angkanya tidak berbeda antar layar. --}}
                <span class="ps-price">Rp {{ number_format($hpCari['bayar'], 0, ',', '.') }} <small>/paket</small></span>
            </span>
            <i class="bi bi-arrow-right-short ps-go"></i>
        </a>
        @endforeach
        @endif

        @if ($results->isNotEmpty())
        <button type="button" wire:click="search" class="ps-all">
            Lihat semua produk <i class="bi bi-arrow-right"></i>
        </button>
        @endif
        @endif
    </div>
    @endif
</div>
