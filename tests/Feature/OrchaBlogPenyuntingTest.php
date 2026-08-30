<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Penjaga penyunting teks pada formulir blog Orcha.
 *
 * Dua bug pernah membuat kotak "Isi Artikel" tidak bisa diketik, dan KEDUANYA
 * tidak menghasilkan pesan galat apa pun di layar — admin cuma menemukan kotak
 * yang diam. Uji ini menjaga keduanya, karena gejalanya tidak akan tertangkap
 * uji apa pun yang hanya memeriksa halamannya terbuka.
 */
class OrchaBlogPenyuntingTest extends TestCase
{
    private function berkasFormulir(): string
    {
        return resource_path('views/livewire/pages/admin/orcha/blog/orcha-blog-form.blade.php');
    }

    /**
     * BUG 1 — nama direktif Blade tertulis di dalam komentar JavaScript.
     *
     * Blade tetap mengurai direktif walau berada di dalam komentar JS. Satu
     * kata "@ script" tanpa pasangan penutupnya menelan sisa view, dan
     * komponennya gagal dengan "missing root tag" — galat yang sama sekali
     * tidak menyebut komentar sebagai sebabnya.
     */
    public function test_blade_formulir_terkompilasi_utuh_dan_punya_tag_akar(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $php = Blade::compileString($sumber);

        $sementara = tempnam(sys_get_temp_dir(), 'blade').'.php';
        file_put_contents($sementara, $php);
        exec('php -l '.escapeshellarg($sementara).' 2>&1', $keluaran, $kode);
        unlink($sementara);

        $this->assertSame(0, $kode, 'Blade formulir gagal dikompilasi: '.implode("\n", $keluaran));

        // Tag akar harus utuh. Kalau sebuah direktif menelan sisa view, jumlah
        // <div> dan </div> pada badan komponen tidak lagi seimbang.
        $badan = substr($sumber, 0, strpos($sumber, "@push('scripts-head')"));

        $this->assertSame(
            substr_count($badan, '<div'),
            substr_count($badan, '</div>'),
            'Tag div pada badan formulir tidak seimbang — biasanya karena direktif Blade tertelan.'
        );
    }

    /**
     * BUG 2 — inisialisasi penyunting ditaruh di direktif skrip Livewire.
     *
     * Direktif itu hanya dijalankan untuk komponen ANAK. Formulir ini komponen
     * halaman penuh, sehingga bloknya terkirim tetapi tidak pernah dieksekusi:
     * Quill termuat, Livewire termuat, tetapi penyuntingnya tidak pernah
     * terbentuk.
     */
    public function test_penyunting_dipasang_dari_tumpukan_skrip_biasa(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $this->assertStringContainsString(
            'new Quill',
            $sumber,
            'Formulir kehilangan inisialisasi penyunting.'
        );

        // Nama direktifnya dirakit supaya uji ini sendiri tidak memicu bug 1
        // saat berkasnya kebetulan ikut terbaca alat lain.
        $direktifSkrip = '@'.'script';

        $this->assertStringNotContainsString(
            $direktifSkrip,
            $sumber,
            'Inisialisasi penyunting tidak boleh memakai direktif skrip Livewire pada komponen halaman penuh.'
        );
    }

    /**
     * Penyunting harus menunggu pustakanya siap.
     *
     * Halaman daftar tidak memuat Quill. Saat admin menekan "Tulis",
     * wire:navigate menyisipkan tag skrip Quill yang baru — yang bisa belum
     * selesai diunduh saat blok ini berjalan.
     */
    public function test_penyunting_menunggu_pustaka_dan_ikut_perpindahan_halaman(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $this->assertStringContainsString('window.Quill', $sumber, 'Tidak ada penjagaan ketersediaan Quill.');
        $this->assertStringContainsString('livewire:navigated', $sumber, 'Penyunting tidak dipasang ulang saat berpindah halaman.');

        // Gagal memuat pustaka harus MEMBERI TAHU admin, bukan meninggalkan
        // kotak yang diam tanpa penjelasan.
        $this->assertStringContainsString('gagal dimuat', $sumber, 'Tidak ada pesan bila penyunting gagal dimuat.');
    }

    /**
     * Pratinjau sampul harus menunjuk server ORCHA, bukan lemon.
     *
     * API mengirim jalur relatif ("/storage/artikel/x.webp"). Dipakai apa
     * adanya, peramban mencarinya di alamat lemon dan mendapat 404 — yang
     * terlihat admin cuma kotak kosong berisi tulisan alt-nya. Tidak ada galat
     * apa pun yang menandainya.
     */
    public function test_pratinjau_sampul_memakai_alamat_orcha(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $this->assertStringContainsString('config(\'orcha.url\')', $sumber,
            'Pratinjau sampul tidak dirakit dari alamat Orcha.');
        $this->assertStringContainsString('$tautanGambar', $sumber);
    }

    /**
     * Berkas yang BARU diunggah dipratinjau lewat PratinjauUnggahan, bukan
     * temporaryUrl().
     *
     * Alamat bawaan Livewire berakhiran .jpg dan lapisan pengoptimal gambar di
     * hosting membuang query string-nya — padahal izin aksesnya ada di situ,
     * sehingga pratinjaunya tidak pernah muncul di produksi. Sudah pernah
     * terjadi; lihat App\Support\PratinjauUnggahan.
     */
    public function test_unggahan_baru_tidak_memakai_temporary_url(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $this->assertStringContainsString('PratinjauUnggahan::url', $sumber);

        // Yang dicari PEMANGGILANNYA ("->temporaryUrl("), bukan namanya saja —
        // komentar di berkas itu menyebutnya untuk menjelaskan kenapa tidak
        // dipakai, dan uji yang mencari nama telanjang akan menuduh komentar.
        $this->assertStringNotContainsString('->temporaryUrl(', $sumber,
            'temporaryUrl() tidak bekerja di produksi — pakai PratinjauUnggahan::url().');
    }

    /**
     * Tombol simpan BERGANTI jadi pemintal, dan hanya saat menyimpan.
     *
     * Menyimpan artikel berarti menunggu Orcha di server lain. Tanpa tanda apa
     * pun, admin menekan tombolnya lagi dan artikel yang sama terkirim dua kali.
     */
    public function test_tombol_simpan_jadi_pemintal_saat_menyimpan(): void
    {
        $sumber = file_get_contents($this->berkasFormulir());

        $this->assertStringContainsString('spinner-border', $sumber, 'Tombol simpan tidak punya pemintal.');
        $this->assertStringContainsString('wire:loading.remove wire:target="simpan"', $sumber,
            'Isi tombol harus DIGANTI pemintal, bukan pemintal ditaruh di sebelahnya.');
        $this->assertStringContainsString('wire:loading.attr="disabled" wire:target="simpan"', $sumber,
            'Tombol harus dimatikan saat menyimpan supaya tidak terkirim dua kali.');
    }

    /** Isi awal dibaca dari isian tersembunyi, yang karena itu wajib membawa nilainya. */
    public function test_isian_tersembunyi_membawa_isi_awal_artikel(): void
    {
        $this->assertMatchesRegularExpression(
            '/id="isi"\s+value="\{\{ \$isi \}\}"/',
            file_get_contents($this->berkasFormulir()),
            'Isian tersembunyi harus membawa value, karena dari sanalah penyunting membaca isi artikel saat menyunting.'
        );
    }
}
