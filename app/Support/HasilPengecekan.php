<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderUpload;

/**
 * Menyimpulkan berkas hasil APA SAJA yang seharusnya diterima pelanggan,
 * lalu mana yang belum terunggah.
 *
 * Selama ini penyimpanan hasil hanya menuntut "minimal satu berkas", sehingga
 * menyimpan 2 dari 3 berhasil tanpa peringatan apa pun. Admin mengira sudah
 * mengirim tiga berkas, pelanggan hanya menerima dua, dan tak ada satu pun
 * tanda yang memberi tahu — kejadian pada pesanan parafrase 5 Sep 2026, yang
 * baru ketahuan tiga hari kemudian karena pelanggan tidak melihat hasil AI-nya.
 *
 * Yang dipakai sebagai acuan adalah ADD-ON yang benar-benar dibeli, bukan
 * tebakan: pesanan yang tidak membeli cek AI tidak boleh dituduh kurang.
 */
class HasilPengecekan
{
    /**
     * Label berkas hasil yang seharusnya ada tapi belum terunggah.
     *
     * @return array<int, string>
     */
    public static function yangKurang(Order $order, OrderUpload $up): array
    {
        $kurang = [];

        // Dokumen hasil parafrase: inti pekerjaannya, selalu wajib.
        if ($up->jenis === 'parafrase' && ! $up->hasil_docx_path) {
            $kurang[] = 'Dokumen Hasil (Parafrase)';
        }

        $addon = self::namaAddon($order);

        $mintaPlagiasi = $up->jenis === 'plagiasi'
            || self::mengandung($addon, ['plagiasi', 'turnitin']);

        if ($mintaPlagiasi && ! $up->hasil_path) {
            $kurang[] = 'Hasil Cek Plagiasi';
        }

        $mintaAi = $up->jenis === 'ai' || self::mengandung($addon, ['ai']);

        if ($mintaAi && ! $up->hasil_ai_path) {
            $kurang[] = 'Hasil Cek AI';
        }

        return $kurang;
    }

    /**
     * Nama seluruh add-on yang dibeli pada pesanan ini, huruf kecil.
     *
     * @return array<int, string>
     */
    private static function namaAddon(Order $order): array
    {
        $nama = [];

        foreach ($order->items as $item) {
            foreach ((array) ($item->addons ?? []) as $addon) {
                $label = is_array($addon) ? ($addon['nama'] ?? '') : (string) $addon;

                if ($label !== '') {
                    $nama[] = mb_strtolower($label);
                }
            }
        }

        return $nama;
    }

    /**
     * Apakah salah satu nama add-on memuat kata kunci ini?
     *
     * Dicocokkan sebagai KATA UTUH agar "Cek Plagiasi AI" tidak tertukar
     * dengan kata lain yang kebetulan memuat huruf a-i berurutan.
     *
     * @param  array<int, string>  $nama
     * @param  array<int, string>  $kunci
     */
    private static function mengandung(array $nama, array $kunci): bool
    {
        foreach ($nama as $satu) {
            foreach ($kunci as $k) {
                if (preg_match('/\b'.preg_quote($k, '/').'\b/u', $satu)) {
                    return true;
                }
            }
        }

        return false;
    }
}
