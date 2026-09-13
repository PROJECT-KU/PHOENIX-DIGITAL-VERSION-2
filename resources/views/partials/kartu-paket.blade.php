{{-- Kartu paket bundling.

     Disatukan di sini supaya beranda dan halaman paket tidak bisa berbeda
     tampilan: dua salinan markup pasti menyimpang cepat atau lambat.

     Variabel:
       $item        paket bundling
--}}
{{-- @once: partial ini dipanggil di dalam perulangan, jadi tanpa penjaga ini
     blok gaya tercetak sebanyak jumlah paket.

     Ditulis INLINE, bukan di resources/css: berkas hasil Vite di server beku di
     versi yang terakhir diunggah, sehingga kelas baru di sana tidak akan pernah
     tampil sampai ada yang membangun & mengunggahnya ulang. --}}
@once
    <style>
        /* ===== Kartu paket =====
           Awalan pkt-, BUKAN kp-: kartu "Kategori Populer" di beranda
           (kategori-populer.blade.php) sudah memakai .kp-kartu dan .kp-nama.
           Versi pertama kartu ini memakai kp- juga, dan gayanya langsung bocor
           ke sembilan kartu kategori di halaman yang sama — tingginya berubah
           dari 143px jadi ikut aturan kartu paket. Tidak ada galat, hanya
           halaman yang diam-diam berubah.

           Rupanya mencontoh kartu "Produk Terlaris" di beranda (.pt-*): datar,
           bergaris tepi tipis, pelat netral untuk logonya, dan garis jingga yang
           menarik diri di tepi atas saat disentuh.

           Dulu memakai kerangka .fs-card milik kartu flash sale — latar persik
           bergradasi dengan kilau berdenyut di belakang logo. Ramai sendiri di
           sebelah kartu terlaris yang tenang, dan .fs-card itu sendiri
           didefinisikan di public-custom-styles.css yang beku di server. Kelas
           pkt-* di bawah membuat kartu ini berdiri sendiri. */
        .pkt-kartu {
            display: flex; flex-direction: column;
            background: #fff; border: 1px solid #eceff3; border-radius: 14px;
            padding: 16px 16px 18px; position: relative; overflow: hidden;
            height: 100%;
            transition: border-color .25s ease;
        }
        .pkt-kartu:hover { border-color: #f7c9a3; }
        /* Garis jingga yang menarik diri dari kiri ke kanan — kartunya sendiri
           tetap diam. Sederet kartu yang serentak melompat membuat halaman
           terasa seperti demo, bukan toko. */
        .pkt-kartu::after {
            content: ""; position: absolute; top: 0; left: 0; height: 2px; width: 100%;
            background: linear-gradient(90deg, #fba919, #f26522);
            transform: scaleX(0); transform-origin: left;
            transition: transform .3s cubic-bezier(.4, 0, .2, 1);
        }
        .pkt-kartu:hover::after { transform: scaleX(1); }

        /* --- Pelat logo ---
           Gambar paket datang dengan bentuk & warna latar berbeda-beda; pelat
           netral memberi mereka satu bidang yang sama, dan rasio tetap menjaga
           tinggi kartunya seragam. */
        .pkt-pelat {
            position: relative; margin-bottom: 14px;
            aspect-ratio: 4 / 3; border-radius: 12px;
            background: #f8fafc; border: 1px solid #f1f4f8;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            transition: background .25s ease, border-color .25s ease;
        }
        .pkt-kartu:hover .pkt-pelat { background: #fff8f2; border-color: #f9e0cc; }
        .pkt-pelat img { max-width: 76%; max-height: 72%; object-fit: contain; display: block; mix-blend-mode: multiply; }

        /* Ubin cadangan — menyalin .sk-cadangan milik kartu produk di /shop.
           Dulu cadangannya (logo Phoenix) hanya dipakai bila kolom `gambar`
           bernilai NULL, padahal yang biasa terjadi adalah gambar TERISI tetapi
           berkasnya tidak ada — dan karena alt-nya memuat nama paket, yang
           tampil justru teks alt mentah: "Combo Riset Hemat" sebagai gambar
           rusak. */
        .pkt-cadangan {
            display: none; position: relative; align-items: center; justify-content: center;
            width: 58px; height: 58px; border-radius: 18px;
            background: linear-gradient(140deg, var(--pktc), color-mix(in srgb, var(--pktc) 60%, #fff));
            color: #fff; font-size: 1.5rem;
            box-shadow: 0 14px 26px -14px color-mix(in srgb, var(--pktc) 85%, transparent);
            transition: transform .35s ease;
        }
        .pkt-pelat.is-kosong { background: color-mix(in srgb, var(--pktc) 8%, #fff); border-color: color-mix(in srgb, var(--pktc) 18%, #fff); }
        .pkt-pelat.is-kosong .pkt-cadangan { display: flex; }
        .pkt-kartu:hover .pkt-cadangan { transform: scale(1.06) rotate(-4deg); }
        .pkt-cadangan i.bi, .pkt-cadangan i.bi::before { display: block; line-height: 1; }

        /* Lencana hemat di pojok pelat — keterangan paketnya, bukan judul
           barisnya. */
        .pkt-hemat {
            position: absolute; top: 8px; left: 8px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px;
            height: 26px; padding: 0 10px; border-radius: 99px;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: .72rem; white-space: nowrap;
            box-shadow: 0 8px 16px -10px rgba(242, 101, 34, .9);
        }
        .pkt-hemat i.bi, .pkt-hemat i.bi::before { display: block; line-height: 1; font-size: .75rem; }

        .pkt-nama {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: 1.02rem;
            color: #23272f; line-height: 1.35; margin: 0 0 8px; letter-spacing: -.015em;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        .pkt-isi {
            display: flex; align-items: flex-start; gap: 6px; margin: 0 0 4px;
            font-size: .78rem; line-height: 1.45; color: #64748b;
        }
        .pkt-isi i { margin-top: 2px; flex-shrink: 0; color: #94a3b8; }

        .pkt-kode { margin-top: 2px; font-size: .74rem; font-weight: 600; color: #f26522; }
        .pkt-kode b { letter-spacing: .3px; }

        /* Harga didorong ke bawah oleh margin-top:auto supaya sederet kartu
           dengan isi paket yang berbeda panjangnya tetap sejajar harganya. */
        .pkt-harga { border-top: 1px solid #f2f4f7; padding-top: 13px; margin-top: auto; }
        .pkt-harga small { display: block; font-size: .74rem; color: #9ca3af; margin-bottom: 2px; }
        .pkt-nominal { display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px; }
        .pkt-harga b {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.32rem; color: #f26522; letter-spacing: -.03em;
            font-variant-numeric: lining-nums tabular-nums;
        }
        .pkt-coret { font-size: .82rem; color: #9ca3af; text-decoration: line-through; }
        .pkt-satuan { font-size: .78rem; color: #9ca3af; font-weight: 600; }

        .pkt-aksi { display: flex; gap: 8px; margin-top: 13px; }
        .pkt-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            height: 42px; border-radius: 11px; cursor: pointer; text-decoration: none;
            border: 1.5px solid transparent;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: .84rem;
            white-space: nowrap;
            transition: filter .16s ease, border-color .16s ease, color .16s ease;
        }
        /* Isi tombol dibungkus <span> oleh wire:loading. Span polos bukan flex,
           jadi ikon yang display:block memaksa ganti baris dan ikonnya berdiri
           DI ATAS teksnya — bukan di sampingnya. Cacat yang sama pernah muncul
           di tombol "Pakai" pada /checkout. */
        .pkt-btn > span { display: inline-flex; align-items: center; justify-content: center; gap: 7px; }
        .pkt-btn i.bi, .pkt-btn i.bi::before { display: block; line-height: 1; font-size: .9rem; }
        .pkt-btn:disabled { opacity: .6; cursor: wait; }
        /* border:0 — cincin border transparan di atas latar gradasi
           meninggalkan garis pucat di tepi tombol. */
        .pkt-btn-keranjang { flex: 1 1 auto; background: linear-gradient(135deg, #fba919, #f26522); border: 0; color: #fff; }
        .pkt-btn-keranjang:not(:disabled):hover { filter: brightness(1.05); color: #fff; }
        .pkt-btn-lihat { flex: 0 0 auto; padding: 0 16px; background: #fff; border-color: #e7ebf0; color: #23272f; }
        .pkt-btn-lihat:hover { border-color: #f26522; color: #c2410c; }

        @media (max-width: 575.98px) {
            .pkt-kartu { padding: 14px 13px 16px; }
            .pkt-nama { font-size: .94rem; }
            .pkt-harga b { font-size: 1.18rem; }
            .pkt-btn { height: 40px; font-size: .8rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pkt-kartu::after, .pkt-pelat, .pkt-cadangan, .pkt-btn { transition: none; }
            .pkt-kartu:hover .pkt-cadangan { transform: none; }
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

    // Warna & ikon kategori 'Paket Bundling' di KategoriBeranda — taksonomi yang
    // sama dengan Shop, keranjang, dan checkout.
    $katKp = collect(\App\Support\KategoriBeranda::PETA)->firstWhere('label', 'Paket Bundling');
    $warnaKp = $katKp['warna'] ?? '#f26522';
    $ikonKp = $katKp['ikon'] ?? 'bi-box-seam';

    // Gambar dipakai HANYA bila berkasnya benar-benar ada. Storage::exists,
    // bukan is_file, mengikuti cara /shop memeriksanya.
    $gambarKp = $item->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists('img/ProductBundlings/'.basename($item->gambar))
        ? asset('storage/img/ProductBundlings/'.basename($item->gambar))
        : null;
@endphp

<div class="pkt-kartu">
    <div class="pkt-pelat {{ $gambarKp ? '' : 'is-kosong' }}" style="--pktc: {{ $warnaKp }}">
        {{-- Berlapis dua, sama dengan /shop: penjaga di server untuk berkas yang
             memang tidak ada, dan onerror untuk berkas yang ada saat dirender
             tetapi gagal diambil peramban. --}}
        @if ($gambarKp)
            <img loading="lazy" src="{{ $gambarKp }}" alt="{{ $item->nama_paket }}"
                onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
        @endif
        <span class="pkt-cadangan"><i class="bi {{ $ikonKp }}"></i></span>

        @if ($hp['potongan'] > 0)
            <span class="pkt-hemat">
                <i class="bi bi-lightning-charge-fill"></i>
                Hemat Rp{{ number_format($hp['potongan'], 0, ',', '.') }}
            </span>
        @endif
        {{-- Tidak ada lencana pengganti bila paketnya memang tanpa potongan.
             "Paket Hemat" yang tertempel di semua kartu — termasuk yang harganya
             tidak dipotong sama sekali — bukan cuma hiasan, melainkan klaim yang
             tidak dibuktikan angka mana pun di kartu itu. --}}
    </div>

    <h3 class="pkt-nama">{{ $item->nama_paket }}</h3>

    @if ($isiPaket->isNotEmpty())
        <div class="pkt-isi">
            <i class="bi bi-box-seam"></i>
            <span>{{ $isiPaket->map->nama_akun->join(' + ') }}</span>
        </div>
    @endif

    @if ($hp['butuh_kode'])
        {{-- Promo berkode tidak berlaku sendiri. Kodenya WAJIB terlihat, kalau
             tidak pembeli mengira harga ini otomatis lalu kecewa saat checkout
             menagih harga penuh. --}}
        <div class="pkt-kode">pakai kode <b>{{ $hp['promo']->kode_promo }}</b></div>
    @endif

    <div class="pkt-harga">
        <small>Mulai dari</small>
        <div class="pkt-nominal">
            <b>Rp{{ number_format($hp['bayar'], 0, ',', '.') }}</b>
            @if ($hp['coret'] > $hp['bayar'])
                <span class="pkt-coret">Rp{{ number_format($hp['coret'], 0, ',', '.') }}</span>
            @endif
            <span class="pkt-satuan">/ paket</span>
        </div>
    </div>

    <div class="pkt-aksi">
        <button type="button" class="pkt-btn pkt-btn-keranjang"
            wire:click="addToCart('{{ $item->id }}')" wire:loading.attr="disabled"
            wire:target="addToCart('{{ $item->id }}')">
            <span wire:loading.remove wire:target="addToCart('{{ $item->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
            <span wire:loading wire:target="addToCart('{{ $item->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
        </button>

        <a href="{{ route('bundling.detail', $item->id) }}" class="pkt-btn pkt-btn-lihat">Lihat</a>
    </div>
</div>
