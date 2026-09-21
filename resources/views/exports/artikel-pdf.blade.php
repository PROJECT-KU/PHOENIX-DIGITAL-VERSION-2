<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Artikel</title>
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
        .kecil { color: #64748b; font-size: 8px; }
        .tengah { text-align: center; }
    </style>
</head>
<body>
    <div class="kepala">
        <h1>Daftar Artikel Blog — Phoenix Digital</h1>
        <p>Dicetak {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>

    <div class="ringkas">
        <b>{{ $artikel->count() }}</b> artikel ·
        <b>{{ number_format($artikel->sum('views'), 0, ',', '.') }}</b> kali dibaca
        @if ($saringan)
            · saringan: {{ collect($saringan)->pluck('label')->implode(' · ') }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 34%;">Judul</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 15%;">Tayang</th>
                <th style="width: 9%;">Dibaca</th>
                <th>Penulis</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($artikel as $a)
                <tr>
                    <td>
                        {{ $a->title }}
                        <div class="kecil">/blog/{{ $a->slug }}</div>
                    </td>
                    <td>{{ $a->category ?: '-' }}</td>
                    <td>{{ $a->keadaan()[0] }}</td>
                    <td>{{ optional($a->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                    <td>{{ number_format((int) $a->views, 0, ',', '.') }}</td>
                    <td>{{ $a->author ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="tengah kecil" style="padding: 18px;">Tidak ada artikel untuk saringan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
