<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tiket {{ $pesan->ticket }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #1c1f26; font-size: 11px; }
        .kepala { border-bottom: 2px solid #7c3aed; padding-bottom: 8px; margin-bottom: 14px; }
        .kepala h1 { margin: 0 0 3px; font-size: 16px; }
        .kepala p { margin: 0; font-size: 9px; color: #64748b; }
        .rinci { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .rinci td { padding: 5px 7px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        .rinci td.judul { width: 110px; color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
        .kutip { padding: 11px 13px; border-left: 3px solid #7c3aed; background: #faf5ff; margin-bottom: 16px; white-space: pre-wrap; }
        h2 { font-size: 12px; margin: 0 0 8px; }
        .baris { border-bottom: 1px solid #f1f5f9; padding: 7px 0; }
        .baris b { font-size: 10.5px; }
        .baris .kecil { color: #64748b; font-size: 9px; }
        .baris .isi { margin-top: 4px; padding: 7px 9px; background: #f8fafc; border: 1px solid #eef2f7; white-space: pre-wrap; }
        .kaki { margin-top: 18px; font-size: 8.5px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <div class="kepala">
        <h1>Tiket {{ $pesan->ticket }} — Phoenix Digital</h1>
        <p>Dicetak {{ now()->locale('id')->translatedFormat('l, d F Y H:i') }} oleh {{ auth()->user()?->name }}</p>
    </div>

    <table class="rinci">
        <tr><td class="judul">Pengirim</td><td>{{ $pesan->name }}</td></tr>
        <tr><td class="judul">Kontak</td><td>{{ $pesan->email ?: '—' }}{{ $pesan->no_telp ? ' · '.$pesan->no_telp : '' }}</td></tr>
        <tr><td class="judul">Masuk</td><td>{{ $pesan->created_at?->locale('id')->translatedFormat('d F Y, H:i') }}</td></tr>
        <tr><td class="judul">Status</td><td>{{ $pesan->tampilanStatus()[0] }} · prioritas {{ $pesan->tampilanPrioritas()[0] }}</td></tr>
        <tr><td class="judul">Topik</td><td>{{ $pesan->labelKategori() ?: 'Belum ditentukan' }}</td></tr>
        <tr><td class="judul">Petugas</td><td>{{ $pesan->petugas?->name ?: 'Belum ditugaskan' }}</td></tr>
        <tr><td class="judul">Dibaca</td><td>{{ $pesan->read_at?->locale('id')->translatedFormat('d F Y, H:i') ?: 'Belum dibaca' }}</td></tr>
        <tr><td class="judul">Penilaian</td><td>{{ $pesan->tampilanKepuasan()[0] ?? 'Belum dinilai' }}{{ $pesan->kepuasan_komentar ? ' — '.$pesan->kepuasan_komentar : '' }}</td></tr>
        <tr><td class="judul">Dibalas</td><td>{{ $pesan->replied_at?->locale('id')->translatedFormat('d F Y, H:i') ?: 'Belum dicatat' }}</td></tr>
    </table>

    <h2>Isi pesan</h2>
    <div class="kutip">{{ $pesan->message }}</div>

    @if ($pesan->lampiran->isNotEmpty())
        <h2>Lampiran</h2>
        @foreach ($pesan->lampiran as $l)
            <div class="baris">
                <b>{{ $l->nama_asli }}</b>
                <div class="kecil">{{ $l->ukuranTerbaca() }} · {{ $l->dariAdmin() ? 'ditambahkan admin' : 'dari pelanggan' }}</div>
            </div>
        @endforeach
    @endif

    <h2>Linimasa</h2>
    <div class="baris">
        <b>Pesan masuk</b>
        <div class="kecil">{{ $pesan->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · dari {{ $pesan->name }}</div>
    </div>
    @forelse ($pesan->logs as $log)
        <div class="baris">
            <b>{{ $log->tampilan()[2] }}</b>
            <div class="kecil">{{ $log->created_at?->locale('id')->translatedFormat('d M Y, H:i') }} · {{ $log->pelaku() }}</div>
            @if ($log->isi)
                <div class="isi">{{ $log->isi }}</div>
            @endif
        </div>
    @empty
        <div class="baris kecil">Belum ada tindak lanjut yang tercatat.</div>
    @endforelse

    <div class="kaki">Dokumen internal. Nomor telepon & alamat surel hanya untuk keperluan balasan.</div>
</body>
</html>
