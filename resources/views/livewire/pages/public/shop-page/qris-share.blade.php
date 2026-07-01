<div id="qrisShareRoot" data-expires="{{ optional($order->expired_at)->toIso8601String() }}"
    @if (! $paid && ! $this->isExpired()) wire:poll.5s.keep-alive="checkPayment" @endif>

    <style>
        .qs-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 1.25rem; background: linear-gradient(160deg, #eef0ff, #f8f9ff); }
        .qs-card { width: 100%; max-width: 420px; border-radius: 1.5rem; background: #fff;
            box-shadow: 0 20px 50px rgba(78, 70, 229, 0.15); overflow: hidden; }
        .qs-head { background: linear-gradient(135deg, #6c63ff, #4e46e5); color: #fff;
            padding: 1.5rem; text-align: center; }
        .qs-body { padding: 1.5rem; text-align: center; }
        .qs-frame { background: #fff; border-radius: 18px; padding: .75rem; border: 1px solid #eef0f6;
            display: inline-block; position: relative; }
        #qsCanvasWrap canvas, #qsCanvasWrap img { display: block; width: 230px !important; height: 230px !important; }
        .qs-amount { font-weight: 800; font-size: 1.9rem; color: #4e46e5; }
        .qs-countdown-box { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1rem;
            border-radius: 999px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);
            color: #b45309; font-weight: 700; }
        .qs-overlay { position: absolute; inset: 0; background: rgba(255, 255, 255, 0.94); border-radius: 18px;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .4rem;
            text-align: center; padding: 1rem; }
        .qs-paid { display: flex; flex-direction: column; align-items: center; gap: .75rem; padding: 1rem 0; }
        .qs-paid-ic { width: 84px; height: 84px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 2.5rem; color: #fff;
            background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 10px 24px rgba(16, 185, 129, 0.4); }
    </style>

    <div class="qs-wrap">
        <div class="qs-card">
            <div class="qs-head">
                <div style="font-size:.8rem; opacity:.85; letter-spacing:.05em;">PEMBAYARAN QRIS</div>
                <div class="fw-bold fs-5">{{ $order->customer->nama ?? 'Pelanggan' }}</div>
                <div style="font-size:.78rem; opacity:.8;">{{ $order->order_number }}</div>
            </div>

            <div class="qs-body">
                @if ($paid)
                    <div class="qs-paid">
                        <div class="qs-paid-ic"><i class="bi bi-check-lg"></i></div>
                        <h5 class="fw-bold mb-0 text-success">Pembayaran Berhasil</h5>
                        <p class="text-muted mb-0">Terima kasih, pembayaran Anda sebesar
                            <b>Rp {{ number_format((int) $order->total, 0, ',', '.') }}</b> telah kami terima.</p>
                    </div>
                @else
                    <p class="text-muted small mb-2">Scan QR di bawah dengan aplikasi e-wallet / m-banking apa pun</p>
                    <div class="qs-frame">
                        <div id="qsCanvasWrap" wire:ignore data-qris="{{ $order->qris_content }}"></div>
                        <div class="qs-overlay" data-when-expired style="display:none;">
                            <i class="bi bi-clock-history text-danger fs-1"></i>
                            <div class="fw-bold">QR Kedaluwarsa</div>
                            <small class="text-muted">Hubungi admin untuk QR baru</small>
                        </div>
                    </div>

                    <div class="my-3">
                        <div class="text-muted small">Total Pembayaran</div>
                        <div class="qs-amount">Rp {{ number_format((int) $order->total, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-3" data-when-active>
                        <button type="button" id="qsDownloadBtn"
                            class="btn btn-outline-dark btn-sm rounded-pill px-3">
                            <i class="bi bi-download"></i> Download QR (PNG)
                        </button>
                    </div>

                    <div data-when-active>
                        <span class="qs-countdown-box">
                            <i class="bi bi-stopwatch"></i> <span id="qsCountdown">--:--</span>
                        </span>
                        <p class="text-muted small mt-3 mb-0">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                            Menunggu pembayaran… halaman ini akan otomatis terupdate.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Script biasa (tidak bergantung pada @script Livewire) supaya QR & countdown
         tetap tampil walau ada konflik JS template di layout publik. --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <script>
            (function () {
                function qsRenderQris(tries) {
                    tries = tries || 0;
                    var wrap = document.getElementById('qsCanvasWrap');
                    if (!wrap) {
                        if (tries < 60) return setTimeout(function () { qsRenderQris(tries + 1); }, 150);
                        return;
                    }
                    var content = wrap.dataset.qris;
                    if (!content) return;
                    if (typeof QRCode === 'undefined') {
                        if (tries < 60) return setTimeout(function () { qsRenderQris(tries + 1); }, 150);
                        return;
                    }
                    if (wrap.dataset.rendered === '1') return;
                    wrap.innerHTML = '';
                    new QRCode(wrap, {
                        text: content,
                        width: 230,
                        height: 230,
                        correctLevel: QRCode.CorrectLevel.M
                    });
                    wrap.dataset.rendered = '1';
                }

                function qsTick() {
                    var root = document.getElementById('qrisShareRoot');
                    if (!root) return;
                    var end = root.dataset.expires ? new Date(root.dataset.expires).getTime() : 0;
                    var left = end ? Math.max(0, Math.floor((end - Date.now()) / 1000)) : 0;
                    var m = String(Math.floor(left / 60)).padStart(2, '0');
                    var s = String(left % 60).padStart(2, '0');
                    var cd = document.getElementById('qsCountdown');
                    if (cd) cd.textContent = m + ':' + s;
                    var expired = !end || left <= 0;
                    document.querySelectorAll('[data-when-expired]').forEach(function (el) { el.style.display = expired ? '' : 'none'; });
                    document.querySelectorAll('[data-when-active]').forEach(function (el) { el.style.display = expired ? 'none' : ''; });
                }

                var qsInfo = {
                    title: 'Phoenix Digital',
                    customer: @js($order->customer->nama ?? 'Pelanggan'),
                    orderNumber: @js($order->order_number),
                    amount: @js('Rp ' . number_format((int) $order->total, 0, ',', '.')),
                    nmid: @js(config('services.qris.nmid')),
                };

                function qsDownloadPng() {
                    var wrap = document.getElementById('qsCanvasWrap');
                    var qr = wrap ? (wrap.querySelector('canvas') || wrap.querySelector('img')) : null;
                    if (!qr) return;

                    var scale = 2;
                    var W = 460, headerH = 100, custH = 66, qrPad = 26, qrSize = 300, amountH = 90, footerH = 40;
                    var H = headerH + custH + qrPad + qrSize + amountH + footerH;
                    var c = document.createElement('canvas');
                    c.width = W * scale;
                    c.height = H * scale;
                    var ctx = c.getContext('2d');
                    ctx.scale(scale, scale);
                    ctx.textAlign = 'center';

                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, W, H);

                    var fit = function (text, maxW, font) {
                        ctx.font = font;
                        text = String(text || '');
                        if (ctx.measureText(text).width <= maxW) return text;
                        while (text.length > 1 && ctx.measureText(text + '…').width > maxW) {
                            text = text.slice(0, -1);
                        }
                        return text + '…';
                    };

                    var grad = ctx.createLinearGradient(0, 0, W, headerH);
                    grad.addColorStop(0, '#6c63ff');
                    grad.addColorStop(1, '#4e46e5');
                    ctx.fillStyle = grad;
                    ctx.fillRect(0, 0, W, headerH);
                    ctx.fillStyle = 'rgba(255,255,255,0.85)';
                    ctx.font = '600 13px Arial';
                    ctx.fillText('PEMBAYARAN QRIS', W / 2, 36);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillText(fit(qsInfo.title, W - 60, 'bold 22px Arial'), W / 2, 64);
                    ctx.fillStyle = 'rgba(255,255,255,0.8)';
                    ctx.font = '12px Arial';
                    ctx.fillText(qsInfo.orderNumber, W / 2, 86);

                    var cy = headerH;
                    ctx.fillStyle = '#f1f3ff';
                    ctx.fillRect(0, cy, W, custH);
                    ctx.fillStyle = '#8b8fa3';
                    ctx.font = '600 11px Arial';
                    ctx.fillText('UNTUK PELANGGAN', W / 2, cy + 25);
                    ctx.fillStyle = '#1e293b';
                    ctx.fillText(fit(qsInfo.customer, W - 48, 'bold 19px Arial'), W / 2, cy + 49);

                    var qy = headerH + custH + qrPad;
                    var qx = (W - qrSize) / 2;
                    ctx.strokeStyle = '#e6e8f2';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(qx - 8.5, qy - 8.5, qrSize + 17, qrSize + 17);
                    ctx.drawImage(qr, qx, qy, qrSize, qrSize);

                    var ay = qy + qrSize + 38;
                    ctx.fillStyle = '#64748b';
                    ctx.font = '13px Arial';
                    ctx.fillText('Total Pembayaran', W / 2, ay);
                    ctx.fillStyle = '#4e46e5';
                    ctx.font = 'bold 30px Arial';
                    ctx.fillText(qsInfo.amount, W / 2, ay + 36);

                    ctx.fillStyle = '#a0a6b8';
                    ctx.font = '11px Arial';
                    ctx.fillText('NMID ' + (qsInfo.nmid || '-'), W / 2, H - 16);

                    var safe = (qsInfo.customer || 'pelanggan').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
                    var a = document.createElement('a');
                    a.href = c.toDataURL('image/png');
                    a.download = 'QRIS-' + qsInfo.orderNumber + '-' + safe + '.png';
                    a.click();
                }

                function qsInit() {
                    qsRenderQris(0);
                    qsTick();
                    if (window.__qsInterval) clearInterval(window.__qsInterval);
                    window.__qsInterval = setInterval(qsTick, 1000);
                    var btn = document.getElementById('qsDownloadBtn');
                    if (btn && !btn.dataset.bound) {
                        btn.dataset.bound = '1';
                        btn.addEventListener('click', qsDownloadPng);
                    }
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', qsInit);
                } else {
                    qsInit();
                }
                document.addEventListener('livewire:navigated', qsInit);
            })();
        </script>
    @endpush
</div>
