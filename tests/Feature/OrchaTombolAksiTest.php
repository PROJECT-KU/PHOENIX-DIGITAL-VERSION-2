<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tombol aksi di tabel halaman Orcha harus memakai VARIAN, bukan .orcha-aksi
 * polos.
 *
 * .orcha-aksi sendiri hanya mengatur bentuk: latar dan batasnya bening.
 * Warnanya datang dari varian — orcha-aksi-wa, orcha-aksi-hapus,
 * orcha-aksi-ubah, dan seterusnya.
 *
 * Tanpa varian, tombolnya tergambar sebagai ikon telanjang tanpa kotak. Yang
 * membuatnya buruk bukan polosnya sendiri melainkan tetangganya: satu tombol
 * berkotak di sebelah satu tombol tanpa kotak terbaca seperti salah satunya
 * rusak.
 */
class OrchaTombolAksiTest extends TestCase
{
    public function test_setiap_tombol_aksi_memakai_varian_warnanya(): void
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

            preg_match_all('/class="([^"]*\borcha-aksi\b[^"]*)"/', file_get_contents($satu), $cocok);

            foreach ($cocok[1] as $kelas) {
                if (! preg_match('/orcha-aksi-[a-z]/', $kelas)) {
                    $pelanggar[] = basename($satu).' → class="'.trim($kelas).'"';
                }
            }
        }

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Tombol aksi tanpa varian warna — tergambar sebagai ikon telanjang:'],
            $pelanggar,
            ['', 'Pakai orcha-aksi-wa / orcha-aksi-hapus / orcha-aksi-ubah / orcha-aksi-lihat.'],
        )));
    }
}
