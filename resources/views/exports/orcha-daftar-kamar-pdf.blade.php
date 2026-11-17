{{-- Daftar kamar, untuk diserahkan ke resepsionis hotel.

     Jenis kelamin ikut tercetak — itulah yang dicocokkan resepsionis saat
     menyerahkan kunci, dan daftar tanpa keterangan itu membuatnya menebak dari
     nama. --}}
@php
    $perKamar = collect($peserta)
        ->filter(fn ($s) => filled($s['kamar'] ?? null))
        ->groupBy(fn ($s) => trim((string) $s['kamar']));

    $belum = collect($peserta)->filter(fn ($s) => blank($s['kamar'] ?? null))->values();

    $pemesan = collect($peserta)->pluck('pemesan')->filter()->unique()->values();
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Kamar</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #16202b; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .ket { color: #5b6876; font-size: 10px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d8e2ea; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #fdeef5; font-size: 9px; }
        .nomor { font-weight: bold; font-size: 11px; }
        .isi { font-size: 10px; line-height: 1.5; }
        .campur { background: #fff7e6; }
        .kosong { color: #9aa6b2; font-style: italic; }
        .awas { background: #fff7e6; border: 1px solid #f3d49b; padding: 6px 8px; margin-top: 10px; }
        .kaki { margin-top: 14px; font-size: 9px; color: #5b6876; }
    </style>
</head>
<body>
    <h1>Daftar Kamar</h1>
    <p class="ket">
        {{ $keberangkatan['paket']['nama'] ?? 'Paket' }} &middot;
        Rombongan {{ $keberangkatan['rentang_tanggal'] ?? '—' }} &middot;
        {{ collect($peserta)->count() }} peserta &middot;
        dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
    </p>

    <table>
        <tr>
            <th style="width:70px">Kamar</th>
            <th style="width:40px">Isi</th>
            <th>Penghuni</th>
        </tr>

        @foreach ($kamar as $satu)
            @php
                $nama = (string) ($satu['nama'] ?? '');
                $isi = $perKamar[$nama] ?? collect();
                $kelamin = $isi->pluck('jenis_kelamin')->filter()->unique();
            @endphp
            <tr class="{{ $kelamin->count() > 1 ? 'campur' : '' }}">
                <td class="nomor">{{ $nama }}</td>
                <td>{{ $isi->count() }}/{{ (int) ($satu['kapasitas'] ?? 0) }}</td>
                <td class="isi">
                    @if ($isi->isEmpty())
                        <span class="kosong">kosong</span>
                    @else
                        @foreach ($isi as $orang)
                            {{ $loop->iteration }}. {{ $orang['nama'] }}
                            ({{ mb_substr((string) ($orang['jenis_kelamin'] ?? '?'), 0, 1) ?: '?' }})<br>
                        @endforeach

                        {{-- Kamar tercampur ditandai di lembarnya juga.

                             Resepsionis yang menyerahkan kunci tidak tahu mana
                             yang disengaja (pasangan suami-istri) dan mana yang
                             salah ketik — yang ia butuhkan cuma tanda untuk
                             bertanya. --}}
                        @if ($kelamin->count() > 1)
                            <em>— satu kamar laki-laki &amp; perempuan, mohon dikonfirmasi</em>
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    @if ($belum->isNotEmpty())
        <div class="awas">
            <strong>{{ $belum->count() }} peserta belum dapat kamar:</strong>
            {{ $belum->pluck('nama')->implode(', ') }}.
        </div>
    @endif

    <p class="kaki">
        Hubungi {{ $pemesan->take(3)->implode(', ') }}{{ $pemesan->count() > 3 ? ', dan lainnya' : '' }}
        untuk perubahan daftar ini.
    </p>
</body>
</html>
