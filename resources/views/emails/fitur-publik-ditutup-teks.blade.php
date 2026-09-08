{{ $judul }}

@if ($ditutup)
Halaman {{ $namaHalaman }} di Phoenix Digital sedang kami perbaiki, jadi untuk
sementara belum bisa diakses. Bagian lain tetap berjalan seperti biasa.

Sejak              : {{ $mulai ? $mulai->translatedFormat('l, d F Y, H:i').' WIB' : 'baru saja' }}
Perkiraan selesai  : {{ $sampai ? $sampai->translatedFormat('l, d F Y, H:i').' WIB' : 'belum bisa kami pastikan' }}

{{ $pesan }}
@else
Kabar baik: halaman {{ $namaHalaman }} sudah bisa diakses lagi seperti biasa.
Terima kasih sudah menunggu.
@endif

Butuh bantuan? Balas surel ini, atau hubungi kami lewat WhatsApp di
https://wa.me/6289505967995

--
Phoenix Digital
