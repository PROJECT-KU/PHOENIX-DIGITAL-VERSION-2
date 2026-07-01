<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Bukti Pemesanan {{ $order->order_number }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        /* ===== HEADER ===== */
        .header {
            background: #4e46e5;
            color: #ffffff;
            padding: 26px 44px 22px 44px;
        }

        .brand-name {
            font-size: 21px;
            font-weight: bold;
            letter-spacing: .4px;
        }

        .brand-sub {
            font-size: 9.5px;
            color: #cfccff;
            margin-top: 4px;
        }

        .doc-type {
            font-size: 15px;
            font-weight: bold;
            text-align: right;
            letter-spacing: 1px;
        }

        .doc-no {
            font-size: 10.5px;
            color: #cfccff;
            text-align: right;
            margin-top: 4px;
        }

        .accent-bar {
            height: 5px;
            background: #22c55e;
        }

        .content {
            padding: 28px 44px 0 44px;
        }

        /* ===== INFO ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .info-table td {
            vertical-align: top;
        }

        .info-card {
            border: 1px solid #e9eaff;
            background: #f9f9ff;
            border-radius: 10px;
            padding: 13px 15px;
        }

        .info-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #9095a6;
            margin-bottom: 5px;
        }

        .info-name {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }

        .info-line {
            font-size: 11px;
            color: #4b5563;
            margin-top: 3px;
        }

        .status-stamp {
            display: inline-block;
            background: #dcfce7;
            color: #15803d;
            border: 2px solid #22c55e;
            border-radius: 10px;
            padding: 9px 22px;
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1.5px;
        }

        .pay-line {
            font-size: 10px;
            color: #6b7280;
            margin-top: 7px;
        }

        /* ===== ITEMS ===== */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.items thead th {
            background: #eef0ff;
            color: #4e46e5;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: left;
            padding: 10px 13px;
        }

        /* Semua kolom (header & isi) dibuat seragam rata-kiri. */
        table.items th,
        table.items td {
            text-align: left;
        }

        table.items tbody td {
            border-bottom: 1px solid #eef0f6;
            padding: 11px 13px;
            font-size: 11px;
            vertical-align: top;
        }

        .prod-name {
            font-weight: bold;
            color: #111827;
            font-size: 11.5px;
        }

        .prod-sub {
            font-size: 9.5px;
            color: #98a1b3;
            margin-top: 3px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ===== SUMMARY ===== */
        .summary {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            padding: 5px 0;
            font-size: 12px;
        }

        .summary .label {
            color: #4b5563;
        }

        .summary .val {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }

        .total-box {
            background: #4e46e5;
            border-radius: 10px;
            padding: 13px 18px;
        }

        .t-label {
            color: #cfccff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .t-val {
            color: #ffffff;
            font-size: 20px;
            font-weight: bold;
            text-align: right;
        }

        .note-box {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 13px 15px;
            font-size: 10px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* ===== VALIDATION (TTD + CAP) ===== */
        .valid-wrap {
            width: 100%;
            border-collapse: collapse;
            margin-top: 26px;
        }

        .valid-wrap td {
            vertical-align: top;
        }

        .valid-block {
            position: relative;
            width: 252px;
            margin-left: auto;
            text-align: center;
        }

        .vb-caption {
            font-size: 11px;
            color: #374151;
        }

        .vb-pdw {
            font-size: 11px;
            font-weight: bold;
            color: #4e46e5;
        }

        .vb-sign {
            height: 78px;
            overflow: hidden;
        }

        .vb-sign img {
            width: 150px;
            height: auto;
            margin-top: 6px;
        }

        .vb-name {
            border-top: 1px solid #9ca3af;
            padding-top: 6px;
            font-weight: bold;
            color: #111827;
            font-size: 11px;
        }

        .vb-cap {
            position: absolute;
            top: 16px;
            left: 0;
            width: 130px;
            height: 130px;
            opacity: .9;
            z-index: 5;
        }

        /* ===== FOOTER ===== */
        .footer {
            margin: 28px 44px 0 44px;
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
            font-size: 9.5px;
            color: #9ca3af;
            text-align: center;
        }

        .footer .thanks {
            color: #4e46e5;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>
    @php
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $subtotal = $order->items->sum(fn ($i) => $i->price * $i->quantity);
        $paymentMethod = $order->payment_method ? strtoupper(str_replace('_', ' ', $order->payment_method)) : '-';
        $paidAt = $order->paid_at ?? $order->created_at;

        $capPath = file_exists(storage_path('app/public/img/archive/cap.png'))
            ? storage_path('app/public/img/archive/cap.png')
            : public_path('assets/img/logophoenix.png');
        $ttdPath = file_exists(storage_path('app/public/img/archive/ttd.png'))
            ? storage_path('app/public/img/archive/ttd.png')
            : public_path('assets/img/ttd.png');

        // Gabungkan item paket bundling menjadi 1 baris (nama paket), bukan per item.
        // Item paket disimpan dengan format nama: "[Nama Paket] Produk".
        $displayRows = [];
        $bundleIndex = [];
        foreach ($order->items as $item) {
            if (preg_match('/^\[(.+?)\]\s*(.*)$/u', (string) $item->product_name, $m)) {
                $bundleName = trim($m[1]);
                $sub = trim($m[2]);
                if (! isset($bundleIndex[$bundleName])) {
                    $bundleIndex[$bundleName] = count($displayRows);
                    $displayRows[] = (object) [
                        'type' => 'bundle',
                        'name' => $bundleName,
                        'qty' => 1,
                        'price' => 0,
                        'subtotal' => 0,
                        'includes' => [],
                    ];
                }
                $row = $displayRows[$bundleIndex[$bundleName]];
                $row->price += (float) $item->price * (int) $item->quantity;
                $row->subtotal += (float) $item->price * (int) $item->quantity;
                if ($sub !== '') {
                    $row->includes[] = $sub;
                }
            } else {
                $displayRows[] = (object) [
                    'type' => 'product',
                    'name' => $item->product_name,
                    'qty' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => (float) $item->price * (int) $item->quantity,
                    'item' => $item,
                ];
            }
        }
    @endphp

    <!-- HEADER -->
    <div class="header">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="vertical-align:top;">
                    <div class="brand-name">PHOENIX DIGITAL WAREHOUSE</div>
                    <div class="brand-sub">phoenixdigital.id &bull; IG: phoenixdigital_warehouse</div>
                </td>
                <td style="vertical-align:top;">
                    <div class="doc-type">BUKTI PEMESANAN</div>
                    <div class="doc-no">No. {{ $order->order_number }}</div>
                    <div class="doc-no">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-bar"></div>

    <div class="content">
        <!-- INFO -->
        <table class="info-table">
            <tr>
                <td style="width:56%; padding-right:11px;">
                    <div class="info-card">
                        <div class="info-label">Pelanggan</div>
                        <div class="info-name">{{ $order->customer->nama ?? '-' }}</div>
                        <div class="info-line">{{ $order->customer->no_hp ?? '-' }}</div>
                        <div class="info-line">{{ $order->customer->email ?? '-' }}</div>
                    </div>
                </td>
                <td style="width:44%; padding-left:11px; text-align:center;">
                    <div class="status-stamp">&#10003; LUNAS</div>
                    <div class="pay-line">Metode: {{ $paymentMethod }}</div>
                    <div class="pay-line">Dibayar: {{ \Carbon\Carbon::parse($paidAt)->translatedFormat('d F Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- ITEMS -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width:24px;">No</th>
                    <th>Produk</th>
                    <th class="text-center" style="width:38px;">Qty</th>
                    <th class="text-end" style="width:100px;">Harga</th>
                    <th class="text-end" style="width:105px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($displayRows as $i => $row)
                <tr>
                    <td class="text-center" style="width:24px;">{{ $i + 1 }}</td>
                    <td>
                        @if ($row->type === 'bundle')
                        <div class="prod-name">
                            {{ $row->name }}
                            <span style="display:inline-block; background:#ede9fe; color:#6d28d9; font-size:8px; font-weight:bold; padding:1px 6px; border-radius:8px; vertical-align:middle;">PAKET</span>
                        </div>
                        @if (count($row->includes))
                        <div class="prod-sub">Termasuk: {{ implode(', ', $row->includes) }}</div>
                        @endif
                        @else
                        @php $item = $row->item; @endphp
                        <div class="prod-name">{{ $item->product_name }}</div>
                        <div class="prod-sub">
                            Durasi: {{ $item->getFullDurationLabel() }}
                            @if ($item->start_date && $item->end_date)
                            &nbsp;&bull;&nbsp; Aktif:
                            {{ \Carbon\Carbon::parse($item->start_date)->translatedFormat('d M Y') }} &ndash;
                            {{ \Carbon\Carbon::parse($item->end_date)->translatedFormat('d M Y') }}
                            @endif
                        </div>
                        @if ($item->ebooks->count() || $item->bonus_description)
                        <div class="prod-sub" style="color:#059669; font-weight:bold; margin-top:3px;">
                            &#127873; Bonus:
                            {{ $item->ebooks->pluck('judul')->implode(', ') }}@if ($item->ebooks->count() && $item->bonus_description), @endif{{ $item->bonus_description }}
                        </div>
                        @endif
                        @endif
                    </td>
                    <td class="text-center" style="width:38px;">{{ $row->qty }}</td>
                    <td class="text-end" style="width:100px;">{{ $rp($row->price) }}</td>
                    <td class="text-end" style="width:105px;">{{ $rp($row->subtotal) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding:16px; color:#9ca3af;">Tidak ada item.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- SUMMARY -->
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:48%; padding-right:18px; vertical-align:top;">
                    <div class="note-box">
                        <strong style="color:#4b5563;">Catatan</strong><br>
                        Dokumen ini adalah bukti pemesanan yang sah dan menyatakan pembayaran telah
                        <strong>LUNAS</strong>. Mohon disimpan sebagai referensi transaksi Anda.
                    </div>
                </td>
                <td style="width:52%; vertical-align:top;">
                    <table class="summary">
                        <tr>
                            <td class="label">Subtotal</td>
                            <td class="val">{{ $rp($subtotal) }}</td>
                        </tr>
                        @if ($order->promo_discount > 0)
                        <tr>
                            <td class="label">Diskon Promo</td>
                            <td class="val" style="color:#dc2626;">- {{ $rp($order->promo_discount) }}</td>
                        </tr>
                        @endif
                        @if ($order->points_discount > 0)
                        <tr>
                            <td class="label">Diskon Poin</td>
                            <td class="val" style="color:#dc2626;">- {{ $rp($order->points_discount) }}</td>
                        </tr>
                        @endif
                        @if ($order->referral_discount > 0)
                        <tr>
                            <td class="label">Diskon Referral</td>
                            <td class="val" style="color:#dc2626;">- {{ $rp($order->referral_discount) }}</td>
                        </tr>
                        @endif
                        @if ($order->unique_code > 0)
                        <tr>
                            <td class="label">Kode Unik</td>
                            <td class="val">+ {{ $rp($order->unique_code) }}</td>
                        </tr>
                        @endif
                    </table>
                    <div class="total-box" style="margin-top:8px;">
                        <table style="width:100%; border-collapse:collapse;">
                            <tr>
                                <td class="t-label" style="vertical-align:middle;">Total Dibayar</td>
                                <td class="t-val">{{ $rp($order->total) }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- VALIDATION: TANDA TANGAN + CAP -->
        <table class="valid-wrap">
            <tr>
                <td style="width:52%;">&nbsp;</td>
                <td style="width:48%;">
                    <div class="valid-block">
                        <div class="vb-caption">Hormat kami,</div>
                        <div class="vb-pdw">Phoenix Digital Warehouse</div>
                        <div class="vb-sign">
                            <img src="{{ $ttdPath }}" alt="ttd">
                        </div>
                        <div class="vb-name">Admin Phoenix Digital</div>
                        <img class="vb-cap" src="{{ $capPath }}" alt="cap">
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="thanks">Terima kasih telah berbelanja di Phoenix Digital Warehouse!</div>
        Dokumen dibuat &amp; disahkan otomatis oleh sistem &bull; {{ now()->translatedFormat('d F Y, H:i') }} WIB
        &bull; phoenixdigital.id
    </div>
</body>

</html>
