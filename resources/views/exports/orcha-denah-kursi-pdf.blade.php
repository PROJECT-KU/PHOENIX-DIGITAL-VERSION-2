{{-- Denah tempat duduk, untuk dibawa sopir dan tour leader.

     Berbentuk PETAK, bukan daftar nomor. Yang dipakai di lapangan adalah
     bentuk busnya: tour leader berdiri di depan lorong dan membaca ke
     belakang, dan daftar "1. Budi, 2. Siti" menuntutnya menerjemahkan sendiri
     jadi posisi sambil berdiri di parkiran.

     Daftar nama berurut tetap ikut di halaman berikutnya — itu yang dipakai
     memanggil nama sebelum naik, dan petak tidak bisa dipakai untuk itu. --}}
@php
    $penghuni = [];

    foreach ($peserta as $satu) {
        if (blank($satu['kursi'] ?? null)) {
            continue;
        }

        $penghuni[trim((string) ($satu['bus'] ?? '')).'|'.trim((string) $satu['kursi'])] = $satu;
    }

    $belum = collect($peserta)->filter(fn ($s) => blank($s['kursi'] ?? null))->values();
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Denah Kursi</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #16202b; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .ket { color: #5b6876; font-size: 10px; margin: 0 0 12px; }
        .bus { margin-bottom: 16px; page-break-inside: avoid; }
        .bus-nama { font-size: 12px; font-weight: bold; margin: 0 0 6px; }
        table.petak { border-collapse: separate; border-spacing: 3px; }
        table.petak td { width: 86px; height: 40px; vertical-align: middle; }
        .kursi {
            border: 1px solid #b9c6d2; border-radius: 5px; padding: 3px 4px;
            height: 100%; box-sizing: border-box;
        }
        .kursi .no { font-size: 8px; color: #6b7785; }
        .kursi .nm { font-size: 9px; font-weight: bold; line-height: 1.15; }
        .kursi.kosong { border-style: dashed; color: #9aa6b2; }
        .kursi.L { background: #eaf5fd; border-color: #8fc6e8; }
        .kursi.P { background: #fdeef5; border-color: #eaa3c4; }
        .lorong { width: 22px; }
        .depan { font-size: 9px; color: #6b7785; margin: 0 0 4px; letter-spacing: 1px; }
        table.daftar { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.daftar th, table.daftar td { border: 1px solid #d8e2ea; padding: 4px 6px; text-align: left; }
        table.daftar th { background: #eef6fb; font-size: 9px; }
        .awas { background: #fff7e6; border: 1px solid #f3d49b; padding: 6px 8px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Denah Tempat Duduk</h1>
    <p class="ket">
        {{ $keberangkatan['paket']['nama'] ?? 'Paket' }} &middot;
        Rombongan {{ $keberangkatan['rentang_tanggal'] ?? '—' }} &middot;
        dicetak {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
    </p>

    @foreach ($petak as $bus)
        <div class="bus">
            <p class="bus-nama">{{ $bus['nama'] }} &middot; {{ $bus['jumlah_kursi'] }} kursi</p>
            <p class="depan">— DEPAN / SOPIR —</p>

            <table class="petak">
                @foreach ($bus['baris'] as $baris)
                    <tr>
                        @foreach ($baris as $sel)
                            @if ($sel === null)
                                <td class="lorong"></td>
                            @else
                                @php
                                    $orang = $penghuni[$bus['nama'].'|'.$sel] ?? null;
                                    $tanda = ($orang['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'L'
                                        : ((($orang['jenis_kelamin'] ?? '') === 'Perempuan') ? 'P' : '');
                                @endphp
                                <td>
                                    <div class="kursi {{ $orang ? $tanda : 'kosong' }}">
                                        <div class="no">{{ $sel }}</div>
                                        <div class="nm">{{ $orang['nama'] ?? '' }}</div>
                                    </div>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    {{-- Daftar berurut, di halaman sendiri.

         Dipakai memanggil nama sebelum naik — pekerjaan yang tidak bisa
         dilakukan dari petak, karena mata harus melompat baris demi baris. --}}
    <div style="page-break-before: always">
        <h1>Daftar Peserta per Kursi</h1>
        <p class="ket">Urut nomor kursi — untuk memanggil nama sebelum berangkat.</p>

        @foreach ($petak as $bus)
            <p class="bus-nama">{{ $bus['nama'] }}</p>
            <table class="daftar">
                <tr>
                    <th style="width:40px">Kursi</th>
                    <th>Nama</th>
                    <th style="width:28px">L/P</th>
                    <th style="width:150px">Titik jemput</th>
                    <th style="width:90px">Pemesan</th>
                </tr>
                @foreach ($bus['kursi'] as $nomor)
                    @php $orang = $penghuni[$bus['nama'].'|'.$nomor] ?? null; @endphp
                    <tr>
                        <td>{{ $nomor }}</td>
                        <td>{{ $orang['nama'] ?? '—' }}</td>
                        <td>{{ mb_substr((string) ($orang['jenis_kelamin'] ?? ''), 0, 1) }}</td>
                        <td>{{ $orang['titik_jemput'] ?? '' }}</td>
                        <td>{{ $orang['kode'] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        @endforeach

        {{-- Siapa naik di mana, per bus.

             Pertanyaan yang ditanyakan sopir di titik jemput ketiga: "di Janti
             nanti siapa saja yang naik?". Daftar per kursi di atas tidak bisa
             menjawabnya — mata harus melompat dua puluh baris sambil berdiri
             di pinggir jalan. --}}
        @foreach ($petak as $bus)
            @php
                $naikDiSini = collect($penghuni)
                    ->filter(fn ($o) => ($o['bus'] ?? '') === $bus['nama'])
                    ->groupBy(fn ($o) => trim((string) ($o['titik_jemput'] ?? '')) ?: 'Belum ditentukan')
                    ->sortKeys();
            @endphp

            @if ($naikDiSini->isNotEmpty())
                <p class="bus-nama" style="margin-top:12px">Titik jemput — {{ $bus['nama'] }}</p>
                <table class="daftar">
                    <tr>
                        <th style="width:160px">Naik di</th>
                        <th style="width:40px">Orang</th>
                        <th>Nama (nomor kursi)</th>
                    </tr>
                    @foreach ($naikDiSini as $titik => $orang)
                        <tr>
                            <td><strong>{{ $titik }}</strong></td>
                            <td>{{ $orang->count() }}</td>
                            <td>
                                {{ $orang->sortBy(fn ($o) => (int) $o['kursi'])
                                    ->map(fn ($o) => $o['nama'].' ('.$o['kursi'].')')
                                    ->implode(', ') }}
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        @endforeach

        {{-- Yang belum dapat kursi DISEBUT, bukan dihilangkan dari lembarnya.

             Lembar yang hanya memuat yang sudah ditempatkan terbaca sebagai
             daftar lengkap, dan yang kurang baru ketahuan saat orangnya berdiri
             di parkiran tanpa nama di kertas. --}}
        @if ($belum->isNotEmpty())
            <div class="awas">
                <strong>{{ $belum->count() }} peserta belum dapat kursi:</strong>
                {{ $belum->pluck('nama')->implode(', ') }}.
            </div>
        @endif
    </div>
</body>
</html>
