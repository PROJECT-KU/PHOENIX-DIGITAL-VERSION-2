<?php

namespace Tests\Feature;

use App\Support\HitunganOrcha;
use App\Support\OrchaDaftarTungguPerhatian;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Penanda Daftar Tunggu di bilah samping.
 *
 * Yang dihitung HANYA yang menunggu tanpa surel — bagian daftar yang sistem
 * tidak bisa kabari sama sekali. Antrean yang panjang wajar dan tidak menuntut
 * apa pun; penanda yang menghitung seluruhnya menyala terus tanpa pernah bisa
 * dinolkan, dan penanda yang tidak pernah padam berhenti dibaca orang.
 */
class OrchaPenandaDaftarTungguTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('orcha.url', 'https://orcha.test/api/v1');
        config()->set('orcha.kunci', 'kunci-uji');

        OrchaDaftarTungguPerhatian::lupakan();
    }

    public function test_yang_dihitung_yang_perlu_dihubungi(): void
    {
        /*
         | Bukan seluruh antrean, dan bukan pula seluruh yang tanpa surel:
         | hanya yang kursinya SUDAH terbuka, tanpa surel, dan belum
         | dihubungi siapa pun.
         */
        Http::fake(['*' => Http::response(['data' => [
            'perlu_dihubungi' => 3, 'menunggu' => 12, 'dikabari' => 40,
        ]])]);

        $this->assertSame(3, OrchaDaftarTungguPerhatian::jumlah());
    }

    public function test_orcha_yang_mati_tidak_merobohkan_bilah_samping(): void
    {
        /*
         | Penanda ini dipanggil di TIAP halaman admin lemon. Orcha yang sedang
         | tidak bisa dihubungi tidak boleh ikut mematikan seluruh lemon — yang
         | hilang cukup angkanya.
         */
        Http::fake(['*' => Http::response([], 500)]);

        $this->assertSame(0, OrchaDaftarTungguPerhatian::jumlah());
        $this->assertSame(
            ['perlu_dihubungi' => 0, 'menunggu' => 0, 'dikabari' => 0],
            OrchaDaftarTungguPerhatian::ambil(),
        );
    }

    public function test_jawaban_yang_kurang_kuncinya_tetap_berbentuk_utuh(): void
    {
        // Bentuk jawabannya harus sama dengan bentuk bawaan supaya pemakainya
        // tidak perlu memeriksa dua kemungkinan.
        Http::fake(['*' => Http::response(['data' => ['perlu_dihubungi' => 2]])]);

        $this->assertSame(
            ['perlu_dihubungi' => 2, 'menunggu' => 0, 'dikabari' => 0],
            OrchaDaftarTungguPerhatian::ambil(),
        );
    }

    public function test_angkanya_disimpan_sebentar_bukan_ditembak_tiap_halaman(): void
    {
        Http::fake(['*' => Http::response(['data' => ['perlu_dihubungi' => 3]])]);

        OrchaDaftarTungguPerhatian::ambil();
        OrchaDaftarTungguPerhatian::ambil();

        Http::assertSentCount(1);
    }

    public function test_penandanya_ikut_dilupakan_bersama_yang_lain(): void
    {
        /*
         | Satu perubahan bisa menggeser lebih dari satu hitungan, dan menebak
         | mana saja yang terpengaruh di tiap tempat pemanggilan adalah cara
         | paling pasti untuk melewatkan salah satunya.
         |
         | Penanda baru yang lupa didaftarkan di sini tidak menghasilkan galat
         | apa pun — angkanya cuma tertinggal semenit, dan admin mengira
         | tekanannya tidak tersimpan lalu mengulanginya.
         */
        $this->assertContains(OrchaDaftarTungguPerhatian::class, HitunganOrcha::semua());
    }

    public function test_bilah_samping_menandai_menu_daftar_tunggu(): void
    {
        $bilah = file_get_contents(
            resource_path('views/livewire/layout/sidebar.blade.php')
        );

        $this->assertStringContainsString("'admin.orcha.daftar-tunggu' => [", $bilah,
            'Menu Daftar Tunggu tidak terdaftar di penanda bilah samping.');

        // Judul tempelnya menyebut seluruh antrean, supaya angka kecil di menu
        // tidak dibaca sebagai "cuma segini yang menunggu".
        $this->assertStringContainsString('menunggu kursi', $bilah);
    }

    public function test_tombol_whatsapp_sekaligus_menandai_sudah_dihubungi(): void
    {
        /*
         | Tanpa ini, satu-satunya cara menurunkan penandanya adalah
         | mengeluarkan orangnya dari antrean — padahal yang menjawab "nanti
         | saya kabari lagi" memang belum boleh dikeluarkan.
         |
         | x-on:click, BUKAN wire:click: Livewire menahan perilaku bawaan
         | tautan sehingga WhatsApp-nya tidak jadi terbuka. Diperiksa di
         | berkas, karena keduanya sama-sama tidak menghasilkan galat — yang
         | satu cuma diam-diam tidak membuka apa pun.
         */
        $layar = file_get_contents(resource_path(
            'views/livewire/pages/admin/orcha/daftar-tunggu/index.blade.php'
        ));

        $this->assertStringContainsString('x-on:click="$wire.tandaiDihubungi(', $layar,
            'Tombol WhatsApp tidak lagi menandai bahwa orangnya sudah dihubungi.');

        $this->assertStringNotContainsString('wire:click="tandaiDihubungi', $layar,
            'Pakai x-on:click — wire:click menahan tautannya sehingga WhatsApp tidak terbuka.');
    }

    public function test_layar_tidak_menyebut_dikabari_untuk_yang_tanpa_email(): void
    {
        /*
         | Bug yang ditemukan lewat satu pertanyaan: "kalau mengabari lewat WA
         | bagaimana sistem sudah tahu?".
         |
         | dikabari_pada tidak berarti "sudah dikabari" — untuk yang tanpa
         | email, penandanya dipasang lalu tidak ada apa pun yang dikirim.
         | Memeriksanya lebih dulu membuat orang yang tidak bisa dijangkau
         | siapa pun tergambar hijau "Dikabari 2 jam lalu": layar yang
         | mengatakan kebalikan dari kenyataan, tepat untuk orang yang paling
         | membutuhkan admin.
         */
        $layar = file_get_contents(resource_path(
            'views/livewire/pages/admin/orcha/daftar-tunggu/index.blade.php'
        ));

        $this->assertStringContainsString(
            "\$baris['dikabari_pada'] && blank(\$baris['email'])",
            $layar,
            'Keadaan "kursi terbuka tetapi tanpa email" tidak lagi dibedakan — '
            .'orang yang tidak bisa dijangkau akan tergambar sebagai sudah dikabari.',
        );

        $this->assertStringContainsString('Kursi terbuka — belum bisa dikabari', $layar);
    }

    public function test_jumlah_antrean_berupa_lencana_bukan_teks_samar(): void
    {
        /*
         | Sebagai teks abu-abu kecil di ujung kanan, angkanya terbaca sebagai
         | keterangan yang boleh dilewati — padahal inilah yang menjawab
         | pertanyaan pertama admin saat membuka layar ini: seberapa besar
         | permintaan yang tertahan, dan apakah antreannya sudah cukup panjang
         | untuk membuka keberangkatan tambahan.
         */
        $layar = file_get_contents(resource_path(
            'views/livewire/pages/admin/orcha/daftar-tunggu/index.blade.php'
        ));

        $this->assertStringContainsString('menunggu kursi', $layar);

        $this->assertStringNotContainsString('<span class="text-muted small ms-lg-auto">', $layar,
            'Jumlah antrean kembali jadi teks samar; pakai lencana supaya terbaca.');
    }

    public function test_layar_menyebut_angka_yang_sama_dengan_penanda_menu(): void
    {
        /*
         | Layar sempat cuma menyebut "1 menunggu kursi" sementara penanda di
         | menu kosong — dan yang melihatnya menyimpulkan keduanya tidak
         | sinkron.
         |
         | Keduanya memang menjawab pertanyaan berbeda: yang satu seberapa besar
         | antreannya, yang satu berapa yang menunggu ditelepon. Tetapi layar
         | yang tampak bertentangan dengan dirinya sendiri membuat orang
         | berhenti mempercayai kedua angkanya sekaligus — dan itu lebih mahal
         | daripada salah satu angkanya keliru.
         |
         | Diperbaiki dengan menaruh keduanya berdampingan, bukan dengan
         | menyamakan artinya.
         */
        $layar = file_get_contents(resource_path(
            'views/livewire/pages/admin/orcha/daftar-tunggu/index.blade.php'
        ));

        $this->assertStringContainsString("\$meta['perlu_dihubungi']", $layar,
            'Layar tidak menyebut angka yang dipakai penanda di menu — '
            .'admin akan mengira keduanya tidak sinkron.');

        $this->assertStringContainsString('perlu dihubungi', $layar);
    }

    public function test_kotak_cari_memakai_partial_bersama(): void
    {
        /*
         | Layar ini sempat memasang ikon kaca pembesarnya sendiri —
         | position-absolute tanpa memberi padding kiri pada isiannya —
         | sehingga ikonnya menindih tulisan petunjuknya.
         |
         | Markup sendiri untuk hal yang sudah punya partial adalah cara paling
         | pasti untuk berbeda dari layar lain, dan bedanya baru terlihat
         | setelah ada yang membuka keduanya berurutan.
         */
        $layar = file_get_contents(resource_path(
            'views/livewire/pages/admin/orcha/daftar-tunggu/index.blade.php'
        ));

        $this->assertStringContainsString('partials.cari', $layar,
            'Kotak cari tidak memakai partial bersama.');

        $this->assertStringNotContainsString('bi-search', $layar,
            'Ikon cari dipasang sendiri; ia akan menindih tulisan petunjuknya.');
    }

    public function test_tidak_ada_layar_orcha_yang_memasang_kotak_carinya_sendiri(): void
    {
        // Sapuan, bukan pemeriksaan satu layar: yang sekali terjadi biasanya
        // terjadi lagi di layar berikutnya yang dibuat terburu-buru.
        $pelanggar = [];

        $berkas = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
        );

        foreach ($berkas as $satu) {
            if (preg_match('/ \d+\.blade\.php$/', $satu) || str_ends_with($satu, 'partials/cari.blade.php')) {
                continue;
            }

            if (str_contains(file_get_contents($satu), 'wire:model.live.debounce.400ms="cari"')) {
                $pelanggar[] = basename(dirname($satu)).'/'.basename($satu);
            }
        }

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Layar berikut memasang kotak carinya sendiri:'],
            $pelanggar,
            ['', "Pakai @include('livewire.pages.admin.orcha.partials.cari', ['petunjuk' => '...'])."],
        )));
    }
}
