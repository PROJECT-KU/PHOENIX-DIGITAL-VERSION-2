@props([
    'data',
    'label' => 'periode lalu',
    'rentang' => null,
    'biaya' => false,
    // Satuan pembandingnya. Bawaannya rupiah karena hampir semua kartu di
    // dasbor berisi uang, tetapi ada juga yang berisi CACAH (jumlah pesanan,
    // jumlah pelanggan) — dan "Rp 12" untuk dua belas pesanan salah baca.
    'prefiks' => 'Rp ',
    'sufiks' => '',
    'desimal' => 0,
])

{{-- Perbandingan PERIODE INI vs PERIODE LALU — lihat App\Support\PerbandinganPeriode.

     'biaya' membalik ARTI warnanya. Pada pemasukan dan saldo, naik itu kabar
     baik (hijau). Pada pengeluaran, naik justru yang perlu diperhatikan; kalau
     ia ikut hijau, satu-satunya kartu yang memberi peringatan malah terbaca
     seperti pencapaian. --}}
@php
    $persen = $data['persen'] ?? null;
    $arah = $data['arah'] ?? 0;
    $sebelumnya = (float) ($data['sebelumnya'] ?? 0);

    if ($arah > 0) {
        $ikon = 'bi-arrow-up-right';
        $rupa = $biaya ? 'is-buruk' : 'is-baik';
        // Persen null = pembandingnya nol atau minus, jadi tidak bisa
        // dipersenkan; yang tampil kata, bukan angka karangan.
        $teks = $persen === null ? 'Naik' : number_format(abs($persen), 1, ',', '.') . '%';
    } elseif ($arah < 0) {
        $ikon = 'bi-arrow-down-right';
        $rupa = $biaya ? 'is-baik' : 'is-buruk';
        $teks = $persen === null ? 'Turun' : number_format(abs($persen), 1, ',', '.') . '%';
    } else {
        [$ikon, $rupa, $teks] = ['bi-dash', 'is-datar', 'Tetap'];
    }
@endphp

<x-banding-gaya />

{{-- Atribut biasa, BUKAN @if ... @endif di dalam tag: tanda lebih-besar tepat
     sesudah @endif membuat Livewire melewati penanda morph-nya. --}}
<span class="bnd" title="{{ $rentang ? 'Periode sebelumnya: '.$rentang : '' }}">
    <span class="bnd-pil {{ $rupa }}"><i class="bi {{ $ikon }}"></i>{{ $teks }}</span>
    <span class="bnd-ket">vs <b>{{ $prefiks }}{{ number_format($sebelumnya, $desimal, ',', '.') }}{{ $sufiks }}</b> {{ $label }}</span>
</span>
