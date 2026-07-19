<div>
    <div class="page-title ph-page-title">
        <div class="container">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-search"></i> Lacak Pesanan</span>
                <h1>Lacak Pesanan Anda</h1>
                <p>Cukup masukkan <b>Nomor Order</b> &amp; <b>Nomor HP</b> yang dipakai saat memesan — tanpa perlu login.</p>
            </div>
        </div>
    </div>

    <section class="co-section">
        <div class="container">
            <div style="max-width: 620px; margin: 0 auto;">
                {{-- Form --}}
                <div class="co-card">
                    <div class="co-card-head"><i class="bi bi-box-seam"></i> Cari Pesanan</div>
                    <div class="co-card-body">
                        <form wire:submit="track">
                            <div class="co-field">
                                <label>Nomor Order <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="orderNumber"
                                    placeholder="Contoh: INV-20260712-0009">
                                @error('orderNumber') <span class="co-err">{{ $message }}</span> @enderror
                            </div>
                            <div class="co-field" style="margin-top:12px;">
                                <label>Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="phone"
                                    placeholder="Contoh: 0895xxxxxxx">
                                @error('phone') <span class="co-err">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="co-btn co-btn-primary w-100 justify-content-center" style="margin-top:16px;"
                                wire:loading.attr="disabled" wire:target="track">
                                <span wire:loading.remove wire:target="track"><i class="bi bi-search"></i> Lacak Pesanan</span>
                                <span wire:loading wire:target="track"><span class="spinner-border spinner-border-sm"></span> Mencari...</span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Hasil --}}
                @if ($searched)
                    @if ($order)
                        <div class="pay-card" style="margin-top:18px;">
                            <div class="pay-card-head">
                                <i class="bi bi-receipt"></i> Pesanan {{ $order->order_number }}
                            </div>
                            <div class="pay-card-body">
                                <div class="pay-info-row" style="display:flex;justify-content:space-between;align-items:center;">
                                    <span>Status</span>
                                    <span>{!! $order->getStatusBadge() !!}</span>
                                </div>
                                <div class="pay-info-row" style="display:flex;justify-content:space-between;margin-top:6px;">
                                    <span>Tanggal</span>
                                    <b>{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</b>
                                </div>

                                <div class="pay-sum" style="margin-top:14px;">
                                    @foreach ($order->items as $item)
                                        <div class="pay-item">
                                            <div>
                                                <div class="pay-item-name">{{ $item->product_name }}</div>
                                                <div class="pay-item-dur">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="pay-total" style="margin-top:12px;">
                                    <span>Total</span>
                                    <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>

                                <div class="ph-empty-actions" style="margin-top:16px;">
                                    {{-- Struk hanya untuk pesanan SELESAI — dulu tampil di
                                         semua status, termasuk pesanan yang dibatalkan. --}}
                                    @if ($order->share_token && $order->status === 'completed')
                                        <a href="{{ route('order.receipt', $order->share_token) }}" class="ph-empty-btn">
                                            <i class="bi bi-file-earmark-text"></i> Lihat Struk
                                        </a>
                                    @endif
                                    @if ($order->status === 'pending')
                                        <a href="{{ route('payment', $order) }}" class="ph-empty-btn ghost">
                                            <i class="bi bi-qr-code"></i> Lanjutkan Pembayaran
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="ph-empty my-4" style="max-width:520px;margin:18px auto 0;">
                            <h3 class="ph-empty-title" style="font-size:1.1rem;">Pesanan tidak ditemukan</h3>
                            <p class="ph-empty-sub">Periksa kembali <b>Nomor Order</b> dan <b>Nomor HP</b> Anda — pastikan keduanya sama persis dengan saat memesan.</p>
                            <div class="ph-empty-actions">
                                <a href="https://wa.me/6289505967995?text=Halo%20Phoenix%20Digital%2C%20saya%20ingin%20menanyakan%20pesanan%20saya." target="_blank" rel="noopener" class="ph-empty-btn"><i class="bi bi-whatsapp"></i> Tanya Admin</a>
                            </div>
                        </div>
                    @endif
                @endif

                <p class="cart-summary-note" style="justify-content:center;margin-top:16px;">
                    <i class="bi bi-shield-lock"></i> Data pesanan hanya bisa dibuka dengan nomor order + nomor HP yang cocok.
                </p>
            </div>
        </div>
    </section>
</div>
