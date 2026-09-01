<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Galat dari Orcha harus BENAR-BENAR tertangkap halaman yang memanggilnya.
 *
 * Dua halaman sempat menangkap App\Services\OrchaTidakTerjangkau — kelas yang
 * tidak ada. PHP tidak mengeluh soal itu: catch pada kelas yang tak dikenal
 * hanya tidak pernah cocok. Akibatnya penolakan isian yang seharusnya muncul
 * sebagai pesan di formulir justru lolos jadi halaman galat penuh, lengkap
 * dengan jejak tumpukan dan potongan kode — yang dilihat admin, bukan
 * pengembang.
 *
 * Gejalanya tidak muncul sampai Orcha benar-benar menolak sesuatu, jadi
 * halaman itu bisa berbulan-bulan terlihat baik-baik saja.
 */
class OrchaGalatTertangkapTest extends TestCase
{
    /** @return array<int, string> */
    private function berkasOrcha(): array
    {
        $semua = array_merge(
            glob(app_path('Livewire/Pages/Admin/Orcha/*.php')) ?: [],
            glob(app_path('Livewire/Pages/Admin/Orcha/*/*.php')) ?: [],
        );

        // Salinan kembar buatan OneDrive tidak pernah dimuat autoloader.
        return array_values(array_filter($semua, fn ($f) => ! preg_match('/ \d+\.php$/', $f)));
    }

    public function test_tidak_ada_yang_menangkap_kelas_galat_yang_tidak_ada(): void
    {
        $pelanggar = [];

        foreach ($this->berkasOrcha() as $berkas) {
            $isi = file_get_contents($berkas);

            preg_match_all('/use\s+(App\\\\[A-Za-z\\\\]*OrchaTidakTerjangkau);/', $isi, $cocok);

            foreach ($cocok[1] as $kelas) {
                if (! class_exists($kelas)) {
                    $pelanggar[] = basename($berkas).' → '.$kelas;
                }
            }
        }

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Menangkap kelas galat yang tidak ada — catch-nya tidak akan pernah cocok:'],
            $pelanggar,
            ['', 'Yang benar: App\Exceptions\OrchaTidakTerjangkau'],
        )));
    }

    /**
     * Kelas yang benar memang ada, dan namanya persis itu.
     *
     * Tanpa ini, uji di atas ikut hijau kalau seluruh berkas kebetulan
     * berhenti mengimpornya sama sekali.
     */
    public function test_kelas_galat_orcha_ada_di_tempat_yang_diharapkan(): void
    {
        $this->assertTrue(class_exists(\App\Exceptions\OrchaTidakTerjangkau::class));
        $this->assertFalse(class_exists(\App\Services\OrchaTidakTerjangkau::class));
    }
}
