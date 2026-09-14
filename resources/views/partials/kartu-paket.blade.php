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

        /* --- Media ---
           Perlakuan yang sama dengan kartu paket di /bundling/product (.pb-media):
           sapuan warna KATEGORI, bola cahaya di pojok, dan — bila gambarnya
           tidak ada — TUMPUKAN UBIN per produk isi paket, masing-masing
           berwarna kategorinya sendiri.

           Ubin tunggal berikon kotak yang dipakai sebelumnya membuat keempat
           kartu terlihat sama persis; tumpukan ini justru memberi tahu isi
           paketnya tanpa satu kata pun. */
        .pkt-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            aspect-ratio: 16 / 11; border-radius: 12px; overflow: hidden; margin-bottom: 14px;
            background: linear-gradient(160deg, color-mix(in srgb, var(--c) 11%, #fff) 0%, #fff 78%);
        }
        .pkt-media::before {
            content: ""; position: absolute; top: -44px; right: -44px;
            width: 132px; height: 132px; border-radius: 50%;
            background: color-mix(in srgb, var(--c) 12%, transparent);
            transition: transform .35s ease;
        }
        .pkt-kartu:hover .pkt-media::before { transform: scale(1.3); }
        .pkt-media img {
            position: relative; max-width: 70%; max-height: 66%; object-fit: contain;
            mix-blend-mode: multiply; transition: transform .35s ease;
        }
        .pkt-kartu:hover .pkt-media img { transform: scale(1.05); }

        .pkt-tumpuk { display: none; position: relative; align-items: center; justify-content: center; padding-left: 12px; }
        .pkt-media.is-kosong .pkt-tumpuk { display: flex; }
        .pkt-tumpuk > span {
            width: 50px; height: 50px; margin-left: -12px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(140deg, var(--p), color-mix(in srgb, var(--p) 60%, #fff));
            color: #fff; font-size: 1.25rem;
            box-shadow: 0 0 0 3px #fff, 0 12px 22px -12px color-mix(in srgb, var(--p) 85%, transparent);
            transform: rotate(var(--r, 0deg)); transition: transform .3s ease;
        }
        .pkt-kartu:hover .pkt-tumpuk > span { transform: rotate(0deg) translateY(-3px); }
        .pkt-tumpuk i.bi, .pkt-tumpuk i.bi::before { display: block; line-height: 1; }

        /* Berapa produk di dalamnya — pertanyaan pertama tentang sebuah paket. */
        .pkt-jumlah {
            position: absolute; top: 10px; right: 10px; z-index: 1;
            display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px 0 8px;
            border-radius: 99px; background: rgba(255, 255, 255, .92);
            border: 1px solid color-mix(in srgb, var(--c) 22%, #fff);
            color: var(--c); font-size: .7rem; font-weight: 700; white-space: nowrap;
        }
        .pkt-jumlah i.bi, .pkt-jumlah i.bi::before { display: block; line-height: 1; font-size: .72rem; }

        /* Lencana hemat di pojok KIRI media; jumlah produk di kanan, jadi
           keduanya tidak berebut satu titik. */
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

            /* Dua kartu sebaris (col-6) menyisakan ~143px untuk isi kartu,
               sedangkan "Keranjang" + "Lihat" berdampingan butuh ~168px —
               tombol "Lihat" menyembul keluar kartu lalu terpotong kartu di
               sebelahnya. Ditumpuk, keduanya utuh dan bidang sentuhnya justru
               lebih lebar. */
            .pkt-aksi { flex-direction: column; gap: 8px; }
            .pkt-btn { width: 100%; flex: 0 0 auto; }
            .pkt-btn-lihat { padding: 0 12px; }

            /* Lencana hemat dipendekkan: "Hemat Rp250.000" pada ukuran penuh
               lebih lebar dari bidang gambarnya sendiri, dan ujungnya terpotong
               karena bidang itu memotong apa pun yang menjorok keluar. */
            .pkt-hemat {
                height: 23px; padding: 0 8px; gap: 4px; font-size: .64rem;
                max-width: calc(100% - 16px);
            }
            .pkt-hemat i.bi, .pkt-hemat i.bi::before { font-size: .68rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pkt-kartu::after, .pkt-media::before, .pkt-media img, .pkt-tumpuk > span, .pkt-btn { transition: none; }
            .pkt-kartu:hover .pkt-media::before,
            .pkt-kartu:hover .pkt-media img,
            .pkt-kartu:hover .pkt-tumpuk > span { transform: none; }
        }
    </style>
@endonce

@php
    /*
     | Satu sumber untuk seluruh tampilan paket: warna aksen, tumpukan ubin isi,
     | penjaga berkas gambar, harga, hemat, dan kode promo. Halaman
     | /bundling/product dan halaman detail memakai helper yang sama — jadi
     | paket yang sama tidak mungkin tampil berbeda antar halaman.
     |
     | Sebelumnya kartu ini menghitung sendiri, dan hasilnya menyimpang: lencana
     | hematnya memakai 'potongan' (promo tambahan, hampir selalu 0) sehingga
     | tidak pernah muncul, padahal /bundling/product menampilkannya dari selisih
     | harga coret.
     */
    $k = \App\Support\KartuPaket::data($item);
@endphp

<div class="pkt-kartu" style="--c: {{ $k['warna'] }}">
    <div class="pkt-media {{ $k['gambar'] ? '' : 'is-kosong' }}">
        {{-- Berlapis dua, sama dengan /shop & /bundling/product: penjaga di
             server untuk berkas yang memang tidak ada, dan onerror untuk berkas
             yang ada saat dirender tetapi gagal diambil peramban. --}}
        @if ($k['gambar'])
            <img loading="lazy" src="{{ $k['gambar'] }}" alt=""
                onerror="this.parentNode.classList.add('is-kosong'); this.remove();">
        @endif

        <span class="pkt-tumpuk">
            @foreach ($k['tumpuk'] as $t)
                <span style="--p: {{ $t['warna'] }}; --r: {{ $t['putar'] }}deg"><i class="bi {{ $t['ikon'] }}"></i></span>
            @endforeach
        </span>

        <span class="pkt-jumlah"><i class="bi bi-box-seam"></i>{{ $k['jumlahIsi'] }} produk</span>

        @if ($k['hemat'])
            <span class="pkt-hemat"><i class="bi bi-lightning-charge-fill"></i>{{ $k['hemat'] }}</span>
        @endif
    </div>

    <h3 class="pkt-nama">{{ $k['nama'] }}</h3>

    @if (! empty($k['isi']))
        <div class="pkt-isi">
            <i class="bi bi-box-seam"></i>
            <span>{{ collect($k['isi'])->pluck('nama')->join(' + ') }}</span>
        </div>
    @endif

    @if ($k['kode'])
        {{-- Promo berkode tidak berlaku sendiri. Kodenya WAJIB terlihat, kalau
             tidak pembeli mengira harga ini otomatis lalu kecewa saat checkout
             menagih harga penuh. --}}
        <div class="pkt-kode">pakai kode <b>{{ $k['kode'] }}</b></div>
    @endif

    <div class="pkt-harga">
        <small>Mulai dari</small>
        <div class="pkt-nominal">
            <b>Rp{{ number_format($k['harga'], 0, ',', '.') }}</b>
            @if ($k['hargaAsli'])
                <span class="pkt-coret">Rp{{ number_format($k['hargaAsli'], 0, ',', '.') }}</span>
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

        <a href="{{ $k['url'] }}" class="pkt-btn pkt-btn-lihat">Lihat</a>
    </div>
</div>
