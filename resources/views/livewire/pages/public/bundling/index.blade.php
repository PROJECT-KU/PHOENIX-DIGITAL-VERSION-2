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
            {{-- Paginasi seragam dengan halaman shop: pembungkus .ph-pagination
                 yang menengahkan, dan hanya tampil bila memang lebih dari
                 satu halaman. --}}
            @if ($bundlings->hasPages())
                <div class="mt-5 ph-pagination">
                    {{ $bundlings->links('pagination.ph') }}
                </div>
            @endif
        @endif
    </div>

    @include('partials.modal-paket')
    @endif
</section>
