{{ $judul }}

@if ($rupa === 'pembatalan')
Kegiatan berikut dibatalkan. Anda tidak perlu hadir.
@elseif ($rupa === 'dikeluarkan')
Anda tidak lagi tercatat sebagai peserta kegiatan berikut, dan kegiatan ini
sudah hilang dari dasbor Anda. Kegiatannya sendiri tetap berjalan.
@elseif ($rupa === 'perubahan')
Ada perubahan pada kegiatan yang Anda ikuti. Harap perhatikan waktunya.
@else
Anda diundang mengikuti kegiatan berikut.
@endif

Kegiatan : {{ $kegiatan->judul }}
Jenis    : {{ $namaJenis }}
Tanggal  : {{ $tanggal }}
Waktu    : {{ $waktu }}
@if ($kegiatan->lokasi)
Lokasi   : {{ $kegiatan->lokasi }}
@endif
@if ($kegiatan->deskripsi)

Catatan:
{{ $kegiatan->deskripsi }}
@endif

@if (! in_array($rupa, ['pembatalan', 'dikeluarkan']))
Kegiatan ini juga muncul di dasbor Anda saat masuk ke panel lemon.
@endif

Dikirim oleh {{ $olehSiapa }} pada {{ now()->translatedFormat('d F Y, H:i') }} WIB.

--
lemon by ACM
