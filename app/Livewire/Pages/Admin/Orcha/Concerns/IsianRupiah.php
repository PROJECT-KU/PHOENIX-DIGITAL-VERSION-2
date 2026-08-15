<?php

namespace App\Livewire\Pages\Admin\Orcha\Concerns;

/**
 * Isian uang yang tampil bertitik: 1.430.000, bukan 1430000.
 *
 * Formatnya dikerjakan di server saat isian ditinggalkan (wire:model.blur),
 * jadi tidak perlu pustaka masking di browser dan hasilnya sama persis dengan
 * yang tersimpan. Angka mentahnya tetap dipegang properti terpisah — itu yang
 * dikirim ke Orcha, supaya titik pemisah tidak pernah ikut terkirim.
 */
trait IsianRupiah
{
    /**
     * Ambil angka dari ketikan apa pun: "Rp 1.430.000", "1430000", "1,430,000".
     */
    protected function angkaDari(?string $teks): int
    {
        return (int) preg_replace('/\D/', '', (string) $teks);
    }

    /** 1430000 → "1.430.000". Nol ditampilkan kosong supaya tidak mengganggu. */
    protected function keRupiah($angka): string
    {
        $angka = (int) $angka;

        return $angka > 0 ? number_format($angka, 0, ',', '.') : '';
    }
}
