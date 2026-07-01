<div>
    <form wire:submit.prevent="save">
        <div class="card border-0 shadow-sm rounded-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
            <div class="card-header bg-primary bg-opacity-10 p-3 border-0 rounded-top-4">
                <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-person-vcard me-2"></i>Informasi Pelanggan</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Nama Pelanggan</label>
                            <input class="form-control" type="text" wire:model="name" placeholder="nama pelanggan">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Pelangan</label>
                            <input class="form-control" type="email" wire:model="email" placeholder="email@example.com">
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">Nomor Handphone</label>
                            <input class="form-control" type="text" wire:model="phone" placeholder="082*********">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary">Status Member</label>
                        <select wire:model="statusMember" class="form-select form-control form-select" id="basicSelect">
                            <option value="">-- Pilih Status --</option>
                            <option value="non-active">Non-active</option>
                            <option value="active">Active</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tanggal_daftar" class="form-label">Tanggal Daftar</label>
                            <input id="tanggal_daftar" class="form-control bg-light" type="text" readonly
                                value="{{ $customer?->created_at ? $customer->created_at->translatedFormat('d F Y, H:i') : 'Otomatis saat data disimpan' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="total_point" class="form-label">Total Point</label>
                            <input id="total_point" class="form-control bg-light" type="text" readonly
                                value="{{ number_format($customer?->point ?? 0, 0, ',', '.') }} Poin (Senilai Rp {{ number_format(($customer?->point ?? 0) * 500, 0, ',', '.') }})">
                            <small class="text-muted">1 poin = Rp 500 · poin didapat tiap belanja kelipatan Rp 50.000</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kode_ref" class="form-label">Kode Refreal</label>
                            <input id="kode_ref" class="form-control bg-light" type="text" readonly
                                value="{{ $customer?->kode_ref ?: '-' }}">
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit"
                        class="btn btn-primary px-5 flex-grow-1 d-inline-flex align-items-center justify-content-center"
                        style="height: 52px;">
                        <i class="bi bi-check2-circle me-2 fs-5"></i>
                        <span>{{ $this->mode === 'create' ? 'Simpan Data' : 'Update Data' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ================== RIWAYAT ORDER PELANGGAN (berdasarkan nomor telepon) ================== --}}
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @if ($customer)
    @php
    $customerOrders = \App\Models\Order::with('items')
    ->whereHas('customer', function ($q) use ($customer) {
    $q->where('no_hp', $customer->no_hp);
    })
    ->latest()
    ->get();

    // Total keseluruhan yang benar-benar dibayarkan (pesanan yang sudah dibayar)
    $paidOrders = $customerOrders->filter(function ($o) {
    return $o->paid_at !== null || in_array($o->status, ['paid', 'processing', 'completed']);
    });
    $grandTotalPaid = $paidOrders->sum('total');
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mt-4" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px);">
        <div class="card-header bg-primary bg-opacity-10 p-3 border-0 rounded-top-4 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-primary fw-bold">
                <i class="bi bi-bag-check me-2"></i>Riwayat Pesanan
            </h5>
            <span class="badge rounded-pill bg-primary">{{ $customerOrders->count() }} Pesanan</span>
        </div>
        <div class="card-body p-4">
            @forelse ($customerOrders as $order)
            @if (!$loop->first)
            <hr class="my-3 opacity-25">@endif
            <div x-data="{ open: false }">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div>
                        <div class="fw-bold text-dark">
                            <i class="bi bi-receipt me-1 text-primary"></i>{{ $order->order_number }}
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>{{ $order->created_at->translatedFormat('d F Y, H:i') }}
                            <span class="mx-1">&middot;</span>
                            <i class="bi bi-box-seam me-1"></i>{{ $order->items->count() }} item
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-md-end">
                            <div class="fw-bold text-dark">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                            <div>{!! $order->getStatusBadge() !!}</div>
                        </div>
                        <button type="button" @click="open = !open"
                            class="btn btn-outline-secondary btn-sm rounded-pill d-inline-flex align-items-center gap-1"
                            :aria-expanded="open" title="Lihat item pesanan">
                            <i class="bi bi-list-ul"></i>
                            <span class="small">Item</span>
                            <i class="bi bi-chevron-down small" :style="open ? 'transform: rotate(180deg)' : ''"
                                style="transition: transform .2s ease;"></i>
                        </button>
                        <a wire:navigate href="{{ route('admin.pesanantoko.detail', $order) }}"
                            class="btn btn-outline-primary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center"
                            style="width: 38px; height: 38px;" title="Lihat detail pesanan">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>

                <div class="row g-2 mt-2 small">
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Kode Unik</div>
                        <div class="fw-semibold text-dark">{{ $order->unique_code ?? '-' }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Harga Awal</div>
                        <div class="fw-semibold text-dark">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Diskon</div>
                        <div class="fw-semibold text-danger">- Rp {{ number_format($order->total_discount, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted">Setelah Diskon</div>
                        <div class="fw-semibold text-success">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                    </div>
                </div>

                @php
                $promoCodes = array_filter($order->getAppliedPromoCodes());
                $appliedPromos = collect($order->applied_promos ?? []);
                // Flash sale & auto promo tidak punya kode, dideteksi dari tipe_promo
                $flashSales = $appliedPromos->where('tipe_promo', 'flash_sale')->pluck('nama_promo')->filter()->values();
                $autoPromos = $appliedPromos->where('tipe_promo', 'auto_promo')->pluck('nama_promo')->filter()->values();
                // Flag used_points/points_discount tidak tersimpan di order (bukan fillable),
                // jadi potongan poin disimpulkan dari sisa diskon di luar promo & referral.
                $pointsDiscount = max(0, (int) $order->total_discount - (int) $order->promo_discount - (int) $order->referral_discount);
                $pointsUsed = $pointsDiscount > 0 ? (int) round($pointsDiscount / 500) : 0;
                $referrer = $order->referral_code && $order->referrer_id ? \App\Models\Customer::find($order->referrer_id) : null;
                @endphp

                @if (!empty($promoCodes) || $flashSales->isNotEmpty() || $autoPromos->isNotEmpty() || $pointsUsed > 0 || $order->referral_code)
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @foreach ($flashSales as $fs)
                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger fw-semibold">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Flash Sale{{ $fs ? ': ' . $fs : '' }}
                    </span>
                    @endforeach

                    @foreach ($autoPromos as $ap)
                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary fw-semibold">
                        <i class="bi bi-tags-fill me-1"></i>Promo{{ $ap ? ': ' . $ap : '' }}
                    </span>
                    @endforeach

                    @foreach ($promoCodes as $kode)
                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning fw-semibold">
                        <i class="bi bi-tag-fill me-1"></i>Promo: {{ $kode }}
                    </span>
                    @endforeach

                    @if ($pointsUsed > 0)
                    <span class="badge rounded-pill bg-info bg-opacity-10 border fw-semibold" style="color: #0dcaf0; border-color: #0dcaf0 !important;">
                        <i class="bi bi-star-fill me-1"></i>{{ number_format($pointsUsed, 0, ',', '.') }} poin dipakai (Rp {{ number_format($pointsDiscount, 0, ',', '.') }})
                    </span>
                    @endif

                    @if ($order->referral_code)
                    @if ($referrer)
                    <a wire:navigate href="{{ route('admin.customer.edit', $referrer) }}"
                        class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-semibold text-decoration-none"
                        title="Lihat pemilik kode referral: {{ $referrer->nama }}">
                        <i class="bi bi-people-fill me-1"></i>Referral: {{ $order->referral_code }}
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                    @else
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success fw-semibold">
                        <i class="bi bi-people-fill me-1"></i>Referral: {{ $order->referral_code }}
                    </span>
                    @endif
                    @endif
                </div>
                @endif

                <div x-show="open" x-collapse.duration.300ms x-cloak class="mt-3">
                    <div class="border rounded-3 p-2" style="background: rgba(248, 249, 250, 0.7);">
                        @forelse ($order->items as $oi)
                        <div class="d-flex align-items-center gap-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="d-inline-flex align-items-center justify-content-center rounded bg-primary bg-opacity-10 text-primary flex-shrink-0"
                                style="width: 36px; height: 36px;"><i class="bi bi-box-seam"></i></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small text-dark">
                                    {{ $oi->product_name }}
                                    @if ($oi->duration_value && $oi->duration_type)
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary ms-1">
                                        <i class="bi bi-clock-history me-1"></i>{{ $oi->getDurationLabel() }}
                                    </span>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $oi->quantity }} x Rp {{ number_format($oi->price, 0, ',', '.') }}</small>
                            </div>
                            <div class="fw-semibold small text-dark">Rp {{ number_format($oi->subtotal, 0, ',', '.') }}</div>
                        </div>
                        @empty
                        <div class="text-muted small text-center py-2">Tidak ada item pada pesanan ini.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-bag-x fs-1 opacity-50 d-block mb-2"></i>
                <p class="mb-0">Pelanggan ini belum pernah melakukan pemesanan.</p>
            </div>
            @endforelse

            @if ($customerOrders->isNotEmpty())
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mt-4 p-3 rounded-3"
                style="background: linear-gradient(135deg, rgba(108, 99, 255, 0.12), rgba(78, 70, 229, 0.12));">
                <span class="fw-bold text-primary">
                    <i class="bi bi-cash-stack me-2"></i>Total Keseluruhan Dibayarkan
                    <small class="text-muted fw-normal">({{ $paidOrders->count() }} pesanan terbayar)</small>
                </span>
                <span class="fw-bold fs-4 text-primary">Rp {{ number_format($grandTotalPaid, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>