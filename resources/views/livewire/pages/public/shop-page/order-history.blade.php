<div>
    {{-- Sembunyikan garis background di halaman riwayat --}}
    <style>
        #ph-page-lines { display: none !important; }
    </style>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-clock-history"></i> Riwayat</span>
                <h1>Riwayat Pesanan</h1>
                <p>Semua pesanan Anda tampil di sini. Ganti perangkat? Pulihkan lewat Nomor HP.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Toko</a></li>
                    <li class="current">Riwayat Pesanan</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->
    <div class="container py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
            <p class="oh-note mb-0">
                <i class="bi bi-info-circle"></i>
                Riwayat tersimpan di perangkat ini. Pindah perangkat, ganti browser, atau hapus cookie?
                Pulihkan lewat <b>Nomor HP</b>.
            </p>
            <button type="button" class="ph-empty-btn ghost flex-shrink-0" data-bs-toggle="modal"
                data-bs-target="#restoreModal">
                <i class="bi bi-arrow-repeat"></i> Pulihkan Riwayat
            </button>
        </div>

        @if($this->myOrders->total() > 0)
        <p class="small text-muted mb-2"><i class="bi bi-receipt me-1"></i>{{ $this->myOrders->total() }} pesanan ditemukan</p>
        <div class="accordion" id="orderAccordion">
            @foreach($this->myOrders as $order)
            <div class="accordion-item mb-3 border rounded overflow-hidden">
                <h2 class="accordion-header" id="heading{{ $order->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $order->id }}">
                        <div class="d-flex w-100 justify-content-between align-items-center me-3">
                            <div class="d-flex flex-column gap-2">
                                <span class="fw-bold text-dark">{{ $order->order_number }}</span>
                                <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                @php
                                    $totalAcc = $order->items->count();
                                    $habisCount = $order->items->filter(fn ($i) => $i->isHabis())->count();
                                    $soonCount = $order->items->filter(fn ($i) => ! $i->isHabis() && $i->end_date && $i->isExpiringSoon())->count();
                                @endphp
                                @if ($habisCount > 0 && $habisCount === $totalAcc)
                                    <span class="ph-order-badge is-habis"><i class="bi bi-x-circle-fill"></i>
                                        {{ $totalAcc > 1 ? 'Semua Akun Habis' : 'Akun Habis' }}</span>
                                @elseif ($habisCount > 0)
                                    <span class="ph-order-badge is-mixed"><i class="bi bi-exclamation-triangle-fill"></i>
                                        {{ $habisCount }}/{{ $totalAcc }} Akun Habis</span>
                                @elseif ($soonCount > 0)
                                    <span class="ph-order-badge is-soon"><i class="bi bi-clock-fill"></i> Segera
                                        Berakhir</span>
                                @endif
                            </div>
                            <div>
                                @php
                                $badgeClass = match($order->status) {
                                'paid', 'completed' => 'bg-success',
                                'pending' => 'bg-warning text-dark',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill me-2">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <span class="fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapse{{ $order->id }}" class="accordion-collapse collapse" data-bs-parent="#orderAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="text-muted">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Durasi</th>
                                        <th class="text-end">Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td class="py-2">
                                            <span class="fw-medium">{{ $item->product_name }}</span>
                                            @if ($item->isHabis())
                                                <span class="ph-sub-badge is-habis"><i class="bi bi-x-circle-fill"></i> Habis</span>
                                            @elseif ($item->end_date && $item->isExpiringSoon())
                                                <span class="ph-sub-badge is-soon"><i class="bi bi-clock-fill"></i> {{ $item->getRemainingLabel() }}</span>
                                            @elseif ($item->end_date)
                                                <span class="ph-sub-badge is-active"><i class="bi bi-check-circle-fill"></i> Aktif · {{ $item->getRemainingLabel() }}</span>
                                            @else
                                                <span class="ph-sub-badge is-wait"><i class="bi bi-hourglass-split"></i> Menunggu aktivasi</span>
                                            @endif
                                            @if ($item->end_date)
                                                <div class="ph-sub-meta">
                                                    <i class="bi bi-calendar-event"></i>
                                                    {{ $item->isHabis() ? 'Berakhir' : 'Berlaku s.d.' }}
                                                    {{ \Illuminate\Support\Carbon::parse($item->end_date)->translatedFormat('d M Y') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center py-2">
                                            {{ $item->duration_value }} {{ ucfirst($item->duration_type) }}
                                        </td>
                                        <td class="text-end py-2">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-top oh-summary">
                                    <tr>
                                        <td colspan="2" class="text-end pt-3">Subtotal</td>
                                        <td class="text-end pt-3">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @if ((float) $order->total_discount > 0)
                                        <tr class="oh-sum-disc">
                                            <td colspan="2" class="text-end">Diskon</td>
                                            <td class="text-end">− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    @if ((int) $order->unique_code > 0)
                                        <tr class="oh-sum-unique">
                                            <td colspan="2" class="text-end">Kode Unik</td>
                                            <td class="text-end">+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold pt-2">Total Bayar</td>
                                        <td class="text-end fw-bold pt-2 oh-total">
                                            Rp {{ number_format($order->total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @php
                            $promos = collect($order->applied_promos ?? []);
                            $hasReferral = ! empty($order->referral_code) || (float) $order->referral_discount > 0;
                            $hasPoints = (int) ($order->used_points ?? 0) > 0 || (float) ($order->points_discount ?? 0) > 0;
                            $hasAnyPromo = $promos->isNotEmpty() || $hasReferral || $hasPoints;
                        @endphp

                        @if ($hasAnyPromo)
                            <div class="oh-promo">
                                <div class="oh-promo-title"><i class="bi bi-ticket-perforated-fill"></i> Promo Digunakan</div>
                                    <div class="oh-promo-list">
                                        @foreach ($promos as $p)
                                            @php
                                                $tipe = $p['tipe_promo'] ?? '';
                                                [$label, $cls, $icon] = match ($tipe) {
                                                    'flash_sale' => ['Flash Sale', 'flash', 'bi-lightning-charge-fill'],
                                                    'kode_promo' => ['Kode Promo', 'kode', 'bi-tag-fill'],
                                                    'auto_promo' => ['Promo Otomatis', 'auto', 'bi-magic'],
                                                    default => ['Promo', 'auto', 'bi-gift-fill'],
                                                };
                                            @endphp
                                            <div class="oh-promo-item">
                                                <span class="oh-promo-tag {{ $cls }}"><i class="bi {{ $icon }}"></i> {{ $label }}</span>
                                                <span class="oh-promo-name">
                                                    {{ $p['nama_promo'] ?? '-' }}
                                                    @if (! empty($p['kode_promo']))
                                                        <code class="oh-code">{{ $p['kode_promo'] }}</code>
                                                    @endif
                                                </span>
                                                @if (! empty($p['jumlah_diskon']))
                                                    <span class="oh-promo-amt">− Rp {{ number_format($p['jumlah_diskon'], 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        @endforeach

                                        @if ($hasReferral)
                                            <div class="oh-promo-item">
                                                <span class="oh-promo-tag referral"><i class="bi bi-people-fill"></i> Referral</span>
                                                <span class="oh-promo-name">
                                                    @if ($order->referral_code)
                                                        <code class="oh-code">{{ $order->referral_code }}</code>
                                                    @endif
                                                </span>
                                                @if ((float) $order->referral_discount > 0)
                                                    <span class="oh-promo-amt">− Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        @if ($hasPoints)
                                            <div class="oh-promo-item">
                                                <span class="oh-promo-tag point"><i class="bi bi-star-fill"></i> Poin</span>
                                                <span class="oh-promo-name">{{ number_format((int) $order->used_points, 0, ',', '.') }} poin</span>
                                                @if ((float) ($order->points_discount ?? 0) > 0)
                                                    <span class="oh-promo-amt">− Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($this->myOrders->hasPages())
        <div class="mt-4 ph-pagination">
            {{ $this->myOrders->links('pagination.ph') }}
        </div>
        @endif
        @else
        <div class="ph-empty py-4">
            <div class="ph-empty-art">
                <svg viewBox="0 0 240 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img"
                    aria-label="Belum ada riwayat pesanan">
                    <defs>
                        <radialGradient id="peGlowH" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#fba919" stop-opacity=".55" />
                            <stop offset="70%" stop-color="#fba919" stop-opacity="0" />
                        </radialGradient>
                        <linearGradient id="peDoc" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#fbc25a" />
                            <stop offset="100%" stop-color="#f26522" />
                        </linearGradient>
                    </defs>
                    <ellipse class="pe-glow" cx="120" cy="108" rx="78" ry="78" fill="url(#peGlowH)" />
                    <ellipse class="pe-shadow" cx="120" cy="184" rx="54" ry="8" fill="#e15a18" />

                    <g transform="translate(46,66)"><path class="pe-spark s1" d="M0,-7 L1.8,-1.8 7,0 1.8,1.8 0,7 -1.8,1.8 -7,0 -1.8,-1.8Z" fill="#fba919" /></g>
                    <g transform="translate(198,80)"><path class="pe-spark s2" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f26522" /></g>
                    <g transform="translate(56,150)"><path class="pe-spark s4" d="M0,-6 L1.5,-1.5 6,0 1.5,1.5 0,6 -1.5,1.5 -6,0 -1.5,-1.5Z" fill="#f4772b" /></g>

                    <g class="pe-float">
                        <path d="M80 50 Q80 44 86 44 H154 Q160 44 160 50 V148 L150 142 L140 148 L130 142 L120 148 L110 142 L100 148 L90 142 L80 148 Z"
                            fill="#ffffff" stroke="url(#peDoc)" stroke-width="2.5" stroke-linejoin="round" />
                        <rect x="96" y="58" width="48" height="9" rx="4" fill="url(#peDoc)" />
                        <rect x="96" y="79" width="48" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="90" width="34" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="105" width="48" height="5" rx="2.5" fill="#f1e6d8" />
                        <rect x="96" y="116" width="28" height="5" rx="2.5" fill="#f1e6d8" />
                    </g>

                    <g class="pe-float-2">
                        <circle cx="164" cy="126" r="21" fill="#ffffff" stroke="url(#peDoc)" stroke-width="3" />
                        <path d="M164 126 V114 M164 126 L173 130" stroke="#f26522" stroke-width="3"
                            stroke-linecap="round" />
                        <circle cx="164" cy="126" r="2.6" fill="#f26522" />
                    </g>
                </svg>
            </div>
            <h3 class="ph-empty-title">Belum ada riwayat pesanan</h3>
            <p class="ph-empty-sub">Pesanan Anda akan muncul di sini secara otomatis. Memesan lewat perangkat lain?
                Gunakan <strong>Pulihkan Riwayat</strong>.</p>
            <div class="ph-empty-actions">
                <a href="{{ route('shop.index') }}" class="ph-empty-btn"><i class="bi bi-bag"></i> Mulai Belanja</a>
                <button type="button" class="ph-empty-btn ghost" data-bs-toggle="modal" data-bs-target="#restoreModal">
                    <i class="bi bi-arrow-repeat"></i> Pulihkan Riwayat
                </button>
            </div>
        </div>
        @endif

        <div wire:ignore.self class="modal fade restore-modal" id="restoreModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-head">
                        <span class="re-eyebrow"><i class="bi bi-arrow-repeat"></i> Pulihkan</span>
                        <h5>Pulihkan Riwayat Pesanan</h5>
                        <p>Tampilkan pesanan Anda di perangkat ini menggunakan Nomor HP.</p>
                        <button type="button" class="modal-close-x" data-bs-dismiss="modal" aria-label="Tutup"><i
                                class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="restoreSession">
                            <div class="mb-3">
                                <label class="form-label">Nomor WhatsApp <span class="req">*wajib</span></label>
                                <input type="number" wire:model="phoneNumber"
                                    class="form-control @error('phoneNumber') is-invalid @enderror"
                                    placeholder="0821*********">
                                @error('phoneNumber')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kode Pesanan <span class="opt">(opsional)</span></label>
                                <input type="text" wire:model="invoiceCode"
                                    class="form-control @error('invoiceCode') is-invalid @enderror"
                                    placeholder="Kosongkan untuk melihat semua pesanan">
                                @error('invoiceCode')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="re-hint">
                                <i class="bi bi-info-circle"></i>
                                <span>Isi <b>Nomor HP saja</b> untuk melihat <b>semua riwayat</b> pesanan Anda. Tambahkan
                                    <b>Kode Pesanan</b> bila ingin menampilkan <b>satu pesanan</b> tertentu saja.</span>
                            </div>

                            <button type="submit" class="re-submit">
                                <span wire:loading.remove wire:target="restoreSession"><i class="bi bi-arrow-repeat"></i>
                                    Tampilkan Riwayat</span>
                                <span wire:loading wire:target="restoreSession">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Memproses...
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    const restoreModalEl = document.getElementById('restoreModal');
    const restoreModal = new bootstrap.Modal(restoreModalEl);

    $wire.on('restore-success', (data) => {
        const modalInstance = bootstrap.Modal.getInstance(restoreModalEl);
        if (modalInstance) modalInstance.hide();

        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            html:
                '<div class="ph-toast">' +
                  '<span class="ph-toast-ic"><i class="bi bi-clock-history"></i></span>' +
                  '<div class="ph-toast-txt">' +
                    '<strong>Berhasil</strong>' +
                    '<span>' + (data[0].message || 'Riwayat pesanan ditampilkan.') + '</span>' +
                  '</div>' +
                '</div>',
            customClass: { popup: 'ph-toast-popup' },
            didClose: () => { window.location.reload(); }
        });
    });
</script>
@endscript