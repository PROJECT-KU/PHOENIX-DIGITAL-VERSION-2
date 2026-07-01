<div>
    <style>
        .of-section {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .of-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        .of-icon.green {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
        }

        .of-icon.amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 6px 14px rgba(217, 119, 6, 0.35);
        }

        .of-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            width: 100%;
            height: 100%;
        }

        .of-icon i.bi::before {
            display: block;
            line-height: 1;
        }

        .of-form-label {
            font-weight: 600;
            color: #475569;
            font-size: .85rem;
        }

        /* Kartu info status member & poin */
        .cust-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        @media (max-width: 575.98px) {
            .cust-meta {
                grid-template-columns: 1fr;
            }
        }

        .cm-card {
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .75rem .9rem;
            border-radius: 12px;
            border: 1px solid #e7e9f5;
            background: #f8f9ff;
        }

        .cm-card.is-active {
            border-color: rgba(16, 185, 129, 0.30);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.10), rgba(5, 150, 105, 0.04));
        }

        .cm-card.cm-point {
            border-color: rgba(245, 158, 11, 0.30);
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.10), rgba(217, 119, 6, 0.04));
        }

        .cm-ic {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            background: #e2e8f0;
            color: #64748b;
        }

        .cm-card.is-active .cm-ic {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
        }

        .cm-card.cm-point .cm-ic {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        .cm-ic i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            width: 100%;
            height: 100%;
        }

        .cm-ic i.bi::before {
            display: block;
            line-height: 1;
        }

        .cm-label {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #8b8fa3;
            font-weight: 600;
        }

        .cm-value {
            display: block;
            font-weight: 700;
            color: #1e293b;
            font-size: .95rem;
        }

        .cm-rp {
            font-weight: 600;
            color: #b45309;
            font-size: .78rem;
        }

        .of-item {
            border: 1px solid rgba(108, 99, 255, 0.14);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.6);
            overflow: hidden;
        }

        .of-item-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .55rem .9rem;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.05));
            border-bottom: 1px solid rgba(108, 99, 255, 0.12);
        }

        .of-item-head .it-no {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-weight: 700;
            color: #4e46e5;
            font-size: .88rem;
        }

        .of-item-head .it-no .num {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
        }

        .of-item-head .it-no .num i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            width: 100%;
            height: 100%;
        }

        .of-item-head .it-no .num i.bi::before {
            display: block;
            line-height: 1;
        }

        .of-item-body {
            padding: 1rem .9rem;
        }

        .of-item-head.is-bundle {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.05));
            border-bottom-color: rgba(16, 185, 129, 0.18);
        }

        .of-item-head.is-bundle .it-no {
            color: #059669;
        }

        .of-item-head.is-bundle .it-no .num {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .bundle-prod {
            padding: .6rem .75rem;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #eef0f6;
            margin-bottom: .5rem;
        }

        .bundle-note {
            font-size: .76rem;
            color: #475569;
            background: #fff8e6;
            border: 1px dashed #f0c674;
            border-radius: 10px;
            padding: .5rem .75rem;
            line-height: 1.4;
        }

        .bundle-note i {
            color: #d97706;
        }

        /* Tombol pemicu picker (tampil seperti select) */
        .of-picker-btn {
            cursor: pointer;
        }

        .of-picker-btn::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            float: right;
            color: #94a3b8;
            font-size: .8rem;
        }

        /* Daftar di dalam SweetAlert picker */
        .of-pick-list {
            max-height: 320px;
            overflow-y: auto;
            text-align: left;
            display: flex;
            flex-direction: column;
            gap: .4rem;
            padding: .2rem;
        }

        .of-pick-item {
            display: block;
            width: 100%;
            text-align: left;
            border: 1px solid #e6e8f2;
            background: #fff;
            border-radius: 12px;
            padding: .7rem .9rem;
            font-weight: 600;
            color: #1e293b;
            font-size: .92rem;
            transition: all .15s ease;
        }

        .of-pick-item:hover {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.04));
            transform: translateY(-1px);
        }

        .of-pick-item .op-sub {
            display: block;
            font-size: .76rem;
            font-weight: 500;
            color: #94a3b8;
            margin-top: .1rem;
        }

        .of-pick-empty {
            text-align: center;
            color: #94a3b8;
            padding: 1.5rem;
            font-size: .9rem;
        }

        .of-subtotal-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 1rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.07), rgba(78, 70, 229, 0.04));
            border: 1px dashed rgba(108, 99, 255, 0.30);
        }

        .of-subtotal-bar .st-val {
            font-weight: 800;
            color: #4e46e5;
            font-size: 1.05rem;
        }

        /* Pilihan metode pembayaran */
        .pay-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
        }

        @media (max-width: 575.98px) {
            .pay-grid {
                grid-template-columns: 1fr;
            }
        }

        .pay-opt {
            position: relative;
            display: flex;
            align-items: center;
            gap: .7rem;
            padding: .85rem 1rem;
            border-radius: 14px;
            border: 1.5px solid #e6e8f2;
            background: rgba(255, 255, 255, 0.65);
            cursor: pointer;
            transition: all .2s ease;
        }

        .pay-opt input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .pay-opt:hover {
            border-color: rgba(108, 99, 255, 0.4);
            background: rgba(108, 99, 255, 0.05);
        }

        .pay-opt:has(input:checked) {
            border-color: #6c63ff;
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.12), rgba(78, 70, 229, 0.05));
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.14);
        }

        .pay-opt .pay-ic {
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef0ff;
            color: #4e46e5;
            font-size: 1.1rem;
        }

        .pay-opt .pay-ic i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            width: 100%;
            height: 100%;
        }

        .pay-opt .pay-ic i.bi::before {
            display: block;
            line-height: 1;
        }

        .pay-opt:has(input:checked) .pay-ic {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            color: #fff;
        }

        .pay-opt .pay-name {
            font-weight: 700;
            font-size: .88rem;
            color: #1e293b;
        }

        .pay-opt .pay-sub {
            font-size: .72rem;
            color: #94a3b8;
        }

        .of-summary {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .of-summary .row-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .5rem 0;
            font-size: .95rem;
            color: #475569;
        }

        .of-total-box {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            border-radius: 12px;
            padding: .9rem 1.1rem;
            color: #fff;
        }

        .promo-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .3rem .7rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 700;
            color: #fff;
        }

        .chip-flash {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
        }

        .chip-promo {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
        }
    </style>

    <form wire:submit="save">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- ============ CUSTOMER ============ -->
                <div class="of-section p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="of-icon green"><i class="bi bi-person-vcard-fill"></i></span>
                        <div>
                            <h5 class="fw-bold mb-0">Data Pelanggan</h5>
                            <small class="text-muted">Ketik no HP — jika sudah terdaftar, data terisi otomatis</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="of-form-label">Nomor HP <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" wire:model.live.debounce.500ms="no_hp"
                                    class="form-control @error('no_hp') is-invalid @enderror" placeholder="08xxxxxxxxxx">
                                <div wire:loading wire:target="no_hp,searchCustomer"
                                    class="position-absolute end-0 top-50 translate-middle-y pe-3">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </div>
                            </div>
                            @error('no_hp') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @if ($customerFound)
                            <small class="text-success fw-semibold"><i class="bi bi-check-circle-fill"></i> Pelanggan
                                ditemukan — data terisi otomatis</small>
                            @elseif (strlen($no_hp) >= 10)
                            <small class="text-muted"><i class="bi bi-info-circle"></i> Pelanggan baru — isi data
                                manual</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="of-form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nama"
                                class="form-control @error('nama') is-invalid @enderror {{ $customerFound ? 'bg-light' : '' }}"
                                placeholder="Nama pelanggan">
                            @error('nama') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        @if ($customerFound)
                        <div class="col-12">
                            <label class="of-form-label">Email</label>
                            <input type="email" wire:model="email"
                                class="form-control bg-light @error('email') is-invalid @enderror"
                                placeholder="email@contoh.com" readonly>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        @endif
                        @if ($foundCustomer)
                        <div class="col-12">
                            <div class="cust-meta">
                                <div class="cm-card {{ $foundCustomer->status_member === 'active' ? 'is-active' : '' }}">
                                    <span class="cm-ic">
                                        <i class="bi {{ $foundCustomer->status_member === 'active' ? 'bi-patch-check-fill' : 'bi-person' }}"></i>
                                    </span>
                                    <span>
                                        <span class="cm-label">Status Member</span>
                                        <span class="cm-value">{{ $foundCustomer->status_member === 'active' ? 'Member Aktif' : 'Non-Member' }}</span>
                                    </span>
                                </div>
                                <div class="cm-card cm-point">
                                    <span class="cm-ic"><i class="bi bi-coin"></i></span>
                                    <span>
                                        <span class="cm-label">Poin Tersedia</span>
                                        <span class="cm-value">{{ number_format($foundCustomer->point, 0, ',', '.') }} poin
                                            <small class="cm-rp">≈ Rp {{ number_format($pointsValue, 0, ',', '.') }}</small>
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- ============ AKUN DIBELI ============ -->
                <div class="of-section p-4 mb-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="of-icon"><i class="bi bi-bag-check-fill"></i></span>
                            <h5 class="fw-bold mb-0">Pemesanan Akun</h5>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" wire:click="addItem"
                                class="btn btn-sm btn-outline-primary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-plus-lg"></i> Tambah Akun
                            </button>
                            @if (count($bundlings) > 0)
                            <button type="button" onclick="ofBundlePicker(this)"
                                class="btn btn-success btn-sm d-inline-flex align-items-center justify-content-center gap-1 text-nowrap flex-shrink-0">
                                <i class="bi bi-box-seam"></i> <span>Tambah Paket</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    @foreach ($items as $i => $item)
                    @php $isBundle = ($item['type'] ?? 'product') === 'bundle'; @endphp
                    <div class="of-item mb-3">
                        <div class="of-item-head {{ $isBundle ? 'is-bundle' : '' }}">
                            @if ($isBundle)
                            <span class="it-no"><span class="num"><i class="bi bi-box-seam"></i></span> Paket: {{ $item['bundling_name'] }}</span>
                            @else
                            <span class="it-no"><span class="num">{{ $i + 1 }}</span> Akun</span>
                            @endif
                            @if (count($items) > 1)
                            <button type="button" wire:click="removeItem({{ $i }})"
                                class="btn btn-sm btn-light text-danger d-inline-flex align-items-center justify-content-center p-1"
                                style="width:30px;height:30px;" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                        <div class="of-item-body">
                            @if ($isBundle)
                            {{-- ===== PAKET BUNDLING: produk dipecah, durasi per produk ===== --}}
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <span class="badge bg-success-subtle text-success border border-success">
                                    <i class="bi bi-box-seam"></i> Harga paket: Rp {{ number_format($item['harga_bundling'], 0, ',', '.') }}
                                </span>
                                <small class="text-muted">Harga dibagi proporsional ke tiap produk</small>
                            </div>
                            @foreach ($item['products'] as $j => $sub)
                            <div class="bundle-prod d-flex align-items-center justify-content-between gap-2">
                                <span class="fw-semibold" style="font-size:.9rem;">
                                    {{ $j + 1 }}. {{ $sub['product_name'] }}
                                    <span class="badge bg-primary-subtle text-primary border border-primary ms-1">
                                        <i class="bi bi-clock"></i> {{ $sub['duration_value'] }} {{ $sub['duration_type'] }}
                                    </span>
                                </span>
                                <span class="badge bg-light text-dark border">Rp {{ number_format($sub['distributed'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                            <div class="of-subtotal-bar mt-2" style="border-color:rgba(16,185,129,.35); background:linear-gradient(135deg,rgba(16,185,129,.08),rgba(5,150,105,.04));">
                                <span class="text-muted small">Subtotal paket</span>
                                <span class="st-val" style="color:#059669;">Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="bundle-note mt-2">
                                <i class="bi bi-shield-check"></i> Harga paket sudah termasuk diskon — <b>flash sale & promo
                                    tidak berlaku</b> untuk produk di dalam paket. Bonus/ebook diisi saat proses.
                            </div>
                            @else
                            {{-- ===== PRODUK SATUAN ===== --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="of-form-label">Produk Akun <span class="text-danger">*</span></label>
                                    @php $selProd = $products->firstWhere('id', $item['product_id'] ?? null); @endphp
                                    <button type="button" onclick="ofProductPicker(this, {{ $i }})"
                                        class="form-select text-start of-picker-btn @error('items.'.$i.'.product_id') is-invalid @enderror">
                                        @if ($selProd)
                                        <span class="text-dark">{{ $selProd->nama_akun }}</span>
                                        @else
                                        <span class="text-muted">-- Pilih Produk --</span>
                                        @endif
                                    </button>
                                    @error('items.'.$i.'.product_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="of-form-label">Satuan</label>
                                    <select wire:model.live="items.{{ $i }}.duration_type" class="form-select">
                                        <option value="bulan">Bulan</option>
                                        <option value="tahun">Tahun</option>
                                    </select>
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="of-form-label">Durasi</label>
                                    <input type="number" min="1" wire:model.live="items.{{ $i }}.duration_value" class="form-control">
                                </div>
                                <div class="col-4 col-md-2">
                                    <label class="of-form-label">Qty</label>
                                    <input type="number" min="1" wire:model.live="items.{{ $i }}.quantity" class="form-control">
                                </div>
                                <div class="col-12">
                                    <div class="of-subtotal-bar">
                                        <span class="text-muted small">Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }} × {{ $item['quantity'] ?? 1 }}</span>
                                        <span class="st-val">Rp {{ number_format($item['subtotal'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @error('items') <div class="text-danger small">{{ $message }}</div> @enderror

                    <div class="alert alert-info mt-2 mb-0 py-2 px-3 rounded-3" style="font-size:.83rem;">
                        <i class="bi bi-info-circle me-1"></i> Bonus durasi, bonus ebook, & data akun diisi per item di
                        halaman <b>Detail → Proses Pesanan</b> setelah ini.
                    </div>
                </div>

                <!-- ============ DISKON & POIN ============ -->
                <div class="of-section p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="of-icon amber"><i class="bi bi-percent"></i></span>
                        <h5 class="fw-bold mb-0">Diskon & Poin</h5>
                    </div>

                    @php
                    $flashSales = collect($appliedPromos)->where('tipe_promo', 'flash_sale');
                    $autoPromos = collect($appliedPromos)->where('tipe_promo', 'auto_promo');
                    @endphp
                    @if ($flashSales->isNotEmpty() || $autoPromos->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach ($flashSales as $fs)
                        <span class="promo-chip chip-flash"><i class="bi bi-lightning-charge-fill"></i> Flash Sale otomatis</span>
                        @endforeach
                        @foreach ($autoPromos as $ap)
                        <span class="promo-chip chip-promo"><i class="bi bi-tags-fill"></i> Promo otomatis</span>
                        @endforeach
                    </div>
                    @endif

                    <label class="of-form-label">Kode Promo</label>
                    <div class="input-group mb-1">
                        <input type="text" wire:model="kodePromo" class="form-control" placeholder="Masukkan kode promo"
                            {{ $promoValid ? 'readonly' : '' }}>
                        @if ($promoValid)
                        <button type="button" wire:click="removePromo" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        @else
                        <button type="button" wire:click="applyPromo" class="btn btn-primary">Pakai</button>
                        @endif
                    </div>
                    @if ($promoMessage)
                    <small class="{{ $promoValid ? 'text-success' : 'text-danger' }}">{{ $promoMessage }}</small>
                    @endif

                    @if ($showReferralInput)
                    <hr class="my-3">
                    <label class="of-form-label">Kode Referral <span class="text-muted">(khusus pembeli pertama)</span></label>
                    <div class="input-group mb-1">
                        <input type="text" wire:model="referralCode" class="form-control" placeholder="PDW_XXXX"
                            {{ $referralValid ? 'readonly' : '' }}>
                        @if ($referralValid)
                        <button type="button" wire:click="removeReferral" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                        @else
                        <button type="button" wire:click="checkReferralCode" class="btn btn-success">Cek</button>
                        @endif
                    </div>
                    @if ($referralMessage)
                    <small class="{{ $referralValid ? 'text-success' : 'text-danger' }}">{{ $referralMessage }}</small>
                    @endif
                    @endif

                    @if ($pointsValue > 0)
                    <hr class="my-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="usePoints" wire:model.live="usePoints">
                        <label class="form-check-label fw-semibold" for="usePoints">
                            Gunakan poin — {{ $foundCustomer->point }} poin (senilai Rp {{ number_format($pointsValue, 0, ',', '.') }})
                        </label>
                    </div>
                    @endif
                </div>

                <!-- ============ PEMBAYARAN ============ -->
                <div class="of-section p-4 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="of-icon"><i class="bi bi-wallet2"></i></span>
                        <h5 class="fw-bold mb-0">Metode Pembayaran <span class="text-danger">*</span></h5>
                    </div>

                    <div class="pay-grid">
                        <label class="pay-opt">
                            <input type="radio" value="transfer" wire:model="payment_method">
                            <span class="pay-ic"><i class="bi bi-bank"></i></span>
                            <span>
                                <span class="pay-name d-block">Transfer Bank</span>
                                <span class="pay-sub">Transfer manual ke rekening</span>
                            </span>
                        </label>
                        <label class="pay-opt">
                            <input type="radio" value="qris_statis" wire:model="payment_method">
                            <span class="pay-ic"><i class="bi bi-qr-code"></i></span>
                            <span>
                                <span class="pay-name d-block">QRIS Statis</span>
                                <span class="pay-sub">Scan QR tetap</span>
                            </span>
                        </label>
                        <label class="pay-opt">
                            <input type="radio" value="qris_dinamis" wire:model="payment_method">
                            <span class="pay-ic"><i class="bi bi-qr-code-scan"></i></span>
                            <span>
                                <span class="pay-name d-block">QRIS Dinamis</span>
                                <span class="pay-sub">QR sesuai nominal</span>
                            </span>
                        </label>
                    </div>
                    @error('payment_method') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

                    <div class="mt-3">
                        <label class="of-form-label">Catatan Pelanggan <span class="text-muted">(opsional)</span></label>
                        <textarea wire:model="customer_notes" rows="2" class="form-control" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>
            </div>

            <!-- ============ RINGKASAN ============ -->
            <div class="col-lg-4">
                <div class="of-summary p-4" style="position: sticky; top: 1rem;">
                    <h5 class="fw-bold mb-3"><i class="bi bi-receipt-cutoff text-primary me-1"></i> Ringkasan</h5>

                    <div class="row-line">
                        <span>Subtotal</span>
                        <span class="fw-semibold text-dark">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if ($promoDiscount > 0)
                    <div class="row-line text-danger">
                        <span><i class="bi bi-tags-fill"></i> Diskon Promo</span>
                        <span class="fw-semibold">- Rp {{ number_format($promoDiscount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if ($referralDiscount > 0)
                    <div class="row-line text-danger">
                        <span><i class="bi bi-people-fill"></i> Diskon Referral</span>
                        <span class="fw-semibold">- Rp {{ number_format($referralDiscount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if ($pointsDiscount > 0)
                    <div class="row-line text-danger">
                        <span><i class="bi bi-coin"></i> Potongan Poin</span>
                        <span class="fw-semibold">- Rp {{ number_format($pointsDiscount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if ($uniqueCode > 0)
                    <div class="row-line">
                        <span>Kode Unik</span>
                        <span class="fw-semibold">+ Rp {{ number_format($uniqueCode, 0, ',', '.') }}</span>
                    </div>
                    @endif

                    <div class="of-total-box mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="opacity:.85; font-size:.85rem; text-transform:uppercase; letter-spacing:.05em;">Total</span>
                            <span class="fw-bold fs-4">Rp {{ number_format($finalTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button type="submit"
                        class="btn btn-primary w-100 mt-3 d-flex align-items-center justify-content-center"
                        style="height: 50px;" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save" class="d-inline-flex align-items-center">
                            <i class="bi bi-check2-circle me-2 fs-5"></i> Buat Pesanan
                        </span>
                    </button>
                    <small class="text-muted d-block text-center mt-2">Setelah dibuat, Anda diarahkan ke <b>Detail
                            Pesanan</b> — proses tiap akun (data akun, bonus, ebook) dari sana.</small>
                </div>
            </div>
        </div>
    </form>

    @php
        $ofProductsData = $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->nama_akun])->values();
        $ofBundlesData = $bundlings->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->nama_paket,
            'price' => 'Rp ' . number_format((int) preg_replace('/[^0-9]/', '', (string) $b->harga_bundling), 0, ',', '.'),
        ])->values();
    @endphp
</div>

@script
    <script>
    {{-- SweetAlert picker untuk produk & paket bundling (data banyak → mudah dicari) --}}
    window.__ofProducts = {!! json_encode($ofProductsData) !!};
    window.__ofBundles = {!! json_encode($ofBundlesData) !!};

    if (!window.__ofPickerBound) {
            window.__ofPickerBound = true;

            const ofGlossy = {
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0', title: 'fw-bold' },
                buttonsStyling: false,
                showConfirmButton: false,
                showCloseButton: true,
                width: 480,
                padding: '1.25rem',
            };

            window.__ofPicker = function(title, items, onPick) {
                if (typeof Swal === 'undefined') return;
                const rows = items.length
                    ? items.map(it =>
                        `<button type="button" class="of-pick-item" data-id="${it.id}" data-search="${(it.name + ' ' + (it.sub || '')).toLowerCase()}">
                            ${it.name}${it.sub ? `<span class="op-sub">${it.sub}</span>` : ''}
                         </button>`).join('')
                    : '<div class="of-pick-empty">Tidak ada data</div>';

                Swal.fire({
                    title: title,
                    html: `<input id="ofPickSearch" class="form-control mb-2" placeholder="Ketik untuk mencari...">
                           <div id="ofPickList" class="of-pick-list">${rows}</div>`,
                    ...ofGlossy,
                    didOpen: () => {
                        const search = document.getElementById('ofPickSearch');
                        const listEl = document.getElementById('ofPickList');
                        if (search) {
                            search.addEventListener('input', () => {
                                const q = search.value.toLowerCase();
                                listEl.querySelectorAll('.of-pick-item').forEach(b => {
                                    b.style.display = b.dataset.search.includes(q) ? '' : 'none';
                                });
                            });
                            setTimeout(() => search.focus(), 100);
                        }
                        listEl.querySelectorAll('.of-pick-item').forEach(b => {
                            b.addEventListener('click', () => {
                                onPick(b.dataset.id);
                                Swal.close();
                            });
                        });
                    }
                });
            };

            window.ofProductPicker = function(btn, index) {
                const el = btn.closest('[wire\\:id]');
                if (!el) return;
                const cid = el.getAttribute('wire:id');
                window.__ofPicker('Pilih Produk Akun',
                    (window.__ofProducts || []).map(p => ({ id: p.id, name: p.name })),
                    (id) => Livewire.find(cid).call('setItemProduct', index, id));
            };

            window.ofBundlePicker = function(btn) {
                const el = btn.closest('[wire\\:id]');
                if (!el) return;
                const cid = el.getAttribute('wire:id');
                window.__ofPicker('Pilih Paket Bundling',
                    (window.__ofBundles || []).map(b => ({ id: b.id, name: b.name, sub: b.price })),
                    (id) => Livewire.find(cid).call('addBundleById', id));
            };
        }
    </script>
@endscript