{{-- Satu baris label–nilai bahasa rupa dasbor: label kiri, nilai rata kanan.

     Dipakai menggantikan medan berkotak di kartu yang hanya selebar setengah
     rak. Sebagai kotak, "Isuzu Elf Long Medium Bus Pariwisata 2023 · 1.200 cc"
     terbungkus jadi empat baris dan memaksa SELURUH kotak di barisnya ikut
     setinggi itu — tetangganya yang isinya satu kata ("Manual") lalu berdiri
     dengan tiga baris kosong di bawahnya. Sebagai baris, panjang isi hanya
     menambah tinggi barisnya sendiri.

     Bentuknya sama dengan kartu "Pembayaran & Catatan" dan "Data Pembeli" di
     Pesanan Toko. --}}
<div class="dsb-data-baris">
    <span class="dsb-data-label">{{ $label }}</span>
    <span class="dsb-data-nilai">
        @if (! empty($tautan ?? null) && filled($nilai))
            <a href="{{ $tautan }}" target="_blank" rel="noopener">{{ $nilai }}</a>
        @else
            {{ filled($nilai ?? null) ? $nilai : '—' }}
        @endif
    </span>
</div>
