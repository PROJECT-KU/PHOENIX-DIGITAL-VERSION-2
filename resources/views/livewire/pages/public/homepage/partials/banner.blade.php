@php $multiBanner = $banners->count() > 1; @endphp

<section id="hero" class="ph-hero section">
    <div class="container">
        {{-- Chip mengambang (ala flip.id) untuk mengisi area kosong --}}
        <div class="ph-hero-deco" aria-hidden="true">
            <span class="ph-chip c1"><span class="ph-chip-ic" style="--c:#f59e0b"><i class="bi bi-star-fill"></i></span> <b>4.9</b>&nbsp;Rating</span>
            <span class="ph-chip c2"><span class="ph-chip-ic" style="--c:#16a34a"><i class="bi bi-shield-check"></i></span> Akun Resmi &amp; Aman</span>
            <span class="ph-chip c3"><span class="ph-chip-ic" style="--c:#f26522"><i class="bi bi-lightning-charge-fill"></i></span> Proses Instan</span>
            <span class="ph-chip c4"><span class="ph-chip-ic" style="--c:#7c3aed"><i class="bi bi-emoji-smile-fill"></i></span> 5.000+ Pelanggan</span>
        </div>

        <div class="swiper phoenix-hero-swiper" data-aos="fade-up" data-multi="{{ $multiBanner ? '1' : '0' }}">
            <div class="swiper-wrapper">
                @forelse ($banners as $banner)
                <div class="swiper-slide">
                    <article class="ph-hero-slide">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-stars"></i> Phoenix Digital</span>
                            <h1 class="ph-hero-title">{{ $banner->judul }}</h1>
                            @if ($banner->deskripsi)
                            <p class="ph-hero-desc">{{ $banner->deskripsi }}</p>
                            @endif
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">
                                    Belanja Sekarang <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>
                            <div class="ph-hero-meta">
                                <span class="ph-hero-stars">
                                    <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                                </span>
                                <span><b>4.9</b> — dipercaya 5.000+ pelanggan</span>
                            </div>
                        </div>
                        <div class="ph-hero-media">
                            <img src="{{ asset('storage/img/banners/' . $banner->gambar) }}"
                                alt="{{ $banner->judul ?? 'Banner' }}" loading="lazy">
                        </div>
                    </article>
                </div>
                @empty
                <div class="swiper-slide">
                    <article class="ph-hero-slide ph-hero-slide--empty">
                        <div class="ph-hero-text">
                            <span class="ph-hero-eyebrow"><i class="bi bi-stars"></i> Phoenix Digital</span>
                            <h1 class="ph-hero-title">Solusi Akun &amp; Lisensi Digital Terpercaya</h1>
                            <p class="ph-hero-desc">Akun premium, lisensi, dan tools AI untuk riset &amp; produktivitas — proses cepat dan bergaransi.</p>
                            <div class="ph-hero-actions">
                                <a href="{{ route('shop.index') }}" class="ph-btn-primary">Belanja Sekarang <i class="bi bi-arrow-right"></i></a>
                                <a href="{{ route('shop.index') }}" class="ph-btn-ghost">Lihat Katalog</a>
                            </div>
                        </div>
                        <div class="ph-hero-media ph-hero-media--empty">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </article>
                </div>
                @endforelse
            </div>

            @if ($multiBanner)
            <div class="swiper-pagination"></div>
            @endif
        </div>

        {{-- Baris kepercayaan (konsisten berapa pun jumlah banner) --}}
        <div class="ph-hero-trust" data-aos="fade-up" data-aos-delay="100">
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-lightning-charge"></i></span>
                <div><strong>Proses Instan</strong><span>Akun cepat aktif</span></div>
            </div>
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-shield-check"></i></span>
                <div><strong>Bergaransi</strong><span>Aman &amp; terjamin</span></div>
            </div>
            <div class="ph-trust-item">
                <span class="ph-trust-ico"><i class="bi bi-headset"></i></span>
                <div><strong>Bantuan 24/7</strong><span>Siap membantu</span></div>
            </div>
        </div>
    </div>
</section>
