<div>
    <form wire:submit="save">

        {{-- ================== DATA CUSTOMER ================== --}}
        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle me-2"></i>
                Data Customer
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">
                            Nomor HP
                        </label>

                        <input type="text" wire:model.live.debounce.500ms="no_hp" class="form-control"
                            placeholder="08xxxxxxxxxx">

                        @if ($customerFound)
                            <small class="text-success">
                                <i class="bi bi-check-circle-fill"></i>
                                Customer ditemukan
                            </small>
                        @endif
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">
                            Nama Customer
                        </label>

                        <input type="text" wire:model="nama" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">
                            Email
                        </label>

                        <input type="email" wire:model="email" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">
                            Reward Point
                        </label>

                        <input type="text" class="form-control text-warning fw-bold"
                            value="{{ number_format($customerPoint) }} Poin" readonly>
                    </div>

                    @if ($customerFound)
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                Status Member
                            </label>

                            <div>
                                <span class="badge {{ $customerStatus === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($customerStatus) }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                Kode Referral
                            </label>

                            <div class="border rounded p-2 bg-light">
                                <span class="fw-bold text-primary">
                                    {{ $customerReferralCode ?: '-' }}
                                </span>
                            </div>
                        </div>
                    @endif

                    @if ($showPointsOption)
                        <div class="col-md-6 mb-3">

                            <label class="form-label fw-semibold">
                                Gunakan Poin
                            </label>

                            <div class="form-check">

                                <input type="checkbox" class="form-check-input" wire:model.live="usePoints">

                                <label class="form-check-label">

                                    Pakai {{ number_format($availablePoints) }}
                                    poin
                                    (Rp {{ number_format($pointsValue) }})

                                </label>

                            </div>

                        </div>
                    @endif

                </div>

            </div>

        </div>

        {{-- ================== DATA PRODUK ================== --}}
        <div class="card shadow-sm border mb-4">

            <div class="card-header bg-light d-flex justify-content-between align-items-center">

                <span class="fw-semibold">
                    <i class="bi bi-bag-check me-2"></i>
                    Produk Pesanan
                </span>

                <button type="button" class="btn btn-primary btn-sm" wire:click="addItem">

                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Produk

                </button>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-light">
                            <tr>
                                <th width="25%">Produk</th>
                                <th width="12%">Tipe</th>
                                <th width="18%">Durasi</th>
                                <th width="10%">Qty</th>
                                <th width="12%">Harga</th>
                                <th width="12%">Subtotal</th>
                                <th width="6%">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">

                                    <td>
                                        <select wire:model.live="items.{{ $index }}.product_id"
                                            class="form-select">

                                            <option value="">Pilih Produk</option>

                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->nama_akun }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </td>

                                    <td>
                                        <select wire:model.live="items.{{ $index }}.duration_type"
                                            class="form-select">

                                            <option value="bulan">
                                                Bulanan
                                            </option>

                                            <option value="paket">
                                                Paket
                                            </option>

                                        </select>
                                    </td>

                                    <td>

                                        @if (($item['duration_type'] ?? 'bulan') === 'bulan')
                                            <select wire:model.live="items.{{ $index }}.duration_value"
                                                class="form-select">

                                                @for ($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}">
                                                        {{ $i }} Bulan
                                                    </option>
                                                @endfor

                                            </select>
                                        @else
                                            <select wire:model.live="items.{{ $index }}.duration_value"
                                                class="form-select">

                                                <option value="5">
                                                    Paket 5 Bulan
                                                </option>

                                                <option value="10">
                                                    Paket 10 Bulan
                                                </option>

                                                <option value="12">
                                                    Paket 1 Tahun
                                                </option>

                                            </select>
                                        @endif

                                    </td>

                                    <td>
                                        <input type="number" min="1" class="form-control"
                                            wire:model.change="items.{{ $index }}.quantity">
                                    </td>

                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                                    </td>

                                    <td class="fw-semibold">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </td>

                                    <td class="text-center">

                                        @if (count($items) > 1)
                                            <button type="button" class="btn btn-danger btn-sm"
                                                wire:click="removeItem({{ $index }})">

                                                <i class="bi bi-trash"></i>

                                            </button>
                                        @endif

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                    <div class="card shadow-sm border mb-4">

                        <div class="card-header bg-light">
                            <i class="bi bi-ticket-perforated me-2"></i>
                            Promo & Diskon
                        </div>

                        <div class="card-body">

                            <div class="row g-3">

                                <div class="col-md-8">

                                    <label class="form-label fw-semibold">
                                        Pilih Promo Aktif
                                    </label>

                                    <select class="form-select" wire:model.live="selectedPromoId">

                                        <option value="">
                                            Tanpa Promo
                                        </option>

                                        @foreach ($activePromos as $promo)
                                            <option value="{{ $promo->id }}">
                                                {{ $promo->nama_promo }}
                                                @if ($promo->kode_promo)
                                                    ({{ $promo->kode_promo }})
                                                @endif
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">
                                        Diskon
                                    </label>

                                    <input type="text" class="form-control text-success fw-bold"
                                        value="Rp {{ number_format($promoDiscount, 0, ',', '.') }}" readonly>

                                </div>

                            </div>

                            @if ($promoMessage)
                                <div class="alert {{ $promoValid ? 'alert-success' : 'alert-danger' }} mt-3 mb-0">

                                    {{ $promoMessage }}

                                </div>
                            @endif

                            @if (count($appliedPromos))
                                <div class="mt-3">

                                    @foreach ($appliedPromos as $promo)
                                        <span class="badge bg-success me-2">
                                            {{ $promo['nama_promo'] }}
                                        </span>
                                    @endforeach

                                </div>
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- ================== RINGKASAN ================== --}}
        <div class="card shadow-sm border mb-4">

            <div class="card-header bg-light fw-semibold">
                <i class="bi bi-receipt me-2"></i>
                Ringkasan Pesanan
            </div>

            <div class="card-body">

                <div class="d-flex justify-content-between mb-2">

                    <span>Jumlah Produk</span>

                    <span class="fw-bold">
                        {{ count($items) }} Produk
                    </span>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>Total Qty</span>

                    <span class="fw-bold">
                        {{ collect($items)->sum('quantity') }}
                    </span>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>Subtotal</span>

                    <span class="fw-bold">
                        Rp {{ number_format($this->subTotal, 0, ',', '.') }}
                    </span>

                </div>

                @if ($promoDiscount > 0)
                    <div class="d-flex justify-content-between text-success mb-2">

                        <span>Diskon Promo</span>

                        <span class="fw-bold">
                            - Rp {{ number_format($promoDiscount, 0, ',', '.') }}
                        </span>

                    </div>
                @endif

                @if ($pointsDiscount > 0)
                    <div class="d-flex justify-content-between text-warning mb-2">

                        <span>Diskon Poin</span>

                        <span class="fw-bold">
                            - Rp {{ number_format($pointsDiscount, 0, ',', '.') }}
                        </span>

                    </div>
                @endif

                <hr>

                <div class="d-flex justify-content-between">

                    <span class="fs-5 fw-bold">
                        Grand Total
                    </span>

                    <span class="fs-5 fw-bold text-success">
                        Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                    </span>

                </div>

            </div>

        </div>

        {{-- ================== BUTTON ================== --}}
        <div class="mb-5">

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-save me-1"></i>
                Simpan Pesanan
            </button>

        </div>

    </form>
</div>
