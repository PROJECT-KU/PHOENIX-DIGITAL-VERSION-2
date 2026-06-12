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

                    <div class="col-md-4 mb-3">
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

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            Nama Customer
                        </label>

                        <input type="text" wire:model="nama" class="form-control">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            Email
                        </label>

                        <input type="email" wire:model="email" class="form-control">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">
                            Catatan Customer
                        </label>

                        <textarea wire:model="customer_notes" rows="3" class="form-control" placeholder="Catatan tambahan..."></textarea>
                    </div>

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
                                <th>Produk</th>
                                <th width="120">Tipe</th>
                                <th width="120">Paket</th>
                                <th width="100">Qty</th>
                                <th width="170">Harga</th>
                                <th width="170">Subtotal</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">

                                    <td>
                                        <select wire:model.live="items.{{ $index }}.product_id"
                                            class="form-select">

                                            <option value="">
                                                Pilih Produk
                                            </option>

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

                                            <option value="bulan">Bulan</option>
                                            <option value="tahun">Tahun</option>

                                        </select>
                                    </td>

                                    <td>
                                        <select wire:model.live="items.{{ $index }}.duration_value"
                                            class="form-select">

                                            <option value="1">1</option>
                                            <option value="5">5</option>
                                            <option value="10">10</option>

                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" min="1" class="form-control"
                                            wire:model.live="items.{{ $index }}.quantity">
                                    </td>

                                    <td>
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

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Produk</label>
                        <input type="text" class="form-control" value="{{ count($items) }} Produk" readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Total Qty</label>
                        <input type="text" class="form-control" value="{{ collect($items)->sum('quantity') }}"
                            readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Grand Total</label>
                        <input type="text" class="form-control fw-bold text-success"
                            value="Rp {{ number_format($this->grandTotal, 0, ',', '.') }}" readonly>
                    </div>

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
