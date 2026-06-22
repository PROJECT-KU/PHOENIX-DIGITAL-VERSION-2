<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    @php
        $accent = '#4f46e5';
        $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    @endphp
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 0; }
        body { color: #1f2937; font-size: 10.5px; line-height: 1.45; padding: 28px 32px 16px; }

        /* ===== Kop ===== */
        .header { width: 100%; border-collapse: collapse; }
        .brand { font-size: 21px; font-weight: bold; color: {{ $accent }}; }
        .brand small { display: block; font-size: 8.5px; color: #6b7280; font-weight: normal; letter-spacing: 1.5px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 19px; color: #111827; letter-spacing: 2px; }
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
        .j-pinjam { background: #eef2ff; color: {{ $accent }}; }
        .j-kembali { background: #ecfdf5; color: #059669; }
        .s-lunas { background: #ecfdf5; color: #059669; }
        .s-berjalan { background: #eff6ff; color: #2563eb; }
        .s-pending { background: #fffbeb; color: #d97706; }
        .t-ex { color: #e11d48; }
        .t-in { color: #059669; }

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
                <div class="brand">Phoenix Digital<small>SISTEM MANAJEMEN KEUANGAN</small></div>
            </td>
            <td class="doc-title">
                <h1>{{ $judul }}</h1>
                <span class="badge">{{ $konteks }}</span>
                <span class="printed">Dicetak: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB</span>
            </td>
        </tr>
    </table>
    <div class="divider"></div>

    {{-- ===== Ringkasan ===== --}}
    <div class="section-title">Ringkasan</div>
    <table class="summary">
        <tr>
            <td>
                <div class="scard" style="background: #fef2f2; border-color: #fee2e2;">
                    <div class="label">Total Peminjaman</div>
                    <div class="value t-ex">{{ $rupiah($summary['peminjaman']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #ecfdf5; border-color: #d1fae5;">
                    <div class="label">Total Pengembalian</div>
                    <div class="value t-in">{{ $rupiah($summary['pengembalian']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #f5f3ff; border-color: #e0e7ff;">
                    <div class="label">Selisih (Sisa)</div>
                    <div class="value" style="color: {{ $summary['selisih'] > 0 ? '#e11d48' : '#059669' }};">{{ $rupiah($summary['selisih']) }}</div>
                </div>
            </td>
            <td>
                <div class="scard" style="background: #f8fafc; border-color: #e5e7eb;">
                    <div class="label">Jumlah Transaksi</div>
                    <div class="value" style="color: #111827;">{{ number_format($summary['count'], 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== Rincian transaksi ===== --}}
    <div class="section-title">Rincian Transaksi ({{ count($rows) }})</div>
    <table class="tx">
        <thead>
            <tr>
                <th class="center first" style="width: 4%;">No</th>
                <th style="width: 13%;">ID Transaksi</th>
                <th style="width: 11%;">Jenis</th>
                <th style="width: 11%;">Tanggal</th>
                <th style="width: 14%;">Nama</th>
                <th style="width: 20%;">Deskripsi</th>
                <th class="center" style="width: 9%;">Status</th>
                <th style="width: 11%;">Penginput</th>
                <th class="num lastcol" style="width: 13%;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $r)
            @php
                $isPinjam = $r['jenis'] === 'peminjaman';
                $statusClass = $r['status'] === 'lunas' ? 's-lunas' : ($r['status'] === 'berjalan' ? 's-berjalan' : 's-pending');
            @endphp
            <tr class="{{ $i % 2 === 1 ? 'alt' : '' }}">
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $r['id_transaksi'] }}</td>
                <td><span class="pill {{ $isPinjam ? 'j-pinjam' : 'j-kembali' }}">{{ $r['jenis_label'] }}</span></td>
                <td>{{ $r['tanggal'] }}</td>
                <td>{{ $r['nama'] }}</td>
                <td>{{ $r['deskripsi'] ?: '-' }}</td>
                <td class="center"><span class="pill {{ $statusClass }}">{{ ucfirst($r['status']) }}</span></td>
                <td>{{ $r['penginput'] }}</td>
                <td class="num {{ $isPinjam ? 't-ex' : 't-in' }}">{{ ($isPinjam ? '- ' : '+ ').$rupiah($r['nominal']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="empty">Tidak ada data untuk kriteria ini.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
        <tfoot>
            <tr>
                <td colspan="8" class="num">Selisih (Peminjaman - Pengembalian)</td>
                <td class="num" style="color: {{ $summary['selisih'] > 0 ? '#e11d48' : '#059669' }};">{{ $rupiah($summary['selisih']) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Laporan ini dibuat otomatis oleh sistem Phoenix Digital &middot; {{ $konteks }}.
    </div>
</body>

</html>
