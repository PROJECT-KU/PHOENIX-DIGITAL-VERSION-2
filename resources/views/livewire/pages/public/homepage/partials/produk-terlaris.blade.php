{{-- Produk terlaris, dihitung dari pesanan yang benar-benar dibayar.

     Dulu bagian ini empat kartu yang DITULIS TETAP di sini: nama, gambar, dan
     keterangannya dipatok, semuanya menaut ke /shop dan bukan ke produknya.
     Daftar itu tidak pernah berubah meski yang laku sudah berganti
     berbulan-bulan — dan tidak ada yang tahu ia bohong, karena tampilannya
     tetap meyakinkan.

     Gaya ditulis inline, bukan di stylesheet Vite: public/build masuk
     .gitignore, jadi markup bisa sampai ke server (git pull) tanpa CSS-nya.

     TANPA pita jaminan di bawahnya. Rancangan yang dicontoh memang memuatnya,
     tapi beranda ini sudah punya baris kepercayaan sendiri tepat di bawah hero
     (Proses Instan / Bergaransi / Bantuan 24/7 — lihat partials/banner). Dua
     baris yang mengatakan hal sama dalam satu layar bukan menguatkan, melainkan
     saling melemahkan: pembaca berhenti mempercayai keduanya. --}}
@php
    // Harga ditampilkan dengan satuan yang benar. Produk jasa harga per
    // bulannya 0; tanpa pembedaan ini semuanya tampil "Rp 0 / bulan".
    $hargaProduk = function ($p) {
        $isJasa = (bool) $p->butuh_file;
        $perHalaman = $isJasa && $p->jasaPerHalaman();

        return [
            'nilai' => $perHalaman
                ? (int) $p->hargaPerHalaman()
                : ($isJasa ? (int) ($p->hargaSekali() ?? 0) : (int) $p->harga_perbulan),
            'satuan' => $perHalaman ? '/ halaman' : ($isJasa ? '/ cek' : '/ bulan'),
        ];
    };
@endphp

