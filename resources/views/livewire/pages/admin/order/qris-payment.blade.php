<div id="qrisRoot" data-expires="{{ optional($order->expired_at)->toIso8601String() }}"
    @if ($order->status === 'pending' && ! $this->isExpired()) wire:poll.5s.keep-alive="checkPayment" @endif>

    <style>
        .qris-card {
            border: 1px solid rgba(108, 99, 255, 0.14);
            border-radius: 1.25rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.97), rgba(248, 249, 255, 0.97));
            box-shadow: 0 12px 32px rgba(108, 99, 255, 0.12);
        }

        .qris-frame {
            background: #fff;
            border-radius: 18px;
            padding: 1rem;
            border: 1px solid #eef0f6;
            box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
            display: inline-block;
            position: relative;
        }

        #qrisCanvasWrap canvas,
        #qrisCanvasWrap img {
            display: block;
            width: 240px !important;
            height: 240px !important;
        }

        .qris-amount {
            font-weight: 800;
            font-size: 1.8rem;
            color: #4e46e5;
            letter-spacing: -.01em;
        }

        .qris-countdown {
            font-weight: 800;
            font-size: 1.35rem;
            font-variant-numeric: tabular-nums;
            color: #0f172a;
        }

        .qris-countdown-box {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .5rem 1rem;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 119, 6, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #b45309;
        }

        .qris-merchant {
            font-size: .8rem;
            color: #64748b;
        }

        .qris-merchant b {
            color: #1e293b;
        }

        .qris-expired-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            border-radius: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            text-align: center;
            padding: 1rem;
        }

        .qris-btn {
            border-radius: 12px;
            font-weight: 600;
            font-size: .92rem;
            padding: .7rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            line-height: 1;
            border: 1px solid transparent;
            transition: all .18s ease;
        }

        /* Ikon selalu sejajar dengan teks */
        .qris-btn i.bi {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 1.02em;
        }

        .qris-btn i.bi::before {
            display: block;
            line-height: 1;
        }

        /* Aksi utama: WhatsApp */
        .qris-btn-wa {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            box-shadow: 0 8px 18px rgba(22, 163, 74, 0.28);
        }

        .qris-btn-wa:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(22, 163, 74, 0.38);
        }

        /* Aksi sekunder: lembut */
        .qris-btn-soft {
            background: #f1f3ff;
            color: #4e46e5;
            border-color: #e2e5ff;
        }

        .qris-btn-soft:hover {
            background: #e7e9ff;
            color: #4036d6;
            border-color: #cfd3ff;
            transform: translateY(-1px);
        }

        /* Aksi tersier: ghost */
        .qris-btn-ghost {
            background: #fff;
            color: #64748b;
            border-color: #e6e8f2;
        }

        .qris-btn-ghost:hover {
            background: #f8fafc;
            color: #475569;
            border-color: #d6dae8;
        }

        .qris-status-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .8rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 700;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        .qris-status-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: qrisPulse 1.4s infinite;
        }

        @keyframes qrisPulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .4;
                transform: scale(.7);
            }
        }

        /* ===== Penyempurnaan tampilan (clean + glossy) ===== */
        .qris-card {
            border: 1px solid rgba(255, 255, 255, 0.65) !important;
            border-radius: 24px !important;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.86), rgba(244, 246, 255, 0.86)) !important;
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 45px rgba(108, 99, 255, 0.14) !important;
        }

        .qris-frame {
            border-radius: 20px;
            border: 1px solid rgba(108, 99, 255, 0.12);
            box-shadow: 0 10px 26px rgba(16, 24, 40, 0.08);
        }

        .qris-qr-col {
            text-align: center;
        }

        .qris-amount-label {
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            font-weight: 600;
        }

        .qris-amount {
            font-weight: 800;
            font-size: 2rem;
            line-height: 1.15;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #6c63ff, #4e46e5);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .qris-countdown {
            color: #92400e;
            font-size: 1.25rem;
            line-height: 1;
        }

        .qris-countdown-box i.bi {
            display: inline-flex;
            align-items: center;
            line-height: 1;
            font-size: 1.1rem;
        }

        .qris-countdown-cap {
            font-size: .78rem;
            color: #94a3b8;
            margin-left: .5rem;
        }

        .qris-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(108, 99, 255, 0.18), transparent);
            margin: 1.25rem 0;
        }

        .qris-merchant {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .82rem;
            color: #64748b;
        }

        .qris-merchant i.bi {
            color: #10b981;
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .qris-merchant b {
            color: #1e293b;
        }

        .qris-note {
            display: flex;
            align-items: flex-start;
            gap: .45rem;
            font-size: .82rem;
            color: #94a3b8;
            line-height: 1.45;
        }

        .qris-note i.bi {
            color: #6c63ff;
            margin-top: .12rem;
            display: inline-flex;
            line-height: 1;
        }

        .qris-status-pill i.bi {
            display: inline-flex;
            align-items: center;
            line-height: 1;
        }

        .qris-expired-overlay i.bi.bi-clock-history {
            font-size: 2rem;
            color: #ef4444;
        }

        /* SweetAlert glossy — seragam dengan fitur lain (mis. Banners) */
        .swal-glossy-popup {
            border-radius: 28px !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
        }

        .swal-glossy-title {
            font-weight: 700 !important;
        }

        .btn-glossy-confirm {
            background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
            color: #fff !important;
            padding: 12px 24px !important;
            border-radius: 12px !important;
            margin: 0 5px !important;
            border: none !important;
            font-weight: 600 !important;
        }

        .btn-glossy-cancel {
            background: #e2e8f0 !important;
            color: #475569 !important;
            padding: 12px 24px !important;
            border-radius: 12px !important;
            margin: 0 5px !important;
            border: none !important;
            font-weight: 600 !important;
        }
    </style>

    <div class="container py-4" style="max-width: 760px;">
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.pesanantoko.index') }}" class="btn btn-sm btn-light rounded-circle">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="fw-bold mb-0">Pembayaran QRIS Dinamis</h4>
                <small class="text-muted">{{ $order->order_number }} ·
                    {{ $order->customer->nama ?? 'Pelanggan' }}</small>
            </div>
        </div>

        @if ($errorMessage)
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errorMessage }}</div>
        </div>
        @endif

        <div class="qris-card p-4 p-md-5">
            <div class="row g-4 g-md-5 align-items-center">
                {{-- QR --}}
                <div class="col-md-6">
                    <div class="qris-qr-col">
                        <div class="qris-frame">
                            <div id="qrisCanvasWrap" wire:ignore data-qris="{{ $order->qris_content }}"></div>

                            {{-- Overlay saat kedaluwarsa (dikontrol JS) --}}
                            <div class="qris-expired-overlay" data-when-expired style="display:none;">
                                <i class="bi bi-clock-history"></i>
                                <div class="fw-bold">QR Kedaluwarsa</div>
                                <small class="text-muted">Buat QR baru untuk melanjutkan</small>
                                <button type="button" wire:click="refreshQr" class="btn qris-btn qris-btn-soft btn-sm mt-1"
                                    wire:loading.attr="disabled" wire:target="refreshQr">
                                    <i class="bi bi-arrow-clockwise"></i> <span>Buat QR Baru</span>
                                </button>
                            </div>
                        </div>
                        <div class="qris-merchant mt-3">
                            <i class="bi bi-shield-check"></i>
                            <span><b>Phoenix Digital</b> · NMID {{ config('services.qris.nmid') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="col-md-6">
                    <div class="mb-3" data-when-active>
                        <span class="qris-status-pill"><span class="dot"></span> Menunggu pembayaran…</span>
                    </div>

                    <div class="qris-amount-label">Total Pembayaran</div>
                    <div class="qris-amount">Rp {{ number_format((int) $order->total, 0, ',', '.') }}</div>

                    <div class="mt-3" data-when-active>
                        <span class="qris-countdown-box">
                            <i class="bi bi-stopwatch"></i>
                            <span class="qris-countdown" id="qrisCountdown">--:--</span>
                        </span>
                        <span class="qris-countdown-cap">tersisa</span>
                    </div>

                    <div class="qris-divider"></div>

                    <div class="qris-actions">
                        {{-- Aksi utama --}}
                        <a href="{{ $this->waLink }}" target="_blank" rel="noopener"
                            class="btn qris-btn qris-btn-wa w-100">
                            <i class="bi bi-whatsapp"></i> <span>Bagikan ke WhatsApp</span>
                        </a>

                        {{-- Aksi sekunder (2 kolom) --}}
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <button type="button" id="qrisDownloadBtn" class="btn qris-btn qris-btn-soft w-100"
                                    data-title="Phoenix Digital" data-order="{{ $order->order_number }}"
                                    data-customer="{{ $order->customer->nama ?? 'Pelanggan' }}"
                                    data-amount="Rp {{ number_format((int) $order->total, 0, ',', '.') }}"
                                    data-nmid="{{ config('services.qris.nmid') }}">
                                    <i class="bi bi-download"></i> <span>Download PNG</span>
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="button" wire:click="refreshQr"
                                    class="btn qris-btn qris-btn-soft w-100"
                                    wire:loading.attr="disabled" wire:target="refreshQr">
                                    <i class="bi bi-arrow-clockwise"></i> <span>QR Baru</span>
                                </button>
                            </div>
                        </div>

                        {{-- Aksi tersier --}}
                        <button type="button" id="qrisDraftBtn" class="btn qris-btn qris-btn-ghost w-100 mt-2">
                            <i class="bi bi-inbox"></i> <span>Simpan ke Draft</span>
                        </button>
                    </div>

                    <p class="qris-note mt-3 mb-0">
                        <i class="bi bi-info-circle"></i>
                        <span>Status pembayaran dicek otomatis. Saat customer berhasil membayar, pesanan
                            langsung diteruskan ke proses.</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('livewire.layout.sweetalert')

        @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endpush

    @script
    <script>
        const ofDetailUrl = @js(route('admin.pesanantoko.index', ['activeTab' => 'neworder']));

        function ofRenderQris(content, tries = 0) {
            const wrap = document.getElementById('qrisCanvasWrap');
            if (!wrap) return;
            content = content || wrap.dataset.qris;
            if (!content) return;
            // Tunggu library QR (CDN) selesai dimuat
            if (typeof QRCode === 'undefined') {
                if (tries < 60) return setTimeout(() => ofRenderQris(content, tries + 1), 150);
                return;
            }
            wrap.innerHTML = '';
            wrap.dataset.qris = content;
            new QRCode(wrap, {
                text: content,
                width: 240,
                height: 240,
                correctLevel: QRCode.CorrectLevel.M,
            });
        }

        function ofTick() {
            const root = document.getElementById('qrisRoot');
            if (!root) return;
            const end = root.dataset.expires ? new Date(root.dataset.expires).getTime() : 0;
            const left = end ? Math.max(0, Math.floor((end - Date.now()) / 1000)) : 0;
            const m = String(Math.floor(left / 60)).padStart(2, '0');
            const s = String(left % 60).padStart(2, '0');
            const cd = document.getElementById('qrisCountdown');
            if (cd) cd.textContent = m + ':' + s;

            const expired = !end || left <= 0;
            document.querySelectorAll('[data-when-expired]').forEach(el => el.style.display = expired ? '' : 'none');
            document.querySelectorAll('[data-when-active]').forEach(el => el.style.display = expired ? 'none' : '');
        }

        // Render awal + jalankan countdown
        setTimeout(() => ofRenderQris(), 60);
        ofTick();
        if (window.__ofQrisInterval) clearInterval(window.__ofQrisInterval);
        window.__ofQrisInterval = setInterval(ofTick, 1000);

        // QR baru setelah refresh (konten dikirim via event karena wrap di-wire:ignore)
        $wire.on('qris-refreshed', (e) => {
            const content = (e && e.content) || (Array.isArray(e) && e[0] && e[0].content) || null;
            setTimeout(() => ofRenderQris(content), 80);
        });

        // Konfirmasi "Simpan ke Draft" dengan SweetAlert glossy
        const ofDraftBtn = document.getElementById('qrisDraftBtn');
        if (ofDraftBtn) {
            ofDraftBtn.addEventListener('click', () => {
                if (typeof Swal === 'undefined') {
                    $wire.call('saveDraft');
                    return;
                }
                Swal.fire({
                    title: 'Simpan ke Draft?',
                    text: 'Pesanan disimpan dulu dan bisa dilanjutkan kapan saja dari daftar pesanan.',
                    icon: 'question',
                    background: 'rgba(255, 255, 255, 0.8)',
                    backdrop: 'rgba(139, 92, 246, 0.15)',
                    customClass: {
                        popup: 'swal-glossy-popup',
                        confirmButton: 'btn-glossy-confirm',
                        cancelButton: 'btn-glossy-cancel',
                        title: 'swal-glossy-title',
                    },
                    buttonsStyling: false,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan draft',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((res) => {
                    if (res.isConfirmed) $wire.call('saveDraft');
                });
            });
        }

        // Download QR sebagai PNG — kartu rapi: header, nama pelanggan, QR, nominal
        window.ofComposeQrisPng = function(qrWrapId, info, fileName) {
            const wrap = document.getElementById(qrWrapId);
            const qr = wrap ? (wrap.querySelector('canvas') || wrap.querySelector('img')) : null;
            if (!qr) return;

            const scale = 2; // tajam saat dibagikan / dicetak
            const W = 460,
                headerH = 100,
                custH = 66,
                qrPad = 26,
                qrSize = 300,
                amountH = 90,
                footerH = 40;
            const H = headerH + custH + qrPad + qrSize + amountH + footerH;

            const c = document.createElement('canvas');
            c.width = W * scale;
            c.height = H * scale;
            const ctx = c.getContext('2d');
            ctx.scale(scale, scale);
            ctx.textAlign = 'center';

            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, W, H);

            // Potong teks bila terlalu panjang
            const fit = function(text, maxW, font) {
                ctx.font = font;
                text = String(text || '');
                if (ctx.measureText(text).width <= maxW) return text;
                while (text.length > 1 && ctx.measureText(text + '…').width > maxW) {
                    text = text.slice(0, -1);
                }
                return text + '…';
            };

            // Header gradient ungu
            const grad = ctx.createLinearGradient(0, 0, W, headerH);
            grad.addColorStop(0, '#6c63ff');
            grad.addColorStop(1, '#4e46e5');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, W, headerH);
            ctx.fillStyle = 'rgba(255,255,255,0.85)';
            ctx.font = '600 13px Arial';
            ctx.fillText('PEMBAYARAN QRIS', W / 2, 36);
            ctx.fillStyle = '#ffffff';
            ctx.fillText(fit(info.title, W - 60, 'bold 22px Arial'), W / 2, 64);
            ctx.fillStyle = 'rgba(255,255,255,0.8)';
            ctx.font = '12px Arial';
            ctx.fillText(info.orderNumber, W / 2, 86);

            // Blok nama pelanggan (hindari salah kirim)
            const cy = headerH;
            ctx.fillStyle = '#f1f3ff';
            ctx.fillRect(0, cy, W, custH);
            ctx.fillStyle = '#8b8fa3';
            ctx.font = '600 11px Arial';
            ctx.fillText('UNTUK PELANGGAN', W / 2, cy + 25);
            ctx.fillStyle = '#1e293b';
            ctx.fillText(fit(info.customer, W - 48, 'bold 19px Arial'), W / 2, cy + 49);

            // QR + bingkai
            const qy = headerH + custH + qrPad;
            const qx = (W - qrSize) / 2;
            ctx.strokeStyle = '#e6e8f2';
            ctx.lineWidth = 1;
            ctx.strokeRect(qx - 8.5, qy - 8.5, qrSize + 17, qrSize + 17);
            ctx.drawImage(qr, qx, qy, qrSize, qrSize);

            // Total
            const ay = qy + qrSize + 38;
            ctx.fillStyle = '#64748b';
            ctx.font = '13px Arial';
            ctx.fillText('Total Pembayaran', W / 2, ay);
            ctx.fillStyle = '#4e46e5';
            ctx.font = 'bold 30px Arial';
            ctx.fillText(info.amount, W / 2, ay + 36);

            // Footer
            ctx.fillStyle = '#a0a6b8';
            ctx.font = '11px Arial';
            ctx.fillText('NMID ' + (info.nmid || '-'), W / 2, H - 16);

            const a = document.createElement('a');
            a.href = c.toDataURL('image/png');
            a.download = fileName;
            a.click();
        };

        const ofDownloadBtn = document.getElementById('qrisDownloadBtn');
        if (ofDownloadBtn) {
            ofDownloadBtn.addEventListener('click', function() {
                const info = {
                    title: ofDownloadBtn.dataset.title,
                    orderNumber: ofDownloadBtn.dataset.order,
                    customer: ofDownloadBtn.dataset.customer,
                    amount: ofDownloadBtn.dataset.amount,
                    nmid: ofDownloadBtn.dataset.nmid,
                };
                const safe = (info.customer || 'pelanggan').replace(/[^a-z0-9]+/gi, '-').toLowerCase();
                window.ofComposeQrisPng('qrisCanvasWrap', info, 'QRIS-' + info.orderNumber + '-' + safe + '.png');
            });
        }

        // Pembayaran berhasil → glossy success → teruskan ke detail
        $wire.on('qris-paid', () => {
            if (window.__ofQrisInterval) clearInterval(window.__ofQrisInterval);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Pembayaran Berhasil!',
                    text: 'QRIS sudah dibayar. Pesanan masuk ke tab Pesanan Baru…',
                    icon: 'success',
                    background: 'rgba(255, 255, 255, 0.8)',
                    backdrop: 'rgba(16, 185, 129, 0.15)',
                    customClass: {
                        popup: 'swal-glossy-popup',
                        title: 'swal-glossy-title',
                    },
                    buttonsStyling: false,
                    timer: 2200,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                }).then(() => window.location = ofDetailUrl);
            } else {
                window.location = ofDetailUrl;
            }
        });
    </script>
    @endscript
</div>