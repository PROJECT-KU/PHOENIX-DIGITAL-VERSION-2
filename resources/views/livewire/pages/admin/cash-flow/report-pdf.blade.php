<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Cashflow {{ $periode }}</title>
    @php
        $accent = '#4f46e5';
        $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    @endphp
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 0; }
        body { color: #1f2937; font-size: 11px; line-height: 1.45; padding: 32px 34px 18px; }

        /* ===== Kop ===== */
        .header { width: 100%; border-collapse: collapse; }
        .brand { font-size: 21px; font-weight: bold; color: {{ $accent }}; }
        .brand small { display: block; font-size: 8.5px; color: #6b7280; font-weight: normal; letter-spacing: 1.5px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 20px; color: #111827; letter-spacing: 2px; }
        .doc-title .badge { display: inline-block; margin-top: 5px; padding: 3px 12px; border-radius: 20px;
            background: #eef2ff; color: {{ $accent }}; font-size: 9px; font-weight: bold; }
        .doc-title .printed { display: block; margin-top: 5px; font-size: 8px; color: #9ca3af; }

        .divider { height: 3px; background: {{ $accent }}; border-radius: 3px; margin: 12px 0 16px; }

        .section-title { font-size: 11px; font-weight: bold; color: #111827; text-transform: uppercase;
            letter-spacing: 0.6px; padding-bottom: 5px; border-bottom: 2px solid #e5e7eb; margin-bottom: 10px; }

        /* ===== Kartu ringkasan ===== */
        .summary { width: 100%; border-collapse: separate; border-spacing: 7px 0; margin: 0 -7px 18px; }
        .summary td { width: 25%; vertical-align: top; }
        .scard { border: 1px solid #eef2f7; border-radius: 9px; padding: 11px 13px; }
        .scard .label { font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.4px; color: #6b7280; }
        .scard .value { font-size: 15px; font-weight: bold; margin-top: 5px; }

        /* ===== Box omset ===== */
        .omset-box { border: 1px solid #c7d2fe; border-radius: 10px; margin-bottom: 18px; }
        .omset-box table { width: 100%; border-collapse: collapse; }
        .omset-box td { vertical-align: middle; padding: 13px 16px; border-right: 1px solid #e0e7ff; }
        .omset-box td.last { border-right: none; }
        .omset-box .main { background: #f5f3ff; border-radius: 10px 0 0 10px; }
        .omset-box .label { color: #6b7280; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.4px; }
        .omset-box .big { font-size: 19px; font-weight: bold; color: {{ $accent }}; margin-top: 3px; }
        .omset-box .sub { font-size: 13px; font-weight: bold; color: #111827; margin-top: 3px; }
        .omset-box .pill { display: inline-block; margin-top: 4px; padding: 1px 8px; border-radius: 12px;
            background: #ede9fe; color: {{ $accent }}; font-size: 8px; font-weight: bold; }

        /* ===== Tabel transaksi ===== */
        table.tx { width: 100%; border-collapse: collapse; }
        table.tx th { background: {{ $accent }}; color: #fff; font-size: 8.5px; text-transform: uppercase;
            letter-spacing: 0.4px; padding: 8px 7px; text-align: left; }
        table.tx th.first { border-radius: 6px 0 0 0; }
        table.tx th.lastcol { border-radius: 0 6px 0 0; }
        table.tx td { padding: 7px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        table.tx tbody tr.alt td { background: #f9fafb; }
        table.tx .num { text-align: right; white-space: nowrap; }
        table.tx .center { text-align: center; }
        table.tx tfoot td { background: #f5f3ff; font-weight: bold; font-size: 12px;
            padding: 10px 7px; border-bottom: none; border-top: 2px solid {{ $accent }}; }

        .pill { padding: 2px 8px; border-radius: 20px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .p-in { background: #ecfdf5; color: #059669; }
        .p-ex { background: #fff1f2; color: #e11d48; }
        .t-in { color: #059669; }
        .t-ex { color: #e11d48; }

        .footer { margin-top: 18px; padding-top: 10px; border-top: 1px solid #e5e7eb;
            color: #9ca3af; font-size: 8.5px; text-align: center; }
        .empty { text-align: center; color: #9ca3af; padding: 22px; font-style: italic; }
    </style>
</head>

<body>
    {{-- ===== Kop ===== --}}
    <table class="header">
        <tr>
            <td>
                <table style="border-collapse:collapse; border:none; margin:0;"><tr><td style="width:86px; vertical-align:middle; padding:0 12px 0 0; border:none;"><img src="{{ storage_path('app/public/img/archive/logo-icon.png') }}" style="width:72px; height:66px;" alt=""></td><td style="vertical-align:middle; border:none; padding:0;"><div class="brand">PT. Asthana Cipta Mandiri<small>SISTEM MANAJEMEN KEUANGAN</small></div></td></tr></table>
            </td>
            <td class="doc-title">
                <h1>LAPORAN CASHFLOW</h1>
                <span class="badge">Periode: {{ $periode }}</span>
                <span class="printed">Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
            </td>
        </tr>
    </table>
    <div class="divider"></div>

    {{-- ===== Ringkasan keuangan ===== --}}
    <div class="section-title">Ringkasan Keuangan</div>
    <table class="summary">
        <tr>
            <td>
                <div class="scard" style="background: #ecfdf5; border-color: #d1fae5;">
                    <div class="label">Pemasukan</div>
                    <div class="value t-in">{{ $rupiah($summary['income']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #fff1f2; border-color: #ffe4e6;">
                    <div class="label">Pengeluaran</div>
                    <div class="value t-ex">{{ $rupiah($summary['expense']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #eef2ff; border-color: #e0e7ff;">
                    <div class="label">Net Cashflow</div>
                    <div class="value" style="color: {{ $summary['net'] < 0 ? '#e11d48' : $accent }};">{{ $rupiah($summary['net']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #eff6ff; border-color: #dbeafe;">
                    <div class="label">Total Kode Unik</div>
                    <div class="value" style="color: #2563eb;">{{ $rupiah($totalKodeUnik) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== Omset bersih penjualan ===== --}}
    <div class="section-title">Omset Bersih Penjualan</div>
    <div class="omset-box">
        <table>
            <tr>
                <td class="main" style="width: 40%;">
                    <div class="label">Omset Bersih</div>
                    <div class="big">{{ $rupiah($omset['bersih']) }}</div>
                    <span class="pill">Margin {{ $omset['margin'] }}%</span>
                </td>
                <td style="width: 30%;">
                    <div class="label">Total Penjualan</div>
                    <div class="sub t-in">{{ $rupiah($omset['penjualan']) }}</div>
                </td>
                <td class="last" style="width: 30%;">
                    <div class="label">Total Modal (Harga Awal)</div>
                    <div class="sub t-ex">{{ $rupiah($omset['modal']) }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== Rincian transaksi ===== --}}
    <div class="section-title">Rincian Transaksi ({{ count($rows) }})</div>
    <table class="tx">
        <thead>
            <tr>
                <th class="center first" style="width: 5%;">No</th>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 14%;">Kategori</th>
                <th class="center" style="width: 10%;">Tipe</th>
                <th style="width: 23%;">Deskripsi</th>
                <th style="width: 19%;">Sumber</th>
                <th class="num lastcol" style="width: 18%;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
            @php $in = $r['tipe'] === 'income'; @endphp
            <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $r['tanggal'] }}</td>
                <td>{{ $r['kategori'] }}</td>
                <td class="center"><span class="pill {{ $in ? 'p-in' : 'p-ex' }}">{{ $in ? 'Masuk' : 'Keluar' }}</span></td>
                <td>{{ $r['deskripsi'] }}</td>
                <td>{{ $r['sumber'] }}</td>
                <td class="num {{ $in ? 't-in' : 't-ex' }}">{{ ($in ? '+ ' : '- ').$rupiah($r['amount']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
        <tfoot>
            <tr>
                <td colspan="6" class="num">Net Cashflow Periode</td>
                <td class="num" style="color: {{ $summary['net'] < 0 ? '#e11d48' : '#059669' }};">{{ $rupiah($summary['net']) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Laporan ini dibuat otomatis oleh sistem PT. Asthana Cipta Mandiri &middot; Periode {{ $periode }}.
    </div>
</body>

</html>
