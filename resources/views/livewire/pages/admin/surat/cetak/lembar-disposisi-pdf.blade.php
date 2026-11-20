{{--
    Lembar disposisi — untuk dicetak dan DITEMPEL di surat fisiknya.

    Bentuknya meniru lembar disposisi kertas yang sudah dikenal: identitas
    surat di atas, lalu kotak-kotak perintah dengan ruang tanda tangan. Itu
    bukan nostalgia — surat fisiknya memang berpindah meja, dan yang berpindah
    bersamanya harus bisa dibaca tanpa membuka layar.
--}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lembar Disposisi {{ $surat->nomor_agenda }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 15px; margin: 0; text-align: center; letter-spacing: .5px; }
        .sub { text-align: center; font-size: 9px; color: #6b7280; margin: 2px 0 10px; }
        table { width: 100%; border-collapse: collapse; }
        .id td { border: 0.6px solid #9ca3af; padding: 4px 6px; }
        .id td.k { background: #f3f4f6; width: 24%; font-size: 9px; }
        .rahasia { border: 1.2px solid #b91c1c; color: #b91c1c; padding: 3px 7px; font-weight: bold; font-size: 10px; display: inline-block; margin-bottom: 6px; }
        .judul { margin: 14px 0 5px; font-size: 11px; font-weight: bold; }
        .dis th, .dis td { border: 0.6px solid #9ca3af; padding: 5px 6px; vertical-align: top; }
        .dis th { background: #f3f4f6; font-size: 9px; text-align: left; }
        .dis .ruang { height: 34px; }
        .kosong { border: 0.6px dashed #9ca3af; padding: 14px; text-align: center; color: #6b7280; }
        .catatan { margin-top: 14px; border: 0.6px solid #9ca3af; height: 90px; }
        .catatan .k { background: #f3f4f6; padding: 4px 6px; font-size: 9px; border-bottom: 0.6px solid #9ca3af; }
    </style>
</head>
<body>
    <h1>LEMBAR DISPOSISI</h1>
    <p class="sub">Dicetak {{ now()->locale('id')->translatedFormat('d F Y, H.i') }}</p>

    @if ($surat->sifat === 'rahasia')
        <span class="rahasia">RAHASIA</span>
    @endif

    <table class="id">
        <tr>
            <td class="k">Nomor Agenda</td><td>{{ $surat->nomor_agenda }}</td>
            <td class="k">Sifat</td><td>{{ $surat->sifat_label }}</td>
        </tr>
        <tr>
            <td class="k">Nomor Surat</td><td>{{ $surat->nomor_surat ?: '-' }}</td>
            <td class="k">Tanggal Surat</td><td>{{ $surat->tanggal_surat_teks }}</td>
        </tr>
        <tr>
            <td class="k">{{ $surat->pihak_label }}</td><td>{{ $surat->pihak }}</td>
            <td class="k">Diterima/Dikirim</td><td>{{ $surat->tanggal_catat_teks }}</td>
        </tr>
        <tr>
            <td class="k">Perihal</td><td colspan="3">{{ $surat->perihal }}</td>
        </tr>
        <tr>
            <td class="k">Klasifikasi</td><td>{{ $surat->klasifikasi?->label ?: 'Belum diklasifikasi' }}</td>
            <td class="k">Tempat Berkas</td><td>{{ $surat->lokasi_fisik ?: '-' }}</td>
        </tr>
        @if ($surat->tembusan)
            <tr><td class="k">Tembusan</td><td colspan="3">{{ $surat->tembusan }}</td></tr>
        @endif
    </table>

    <p class="judul">Diteruskan kepada / Instruksi</p>

    @if ($disposisi->isEmpty())
        {{-- Lembar kosong tetap berguna: ia dicetak lebih dulu, lalu
             perintahnya ditulis tangan di meja pimpinan. --}}
        <table class="dis">
            <thead>
                <tr><th style="width:26%">Kepada</th><th>Instruksi</th><th style="width:16%">Tenggat</th><th style="width:18%">Paraf</th></tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 4; $i++)
                    <tr><td class="ruang"></td><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>
    @else
        <table class="dis">
            <thead>
                <tr><th style="width:26%">Kepada</th><th>Instruksi</th><th style="width:16%">Tenggat</th><th style="width:18%">Paraf</th></tr>
            </thead>
            <tbody>
                @foreach ($disposisi as $d)
                    <tr>
                        <td>
                            {{ $d->kepada_teks }}<br>
                            <span style="font-size:8px;color:#6b7280">{{ \App\Models\Surat\SuratDisposisi::STATUS[$d->status] ?? $d->status }}</span>
                        </td>
                        <td>{{ $d->instruksi }}</td>
                        <td>{{ $d->batas?->locale('id')->translatedFormat('d M Y') ?: 'Tanpa tenggat' }}</td>
                        <td class="ruang"></td>
                    </tr>
                @endforeach
                @for ($i = $disposisi->count(); $i < 4; $i++)
                    <tr><td class="ruang"></td><td></td><td></td><td></td></tr>
                @endfor
            </tbody>
        </table>
    @endif

    <div class="catatan">
        <div class="k">Catatan</div>
    </div>
</body>
</html>
