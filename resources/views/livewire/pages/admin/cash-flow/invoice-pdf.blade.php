<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice {{ $cashFlow->id }}</title>
    @php
        $isIncome = $cashFlow->type === 'income';
        $accent = $isIncome ? '#059669' : '#e11d48';
        $accentSoft = $isIncome ? '#ecfdf5' : '#fff1f2';
    @endphp
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 0; }
        body { color: #1f2937; font-size: 11px; line-height: 1.45; padding: 32px 34px 18px; }

        .header { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .brand { font-size: 20px; font-weight: bold; color: #4f46e5; }
        .brand small { display: block; font-size: 9px; color: #6b7280; font-weight: normal; letter-spacing: 1px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 22px; color: #111827; letter-spacing: 2px; }
        .doc-title .badge { display: inline-block; margin-top: 4px; padding: 3px 10px; border-radius: 20px;
            background: {{ $accentSoft }}; color: {{ $accent }}; font-size: 9px; font-weight: bold; text-transform: uppercase; }

        .divider { height: 3px; background: {{ $accent }}; border-radius: 3px; margin-bottom: 12px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta td { vertical-align: top; padding: 0; width: 50%; }
        .meta .label { color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta .value { font-weight: bold; color: #111827; font-size: 12px; }

        .amount-box { background: {{ $accentSoft }}; border: 1px solid {{ $accent }}; border-radius: 10px;
            padding: 10px 16px; margin-bottom: 14px; }
        .amount-box .label { color: {{ $accent }}; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-box .amount { font-size: 23px; font-weight: bold; color: {{ $accent }}; }

        .section-title { font-size: 11px; font-weight: bold; color: #111827; text-transform: uppercase;
            letter-spacing: 0.5px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; margin-bottom: 8px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.data td { padding: 4px 4px; border-bottom: 1px solid #f1f5f9; }
        table.data td.k { color: #6b7280; width: 38%; }
        table.data td.v { color: #111827; font-weight: bold; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.items th { background: #f8fafc; color: #475569; text-transform: uppercase; font-size: 9px;
            letter-spacing: 0.5px; padding: 6px; text-align: left; border-bottom: 2px solid #e5e7eb; }
        table.items td { padding: 6px; border-bottom: 1px solid #f1f5f9; }
        table.items .num { text-align: right; }
        table.items .center { text-align: center; }

        .totals { width: 280px; float: right; border-collapse: collapse; }
        .totals td { padding: 4px 8px; }
        .totals td.k { color: #6b7280; text-align: right; }
        .totals td.v { text-align: right; font-weight: bold; color: #111827; width: 130px; }
        .totals tr.grand td { border-top: 2px solid {{ $accent }}; color: {{ $accent }}; font-size: 13px; padding-top: 6px; }

        .footer { clear: both; margin-top: 22px; padding-top: 10px; border-top: 1px solid #e5e7eb;
            color: #9ca3af; font-size: 9px; text-align: center; }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <table style="border-collapse:collapse; border:none; margin:0;"><tr><td style="width:86px; vertical-align:middle; padding:0 12px 0 0; border:none;"><img src="{{ storage_path('app/public/img/archive/logo-icon.png') }}" style="width:72px; height:66px;" alt=""></td><td style="vertical-align:middle; border:none; padding:0;"><div class="brand">PT. Asthana Cipta Mandiri<small>SISTEM MANAJEMEN KEUANGAN</small></div></td></tr></table>
            </td>
            <td class="doc-title">
                <h1>INVOICE</h1>
                <span class="badge">{{ $isIncome ? 'Pemasukan' : 'Pengeluaran' }}</span>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="meta">
        <tr>
            <td>
                <div class="label">No. Referensi</div>
                <div class="value">#{{ strtoupper(substr($cashFlow->id, 0, 8)) }}</div>
            </td>
            <td style="text-align: right;">
                <div class="label">Tanggal Transaksi</div>
                <div class="value">{{ $cashFlow->transaction_date->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 10px;">
                <div class="label">Jenis Sumber</div>
                <div class="value">{{ $detail['jenis'] }}</div>
            </td>
            <td style="padding-top: 10px; text-align: right;">
                <div class="label">Kategori</div>
                <div class="value">{{ ucfirst($cashFlow->category ?? '-') }}</div>
            </td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Nominal Transaksi</div>
        <div class="amount">{{ $isIncome ? '+' : '-' }} Rp {{ number_format($cashFlow->amount, 0, ',', '.') }}</div>
    </div>

    @if(!empty($detail['rows']))
    <div class="section-title">Informasi Sumber</div>
    <table class="data">
        @foreach($detail['rows'] as $label => $value)
        <tr>
            <td class="k">{{ $label }}</td>
            <td class="v">{{ $value ?: '-' }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if(!empty($detail['items']))
    <div class="section-title">Rincian Item</div>
    <table class="items">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Durasi</th>
                <th class="center">Qty</th>
                <th class="num">Harga</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail['items'] as $it)
            <tr>
                <td>{{ $it['nama'] }}</td>
                <td>{{ $it['durasi'] }}</td>
                <td class="center">{{ $it['qty'] }}</td>
                <td class="num">{{ $it['harga'] }}</td>
                <td class="num">{{ $it['subtotal'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(!empty($detail['itemsNote']))
    <p style="color: #6b7280; font-size: 9px; margin-bottom: 14px;">{{ $detail['itemsNote'] }}</p>
    @endif
    @endif

    @if(!empty($detail['totals']))
    <table class="totals">
        @php $totalKeys = array_keys($detail['totals']); $lastKey = end($totalKeys); @endphp
        @foreach($detail['totals'] as $label => $value)
        <tr class="{{ $label === $lastKey ? 'grand' : '' }}">
            <td class="k">{{ $label }}</td>
            <td class="v">{{ $value }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    @if($cashFlow->description)
    <div style="clear: both;"></div>
    <div class="section-title" style="margin-top: 10px;">Catatan</div>
    <p style="color: #374151;">{{ $cashFlow->description }}</p>
    @endif

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem PT. Asthana Cipta Mandiri pada {{ now()->translatedFormat('d F Y, H:i') }} WIB.<br>
        Invoice ini sah tanpa tanda tangan basah.
    </div>
</body>

</html>
