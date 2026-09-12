{{-- Pencarian global di header.

     Kelas cr-*: aturan .ps-* ada di public-custom-styles.css yang beku di
     server, jadi tampilan ini tidak bergantung padanya. Bahasa visualnya sama
     dengan Beranda/Shop/Bundling: ubin berwarna menurut KATEGORI produk,
     sudut 16-18px, dan sapuan warna tipis saat disentuh kursor.

     x-data sengaja tetap statis ({ open: false }) — nilai dari server di sini
     membuat morph Livewire mengikat ulang ke lingkup yang basi setiap kali
     hasil pencarian diperbarui. --}}
<div class="phoenix-search cr-cari" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
    <style>
        .cr-cari { position: relative; width: 100%; }
        .cr-form {
            position: relative; display: flex; align-items: center; gap: 10px;
            height: 46px; padding: 0 12px 0 14px; border-radius: 14px;
            background: #fff; border: 1.5px solid #eceff3;
            transition: border-color .18s ease, box-shadow .18s ease;
        }
        .cr-form:focus-within { border-color: #f26522; box-shadow: 0 0 0 4px rgba(242, 101, 34, .13); }
        .cr-form > .bi-search { color: #94a3b8; font-size: 1rem; transition: color .18s ease; }
        .cr-form > .bi-search, .cr-form > .bi-search::before { display: block; line-height: 1; }
        .cr-form:focus-within > .bi-search { color: #f26522; }
        .cr-input {
            flex: 1 1 auto; min-width: 0; height: 100%; border: 0; outline: none; background: transparent;
            font-size: .92rem; color: #1c1f26;
        }
        .cr-input::placeholder { color: #94a3b8; }
        .cr-loading { display: inline-flex; color: #f26522; }
        .cr-clear {
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 26px; height: 26px; border: 0; border-radius: 50%; background: #f1f5f9; color: #64748b;
            cursor: pointer; transition: background .18s ease, color .18s ease;
        }
        .cr-clear:hover { background: #fee2e2; color: #dc2626; }
        .cr-clear i.bi, .cr-clear i.bi::before { display: block; line-height: 1; font-size: .85rem; }

        /* ===== Panel hasil ===== */
        .cr-panel {
            position: absolute; top: calc(100% + 10px); left: 0; right: 0; z-index: 1200;
            max-height: min(70vh, 520px); overflow-y: auto; padding: 8px;
            background: #fff; border: 1px solid #eceff3; border-radius: 18px;
            box-shadow: 0 24px 54px -28px rgba(15, 23, 42, .55);
        }
        .cr-label {
            display: flex; align-items: center; gap: 7px; padding: 10px 10px 6px;
            font-size: .68rem; font-weight: 800; letter-spacing: .11em; text-transform: uppercase; color: #94a3b8;
        }
        .cr-label::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--c, #f26522); }
        .cr-item {
            display: flex; align-items: center; gap: 12px; padding: 9px 10px; border-radius: 13px;
            text-decoration: none; transition: background .16s ease;
        }
        .cr-item:hover { background: color-mix(in srgb, var(--c) 8%, #fff); }
        /* Ubin gambar: warnanya mengikuti kategori produk, sama seperti kartu
           di Shop — glif tunggal selalu display:block + line-height:1. */
        .cr-ubin {
            position: relative; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 44px; height: 44px; border-radius: 13px; overflow: hidden; font-size: 1.15rem;
            background: linear-gradient(150deg, color-mix(in srgb, var(--c) 16%, #fff) 0%, #fff 80%);
            color: color-mix(in srgb, var(--c) 85%, #0f172a);
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--c) 16%, transparent);
        }
        .cr-ubin img { width: 100%; height: 100%; object-fit: contain; padding: 5px; mix-blend-mode: multiply; }
        .cr-ubin i.bi, .cr-ubin i.bi::before { display: block; line-height: 1; }
        .cr-isi { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1 1 auto; }
        .cr-nama {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: .88rem;
            color: #1c1f26; line-height: 1.35; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .cr-kat { display: inline-flex; align-items: center; gap: 5px; font-size: .7rem; font-weight: 700; color: color-mix(in srgb, var(--c) 75%, #0f172a); }
        .cr-kat i.bi, .cr-kat i.bi::before { display: block; line-height: 1; font-size: .72rem; }
        .cr-harga { flex-shrink: 0; text-align: right; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; font-size: .88rem; color: #1c1f26; white-space: nowrap; }
        .cr-harga small { display: block; font-size: .68rem; font-weight: 600; color: #94a3b8; }
        .cr-panah {
            flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 26px; height: 26px;
            border-radius: 50%; background: #f8fafc; color: #94a3b8; font-size: .8rem;
            transition: background .16s ease, color .16s ease, transform .16s ease;
        }
        .cr-item:hover .cr-panah { background: var(--c); color: #fff; transform: translateX(2px); }
        .cr-panah i.bi, .cr-panah i.bi::before { display: block; line-height: 1; }

        /* Tanpa hasil */
        .cr-kosong { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 26px 18px 22px; text-align: center; }
        .cr-kosong-ubin {
            display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 16px;
            background: #fff5ee; color: #c2410c; font-size: 1.3rem;
        }
        .cr-kosong-ubin i.bi, .cr-kosong-ubin i.bi::before { display: block; line-height: 1; }
        .cr-kosong b { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800; font-size: .92rem; color: #1c1f26; }
        .cr-kosong span { font-size: .82rem; color: #64748b; line-height: 1.55; }
        .cr-saran { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 4px; }
        .cr-saran a {
            display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border-radius: 99px;
            background: #fff; border: 1px solid #eceff3; color: #475569; font-size: .78rem; font-weight: 700; text-decoration: none;
            transition: border-color .18s ease, color .18s ease;
        }
        .cr-saran a:hover { border-color: #f26522; color: #c2410c; }
        .cr-saran i.bi, .cr-saran i.bi::before { display: block; line-height: 1; font-size: .8rem; }

        /* Tombol lihat semua */
        .cr-semua {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; height: 42px;
            margin-top: 6px; border: 0; border-radius: 13px; cursor: pointer;
            background: var(--ph-grad, linear-gradient(135deg, #fba919, #f26522)); color: #fff;
            font-weight: 700; font-size: .85rem;
            box-shadow: 0 10px 20px -12px rgba(242, 101, 34, .8);
            transition: filter .16s ease;
        }
        .cr-semua:hover { filter: brightness(1.05); }
        .cr-semua i.bi, .cr-semua i.bi::before { display: block; line-height: 1; }

        @media (max-width: 991.98px) {
            .cr-panel { max-height: min(60vh, 420px); }
            .cr-harga { font-size: .84rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .cr-item, .cr-panah, .cr-item:hover .cr-panah { transition: none; transform: none; }
        }
    </style>

    <form wire:submit.prevent="search" class="cr-form" role="search">
        <i class="bi bi-search"></i>
        <input
            type="text"
            wire:model.live.debounce.350ms="searchQuery"
            @focus="open = true"
            @input="open = true"
            class="cr-input"
            placeholder="Cari produk atau paket..."
            aria-label="Cari produk atau paket"
            autocomplete="off">
        <span class="cr-loading" wire:loading wire:target="searchQuery">
            <span class="spinner-border spinner-border-sm"></span>
        </span>
        <button type="button" class="cr-clear" x-show="$wire.searchQuery.length"
            @click="$wire.set('searchQuery', ''); open = false" aria-label="Bersihkan pencarian">
            <i class="bi bi-x-lg"></i>
        </button>
    </form>

    @if (trim($searchQuery) !== '')
    <div class="cr-panel" x-show="open" x-transition.opacity style="display:none;">
        @if ($results->isEmpty() && $bundlings->isEmpty())
        <div class="cr-kosong">
            <span class="cr-kosong-ubin"><i class="bi bi-search-heart"></i></span>
            <b>Belum ada yang cocok</b>
            <span>Tidak ada produk atau paket untuk "{{ $searchQuery }}". Coba kata lain, atau telusuri katalognya.</span>
            <span class="cr-saran">
                <a href="{{ route('shop.index') }}"><i class="bi bi-bag"></i> Lihat Semua Produk</a>
                <a href="{{ route('bundling.product-bundlings') }}"><i class="bi bi-box-seam"></i> Paket Bundling</a>
            </span>
        </div>
        @else
        {{-- Produk --}}
        @if ($results->isNotEmpty())
        <div class="cr-label">Produk</div>
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

            // Warna & ikon mengikuti KATEGORI produk — taksonomi yang sama
            // dengan kartu di beranda, /shop, dan halaman detail.
            $katCari = \App\Support\KategoriBeranda::untukProduk($item->nama_akun);
            $gambarCari = $item->image && is_file(public_path('storage/img/Product/'.$item->image))
                ? asset('storage/img/Product/'.$item->image)
                : null;
        @endphp
        <a href="{{ route('shop.detail-product', $item->id) }}" class="cr-item" style="--c: {{ $katCari['warna'] ?? '#f26522' }}">
            <span class="cr-ubin">
                @if ($gambarCari)
                <img src="{{ $gambarCari }}" alt="" loading="lazy" onerror="this.remove();">
                @else
                <i class="bi {{ $katCari['ikon'] ?? 'bi-box-seam' }}"></i>
                @endif
            </span>
            <span class="cr-isi">
                <span class="cr-nama">{{ $item->nama_akun }}</span>
                <span class="cr-kat">
                    <i class="bi {{ $katCari['ikon'] ?? 'bi-tag' }}"></i> {{ $katCari['label'] ?? ucfirst((string) $item->tipe_akun) }}
                </span>
            </span>
            <span class="cr-harga">Rp {{ number_format($hargaCari, 0, ',', '.') }}<small>{{ $satuanCari }}</small></span>
            <span class="cr-panah"><i class="bi bi-arrow-right"></i></span>
        </a>
        @endforeach
        @endif

        {{-- Paket Bundling --}}
        @if ($bundlings->isNotEmpty())
        <div class="cr-label" style="--c: #f26522">Paket Bundling</div>
        @foreach ($bundlings as $b)
        @php
            $hpCari = \App\Support\HargaPaket::untuk($b);
            $gambarPaket = $b->gambar && is_file(public_path('storage/img/ProductBundlings/'.$b->gambar))
                ? asset('storage/img/ProductBundlings/'.$b->gambar)
                : null;
        @endphp
        <a href="{{ route('bundling.detail', $b->id) }}" class="cr-item" style="--c: #f26522">
            <span class="cr-ubin">
                @if ($gambarPaket)
                <img src="{{ $gambarPaket }}" alt="" loading="lazy" onerror="this.remove();">
                @else
                <i class="bi bi-box2-heart-fill"></i>
                @endif
            </span>
            <span class="cr-isi">
                <span class="cr-nama">{{ $b->nama_paket }}</span>
                <span class="cr-kat"><i class="bi bi-box-seam"></i> Paket Bundling</span>
            </span>
            {{-- HargaPaket: sumber harga yang sama dengan kartu, halaman detail,
                 dan keranjang — supaya angkanya tidak berbeda antar layar. --}}
            <span class="cr-harga">Rp {{ number_format($hpCari['bayar'], 0, ',', '.') }}<small>/paket</small></span>
            <span class="cr-panah"><i class="bi bi-arrow-right"></i></span>
        </a>
        @endforeach
        @endif

        @if ($results->isNotEmpty())
        <button type="button" wire:click="search" class="cr-semua">
            Lihat semua hasil <i class="bi bi-arrow-right"></i>
        </button>
        @endif
        @endif
    </div>
    @endif
</div>
