<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Formulir promo harus membedakan JUMLAH PESERTA dari JUMLAH REKAN.
 *
 * Yang dibandingkan Orcha jumlah peserta satu pendaftaran — pemesannya ikut
 * terhitung. Yang diucapkan orang jumlah rekan: "ajak 5 dapat diskon" berarti
 * enam orang berangkat.
 *
 * Selisihnya satu, dan justru karena cuma satu ia lolos dari pemeriksaan mata.
 * Admin yang mengetik 5 sambil membayangkan "ajak 5 rekan" memberi potongan
 * satu tingkat lebih murah kepada setiap rombongan berlima, dan tidak ada yang
 * menyadarinya sampai laporan keuntungan turun.
 */
class OrchaPromoRekanTest extends TestCase
{
    private function berkas(): string
    {
        return file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/promo/index.blade.php')
        );
    }

    public function test_pratinjau_menyebut_rekan_bukan_peserta(): void
    {
        $isi = $this->berkas();

        // Pratinjaunya harus merakit kalimat dari $rekan. Memakai $min di sana
        // membuat tingkat 6 tertulis "Ajak 6 rekan" — satu rekan lebih banyak
        // daripada yang benar-benar disyaratkan.
        $this->assertStringContainsString('Ajak {{ $rekan }} rekan', $isi,
            'Pratinjau promo tidak merakit kalimatnya dari jumlah rekan.');

        $this->assertStringNotContainsString('Ajak {{ $min }} orang', $isi,
            'Pratinjau promo masih menyebut jumlah peserta sebagai jumlah yang diajak.');
    }

    public function test_isian_menerangkan_bahwa_pemesan_ikut_terhitung(): void
    {
        $isi = $this->berkas();

        $this->assertStringContainsString('pemesan mengajak {{ $rekan }} rekan', $isi,
            'Isian minimal peserta tidak menerjemahkan angkanya jadi jumlah rekan.');
    }

    public function test_jumlah_rekan_tidak_pernah_nol(): void
    {
        $isi = $this->berkas();

        // min_peserta terkecil yang diizinkan 2. Tanpa max(1, ...), suatu saat
        // batasnya dilonggarkan dan kalimatnya berbunyi "ajak 0 rekan".
        $this->assertStringContainsString('$rekan = max(1, $min - 1);', $isi,
            'Jumlah rekan tidak dijaga supaya minimal satu.');
    }
}
