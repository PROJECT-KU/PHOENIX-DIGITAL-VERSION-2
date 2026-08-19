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
                <div class="col-6 col-md-4 col-lg-3" wire:key="bundling-{{ $item->id }}">
                    @include('partials.kartu-paket', ['item' => $item, 'detailKlik' => "openDetail('{$item->id}')"])
                </div>
            @empty
                <div class="col-12">
                    <div class="bd-empty"><i class="bi bi-box-seam"></i> Belum ada paket bundling saat ini.</div>
                </div>
            @endforelse
        </div>

        @if ($diBeranda)
            {{-- Beranda hanya memuat 4 paket terbaru sebagai cuplikan; sisanya
                 ada di halaman paket tersendiri. --}}
            @if ($bundlings->isNotEmpty())
                <div class="text-center mt-4">
                    <a href="{{ route('bundling.product-bundlings') }}" class="ph-btn-ghost">Lihat Semua Paket <i class="bi bi-arrow-right"></i></a>
                </div>
            @endif
        @else
            <div class="mt-4">{{ $bundlings->links() }}</div>
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
