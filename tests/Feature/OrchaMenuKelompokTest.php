<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menu Orcha di bilah samping, setelah dikelompokkan.
 *
 * Mengelompokkan daftar berarti memindahkan lima belas baris ke dalam empat
 * larik bersarang — suntingan yang paling gampang menelan satu baris tanpa
 * jejak. Halamannya tetap terbuka, rutenya tetap hidup, hanya pintunya yang
 * hilang dari bilah; tidak ada galat apa pun yang menandainya, dan yang
 * menyadarinya adalah admin yang mencari menunya dan tidak menemukannya.
 */
class OrchaMenuKelompokTest extends TestCase
{
    private function sumber(): string
    {
        return file_get_contents(resource_path('views/livewire/layout/sidebar.blade.php'));
    }

    /** @return array<int, string> */
    private function ruteMenu(): array
    {
        $isi = $this->sumber();

        $awal = strpos($isi, '$menuOrcha = [');
        $akhir = strpos($isi, '@endphp', $awal);
        $blok = substr($isi, $awal, $akhir - $awal);

        preg_match_all("/'(admin\.orcha\.[a-z0-9_.-]+)'/", $blok, $cocok);

        return $cocok[1];
    }

    public function test_semua_menu_orcha_masih_ada_dan_tidak_terduplikasi(): void
    {
        $rute = $this->ruteMenu();

        // Angkanya ditulis apa adanya: kalau sebuah menu memang sengaja
        // ditambah atau dibuang, angkanya ikut disunting — dan suntingan itu
        // yang membuat perubahannya terlihat di ulasan, bukan lewat begitu saja.
        $this->assertCount(20, $rute, 'Jumlah menu Orcha berubah: '.implode(', ', $rute));
        $this->assertSame(array_unique($rute), $rute, 'Ada menu Orcha yang tercantum dua kali.');
    }

    /** Menu yang menunjuk rute tak dikenal akan meledak saat bilahnya digambar. */
    public function test_setiap_menu_menunjuk_rute_yang_benar_benar_ada(): void
    {
        foreach ($this->ruteMenu() as $satu) {
            $this->assertTrue(Route::has($satu), "Menu bilah samping menunjuk rute '$satu' yang tidak ada.");
        }
    }

    /**
     * Judul kelompoknya harus benar-benar tergambar.
     *
     * Tanpa ini larik boleh saja berkunci 'Pemesanan' sementara perulangannya
     * lupa mencetak kuncinya — dan hasilnya kembali jadi lima belas baris rata
     * seperti sebelum dikelompokkan.
     */
    public function test_judul_kelompok_ikut_tergambar(): void
    {
        $isi = $this->sumber();

        $this->assertStringContainsString(
            'sidebar-title">{{ $judulGrup }}',
            $isi,
            'Judul kelompok tidak pernah dicetak, jadi menunya tetap terlihat rata.'
        );

        foreach (['Menu', 'Pemesanan', 'Layanan & Armada', 'Isi Situs'] as $judul) {
            $this->assertStringContainsString("'$judul' => [", $isi, "Kelompok '$judul' hilang.");
        }
    }
}
