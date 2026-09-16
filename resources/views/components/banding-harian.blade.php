@props(['data'])

{{--
    Perbandingan HARI INI vs KEMARIN.

    Dipakai bersama Dashboard & Cash Flow supaya kalimatnya seragam, dan
    diletakkan menempel pada angka yang dibandingkan.

    'persen' bernilai null saat kemarin nol — ditampilkan sebagai kata, bukan
    "naik 100%", karena naik dari nol tidak bisa dipersenkan.
--}}
@php
    $persen = $data['persen'] ?? null;
    $arah = $data['arah'] ?? 0;
    $kemarin = (float) ($data['kemarin'] ?? 0);

    if ($persen === null) {
        [$ikon, $rupa, $teks] = ($data['hari_ini'] ?? 0) > 0
            ? ['bi-arrow-up-right', 'is-baik', 'Naik dari nol']
            : ['bi-dash', 'is-datar', 'Belum ada'];
    } elseif ($arah > 0) {
        [$ikon, $rupa, $teks] = ['bi-arrow-up-right', 'is-baik', number_format(abs($persen), 1, ',', '.') . '%'];
    } elseif ($arah < 0) {
        [$ikon, $rupa, $teks] = ['bi-arrow-down-right', 'is-buruk', number_format(abs($persen), 1, ',', '.') . '%'];
    } else {
        [$ikon, $rupa, $teks] = ['bi-dash', 'is-datar', 'Tetap'];
    }
@endphp

<x-banding-gaya />

<span class="bnd">
    <span class="bnd-pil {{ $rupa }}"><i class="bi {{ $ikon }}"></i>{{ $teks }}</span>
    <span class="bnd-ket">vs <b>Rp {{ number_format($kemarin, 0, ',', '.') }}</b> kemarin</span>
</span>
