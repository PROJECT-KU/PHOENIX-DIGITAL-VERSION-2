<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Konfirmasi tindakan berbahaya memakai SweetAlert, bukan dialog peramban.
 *
 * wire:confirm menampilkan dialog bawaan peramban, lengkap dengan baris
 * "127.0.0.1:8001 says" di atas kalimatnya. Terbaca seperti peringatan sistem
 * yang bocor, bukan bagian dari aplikasi — dan pada tindakan yang menghapus
 * data, kepercayaan pada dialognya ikut menentukan apakah orang membacanya
 * atau menekan OK begitu saja.
 *
 * INI BUKAN PENGAMAN. Siapa pun bisa memanggil metodenya langsung tanpa
 * melewati dialog mana pun; yang menjaga tetap pemeriksaan izin di server.
 * Yang dijaga di sini keseragaman tampilannya.
 */
class KonfirmasiHapusTest extends TestCase
{
    /** @return array<int, string> */
    private function berkasBlade(): array
    {
        $hasil = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($iterator as $berkas) {
            if (! $berkas->isFile() || ! str_ends_with($berkas->getFilename(), '.blade.php')) {
                continue;
            }

            // Salinan kembar buatan OneDrive tidak pernah dirender Blade.
            if (preg_match('/ \d+\.blade\.php$/', $berkas->getFilename())) {
                continue;
            }

            $hasil[] = $berkas->getPathname();
        }

        return $hasil;
    }

    public function test_tidak_ada_layar_yang_memakai_dialog_bawaan_peramban(): void
    {
        $pelanggar = [];

        foreach ($this->berkasBlade() as $berkas) {
            /*
             | Yang dicari ATRIBUTNYA — "wire:confirm=" — bukan sekadar
             | katanya. Beberapa berkas menyebut "wire:confirm" di dalam
             | komentar yang justru menerangkan kenapa ia TIDAK dipakai, dan
             | menandai berkas itu sebagai pelanggar membuat penjaganya merah
             | pada berkas yang sudah benar. Penjaga yang merah tanpa sebab
             | adalah penjaga yang akhirnya dimatikan orang.
             */
            if (str_contains(file_get_contents($berkas), 'wire:confirm=')) {
                $pelanggar[] = str_replace(resource_path('views').'/', '', $berkas);
            }
        }

        sort($pelanggar);

        $this->assertSame([], $pelanggar, implode("\n", array_merge(
            ['Layar berikut masih memakai dialog bawaan peramban:'],
            $pelanggar,
            [
                '',
                'Ganti dengan tombol berkelas .pcek-konfirmasi:',
                '  data-action="namaMetode" data-arg="id"',
                '  data-title="..." data-text="..." data-confirm="Ya, hapus" data-icon="warning"',
            ],
        )));
    }

    public function test_penangannya_hanya_satu_salinan_di_layout(): void
    {
        /*
         | Penangannya sempat ada DUA salinan — satu di partial skrip Orcha,
         | satu di detail pesanan — sementara halaman lain yang tidak
         | menyalinnya jatuh ke dialog bawaan.
         |
         | Dua salinan yang sama membuat gayanya berbeda suatu saat, dan
         | dialog yang berbeda bentuk untuk tindakan yang sama berbahayanya
         | membuat orang berhenti membacanya.
         */
        $memasang = [];

        foreach ($this->berkasBlade() as $berkas) {
            if (str_contains(file_get_contents($berkas), '__pcekKonfirmasiBound')) {
                $memasang[] = str_replace(resource_path('views').'/', '', $berkas);
            }
        }

        $this->assertSame(['livewire/layout/templateindex.blade.php'], $memasang,
            'Penangan konfirmasi harus tepat satu salinan, di layout — bukan disalin per halaman.');
    }

    public function test_layout_memasang_penangannya(): void
    {
        // Kalau lepas dari layout, SELURUH tombol konfirmasi diam-diam berubah
        // jadi tombol biasa: tindakannya tetap jalan, dialognya tidak pernah
        // muncul. Tidak ada galat, dan yang menyadarinya nanti orang yang
        // sudah terlanjur menghapus sesuatu.
        $layout = file_get_contents(
            resource_path('views/livewire/layout/templateindex.blade.php')
        );

        $this->assertStringContainsString('.pcek-konfirmasi', $layout);
        $this->assertStringContainsString('Swal.fire', $layout);
    }
}
