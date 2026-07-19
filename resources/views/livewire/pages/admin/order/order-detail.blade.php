
@section('title')
Detail Pesanan || lemon
@stop
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
                    <span class="info-label">Metode Pembayaran</span>
                    <span class="info-value">
                        @php
                        $payMap = [
                        'transfer' => ['Transfer Bank', 'bi-bank', 'primary'],
                        'qris_statis' => ['QRIS Statis', 'bi-qr-code', 'info'],
                        'qris_dinamis' => ['QRIS Dinamis', 'bi-qr-code-scan', 'success'],
                        ];
                        $pay = $payMap[$order->payment_method] ?? null;
                        @endphp
                        @if ($pay)
                        <span class="badge bg-{{ $pay[2] }}-subtle text-{{ $pay[2] }} border border-{{ $pay[2] }}">
                            <i class="bi {{ $pay[1] }}"></i> {{ $pay[0] }}
                        </span>
                        @else
                        <span class="text-muted fw-normal">-</span>
                        @endif
                    </span>
                </div>
                @if($order->bukti_pembayaran)
                <div class="info-row">
                    <span class="info-label">Bukti Pembayaran</span>
                    <span class="info-value">
                        <a href="javascript:void(0)" role="button" class="bukti-zoom-trigger d-inline-block"
                            data-bukti-url="{{ route('admin.pesanantoko.bukti', $order) }}" title="Perbesar bukti pembayaran">
                            <img src="{{ route('admin.pesanantoko.bukti', $order) }}" alt="Bukti pembayaran"
                                style="max-height:64px; border-radius:8px; border:1px solid #e6e8f2; cursor:zoom-in;">
                        </a>
                    </span>
                </div>
                @endif
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

    {{-- ===== Pengecekan plagiasi (pesanan JASA) ===== --}}
    @if ($order->butuhUpload())
    @php
        $jKuota = $order->kuotaPengecekan();
        $jTerpakai = $order->terpakaiPengecekan();
        $jSisa = $order->sisaKuota();
    @endphp
    <style>
        /* Ikon sejajar teks di seluruh blok pengecekan */
        .pcek i.bi { line-height: 1; vertical-align: -.075em; }
        .pcek .pcek-head-ic { width: 44px; height: 44px; border-radius: 13px; display: flex; align-items: center; justify-content: center; background: #fff7ed; color: #ea580c; font-size: 1.3rem; flex-shrink: 0; }
        .pcek .pcek-fileic { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; background: #fff7ed; color: #ea580c; font-size: 1.2rem; flex-shrink: 0; }
        /* Ikon di dalam kotak: pusatkan penuh. Glyph bootstrap-icons dirender via
           ::before, jadi jadikan <i> flex + ::before block agar benar-benar center. */
        .pcek .pcek-head-ic i.bi, .pcek .pcek-fileic i.bi { display: flex; align-items: center; justify-content: center; width: 1em; height: 1em; line-height: 1; vertical-align: 0; }
        .pcek .pcek-head-ic i.bi::before, .pcek .pcek-fileic i.bi::before { display: block; line-height: 1; margin: 0; }
        .pcek .pcek-link-box { display: flex; align-items: stretch; border: 1px solid #e2e8f0; border-radius: 11px; overflow: hidden; background: #fff; }
        .pcek .pcek-link-box input { border: 0; background: #f8fafc; font-size: .82rem; color: #475569; padding: .55rem .8rem; flex: 1; min-width: 0; outline: none; }
        .pcek .pcek-link-box button { border: 0; border-left: 1px solid #e2e8f0; background: #fff; color: #ea580c; font-weight: 600; font-size: .82rem; padding: .55rem 1rem; white-space: nowrap; display: inline-flex; align-items: center; gap: .4rem; transition: background .15s; }
        .pcek .pcek-link-box button:hover { background: #fff7ed; }
        .pcek .pcek-item { border: 1px solid #eef0f6; border-radius: 15px; padding: 1rem 1.1rem; background: #fff; transition: box-shadow .2s, border-color .2s; }
        .pcek .pcek-item:hover { box-shadow: 0 6px 18px rgba(15, 23, 42, .06); border-color: #e2e8f0; }
        .pcek .pcek-tag { display: inline-flex; align-items: center; gap: .35rem; font-size: .78rem; }
        /* Panel penyelesaian pesanan jasa */
        .pcek .pcek-finish { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; padding: 13px 15px; border: 1px solid #ddd6fe; border-radius: 13px; background: linear-gradient(180deg, #f5f3ff, #fff); }
        .pcek .pcek-finish b { display: block; font-size: .85rem; color: #4338ca; }
        .pcek .pcek-finish small { display: block; font-size: .76rem; color: #64748b; line-height: 1.45; margin-top: 2px; }
        .pcek .pcek-finish small b { display: inline; color: #4338ca; }
        /* Setelan exclude + catatan customer */
        .pcek .pcek-set { margin-top: 12px; padding-top: 12px; border-top: 1px dashed #eef0f6; }
        .pcek .pcek-set-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .pcek .pcek-set-lbl { display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94a3b8; }
        .pcek .pcek-set-lbl i.bi { display: flex; align-items: center; line-height: 1; font-size: .82rem; }
        .pcek .pcek-set-lbl i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-set-vals { display: flex; flex-wrap: wrap; gap: 6px; }
        .pcek .pcek-c { display: inline-flex; align-items: center; padding: 4px 11px; border-radius: 99px; background: #eef2ff; color: #4338ca; font-size: .76rem; font-weight: 600; white-space: nowrap; }
        .pcek .pcek-c.off { background: #f1f5f9; color: #94a3b8; font-weight: 500; }
        .pcek .pcek-note { display: flex; align-items: flex-start; gap: 8px; margin-top: 10px; padding: 9px 12px; border-left: 3px solid #cbd5e1; border-radius: 0 9px 9px 0; background: #f8fafc; font-size: .8rem; color: #475569; line-height: 1.5; }
        .pcek .pcek-note i.bi { flex-shrink: 0; margin-top: .15rem; color: #94a3b8; display: flex; line-height: 1; }
        .pcek .pcek-note i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-note b { color: #334155; font-weight: 700; }
        /* Baris aksi — satu sistem tombol, tinggi & radius seragam */
        .pcek .pcek-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 14px; padding-top: 13px; border-top: 1px solid #f1f5f9; }
        .pcek .pcek-push { margin-left: auto; }
        .pcek .pcek-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 36px; padding: 0 14px; border: 1px solid transparent; border-radius: 9px; font-size: .82rem; font-weight: 600; line-height: 1; white-space: nowrap; cursor: pointer; text-decoration: none; transition: background .16s, border-color .16s, color .16s; }
        .pcek .pcek-btn i.bi { font-size: .92rem; display: flex; align-items: center; line-height: 1; }
        .pcek .pcek-btn i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-btn:disabled { opacity: .6; cursor: not-allowed; }
        .pcek .pcek-btn.ghost { background: #fff; border-color: #e2e8f0; color: #475569; }
        .pcek .pcek-btn.ghost:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .pcek .pcek-btn.primary { background: #4e46e5; color: #fff; }
        .pcek .pcek-btn.primary:hover { background: #4338ca; color: #fff; }
        .pcek .pcek-btn.success { background: #16a34a; color: #fff; }
        .pcek .pcek-btn.success:hover { background: #15803d; color: #fff; }
        .pcek .pcek-btn.danger { background: #fff; border-color: #fecaca; color: #dc2626; }
        .pcek .pcek-btn.danger:hover { background: #fef2f2; border-color: #fca5a5; }
        /* Form unggah hasil */
        .pcek .pcek-form { margin-top: 14px; padding: 15px; border: 1px solid #d1fae5; border-radius: 14px; background: linear-gradient(180deg, #f6fefa, #fff); }
        .pcek .pcek-form-head { display: flex; align-items: center; gap: 11px; margin-bottom: 13px; }
        .pcek .pcek-form-ic { width: 38px; height: 38px; flex-shrink: 0; border-radius: 11px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; }
        .pcek .pcek-form-ic i.bi { display: flex; align-items: center; justify-content: center; line-height: 1; }
        .pcek .pcek-form-ic i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-form-head b { display: block; font-size: .88rem; font-weight: 700; color: #14532d; }
        .pcek .pcek-form-head small { display: block; font-size: .74rem; color: #64748b; }
        .pcek .pcek-form-x { flex-shrink: 0; width: 30px; height: 30px; border: 0; border-radius: 8px; background: transparent; color: #94a3b8; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .16s, color .16s; }
        .pcek .pcek-form-x:hover { background: #f1f5f9; color: #475569; }
        .pcek .pcek-form-x i.bi { display: flex; line-height: 1; font-size: .82rem; }
        /* Dropzone hasil (admin) */
        .pcek .pcek-drop { position: relative; display: block; padding: 16px 14px; border: 2px dashed #bbf7d0; border-radius: 12px; background: #fff; cursor: pointer; text-align: center; transition: border-color .18s, background .18s; }
        .pcek .pcek-drop:hover { border-color: #4ade80; background: #f6fefa; }
        .pcek .pcek-drop-input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .pcek .pcek-drop-state { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px; }
        .pcek .pcek-drop-state i.bi { display: flex; align-items: center; line-height: 1; font-size: 1.15rem; }
        .pcek .pcek-drop-state i.bi::before { display: block; line-height: 1; }
        .pcek .pcek-drop-state i.bi.up { color: #16a34a; }
        .pcek .pcek-drop-state i.bi.ok { color: #16a34a; }
        .pcek .pcek-drop-state .nm { font-size: .84rem; font-weight: 600; color: #334155; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pcek .pcek-drop-state .hint { font-size: .74rem; color: #94a3b8; width: 100%; }
        .pcek .pcek-drop-state .chg { font-size: .72rem; font-weight: 700; color: #15803d; background: #dcfce7; padding: 3px 9px; border-radius: 99px; }
        .pcek .pcek-spin { animation: pcekSpin 1s linear infinite; color: #16a34a; }
        @keyframes pcekSpin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .pcek .pcek-spin { animation: none; } }
        /* Baris bawah form: persen di kiri, tombol rata kanan */
        .pcek .pcek-form-foot { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; margin-top: 14px; }
        .pcek .pcek-form-btns { display: flex; gap: 8px; margin-left: auto; }
        @media (max-width: 479px) {
            .pcek .pcek-form-foot { align-items: stretch; }
            .pcek .pcek-persen { width: 100%; }
            .pcek .pcek-persen-wrap { max-width: none; }
            .pcek .pcek-form-btns { width: 100%; margin-left: 0; }
            .pcek .pcek-form-btns .pcek-btn { flex: 1; }
        }
        /* Baris aksi form hasil — dua tombol mengisi penuh, tanpa ruang kosong */
        .pcek .pcek-aksi { display: flex; gap: 8px; margin-top: 14px; }
        .pcek .pcek-aksi .pcek-btn { flex: 1; }
        .pcek .pcek-aksi .pcek-btn.success { flex: 2; }

        /* Slot berkas hasil (plagiasi / AI / dokumen) */
        .pcek .pcek-slot { display: flex; gap: 11px; padding: 13px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; }
        .pcek .pcek-slot-no { width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; background: #dcfce7; color: #15803d; font-size: .76rem; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        .pcek .pcek-slot-body { flex: 1; min-width: 0; }
        .pcek .pcek-slot-lbl { display: block; font-size: .82rem; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .pcek .pcek-slot-lbl span { font-weight: 500; color: #94a3b8; font-size: .72rem; }
        .pcek .pcek-slot .pcek-drop { padding: 12px 10px; }
        .pcek .pcek-slot .pcek-persen-wrap { max-width: 150px; }

        /* Persen kemiripan */
        .pcek .pcek-lbl { display: block; font-size: .78rem; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .pcek .pcek-lbl span { font-weight: 500; color: #94a3b8; font-size: .72rem; }
        .pcek .pcek-persen-wrap { position: relative; max-width: 180px; }
        .pcek .pcek-persen-num { width: 100%; height: 40px; padding: 0 46px 0 13px; font-size: .95rem; font-weight: 700; color: #334155; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; outline: none; transition: border-color .18s, box-shadow .18s; }
        .pcek .pcek-persen-num:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, .13); }
        .pcek .pcek-persen-suffix { position: absolute; top: 50%; right: 5px; transform: translateY(-50%); min-width: 34px; text-align: center; padding: 6px 8px; border-radius: 8px; background: #dcfce7; color: #15803d; font-size: .82rem; font-weight: 700; pointer-events: none; }
        .pcek .pcek-auto { display: flex; align-items: flex-start; gap: 7px; margin-top: 8px; font-size: .76rem; color: #15803d; line-height: 1.45; }
        .pcek .pcek-manual { display: flex; align-items: flex-start; gap: 7px; margin-top: 8px; font-size: .76rem; color: #b45309; line-height: 1.45; }
        .pcek .pcek-pilih { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
        .pcek .pcek-pilih-btn { padding: 3px 12px; border-radius: 99px; border: 1px solid #fbbf24;
            background: #fffbeb; color: #b45309; font-size: .78rem; font-weight: 700; cursor: pointer; }
        .pcek .pcek-pilih-btn:hover { background: #fde68a; }
        .pcek .pcek-auto i.bi { flex-shrink: 0; margin-top: .12rem; display: flex; line-height: 1; }
        .pcek .pcek-auto i.bi::before { display: block; line-height: 1; }
    </style>
    <div class="card border-0 shadow-sm rounded-4 mb-4 pcek">
        <div class="card-body p-4">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="pcek-head-ic"><i class="bi bi-shield-check"></i></div>
                <div class="flex-grow-1" style="min-width:0;">
                    <h5 class="fw-bold mb-0">Pengecekan Plagiasi</h5>
                    <small class="text-muted">Kelola dokumen &amp; hasil pengecekan customer</small>
                </div>
                <span class="badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1 flex-shrink-0 {{ $jSisa > 0 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                    <i class="bi bi-collection"></i> {{ $jSisa }} sisa
                </span>
            </div>

            {{-- Kuota terpakai (progress) --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted d-inline-flex align-items-center gap-1"><i class="bi bi-graph-up-arrow"></i> Kuota terpakai</small>
                    <small class="fw-semibold">{{ $jTerpakai }} / {{ $jKuota }}</small>
                </div>
                <div class="progress" style="height:8px; border-radius:99px; background:#f1f5f9;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $jKuota > 0 ? round($jTerpakai / $jKuota * 100) : 0 }}%;"></div>
                </div>
            </div>

            {{-- Penyelesaian manual: dipakai bila customer tak memakai seluruh
                 kuotanya, supaya omset tetap masuk cash flow & tak menggantung. --}}
            @if ($order->status !== 'completed' && $order->uploads->where('status', 'selesai')->isNotEmpty())
            <div class="pcek-finish">
                <div class="flex-grow-1" style="min-width:0;">
                    <b>Selesaikan pesanan jasa ini?</b>
                    <small>
                        @if ($jSisa > 0)
                            Masih ada <b>{{ $jSisa }}</b> kuota tersisa. Selesaikan bila customer tak akan memakainya lagi — omset akan tercatat di cash flow.
                        @else
                            Semua kuota terpakai. Pesanan akan diselesaikan otomatis setelah hasil terakhir diunggah.
                        @endif
                    </small>
                </div>
                <button type="button" class="pcek-btn primary flex-shrink-0 pcek-konfirmasi"
                    data-action="selesaikanJasa"
                    data-title="Selesaikan pesanan jasa?"
                    data-text="Item akan ditandai terkirim dan omset dicatat ke cash flow."
                    data-confirm="Ya, selesaikan"
                    data-icon="question">
                    <i class="bi bi-check2-circle"></i> Selesaikan Pesanan
                </button>
            </div>
            @endif

            {{-- Link customer (jaga-jaga bila customer lupa/hilang link) --}}
            <label class="form-label small text-muted mb-1 d-inline-flex align-items-center gap-1"><i class="bi bi-link-45deg"></i> Link customer (bila lupa / hilang)</label>
            <div class="pcek-link-box mb-4">
                <input type="text" id="cust-cek-link" readonly value="{{ url('/cek/'.$order->share_token) }}">
                <button type="button" onclick="salinLinkCek()"><i class="bi bi-clipboard"></i> Salin</button>
            </div>

            @forelse ($order->uploads->sortByDesc('created_at') as $up)
            <div class="pcek-item mb-3" wire:key="adm-up-{{ $up->id }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="pcek-fileic"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-semibold text-dark text-truncate">
                            {{ $up->nama_asli }}
                            @if ($up->jenisLabel())
                            <span class="badge bg-{{ $up->jenisWarna() }}-subtle text-{{ $up->jenisWarna() }} rounded-pill ms-1" style="font-size:.68rem; vertical-align:middle;">{{ $up->jenisLabel() }}</span>
                            @endif
                        </div>
                        <div class="text-muted d-inline-flex align-items-center gap-2 flex-wrap" style="font-size:.8rem;">
                            <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-clock"></i> {{ $up->created_at->format('d M Y H:i') }}</span>
                            <span class="text-secondary">&middot;</span>
                            <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-hdd"></i> {{ $up->ukuranLabel() }}</span>
                        </div>
                    </div>
                    <span class="badge bg-{{ $up->statusWarna() }}-subtle text-{{ $up->statusWarna() }} rounded-pill flex-shrink-0 d-inline-flex align-items-center gap-1 px-3 py-2">
                        <i class="bi {{ $up->statusIcon() }}"></i> {{ $up->statusLabel() }}
                    </span>
                </div>

                {{-- Setelan exclude + catatan dari customer --}}
                <div class="pcek-set">
                    <div class="pcek-set-row">
                        <span class="pcek-set-lbl"><i class="bi bi-sliders"></i> Kecualikan</span>
                        <span class="pcek-set-vals">
                            @forelse ($up->daftarExclude() as $ex)
                            <span class="pcek-c">{{ $ex }}</span>
                            @empty
                            <span class="pcek-c off">Tidak ada</span>
                            @endforelse
                        </span>
                    </div>

                    @if ($up->catatan)
                    <div class="pcek-note">
                        <i class="bi bi-chat-left-quote"></i>
                        <span><b>Catatan customer:</b> {{ $up->catatan }}</span>
                    </div>
                    @endif
                </div>

                {{-- Persen kemiripan bila sudah selesai --}}
                @if ($up->status === 'selesai' && (! is_null($up->persentase) || ! is_null($up->persentase_ai)))
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @if (! is_null($up->persentase))
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-graph-up"></i> Plagiasi: {{ $up->persentase }}%
                    </span>
                    @endif
                    @if (! is_null($up->persentase_ai))
                    <span class="badge bg-info-subtle text-info rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-robot"></i> AI: {{ $up->persentase_ai }}%
                    </span>
                    @endif
                </div>
                @endif

                {{-- Aksi — satu aksi utama per status, sisanya netral; destruktif di kanan --}}
                <div class="pcek-actions">
                    <a href="{{ route('admin.jasa.berkas', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-download"></i> File Customer
                    </a>
                    @if ($up->pdf_path)
                    {{-- Parafrase: PDF acuan jumlah halaman (file utama = DOCX kerja) --}}
                    <a href="{{ route('admin.jasa.pdf', $up) }}" class="pcek-btn ghost" title="PDF acuan jumlah halaman">
                        <i class="bi bi-filetype-pdf"></i> PDF Acuan
                    </a>
                    @endif

                    @if ($up->status === 'menunggu')
                    <button type="button" wire:click="mulaiProses('{{ $up->id }}')" class="pcek-btn primary">
                        <i class="bi bi-play-fill"></i> Mulai Proses
                    </button>
                    @endif

                    @if (in_array($up->status, ['menunggu', 'diproses']))
                    <button type="button" wire:click="bukaUploadHasil('{{ $up->id }}')"
                        class="pcek-btn {{ $up->status === 'diproses' ? 'primary' : 'ghost' }}">
                        <i class="bi bi-cloud-arrow-up"></i> Unggah Hasil
                    </button>
                    @endif

                    @if ($up->status === 'selesai')
                    @if ($up->hasil_path)
                    <a href="{{ route('admin.jasa.hasil', $up) }}" class="pcek-btn primary">
                        <i class="bi bi-file-earmark-check"></i> Hasil Plagiasi
                    </a>
                    @endif
                    @if ($up->hasil_ai_path)
                    <a href="{{ route('admin.jasa.hasil-ai', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-robot"></i> Hasil AI
                    </a>
                    @endif
                    @if ($up->hasil_docx_path)
                    <a href="{{ route('admin.jasa.hasil-docx', $up) }}" class="pcek-btn ghost">
                        <i class="bi bi-file-earmark-word"></i> Dokumen Hasil
                    </a>
                    @endif
                    <button type="button" wire:click="bukaUploadHasil('{{ $up->id }}')" class="pcek-btn ghost">
                        <i class="bi bi-arrow-repeat"></i> Ganti Hasil
                    </button>
                    @endif

                    @if (in_array($up->status, ['menunggu', 'diproses']))
                    <button type="button" class="pcek-btn danger pcek-push pcek-konfirmasi" title="Batalkan pengecekan"
                        data-action="batalkanPengecekan"
                        data-arg="{{ $up->id }}"
                        data-title="Batalkan pengecekan ini?"
                        data-text="Kuota customer akan dikembalikan."
                        data-confirm="Ya, batalkan"
                        data-icon="warning">
                        <i class="bi bi-x-lg"></i> Batalkan
                    </button>
                    @endif
                </div>

                {{-- Form unggah hasil (inline, saat aktif) --}}
                @if ($uploadAktifId === $up->id)
                <div class="pcek-form" wire:key="adm-hasilform-{{ $up->id }}">
                    <div class="pcek-form-head">
                        <span class="pcek-form-ic"><i class="bi bi-file-earmark-arrow-up"></i></span>
                        <div class="flex-grow-1" style="min-width:0;">
                            <b>Unggah Hasil Pengecekan</b>
                            <small>File hasil langsung bisa diunduh customer</small>
                        </div>
                        <button type="button" wire:click="tutupUploadHasil" class="pcek-form-x" title="Tutup"><i class="bi bi-x-lg"></i></button>
                    </div>

                    {{-- Hasil cek PLAGIASI — hanya bila layanannya memang dibeli --}}
                    @if ($this->slotTampil('plagiasi'))
                    <div class="pcek-slot">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('plagiasi') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl">Hasil Cek Plagiasi <span>PDF / DOCX</span></label>
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilFile" accept=".pdf,.docx" class="pcek-drop-input">
                                <span wire:loading wire:target="hasilFile" class="pcek-drop-state">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah &amp; membaca…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilFile" class="pcek-drop-state">
                                    @if ($hasilFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            <div class="pcek-persen mt-2">
                                <label class="pcek-lbl">Persen plagiasi <span>boleh dikosongkan</span></label>
                                <div class="pcek-persen-wrap">
                                    <input type="number" min="0" max="100" wire:model="persentaseInput" class="pcek-persen-num" placeholder="23">
                                    <span class="pcek-persen-suffix">%</span>
                                </div>
                                @if ($persenTerbacaOtomatis)
                                <div class="pcek-auto"><i class="bi bi-magic"></i><span>Terbaca otomatis — mohon dicek.</span></div>
                                @elseif ($persenGagalBaca)
                                <div class="pcek-manual"><i class="bi bi-pencil-square"></i><span>Persen tak terbaca dari berkas ini — isi manual dari PDF.</span></div>
                                @endif
                                @error('persentaseInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    @endif

                    {{-- Hasil cek AI — hanya bila layanannya memang dibeli --}}
                    @if ($this->slotTampil('ai'))
                    <div class="pcek-slot">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('ai') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl">Hasil Cek AI <span>PDF</span></label>
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilAiFile" accept=".pdf" class="pcek-drop-input">
                                <span wire:loading wire:target="hasilAiFile" class="pcek-drop-state">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah &amp; membaca…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilAiFile" class="pcek-drop-state">
                                    @if ($hasilAiFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilAiFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_ai_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_ai_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilAiFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            <div class="pcek-persen mt-2">
                                <label class="pcek-lbl">Persen AI <span>boleh dikosongkan</span></label>
                                <div class="pcek-persen-wrap">
                                    <input type="number" min="0" max="100" wire:model="persentaseAiInput" class="pcek-persen-num" placeholder="8">
                                    <span class="pcek-persen-suffix">%</span>
                                </div>
                                @if ($persenAiTerbacaOtomatis)
                                <div class="pcek-auto">
                                    <i class="bi bi-magic"></i>
                                    <span>
                                        Terbaca otomatis dari <b>{{ $sumberAi === 'gptzero' ? 'GPTZero' : 'Turnitin' }}</b>
                                        ({{ $labelAi ?? 'Persen AI' }}) — mohon dicek.
                                        @if ($sumberAi === 'gptzero')
                                        <br><span class="text-muted">Catatan: GPTZero melaporkan <i>probabilitas dokumen dibuat AI</i>, bukan persentase teks AI.</span>
                                        @endif
                                    </span>
                                </div>
                                @elseif (count($pilihanAi) > 1)
                                <div class="pcek-manual">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>
                                        Berkas memuat <b>{{ count($pilihanAi) }} laporan bertumpuk</b>
                                        ({{ implode('%, ', $pilihanAi) }}%). Diisi <b>{{ end($pilihanAi) }}%</b> —
                                        nilai lapisan teratas, yang tampak saat PDF dibuka.
                                        Nilai lain tersembunyi di bawahnya; ganti bila perlu:
                                        <span class="pcek-pilih">
                                            @foreach ($pilihanAi as $nilai)
                                            <button type="button" class="pcek-pilih-btn"
                                                wire:click="$set('persentaseAiInput', {{ $nilai }})">{{ $nilai }}%</button>
                                            @endforeach
                                        </span>
                                    </span>
                                </div>
                                @elseif ($persenAiGagalBaca)
                                <div class="pcek-manual"><i class="bi bi-pencil-square"></i><span>Format laporan tak dikenali — isi persen manual dari PDF.</span></div>
                                @endif
                                @error('persentaseAiInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    @endif

                    {{-- Dokumen hasil parafrase — khusus jasa per halaman --}}
                    @if ($this->slotTampil('docx'))
                    <div class="pcek-slot">
                        <span class="pcek-slot-no">{{ $this->nomorSlot('docx') }}</span>
                        <div class="pcek-slot-body">
                            <label class="pcek-slot-lbl">Dokumen Hasil (Parafrase) <span>DOCX</span></label>
                            <label class="pcek-drop">
                                <input type="file" wire:model="hasilDocxFile" accept=".docx" class="pcek-drop-input">
                                <span wire:loading wire:target="hasilDocxFile" class="pcek-drop-state">
                                    <i class="bi bi-arrow-repeat pcek-spin"></i>
                                    <span class="nm">Mengunggah…</span>
                                </span>
                                <span wire:loading.remove wire:target="hasilDocxFile" class="pcek-drop-state">
                                    @if ($hasilDocxFile)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $hasilDocxFile->getClientOriginalName() }}</span>
                                    <span class="chg">Ganti</span>
                                    @elseif ($up->hasil_docx_nama)
                                    <i class="bi bi-file-earmark-check ok"></i>
                                    <span class="nm">{{ $up->hasil_docx_nama }}</span>
                                    <span class="chg">Ganti</span>
                                    @else
                                    <i class="bi bi-cloud-arrow-up up"></i>
                                    <span class="nm">Pilih file atau seret ke sini</span>
                                    @endif
                                </span>
                            </label>
                            @error('hasilDocxFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif

                    {{-- Aksi — kedua tombol mengisi penuh lebar form --}}
                    <div class="pcek-aksi">
                        <button type="button" wire:click="tutupUploadHasil" class="pcek-btn ghost">Batal</button>
                        <button type="button" wire:click="simpanHasil" wire:loading.attr="disabled"
                            wire:target="simpanHasil,hasilFile,hasilAiFile,hasilDocxFile" class="pcek-btn success">
                            <span wire:loading.remove wire:target="simpanHasil" class="d-inline-flex align-items-center gap-2"><i class="bi bi-check-lg"></i> Simpan Hasil</span>
                            <span wire:loading wire:target="simpanHasil" class="d-inline-flex align-items-center gap-2"><i class="bi bi-hourglass-split"></i> Menyimpan…</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-4">
                <i class="bi bi-inbox d-block mb-2" style="font-size:2rem; opacity:.45;"></i>
                <div class="fw-semibold text-dark">Belum ada dokumen</div>
                <small>Customer mengunggah lewat link pengecekan di atas.</small>
            </div>
            @endforelse
        </div>
    </div>
    @endif

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
                                {{-- Add-on & jumlah halaman (khusus produk jasa) --}}
                                @if (! empty($item->addons) || $item->jumlah_halaman)
                                {{-- Rata kiri, seragam dengan badge ebook/bonus di kolom yang sama --}}
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @if ($item->jumlah_halaman)
                                    <span class="badge bg-info-subtle text-info border border-info rounded-pill" style="font-size:.68rem;">
                                        <i class="bi bi-file-earmark-text"></i>
                                        {{ $item->halaman_dihitung ?? $item->jumlah_halaman }} dari {{ $item->jumlah_halaman }} halaman
                                    </span>
                                    @endif
                                    @if ($item->halaman_dikecualikan)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill" style="font-size:.68rem;"
                                        title="Halaman ini TIDAK perlu dikerjakan">
                                        <i class="bi bi-slash-circle"></i> Lewati hal. {{ $item->halaman_dikecualikan }}
                                    </span>
                                    @endif
                                    @foreach (($item->addons ?? []) as $ad)
                                    <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill" style="font-size:.68rem;"
                                        title="Tambahan Rp {{ number_format($ad['harga'] ?? 0, 0, ',', '.') }}">
                                        <i class="bi bi-plus-circle"></i> {{ $ad['nama'] ?? '-' }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
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
                            @php
                                // Harga ASLI (sebelum diskon) — dihitung dari produk; fallback ke harga tersimpan.
                                $prod = $item->product;
                                $hargaAsli = (int) $item->price;
                                if ($prod) {
                                    $inPkg = $prod->daftarHarga()->contains(fn ($r) => $r['durasi_type'] === $item->duration_type && (int) $r['durasi_value'] === (int) $item->duration_value);
                                    if ($inPkg) {
                                        $hargaAsli = (int) $prod->hargaUntuk((int) $item->duration_value, $item->duration_type);
                                    } else {
                                        $perB = (int) ($prod->harga_perbulan ?? 0);
                                        $hargaAsli = ($item->duration_type === 'bulan' && $perB > 0) ? $perB * (int) $item->duration_value : (int) $item->price;
                                    }
                                    if ($hargaAsli <= 0) {
                                        $hargaAsli = (int) $item->price;
                                    }
                                }
                            @endphp
                            <td class="text-end">Rp {{ number_format($hargaAsli, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($hargaAsli * $item->quantity, 0, ',', '.') }}</td>
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
                            <span>Subtotal</span>
                            <span class="fw-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
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
                        @if($order->total_discount > 0)
                        <div class="summary-row" style="border-top:1px dashed #e5e7eb; padding-top:.5rem;">
                            <span class="fw-semibold">Setelah Diskon</span>
                            <span class="fw-semibold">Rp {{ number_format($order->subtotal - $order->total_discount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->unique_code > 0)
                        <div class="summary-row">
                            <span>Kode Unik <i class="bi bi-info-circle" title="Untuk verifikasi pembayaran"></i></span>
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

    @include('livewire.layout.sweetalert')
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

    // Popup glossy untuk memperbesar bukti pembayaran (seragam dgn fitur lain).
    if (!window.__buktiZoomBound) {
        window.__buktiZoomBound = true;
        document.addEventListener('click', function (e) {
            const trigger = e.target.closest && e.target.closest('.bukti-zoom-trigger');
            if (!trigger) return;
            e.preventDefault();
            const url = trigger.getAttribute('data-bukti-url');
            if (!url) return;
            if (typeof Swal === 'undefined') { window.open(url, '_blank'); return; }
            Swal.fire({
                // Gambar dibatasi ke ukuran layar agar muat tanpa perlu scroll.
                html: '<div style="display:flex; align-items:center; justify-content:center; width:100%;"><img src="' + url + '" alt="Bukti pembayaran" style="max-width:88vw; max-height:82vh; width:auto; height:auto; object-fit:contain; border-radius:12px;"></div>',
                background: 'rgba(255, 255, 255, 0.92)',
                backdrop: 'rgba(139, 92, 246, 0.15)',
                customClass: { popup: 'swal-glossy-popup rounded-4 shadow-lg border-0' },
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                padding: '1rem',
            });
        });
    }

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

        Livewire.on('order-updated', (e) => {
            const msg = (e && (e.message ?? (Array.isArray(e) ? e[0]?.message : null))) || 'Berhasil diperbarui.';
            Swal.fire({ title: 'Berhasil', text: msg, icon: 'success', timer: 2200, showConfirmButton: false, ...waGlossyConfig });
        });
    });

    function salinLinkCek() {
        const el = document.getElementById('cust-cek-link');
        const txt = el ? (el.value || el.textContent || '').trim() : '';
        const done = () => {
            if (window.Swal) Swal.fire({
                title: 'Link Disalin',
                text: 'Link pengecekan customer berhasil disalin ke clipboard.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                ...waGlossyConfig
            });
        };
        if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(txt).then(done).catch(done);
        else if (el) { el.select(); try { document.execCommand('copy'); } catch (err) {} done(); }
    }

    // Konfirmasi seragam (SweetAlert glossy) untuk tombol ber-class .pcek-konfirmasi,
    // menggantikan wire:confirm bawaan browser. Aksi & teks diambil dari data-*.
    if (!window.__pcekKonfirmasiBound) {
        window.__pcekKonfirmasiBound = true;
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.pcek-konfirmasi');
            if (!btn) return;
            e.preventDefault();

            const method = btn.dataset.action;
            const arg = btn.dataset.arg || null;
            const component = btn.closest('[wire\\:id]');
            if (!method || !component) return;

            const jalankan = () => {
                const lw = Livewire.find(component.getAttribute('wire:id'));
                if (lw) arg ? lw.call(method, arg) : lw.call(method);
            };

            if (typeof Swal === 'undefined') { jalankan(); return; }

            Swal.fire({
                title: btn.dataset.title || 'Anda yakin?',
                text: btn.dataset.text || '',
                icon: btn.dataset.icon || 'question',
                showCancelButton: true,
                confirmButtonText: btn.dataset.confirm || 'Ya, lanjutkan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                ...waGlossyConfig,
                // waGlossyConfig tak mendefinisikan kelas tombol (buttonsStyling:false),
                // jadi tombol akan polos. Sisipkan kelas glossy standar agar seragam
                // dengan konfirmasi hapus di seluruh admin.
                customClass: {
                    ...waGlossyConfig.customClass,
                    confirmButton: 'btn-glossy-confirm',
                    cancelButton: 'btn-glossy-cancel',
                },
            }).then((r) => { if (r.isConfirmed) jalankan(); });
        });
    }

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