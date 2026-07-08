<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $g->id_transaksi }}</title>
    @php
        $accent = '#4f46e5';
        $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');

        $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $periode = ($g->periode_bulan && $g->periode_tahun) ? (($namaBulan[(int)$g->periode_bulan] ?? '').' '.$g->periode_tahun) : '-';

        $lemburLabel = ((int) $g->jam_lembur > 0) ? 'Uang Lembur ('.(int) $g->jam_lembur.' jam)' : 'Uang Lembur';
        $offlineLabel = ((int) $g->jumlah_hadir_offline > 0) ? 'Uang Hadir Offline ('.(int) $g->jumlah_hadir_offline.' hari)' : 'Uang Hadir Offline';
        $onlineLabel = ((int) $g->jumlah_hadir_online > 0) ? 'Uang Hadir Online ('.(int) $g->jumlah_hadir_online.' hari)' : 'Uang Hadir Online';

        $pendapatan = array_filter([
            'Gaji Pokok' => (float) $g->gaji_pokok,
            'Bonus Kinerja' => (float) $g->bonus_kinerja,
            'Bonus Lainnya' => (float) $g->bonus_lainnya,
            'Bonus Penyelesaian Task' => (float) $g->bonus_penyelesaian_task,
            $lemburLabel => (float) $g->uang_lembur,
            $offlineLabel => (float) $g->uang_hadir_offline,
            $onlineLabel => (float) $g->uang_hadir_online,
            'Tunjangan Kesehatan' => (float) $g->tunjangan_kesehatan,
            'Tunjangan THR' => (float) $g->tunjangan_thr,
            'Tunjangan Ketenagakerjaan' => (float) $g->tunjangan_ketenagakerjaan,
            'Tunjangan Transport' => (float) $g->tunjangan_transport,
            'Tunjangan Makan' => (float) $g->tunjangan_makan,
            'Tunjangan Lainnya' => (float) $g->tunjangan_lainnya,
        ], fn ($v) => $v > 0);

        $potongan = array_filter([
            'Potongan Umum' => (float) $g->potongan,
            'BPJS Kesehatan' => (float) $g->potongan_bpjs_kesehatan,
            'BPJS Ketenagakerjaan' => (float) $g->potongan_bpjs_ketenagakerjaan,
            'PPh 21' => (float) $g->pph21,
            'Potongan Pinjaman' => (float) $g->potongan_pinjaman,
        ], fn ($v) => $v > 0);

        $totalPendapatan = array_sum($pendapatan);
        $totalPotongan = array_sum($potongan);
    @endphp
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 0; }
        body { color: #1f2937; font-size: 11px; line-height: 1.5; padding: 32px 36px; }

        .header { width: 100%; border-collapse: collapse; }
        .brand { font-size: 20px; font-weight: bold; color: {{ $accent }}; }
        .brand small { display: block; font-size: 8.5px; color: #6b7280; font-weight: normal; letter-spacing: 1.5px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 18px; color: #111827; letter-spacing: 2px; }
        .doc-title .badge { display: inline-block; margin-top: 4px; padding: 3px 12px; border-radius: 20px;
            background: #eef2ff; color: {{ $accent }}; font-size: 9.5px; font-weight: bold; }

        .divider { height: 3px; background: {{ $accent }}; border-radius: 3px; margin: 12px 0 16px; }

        /* Info karyawan */
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { padding: 3px 0; vertical-align: top; font-size: 10.5px; }
        .info .lbl { color: #6b7280; width: 24%; }
        .info .sep { width: 2%; }
        .info .val { font-weight: bold; color: #111827; }

        .section-title { font-size: 10px; font-weight: bold; color: #fff; background: {{ $accent }};
            text-transform: uppercase; letter-spacing: 0.5px; padding: 6px 10px; border-radius: 6px 6px 0 0; }

        table.box { width: 100%; border-collapse: collapse; border: 1px solid #eef2f7; border-top: none; margin-bottom: 14px; }
        table.box td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
        table.box td.num { text-align: right; white-space: nowrap; }
        table.box tr:last-child td { border-bottom: none; }
        table.box tfoot td { background: #f8fafc; font-weight: bold; border-top: 1px solid #e5e7eb; }
        .muted { color: #9ca3af; font-style: italic; }

        .takehome { margin-top: 6px; background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff;
            border-radius: 10px; padding: 14px 18px; }
        .takehome table { width: 100%; }
        .takehome .lbl { font-size: 11px; opacity: .9; }
        .takehome .amt { font-size: 20px; font-weight: bold; text-align: right; }

        .twocol { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin: 0 -10px; }
        .twocol > tbody > tr > td { width: 50%; vertical-align: top; }

        .status-pill { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .s-completed { background: #ecfdf5; color: #059669; }
        .s-pending { background: #fffbeb; color: #d97706; }

        .sign { width: 100%; margin-top: 34px; border-collapse: collapse; }
        .sign td { width: 50%; text-align: center; font-size: 10px; vertical-align: top; }
        .sign .line { margin-top: 48px; border-top: 1px solid #9ca3af; padding-top: 4px; display: inline-block; min-width: 150px; }

        .footer { margin-top: 22px; padding-top: 10px; border-top: 1px solid #e5e7eb;
            color: #9ca3af; font-size: 8.5px; text-align: center; }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <table style="border-collapse:collapse; border:none; margin:0;"><tr><td style="width:86px; vertical-align:middle; padding:0 12px 0 0; border:none;"><img src="{{ storage_path('app/public/img/archive/logo-icon.png') }}" style="width:72px; height:66px;" alt=""></td><td style="vertical-align:middle; border:none; padding:0;"><div class="brand">PT. Asthana Cipta Mandiri<small>SISTEM MANAJEMEN KEUANGAN</small></div></td></tr></table>
            </td>
            <td class="doc-title">
                <h1>SLIP GAJI</h1>
                <span class="badge">Periode {{ $periode }}</span>
            </td>
        </tr>
    </table>
    <div class="divider"></div>

    {{-- Info karyawan --}}
    <table class="info">
        <tr>
            <td class="lbl">Nama Karyawan</td><td class="sep">:</td><td class="val">{{ $g->karyawan->name ?? '-' }}</td>
            <td class="lbl">ID Transaksi</td><td class="sep">:</td><td class="val">{{ $g->id_transaksi }}</td>
        </tr>
        <tr>
            <td class="lbl">Bank</td><td class="sep">:</td><td class="val">{{ $g->bank ?: '-' }}</td>
            <td class="lbl">Tanggal Bayar</td><td class="sep">:</td><td class="val">{{ $g->tanggal_transaksi_formatted }}</td>
        </tr>
        <tr>
            <td class="lbl">No. Rekening</td><td class="sep">:</td><td class="val">{{ $g->no_rek ?: '-' }}</td>
            <td class="lbl">Status</td><td class="sep">:</td>
            <td class="val">
                <span class="status-pill {{ $g->status === 'completed' ? 's-completed' : 's-pending' }}">{{ ucfirst($g->status) }}</span>
            </td>
        </tr>
    </table>

    {{-- Pendapatan & Potongan berdampingan --}}
    <table class="twocol">
        <tr>
            <td>
                <div class="section-title">Pendapatan</div>
                <table class="box">
                    <tbody>
                        @forelse($pendapatan as $label => $nilai)
                        <tr><td>{{ $label }}</td><td class="num">{{ $rupiah($nilai) }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="muted">Tidak ada</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr><td>Total Pendapatan</td><td class="num">{{ $rupiah($totalPendapatan) }}</td></tr>
                    </tfoot>
                </table>
            </td>
            <td>
                <div class="section-title">Potongan</div>
                <table class="box">
                    <tbody>
                        @forelse($potongan as $label => $nilai)
                        <tr><td>{{ $label }}</td><td class="num">{{ $rupiah($nilai) }}</td></tr>
                        @empty
                        <tr><td colspan="2" class="muted">Tidak ada</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr><td>Total Potongan</td><td class="num">{{ $rupiah($totalPotongan) }}</td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    {{-- Gaji bersih --}}
    <div class="takehome">
        <table>
            <tr>
                <td class="lbl">GAJI BERSIH DITERIMA<br><span style="font-size:8.5px; opacity:.8;">(Total Pendapatan &minus; Total Potongan)</span></td>
                <td class="amt">{{ $rupiah($g->total) }}</td>
            </tr>
        </table>
    </div>

    @if($g->deskripsi)
    <div style="margin-top: 12px; font-size: 10px; color: #6b7280;">
        <strong>Catatan:</strong> {{ $g->deskripsi }}
    </div>
    @endif

    {{-- Tanda tangan --}}
    <table class="sign">
        <tr>
            <td>
                Diterima oleh,
                <div class="line">{{ $g->karyawan->name ?? 'Karyawan' }}</div>
            </td>
            <td>
                Hormat kami,
                <div class="line">HRD / Keuangan</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Slip gaji ini dibuat otomatis oleh sistem PT. Asthana Cipta Mandiri &middot; dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB.
    </div>
</body>

</html>
