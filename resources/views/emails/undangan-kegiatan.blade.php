<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $judul }}</title>
</head>
{{-- text-size-adjust dikunci 100%: aplikasi Gmail di ponsel menaikkan sendiri
     huruf yang dianggapnya terlalu kecil, dan saat menskalakan ulang begitu ia
     kerap membuang perataan teks yang diwarisi elemen inline. --}}
<body style="margin:0;padding:26px 16px;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;color:#334155;line-height:1.6;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;text-size-adjust:100%;">
@php
    // Warna kegiatan dipakai apa adanya untuk undangan & perubahan.
    //
    // PEMBATALAN merah: itu kabar yang harus terbaca dalam sekejap, karena
    // orang yang melewatkannya datang ke rapat yang sudah tidak ada. Merah
    // bukan kemeriahan, melainkan tanda berhenti.
    //
    // DIKELUARKAN tetap abu-abu: bagi si penerima kegiatannya memang berlanjut,
    // hanya tanpa dirinya. Memerahkannya menyamakan "Anda tidak perlu ikut"
    // dengan "acaranya batal", dan dua hal itu tidak sama.
    $batal = $rupa === 'pembatalan';
    $pudar = in_array($rupa, ['pembatalan', 'dikeluarkan']);

    $warna = match (true) {
        $batal => '#dc2626',
        $pudar => '#94a3b8',
        default => $kegiatan->warna(),
    };

    $lembut = match (true) {
        $batal => '#fef2f2',
        $pudar => '#f1f5f9',
        default => $kegiatan->lembut(),
    };
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
<tr><td align="center">
{{-- width="100%" + max-width, bukan width="540": lebar tetap 540 membuat
     kartunya terpotong di layar ponsel yang lebih sempit. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
       style="width:100%;max-width:540px;background:#ffffff;border-radius:16px;border:1px solid #e8ecf2;overflow:hidden;">

    {{-- Pita warna setipis mungkin sebagai pengganti gambar: klien surel sering
         memblokir gambar, tapi tidak pernah memblokir warna latar sel tabel. --}}
    <tr><td style="height:5px;background:{{ $warna }};line-height:5px;font-size:0;">&nbsp;</td></tr>

    <tr>
        <td align="center" style="padding:32px 30px 20px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 16px;">
                <tr><td style="padding:6px 14px;border-radius:999px;background:{{ $lembut }};font-size:12px;font-weight:700;letter-spacing:.04em;color:{{ $warna }};text-transform:uppercase;">
                    @switch ($rupa)
                        @case ('pembatalan') Dibatalkan @break
                        @case ('dikeluarkan') Bukan peserta lagi @break
                        @case ('perubahan') Jadwal berubah @break
                        @default {{ $namaJenis }}
                    @endswitch
                </td></tr>
            </table>
            <h1 style="margin:0;font-size:20px;line-height:1.35;color:#1e293b;">{{ $kegiatan->judul }}</h1>
        </td>
    </tr>

    <tr>
        <td style="padding:0 30px;">
            {{-- Kalimat pembatalan ikut merah dan tebal. Pita serta lencana di
                 atas sudah merah, tapi keduanya hiasan; kalimat inilah yang
                 dibaca orang, jadi ia yang paling tidak boleh terlewat. --}}
            <p style="margin:0 0 20px;font-size:14.5px;text-align:center;color:{{ $rupa === 'pembatalan' ? '#dc2626' : '#64748b' }};{{ $rupa === 'pembatalan' ? 'font-weight:700;' : '' }}">
                @if ($rupa === 'pembatalan')
                    Kegiatan ini dibatalkan. Anda tidak perlu hadir.
                @elseif ($rupa === 'dikeluarkan')
                    Anda tidak lagi tercatat sebagai peserta, dan kegiatan ini sudah hilang
                    dari dasbor Anda. Kegiatannya sendiri tetap berjalan.
                @elseif ($rupa === 'perubahan')
                    Ada perubahan pada kegiatan yang Anda ikuti. Harap perhatikan waktunya.
                @else
                    Anda diundang mengikuti kegiatan ini.
                @endif
            </p>

            {{-- Label kiri, nilai kanan: satu kolom yang mudah disusuri mata. --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border:1px solid #eef1f6;border-radius:12px;background:#fbfcfd;margin-bottom:22px;">
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#94a3b8;border-bottom:1px solid #f2f5f9;">Tanggal</td>
                    <td align="right" style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#1e293b;border-bottom:1px solid #f2f5f9;">
                        {{ $tanggal }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#94a3b8;{{ $kegiatan->lokasi ? 'border-bottom:1px solid #f2f5f9;' : '' }}">Waktu</td>
                    <td align="right" style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#1e293b;{{ $kegiatan->lokasi ? 'border-bottom:1px solid #f2f5f9;' : '' }}">
                        {{ $waktu }}
                    </td>
                </tr>
                @if ($kegiatan->lokasi)
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#94a3b8;">Lokasi</td>
                    <td align="right" style="padding:12px 16px;font-size:13.5px;font-weight:700;color:#1e293b;">
                        {{ $kegiatan->lokasi }}
                    </td>
                </tr>
                @endif
            </table>

            @if ($kegiatan->deskripsi)
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="border-left:3px solid {{ $warna }};background:{{ $lembut }};border-radius:0 10px 10px 0;margin-bottom:22px;">
                <tr><td style="padding:14px 16px;font-size:13.5px;color:#475569;white-space:pre-line;">{{ $kegiatan->deskripsi }}</td></tr>
            </table>
            @endif

            @if (! $pudar)
            <p style="margin:0 0 24px;font-size:13px;color:#94a3b8;text-align:center;">
                Kegiatan ini juga muncul di dasbor Anda saat masuk ke panel lemon.
            </p>
            @endif
        </td>
    </tr>

    <tr>
        <td align="center" style="padding:16px 30px 26px;border-top:1px solid #f2f5f9;text-align:center;">
            {{-- Rata tengahnya dititipkan ke atribut align="center" pada <td>,
                 bukan hanya ke CSS: atribut HTML dihormati bahkan oleh klien
                 yang membuang sebagian gaya. Nama merek dipisah jadi paragraf
                 sendiri dengan ukuran huruf yang disebut tegas — sebelumnya ia
                 hanya <strong> di balik <br>, mewarisi ukuran dan perataan dari
                 induknya, dan itulah yang lepas di Gmail ponsel. --}}
            <p style="margin:0 0 6px;font-size:13px;line-height:1.5;color:#a8b3c4;text-align:center;">
                Dikirim oleh {{ $olehSiapa }} · {{ now()->translatedFormat('d F Y, H:i') }} WIB
            </p>
            <p style="margin:0;font-size:13px;line-height:1.5;font-weight:700;color:#94a3b8;text-align:center;">
                lemon by ACM
            </p>
        </td>
    </tr>
</table>
</td></tr>
</table>
</body>
</html>
