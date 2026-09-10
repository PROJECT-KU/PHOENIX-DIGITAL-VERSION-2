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
            padding: 16px 16px 18px; position: relative; overflow: hidden;
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

        /* --- Pelat logo --- */
        /* Gambar produk datang dengan bentuk dan warna latar berbeda-beda;
           ditaruh langsung di atas kartu putih, kelimanya melayang di
           ketinggian berbeda dan barisnya terlihat goyah. Pelat memberi mereka
           satu bidang yang sama, dan rasio tetap menjaga tingginya seragam. */
        .pt-pelat {
            position: relative; margin-bottom: 16px;
            aspect-ratio: 4 / 3; border-radius: 12px;
            background: #f8fafc; border: 1px solid #f1f4f8;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            transition: background .25s ease, border-color .25s ease;
        }
        .pt-kartu:hover .pt-pelat { background: #fff8f2; border-color: #f9e0cc; }
        .pt-pelat img {
            max-width: 76%; max-height: 72%; object-fit: contain; display: block;
        }

        /* --- Peringkat --- */
        /* Bagian ini sebuah TANGGA, dan angka 1 sampai 5 yang membuatnya
           terbaca begitu. Ditaruh di pojok pelat, bukan di atas kartu: ia
           keterangan produknya, bukan judul barisnya. */
        .pt-peringkat {
            position: absolute; top: 8px; left: 8px;
            display: inline-flex; align-items: center; gap: 4px;
            min-width: 24px; height: 24px; padding: 0 8px;
            border-radius: 999px; background: #fff; border: 1px solid #eceff3;
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            font-weight: 800; font-size: .78rem; color: #98a1b0;
            font-variant-numeric: tabular-nums;
        }
        .pt-peringkat.pt-juara {
            background: linear-gradient(135deg, #f26522, #fb8b3c);
            border-color: transparent; color: #fff;
            box-shadow: 0 4px 12px rgba(242, 101, 34, .3);
        }
        .pt-peringkat i.bi { font-size: .68rem; line-height: 1; }

        /* --- Nama --- */
        /* Keterangan produk DIBUANG dari kartu. Isinya kalimat pemasaran yang
           mirip satu sama lain, dan dua baris abu dikali lima kartu jadi
           sepuluh baris yang tidak membantu siapa pun memilih. Yang menentukan
           pilihan di sini: logonya, namanya, berapa kali dibeli, dan harganya. */
        .pt-nama {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 700; font-size: 1.02rem;
            color: #23272f; line-height: 1.35; margin: 0 0 10px; min-height: 2.7em;
            letter-spacing: -.015em;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* --- Bukti laku --- */
        /* Angka inilah SATU-SATUNYA alasan bagian ini bernama "terlaris".
           Sebagai baris abu kecil ia terbaca sebagai catatan kaki, dan judul
           bagiannya jadi klaim kosong. Sebagai chip, ia jadi bukti. */
        .pt-laku {
            display: inline-flex; align-items: center; gap: 6px; min-height: 26px;
            font-size: .76rem; font-weight: 600; color: #5b6472;
        }
        .pt-laku:not(:empty) {
            background: #f1f7f3; border: 1px solid #d8ebe0; border-radius: 999px;
            padding: 4px 11px;
        }
        .pt-laku b { color: #17803d; font-weight: 800; }
        .pt-laku i.bi { color: #17803d; font-size: .8rem; line-height: 1; }

        /* --- Harga --- */
        .pt-harga { border-top: 1px solid #f2f4f7; padding-top: 14px; margin-top: auto; }
        .pt-harga small { display: block; font-size: .74rem; color: #9ca3af; margin-bottom: 2px; }
        .pt-nominal { display: flex; align-items: baseline; gap: 5px; }
        .pt-harga b {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.32rem; color: #f26522; letter-spacing: -.03em;
            font-variant-numeric: lining-nums tabular-nums;
        }
        .pt-harga span { font-size: .78rem; color: #9ca3af; font-weight: 600; }

        /* Tautan teks, bukan tombol berbingkai. Lima tombol sekaligus dalam satu
           baris membuat semuanya terasa sama mendesak, padahal seluruh kartunya
           memang sudah bisa diklik. */
        .pt-tombol {
            display: inline-flex; align-items: center; gap: 7px; margin-top: 12px;
            color: #f26522; font-weight: 700; font-size: .84rem;
            transition: gap .2s ease;
        }
        .pt-kartu:hover .pt-tombol { color: #d9531a; gap: 11px; }
        .pt-tombol i.bi { line-height: 1; }
        .pt-tombol i.bi::before { display: block; line-height: 1; }

        @media (max-width: 1199.98px) { .pt-deret { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 767.98px) {
            .pt-head { flex-direction: column; align-items: flex-start; gap: 10px; }
            .pt-head h2 { font-size: 1.6rem; }
            .pt-deret { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .pt-kartu { padding: 18px 14px 16px; }
            .pt-nama { font-size: .94rem; min-height: 2.6em; }
            .pt-harga b { font-size: 1.18rem; }
            .pt-laku { font-size: .72rem; }
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
                    {{-- Logo duduk di atas PELAT bersama.

                         Gambar produk datang dengan bentuk dan warna latar yang
                         berbeda-beda; ditaruh langsung di atas kartu putih,
                         kelimanya melayang di ketinggian yang berbeda dan
                         barisnya terlihat goyah. Pelat memberi mereka satu
                         bidang yang sama. --}}
                    <div class="pt-pelat">
                        <img loading="lazy" alt=""
                             src="{{ $p->image
                                ? asset('storage/img/Product/'.$p->image)
                                : asset('niceshop/assets/img/product/scopus.png') }}">

                        {{-- Peringkat, bukan hanya juara satu.

                             Bagian ini sebuah TANGGA — angka 1 sampai 5 justru
                             yang membuatnya terbaca begitu, dan tangga jauh
                             lebih menarik disusuri daripada lima kartu setara.
                             Juara satu diberi warna merek, sisanya netral. --}}
                        <span class="pt-peringkat {{ $i === 0 ? 'pt-juara' : '' }}">
                            @if ($i === 0)<i class="bi bi-star-fill"></i>@endif{{ $i + 1 }}
                        </span>
                    </div>

                    <h3 class="pt-nama">{{ $p->nama_akun }}</h3>

                    {{-- Selalu dirender, meski kosong: ruangnya harus tetap ada
                         supaya kartu tanpa angka pesanan tidak naik sendiri.

                         Dijadikan chip, bukan baris teks kecil berwarna abu.
                         Angka inilah SATU-SATUNYA alasan bagian ini bernama
                         "terlaris" — menyembunyikannya sebagai catatan kaki
                         membuat judulnya jadi klaim kosong. --}}
                    <span class="pt-laku">
                        @if ($p->pesanan > 0)
                            <i class="bi bi-graph-up-arrow"></i> <b>{{ $p->pesanan }}×</b> dipesan
                        @endif
                    </span>

                    <div class="pt-harga">
                        <small>Mulai dari</small>
                        <span class="pt-nominal">
                            <b>Rp{{ number_format($h['nilai'], 0, ',', '.') }}</b>
                            <span>{{ $h['satuan'] }}</span>
                        </span>
                    </div>

                    <span class="pt-tombol">Lihat Produk <i class="bi bi-arrow-right"></i></span>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif
