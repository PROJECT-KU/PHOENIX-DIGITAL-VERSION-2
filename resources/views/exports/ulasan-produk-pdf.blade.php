<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Moderasi Ulasan Produk</title>
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
    </style>
</head>
<body>
    <div class="kepala">
        <h1>Moderasi Ulasan Produk — Phoenix Digital</h1>
        <p>Dicetak {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>

    <div class="ringkas">
        <b>{{ $ulasan->count() }}</b> ulasan · rata-rata <b>{{ $rata ? number_format($rata, 1, ',', '.') : '–' }}</b> bintang
        @if ($saringan)
            · saringan: {{ implode(' · ', $saringan) }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 62px;">Tanggal</th>
                <th style="width: 120px;">Produk / Paket</th>
                <th style="width: 90px;">Pengulas</th>
                <th class="tengah" style="width: 34px;">Bintang</th>
                <th>Ulasan</th>
                <th style="width: 68px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ulasan as $u)
                <tr>
                    <td>{{ $u->created_at?->format('d/m/Y') }}<div class="kecil">{{ $u->created_at?->format('H:i') }}</div></td>
                    <td>
                        {{ $u->namaTarget() }}
                        <div class="kecil">{{ $u->jenis === 'paket' ? 'paket bundling' : 'produk satuan' }}</div>
                    </td>
                    <td>{{ $u->nama }}</td>
                    <td class="tengah">{{ $u->rating }}</td>
                    <td>{{ $u->ulasan }}</td>
                    <td>{{ $u->tampilanStatus()[0] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="tengah kecil" style="padding: 18px;">Tidak ada ulasan untuk saringan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
