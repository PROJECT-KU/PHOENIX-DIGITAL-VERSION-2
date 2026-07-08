<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice — Phoenix Digital</title>
    @php
        // Embed logo/cap/ttd sebagai base64 agar aman dirender DomPDF.
        $imgUri = function ($path) {
            $full = storage_path('app/public/img/archive/'.$path);
            if (! is_file($full)) {
                return null;
            }
            return 'data:image/png;base64,'.base64_encode(file_get_contents($full));
        };
        $logo = $imgUri('phoenix.png');
        $cap = $imgUri('cap.png');
        $ttd = $imgUri('ttd.png');
    @endphp
    <style>
        @page { margin: 130px 40px 120px 40px; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        /* ===== Header (fixed, tiap halaman) ===== */
        .doc-header {
            position: fixed;
            top: -105px; left: 0; right: 0;
            height: 105px;
            padding: 0;
        }
        .doc-header-bar {
            width: 100%;
            border-collapse: collapse;
        }
        .doc-header-bar td { vertical-align: middle; border: none; }
        .brand-logo { width: 132px; height: auto; }
        .brand-name { font-size: 20px; font-weight: bold; color: #0f172a; letter-spacing: .5px; }
        .brand-sub { font-size: 9px; color: #6b7280; letter-spacing: 1.5px; text-transform: uppercase; }
        .invoice-word {
            text-align: right;
            font-size: 34px;
            font-weight: bold;
            color: #ea8a00;
            letter-spacing: 3px;
        }
        .header-rule { height: 3px; background: #ea8a00; margin-top: 8px; }
        .header-rule-soft { height: 1px; background: #f6d9ac; margin-top: 2px; }

        /* ===== Footer (fixed) ===== */
        .doc-footer {
            position: fixed;
            bottom: -95px; left: 0; right: 0;
            height: 90px;
        }
        .footer-band {
            background: #0f172a;
            color: #fff;
            padding: 12px 40px;
            font-size: 10px;
        }
        .footer-band .accent { color: #f5a524; font-weight: bold; }
        .footer-strip { height: 5px; background: #ea8a00; }

        /* ===== Meta (nomor & tanggal) ===== */
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .meta-table td { border: none; padding: 0; vertical-align: top; }
        .bill-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #ea8a00; font-weight: bold; margin-bottom: 3px; }
        .bill-to { font-size: 11px; line-height: 1.55; color: #374151; }
        .bill-to strong { color: #111827; }
        .meta-box { border-collapse: collapse; float: right; }
        .meta-box td {
            border: 1px solid #e5e7eb;
            padding: 6px 12px;
            font-size: 10px;
        }
        .meta-box .mk { background: #faf3e6; color: #92660b; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
        .meta-box .mv { color: #1f2937; font-weight: bold; }

        /* ===== Tabel item ===== */
        .items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .items thead th {
            background: #ea8a00;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .4px;
            padding: 9px 8px;
            text-align: center;
            border: none;
        }
        .items tbody td {
            padding: 9px 8px;
            font-size: 10.5px;
            border-bottom: 1px solid #eef0f4;
            vertical-align: middle;
        }
        .items tbody tr.alt td { background: #fbf7f0; }
        .items .camp-name { font-weight: bold; color: #111827; }
        .items .camp-batch { color: #9ca3af; font-size: 9px; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* ===== Total ===== */
        .total-wrap { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .total-wrap > tbody > tr > td { border: none; padding: 0; vertical-align: top; }
        .total-box { border-collapse: collapse; width: 100%; }
        .total-box td { padding: 7px 12px; font-size: 11px; }
        .total-box .tl { color: #6b7280; }
        .total-box .tv { text-align: right; font-weight: bold; color: #111827; }
        .total-box .grand-l {
            background: #0f172a; color: #fff; font-weight: bold; font-size: 12px;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .total-box .grand-v {
            background: #ea8a00; color: #fff; font-weight: bold; font-size: 13px; text-align: right;
        }

        /* ===== Pembayaran & TTD ===== */
        .pay-table { width: 100%; border-collapse: collapse; margin-top: 34px; }
        .pay-table td { border: none; vertical-align: top; }
        .pay-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #ea8a00; font-weight: bold; margin-bottom: 6px; }
        .pay-body { font-size: 11px; line-height: 1.6; color: #374151; }
        .pay-body strong { color: #111827; }
        .sign-area { text-align: center; }
        .sign-stack { position: relative; height: 108px; width: 210px; margin: 4px auto 0; }
        .sign-cap { position: absolute; top: 0; left: 26px; width: 118px; opacity: .9; }
        .sign-ttd { position: absolute; top: 14px; left: 55px; width: 120px; }
        .sign-name { font-weight: bold; color: #111827; font-size: 11px; }
        .sign-role { color: #6b7280; font-size: 9.5px; }
        .thanks { margin-top: 26px; text-align: center; color: #9ca3af; font-size: 10px; font-style: italic; }
    </style>
</head>

<body>
    {{-- ===== Header fixed ===== --}}
    <div class="doc-header">
        <table class="doc-header-bar">
            <tr>
                <td style="width: 55%;">
                    @if($logo)
                        <img src="{{ $logo }}" class="brand-logo">
                    @else
                        <div class="brand-name">Phoenix Digital</div>
                        <div class="brand-sub">Digital Solution Partner</div>
                    @endif
                </td>
                <td style="width: 45%;">
                    <div class="invoice-word">INVOICE</div>
                </td>
            </tr>
        </table>
        <div class="header-rule"></div>
        <div class="header-rule-soft"></div>
    </div>

    {{-- ===== Footer fixed ===== --}}
    <div class="doc-footer">
        <div class="footer-strip"></div>
        <div class="footer-band">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="border:none; color:#fff;">
                        <span class="accent">PHOENIX DIGITAL</span> &nbsp;•&nbsp; Digital Solution Partner
                    </td>
                    <td style="border:none; text-align:right; color:#cbd5e1;">
                        Contact Center: +62 895-0596-7995
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ===== Konten ===== --}}
    <table class="meta-table">
        <tr>
            <td style="width: 58%;">
                <div class="bill-label">Ditagihkan Kepada</div>
                <div class="bill-to">
                    <strong>Pimpinan Rumah Scopus</strong><br>
                    Bangunsari, Jl. Bangunsari, Bangun Kerto,<br>
                    Turi, Sleman Regency, DIY 55551<br>
                    Telp: 0812-2688-3280
                </div>
            </td>
            <td style="width: 42%;">
                <table class="meta-box">
                    <tr>
                        <td class="mk">No. Invoice</td>
                        <td class="mv">{{ $invoiceNumber }}</td>
                    </tr>
                    <tr>
                        <td class="mk">Tanggal</td>
                        <td class="mv">{{ $date }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th style="width:34%; text-align:left;">Deskripsi</th>
                <th style="width:18%;">Periode</th>
                <th style="width:12%;">Peserta</th>
                <th style="width:15%;">Harga Satuan</th>
                <th style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr class="{{ $loop->even ? 'alt' : '' }}">
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>
                    <span class="camp-name">{{ $item->nama_camp }}</span><br>
                    <span class="camp-batch">Batch #{{ $item->batch_camp }}</span>
                </td>
                <td class="text-center">
                    @if(!empty($item->periode_mulai) && !empty($item->periode_akhir))
                        {{ \Carbon\Carbon::parse($item->periode_mulai)->format('d M Y') }}<br>
                        <span style="color:#9ca3af;">s/d</span> {{ \Carbon\Carbon::parse($item->periode_akhir)->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-center">{{ $item->total_peserta }} orang</td>
                <td class="text-right">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right" style="font-weight:bold;">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-wrap">
        <tr>
            <td style="width: 60%;">&nbsp;</td>
            <td style="width: 40%;">
                <table class="total-box">
                    <tr>
                        <td class="tl">Subtotal</td>
                        <td class="tv">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="grand-l">Total Biaya</td>
                        <td class="grand-v">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="pay-table">
        <tr>
            <td style="width: 58%;">
                <div class="pay-title">Instruksi Pembayaran</div>
                <div class="pay-body">
                    Pembayaran dapat dilakukan melalui transfer bank:<br>
                    <strong>Bank BRI</strong><br>
                    No. Rekening: <strong>3584-0103-4310-539</strong><br>
                    a.n. Biwi Faiza Tu Zulaikhah YS
                </div>
            </td>
            <td style="width: 42%;" class="sign-area">
                <div style="font-size:11px; color:#374151;">Hormat Kami,</div>
                <div class="sign-stack">
                    @if($cap)<img src="{{ $cap }}" class="sign-cap">@endif
                    @if($ttd)<img src="{{ $ttd }}" class="sign-ttd">@endif
                </div>
                <div class="sign-name">Biwi Fa'iza Tu Zulaikhah</div>
                <div class="sign-role">Finance Dept. — Phoenix Digital</div>
            </td>
        </tr>
    </table>

    <div class="thanks">Terima kasih atas kepercayaan Anda kepada Phoenix Digital.</div>
</body>

</html>
