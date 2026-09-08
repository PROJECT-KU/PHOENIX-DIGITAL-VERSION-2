{{ $judul }}

@if ($ditutup)
Modul {{ $namaModul }} untuk sementara tidak bisa dipakai di panel lemon.
Izin Anda tidak dicabut — begitu dibuka kembali, semuanya kembali seperti semula.

Ditutup sejak     : {{ $mulai ? $mulai->translatedFormat('l, d F Y, H:i').' WIB' : 'baru saja' }}
Perkiraan selesai : {{ $sampai ? $sampai->translatedFormat('l, d F Y, H:i').' WIB' : 'belum ditentukan, Anda akan dikabari lewat surel' }}

Keterangan: {{ $pesan }}
@else
Modul {{ $namaModul }} sudah bisa dipakai lagi seperti biasa. Terima kasih sudah menunggu.
@endif

Diubah oleh {{ $olehSiapa }} pada {{ now()->translatedFormat('d F Y, H:i') }} WIB.

--
lemon by ACM
