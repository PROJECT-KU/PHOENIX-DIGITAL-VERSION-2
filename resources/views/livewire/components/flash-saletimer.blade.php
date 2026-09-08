<div @if ($flashSale && !$showDurationModal) wire:poll.1s="updateTimer" @endif id="call-to-action"
    class="{{ $flashSale ? 'call-to-action section' : '' }}">
    @include('partials.media-produk-style')
    @if ($flashSale)
    <style>
        /* ===== Etalase Flash Sale =====
           Ditulis inline: public/build masuk .gitignore, jadi markup bisa sampai
           ke server tanpa CSS-nya.

           Kepalanya dipadatkan jadi satu pita. Sebelumnya lencana, judul, sub-judul,
           keterangan, penghitung mundur, dan tombol tersusun MENURUN dan semuanya
           di tengah — hampir 600 piksel sebelum satu produk pun terlihat. Kini
           keterangan promo di kiri, penghitung & tombol di kanan, dan produknya
           langsung menyusul. */
        #call-to-action.section { padding: 28px 0 32px; }

        #call-to-action .fsx-pita {
            display: flex; align-items: center; justify-content: space-between; gap: 22px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #fff6ec 0%, #fff 60%);
            border: 1px solid #fcd9b6; border-radius: 20px;
            padding: 18px 22px; margin-bottom: 18px;
        }
        #call-to-action .fsx-kiri { min-width: 0; }

        #call-to-action .fsx-lencana {
            display: inline-flex; align-items: center; gap: 7px;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            padding: 5px 12px; border-radius: 999px;
            box-shadow: 0 6px 14px -6px rgba(242, 101, 34, .85);
        }
        #call-to-action .fsx-lencana i.bi { font-size: .78rem; line-height: 1; }
        #call-to-action .fsx-lencana i.bi::before { display: block; line-height: 1; }

        #call-to-action .fsx-judul {
            font-family: 'Poppins', sans-serif; font-weight: 800; color: #23272f;
            font-size: 1.75rem; line-height: 1.15; margin: 10px 0 4px;
        }
        #call-to-action .fsx-hemat { margin: 0; font-size: .92rem; font-weight: 700; color: #f26522; }
        #call-to-action .fsx-hemat span { color: #6b7280; font-weight: 500; }

        #call-to-action .fsx-kanan { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        #call-to-action .fsx-hitung-label {
            display: block; font-size: .7rem; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #9ca3af; margin-bottom: 6px; text-align: center;
        }
        #call-to-action .fsx-kotak-deret { display: flex; gap: 8px; }
        #call-to-action .fsx-kotak {
            min-width: 52px; padding: 7px 8px; border-radius: 12px; text-align: center;
            background: #fff; border: 1px solid #f3e3d2;
        }
        #call-to-action .fsx-kotak b {
            display: block; font-family: 'Poppins', sans-serif; font-weight: 800;
            font-size: 1.22rem; color: #f26522; line-height: 1.1;
            /* Angkanya berganti tiap detik; lebar digit yang tetap membuat kotaknya
               tidak bergoyang mengikuti bentuk angka. */
            font-variant-numeric: tabular-nums;
        }
        #call-to-action .fsx-kotak span { font-size: .64rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #9ca3af; }

        #call-to-action .fsx-tombol {
            display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;
            background: linear-gradient(135deg, #fba919, #f26522); color: #fff;
            font-weight: 700; font-size: .92rem; padding: .72rem 1.35rem; border-radius: 12px;
            box-shadow: 0 10px 22px -10px rgba(242, 101, 34, .9); text-decoration: none;
        }
        #call-to-action .fsx-tombol:hover { color: #fff; filter: brightness(1.05); }
        #call-to-action .fsx-tombol i.bi { line-height: 1; }
        #call-to-action .fsx-tombol i.bi::before { display: block; line-height: 1; }

        /* Kartu produk dipusatkan. Dengan grid Bootstrap, dua kartu menempel ke
           kiri dan menyisakan separuh baris kosong — terlihat seperti ada yang
           gagal dimuat, bukan seperti promo yang memang hanya berisi dua produk. */
        .featured-products-row { display: flex; flex-wrap: wrap; justify-content: center; gap: 16px; margin: 0; }
        .featured-products-row > [class*="col-"] { flex: 0 1 264px; max-width: 264px; width: auto; padding: 0; }

        @media (max-width: 991.98px) {
            #call-to-action .fsx-pita { flex-direction: column; align-items: flex-start; }
            #call-to-action .fsx-kanan { width: 100%; justify-content: space-between; }
        }
        @media (max-width: 575.98px) {
            #call-to-action .fsx-judul { font-size: 1.4rem; }
            #call-to-action .fsx-kanan { gap: 12px; }
            #call-to-action .fsx-kotak { min-width: 46px; }
            #call-to-action .fsx-tombol { width: 100%; justify-content: center; }
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
                $hemat = $flashSale->tipe_diskon === 'persen'
                    ? 'Diskon sampai '.number_format($flashSale->diskon_member_persen, 0).'%'
                    : 'Hemat sampai Rp'.number_format($flashSale->diskon_member_nominal, 0, ',', '.');
            @endphp

            <div class="fsx-pita">
                <div class="fsx-kiri">
                    <span class="fsx-lencana"><i class="bi bi-lightning-charge-fill"></i> {{ $lencana }}</span>
                    <h2 class="fsx-judul">{{ $nama }}</h2>
                    <p class="fsx-hemat">{{ $hemat }}@if ($ket) <span>· {{ $ket }}</span>@endif</p>
                </div>

                <div class="fsx-kanan">
                    <div class="fsx-hitung">
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
                    </div>

                    <a href="{{ route('shop.index') }}" class="fsx-tombol">
                        Belanja Sekarang <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="row featured-products-row g-3 g-lg-4">
                @foreach ($featuredProducts as $product)
                    @php
                        $originalPrice = $product->harga_perbulan;
                        $discountedPrice = $this->getDiscountedPrice($originalPrice);
                        $best = $this->getBestDiscount();
                    @endphp
                    <div class="col-lg-3 col-md-6">
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
                                    <a href="{{ route('shop.detail-product', $product->id) }}" class="fs-btn-view">Lihat</a>
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
