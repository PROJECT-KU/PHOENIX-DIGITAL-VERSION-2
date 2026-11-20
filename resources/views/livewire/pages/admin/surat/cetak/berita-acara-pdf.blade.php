{{--
    Berita acara pemusnahan arsip — ditandatangani dan disimpan permanen.

    Berbeda dari berkas cetakan lain di arsip ini, lembar inilah yang justru
    TIDAK BOLEH ikut dimusnahkan: ia satu-satunya yang menjelaskan ke mana
    perginya surat-surat yang disebutkannya.
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara {{ $ba->nomor }}</title>
    <style>
        @page { margin: 18mm 16mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #111827; line-height: 1.5; }
        h1 { font-size: 14px; margin: 0; text-align: center; letter-spacing: .5px; }
        .nomor { text-align: center; font-size: 10px; margin: 2px 0 14px; }
        p { margin: 0 0 7px; text-align: justify; }
        table.isi { width: 100%; border-collapse: collapse; margin: 10px 0 14px; }
        table.isi th, table.isi td { border: 0.6px solid #9ca3af; padding: 4px 5px; vertical-align: top; font-size: 9px; }
        table.isi th { background: #f3f4f6; text-align: left; }
        .nom { white-space: nowrap; }
        .ttd { width: 100%; margin-top: 22px; }
        .ttd td { border: 0; font-size: 10px; vertical-align: top; width: 50%; }
        .ttd .ruang { height: 54px; }
        .kaki { margin-top: 16px; font-size: 9px; color: #6b7280; }
    </style>
</head>
<body>
    <h1>BERITA ACARA PEMUSNAHAN ARSIP</h1>
    <p class="nomor">Nomor: {{ $ba->nomor }}</p>

    <p>
        Pada hari ini, {{ $ba->tanggal?->locale('id')->translatedFormat('l, d F Y') }}, telah dilaksanakan
        pemusnahan berkas arsip surat yang masa simpannya telah berakhir, sebanyak
        <b>{{ $surat->count() }} ({{ $terbilang }}) berkas</b> sebagaimana terinci dalam daftar di bawah ini.
    </p>

    @if ($ba->catatan)
        <p>{{ $ba->catatan }}</p>
    @endif

    <table class="isi">
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th style="width:14%">Nomor Agenda</th>
                <th style="width:12%">Tgl Surat</th>
                <th>Perihal</th>
                <th style="width:17%">Klasifikasi</th>
                <th style="width:13%">Masa Simpan Habis</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($surat as $i => $s)
                <tr>
                    <td class="nom">{{ $i + 1 }}</td>
                    <td class="nom">{{ $s->nomor_agenda }}</td>
                    <td class="nom">{{ $s->tanggal_surat?->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ $s->perihal }}</td>
                    <td>{{ $s->klasifikasi?->kode ?: '-' }} {{ $s->klasifikasi?->nama }}</td>
                    <td class="nom">{{ $s->musnah_pada?->format('d/m/Y') ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        Pemusnahan dilakukan terhadap <b>berkas fisiknya</b>. Catatan surat pada buku agenda tetap
        dipertahankan dan menunjuk pada berita acara ini.
    </p>

    <table class="ttd">
        <tr>
            <td>Penanggung jawab,</td>
            <td>Dibuat oleh,</td>
        </tr>
        <tr>
            <td class="ruang"></td>
            <td class="ruang"></td>
        </tr>
        <tr>
            <td>( {{ $ba->penanggung_jawab ?: '........................' }} )</td>
            <td>( {{ $ba->pembuat?->name ?: '........................' }} )</td>
        </tr>
    </table>

    <p class="kaki">Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H.i') }}.</p>
</body>
</html>
