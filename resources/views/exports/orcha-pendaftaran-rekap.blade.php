{{-- Rekap daftar pendaftaran, satu baris per pemesanan.

     Angka uang ditulis sebagai ANGKA POLOS, bukan "Rp 8.000.000". Yang
     mengerjakan rekap menjumlahkan kolomnya di Excel, dan kolom berisi teks
     berawalan "Rp" dijumlahkan sebagai nol tanpa satu pun peringatan — total
     yang keliru lalu terbaca sebagai total yang benar. --}}
@php
    $tanggal = fn ($nilai, $bentuk = 'd/m/Y') => $nilai
        ? \Carbon\Carbon::parse($nilai)->locale('id')->translatedFormat($bentuk)
        : '';

    $judulUtama = 'background-color:#0F2D4A;color:#FFFFFF;font-size:15px;font-weight:bold;padding:8px;';
    $kepalaKolom = 'background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;';
    $sel = 'border:1px solid #E3EEF6;padding:4px;';
    $catatan = 'color:#6B7785;font-size:11px;padding:4px;';
@endphp

<table>
    <tr><td colspan="10" style="{{ $judulUtama }}">REKAP PENDAFTARAN TRIP</td></tr>
    <tr>
        <td colspan="10" style="{{ $catatan }}">
            Dibuat {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB &middot;
            {{ $terbawa }} baris
            @if ($total > $terbawa)
                dari {{ $total }} hasil saringan — BARIS BERIKUTNYA TIDAK IKUT.
                Persempit saringannya lalu unduh lagi supaya rekapnya utuh.
            @else
                (seluruh hasil saringan)
            @endif
            @if (($saringan['perlu_ditagih'] ?? null))
                &middot; hanya yang perlu ditelepon
            @endif
        </td>
    </tr>

    <tr>
        <td style="{{ $kepalaKolom }}">Kode</td>
        <td style="{{ $kepalaKolom }}">Nama pemesan</td>
        <td style="{{ $kepalaKolom }}">WhatsApp</td>
        <td style="{{ $kepalaKolom }}">Paket</td>
        <td style="{{ $kepalaKolom }}">Rombongan</td>
        <td style="{{ $kepalaKolom }}">Tanggal berangkat</td>
        <td style="{{ $kepalaKolom }}">Jumlah peserta</td>
        <td style="{{ $kepalaKolom }}">Total tagihan</td>
        <td style="{{ $kepalaKolom }}">Sudah dibayar</td>
        <td style="{{ $kepalaKolom }}">Sisa</td>
    </tr>

    @foreach ($baris as $satu)
        @php $tagihan = $satu['tagihan'] ?? []; @endphp
        <tr>
            <td style="{{ $sel }}">{{ $satu['kode'] ?? '' }}</td>
            <td style="{{ $sel }}">{{ $satu['nama'] ?? '' }}</td>
            {{-- Nomor diawali apostrof supaya Excel tidak membacanya sebagai
                 angka dan membuang nol di depannya: "081234567890" jadi
                 "81234567890", dan nomor itu tidak bisa dihubungi. --}}
            <td style="{{ $sel }}">{{ ($satu['whatsapp'] ?? '') !== '' ? "'".$satu['whatsapp'] : '' }}</td>
            <td style="{{ $sel }}">{{ $satu['paket']['nama'] ?? '' }}</td>
            <td style="{{ $sel }}">
                {{ $satu['keberangkatan']['rentang_tanggal'] ?? 'tanpa rombongan' }}
            </td>
            <td style="{{ $sel }}">{{ $tanggal($satu['tanggal_berangkat'] ?? null) }}</td>
            <td style="{{ $sel }}">{{ (int) ($satu['jumlah_peserta'] ?? 0) }}</td>
            <td style="{{ $sel }}">{{ (int) ($tagihan['total'] ?? 0) }}</td>
            <td style="{{ $sel }}">{{ (int) ($tagihan['sudah'] ?? 0) }}</td>
            <td style="{{ $sel }}">{{ (int) ($tagihan['sisa'] ?? 0) }}</td>
        </tr>
    @endforeach

    {{-- Baris jumlah, dihitung di sini.

         Rekap tanpa baris jumlah menuntut yang membacanya menyeleksi kolom
         sendiri — dan seleksi yang kelebihan satu baris judul menghasilkan
         angka yang tetap terbaca masuk akal. --}}
    <tr>
        <td colspan="6" style="{{ $kepalaKolom }}">JUMLAH {{ $terbawa }} BARIS DI ATAS</td>
        <td style="{{ $kepalaKolom }}">{{ collect($baris)->sum(fn ($s) => (int) ($s['jumlah_peserta'] ?? 0)) }}</td>
        <td style="{{ $kepalaKolom }}">{{ collect($baris)->sum(fn ($s) => (int) ($s['tagihan']['total'] ?? 0)) }}</td>
        <td style="{{ $kepalaKolom }}">{{ collect($baris)->sum(fn ($s) => (int) ($s['tagihan']['sudah'] ?? 0)) }}</td>
        <td style="{{ $kepalaKolom }}">{{ collect($baris)->sum(fn ($s) => (int) ($s['tagihan']['sisa'] ?? 0)) }}</td>
    </tr>
</table>
