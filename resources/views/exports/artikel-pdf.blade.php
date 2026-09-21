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
        /* Mode "ikut isi": tiap artikel satu blok, bukan satu baris tabel —
           isi artikel tidak pernah muat di dalam sel selebar kolom. */
        .naskah { page-break-inside: avoid; border-bottom: 1px solid #eef2f7; padding: 10px 0; }
        .naskah h2 { margin: 0 0 3px; font-size: 12px; }
        .naskah .meta { margin: 0 0 6px; font-size: 8px; color: #64748b; }
        .naskah .ringkasan { margin: 0 0 6px; font-size: 9px; color: #334155; font-style: italic; }
        .naskah .isi { margin: 0; font-size: 9px; line-height: 1.55; text-align: justify; white-space: pre-line; }
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

@if ($ikutIsi)
    @forelse ($artikel as $a)
        <div class="naskah">
            <h2>{{ $a->title }}</h2>
            <p class="meta">
                /blog/{{ $a->slug }} ·
                {{ $a->category ?: 'tanpa kategori' }} ·
                {{ $a->trashed() ? 'Di tong sampah' : $a->keadaan()[0] }} ·
                {{ optional($a->published_at)->format('d/m/Y H:i') ?: 'belum tayang' }} ·
                {{ number_format((int) $a->views, 0, ',', '.') }} dibaca ·
                {{ $a->lamaBaca() }} menit baca
            </p>
            @if ($a->excerpt)
                <p class="ringkasan">{{ $a->excerpt }}</p>
            @endif
            <p class="isi">{{ trim(preg_replace('/\n{3,}/', "\n\n", html_entity_decode(strip_tags(preg_replace('/<\/(p|div|h[1-6]|li|blockquote)>/i', "\n\n", $a->body))))) }}</p>
        </div>
    @empty
        <p class="tengah kecil" style="padding: 18px;">Tidak ada artikel untuk saringan ini.</p>
    @endforelse
@else

    <table>
        <thead>
            <tr>
                <th style="width: 34%;">Judul</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 15%;">Tayang</th>
                <th style="width: 9%;">Dibaca</th>
                <th style="width: 9%;">30 hari</th>
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
                    <td>{{ $a->trashed() ? 'Di tong sampah' : $a->keadaan()[0] }}</td>
                    <td>{{ optional($a->published_at)->format('d/m/Y H:i') ?: '-' }}</td>
                    <td>{{ number_format((int) $a->views, 0, ',', '.') }}</td>
                    <td>{{ number_format($a->baca30(), 0, ',', '.') }}</td>
                    <td>{{ $a->author ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="tengah kecil" style="padding: 18px;">Tidak ada artikel untuk saringan ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endif
</body>
</html>
