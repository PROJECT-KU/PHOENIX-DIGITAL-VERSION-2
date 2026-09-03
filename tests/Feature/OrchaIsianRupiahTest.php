<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Seluruh isian uang di layar Orcha memakai .orcha-rupiah.
 *
 * Kelas itu menaruh "Rp" DI DALAM kotaknya. Kotak "Rp" terpisah bergaya
 * Bootstrap (input-group-text) menghasilkan bentuk yang berbeda: tinggi
 * kotaknya lain, sudutnya lain, dan satu layar yang memakainya terlihat
 * seperti dibuat orang lain.
 *
 * Aturannya sudah ditetapkan dan bahkan dicatat di partial gaya — tetapi
 * catatan tidak bisa menahan siapa pun. Yang menahannya uji ini.
 */
class OrchaIsianRupiahTest extends TestCase
{
    public function test_tidak_ada_layar_orcha_yang_memakai_kotak_rp_terpisah(): void
    {
        $berkas = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
        );

        $pelanggar = [];

        foreach ($berkas as $satu) {
            // Salinan kembar buatan OneDrive tidak pernah dirender Blade.
            if (preg_match('/ \d+\.php$/', $satu)) {
                continue;
            }

            $isi = file_get_contents($satu);

            if (preg_match('/input-group-text[^>]*>\s*Rp\s*</', $isi)) {
                $pelanggar[] = basename(dirname($satu)).'/'.basename($satu);
            }
        }

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Isian uang memakai kotak "Rp" terpisah, bukan .orcha-rupiah:'],
            $pelanggar,
            ['', 'Bungkus isiannya dengan <div class="orcha-rupiah"> dan buang input-group-nya.'],
        )));
    }

    public function test_isian_rupiah_memformat_lewat_blur_bukan_tiap_ketukan(): void
    {
        /*
         | Memformat tiap ketukan membuat kursor melompat ke ujung setiap kali
         | pemisah ribuan bertambah — dan yang membetulkan satu digit di tengah
         | angka harus mengetik ulang seluruhnya.
         |
         | Diperiksa di berkas, bukan lewat perilaku: yang menentukan
         | wire:model.blur, dan .live yang menyelinap masuk tidak menghasilkan
         | galat apa pun — cuma isian yang menyebalkan dipakai.
         */
        $berkas = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
        );

        $pelanggar = [];

        foreach ($berkas as $satu) {
            if (preg_match('/ \d+\.php$/', $satu)) {
                continue;
            }

            $isi = file_get_contents($satu);

            // Isian di dalam pembungkus .orcha-rupiah, sampai tag penutupnya.
            preg_match_all('/orcha-rupiah[^"]*"[^>]*>\s*(<input[^>]*>)/s', $isi, $cocok);

            foreach ($cocok[1] as $tag) {
                if (str_contains($tag, 'wire:model.live')) {
                    $pelanggar[] = basename(dirname($satu)).'/'.basename($satu);
                }
            }
        }

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Isian rupiah memformat tiap ketukan — kursornya akan melompat:'],
            $pelanggar,
            ['', 'Pakai wire:model.blur.'],
        )));
    }
}
