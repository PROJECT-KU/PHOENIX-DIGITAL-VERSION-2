{{-- Produk terlaris, dihitung dari pesanan yang benar-benar dibayar.

     Dulu bagian ini empat kartu yang DITULIS TETAP di sini: nama, gambar, dan
     keterangannya dipatok, semuanya menaut ke /shop dan bukan ke produknya.
     Daftar itu tidak pernah berubah meski yang laku sudah berganti
     berbulan-bulan — dan tidak ada yang tahu ia bohong, karena tampilannya
     tetap meyakinkan.

     Gaya ditulis inline, bukan di stylesheet Vite: public/build masuk
     .gitignore, jadi markup bisa sampai ke server (git pull) tanpa CSS-nya. --}}
@php
    $juara = $terlaris->first();
    // values() wajib: slice() MEMPERTAHANKAN kunci aslinya, sehingga $i pada
    // perulangan di bawah mulai dari 1 dan nomor peringkatnya melompat jadi
    // #3, #4 — salah yang tidak memunculkan galat apa pun, hanya angka keliru
    // yang terlihat masuk akal.
    $lainnya = $terlaris->slice(1)->take(4)->values();

    // Harga ditampilkan dengan satuan yang benar. Produk jasa harga per
    // bulannya 0; tanpa pembedaan ini semuanya tampil "Rp 0/bln".
    $hargaProduk = function ($p) {
        $isJasa = (bool) $p->butuh_file;
        $perHalaman = $isJasa && $p->jasaPerHalaman();

        return [
            'nilai' => $perHalaman
                ? (int) $p->hargaPerHalaman()
                : ($isJasa ? (int) ($p->hargaSekali() ?? 0) : (int) $p->harga_perbulan),
            'satuan' => $perHalaman ? '/halaman' : ($isJasa ? '/cek' : '/bln'),
        ];
    };
@endphp

