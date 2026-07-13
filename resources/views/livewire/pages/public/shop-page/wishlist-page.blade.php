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
