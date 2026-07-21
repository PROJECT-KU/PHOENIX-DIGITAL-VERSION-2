<section id="best-sellers" @class(['bd-section section' => $bundlings->isNotEmpty()]) @if ($bundlings->isEmpty()) style="display:none" @endif>
    @include('partials.bundling-deskripsi-style')
    @if ($bundlings->isNotEmpty())
    <div class="container">
        <div class="ph-sec-head">
            <span class="ph-sec-eyebrow"><i class="bi bi-box2-heart-fill"></i> Hemat Lebih</span>
            <h2 class="ph-sec-title">Paket Bundling</h2>
            <p class="ph-sec-sub">Gabungan beberapa akun premium dalam satu paket — lebih lengkap &amp; lebih hemat.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse ($bundlings as $item)
                @php
                    $hAwal = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_awal);
                    $hBundle = (int) preg_replace('/[^0-9]/', '', (string) $item->harga_bundling);
                @endphp
                <div class="col-lg-4 col-md-6" wire:key="bundling-{{ $item->id }}">
                    <div class="bd-card">
                        <button type="button" class="bd-card-media" wire:click="openDetail('{{ $item->id }}')">
                            <img src="{{ asset('storage/img/ProductBundlings/' . $item->gambar) }}"
                                alt="{{ $item->nama_paket }}" loading="lazy">
                            <span class="bd-badge"><i class="bi bi-stars"></i> Bundling</span>
                        </button>

                        <div class="bd-card-body">
                            <div class="bd-head">
                                <h3 class="bd-name">{{ $item->nama_paket }}</h3>
                                {{-- Kartu cukup teaser singkat; rincian lengkap ada di
                                     daftar "Termasuk dalam paket" & modal detail. --}}
                                @php $__t = \App\Support\DeskripsiProduk::pisah($item->deskripsi); @endphp
                                @php $__teaser = $__t['paragraf'][0] ?? ($__t['poin'][0] ?? ($__t['ekstra'][0]['teks'] ?? '')); @endphp
                                @if ($__teaser !== '')
                                    <p class="bd-card-desc bdesk-teaser">{{ $__teaser }}</p>
                                @endif
                            </div>

                            <div class="bd-price">
                                @if ($hAwal > 0 && $hAwal > $hBundle)
                                    <span class="bd-price-old">{{ $item->harga_awal }}</span>
                                @endif
                                <span class="bd-price-now">{{ $item->harga_bundling }}</span>
                                <span class="bd-price-unit">/ paket</span>
                            </div>

                            @php $durs = $item->durations ?? []; @endphp
                            <div class="bd-incl-box">
                                <div class="bd-incl-title"><i class="bi bi-box-seam"></i> Termasuk dalam paket</div>
                                <ul class="bd-includes">
                                    @foreach ([1, 2, 3, 4, 5] as $i)
                                        @php $product = $item->{'product' . $i}; $dur = $durs['product_' . $i] ?? null; @endphp
                                        @if ($product)
                                            <li>
                                                <span class="bd-incl-name"><i class="bi bi-check-circle-fill"></i> {{ $product->nama_akun }}</span>
                                                <span class="bd-dur">{{ (int) ($dur['value'] ?? 1) }} {{ ucfirst($dur['type'] ?? 'bulan') }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            <div class="bd-card-bottom">
                                <div class="bd-actions">
                                    <button type="button" class="bd-add" wire:click="addToCart('{{ $item->id }}')"
                                        wire:loading.attr="disabled" wire:target="addToCart('{{ $item->id }}')">
                                        <span wire:loading.remove wire:target="addToCart('{{ $item->id }}')"><i class="bi bi-cart-plus"></i> Keranjang</span>
                                        <span wire:loading wire:target="addToCart('{{ $item->id }}')"><span class="spinner-border spinner-border-sm"></span></span>
                                    </button>
                                    <button type="button" class="bd-view" wire:click="openDetail('{{ $item->id }}')">Lihat</button>
                                </div>

                                <p class="bd-card-note">🎉 <b>Jangan lewatkan kesempatan terbatas ini!</b> Promo bisa berakhir kapan saja.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="bd-empty"><i class="bi bi-box-seam"></i> Belum ada paket bundling saat ini.</div>
                </div>
            @endforelse
        </div>

        @if ($bundlings->isNotEmpty())
            <div class="text-center mt-4">
                <a href="{{ route('bundling.product-bundlings') }}" class="ph-btn-ghost">Lihat Semua Paket <i class="bi bi-arrow-right"></i></a>
            </div>
        @endif
    </div>

    {{-- ===== Modal Detail Bundling (isi sama seperti card /bundling/product) ===== --}}
    @if ($showBundleDetail && $detailBundle)
        @php
            $dAwal = (int) preg_replace('/[^0-9]/', '', (string) $detailBundle['harga_awal']);
            $dBundle = (int) preg_replace('/[^0-9]/', '', (string) $detailBundle['harga_bundling']);
        @endphp
        <div class="fs-modal-overlay" wire:key="bd-detail-modal" wire:click.self="closeDetail">
            <div class="fs-modal bd-detail">
                <button type="button" class="fs-modal-close" wire:click="closeDetail" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>

                <div class="bdl-card bdl-card--modal">
                    <div class="bd-modal-eyebrow"><i class="bi bi-box2-heart-fill"></i> Paket Bundling</div>
                    <h2 class="bdl-title">{{ $detailBundle['nama'] }}</h2>

                    @include('partials.bundling-deskripsi', ['teks' => $detailBundle['deskripsi'] ?? ''])

                    <div class="text-center mb-3">
                        <span class="bdl-promo">PROMO HARI INI!</span>
                    </div>

                    <div class="text-center mb-3">
                        @if ($dAwal > $dBundle && $dAwal > 0)
                            <div class="bdl-price-old">Rp {{ number_format($dAwal, 0, ',', '.') }}</div>
                        @endif
                        <div>
                            <span class="bdl-price-now">Rp {{ number_format($dBundle, 0, ',', '.') }}</span>
                            <span class="bdl-price-unit">/ paket</span>
                        </div>
                    </div>

                    @if (!empty($detailBundle['produk']))
                        <div class="bdl-incl mb-3">
                            <div class="bdl-incl-title"><i class="bi bi-box-seam"></i> Termasuk dalam paket</div>
                            @foreach ($detailBundle['produk'] as $pr)
                                <div class="bdl-incl-row">
                                    <span class="bdl-incl-name">
                                        <i class="bi bi-check-circle-fill"></i>{{ $pr['nama'] }}
                                    </span>
                                    <span class="bdl-dur-badge">{{ $pr['dur_value'] }} {{ $pr['dur_type'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <button type="button" class="bdl-order-btn mt-auto" wire:click="addToCart('{{ $detailBundle['id'] }}')"
                        wire:loading.attr="disabled" wire:target="addToCart('{{ $detailBundle['id'] }}')">
                        <span wire:loading.remove wire:target="addToCart('{{ $detailBundle['id'] }}')">Pesan Sekarang!</span>
                        <span wire:loading wire:target="addToCart('{{ $detailBundle['id'] }}')">Memproses...</span>
                    </button>

                    <p class="bdl-foot mt-3 mb-0">🎉 <b>Jangan lewatkan kesempatan terbatas ini!</b> Promo bisa berakhir kapan saja.</p>
                </div>
            </div>
        </div>
    @endif
    @endif
</section>
