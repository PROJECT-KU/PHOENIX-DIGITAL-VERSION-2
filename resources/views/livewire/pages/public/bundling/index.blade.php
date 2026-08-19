<section id="best-sellers" @class(['bd-section section' => $bundlings->isNotEmpty()]) @if ($bundlings->isEmpty()) style="display:none" @endif>
    @include('partials.bundling-deskripsi-style')

    {{-- Gaya ditulis INLINE, bukan di resources/css: berkas CSS dibangun Vite
         dan public/build tidak ikut deploy, jadi gaya di sana tidak pernah
         sampai ke server. --}}
    <style>
        .bd-promo-note {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 138, 0, .1);
            color: var(--ph-orange, #fb8c00);
            font-size: .78rem;
            font-weight: 700;
            line-height: 1.3;
        }

        .bd-promo-kode {
            font-weight: 500;
            opacity: .9;
        }

        .bd-promo-kode b {
            letter-spacing: .3px;
        }
    </style>
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
                        {{-- Header otomatis dari data (bukan banner upload) → seragam. --}}
                        @php $__prod = collect([1, 2, 3, 4, 5])->map(fn ($i) => $item->{'product'.$i})->filter()->map->nama_akun->all(); @endphp
                        <button type="button" class="bd-card-head-btn" wire:click="openDetail('{{ $item->id }}')"
                            aria-label="Lihat detail {{ $item->nama_paket }}">
                            @include('partials.bundling-header', ['produk' => $__prod, 'nama' => $item->nama_paket, 'nomor' => $loop->iteration])
                        </button>

                        <div class="bd-card-body">
                            <div class="bd-head">
                                {{-- Nama ada di header; kartu cukup teaser singkat. --}}
                                @php $__t = \App\Support\DeskripsiProduk::pisah($item->deskripsi); @endphp
                                @php $__teaser = $__t['paragraf'][0] ?? ($__t['poin'][0] ?? ($__t['ekstra'][0]['teks'] ?? '')); @endphp
                                @if ($__teaser !== '')
                                    <p class="bd-card-desc bdesk-teaser">{{ $__teaser }}</p>
                                @endif
                            </div>

                            {{-- Harga tayang diambil dari App\Support\HargaPaket, bukan
                                 dihitung di sini: halaman ini, beranda, jendela detail, dan
                                 etalase flash sale harus menampilkan angka yang sama dengan
                                 yang ditagih keranjang. --}}
                            @php $hp = \App\Support\HargaPaket::untuk($item); @endphp
                            <div class="bd-price">
                                @if ($hp['coret'] > $hp['bayar'])
                                    <span class="bd-price-old">Rp{{ number_format($hp['coret'], 0, ',', '.') }}</span>
                                @endif
                                <span class="bd-price-now">Rp{{ number_format($hp['bayar'], 0, ',', '.') }}</span>
                                <span class="bd-price-unit">/ paket</span>
                            </div>
                            @if ($hp['potongan'] > 0)
                                <div class="bd-promo-note">
                                    <i class="bi bi-tag-fill"></i>
                                    Hemat Rp{{ number_format($hp['potongan'], 0, ',', '.') }}
                                    @if ($hp['butuh_kode'])
                                        {{-- Promo berkode tidak berlaku sendiri. Kodenya WAJIB
                                             ditampilkan, kalau tidak pembeli mengira harga ini
                                             otomatis lalu kecewa di checkout. --}}
                                        <span class="bd-promo-kode">pakai kode <b>{{ $hp['promo']->kode_promo }}</b></span>
                                    @endif
                                </div>
                            @endif

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
                    {{-- Header otomatis dari data paket. --}}
                    @include('partials.bundling-header', [
                        'produk' => collect($detailBundle['produk'] ?? [])->pluck('nama')->all(),
                        'nama' => $detailBundle['nama'],
                    ])

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
