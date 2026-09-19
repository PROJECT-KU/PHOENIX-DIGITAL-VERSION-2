<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #1c1f26; font-size: 10px; }
        .kepala { border-bottom: 2px solid #7c3aed; padding-bottom: 8px; margin-bottom: 12px; }
        .kepala h1 { margin: 0 0 3px; font-size: 16px; }
        .kepala p { margin: 0; font-size: 9px; color: #64748b; }
        .ringkas { margin-bottom: 10px; font-size: 9px; color: #334155; }
        .ringkas b { color: #1c1f26; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; border-bottom: 1px solid #cbd5e1; padding: 6px 5px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid #eef2f7; padding: 6px 5px; vertical-align: top; }
        .tengah { text-align: center; }
        .kecil { color: #64748b; font-size: 8px; }
        .kaki { margin-top: 14px; font-size: 8px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <div class="kepala">
        <h1>{{ $judul }} — Phoenix Digital</h1>
        <p>Dicetak {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>

    <div class="ringkas">
        <b>{{ $testimoni->count() }}</b> testimoni · rata-rata <b>{{ $rata ? number_format($rata, 1, ',', '.') : '–' }}</b> bintang
        @if ($saringan)
            · saringan: {{ implode(' · ', $saringan) }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 62px;">Tanggal</th>
                <th style="width: 95px;">Nama</th>
                <th style="width: 75px;">Peran</th>
                <th class="tengah" style="width: 34px;">Bintang</th>
                <th>Testimoni</th>
                <th style="width: 58px;">Status</th>
                <th style="width: 52px;">Beranda</th>
                <th style="width: 85px;">Ditinjau</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($testimoni as $t)
                <tr>
                    <td>{{ $t->created_at?->format('d/m/Y') }}<div class="kecil">{{ $t->created_at?->format('H:i') }}</div></td>
                    <td>
                        {{ $t->nama }}
                        {{-- nama_publik: yang dilihat pengunjung (tersamar bila anonim). --}}
                        @if ($t->anonim)<div class="kecil">tampil: {{ $t->nama_publik }}</div>@endif
                    </td>
                    <td>{{ $t->peran ?: '—' }}</td>
                    <td class="tengah">{{ $t->rating }}</td>
                    <td>
                        {{ $t->pesan }}
                        @if ($t->alasan_tolak)<div class="kecil">Alasan tolak: {{ $t->alasan_tolak }}</div>@endif
                    </td>
                    <td>{{ ['pending' => 'Menunggu', 'active' => 'Disetujui', 'non-active' => 'Ditolak'][$t->status] ?? $t->status }}</td>
                    <td>
                        {{ $t->status === 'active' && ! $t->tersembunyiKarenaRating() ? 'Ya' : 'Tidak' }}
                        @if ($t->sorot)<div class="kecil">disorot</div>@endif
                    </td>
                    <td>
                        {{ $t->ditinjau_at?->format('d/m/Y H:i') ?: '—' }}
                        @if ($t->peninjau)<div class="kecil">{{ $t->peninjau->name }}</div>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="tengah kecil" style="padding: 18px;">Tidak ada testimoni untuk saringan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="kaki">Nomor WhatsApp pengirim sengaja tidak dicetak.</div>
</body>
</html>
