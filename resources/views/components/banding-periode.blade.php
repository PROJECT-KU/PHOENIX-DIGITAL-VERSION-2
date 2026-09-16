@props([
    'data',
    'label' => 'Periode lalu',
    'rentang' => null,
    'biaya' => false,
])

{{-- Teks perbandingan PERIODE INI vs PERIODE LALU.

     Sejajar dengan <x-banding-harian> yang dipakai kartu harian, tetapi
     pembandingnya periode — lihat App\Support\PerbandinganPeriode.

     'biaya' membalik ARTI warnanya. Pada pemasukan dan saldo, naik itu kabar
     baik (hijau). Pada pengeluaran, naik justru yang perlu diperhatikan; kalau
     ia ikut hijau, satu-satunya kartu yang memberi peringatan malah terbaca
     seperti pencapaian. --}}
@php
    $persen = $data['persen'] ?? null;
    $arah = $data['arah'] ?? 0;
    $sebelumnya = (float) ($data['sebelumnya'] ?? 0);

    // Warna mengikuti BAIK/BURUK, bukan naik/turun.
    $baik = $biaya ? '#b91c1c' : '#047857';   // dipakai saat arah naik
    $buruk = $biaya ? '#047857' : '#b91c1c';  // dipakai saat arah turun

    if ($arah > 0) {
        [$ikon, $warna] = ['bi-arrow-up-right', $baik];
        $kalimat = $persen === null ? 'naik' : 'naik ' . number_format(abs($persen), 1, ',', '.') . '%';
    } elseif ($arah < 0) {
        [$ikon, $warna] = ['bi-arrow-down-right', $buruk];
        $kalimat = $persen === null ? 'turun' : 'turun ' . number_format(abs($persen), 1, ',', '.') . '%';
    } else {
        [$ikon, $warna, $kalimat] = ['bi-dash', '#64748b', 'tidak berubah'];
    }
@endphp

{{-- Rentang tanggalnya dititipkan ke tooltip, bukan ditulis penuh: label
     "Periode 21 Jul – 20 Agt 2026 Rp 18.400.000 naik 20,3%" pecah dua baris di
     kartu selebar sepertiga layar, dan yang dicari mata hanyalah angka
     pembanding beserta arahnya. --}}
{{-- Atribut biasa, BUKAN @if ... @endif di dalam tag: tanda lebih-besar tepat
     sesudah @endif membuat Livewire melewati penanda morph-nya dan pembaruan
     halaman berhenti bekerja. --}}
<span class="d-block mt-1 text-muted" style="font-size: 0.75rem; line-height: 1.5;"
    title="{{ $rentang ? 'Periode sebelumnya: '.$rentang : '' }}">
    {{ $label }} <b>Rp {{ number_format($sebelumnya, 0, ',', '.') }}</b>
    <span style="color: {{ $warna }}; font-weight: 700; white-space: nowrap;">
        <i class="bi {{ $ikon }}" style="vertical-align:-0.125em;"></i> {{ $kalimat }}
    </span>
</span>
