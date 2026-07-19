<div @if ($needsQris) wire:init="prepareQris" @endif>
    <!-- Page Title -->
    <div class="page-title ph-page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <div class="ph-page-head">
                <span class="ph-sec-eyebrow"><i class="bi bi-qr-code"></i> Pembayaran</span>
                <h1>Selesaikan Pembayaran</h1>
                <p>Order <b>#{{ $order->order_number }}</b> — scan QRIS di bawah untuk membayar.</p>
            </div>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    <li class="current">Pembayaran</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Page Title -->

    <section class="pay-section">
        <div class="container">
            @if (session()->has('error'))
                <div class="ct-alert ct-alert-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
            @endif

            <div class="row g-4">
                {{-- QRIS --}}
                <div class="col-lg-7">
                    @if ($qrCodeImage && $payment)
                        <div wire:poll.15s="checkPaymentStatus">
                            <div class="pay-qris-card">
                                <div class="pay-qris-head">
                                    <span class="ph-sec-eyebrow"><i class="bi bi-qr-code-scan"></i> QRIS</span>
                                    <h3>Scan untuk Membayar</h3>
                                    <p>Pembayaran ke <b>Phoenix Digital Warehouse</b></p>
                                </div>

                                <div class="pay-amount">
                                    <small>Nominal</small>
                                    <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>

                                <div class="pay-qr">
                                    <img id="ph-qris-img" src="data:image/png;base64,{{ $qrCodeImage }}" alt="QRIS">
                                </div>

                                <div class="pay-warn">
                                    <i class="bi bi-shield-exclamation"></i>
                                    <div>
                                        <b>Hati-hati penipuan!</b>
                                        Pastikan pembayaran tertuju atas nama
                                        <strong>Phoenix Digital Warehouse</strong>.
                                        Selain nama itu, <u>dipastikan penipuan</u> — jangan lanjutkan.
                                    </div>
                                </div>

                                @if ($qrisNmid || $qrisInvoiceId)
                                    <div class="pay-qr-meta">
                                        @if ($qrisNmid) <span>NMID: {{ $qrisNmid }}</span> @endif
                                        @if ($qrisInvoiceId) <span>Invoice: {{ $qrisInvoiceId }}</span> @endif
                                    </div>
                                @endif

                                <button type="button" class="pay-download" onclick="phDownloadQris(this)"
                                    data-order="{{ $order->order_number }}"
                                    data-nominal="Rp {{ number_format($order->total, 0, ',', '.') }}"
                                    data-nmid="{{ $qrisNmid }}"
                                    data-invoice="{{ $qrisInvoiceId }}"
                                    data-expired="{{ $payment->expired_at->translatedFormat('d M Y, H:i') }}">
                                    <i class="bi bi-download"></i> Simpan / Unduh QRIS
                                </button>

                                @if (!$payment->isExpired())
                                    <div class="pay-countdown">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Sisa waktu</span>
                                        <b id="countdown" wire:ignore data-expired="{{ $payment->expired_at->toIso8601String() }}">…</b>
                                    </div>
                                @else
                                    <div class="pay-expired">
                                        <i class="bi bi-x-octagon-fill"></i> QRIS sudah kadaluarsa.
                                    </div>
                                    <button wire:click="generateNewQris" class="pay-paid-btn mt-2">
                                        <i class="bi bi-arrow-clockwise"></i> Buat QRIS Baru
                                    </button>
                                @endif

                                <div class="pay-steps">
                                    <div class="pay-steps-title"><i class="bi bi-list-check"></i> Cara Pembayaran</div>
                                    <ol>
                                        <li>Buka aplikasi Mobile Banking / E-Wallet.</li>
                                        <li>Pilih menu <b>Scan QRIS</b>.</li>
                                        <li>Scan QR Code di atas.</li>
                                        <li>Pastikan nominal <b>sama persis</b>.</li>
                                        <li>Selesaikan pembayaran.</li>
                                    </ol>
                                </div>

                                @if (!$payment->isExpired())
                                    <button wire:click="checkPaymentStatus" class="pay-paid-btn" wire:loading.attr="disabled" wire:target="checkPaymentStatus">
                                        <span wire:loading.remove wire:target="checkPaymentStatus"><i class="bi bi-check-circle-fill"></i> Saya Sudah Bayar</span>
                                        <span wire:loading wire:target="checkPaymentStatus"><span class="spinner-border spinner-border-sm"></span> Mengecek...</span>
                                    </button>
                                    <p class="pay-autocheck"><i class="bi bi-arrow-repeat"></i> Status pembayaran diperiksa otomatis setiap 15 detik.</p>
                                @endif
                            </div>
                        </div>
                    @elseif ($qrisError)
                        <div class="pay-qris-card" style="text-align:center;">
                            <div class="pay-qris-head">
                                <span class="ph-sec-eyebrow"><i class="bi bi-qr-code-scan"></i> QRIS</span>
                                <h3>Gagal Membuat QRIS</h3>
                            </div>
                            <div class="ct-alert ct-alert-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ $qrisError }}</div>
                            <button wire:click="retryQris" class="pay-paid-btn mt-2" wire:loading.attr="disabled" wire:target="retryQris">
                                <span wire:loading.remove wire:target="retryQris"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</span>
                                <span wire:loading wire:target="retryQris"><span class="spinner-border spinner-border-sm"></span> Membuat...</span>
                            </button>
                        </div>
                    @else
                        {{-- QRIS sedang dibuat (dipicu wire:init) — halaman sudah tampil instan --}}
                        <div class="pay-qris-card" style="text-align:center;">
                            <div class="pay-qris-head">
                                <span class="ph-sec-eyebrow"><i class="bi bi-qr-code-scan"></i> QRIS</span>
                                <h3>Menyiapkan QRIS…</h3>
                                <p>Sebentar ya, kami sedang membuat kode pembayaran Anda.</p>
                            </div>
                            <div class="pay-amount">
                                <small>Nominal</small>
                                <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                            <div class="pay-qr pay-qr-loading" style="display:flex;align-items:center;justify-content:center;min-height:220px;">
                                <span class="spinner-border" style="width:2.4rem;height:2.4rem;color:var(--ph-orange);" role="status" aria-label="Membuat QRIS"></span>
                            </div>
                            <p class="pay-autocheck"><i class="bi bi-shield-lock"></i> Kode QRIS aman &amp; unik untuk pesanan ini.</p>
                        </div>
                    @endif
                </div>

                {{-- Ringkasan & info --}}
                <div class="col-lg-5">
                    <div class="pay-card">
                        <div class="pay-card-head"><i class="bi bi-receipt"></i> Detail Pesanan</div>
                        <div class="pay-card-body">
                            @foreach ($order->items as $item)
                                <div class="pay-item">
                                    <div>
                                        <div class="pay-item-name">{{ $item->product_name }}</div>
                                        <div class="pay-item-dur">{{ $item->getDurationLabel() }} &times;{{ $item->quantity }}</div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="pay-sum">
                                <div class="pay-sum-row"><span>Subtotal</span><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
                                @if ($order->total_discount > 0)
                                    <div class="pay-sum-row is-disc"><span>Diskon</span><strong>− Rp {{ number_format($order->total_discount, 0, ',', '.') }}</strong></div>
                                @endif
                                @if ((int) $order->unique_code > 0)
                                    <div class="pay-sum-row"><span>Kode Unik</span><strong>+ Rp {{ number_format($order->unique_code, 0, ',', '.') }}</strong></div>
                                @endif
                            </div>
                            <div class="pay-total">
                                <span>Total Pembayaran</span>
                                <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="pay-deadline">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <span>Batas Waktu Pembayaran</span>
                            <b>{{ $payment && $payment->expired_at ? $payment->expired_at->format('d M Y, H:i').' WIB' : '—' }}</b>
                        </div>
                    </div>

                    <div class="pay-card">
                        <div class="pay-card-head"><i class="bi bi-person-fill"></i> Informasi Pelanggan</div>
                        <div class="pay-card-body">
                            <div class="pay-info-row"><span>Nama</span><b>{{ $order->customer->nama }}</b></div>
                            <div class="pay-info-row"><span>Email</span><b>{{ $order->customer->email }}</b></div>
                            <div class="pay-info-row"><span>No HP</span><b>{{ $order->customer->no_hp }}</b></div>
                            <div class="pay-info-row"><span>Status</span><span>{!! $order->getStatusBadge() !!}</span></div>
                            <div class="pay-info-row"><span>Dibuat</span><b>{{ $order->created_at->format('d M Y, H:i') }}</b></div>
                        </div>
                    </div>

                    <div class="pay-note">
                        <i class="bi bi-info-circle"></i>
                        Akun premium dikirim via email/WhatsApp maks. 1×24 jam setelah pembayaran terverifikasi. Jika tidak dibayar hingga batas waktu, pesanan otomatis dibatalkan.
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            window.phDownloadQris = function (btn) {
                var imgEl = document.getElementById('ph-qris-img');
                if (!imgEl) return;
                var d = btn.dataset;

                var im = new Image();
                im.onload = function () {
                    var W = 760, H = 1120, F = "'Poppins','Segoe UI',Arial,sans-serif";
                    var c = document.createElement('canvas');
                    c.width = W; c.height = H;
                    var x = c.getContext('2d');

                    function rr(px, py, w, h, r) {
                        x.beginPath();
                        x.moveTo(px + r, py);
                        x.arcTo(px + w, py, px + w, py + h, r);
                        x.arcTo(px + w, py + h, px, py + h, r);
                        x.arcTo(px, py + h, px, py, r);
                        x.arcTo(px, py, px + w, py, r);
                        x.closePath();
                    }

                    // Latar + kartu
                    x.fillStyle = '#fff6ee'; x.fillRect(0, 0, W, H);
                    rr(28, 28, W - 56, H - 56, 30); x.fillStyle = '#fff'; x.fill();
                    x.lineWidth = 2; x.strokeStyle = '#f3dccb'; x.stroke();

                    // Header oranye (sudut atas membulat via clip)
                    x.save();
                    rr(28, 28, W - 56, H - 56, 30); x.clip();
                    var g = x.createLinearGradient(28, 28, W - 28, 190);
                    g.addColorStop(0, '#fba919'); g.addColorStop(1, '#f0531e');
                    x.fillStyle = g; x.fillRect(28, 28, W - 56, 162);
                    x.restore();

                    x.textAlign = 'center';
                    x.fillStyle = '#fff';
                    x.font = '800 40px ' + F;
                    x.fillText('Phoenix Digital', W / 2, 100);
                    x.font = '600 19px ' + F;
                    x.fillText('W A R E H O U S E', W / 2, 140);

                    x.fillStyle = '#8a6a4e'; x.font = '600 20px ' + F;
                    x.fillText('Scan QRIS untuk membayar', W / 2, 232);

                    // Kotak QR
                    var qs = 430, qx = (W - qs) / 2, qy = 260;
                    rr(qx - 22, qy - 22, qs + 44, qs + 44, 22);
                    x.fillStyle = '#fff'; x.fill(); x.lineWidth = 2; x.strokeStyle = '#eadfd2'; x.stroke();
                    x.drawImage(im, qx, qy, qs, qs);

                    var metaY = qy + qs + 52;
                    var meta = [];
                    if (d.nmid) meta.push('NMID: ' + d.nmid);
                    if (d.invoice) meta.push('Invoice: ' + d.invoice);
                    if (meta.length) { x.fillStyle = '#9a8a79'; x.font = '400 17px ' + F; x.fillText(meta.join('     •     '), W / 2, metaY); }

                    // Nominal
                    x.fillStyle = '#9a8a79'; x.font = '600 16px ' + F;
                    x.fillText('N O M I N A L', W / 2, metaY + 46);
                    x.fillStyle = '#f0531e'; x.font = '800 46px ' + F;
                    x.fillText(d.nominal || '', W / 2, metaY + 96);

                    // Peringatan penipuan
                    var wy = metaY + 132, wh = 150, wx = 60, ww = W - 120;
                    rr(wx, wy, ww, wh, 16); x.fillStyle = '#fff5f0'; x.fill();
                    x.lineWidth = 1.5; x.strokeStyle = '#f6c6ad'; x.stroke();
                    x.fillStyle = '#f0531e'; x.fillRect(wx, wy + 14, 5, wh - 28);

                    x.fillStyle = '#b3401a'; x.font = '800 23px ' + F;
                    x.fillText('Hati-hati Penipuan!', W / 2, wy + 44);
                    var lines = ['Pastikan pembayaran atas nama', 'PHOENIX DIGITAL WAREHOUSE.', 'Selain nama itu, dipastikan penipuan.'];
                    for (var i = 0; i < lines.length; i++) {
                        x.font = (i === 1 ? '800 20px ' : '400 20px ') + F;
                        x.fillStyle = (i === 1 ? '#b3401a' : '#7a3d1a');
                        x.fillText(lines[i], W / 2, wy + 80 + i * 28);
                    }

                    // Footer
                    x.fillStyle = '#9a8a79'; x.font = '400 16px ' + F;
                    var foot = 'Order ' + (d.order || '') + (d.expired ? '     •     Berlaku s.d. ' + d.expired : '');
                    x.fillText(foot, W / 2, H - 54);

                    var a = document.createElement('a');
                    a.href = c.toDataURL('image/png');
                    a.download = 'QRIS-' + (d.order || 'phoenix-digital') + '.png';
                    document.body.appendChild(a); a.click(); a.remove();
                };
                im.src = imgEl.src;
            };
        </script>
    @endpush
</div>
