<main class="main">
    <!-- Page Title -->
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Shopping</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="/">Home</a></li>
                    <li class="current">Shop</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <!-- list product -->
    <section style="padding-top: 20px;">
        <div class="container">
            <section style="padding-top: 0;" id="category-header" class="category-header section">
                <div class="container">
                    @if ($search)
                        <div class="mb-4 alert alert-info" role="alert">
                            Menampilkan hasil pencarian untuk: <strong>{{ $search }}</strong>
                            <button wire:click="$set('search', '')" class="btn-close float-end"
                                aria-label="Clear search"></button>
                        </div>
                    @endif
                </div>

                <!-- Category Product List Section -->
                <section style="padding-top: 0;" id="category-product-list" class="category-product-list section">
                    <div class="container">
                        <div class="row g-4">
                            @forelse ($products as $item)
                                <div class="col-6 col-md-4 col-lg-3" wire:key="product-{{ $item->id }}">
                                    <div class="product-card">
                                        <div class="product-image">
                                            @if ($item->image)
                                                <div>
                                                    <img src="{{ asset('storage/img/product/' . $item->image) }}"
                                                        class="main-image img-fluid" alt="{{ $item->nama_akun }}">
                                                    <img src="{{ asset('storage/img/product/' . $item->image) }}"
                                                        class="hover-image img-fluid" alt="{{ $item->nama_akun }}">
                                                </div>
                                            @else
                                                <div>
                                                    <img class="main-image img-fluid" style="object-fit: cover"
                                                        src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                        alt="">
                                                    <img class="hover-image img-fluid" style="object-fit: cover"
                                                        src="https://fastly.picsum.photos/id/77/450/300.jpg?hmac=V_LawevwSaVitpQs2t7AnuBi84UPSNl1Qp3PmKkmaXc"
                                                        alt="">
                                                </div>
                                            @endif

                                            <div class="product-overlay">
                                                <div class="product-actions">
                                                    <a href="{{ route('shop.detail-product', $item->id) }}"
                                                        class="action-btn" data-bs-toggle="tooltip"
                                                        title="Detail Product">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="action-btn" data-bs-toggle="modal"
                                                        data-bs-target="#selectPackageModal{{ $item->id }}"
                                                        title="Tambah ke Keranjang">
                                                        <i class="bi bi-cart-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="product-details">
                                            <h4 class="product-title">
                                                <a href="{{ route('shop.detail-product', $item->id) }}">
                                                    {{ $item->nama_akun }}
                                                </a>
                                            </h4>
                                            <div class="product-meta">
                                                <div class="product-price">
                                                    Mulai Rp
                                                    {{ number_format($item->harga_perbulan, 0, ',', '.') }}/bulan
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Pilih Paket -->
                                <div class="modal fade" id="selectPackageModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Pilih Paket {{ $item->nama_akun }}</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="gap-2 d-flex flex-column">
                                                    @if ($item->harga_perbulan)
                                                        <button type="button" class="btn btn-outline-secondary w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 1)"
                                                            data-bs-dismiss="modal">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <p>Paket 1 Bulan</p>
                                                                <strong>Rp
                                                                    {{ number_format($item->harga_perbulan, 0, ',', '.') }}</strong>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_5_perbulan)
                                                        <button type="button" class="btn btn-outline-secondary w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 5)"
                                                            data-bs-dismiss="modal">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <p>Paket 5 Bulan</p>
                                                                <div>
                                                                    <strong>Rp
                                                                        {{ number_format($item->harga_5_perbulan, 0, ',', '.') }}</strong>

                                                                    <small class="d-block">Hemat
                                                                        {{ number_format($item->harga_perbulan * 5 - $item->harga_5_perbulan, 0, ',', '.') }}</small>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_10_perbulan)
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            wire:click="addToCart('{{ $item->id }}', 'bulan', 10)"
                                                            data-bs-dismiss="modal">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <p>Paket 10 Bulan</p>
                                                                <div>
                                                                    <strong>Rp
                                                                        {{ number_format($item->harga_10_perbulan, 0, ',', '.') }}</strong>
                                                                    <small class="d-block">Hemat
                                                                        {{ number_format($item->harga_perbulan * 10 - $item->harga_10_perbulan, 0, ',', '.') }}</small>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif

                                                    @if ($item->harga_pertahun)
                                                        <button type="button" class="btn btn-outline-secondary w-100"
                                                            wire:click="addToCart('{{ $item->id }}', 'tahun', 1)"
                                                            data-bs-dismiss="modal">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <p>Paket 1 Tahun</p>
                                                                <div>
                                                                    <strong>Rp
                                                                        {{ number_format($item->harga_pertahun, 0, ',', '.') }}</strong>
                                                                    <small class="d-block">Hemat
                                                                        {{ number_format($item->harga_perbulan * 12 - $item->harga_pertahun, 0, ',', '.') }}</small>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center alert alert-warning">
                                        <i class="bi bi-search"></i>
                                        <p class="mt-2 mb-0">Tidak ada produk yang ditemukan</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-5">
                            {{ $products->links('vendor.pagination') }}
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </section>
    <!-- end list product -->
</main>
