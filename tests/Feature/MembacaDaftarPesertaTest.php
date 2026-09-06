<?php

namespace Tests\Feature;

use App\Livewire\Pages\Admin\Orcha\Concerns\MembacaDaftarPeserta;
use Tests\TestCase;

/**
 * Pengurai daftar peserta yang dipakai bersama dua layar.
 *
 * Diuji langsung, bukan lewat layarnya, karena keputusan di dalamnya yang
 * paling mahal kalau salah — dan masing-masing lahir dari kejadian nyata:
 * gelar di belakang nama yang tidak boleh terbaca sebagai titik jemput, baris
 * judul Excel yang tidak boleh jadi peserta, penomoran WhatsApp yang harus
 * dibuang.
 */
class MembacaDaftarPesertaTest extends TestCase
{
    private function pengurai(): object
    {
        return new class
        {
            use MembacaDaftarPeserta {
                uraikanPeserta as public;
                uraikanTempelan as public;
            }
        };
    }

    public function test_tempelan_dipisah_koma_titik_koma_atau_tab(): void
    {
        // Tiga bentuk yang sama-sama datang ke admin: kolom Excel yang disalin
        // (tab), dan ketikan tangan (koma atau titik koma).
        $hasil = $this->pengurai()->uraikanTempelan(
            "Budi, Terminal Bungurasih\nSari; Stasiun Gubeng\nRian\tHalaman sekolah"
        );

        $this->assertSame('Terminal Bungurasih', $hasil[0]['titik_jemput']);
        $this->assertSame('Stasiun Gubeng', $hasil[1]['titik_jemput']);
        $this->assertSame('Halaman sekolah', $hasil[2]['titik_jemput']);
    }

    public function test_penomoran_daftar_whatsapp_dibuang(): void
    {
        $hasil = $this->pengurai()->uraikanTempelan("1. Budi\n2) Sari\n3 - Rian");

        $this->assertSame(['Budi', 'Sari', 'Rian'], array_column($hasil, 'nama'));
    }

    public function test_berkas_tidak_dipisah_koma(): void
    {
        /*
         | Sel berkas sudah terpisah sejak dibaca lalu disambung dengan tab,
         | jadi memisah ulang dengan koma merusak isi selnya sendiri: "Budi
         | Santoso, S.Pd" terbaca sebagai nama "Budi Santoso" dengan titik
         | jemput " S.Pd".
         |
         | Gelar di belakang nama bukan hal yang jarang di daftar peserta
         | sekolah — dan yang menemukan cacatnya nanti tour leader yang
         | memanggil nama yang salah.
         */
        $hasil = $this->pengurai()->uraikanPeserta(
            ["Budi Santoso, S.Pd\tTerminal Bungurasih"],
            dariBerkas: true,
        );

        $this->assertSame('Budi Santoso, S.Pd', $hasil[0]['nama']);
        $this->assertSame('Terminal Bungurasih', $hasil[0]['titik_jemput']);
    }

    public function test_baris_judul_excel_tidak_jadi_peserta(): void
    {
        // Berkas panitia hampir selalu berjudul. Tanpa ini, rombongan bertambah
        // satu orang bernama "Nama" — dan angka pesertanya ikut salah.
        $hasil = $this->pengurai()->uraikanPeserta(
            ["Nama\tTitik Jemput", "Budi\tTerminal Bungurasih"],
            dariBerkas: true,
        );

        $this->assertCount(1, $hasil);
        $this->assertSame('Budi', $hasil[0]['nama']);
    }

    public function test_baris_kosong_dilewati(): void
    {
        $hasil = $this->pengurai()->uraikanTempelan("Budi\n\n   \nSari\n");

        $this->assertCount(2, $hasil);
    }

    public function test_penggantian_dinyatakan_dengan_tanda_panah(): void
    {
        /*
         | Panitia mengirim daftarnya sekaligus — sebagian nama baru, sebagian
         | menggantikan yang berhalangan. Memaksa admin memilah dua kelompok
         | itu dengan tangan hanya memindahkan pekerjaan.
         */
        $hasil = $this->pengurai()->uraikanTempelan('Haha > Wiam');

        $this->assertSame('Wiam', $hasil[0]['nama']);
        $this->assertSame('Haha', $hasil[0]['gantikan']);
    }

    public function test_berkas_menyatakan_penggantian_lewat_kolom_ketiga(): void
    {
        $hasil = $this->pengurai()->uraikanPeserta(
            ["Wiam\tTerminal Bungurasih\tHaha"],
            dariBerkas: true,
        );

        $this->assertSame('Wiam', $hasil[0]['nama']);
        $this->assertSame('Haha', $hasil[0]['gantikan']);
    }

    public function test_penguraiannya_hanya_satu_salinan(): void
    {
        /*
         | Dua layar memakai pengurai ini. Salinan kedua akan kehilangan
         | sebagian keputusan di atas diam-diam, dan bedanya baru ketahuan saat
         | satu layar menghasilkan daftar yang berbeda dari layar lain untuk
         | berkas yang sama.
         */
        $memasang = [];

        foreach (glob(app_path('Livewire/Pages/Admin/Orcha/Pendaftaran/*.php')) ?: [] as $berkas) {
            // Salinan kembar buatan OneDrive tidak pernah dimuat PHP.
            if (preg_match('/ \d+\.php$/', $berkas)) {
                continue;
            }

            if (str_contains(file_get_contents($berkas), 'private function uraikan(')) {
                $memasang[] = basename($berkas);
            }
        }

        $this->assertSame([], $memasang,
            'Ada layar yang memakai penguraiannya sendiri; pakai trait MembacaDaftarPeserta.');
    }
}
