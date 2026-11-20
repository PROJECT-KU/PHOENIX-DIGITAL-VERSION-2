{{--
    Buku agenda sebagai PDF — untuk dicetak, ditandatangani, dan disimpan.

    Kolomnya SENGAJA lebih sedikit daripada versi Excel. Yang dicetak dibaca
    di atas kertas A4 melintang, dan kolom ke-17 pada lebar itu menyusut jadi
    satu huruf per baris. Yang lengkap ada di Excel; yang di sini adalah yang
    dipakai orang saat memeriksa arsip dengan mata.
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Agenda Surat</title>
    <style>
        @page { margin: 14mm 10mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #1f2937; }
        h1 { font-size: 14px; margin: 0 0 2px; }
        .ket { font-size: 8px; color: #6b7280; margin: 0 0 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 0.5px solid #cbd5e1; padding: 3px 4px; vertical-align: top; }
        th { background: #f1f5f9; font-size: 8px; text-align: left; }
        td.nom { white-space: nowrap; }
        .kosong { text-align: center; padding: 18px; color: #6b7280; }
        .ttd { margin-top: 18px; width: 100%; }
        .ttd td { border: 0; padding: 0; font-size: 9px; }
        .ttd .ruang { height: 46px; }
    </style>
</head>
<body>
    <h1>BUKU AGENDA SURAT</h1>
    <p class="ket">{{ $ringkasan }}</p>
    <p class="ket">Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H.i') }} &middot; {{ $surat->count() }} surat</p>

    @if ($surat->isEmpty())
        <p class="kosong">Tidak ada surat yang cocok dengan saringan ini.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:3%">No</th>
                    <th style="width:11%">Nomor Agenda</th>
                    <th style="width:12%">Nomor Surat</th>
                    <th style="width:9%">Tgl Surat</th>
                    <th style="width:26%">Perihal</th>
                    <th style="width:16%">Dari / Kepada</th>
                    <th style="width:11%">Klasifikasi</th>
                    <th style="width:6%">Sifat</th>
                    <th style="width:6%">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($surat as $i => $s)
                    <tr>
                        <td class="nom">{{ $i + 1 }}</td>
                        <td class="nom">{{ $s->nomor_agenda }}</td>
                        <td>{{ $s->nomor_surat ?: '-' }}</td>
                        <td class="nom">{{ $s->tanggal_surat?->format('d/m/Y') ?: '-' }}</td>
                        <td>{{ $s->perihal }}</td>
                        <td>{{ $s->pihak }}</td>
                        <td>{{ $s->klasifikasi?->kode ?: '-' }} {{ $s->klasifikasi?->nama }}</td>
                        <td>{{ $s->sifat_label }}</td>
                        <td>{{ $s->status_label }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Ruang tanda tangan: buku agenda yang dicetak untuk pemeriksaan
             selalu perlu siapa yang menyerahkannya. --}}
        <table class="ttd">
            <tr>
                <td style="width:72%"></td>
                <td>Dicetak oleh,</td>
            </tr>
            <tr>
                <td></td>
                <td class="ruang"></td>
            </tr>
            <tr>
                <td></td>
                <td>( {{ $pencetak ?: '........................' }} )</td>
            </tr>
        </table>
    @endif
</body>
</html>
