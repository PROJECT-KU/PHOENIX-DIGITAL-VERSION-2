<?php

namespace Tests\Feature;

use App\Support\RingkasDokumen;
use Tests\TestCase;
use ZipArchive;

/**
 * DOCX pelanggan kerap melewati batas unggah Groqy/Groupy (di bawah 5 MB).
 * Yang membuat berkas membengkak adalah gambar, sedangkan pemeriksaan
 * kemiripan hanya membaca teks — jadi gambar boleh dimampatkan, teks tidak.
 */
class RingkasDokumenTest extends TestCase
{
    private array $sampah = [];

    protected function tearDown(): void
    {
        foreach ($this->sampah as $berkas) {
            @unlink($berkas);
        }

        parent::tearDown();
    }

    /** DOCX berisi teks + sejumlah gambar berderau (sulit dimampatkan ZIP). */
    private function buatDocx(int $jumlahGambar, int $lebar = 1600, int $tinggi = 1100): string
    {
        $jalur = tempnam(sys_get_temp_dir(), 'uji').'.docx';
        $this->sampah[] = $jalur;

        $teks = '';
        for ($i = 1; $i <= 120; $i++) {
            $teks .= '<w:p><w:r><w:t>Paragraf pengujian nomor '.$i
                .' berisi kalimat yang cukup panjang untuk mewakili isi skripsi.</w:t></w:r></w:p>';
        }

        $zip = new ZipArchive;
        $zip->open($jalur, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Default Extension="jpg" ContentType="image/jpeg"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml',
            '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$teks.'</w:body></w:document>');

        for ($n = 1; $n <= $jumlahGambar; $n++) {
            $im = imagecreatetruecolor($lebar, $tinggi);
            for ($x = 0; $x < $lebar; $x += 2) {
                for ($y = 0; $y < $tinggi; $y += 2) {
                    imagefilledrectangle($im, $x, $y, $x + 2, $y + 2,
                        imagecolorallocate($im, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
                }
            }
            ob_start();
            imagejpeg($im, null, 98);
            $zip->addFromString("word/media/image{$n}.jpg", ob_get_clean());
            imagedestroy($im);
        }

        $zip->close();

        return $jalur;
    }

    private function teksDari(string $docx): string
    {
        $zip = new ZipArchive;
        $zip->open($docx);
        $isi = $zip->getFromName('word/document.xml');
        $zip->close();

        return $isi;
    }

    public function test_docx_kegemukan_dikecilkan_sampai_muat_batas_groupy(): void
    {
        $asli = $this->buatDocx(3);

        $this->assertGreaterThan(RingkasDokumen::BATAS_PENYEDIA, filesize($asli),
            'Berkas uji harus melewati batas dulu agar pemampatan diuji sungguhan.');

        $hasil = RingkasDokumen::kecilkanDocx($asli, RingkasDokumen::BATAS_PENYEDIA);
        $this->sampah[] = $hasil;

        $this->assertNotNull($hasil);
        $this->assertLessThanOrEqual(RingkasDokumen::BATAS_PENYEDIA, filesize($hasil));
    }

    public function test_teks_tidak_berubah_sehingga_hasil_kemiripan_tetap_sama(): void
    {
        $asli = $this->buatDocx(3);
        $hasil = RingkasDokumen::kecilkanDocx($asli, RingkasDokumen::BATAS_PENYEDIA);
        $this->sampah[] = $hasil;

        $this->assertSame($this->teksDari($asli), $this->teksDari($hasil));
    }

    public function test_gambar_diperkecil_bukan_dihapus_agar_tata_letak_utuh(): void
    {
        $asli = $this->buatDocx(3);
        $hasil = RingkasDokumen::kecilkanDocx($asli, RingkasDokumen::BATAS_PENYEDIA);
        $this->sampah[] = $hasil;

        $zip = new ZipArchive;
        $zip->open($hasil);
        $media = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->getNameIndex($i), 'word/media/')) {
                $media[] = $zip->getNameIndex($i);
            }
        }
        $zip->close();

        $this->assertCount(3, $media);
    }

    public function test_berkas_yang_sudah_muat_tidak_disentuh(): void
    {
        $kecil = $this->buatDocx(0);

        $this->assertNull(RingkasDokumen::kecilkanDocx($kecil, RingkasDokumen::BATAS_PENYEDIA));
        $this->assertFalse(RingkasDokumen::perluDiringkas($kecil));
    }

    public function test_pdf_tidak_ikut_diproses(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'uji').'.pdf';
        $this->sampah[] = $pdf;
        file_put_contents($pdf, str_repeat('x', RingkasDokumen::BATAS_PENYEDIA + 1000));

        $this->assertFalse(RingkasDokumen::perluDiringkas($pdf));
        $this->assertNull(RingkasDokumen::kecilkanDocx($pdf, RingkasDokumen::BATAS_PENYEDIA));
    }

    public function test_nama_unduhan_menandai_versi_ringkas(): void
    {
        $this->assertSame('SKRIPSI BAB 1 (ringkas).docx',
            RingkasDokumen::namaRingkas('SKRIPSI BAB 1.docx'));
    }
}
