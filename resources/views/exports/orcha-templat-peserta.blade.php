{{-- Templat daftar peserta.

     TIDAK memakai baris judul berhias di atas.

     Berkas ini dibuat untuk diunggah kembali, dan pengurai di lemon membaca
     kolom pertama tiap baris sebagai nama. Judul berhias di baris pertama
     karenanya ikut masuk sebagai peserta bernama "Daftar Peserta — OT-…" —
     ketahuan saat mengujinya bolak-balik, bukan saat dipakai.

     Keterangan titik jemput ditaruh di baris paling bawah dengan kolom pertama
     dikosongkan, sehingga pengurainya melewatinya. --}}
<table>
    <tr>
        <th style="background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;">
            Nama Peserta
        </th>
        <th style="background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;">
            Titik Jemput
        </th>
        {{-- Kolom ketiga: diisi HANYA bila baris itu menggantikan peserta lama.
             Panitia mengirim satu daftar berisi campuran — sebagian nama baru,
             sebagian pengganti — dan memilahnya dengan tangan cuma memindahkan
             pekerjaan, tidak menghilangkannya. --}}
        <th style="background-color:#EEF6FB;color:#0F2D4A;font-weight:bold;border:1px solid #CFE4F2;padding:5px;">
            Menggantikan (opsional)
        </th>
    </tr>

    {{-- Baris kosong sebanyak peserta yang tercatat: panitia tinggal mengisi,
         tidak perlu menghitung sendiri berapa baris yang dibutuhkan. --}}
    @for ($nomor = 1; $nomor <= $baris; $nomor++)
        <tr>
            <td style="border:1px solid #E9EFF5;padding:5px;"></td>
            <td style="border:1px solid #E9EFF5;padding:5px;"></td>
            <td style="border:1px solid #E9EFF5;padding:5px;"></td>
        </tr>
    @endfor

    @if ($titikJemput !== [])
        <tr>
            <td></td>
            <td style="color:#64748B;">
                Titik jemput tersedia: {{ implode(', ', $titikJemput) }} — {{ $kode }}
            </td>
            <td style="color:#64748B;">
                Isi hanya untuk peserta pengganti. Titik jemputnya tetap di kolom kedua;
                dikosongkan berarti naik di titik yang sama.
            </td>
        </tr>
    @endif
</table>
