<main class="main">
    @php
        $best = $this->bestDiscount;
        $isFlash = $best && ($best['promo']->tipe_promo ?? null) === 'flash_sale';
        $selOrig = $this->selectedHarga();
        $selDisc = $this->applyDiscount($selOrig);
        $selSave = max(0, $selOrig - $selDisc);
    @endphp

    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-box-seam"></i> Detail Produk</span>
                <h1>{{ $product->nama_akun }}</h1>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('homepage') }}">Beranda</a></li>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Detail</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="pd-section">
        <div class="container">
            <div class="row g-4 g-lg-5">
                {{-- Media --}}
                <div class="col-lg-6">
                    <div class="pd-media">
                        @if ($best)
                            <span class="pd-badge {{ $isFlash ? 'is-flash' : '' }}">
                                @if ($isFlash)<i class="bi bi-lightning-charge-fill"></i> @endif
                                @if ($best['type'] === 'persen')
                                    {{ $isFlash ? 'Diskon s.d.' : 'Diskon' }} {{ number_format($best['value'], 0) }}%
                                @else
                                    {{ $isFlash ? 'Diskon s.d.' : 'Diskon' }} Rp{{ number_format($best['value'], 0, ',', '.') }}
                                @endif
                            </span>
                        @endif
                        @if ($product->image)
                            <img src="{{ asset('storage/img/Product/' . $product->image) }}" alt="{{ $product->nama_akun }}">
                        @else
                            <img src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                alt="{{ $product->nama_akun }}">
                        @endif
                    </div>

                    {{-- Info kepercayaan (di bawah gambar) --}}
                    <div class="pd-features">
                        <div class="pd-feature">
                            <span class="pd-feature-ic"><i class="bi bi-shield-check"></i></span>
                            <span class="pd-feature-txt">
                                <b>Bergaransi</b>
                                <small>Selama masa aktif paket</small>
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

                {{-- Info --}}
                <div class="col-lg-6">
                    <span class="ph-sec-eyebrow"><i class="bi bi-stars"></i> Akun Premium</span>
                    <h2 class="pd-title">{{ $product->nama_akun }}</h2>

                    <div class="pd-price">
                        <span class="pd-price-now">Rp {{ number_format($selDisc, 0, ',', '.') }}</span>
                        @if ($selDisc < $selOrig)
                            <span class="pd-price-old">Rp {{ number_format($selOrig, 0, ',', '.') }}</span>
                        @endif
                        <span class="pd-price-unit">/ {{ $durationValue }} {{ ucfirst($durationType) }}</span>
                        @if ($selSave > 0)
                            <span class="pd-price-save">Hemat Rp {{ number_format($selSave, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    @if (trim((string) $product->deskripsi) !== '')
                        <p class="pd-desc">{{ $product->deskripsi }}</p>
                    @endif

                    {{-- Paket --}}
                    <div class="pd-packages">
                        <h4 class="pd-sub"><i class="bi bi-calendar2-week"></i> Pilih Paket</h4>
                        <div class="pd-pkg-grid">
                            @foreach ($product->daftarHarga() as $pkg)
                                @php
                                    $isActive = ($durationType === $pkg['durasi_type'] && (int) $durationValue === (int) $pkg['durasi_value']);
                                    $pOrig = (int) $pkg['harga'];
                                    $pDisc = $this->applyDiscount($pOrig);
                                @endphp
                                <button type="button"
                                    class="pd-pkg {{ $isActive ? 'is-active' : '' }}"
                                    wire:click="selectPackage('{{ $pkg['durasi_type'] }}', {{ $pkg['durasi_value'] }})">
                                    <span class="pd-pkg-dur">{{ $pkg['durasi_value'] }} {{ ucfirst($pkg['durasi_type']) }}</span>
                                    <span class="pd-pkg-price">
                                        @if ($pDisc < $pOrig)
                                            <span class="pd-pkg-old">Rp {{ number_format($pOrig, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="pd-pkg-now">Rp {{ number_format($pDisc, 0, ',', '.') }}</span>
                                    </span>
                                    @if ($pOrig - $pDisc > 0)
                                        <span class="pd-pkg-save">Hemat Rp {{ number_format($pOrig - $pDisc, 0, ',', '.') }}</span>
                                    @endif
                                    <i class="bi bi-check-circle-fill pd-pkg-check"></i>
                                </button>
                            @endforeach

                            {{-- Durasi custom (seperti flash sale) --}}
                            @if ((int) ($product->harga_perbulan ?? 0) > 0)
                                @php $cp = $this->customPricing(); @endphp
                                <div class="pd-pkg pd-pkg-custom {{ $isCustom ? 'is-active' : '' }}">
                                    <button type="button" class="pd-pkg-custom-head" wire:click="chooseCustom">
                                        <span class="pd-pkg-dur">Durasi lain</span>
                                        <span class="pd-pkg-sub">
                                            @if ($cp['matched'])
                                                Sesuai paket {{ $pickCustomMonths }} bulan
                                            @else
                                                Rp {{ number_format($product->harga_perbulan, 0, ',', '.') }}/bulan
                                            @endif
                                        </span>
                                    </button>
                                    <div class="pd-stepper">
                                        <button type="button" wire:click="decCustom" @disabled($pickCustomMonths <= 1)>−</button>
                                        <span class="pd-stepper-val">{{ $pickCustomMonths }} bln</span>
                                        <button type="button" wire:click="incCustom" @disabled($pickCustomMonths >= 60)>+</button>
                                    </div>
                                    @if ($isCustom)
                                        <div class="pd-pkg-custom-total">
                                            <span class="pd-pkg-custom-label">Total {{ $pickCustomMonths }} bulan</span>
                                            @if ($cp['discounted'] < $cp['base'])
                                                <span class="pd-pkg-old">Rp {{ number_format($cp['base'], 0, ',', '.') }}</span>
                                            @endif
                                            <span class="pd-pkg-now">Rp {{ number_format($cp['discounted'], 0, ',', '.') }}</span>
                                            @if ($cp['savings'] > 0)
                                                <span class="pd-pkg-save">Hemat Rp {{ number_format($cp['savings'], 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    <i class="bi bi-check-circle-fill pd-pkg-check"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Beli --}}
                    <div class="pd-buy">
                        <button type="button" class="pd-add" wire:click="addToCart"
                            wire:loading.attr="disabled" wire:target="addToCart">
                            <span wire:loading.remove wire:target="addToCart"><i class="bi bi-cart-plus"></i> Tambah ke Keranjang</span>
                            <span wire:loading wire:target="addToCart"><span class="spinner-border spinner-border-sm"></span> Memproses...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </section>
</main>
