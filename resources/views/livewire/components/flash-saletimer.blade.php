<div @if ($flashSale && !$showDurationModal) wire:poll.1s="updateTimer" @endif id="call-to-action"
    class="{{ $flashSale ? 'call-to-action section' : '' }}">
    @include('partials.media-produk-style')
    @if ($flashSale)
    <style>
        /* ===== Etalase Flash Sale =====
           Ditulis inline: public/build masuk .gitignore, jadi markup bisa sampai
           ke server tanpa CSS-nya.

           Promo dikembalikan ke ukuran pembuka halaman. Bentuk pita tipis
           sebelumnya memang rapi, tapi ia menyamarkan hal yang justru paling
           ingin ditonjolkan: promo berbatas waktu. Pita setinggi 90 piksel
           terbaca sebagai pengumuman, bukan sebagai kesempatan yang akan habis.

           Latarnya persik lembut, bukan jingga pekat: angka penghitung mundur
           dan harga coret perlu kontras tinggi untuk terbaca, dan keduanya
           hilang di atas jingga penuh. */
        #call-to-action.section { padding: 20px 0 28px; background: none; }

        /* Kepala bagian di dalam promo harus sama persis dengan kepala bagian
           lain di halaman. Aturan lama .call-to-action mewarnainya cokelat dan
           membesarkannya, sehingga "Rekomendasi Hari Ini" terlihat seperti
           berasal dari halaman yang berbeda. */
        #call-to-action .kb-kepala { text-align: left; }
        #call-to-action .kb-judul { color: #1c1f26; font-size: 2.1rem; text-align: left; }
        #call-to-action .kb-sub { color: #6b7280; text-align: left; }

        #call-to-action .fsx-hero {
            position: relative; overflow: hidden;
            display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.02fr);
            align-items: center; gap: 28px;
            background: linear-gradient(120deg, #fdf1e6 0%, #fde6d4 52%, #fdf3ea 100%);
            border: 1px solid #f8dfcb; border-radius: 26px;
            padding: 40px 44px;
        }

        /* Lingkaran samar di belakang — memberi kedalaman tanpa menambah
           gambar yang harus diunduh. */
        #call-to-action .fsx-hero::before {
            content: ""; position: absolute; right: -90px; top: -110px;
            width: 340px; height: 340px; border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.85), rgba(253,214,178,0));
            pointer-events: none;
        }

        #call-to-action .fsx-kiri { position: relative; z-index: 1; min-width: 0; }

        #call-to-action .fsx-lencana {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff;
            font-size: .76rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase;
            padding: 8px 16px; border-radius: 999px; margin-bottom: 16px;
            box-shadow: 0 8px 18px rgba(242, 101, 34, .3);
        }
        #call-to-action .fsx-lencana i.bi { font-size: .82rem; line-height: 1; animation: fsx-denyut 1.8s ease-in-out infinite; }
        #call-to-action .fsx-lencana i.bi::before { display: block; line-height: 1; }
        @keyframes fsx-denyut { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
        @media (prefers-reduced-motion: reduce) {
            #call-to-action .fsx-lencana i.bi { animation: none; }
        }

        #call-to-action .fsx-judul {
            font-family: 'Poppins', sans-serif; font-weight: 800; color: #1c1f26;
            font-size: 2.9rem; line-height: 1.06; letter-spacing: -.03em; margin: 0 0 6px;
        }
        #call-to-action .fsx-judul-panjang { font-size: 2rem; line-height: 1.14; }
        #call-to-action .fsx-hemat {
            font-family: 'Poppins', sans-serif; font-weight: 800; color: #1c1f26;
            font-size: 1.9rem; line-height: 1.15; letter-spacing: -.02em; margin: 0 0 14px;
        }
        #call-to-action .fsx-hemat b { color: #f26522; font-weight: 800; }

        #call-to-action .fsx-ket {
            margin: 0 0 24px; color: #6f7683; font-size: .98rem; line-height: 1.65; max-width: 46ch;
        }

        #call-to-action .fsx-tombol {
            display: inline-flex; align-items: center; gap: 10px;
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff;
            font-weight: 700; font-size: 1rem; text-decoration: none;
            padding: 15px 30px; border-radius: 14px;
            box-shadow: 0 14px 30px rgba(242, 101, 34, .3);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        #call-to-action .fsx-tombol:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(242, 101, 34, .38);
        }
        #call-to-action .fsx-tombol i.bi { line-height: 1; }
        #call-to-action .fsx-tombol i.bi::before { display: block; line-height: 1; }

        /* ----- Sisi kanan: penghitung mundur ----- */
        #call-to-action .fsx-kanan { position: relative; z-index: 1; min-width: 0; }

        #call-to-action .fsx-hitung-label {
            display: block; font-size: .76rem; font-weight: 800; letter-spacing: .14em;
            text-transform: uppercase; color: #9a8674; margin-bottom: 12px;
        }
        #call-to-action .fsx-kotak-deret { display: flex; gap: 12px; flex-wrap: wrap; }
        #call-to-action .fsx-kotak {
            flex: 0 1 92px; min-width: 76px; text-align: center;
            background: #fff; border: 1px solid #f6e2d1; border-radius: 16px;
            padding: 14px 8px 11px;
            box-shadow: 0 6px 16px rgba(184, 122, 74, .10);
        }
        #call-to-action .fsx-kotak b {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 800;
            font-size: 2rem; line-height: 1; color: #f26522; letter-spacing: -.02em;
            /* Angka detik berganti tiap detik. Tanpa lebar angka yang seragam,
               seluruh kotak ikut bergoyang tiap kali angkanya berubah. */
            font-variant-numeric: tabular-nums;
        }
        #call-to-action .fsx-kotak span {
            display: block; margin-top: 5px; font-size: .66rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase; color: #a8886c;
        }

        /* ----- Keterangan pelanggan ----- */
        #call-to-action .fsx-sosial {
            display: flex; align-items: center; gap: 13px; margin-top: 22px;
        }
        #call-to-action .fsx-sosial-ikon {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff; border: 1px solid #f6e2d1; color: #f26522; font-size: 1.15rem;
            box-shadow: 0 6px 16px rgba(184, 122, 74, .10);
        }
        #call-to-action .fsx-sosial-ikon i.bi { line-height: 1; }
        #call-to-action .fsx-sosial strong {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: .95rem; color: #1c1f26; line-height: 1.3;
        }
        #call-to-action .fsx-sosial span { display: block; font-size: .82rem; color: #8b7c6d; }

        /* ----- Dua kartu kecil ----- */
        #call-to-action .fsx-kartu-kecil {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px; margin-top: 18px;
        }
        #call-to-action .fsx-kk {
            display: flex; align-items: center; gap: 11px;
            background: #fff; border: 1px solid #f6e2d1; border-radius: 14px;
            padding: 13px 15px; text-decoration: none; min-width: 0;
            box-shadow: 0 6px 16px rgba(184, 122, 74, .09);
        }
        #call-to-action .fsx-kk strong {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 700;
            font-size: .85rem; color: #1c1f26; line-height: 1.3;
        }
        #call-to-action .fsx-kk > span > span { display: block; font-size: .75rem; color: #8b7c6d; line-height: 1.4; }
        #call-to-action .fsx-kk-ikon {
            flex: 0 0 auto; width: 32px; height: 32px; border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            background: #fff2ea; color: #f26522; font-size: .95rem;
        }
        #call-to-action .fsx-kk-ikon i.bi { line-height: 1; }
        #call-to-action .fsx-kk-aksi { justify-content: space-between; transition: border-color .2s ease, transform .2s ease; }
        #call-to-action .fsx-kk-aksi:hover { border-color: #f26522; transform: translateY(-2px); }
        #call-to-action .fsx-kk-panah {
            flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #f26522, #fb8b3c); color: #fff; font-size: .85rem;
        }
        #call-to-action .fsx-kk-panah i.bi { line-height: 1; }

        @media (max-width: 991.98px) {
            #call-to-action .fsx-hero { grid-template-columns: 1fr; gap: 26px; padding: 30px 26px; }
            #call-to-action .fsx-judul { font-size: 2.2rem; }
            #call-to-action .fsx-judul-panjang { font-size: 1.75rem; }
            #call-to-action .fsx-hemat { font-size: 1.5rem; }
            #call-to-action .kb-judul { font-size: 1.6rem; }
        }
        @media (max-width: 575.98px) {
            #call-to-action .fsx-hero { padding: 24px 20px; border-radius: 20px; }
            #call-to-action .fsx-judul { font-size: 1.8rem; }
            #call-to-action .fsx-judul-panjang { font-size: 1.5rem; }
            #call-to-action .fsx-hemat { font-size: 1.25rem; }
            #call-to-action .fsx-ket { font-size: .92rem; margin-bottom: 18px; }
            #call-to-action .fsx-tombol { width: 100%; justify-content: center; padding: 14px 22px; }
            #call-to-action .fsx-kotak-deret { gap: 8px; }
            #call-to-action .fsx-kotak { flex: 1 1 0; min-width: 0; padding: 11px 4px 9px; }
            #call-to-action .fsx-kotak b { font-size: 1.5rem; }
            #call-to-action .fsx-kartu-kecil { grid-template-columns: 1fr; }
        }

        /* ----- Kartu produk promo: melebar, gambar di samping -----
           Hanya untuk baris produk promo (.fsx-deret-produk); baris paket
           bundling di bawahnya tetap memakai kartu tegak yang lama.

           Promo ini biasanya hanya berisi dua sampai empat produk. Kartu tegak
           selebar 264px membuat dua produk tampak seperti sisa dari empat yang
           gagal dimuat. Kartu melebar mengisi barisnya dengan wajar, dan ruang
           tambahannya dipakai untuk sesuatu yang berguna: keterangan singkat
           produknya, yang di kartu tegak tidak muat sama sekali. */
        #call-to-action .fsx-deret-produk { justify-content: flex-start; gap: 20px; }
        #call-to-action .fsx-deret-produk > [class*="col-"] {
            flex: 1 1 420px; max-width: none; width: auto;
        }
        #call-to-action .fsx-deret-produk .fs-card {
            display: flex; flex-direction: row; align-items: stretch; height: 100%;
        }
        #call-to-action .fsx-deret-produk .fs-card-media {
            flex: 0 0 40%; max-width: 40%; min-height: 0;
        }
        #call-to-action .fsx-deret-produk .fs-card-media img {
            width: 100%; height: 100%; object-fit: contain; padding: 18px;
        }
        #call-to-action .fsx-deret-produk .fs-card-body {
            flex: 1 1 auto; min-width: 0;
            display: flex; flex-direction: column; justify-content: center; gap: 8px;
        }
        #call-to-action .fsx-ringkas {
            margin: 0; font-size: .84rem; line-height: 1.55; color: #7b8493;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        #call-to-action .fsx-deret-produk .fs-actions { margin-top: 4px; }

        @media (max-width: 575.98px) {
            /* Di ponsel kartu melebar kembali menumpuk: gambar 40% dari 340px
               tinggal 136px, dan logo produk di dalamnya tidak lagi terbaca. */
            #call-to-action .fsx-deret-produk .fs-card { flex-direction: column; }
            #call-to-action .fsx-deret-produk .fs-card-media { flex: 0 0 auto; max-width: none; }
            #call-to-action .fsx-deret-produk .fs-card-media img { height: auto; padding: 14px; }
        }

        /* Kartu produk dipusatkan. Dengan grid Bootstrap, dua kartu menempel ke
           kiri dan menyisakan separuh baris kosong — terlihat seperti ada yang
           gagal dimuat, bukan seperti promo yang memang hanya berisi dua produk. */
        .featured-products-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; margin: 0; }
        .featured-products-row > [class*="col-"] { flex: 0 1 264px; max-width: 264px; width: auto; padding: 0; }

        @media (max-width: 575.98px) {
            #call-to-action .featured-products-row > [class*="col-"] { flex: 1 1 100%; max-width: none; }
        }
    </style>
        <div class="container">
            @php
                // Promo sering diisi dengan teks yang SAMA di ketiga kolomnya —
                // di server, nama_promo, badge_text, dan deskripsi ketiganya
                // "Pay Day Sale". Dulu ketiganya dicetak apa adanya, jadi satu
                // nama yang sama muncul tiga kali berturut-turut dan memakan
                // separuh layar. Yang kembar disaring di sini.
                $nama = trim((string) $flashSale->nama_promo);
                $lencana = trim((string) $flashSale->badge_text);
                $lencana = ($lencana === '' || strcasecmp($lencana, $nama) === 0) ? 'Flash Sale' : $lencana;

                $ket = trim((string) $flashSale->deskripsi);
                if ($ket !== '' && (strcasecmp($ket, $nama) === 0 || strcasecmp($ket, $lencana) === 0)) {
                    $ket = '';
                }

                // Diskon NOMINAL adalah rupiah, bukan persen. Sebelumnya tanda %
                // ditempelkan ke angkanya juga, sehingga potongan Rp25.000 tampil
                // sebagai "Diskon hingga 25.000%" di beranda.
                // Angkanya diwarnai jingga, kalimatnya tidak: yang perlu
                // ditangkap dalam sekejap adalah BESAR potongannya, bukan kata
                // "hemat". Dirakit di sini, bukan di Blade, supaya angka yang
                // sudah diformat tidak perlu dipecah lagi belakangan.
                $hematHtml = $flashSale->tipe_diskon === 'persen'
                    ? 'Diskon <b>sampai '.e(number_format($flashSale->diskon_member_persen, 0)).'%</b>'
                    : 'Hemat <b>sampai Rp'.e(number_format($flashSale->diskon_member_nominal, 0, ',', '.')).'</b>';
            @endphp

            <div class="fsx-hero">
                <div class="fsx-kiri">
                    <span class="fsx-lencana"><i class="bi bi-lightning-charge-fill"></i> {{ $lencana }}</span>
                    <h2 class="fsx-judul {{ mb_strlen($nama) > 26 ? 'fsx-judul-panjang' : '' }}">{{ $nama }}</h2>
                    <p class="fsx-hemat">{!! $hematHtml !!}</p>
                    <p class="fsx-ket">{{ $ket ?: 'Dapatkan produk digital premium dengan harga lebih hemat. Promo terbatas, jangan sampai terlewat!' }}</p>

                    <a href="{{ route('shop.index') }}" class="fsx-tombol">
                        Belanja Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div class="fsx-kanan">
                    <span class="fsx-hitung-label">Berakhir dalam</span>
                    <div class="fsx-kotak-deret">
                        @foreach ([
                            ['days', 'Hari'], ['hours', 'Jam'], ['minutes', 'Menit'], ['seconds', 'Detik'],
                        ] as [$kunci, $label])
                            <div class="fsx-kotak">
                                <b>{{ str_pad($timeRemaining[$kunci] ?? 0, 2, '0', STR_PAD_LEFT) }}</b>
                                <span>{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tanpa foto wajah. Rancangannya memakai tumpukan avatar
                         pelanggan, tapi tidak ada satu pun foto pelanggan yang
                         boleh dipakai di sini — dan memasang wajah stok sebagai
                         "pelanggan kami" adalah kebohongan yang paling mudah
                         ketahuan. Angkanya saja sudah cukup. --}}
                    <div class="fsx-sosial">
                        <span class="fsx-sosial-ikon"><i class="bi bi-people-fill"></i></span>
                        <span>
                            <strong>5.000+ pelanggan</strong>
                            <span>telah bergabung bersama kami</span>
                        </span>
                    </div>

                    {{-- Dua kartu kecil penutup kolom kanan. Rancangannya menaruh
                         ilustrasi tas belanja di sini; gambarnya tidak ada, dan
                         menempelkan gambar stok yang bukan milik toko ini lebih
                         buruk daripada tidak ada gambar. Ruangnya dipakai untuk
                         dua hal yang justru dibaca orang. --}}
                    <div class="fsx-kartu-kecil">
                        <div class="fsx-kk">
                            <span class="fsx-kk-ikon"><i class="bi bi-patch-check-fill"></i></span>
                            <span>
                                <strong>Produk Original</strong>
                                <span>Lisensi resmi &amp; legal</span>
                            </span>
                        </div>
                        <a class="fsx-kk fsx-kk-aksi" href="{{ route('shop.index') }}">
                            <span>
                                <strong>Upgrade Produktivitasmu</strong>
                                <span>Lihat semua produk promo</span>
                            </span>
                            <span class="fsx-kk-panah"><i class="bi bi-arrow-up-right"></i></span>
                        </a>
                    </div>
                </div>
            </div>

            <x-kepala-bagian
                ikon="bi-fire"
                kicker="Produk Spesial"
                judul="Rekomendasi Hari Ini"
                sub="Produk pilihan dengan harga spesial, kualitas terbaik."
                :tautan-url="route('shop.index')"
                tautan-teks="Lihat Semua Produk" />

            <div class="row featured-products-row fsx-deret-produk g-3 g-lg-4">
                @foreach ($featuredProducts as $product)
                    @php
                        $originalPrice = $product->harga_perbulan;
                        $discountedPrice = $this->getDiscountedPrice($originalPrice);
                        $best = $this->getBestDiscount();
                    @endphp
                    <div class="col-lg-6">
                        <div class="fs-card">
                            <div class="fs-card-media">
                                @if ($product->image)
                                    <img src="{{ asset('storage/img/Product/' . $product->image) }}"
                                        alt="{{ $product->nama_akun }}">
                                @else
                                    <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                        alt="{{ $product->nama_akun }}">
                                @endif
                                @if ($best)
                                    <span class="fs-badge">Diskon s.d.
                                        @if ($best['isNominal'])
                                            Rp{{ number_format($best['value'], 0, ',', '.') }}
                                        @else
                                            {{ number_format($best['value'], 0) }}%
                                        @endif
                                    </span>
                                @endif
                            </div>
                            <div class="fs-card-body">
                                <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-name">{{ $product->nama_akun }}</a>
                                @if (filled($product->deskripsi))
                                    <p class="fsx-ringkas">{{ Str::limit(strip_tags($product->deskripsi), 110) }}</p>
                                @endif
                                <div class="fs-price">
                                    <span class="fs-price-sale">Rp{{ number_format($discountedPrice, 0, ',', '.') }}</span>
                                    @if ($discountedPrice < $originalPrice)
                                        <span class="fs-price-orig">Rp{{ number_format($originalPrice, 0, ',', '.') }}</span>
                                    @endif
                                    <small>/bln</small>
                                </div>
                                <div class="fs-actions">
                                    <button type="button" wire:click="openDuration('{{ $product->id }}')"
                                        wire:loading.attr="disabled" wire:target="openDuration('{{ $product->id }}')"
                                        class="fs-btn-cart">
                                        <span wire:loading.remove wire:target="openDuration('{{ $product->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                        <span wire:loading wire:target="openDuration('{{ $product->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                    <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-btn-view">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Paket bundling yang menumpang etalase promo ini.

                 Barisnya SENGAJA terpisah dari kartu produk di atas: kartu produk
                 membaca kolom khas Product dan menghitung diskon, sedangkan paket
                 punya kolomnya sendiri dan harganya sudah harga promo — tidak
                 didiskon lagi supaya tidak terpotong dua kali. --}}
            @if (count($featuredBundlings))
                <div class="row featured-products-row g-3 g-lg-4 mt-1">
                    @foreach ($featuredBundlings as $paket)
                        @php
                            // Satu sumber perhitungan untuk SEMUA halaman (beranda, daftar
                            // bundling, etalase ini) sekaligus cerminan PromoService —
                            // supaya angka di kartu tidak pernah berbeda dari yang ditagih
                            // di keranjang.
                            $hp = \App\Support\HargaPaket::untuk($paket);
                            $hargaPaket = $hp['bayar'];
                            $hargaAwal = $hp['coret'];
                        @endphp
                        <div class="col-lg-3 col-md-6" wire:key="fs-bundling-{{ $paket->id }}">
                            <div class="fs-card">
                                <div class="fs-card-media">
                                    @if ($paket->gambar)
                                        <img src="{{ asset('storage/img/ProductBundlings/' . $paket->gambar) }}"
                                            alt="{{ $paket->nama_paket }}" loading="lazy">
                                    @endif
                                    <span class="fs-badge">
                                        {{ $hp['potongan'] > 0 ? 'Hemat Rp' . number_format($hp['potongan'], 0, ',', '.') : 'Paket Spesial' }}
                                    </span>
                                </div>
                                <div class="fs-card-body">
                                    <a href="{{ route('bundling.index') }}" class="fs-name">{{ $paket->nama_paket }}</a>
                                    <div class="fs-price">
                                        <span class="fs-price-sale">Rp{{ number_format($hargaPaket, 0, ',', '.') }}</span>
                                        @if ($hargaAwal > $hargaPaket)
                                            <span class="fs-price-orig">Rp{{ number_format($hargaAwal, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                    <div class="fs-actions">
                                        <button type="button" wire:click="tambahPaket('{{ $paket->id }}')"
                                            wire:loading.attr="disabled" wire:target="tambahPaket('{{ $paket->id }}')"
                                            class="fs-btn-cart">
                                            <span wire:loading.remove wire:target="tambahPaket('{{ $paket->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                            <span wire:loading wire:target="tambahPaket('{{ $paket->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                        </button>
                                        <a href="{{ route('bundling.detail', $paket->id) }}" class="fs-btn-view">Lihat</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ===== Modal Pilih Durasi ===== --}}
    @if ($showDurationModal)
        <div class="fs-modal-overlay" wire:key="fs-dur-modal" wire:click.self="closeDuration">
            <div class="fs-modal">
                <button type="button" class="fs-modal-close" wire:click="closeDuration" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

                <div class="fs-modal-head">
                    <div class="fs-modal-thumb">
                        @if ($pickProductImage)
                            <img src="{{ asset('storage/img/Product/' . $pickProductImage) }}" alt="{{ $pickProductName }}">
                        @else
                            <i class="bi bi-box-seam"></i>
                        @endif
                    </div>
                    <div class="fs-modal-title">
                        <span class="fs-modal-eyebrow"><i class="bi bi-lightning-charge-fill"></i> Flash Sale</span>
                        <h4>{{ $pickProductName }}</h4>
                        <p>Pilih durasi langganan</p>
                    </div>
                </div>

                <div class="fs-modal-options">
                    @foreach ($pickPackages as $p)
                        @php $active = ($pickType === $p['duration_type'] && (int) $pickValue === (int) $p['duration_value']); @endphp
                        <button type="button" class="fs-opt {{ $active ? 'is-active' : '' }}"
                            wire:click="selectPackage('{{ $p['duration_type'] }}', {{ $p['duration_value'] }})">
                            <span class="fs-opt-radio"></span>
                            <span class="fs-opt-info">
                                <span class="fs-opt-label">{{ $p['label'] }}</span>
                                @if (!empty($p['savings']) && $p['savings'] > 0)
                                    <span class="fs-opt-save">Hemat Rp{{ number_format($p['savings'], 0, ',', '.') }}</span>
                                @endif
                            </span>
                            <span class="fs-opt-price">
                                @if (($p['discounted'] ?? $p['price']) < $p['price'])
                                    <span class="fs-opt-orig">Rp{{ number_format($p['price'], 0, ',', '.') }}</span>
                                @endif
                                <span class="fs-opt-now">Rp{{ number_format($p['discounted'] ?? $p['price'], 0, ',', '.') }}</span>
                            </span>
                        </button>
                    @endforeach

                    {{-- Durasi custom (bila produk punya harga per bulan) --}}
                    @if ($pickPerBulan > 0)
                        @php
                            $cp = $this->customPricing();
                            $customBase = $cp['base'];
                            $customDisc = $cp['discounted'];
                            $customSave = $cp['savings'];
                        @endphp
                        <div class="fs-opt fs-opt-custom {{ $pickIsCustom ? 'is-active' : '' }}">
                            <span class="fs-opt-radio" wire:click="chooseCustom"></span>
                            <span class="fs-opt-info" wire:click="chooseCustom">
                                <span class="fs-opt-label">Durasi lain</span>
                                <span class="fs-opt-sub">
                                    @if ($cp['matched'])
                                        Sesuai paket {{ $pickCustomMonths }} bulan
                                    @else
                                        Rp{{ number_format($pickPerBulan, 0, ',', '.') }}/bulan
                                    @endif
                                </span>
                            </span>
                            <div class="fs-stepper">
                                <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                <span class="fs-stepper-val">{{ $pickCustomMonths }} bln</span>
                                <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                            </div>
                        </div>
                        @if ($pickIsCustom)
                            <div class="fs-custom-total">
                                <span class="fs-custom-total-left">
                                    Total {{ $pickCustomMonths }} bulan
                                    @if ($customSave > 0)
                                        <span class="fs-opt-save">Hemat Rp{{ number_format($customSave, 0, ',', '.') }}</span>
                                    @endif
                                </span>
                                <span class="fs-custom-total-price">
                                    @if ($customDisc < $customBase)
                                        <span class="fs-opt-orig">Rp{{ number_format($customBase, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="fs-opt-now">Rp{{ number_format($customDisc, 0, ',', '.') }}</span>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>

                <button type="button" class="fs-modal-add" wire:click="confirmAddToCart"
                    wire:loading.attr="disabled" wire:target="confirmAddToCart">
                    <span wire:loading.remove wire:target="confirmAddToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                    <span wire:loading wire:target="confirmAddToCart"><span class="spinner-border spinner-border-sm"></span> Memproses…</span>
                </button>
            </div>
        </div>
    @endif

</div>
