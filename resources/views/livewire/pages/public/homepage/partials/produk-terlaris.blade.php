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

        .pt-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 26px; }
        .pt-head h2 {
            font-family: 'Poppins', sans-serif; font-weight: 800; color: #23272f;
            font-size: 2.1rem; margin: 0 0 6px; line-height: 1.15;
        }
        .pt-head p { color: #6b7280; font-size: .95rem; margin: 0; line-height: 1.6; }
        .pt-semua {
            display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto;
            color: #f26522; font-weight: 700; font-size: .95rem; text-decoration: none; white-space: nowrap;
        }
        .pt-semua:hover { color: #d9531a; }
        .pt-semua i.bi { line-height: 1; }
        .pt-semua i.bi::before { display: block; line-height: 1; }

        /* Lima kartu sejajar. Grid, bukan flex: tiap kolom lebarnya sama persis
           berapa pun panjang nama produknya, jadi barisnya tidak pernah pincang. */
        .pt-deret { display: grid; grid-template-columns: repeat(5, 1fr); gap: 18px; }

        .pt-kartu {
            display: flex; flex-direction: column; text-align: center;
            background: #fff; border: 1px solid #eef1f5; border-radius: 18px;
            padding: 22px 18px 20px; position: relative;
            text-decoration: none; color: inherit;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .pt-kartu:hover {
            transform: translateY(-4px); color: inherit; border-color: #fcd9b6;
            box-shadow: 0 18px 34px -20px rgba(242, 101, 34, .6);
        }

        .pt-lencana {
            position: absolute; top: 14px; left: 14px;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 10px; border-radius: 999px;
            background: #fff3e6; color: #d9531a;
            font-size: .66rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        }
        .pt-lencana i.bi { font-size: .7rem; line-height: 1; }
        .pt-lencana i.bi::before { display: block; line-height: 1; }

        .pt-logo { height: 96px; display: flex; align-items: center; justify-content: center; margin: 12px 0 14px; }
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

        .pt-harga { border-top: 1px solid #f1f3f6; padding-top: 14px; margin-top: auto; text-align: left; }
        .pt-harga small { display: block; font-size: .76rem; color: #9ca3af; margin-bottom: 2px; }
        .pt-harga b { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.28rem; color: #f26522; }
        .pt-harga span { font-size: .8rem; color: #9ca3af; font-weight: 600; }

        .pt-tombol {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 14px; padding: .62rem 1rem; border-radius: 11px;
            border: 1px solid #f7c9a3; color: #f26522; font-weight: 700; font-size: .88rem;
            transition: background .18s ease, color .18s ease, border-color .18s ease;
        }
        .pt-kartu:hover .pt-tombol { background: linear-gradient(135deg, #fba919, #f26522); border-color: transparent; color: #fff; }
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
        <div class="pt-head">
            <div>
                <h2>Produk Terlaris</h2>
                <p>Tools pilihan untuk riset, skripsi &amp; produktivitas yang paling banyak dipesan pelanggan.</p>
            </div>
            <a href="{{ route('shop.index') }}" class="pt-semua">Lihat Semua Produk <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="pt-deret">
            @foreach ($terlaris as $i => $p)
                @php $h = $hargaProduk($p); @endphp
                <a class="pt-kartu" href="{{ route('shop.detail-product', $p->id) }}" wire:key="terlaris-{{ $p->id }}">
                    @if ($i === 0)
                        <span class="pt-lencana"><i class="bi bi-star-fill"></i> Best Seller</span>
                    @endif

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
