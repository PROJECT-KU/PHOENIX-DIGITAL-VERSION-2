<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tampilan halaman Orcha harus seragam dengan lemon.
 *
 * Warna, radius, dan ukurannya diambil dari token di partials/gaya.blade.php
 * yang menyalin nilai templateindex.blade.php. Sebelum ini warnanya ditulis
 * tersebar — 78 kali untuk dua biru saja — sehingga menyeragamkannya berarti
 * menyunting ratusan tempat satu per satu, dan satu tempat yang terlewat
 * meninggalkan noda biru di tengah halaman ungu.
 */
class OrchaTampilanSeragamTest extends TestCase
{
    /** @return array<int, string> */
    private function berkasOrcha(): array
    {
        $semua = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*/*.blade.php')) ?: [],
        );

        // Salinan kembar buatan OneDrive ("form.blade 2.php") tidak pernah
        // dirender Blade — melaporkannya hanya kebisingan.
        return array_values(array_filter($semua, fn ($f) => ! preg_match('/ \d+\.php$/', $f)));
    }

    public function test_token_tampilan_menyalin_nilai_lemon(): void
    {
        $gaya = file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php')
        );

        foreach ([
            '--orc-primer: #7c3aed' => 'warna utama lemon (.btn-primary)',
            '--orc-primer-2: #4f46e5' => 'ujung gradien lemon',
            '--orc-radius-tombol: 12px' => 'radius tombol lemon (.btn)',
            '--orc-radius-isian: 12px' => 'radius isian lemon (.form-control)',
        ] as $token => $asal) {
            $this->assertStringContainsString($token, $gaya, "Token tidak lagi menyalin $asal.");
        }
    }

    /**
     * Warna merek lama tidak boleh muncul lagi di halaman Orcha mana pun.
     *
     * Satu tempat yang terlewat cukup untuk meninggalkan tombol biru di tengah
     * halaman ungu — dan itu justru terbaca sebagai bug tampilan, bukan sebagai
     * aksen.
     */
    public function test_tidak_ada_warna_biru_lama_yang_ditulis_langsung(): void
    {
        $pelanggar = [];

        foreach ($this->berkasOrcha() as $berkas) {
            if (preg_match('/#1d6fa5|#0f2d4a|rgba\(\s*29,\s*111,\s*165|rgba\(\s*15,\s*45,\s*74/i', file_get_contents($berkas))) {
                $pelanggar[] = basename($berkas);
            }
        }

        $this->assertSame([], $pelanggar,
            'Pakai var(--orc-primer) / var(--orc-tinta), jangan warna langsung: '.implode(', ', $pelanggar));
    }

    /**
     * Warna TULISAN tidak boleh ikut jadi warna merek.
     *
     * Biru tua lama dipakai 32 kali sebagai warna teks dan hanya 10 kali
     * sebagai latar. Mengganti warna merek secara buta akan mengubah seluruh
     * tulisan jadi ungu — jebakan yang hanya kelihatan setelah halamannya
     * dibuka.
     */
    public function test_warna_tulisan_terpisah_dari_warna_merek(): void
    {
        $gaya = file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/gaya.blade.php')
        );

        $this->assertStringContainsString('--orc-tinta: #1f2d3d', $gaya,
            'Warna tulisan harus punya tokennya sendiri, terpisah dari warna merek.');

        /*
         | Tulisan biasa memakai --orc-tinta, dan jumlahnya harus jauh lebih
         | banyak daripada teks beraksen merek.
         |
         | Teks aksen — tautan, ikon, keadaan aktif — memang SEHARUSNYA memakai
         | warna merek, jadi menuntut nol pemakaian adalah salah. Yang dijaga di
         | sini: warna merek tidak menelan warna tulisan, seperti yang akan
         | terjadi kalau seseorang mengganti warna secara buta.
         */
        $tinta = substr_count($gaya, 'color: var(--orc-tinta)');
        $aksen = substr_count($gaya, 'color: var(--orc-primer)');

        $this->assertGreaterThan($aksen, $tinta,
            'Tulisan beraksen merek lebih banyak daripada tulisan biasa — warna merek menelan warna teks.');
    }

    /**
     * Sidebar dan lonceng SENGAJA tetap biru.
     *
     * Keduanya menandai ASAL data — sidebar menandai "Anda sedang di mode
     * Orcha", lonceng membedakan pemberitahuan Orcha dari Phoenix di dalam satu
     * daftar yang sama. Menyeragamkannya justru menghapus satu-satunya petunjuk
     * cepat bahwa yang disunting adalah data situs lain.
     */
    public function test_penanda_mode_orcha_tetap_dibedakan(): void
    {
        foreach (['sidebar', 'notification-bell'] as $nama) {
            $this->assertMatchesRegularExpression(
                '/#1d6fa5|#0f2d4a/i',
                file_get_contents(resource_path("views/livewire/layout/$nama.blade.php")),
                "Penanda mode Orcha di $nama hilang — asal data tidak lagi terbaca sekilas."
            );
        }
    }

    /**
     * wire:loading tidak boleh dipasangi utilitas tampilan Bootstrap.
     *
     * Livewire menyembunyikan dengan menulis style="display: none" sebaris,
     * sedangkan utilitas d-* Bootstrap ditulis `display: ... !important`.
     * !important menang, jadi span yang seharusnya hilang tetap terlihat:
     * tombol simpan menampilkan "Update Artikel" DAN pemintal sekaligus,
     * berjejer dalam satu baris, tanpa satu pun galat yang menandainya.
     *
     * Dicari lewat berkas dan bukan lewat halaman yang dirender karena
     * gejalanya murni CSS — HTML yang dihasilkan Livewire sendiri sudah benar.
     */
    public function test_wire_loading_tidak_dipasangi_utilitas_tampilan(): void
    {
        $pelanggar = [];

        foreach ($this->berkasOrcha() as $satu) {
            $isi = file_get_contents($satu);

            // Tag pembukanya diambil utuh, sebab wire:loading dan class-nya
            // sering terpisah baris setelah dirapikan pemformat.
            preg_match_all('/<[a-z]+\s[^>]*>/i', $isi, $cocok);

            foreach ($cocok[0] as $tag) {
                if (! preg_match('/wire:loading([.\w-]*)/', $tag, $modifier)) {
                    continue;
                }

                /*
                 | Yang diperiksa hanya wire:loading yang MENYEMBUNYIKAN.
                 |
                 | wire:loading.attr dan wire:loading.class tidak menyentuh
                 | display sama sekali, dan keduanya justru sering dipasang
                 | pada tombol yang memang butuh d-inline-flex untuk
                 | menengahkan isinya. Menyapu keduanya sekalian berarti
                 | menyalahkan tombol yang sudah benar — dan penjaga yang
                 | menuduh terlalu banyak akan dimatikan orang, bukan dipatuhi.
                 */
                if (str_contains($modifier[1], '.attr') || str_contains($modifier[1], '.class')) {
                    continue;
                }

                if (preg_match('/class="[^"]*\bd-(?:inline-)?(?:flex|block|inline|grid|table)\b/', $tag)) {
                    $pelanggar[] = basename($satu);
                    break;
                }
            }
        }

        $this->assertSame([], $pelanggar,
            'Kelas d-* mengalahkan display:none milik Livewire, jadi kedua keadaan tombol tampil bersamaan: '
            .implode(', ', $pelanggar));
    }
}
