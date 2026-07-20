<div x-data="{ ids: JSON.parse(localStorage.getItem('ph_wishlist') || '[]') }" x-init="$wire.load(ids)">
    <div class="page-title ph-page-title">
        <div class="container">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-heart-fill"></i> Wishlist</span>
                <h1>Produk Favorit Anda</h1>
                <p>Produk yang Anda simpan tersimpan di perangkat ini.</p>
            </div>
        </div>
    </div>

    <section class="rel-section">
        <div class="container">
            @if ($products->isEmpty())
                <div class="ph-empty my-4">
                    {{-- Ilustrasi beranimasi — pola & kelas animasi sama dengan
                         keranjang (pe-float / pe-glow / pe-spark), hanya gambarnya
                         yang berbeda, supaya kedua halaman terasa satu keluarga. --}}
                    <div class="ph-empty-art">
                        <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                            aria-label="Wishlist kosong">
                            <defs>
                                <radialGradient id="peGlowW" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                                    <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                                </radialGradient>
                                <linearGradient id="peHeart" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fbc25a" />
                                    <stop offset="100%" stop-color="#f26522" />
                                </linearGradient>
                            </defs>

                            <ellipse class="pe-glow" cx="120" cy="106" rx="78" ry="78" fill="url(#peGlowW)" />
                            <ellipse class="pe-shadow" cx="120" cy="182" rx="52" ry="8" fill="#e15a18" />

                            <g transform="translate(50,64)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                            <g transform="translate(194,84)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                            <g transform="translate(188,142)"><path class="pe-spark s3" d="M0,-5 L1.3,-1.3 5,0 1.3,1.3 0,5 -1.3,1.3 -5,0 -1.3,-1.3Z" fill="#fbaf45" /></g>
                            <g transform="translate(56,148)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                            {{-- Hati besar (garis putus = belum terisi) --}}
                            <g class="pe-float">
                                <path d="M120,158 C86,134 64,116 64,94 C64,78 76,68 90,68 C101,68 112,74 120,86
                                         C128,74 139,68 150,68 C164,68 176,78 176,94 C176,116 154,134 120,158 Z"
                                    fill="none" stroke="url(#peHeart)" stroke-width="7" stroke-linejoin="round"
                                    stroke-dasharray="10 8" />
                                <path d="M96,88 Q104,80 114,84" stroke="#ffffff" stroke-opacity=".7" stroke-width="4"
                                    stroke-linecap="round" fill="none" />
                            </g>

                            {{-- Hati kecil melayang, berbeda irama --}}
                            <g class="pe-float-2">
                                <path d="M172,52 C165,47 160,43 160,38 C160,34 163,32 166,32 C168,32 170,33 172,36
                                         C174,33 176,32 178,32 C181,32 184,34 184,38 C184,43 179,47 172,52 Z"
                                    fill="url(#peHeart)" opacity=".9" />
                            </g>
                        </svg>
                    </div>
                    <h3 class="ph-empty-title">Wishlist masih kosong</h3>
                    <p class="ph-empty-sub">Simpan produk favorit dengan menekan tombol <b>♥ Simpan ke Wishlist</b> di halaman produk.</p>
                    <div class="ph-empty-actions">
                        <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                    </div>
                </div>
            @else
                <div class="rel-grid">
                    @foreach ($products as $p)
                        <div class="rel-card" style="position:relative;">
                            <button type="button" class="wish-remove" title="Hapus dari wishlist"
                                @click="ids = ids.filter(i => i !== '{{ $p->id }}'); localStorage.setItem('ph_wishlist', JSON.stringify(ids)); window.dispatchEvent(new Event('ph-wishlist-changed')); if (window.phToast) phToast('Dihapus dari wishlist', 'Wishlist', 'bi-heart'); $wire.load(ids)">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <a href="{{ route('shop.detail-product', $p->id) }}" style="text-decoration:none;color:inherit;">
                                <div class="rel-thumb">
                                    @if ($p->image)
                                        <img src="{{ asset('storage/img/Product/'.basename($p->image)) }}" alt="{{ $p->nama_akun }}" loading="lazy">
                                    @else
                                        <span class="rel-noimg"><i class="bi bi-box-seam"></i></span>
                                    @endif
                                </div>
                                <div class="rel-body">
                                    <h3 class="rel-name">{{ $p->nama_akun }}</h3>
                                    @if ($p->harga_perbulan)
                                        <div class="rel-price"><small>Mulai</small> Rp {{ number_format($p->harga_perbulan, 0, ',', '.') }}</div>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