@if ($terlaris->isNotEmpty())
<section id="promo-cards" class="promo-cards section">
    <style>
        /* ===== Produk Terlaris ===== */
        #promo-cards { padding-top: 40px; }

        /* Lima kartu sejajar. Grid, bukan flex: tiap kolom lebarnya sama persis
           berapa pun panjang nama produknya, jadi barisnya tidak pernah pincang. */
        .pt-deret { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; }

        /* Kartu dibuat datar: garis tepi tipis, tanpa bayangan, tanpa melompat
           saat disentuh. Lima kartu yang serentak mengambang begitu kursor lewat
           membuat halaman terasa seperti demo, bukan toko. */
        .pt-kartu {
            display: flex; flex-direction: column; text-align: left;
            background: #fff; border: 1px solid #eceff3; border-radius: 14px;
            padding: 20px 18px 18px; position: relative; overflow: hidden;
            text-decoration: none; color: inherit;
            transition: border-color .25s ease;
        }
        .pt-kartu:hover { color: inherit; border-color: #f7c9a3; }

        /* Garis jingga yang MENARIK DIRI dari kiri ke kanan di tepi atas kartu.
           Bukan sekadar warna yang berganti: gerakannya yang memberi tahu bahwa
           kartu ini menanggapi, sementara kartunya sendiri tetap diam — jauh
           lebih tenang daripada lima kartu yang serentak melompat. */
        .pt-kartu::after {
            content: ""; position: absolute; top: 0; left: 0; height: 2px; width: 100%;
            background: linear-gradient(90deg, #fba919, #f26522);
            transform: scaleX(0); transform-origin: left;
            transition: transform .3s cubic-bezier(.4, 0, .2, 1);
        }
        .pt-kartu:hover::after { transform: scaleX(1); }

        /* Dimatikan bagi yang menyetel perangkatnya mengurangi gerak. */
        @media (prefers-reduced-motion: reduce) {
            .pt-kartu::after { transition: none; }
        }

        /* Lencana hanya untuk peringkat satu, dan tanpa pil: cukup teks kecil
           berwarna merek. Kartu lain tidak lagi bernomor — angka #2 sampai #5
           tidak memberi tahu apa pun yang berguna bagi pembeli. */
        .pt-lencana {
            display: flex; height: 16px; align-items: center; gap: 6px; margin-bottom: 10px;
            color: #f26522; font-size: .68rem; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase;
        }
        .pt-lencana i.bi { font-size: .72rem; line-height: 1; }
        .pt-lencana i.bi::before { display: block; line-height: 1; }

        .pt-logo { height: 84px; display: flex; align-items: center; justify-content: flex-start; margin: 4px 0 16px; }
        .pt-logo img { max-height: 88px; max-width: 100%; object-fit: contain; }

        /* Nama, keterangan, dan jumlah pesanan dipatok tingginya masing-masing.
           Tanpa itu, nama yang pecah jadi dua baris menggeser seluruh isi kartu
           ke bawah, dan kelima kartu berhenti sejajar meski tinggi luarnya sama. */
        .pt-nama {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.05rem;
            color: #23272f; line-height: 1.3; margin: 0 0 8px; min-height: 2.6em;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .pt-ket {
            color: #6b7280; font-size: .84rem; line-height: 1.55; margin: 0 0 10px; min-height: 3.1em;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        /* Bukti bahwa "terlaris" bukan klaim kosong. Dibuat kecil dan redup:
           yang menjual adalah produknya, angka ini hanya menguatkan. */
        .pt-laku { font-size: .76rem; color: #9ca3af; font-weight: 600; margin: 0 0 14px; min-height: 1.2em; }
        .pt-laku b { color: #f26522; font-weight: 700; }

        .pt-harga { border-top: 1px solid #f2f4f7; padding-top: 14px; margin-top: auto; text-align: left; }
        .pt-harga small { display: block; font-size: .76rem; color: #9ca3af; margin-bottom: 2px; }
        .pt-harga b { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.28rem; color: #f26522; }
        .pt-harga span { font-size: .8rem; color: #9ca3af; font-weight: 600; }

        /* Tautan teks, bukan tombol berbingkai. Lima tombol sekaligus dalam satu
           baris membuat semuanya terasa sama mendesak, padahal seluruh kartunya
           memang sudah bisa diklik. */
        .pt-tombol {
            display: inline-flex; align-items: center; gap: 7px; margin-top: 14px;
            color: #f26522; font-weight: 700; font-size: .86rem;
        }
        .pt-kartu:hover .pt-tombol { color: #d9531a; }
        .pt-tombol i.bi { line-height: 1; }
        .pt-tombol i.bi::before { display: block; line-height: 1; }

        @media (max-width: 1199.98px) { .pt-deret { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 767.98px) {
            .pt-head { flex-direction: column; align-items: flex-start; gap: 10px; }
            .pt-head h2 { font-size: 1.6rem; }
            .pt-deret { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .pt-kartu { padding: 18px 14px 16px; }
            .pt-logo { height: 72px; }
            .pt-logo img { max-height: 66px; }
            .pt-nama { font-size: .95rem; }
        }
    </style>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <x-kepala-bagian
            ikon="bi-fire"
            kicker="Produk Terlaris"
            judul="Pilihan Terbaik untuk Kamu"
            sub="Tools pilihan untuk riset, skripsi & produktivitas yang paling banyak dipesan pelanggan."
            :tautan-url="route('shop.index')"
            tautan-teks="Lihat Semua Produk" />

        <div class="pt-deret">
            @foreach ($terlaris as $i => $p)
                @php $h = $hargaProduk($p); @endphp
                <a class="pt-kartu" href="{{ route('shop.detail-product', $p->id) }}" wire:key="terlaris-{{ $p->id }}">
                    {{-- Selalu dirender, meski kosong: lencana ini kini elemen biasa,
                         jadi tanpa slot yang tetap ada kartu pertama akan lebih tinggi
                         daripada empat kartu di sebelahnya. --}}
                    <span class="pt-lencana">
                        @if ($i === 0)<i class="bi bi-star-fill"></i> Terlaris @endif
                    </span>

                    <div class="pt-logo">
                        @if ($p->image)
                            <img loading="lazy" src="{{ asset('storage/img/Product/'.$p->image) }}" alt="{{ $p->nama_akun }}">
                        @else
                            <img loading="lazy" src="{{ asset('niceshop/assets/img/product/scopus.png') }}" alt="{{ $p->nama_akun }}">
                        @endif
                    </div>

                    <h3 class="pt-nama">{{ $p->nama_akun }}</h3>

                    @if ($p->deskripsi)
                        <p class="pt-ket">{{ \Illuminate\Support\Str::limit(strip_tags($p->deskripsi), 80) }}</p>
                    @endif

                    {{-- Selalu dirender, meski kosong: ruangnya harus tetap ada supaya
                         kartu tanpa angka pesanan tidak naik sendiri. --}}
                    <p class="pt-laku">
                        @if ($p->pesanan > 0)<b>{{ $p->pesanan }}×</b> dipesan bulan ini @endif
                    </p>

                    <div class="pt-harga">
                        <small>Mulai dari</small>
                        <b>Rp{{ number_format($h['nilai'], 0, ',', '.') }}</b> <span>{{ $h['satuan'] }}</span>
                    </div>

                    <span class="pt-tombol">Lihat Produk <i class="bi bi-arrow-right"></i></span>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif
