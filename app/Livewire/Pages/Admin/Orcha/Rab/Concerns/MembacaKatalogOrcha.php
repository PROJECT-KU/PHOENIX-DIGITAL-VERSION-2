<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab\Concerns;

use App\Exceptions\OrchaTidakTerjangkau;

/**
 * Katalog destinasi, vendor, dan kosakata RAB — disimpan sebentar.
 *
 * Dipakai daftar master harga maupun borangnya. Ditarik ke sini saat
 * borangnya pindah ke halaman tersendiri: dua salinan yang membaca jalur
 * yang sama tetapi menyimpannya dengan kunci berbeda akan membuat vendor
 * yang baru dibuat muncul di satu layar dan belum muncul di layar lain.
 *
 * Gagal memuatnya memulangkan larik kosong, bukan melempar: katalog yang
 * belum sampai membuat kotak pilih kosong, sedangkan galat membuat seluruh
 * halamannya mati.
 */
trait MembacaKatalogOrcha
{
    /** Provinsi & destinasi untuk pilihan; disimpan sebentar karena jarang berubah. */
    protected function katalog(): array
    {
        $simpan = cache()->get('orcha.rab.katalog');

        if (is_array($simpan) && $simpan !== []) {
            return $simpan;
        }

        try {
            $data = $this->orcha()->ambil('/rab/katalog')['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return [];
        }

        if ($data !== []) {
            cache()->put('orcha.rab.katalog', $data, now()->addMinutes(10));
        }

        return $data;
    }
}
