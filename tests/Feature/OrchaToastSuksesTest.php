<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Pemberitahuan di halaman admin Orcha.
 *
 * Aturannya sekarang: SEMUANYA SweetAlert glossy milik lemon, sukses maupun
 * gagal. Tidak ada lagi toast.
 *
 * Berkas ini dulu menjaga aturan yang SEBALIKNYA (sukses memakai toast).
 * Aturan itu dicabut 14 Nov 2026 karena dua alasan. Pertama, layar Orcha jadi
 * terasa bukan bagian dari lemon: satu tindakan yang sama memberi kabar
 * dengan dua bentuk berbeda tergantung layarnya. Kedua, pada layar yang
 * penangan lamanya masih hidup keduanya terbit BERBARENGAN — Detail Pesanan
 * memasang Livewire.on('order-updated') sekali lalu sengaja membiarkannya
 * bertahan lintas wire:navigate, sehingga satu unggahan di Galeri
 * menerbitkan satu dialog DAN satu toast sekaligus.
 *
 * Yang tetap dibedakan cuma satu: sukses menutup sendiri, gagal menunggu
 * ditekan. Pesan gagal berarti pekerjaannya TIDAK tersimpan; itu harus
 * menghentikan langkah, bukan lewat begitu saja sementara admin sudah pindah
 * ke baris berikutnya.
 */
class OrchaToastSuksesTest extends TestCase
{
    private function partial(): string
    {
        return file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/skrip.blade.php')
        );
    }

    /** @return list<string> */
    private function bladeOrcha(): array
    {
        $berkas = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
        );

        // Salinan kembar buatan OneDrive ("form.blade 2.php") bukan berkas
        // yang dipakai Blade — melaporkannya hanya kebisingan.
        return array_values(array_filter(
            $berkas,
            fn ($satu) => ! preg_match('/ \d+\.php$/', $satu),
        ));
    }

    public function test_partial_pemberitahuan_orcha_terkompilasi(): void
    {
        $php = Blade::compileString($this->partial());

        $sementara = tempnam(sys_get_temp_dir(), 'blade').'.php';
        file_put_contents($sementara, $php);
        exec('php -l '.escapeshellarg($sementara).' 2>&1', $keluaran, $kode);
        unlink($sementara);

        $this->assertSame(0, $kode, implode("\n", $keluaran));
    }

    /**
     * Tidak boleh ada sisa toast di mana pun di halaman Orcha.
     *
     * Diperiksa dari GAYA-nya, bukan dari nama fungsinya: window.orchaToast
     * sengaja dipertahankan sebagai nama lama supaya pemanggil yang sudah ada
     * tidak perlu diubah — yang berganti isinya. Kalau wadah dan animasinya
     * muncul lagi, berarti toastnya benar-benar kembali.
     */
    public function test_tidak_ada_sisa_toast(): void
    {
        $pelanggar = [];

        foreach ($this->bladeOrcha() as $satu) {
            $isi = file_get_contents($satu);

            foreach (['.orcha-toast-wadah', 'orchaToastMasuk', 'orchaToastWaktu', 'toast: true'] as $jejak) {
                if (str_contains($isi, $jejak)) {
                    $pelanggar[] = basename($satu).' → '.$jejak;
                }
            }
        }

        $this->assertSame([], $pelanggar,
            'Toast muncul lagi di halaman Orcha; semuanya harus SweetAlert: '.implode(', ', $pelanggar));
    }

    /**
     * Sukses digambar oleh helper lemon, bukan Swal.fire milik Orcha sendiri.
     *
     * Kalau rupa dialog lemon suatu saat disetel ulang, layar Orcha harus ikut
     * berubah tanpa ada yang perlu ingat menyentuh berkas ini. Dua salinan
     * gaya untuk satu dialog pasti berbeda suatu hari.
     */
    public function test_sukses_memakai_dialog_glossy_lemon(): void
    {
        $this->assertStringContainsString('window.fireGlossySwal', $this->partial(),
            'Dialog sukses Orcha tidak memakai helper glossy milik lemon.');
    }

    /**
     * Halaman Orcha tidak merakit popup suksesnya sendiri.
     *
     * Dijaga sejak sebelum toast dicabut, dan alasannya tidak berubah: satu
     * tempat yang menentukan rupa kabar sukses. Bedanya dulu "harus toast",
     * sekarang "harus lewat peristiwa, biar partial bersama yang menggambar".
     */
    public function test_halaman_orcha_tidak_merakit_popup_suksesnya_sendiri(): void
    {
        $pelanggar = [];

        foreach ($this->bladeOrcha() as $satu) {
            if (basename($satu) === 'skrip.blade.php') {
                continue;
            }

            if (preg_match("/icon:\s*['\"]success['\"]/", file_get_contents($satu))) {
                $pelanggar[] = basename($satu);
            }
        }

        $this->assertSame([], $pelanggar,
            'Kabar sukses harus dikirim sebagai peristiwa, bukan digambar sendiri: '.implode(', ', $pelanggar));
    }

    /**
     * Dulu 'toast-sukses' dikirim dua kali dari OrchaPendaftaranDetail tetapi
     * tidak punya penangan sama sekali — kedua pesan itu tidak pernah muncul,
     * dan tidak ada galat apa pun yang menandainya.
     */
    public function test_setiap_peristiwa_sukses_punya_penangan(): void
    {
        $isi = $this->partial();

        foreach (['order-updated', 'toast-sukses', 'orcha-sukses-pindah'] as $peristiwa) {
            $this->assertStringContainsString(
                "Livewire.on('$peristiwa'",
                $isi,
                "Peristiwa '$peristiwa' dikirim halaman Orcha tetapi tidak ada yang menanganinya."
            );
        }
    }

    /**
     * Pesan gagal harus tetap menghentikan langkah.
     *
     * Bukan cuma "memakai SweetAlert" — sukses pun begitu sekarang. Yang
     * membedakan: gagal punya tombol dan TIDAK menutup sendiri.
     */
    public function test_pesan_gagal_menunggu_diakui(): void
    {
        $isi = $this->partial();

        $this->assertMatchesRegularExpression(
            "/Livewire\.on\('toast-error'.*?Swal\.fire/s",
            $isi,
            'Pesan gagal tidak boleh jadi toast — ia harus menunggu diakui.'
        );

        preg_match("/Livewire\.on\('toast-error'.*?\}\);/s", $isi, $cocok);
        $blok = $cocok[0] ?? '';

        $this->assertStringContainsString('confirmButtonText', $blok,
            'Dialog gagal tidak punya tombol untuk diakui.');
        $this->assertStringNotContainsString('timer', $blok,
            'Dialog gagal tidak boleh menutup sendiri — admin bisa melewatkannya.');
    }

    /**
     * Sukses yang menyeberangi perpindahan halaman dititipkan ke halaman
     * TUJUAN, bukan ditampilkan di halaman yang sedang ditinggalkan.
     */
    public function test_sukses_setelah_pindah_dititipkan_ke_halaman_tujuan(): void
    {
        $isi = $this->partial();

        $this->assertStringContainsString('orchaSuksesNanti', $isi);
        $this->assertStringContainsString("sessionStorage.setItem('orcha-kabar'", $isi);
        $this->assertStringContainsString("sessionStorage.getItem('orcha-kabar')", $isi);
    }
}
