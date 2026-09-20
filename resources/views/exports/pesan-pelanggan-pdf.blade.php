<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pesan Pelanggan</title>
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
        <h1>Pesan Pelanggan (Helpdesk) — Phoenix Digital</h1>
        <p>Dicetak {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>

    <div class="ringkas">
        <b>{{ $pesan->count() }}</b> pesan
        @if ($saringan)
            · saringan: {{ implode(' · ', $saringan) }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 78px;">Tiket</th>
                <th style="width: 60px;">Masuk</th>
                <th style="width: 100px;">Pengirim</th>
                <th>Pesan</th>
                <th style="width: 58px;">Status</th>
                <th style="width: 52px;">Prioritas</th>
                <th style="width: 72px;">Tindak lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pesan as $p)
                <tr>
                    <td>{{ $p->ticket }}</td>
                    <td>{{ $p->created_at?->format('d/m/Y') }}<div class="kecil">{{ $p->created_at?->format('H:i') }}</div></td>
                    <td>
                        {{ $p->name }}
                        <div class="kecil">{{ $p->email }}</div>
                    </td>
                    <td>{{ $p->message }}</td>
                    <td>{{ $p->tampilanStatus()[0] }}<div class="kecil">{{ $p->belumDibaca() ? 'belum dibaca' : 'dibaca' }}</div></td>
                    <td>{{ $p->tampilanPrioritas()[0] }}</td>
                    <td>
                        {{ $p->petugas?->name ?: 'Belum ditugaskan' }}
                        <div class="kecil">{{ $p->replied_at ? 'dibalas '.$p->replied_at->format('d/m/Y') : ($p->lewatBatas() ? 'lewat batas' : 'belum dibalas') }}</div>
                        @if ($balasan = optional($p->logs->where('jenis', 'balasan')->last())->isi)
                            <div class="kecil" style="margin-top:3px; color:#475569;">"{{ \Illuminate\Support\Str::limit($balasan, 90) }}"</div>
                        @endif
                        @if ($nilai = $p->tampilanKepuasan())
                            <div class="kecil" style="margin-top:3px; color:{{ $nilai[2] }};">Penilaian: {{ $nilai[0] }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="tengah kecil" style="padding: 18px;">Tidak ada pesan untuk saringan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="kecil" style="margin-top: 14px; text-align: right;">Nomor telepon & alamat surel hanya untuk keperluan balasan.</div>
</body>
</html>