@if ($juara)
<section id="promo-cards" class="promo-cards section">
    <style>
        /* ===== Produk Terlaris ===== */
        .pt-grid { display: grid; grid-template-columns: 1.05fr 1fr; gap: 22px; align-items: stretch; }
        /* Dua baris sama tinggi: tanpa ini tiap baris setinggi isinya sendiri,
           dan kolom kanan berakhir lebih pendek daripada kartu juara di
           sebelahnya — dua sisi yang tidak rata bawahnya. */
        .pt-kecil { display: grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: 1fr 1fr; gap: 16px; }

        .pt-kartu {
            position: relative; display: flex; flex-direction: column;
            background: #fff; border: 1px solid #f1e6d8; border-radius: 20px; overflow: hidden;
            text-decoration: none; color: inherit;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .pt-kartu:hover {
            transform: translateY(-4px); border-color: #fcd9b6; color: inherit;
            box-shadow: 0 16px 34px -18px rgba(242, 101, 34, .55);
        }

        /* Latar logo disamakan dengan kartu produk di Shop supaya beranda dan
           katalog terasa satu toko, bukan dua situs berbeda. */
        .pt-media {
            position: relative; display: flex; align-items: center; justify-content: center;
            background: radial-gradient(120% 120% at 30% 18%, #fffaf3 0%, #fff4e7 48%, #ffeeda 100%);
        }
        .pt-media img { position: relative; z-index: 1; max-width: 100%; max-height: 100%; object-fit: contain; }

        /* ===== Kartu juara ===== */
        .pt-juara { padding: 0; }
        .pt-juara .pt-media { min-height: 300px; padding: 30px; }
        .pt-juara .pt-media img { max-height: 260px; }
        .pt-juara-isi { padding: 22px 24px 24px; display: flex; flex-direction: column; gap: 10px; flex: 1 1 auto; }
        .pt-juara-isi h3 { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 1.4rem; color: #23272f; margin: 0; line-height: 1.25; }
        .pt-juara-isi p { color: #6b7280; font-size: .9rem; line-height: 1.65; margin: 0; }

        /* ===== Kartu pengiring ===== */
        .pt-kecil .pt-media { flex: 1 1 auto; min-height: 132px; padding: 18px; }
        .pt-kecil .pt-media img { max-height: 96px; }
        .pt-kecil-isi { padding: 13px 15px 15px; display: flex; flex-direction: column; gap: 6px; flex: 1 1 auto; }
        .pt-kecil-isi h4 {
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: .93rem; color: #23272f;
            margin: 0; line-height: 1.35;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }

        /* ===== Peringkat, harga, jumlah pesanan ===== */
        .pt-peringkat {
            position: absolute; top: 12px; left: 12px; z-index: 2;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 11px; border-radius: 999px;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-size: .7rem; font-weight: 800; letter-spacing: .03em;
            box-shadow: 0 6px 14px -6px rgba(242, 101, 34, .8);
        }
        .pt-peringkat i.bi { font-size: .78rem; line-height: 1; }
        .pt-peringkat i.bi::before { display: block; line-height: 1; }
        .pt-peringkat.pt-sunyi { background: #fff; color: #b45309; border: 1px solid #fcd9b6; box-shadow: none; }

        .pt-harga { display: flex; align-items: baseline; gap: 3px; margin-top: auto; }
        .pt-harga b { font-family: 'Poppins', sans-serif; font-weight: 800; color: #f26522; }
        .pt-juara .pt-harga b { font-size: 1.3rem; }
        .pt-kecil .pt-harga b { font-size: 1rem; }
        .pt-harga span { font-size: .76rem; color: #9ca3af; font-weight: 600; }

        .pt-laku { display: inline-flex; align-items: center; gap: 5px; font-size: .76rem; color: #6b7280; font-weight: 600; }
        .pt-laku i.bi { color: #f26522; font-size: .8rem; line-height: 1; }
        .pt-laku i.bi::before { display: block; line-height: 1; }

        .pt-cta {
            display: inline-flex; align-items: center; gap: 8px; align-self: flex-start;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-weight: 700; font-size: .88rem; padding: .62rem 1.25rem; border-radius: 12px;
            box-shadow: 0 8px 20px -8px rgba(242, 101, 34, .8);
        }
        .pt-kartu:hover .pt-cta { filter: brightness(1.05); }

        @media (max-width: 991.98px) {
            .pt-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 575.98px) {
            .pt-kecil { gap: 12px; }
            .pt-juara .pt-media { min-height: 210px; padding: 20px; }
            .pt-juara .pt-media img { max-height: 170px; }
            .pt-juara-isi { padding: 18px; }
            .pt-juara-isi h3 { font-size: 1.15rem; }
        }
    </style>

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="ph-sec-head">
            <span class="ph-sec-eyebrow"><i class="bi bi-fire"></i> Paling Dicari</span>
            <h2 class="ph-sec-title">Produk Terlaris</h2>
            <p class="ph-sec-sub">
                Dihitung dari pesanan pelanggan {{ \App\Support\ProdukTerlaris::JENDELA_HARI }} hari terakhir —
                daftarnya berubah sendiri mengikuti yang paling banyak dipesan.
            </p>
        </div>

        <div class="pt-grid">
            {{-- Juara: diberi ruang paling besar. Ia yang paling banyak dipesan,
                 jadi ia pula yang paling besar peluangnya dipesan lagi. --}}
            @php $h = $hargaProduk($juara); @endphp
            <a class="pt-kartu pt-juara" href="{{ route('shop.detail-product', $juara->id) }}"
                data-aos="fade-right" data-aos-delay="200">
                <span class="pt-peringkat"><i class="bi bi-trophy-fill"></i> Terlaris #1</span>
                <div class="pt-media">
                    @if ($juara->image)
                        <img loading="lazy" src="{{ asset('storage/img/Product/'.$juara->image) }}" alt="{{ $juara->nama_akun }}">
                    @else
                        <img loading="lazy" src="{{ asset('niceshop/assets/img/product/scopus.png') }}" alt="{{ $juara->nama_akun }}">
                    @endif
                </div>
                <div class="pt-juara-isi">
                    <h3>{{ $juara->nama_akun }}</h3>
                    @if ($juara->deskripsi)
                        <p>{{ \Illuminate\Support\Str::limit(strip_tags($juara->deskripsi), 165) }}</p>
                    @endif
                    @if ($juara->pesanan > 0)
                        <span class="pt-laku"><i class="bi bi-bag-check-fill"></i> {{ $juara->pesanan }}× dipesan</span>
                    @endif
                    <div class="pt-harga">
                        <b>Rp{{ number_format($h['nilai'], 0, ',', '.') }}</b><span>{{ $h['satuan'] }}</span>
                    </div>
                    <span class="pt-cta">Lihat Produk <i class="bi bi-arrow-right"></i></span>
                </div>
            </a>

            <div class="pt-kecil">
                @foreach ($lainnya as $i => $p)
                    @php $hk = $hargaProduk($p); @endphp
                    <a class="pt-kartu" href="{{ route('shop.detail-product', $p->id) }}"
                        wire:key="terlaris-{{ $p->id }}" data-aos="fade-up" data-aos-delay="{{ 300 + $i * 100 }}">
                        <span class="pt-peringkat pt-sunyi">#{{ $i + 2 }}</span>
                        <div class="pt-media">
                            @if ($p->image)
                                <img loading="lazy" src="{{ asset('storage/img/Product/'.$p->image) }}" alt="{{ $p->nama_akun }}">
                            @else
                                <img loading="lazy" src="{{ asset('niceshop/assets/img/product/scopus.png') }}" alt="{{ $p->nama_akun }}">
                            @endif
                        </div>
                        <div class="pt-kecil-isi">
                            <h4>{{ $p->nama_akun }}</h4>
                            @if ($p->pesanan > 0)
                                <span class="pt-laku"><i class="bi bi-bag-check-fill"></i> {{ $p->pesanan }}× dipesan</span>
                            @endif
                            <div class="pt-harga">
                                <b>Rp{{ number_format($hk['nilai'], 0, ',', '.') }}</b><span>{{ $hk['satuan'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
