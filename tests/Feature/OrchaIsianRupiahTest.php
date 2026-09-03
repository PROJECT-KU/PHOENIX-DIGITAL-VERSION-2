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

    public function test_perapian_saat_diketik_dipasang_di_partial_bersama(): void
    {
        /*
         | Perapian saat diketik dikerjakan di peramban, satu pendengar di
         | document, dipasang lewat partial gaya yang disertakan seluruh layar
         | Orcha.
         |
         | Yang diperiksa di sini PEMASANGANNYA, bukan perilakunya — perilaku
         | JavaScript tidak bisa dijalankan PHPUnit. Algoritmanya sendiri
         | dibuktikan dengan menjalankannya di peramban sungguhan: mengetik
         | berurutan, menyisipkan digit di tengah sambil memeriksa posisi
         | kursornya, mengetik huruf, mengosongkan, dan melewati batas digit.
         |
         | Yang bisa lepas tanpa suara justru pemasangannya: pemilihnya diubah,
         | atau skripnya terhapus saat partialnya disunting, dan isiannya
         | kembali menampilkan deretan angka tanpa satu pun galat.
         */
        $gaya = file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php')
        );

        $this->assertStringContainsString('.orcha-rupiah input', $gaya,
            'Pendengar perapian rupiah tidak lagi menyasar isian di dalam .orcha-rupiah.');

        // Menempel di document, bukan di elemennya: Livewire menggambar ulang
        // kotaknya kapan saja, dan pendengar yang menempel pada elemen ikut
        // hilang bersamanya.
        $this->assertStringContainsString("document.addEventListener('input'", $gaya,
            'Pendengarnya tidak lagi menempel di document — akan hilang saat Livewire menggambar ulang.');

        // Dipasang sekali walau partialnya disertakan berkali-kali: pendengar
        // ganda memformat dua kali dan melempar kursornya dua kali pula.
        $this->assertStringContainsString('__orchaRupiahTerpasang', $gaya,
            'Penjaga pemasangan ganda hilang.');

        // setSelectionRange itu yang menjaga kursornya. Tanpa itu, kursor
        // melompat ke ujung tiap kali titik pemisah bertambah.
        $this->assertStringContainsString('setSelectionRange', $gaya,
            'Penjagaan posisi kursor hilang — kursor akan melompat ke ujung saat mengetik.');
    }

    public function test_skrip_bersama_tidak_memuat_kurung_sudut_pembuka(): void
    {
        /*
         | Bug nyata, dan gagalnya di tempat yang sama sekali lain.
         |
         | strip_tags membaca kurung sudut pembuka sebagai awal sebuah tag lalu
         | menelan segalanya sampai penutupnya — termasuk isi halaman yang sah.
         | Livewire memakai strip_tags di assertSee, sehingga satu operator
         | perbandingan di dalam skrip ini membuat uji layar LAIN gagal
         | menemukan angka yang jelas-jelas tergambar.
         |
         | Ditemukan begitu: uji dasbor mendadak merah, dan sebabnya skrip di
         | partial gaya yang tidak ada hubungannya dengan dasbor. Yang mencari
         | sebabnya nanti akan mulai dari dasbor, dan tidak akan menemukan apa
         | pun di sana.
         |
         | Perbandingannya dibalik — "a.length > b" alih-alih sebaliknya —
         | supaya bahayanya hilang sama sekali, bukan sekadar dihindari sekali.
         */
        $gaya = file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php')
        );

        $mulai = strrpos($gaya, '<script>') + strlen('<script>');
        $badan = substr($gaya, $mulai, strrpos($gaya, '</script>') - $mulai);

        $this->assertStringNotContainsString('<', $badan, implode("\n", [
            'Skrip di partial gaya memuat kurung sudut pembuka.',
            '',
            'strip_tags akan menelan isi halaman sesudahnya, dan uji layar lain',
            'gagal menemukan teks yang jelas-jelas tergambar — tanpa satu pun',
            'petunjuk yang mengarah ke berkas ini.',
            '',
            'Balik perbandingannya: tulis "a.length > posisi", bukan sebaliknya.',
        ]));
    }

    public function test_isian_rupiah_memformat_lewat_blur_bukan_tiap_ketukan(): void
    {
        /*
         | Perapian tampilannya dikerjakan peramban; yang ke server tetap
         | menunggu isiannya ditinggalkan.
         |
         | wire:model.live di sini berarti bolak-balik ke server tiap ketukan.
         | Pada sambungan yang lambat isiannya tersendat, dan setiap balasan
         | menggambar ulang kotaknya — kursor melompat ke ujung tepat saat
         | orangnya sedang mengetik di tengah, persis hal yang penjagaan kursor
         | di peramban dibuat untuk mencegahnya.
         |
         | Diperiksa di berkas, bukan lewat perilaku: .live yang menyelinap
         | masuk tidak menghasilkan galat apa pun — cuma isian yang menyebalkan
         | dipakai.
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
