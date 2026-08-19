<main class="main">
    @include('partials.media-produk-style')
    <style>
        /* Sengaja inline, bukan di public-custom-styles.css: berkas di
           public/build/ tidak ikut deploy sehingga gaya bisa tertinggal di
           server. Pola yang sama dipakai halaman detail produk satuan. */
        .pkd-isi { list-style:none; margin:0 0 22px; padding:0; display:grid; gap:10px; }
        .pkd-isi li { display:flex; align-items:center; justify-content:space-between; gap:12px;
            padding:11px 14px; border:1px solid rgba(0,0,0,.07); border-radius:12px;
            background:rgba(255,255,255,.55); }
        .pkd-isi-nama { display:flex; align-items:center; gap:9px; font-size:.94rem; min-width:0; }
        .pkd-isi-nama i { color:#16a34a; flex:0 0 auto; }
        .pkd-dur { flex:0 0 auto; font-size:.8rem; font-weight:600; padding:3px 10px;
            border-radius:999px; background:var(--ph-grad-soft, #eef2ff); }
        .pkd-rel-thumb { background:var(--ph-grad-soft); }
        .pkd-rel-thumb img { object-fit:contain !important; padding:12px; mix-blend-mode:multiply; }
    </style>

    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box2-heart"></i> Detail Paket</span>
                <h1>{{ $paket->nama_paket }}</h1>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li><a href="{{ route('bundling.product-bundlings') }}">Paket Bundling</a></li>
                    <li class="current">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="pd-section">
        <div class="container">
            <div class="row g-4 g-lg-5 pd-row">
                {{-- Media --}}
                <div class="col-lg-6 pd-col-media">
                    <div class="pd-media">
                        @if ($hp['potongan'] > 0)
                            <span class="pd-badge is-flash">
                                <i class="bi bi-lightning-charge-fill"></i>
                                Hemat Rp{{ number_format($hp['potongan'], 0, ',', '.') }}
                            </span>
                        @else
                            <span class="pd-badge">Paket Hemat</span>
                        @endif

                        @if ($paket->gambar)
                            <img src="{{ asset('storage/img/ProductBundlings/' . basename($paket->gambar)) }}"
                                alt="{{ $paket->nama_paket }}">
                        @else
                            <img src="{{ asset('storage/img/phoenix-mark.png') }}" alt="{{ $paket->nama_paket }}">
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="col-lg-6 pd-col-info">
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Paket Bundling</span>
                    <h2 class="pd-title">{{ $paket->nama_paket }}</h2>

                    <div class="pd-price">
                        <span class="pd-price-now">Rp {{ number_format($hp['bayar'], 0, ',', '.') }}</span>
                        {{-- Yang dicoret adalah harga awal, yaitu harga bila
                             produknya dibeli satuan (lihat App\Support\HargaPaket). --}}
                        @if ($hp['coret'] > $hp['bayar'])
                            <span class="pd-price-old">Rp {{ number_format($hp['coret'], 0, ',', '.') }}</span>
                        @endif
                        <span class="pd-price-unit">/ paket</span>
                        @if ($hp['potongan'] > 0)
                            <span class="pd-price-save">Hemat Rp {{ number_format($hp['potongan'], 0, ',', '.') }}</span>
                        @endif
                    </div>

                    @if ($hp['butuh_kode'])
                        <p class="pd-desc">Harga promo di atas aktif setelah kode promo dipakai di keranjang.</p>
                    @endif

                    @if ($isi)
                        <h4 class="pd-sub"><i class="bi bi-box-seam"></i> Termasuk dalam paket</h4>
                        <ul class="pkd-isi">
                            @foreach ($isi as $baris)
                                <li>
                                    <span class="pkd-isi-nama">
                                        <i class="bi bi-check-circle-fill"></i><span>{{ $baris['nama'] }}</span>
                                    </span>
                                    <span class="pkd-dur">{{ $baris['dur_value'] }} {{ $baris['dur_type'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="pd-buy">
                        <button type="button" class="pd-add" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart">
                            <span wire:loading.remove wire:target="addToCart"><i class="bi bi-cart-plus"></i> Masukkan Keranjang</span>
                            <span wire:loading wire:target="addToCart"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                        </button>
                    </div>

                    <div class="pd-features">
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Penggantian bila kendala</small>
                            </span>
                        </div>
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-whatsapp"></i></span>
                            <span class="pd-feature-txt">
                                <b>Dukungan Cepat</b>
                                <small>Bantuan &amp; respons via WhatsApp</small>
                            </span>
                        </div>
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-lock-fill"></i></span>
                            <span class="pd-feature-txt">
                                <b>Pembayaran Aman</b>
                                <small>Transfer Bank &amp; QRIS</small>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                @php $desk = \App\Support\DeskripsiProduk::pisah($paket->deskripsi); @endphp
                @if ($desk['paragraf'] || $desk['poin'] || $desk['ekstra'])
                    <div class="col-lg-6 pd-col-desc">
                        <div class="pd-desc-card">
                            <h3 class="pd-desc-head"><i class="bi bi-card-text"></i> Deskripsi Paket</h3>

                            @foreach ($desk['paragraf'] as $i => $par)
                                <p class="pd-desc {{ $i === 0 ? 'is-lead' : '' }}">{{ $par }}</p>
                            @endforeach

                            @if ($desk['poin'])
                                <ul class="pd-feat">
                                    @foreach ($desk['poin'] as $poin)
                                        <li style="--i: {{ $loop->index }}">
                                            <i class="bi bi-check-circle-fill"></i><span>{{ $poin }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($desk['ekstra'])
                                <div class="pd-desc-notes">
                                    @foreach ($desk['ekstra'] as $e)
                                        <p class="pd-desc-note"><span>{{ $e['ikon'] }}</span><span>{{ $e['teks'] }}</span></p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Paket lain --}}
            @if ($lainnya->isNotEmpty())
                <div class="row mt-5">
                    <div class="col-12">
                        <h3 class="pd-sub"><i class="bi bi-collection"></i> Paket Lainnya</h3>
                    </div>
                    @foreach ($lainnya as $item)
                        <div class="col-6 col-lg-3 mb-4">
                            @include('partials.kartu-paket', ['item' => $item])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>
