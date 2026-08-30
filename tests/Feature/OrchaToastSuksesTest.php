<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Pemberitahuan di halaman admin Orcha.
 *
 * Aturannya: SUKSES memakai toast, GAGAL tetap memakai SweetAlert.
 *
 * Bedanya disengaja. Pesan sukses cuma mengabarkan bahwa yang barusan ditekan
 * memang berhasil — admin tidak perlu berhenti, apalagi menekan tombol untuk
 * menutupnya. Pesan gagal berarti pekerjaannya TIDAK tersimpan; itu harus
 * menghentikan langkah dan menunggu diakui, bukan lewat di pojok layar
 * sementara admin sudah pindah ke baris berikutnya.
 */
class OrchaToastSuksesTest extends TestCase
{
    private function partial(): string
    {
        return file_get_contents(
            resource_path('views/livewire/pages/admin/orcha/partials/skrip.blade.php')
        );
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

    /** Tidak boleh ada lagi popup sukses di seluruh halaman Orcha. */
    public function test_tidak_ada_sweetalert_sukses_tersisa(): void
    {
        $berkas = array_merge(
            glob(resource_path('views/livewire/pages/admin/orcha/*.blade.php')) ?: [],
            glob(resource_path('views/livewire/pages/admin/orcha/*/*.blade.php')) ?: [],
        );

        $pelanggar = [];

        foreach ($berkas as $satu) {
            // Salinan kembar buatan OneDrive ("form.blade 2.php") bukan berkas
            // yang dipakai Blade — melaporkannya hanya kebisingan.
            if (preg_match('/ \d+\.php$/', $satu)) {
                continue;
            }

            if (preg_match("/icon:\s*['\"]success['\"]/", file_get_contents($satu))) {
                $pelanggar[] = basename($satu);
            }
        }

        $this->assertSame([], $pelanggar,
            'Pesan sukses di halaman Orcha harus memakai toast: '.implode(', ', $pelanggar));
    }

    public function test_toast_sukses_tersedia_dan_bergaya(): void
    {
        $isi = $this->partial();

        $this->assertStringContainsString('window.orchaToast', $isi, 'Fungsi toast tidak ada.');

        // Gaya DAN perilaku harus berada di berkas yang sama — kalau terpisah,
        // sebuah halaman bisa memuat perilakunya tanpa memuat tampilannya.
        $this->assertStringContainsString('.orcha-toast-wadah', $isi, 'Gaya toast tidak ikut di berkas ini.');
        $this->assertStringContainsString('aria-live', $isi, 'Toast tidak terbaca pembaca layar.');
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

    /** Pesan gagal harus tetap menghentikan langkah. */
    public function test_pesan_gagal_tetap_sweetalert(): void
    {
        $this->assertMatchesRegularExpression(
            "/Livewire\.on\('toast-error'.*?Swal\.fire/s",
            $this->partial(),
            'Pesan gagal tidak boleh jadi toast — ia harus menunggu diakui.'
        );
    }

    /**
     * Sukses yang menyeberangi perpindahan halaman dititipkan ke halaman
     * TUJUAN, bukan ditampilkan di halaman yang sedang ditinggalkan.
     */
    public function test_sukses_setelah_pindah_dititipkan_ke_halaman_tujuan(): void
    {
        $isi = $this->partial();

        $this->assertStringContainsString('orchaSuksesNanti', $isi);
        $this->assertStringContainsString("sessionStorage.setItem('orcha-toast'", $isi);
        $this->assertStringContainsString("sessionStorage.getItem('orcha-toast')", $isi);
    }
}
