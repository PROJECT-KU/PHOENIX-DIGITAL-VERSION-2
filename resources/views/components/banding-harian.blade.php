@props(['data'])

{{--
    Teks perbandingan HARI INI vs KEMARIN.

    Dipakai bersama Dashboard & Cash Flow supaya kalimatnya seragam. Sengaja
    hanya teks (bukan kartu/grafik) sesuai permintaan, dan diletakkan menempel
    pada angka yang dibandingkan.

    'persen' bernilai null saat kemarin nol — ditampilkan sebagai kalimat, bukan
    "naik 100%", karena naik dari nol tidak bisa dipersenkan.
--}}
@php
    $persen = $data['persen'] ?? null;
    $arah = $data['arah'] ?? 0;
    $kemarin = (float) ($data['kemarin'] ?? 0);

    // Kata "kemarin" sudah ada di depan angkanya, jadi kalimat di sini sengaja
    // TIDAK mengulangnya — supaya tidak terbaca "Kemarin Rp 100 sama dengan kemarin".
    if ($persen === null) {
        [$ikon, $warna, $kalimat] = ($data['hari_ini'] ?? 0) > 0
            ? ['bi-arrow-up-right', '#047857', 'naik dari nol']
            : ['bi-dash', '#64748b', 'belum ada pemasukan'];
    } elseif ($arah > 0) {
        [$ikon, $warna, $kalimat] = ['bi-arrow-up-right', '#047857', 'naik ' . number_format(abs($persen), 1, ',', '.') . '%'];
    } elseif ($arah < 0) {
        [$ikon, $warna, $kalimat] = ['bi-arrow-down-right', '#b91c1c', 'turun ' . number_format(abs($persen), 1, ',', '.') . '%'];
    } else {
        [$ikon, $warna, $kalimat] = ['bi-dash', '#64748b', 'tidak berubah'];
    }
@endphp

<span class="d-block mt-1 text-muted" style="font-size: 0.75rem; line-height: 1.5;">
    Kemarin <b>Rp {{ number_format($kemarin, 0, ',', '.') }}</b>
    <span style="color: {{ $warna }}; font-weight: 700; white-space: nowrap;">
        <i class="bi {{ $ikon }}" style="vertical-align:-0.125em;"></i> {{ $kalimat }}
    </span>
</span>
