<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4 mb-4 fixed-header-card">
        <div class="card-body p-4 d-flex align-items-center">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 header-action w-100">
                <div class="title-wrapper text-center text-md-start w-100">
                    <h3 class="gradient-text fw-bold mb-1">Detail Pesanan {{ $order->order_number }}</h3>
                    <div class="breadcrumb-custom d-flex justify-content-center justify-content-md-start">
                        @php
                        $breadcrumbs = [
                        ['name' => 'Beranda', 'url' => route('admin.dashboard')],
                        ['name' => 'Data Pesanan Toko', 'url' => route('admin.pesanantoko.index')],
                        ['name' => 'Detail Pesanan'],
                        ];
                        @endphp
                        <x-breadcrumb :items="$breadcrumbs" />
                    </div>
                </div>
                <a href="{{ $order->getReceiptUrl() }}" target="_blank"
                    class="btn btn-outline-primary d-flex align-items-center justify-content-center px-4 flex-shrink-0">
                    <i class="bi bi-receipt"></i>
                    <span class="ms-2 text-nowrap">Lihat Struk</span>
                </a>
            </div>
        </div>
    </div>

    <style>
        .detail-info-card {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(248, 249, 255, 0.9));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
            height: 100%;
        }

        .detail-info-card .info-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #fff;
            flex-shrink: 0;
        }

        /* Pusatkan ikon Bootstrap (bi) yang punya line-height bawaan */
        .detail-info-card .info-icon i.bi {
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .detail-info-card .info-icon i.bi::before {
            display: block;
            line-height: 1;
        }

        .method-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .85rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 700;
            line-height: 1;
            color: #fff;
        }

        .method-chip i {
            font-size: .95rem;
            line-height: 1;
        }

        .method-flash {
            background: linear-gradient(135deg, #f43f5e, #e11d48);
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.30);
        }

        .method-promo {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 4px 12px rgba(78, 70, 229, 0.30);
        }

        .method-point {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.30);
        }

        .method-referral {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.30);
        }

        .method-none {
            background: #e2e8f0;
            color: #64748b;
        }

        .info-icon.bg-grad-purple {
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            box-shadow: 0 6px 14px rgba(78, 70, 229, 0.35);
        }

        .info-icon.bg-grad-green {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
        }

        .detail-info-card .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: .55rem 0;
            border-bottom: 1px dashed rgba(108, 99, 255, 0.12);
        }

        .detail-info-card .info-row:last-child {
            border-bottom: none;
        }

        .detail-info-card .info-label {
            color: #6b7280;
            font-size: .9rem;
            font-weight: 500;
        }

        .detail-info-card .info-value {
            color: #1e293b;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .items-table thead th {
            background: linear-gradient(135deg, rgba(108, 99, 255, 0.10), rgba(78, 70, 229, 0.08));
            color: #4e46e5;
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            border: none;
            white-space: nowrap;
        }

        .items-table tbody td {
            vertical-align: middle;
        }

        .summary-card {
            border: 1px solid rgba(108, 99, 255, 0.12);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 255, 0.95));
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.08);
        }

        .summary-card .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .5rem 0;
            font-size: .95rem;
            color: #475569;
        }

        .summary-card .summary-total {
            border-top: 2px dashed rgba(108, 99, 255, 0.20);
            margin-top: .35rem;
            padding-top: .85rem;
        }
    </style>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="detail-info-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="info-icon bg-grad-purple"><i class="bi bi-receipt"></i></span>
                    <h5 class="fw-bold mb-0">Data Pesanan</h5>
                </div>
                <div class="info-row">
                    <span class="info-label">No. Order</span>
                    <span class="info-value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value">{{ $order->created_at->format('d-m-Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        @php
                        $color = 'secondary';
                        if ($order->status == 'pending') $color = 'warning';
                        if ($order->status == 'processing') $color = 'info';
                        if ($order->status == 'paid') $color = 'success';
                        if ($order->status == 'cancelled') $color = 'danger';
                        if ($order->status == 'completed') $color = 'primary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ strtoupper($order->status) }}</span>
                    </span>
                </div>
                @php
                $promos = collect($order->applied_promos ?? []);
                $usedFlash = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'flash_sale');
                $usedKodePromo = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'kode_promo');
                $usedAutoPromo = $promos->contains(fn($p) => ($p['tipe_promo'] ?? '') === 'auto_promo');
                $usedPoint = $order->used_points || $order->points_discount > 0;
                $usedReferral = !empty($order->referral_code) || $order->referral_discount > 0;
                $adaDiskon = $usedFlash || $usedKodePromo || $usedAutoPromo || $usedPoint || $usedReferral;
                @endphp
                <div class="info-row">
                    <span class="info-label">Diskon Dipakai</span>
                    <span class="info-value d-flex flex-wrap gap-2 justify-content-end">
                        @if ($usedFlash)
                        <span class="method-chip method-flash"><i class="bi bi-lightning-charge-fill"></i>Flash Sale</span>
                        @endif
                        @if ($usedKodePromo)
                        <span class="method-chip method-promo"><i class="bi bi-ticket-perforated-fill"></i>Kode Promo</span>
                        @endif
                        @if ($usedAutoPromo)
                        <span class="method-chip method-promo"><i class="bi bi-tags-fill"></i>Promo</span>
                        @endif
                        @if ($usedPoint)
                        <span class="method-chip method-point"><i class="bi bi-coin"></i>Poin</span>
                        @endif
                        @if ($usedReferral)
                        <span class="method-chip method-referral"><i class="bi bi-people-fill"></i>Referral</span>
                        @endif
                        @unless ($adaDiskon)
                        <span class="method-chip method-none"><i class="bi bi-dash-circle"></i>Tanpa Diskon</span>
                        @endunless
                    </span>
                </div>
                @if ($usedReferral && !empty($order->referral_code))
                <div class="info-row">
                    <span class="info-label">Kode Referral</span>
                    <span class="info-value">{{ $order->referral_code }}</span>
                </div>
                @endif
                @if ($usedKodePromo)
                <div class="info-row">
                    <span class="info-label">Kode Promo</span>
                    <span class="info-value">
                        {{ collect($order->getAppliedPromoCodes())->filter()->implode(', ') ?: '-' }}
                    </span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Catatan Pelanggan</span>
                    <span class="info-value">
                        @if (filled($order->customer_notes))
                        {{ $order->customer_notes }}
                        @else
                        <span class="text-muted fw-normal">- tidak ada -</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="detail-info-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="info-icon bg-grad-green"><i class="bi bi-person-circle"></i></span>
                    <h5 class="fw-bold mb-0">Data Pembeli</h5>
                </div>
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $order->customer->nama ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $order->customer->email ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Telepon</span>
                    <span class="info-value">{{ $order->customer->no_hp ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-box-seam text-primary fs-5"></i>
                <h5 class="fw-bold mb-0">Item Pesanan</h5>
            </div>
            <div class="table-responsive">
                <table class="table align-middle items-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Durasi</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Masa Aktif</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->items as $item)
                        <tr>
                            <td class="fw-semibold">
                                {{ $item->product->nama_akun ?? '-' }}
                                @if ($item->ebooks->count() || $item->bonus_description)
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach ($item->ebooks as $eb)
                                    <a href="{{ $eb->getViewUrl() }}" target="_blank"
                                        class="badge bg-success-subtle text-success border border-success text-decoration-none"
                                        title="Baca {{ $eb->judul }} (view-only)">
                                        <i class="bi bi-book"></i> {{ $eb->judul }}
                                    </a>
                                    @endforeach
                                    @if ($item->bonus_description)
                                    <span class="badge bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-gift"></i> {{ $item->bonus_description }}
                                    </span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">
                                {{ $item->duration_value }} {{ $item->duration_type }}
                                @if ($item->hasBonusDuration())
                                <small class="d-block text-success fw-semibold">+ {{ $item->bonus_duration_value }} {{ $item->bonus_duration_type }} bonus</small>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                            <td class="text-center">
                                {!! $item->getDeliveryStatusBadge() !!}
                                @if ($item->processed_by && $item->processed_at)
                                <small class="d-block text-muted mt-1" style="line-height:1.25;">
                                    <i class="bi bi-person-check"></i> {{ $item->processedBy->name ?? 'Admin' }}
                                </small>
                                <small class="d-block text-muted" style="font-size:.72rem;">
                                    {{ \Carbon\Carbon::parse($item->processed_at)->translatedFormat('d M Y, H:i') }} WIB
                                </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <div>{!! $item->getSubscriptionStatusBadge() !!}</div>
                                @if ($item->end_date)
                                <small class="d-block text-muted mt-1">
                                    s/d {{ \Carbon\Carbon::parse($item->end_date)->translatedFormat('d M Y') }}
                                </small>
                                <small class="d-block fw-semibold {{ $item->isHabis() ? 'text-danger' : 'text-success' }}">
                                    {{ $item->getRemainingLabel() }}
                                </small>
                                @if ($item->isHabis())
                                @if ($item->habis_notified_at)
                                <span class="badge bg-success-subtle text-success border border-success mt-1"
                                    title="Diberi tahu {{ $item->habis_notified_at->translatedFormat('d M Y H:i') }}">
                                    <i class="bi bi-check2-circle"></i> Sudah diberi tahu
                                </span>
                                @else
                                <span class="badge bg-warning-subtle text-warning border border-warning mt-1">
                                    <i class="bi bi-exclamation-circle"></i> Belum diberi tahu
                                </span>
                                @endif
                                @endif
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary p-2 notes-btn"
                                    title="lihat catatan" data-account="{{ $item->account_notes }}"
                                    data-processing="{{ $item->processing_notes }}">
                                    <i class="bi bi-journal-text"></i>
                                </button>
                                <a wire:navigate href="{{ route('admin.pesanantoko.process', $item->id) }}"
                                    class="btn btn-sm btn-primary p-2" title="proses pesanan">
                                    <i class="bi bi-gear"></i></a>
                                @if ($item->delivery_status != 'pending')
                                <button type="button" class="btn btn-sm btn-outline-secondary p-2 change-sub-btn"
                                    title="ubah status langganan" data-id="{{ $item->id }}"
                                    data-current="{{ $item->subscription_status }}">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                <button class="btn btn-sm btn-success send-wa-btn p-2" title="kirim akun ke pembeli"
                                    type="button" data-id="{{ $item->id }}"
                                    data-idTransaksi="{{ $order->order_number }}"
                                    data-nama="{{ $order->customer->nama }}"
                                    data-wa="{{ $order->customer->no_hp }}"
                                    data-akun="{{ $item->dataakun?->nama_akun ?? '-' }}"
                                    data-tglorder="{{ $order->created_at->translatedFormat('d F Y') }}"
                                    data-total="{{ number_format($order->total, 0, ',', '.') }}"
                                    data-pemesanan="{{ \Carbon\Carbon::parse($item->start_date)->format('d F Y') }}"
                                    data-berakhir="{{ \Carbon\Carbon::parse($item->end_date)->format('d F Y') }}"
                                    data-username="{{ $item->account_username }}"
                                    data-password="{{ $item->account_password }}"
                                    data-linkakses="{{ $item->account_link }}"
                                    data-catatan="{{ $item->account_notes }}"
                                    data-struk="{{ $order->getReceiptUrl() }}"
                                    data-durasi="{{ $item->getFullDurationLabel() }}"
                                    data-bonus="{{ $item->bonus_description }}"
                                    data-ebooks="{{ $item->ebooks->map(fn ($e) => $e->judul . ' - ' . $e->getViewUrl())->implode('||') }}">
                                    <i class="bi bi-whatsapp"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="empty-state-icon-wrapper mb-3">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1" style="color: #1e293b !important;">
                                        Belum Ada Item Pesanan
                                    </h5>
                                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                                        Pesanan ini belum memiliki item produk.
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($order->items->count())
            <div class="row justify-content-end mt-3">
                <div class="col-lg-5 col-md-7">
                    <div class="summary-card p-4">
                        <div class="summary-row">
                            <span>Total</span>
                            <span class="fw-semibold">Rp {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}</span>
                        </div>
                        @if($order->promo_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-tags-fill me-1"></i>Diskon Promo</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->promo_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->points_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-coin me-1"></i>Diskon Poin</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->referral_discount > 0)
                        <div class="summary-row text-danger">
                            <span><i class="bi bi-people-fill me-1"></i>Diskon Referral</span>
                            <span class="fw-semibold">- Rp {{ number_format($order->referral_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->unique_code > 0)
                        <div class="summary-row">
                            <span>Kode Unik</span>
                            <span class="fw-semibold">+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="summary-row summary-total">
                            <span class="fw-bold text-dark">TOTAL PEMBAYARAN</span>
                            <span class="fw-bolder text-success fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@push('scripts')
<style>
    .swal-wa-list {
        display: flex;
        flex-direction: column;
        gap: .65rem;
        margin-top: .5rem;
    }

    .swal-wa-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        width: 100%;
        padding: .9rem 1.1rem;
        border-radius: 14px;
        border: 1px solid rgba(108, 99, 255, 0.18);
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #1e293b;
        font-weight: 600;
        font-size: 1rem;
        text-align: left;
        cursor: pointer;
        transition: all .2s ease;
    }

    .swal-wa-item:hover {
        background: linear-gradient(135deg, #6c63ff, #4e46e5);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(78, 70, 229, 0.35);
        border-color: transparent;
    }

    .swal-wa-item .wa-emoji {
        font-size: 1.25rem;
        line-height: 1;
    }

    .swal-wa-item.sub-item-active {
        background: linear-gradient(135deg, #6c63ff, #4e46e5);
        color: #fff;
        border-color: transparent;
        box-shadow: 0 6px 14px rgba(78, 70, 229, 0.30);
    }
</style>
<script>
    const waGlossyConfig = {
        background: 'rgba(255, 255, 255, 0.8)',
        backdrop: 'rgba(139, 92, 246, 0.15)',
        customClass: {
            popup: 'swal-glossy-popup',
            title: 'swal-glossy-title'
        },
        buttonsStyling: false
    };

    let waData = {};

    document.addEventListener('livewire:init', () => {
        Livewire.on('close-wa-modal', () => {
            if (window.Swal) Swal.close();
        });

        Livewire.on('subscription-status-updated', () => {
            Swal.fire({
                title: 'Status Diperbarui',
                text: 'Status langganan akun berhasil diubah.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                ...waGlossyConfig
            });
        });
    });

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    document.addEventListener('click', function(e) {
        const notesBtn = e.target.closest('.notes-btn');
        if (notesBtn) {
            const account = notesBtn.dataset.account?.trim();
            const processing = notesBtn.dataset.processing?.trim();

            const block = (icon, title, text, color) =>
                `<div style="text-align:left;border:1px solid rgba(108,99,255,.18);border-radius:14px;padding:.9rem 1.1rem;margin-bottom:.65rem;background:rgba(255,255,255,.55);">
                    <div style="font-weight:700;color:${color};margin-bottom:.35rem;"><i class="bi ${icon}"></i> ${title}</div>
                    <div style="color:#334155;">${text ? escapeHtml(text) : '<span style=\'color:#94a3b8\'>- tidak ada -</span>'}</div>
                 </div>`;

            Swal.fire({
                title: 'Catatan Pesanan',
                html: `<div class="swal-wa-list">
                        ${block('bi-person-heart', 'Catatan untuk Pelanggan', account, '#059669')}
                        ${block('bi-shield-lock', 'Catatan Internal (Admin)', processing, '#4e46e5')}
                    </div>`,
                showConfirmButton: false,
                showCloseButton: true,
                width: 480,
                padding: '1.5em',
                ...waGlossyConfig
            });
            return;
        }

        const subBtn = e.target.closest('.change-sub-btn');
        if (subBtn) {
            const itemId = subBtn.dataset.id;
            const current = subBtn.dataset.current;
            const options = {
                baru: '🆕 Baru',
                perpanjang: '🔄 Perpanjang',
                pengganti: '♻️ Pengganti',
                habis: '⛔ Habis',
            };

            let html = '<div class="swal-wa-list">';
            Object.keys(options).forEach((val) => {
                const activeClass = val === current ? ' sub-item-active' : '';
                html +=
                    `<button type="button" class="swal-wa-item${activeClass}" data-sub-val="${val}">${options[val]}</button>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Ubah Status Langganan',
                html: html,
                showConfirmButton: false,
                showCloseButton: true,
                width: 460,
                padding: '1.5em',
                ...waGlossyConfig,
                didOpen: () => {
                    Swal.getPopup().querySelectorAll('.swal-wa-item').forEach((item) => {
                        item.addEventListener('click', () => {
                            const val = item.dataset.subVal;
                            const component = subBtn.closest('[wire\\:id]');
                            if (component) {
                                Livewire.find(component.getAttribute('wire:id'))
                                    .call('updateSubscriptionStatus', itemId, val);
                            }
                            Swal.close();
                        });
                    });
                }
            });
            return;
        }

        const button = e.target.closest('.send-wa-btn');
        if (!button) return;

        waData = {
            id: button.dataset.id,
            idtransaksi: button.dataset.idtransaksi,
            noWa: button.dataset.wa,
            nama: button.dataset.nama,
            akun: button.dataset.akun,
            tglorder: button.dataset.tglorder,
            total: button.dataset.total,
            pemesanan: button.dataset.pemesanan,
            berakhir: button.dataset.berakhir,
            username: button.dataset.username,
            password: button.dataset.password,
            linkakses: button.dataset.linkakses,
            catatan: button.dataset.catatan,
            struk: button.dataset.struk,
            bonus: button.dataset.bonus,
            ebooks: button.dataset.ebooks,
        };

        Swal.fire({
            title: 'Kirim WhatsApp',
            html: `
                <p class="text-muted mb-2">Pilih jenis pesan yang akan dikirim ke pembeli:</p>
                <div class="swal-wa-list">
                    <button type="button" class="swal-wa-item" data-wa-type="pengiriman">
                        <span class="wa-emoji">📦</span> Pengiriman Akun
                    </button>
                    <button type="button" class="swal-wa-item" data-wa-type="pembaharuan">
                        <span class="wa-emoji">♻️</span> Pembaharuan Akun
                    </button>
                    <button type="button" class="swal-wa-item" data-wa-type="habis">
                        <span class="wa-emoji">⛔</span> Akun Habis
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 460,
            padding: '1.5em',
            ...waGlossyConfig,
            didOpen: () => {
                Swal.getPopup().querySelectorAll('.swal-wa-item').forEach((item) => {
                    item.addEventListener('click', () => {
                        kirimWa(item.dataset.waType);
                        Swal.close();
                    });
                });
            }
        });
    });

    function kirimWa(type) {
        const idItem = waData.id;
        const idtransaksi = waData.idtransaksi;
        const nama = waData.nama;
        const noWa = waData.noWa;
        const akun = waData.akun;
        const tglorder = waData.tglorder;
        const total = waData.total;
        const pemesanan = waData.pemesanan;
        const berakhir = waData.berakhir;
        const username = waData.username;
        const password = waData.password;
        const linkakses = waData.linkakses;
        // Emoji dibangun dari code point agar tidak rusak ("?") karena encoding
        const EMO = {
            bullet: String.fromCodePoint(0x2022), // •
            pin: String.fromCodePoint(0x1F4CC), // 📌
            receipt: String.fromCodePoint(0x1F9FE), // 🧾
            gift: String.fromCodePoint(0x1F381), // 🎁
            book: String.fromCodePoint(0x1F4DA), // 📚
        };

        const catatan = (waData.catatan || '').trim();
        const blokCatatan = catatan ? `\n\n${EMO.pin} Catatan: ${catatan}` : '';
        const struk = (waData.struk || '').trim();
        const blokStruk = struk ? `\n\n${EMO.receipt} *Struk pembelian Anda:* ${struk}` : '';
        const bonus = (waData.bonus || '').trim();
        const ebooksRaw = (waData.ebooks || '').trim();
        const ebookList = ebooksRaw ? ebooksRaw.split('||') : [];
        let blokBonus = '';
        if (bonus || ebookList.length) {
            blokBonus = `\n\n${EMO.gift} *BONUS EBOOK/PANDUAN UNTUK ANDA*`;
            ebookList.forEach((e) => {
                const [judul, url] = e.split(' - ');
                blokBonus += `\n${EMO.book} *${judul}:* ${url}`;
            });
            if (bonus) blokBonus += `\n${bonus}`;
        }

        let pesan = '';

        if (type === 'pengiriman') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Anda baru saja order akun *${akun}* pada tanggal *${tglorder}*.
Untuk akun *${akun}* yang bisa anda gunakan mulai tanggal *${pemesanan}* dengan masa aktif sampai tanggal *${berakhir}*.
Total pembayaran anda *Rp ${total}*. Untuk Detail akun sebagai berikut:

${EMO.bullet} Username: *${username}*
${EMO.bullet} Password: *${password}*
${EMO.bullet} Link Login: ${linkakses}${blokCatatan}${blokBonus}${blokStruk}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'pembaharuan') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun *${akun}* yang anda order pada tanggal *${tglorder}* dengan masa aktif sampai tanggal *${berakhir}* terdapat pembaharuan akun *${akun}*. Untuk detail akunya sebagai berikut:

${EMO.bullet} Username: *${username}*
${EMO.bullet} Password: *${password}*
${EMO.bullet} Link Login: ${linkakses}${blokCatatan}${blokBonus}

Jika ada kendala, jangan ragu untuk menghubungi kami.
Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        } else if (type === 'habis') {
            pesan =
                `ID Transaksi: ${idtransaksi}

Halo ${nama},
Akun *${akun}* yang anda order pada tanggal *${tglorder}* dengan masa aktif sampai *${berakhir}* *SUDAH HABIS MASA AKTIFNYA*. Jika Anda ingin memperpanjang akun *${akun}* Anda, silakan hubungi kami.

Terima kasih telah menggunakan layanan kami.

Salam hangat,
Phoenix Digital Warehouse
Instagram: phoenixdigital_warehouse
Website: https://phoenixdigital.id/`;
        }

        // Bersihkan nomor (hanya digit) & pakai endpoint api.whatsapp.com
        // yang lebih konsisten menampilkan emoji di WhatsApp Web/Desktop.
        const noWaClean = (noWa || '').replace(/\D/g, '');
        const url = `https://api.whatsapp.com/send?phone=${noWaClean}&text=${encodeURIComponent(pesan)}`;
        window.open(url, '_blank');

        if (type === 'habis') {
            // Pemberitahuan akun habis: hanya catat waktu notifikasi,
            // jangan ubah status delivery/order.
            Livewire.dispatch('habis-notified', {
                id: idItem
            });
        } else {
            Livewire.dispatch('sent-on-whatsapp', {
                id: idItem
            });
        }
    }
</script>
@endpush