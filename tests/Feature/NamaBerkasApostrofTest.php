<?php

use App\Livewire\Pages\Public\ShopPage\JasaCekPage;

/**
 * Firewall hosting menolak unggahan yang nama berkasnya memuat apostrof —
 * dikira percobaan SQL injection — dengan HTTP 403, sebelum permintaannya
 * menyentuh Laravel. Terbukti di server 22 Agu 2026: nama yang sama tanpa
 * apostrof diterima (419, sampai ke Laravel), dengan apostrof ditolak (403).
 *
 * Peramban karena itu mengganti nama berkas sebelum mengunggah, dan nama
 * aslinya dititipkan lewat properti terpisah. Yang diuji di sini adalah sisi
 * PHP-nya: nama mana yang akhirnya disimpan.
 */
function namaTersimpan(string $namaTerunggah, string $titipan = ''): string
{
    $komponen = new JasaCekPage;
    $komponen->namaAsliDokumen = $titipan;

    $berkas = new class($namaTerunggah) extends \Illuminate\Http\UploadedFile
    {
        public function __construct(private string $nama)
        {
            $jalur = tempnam(sys_get_temp_dir(), 'uji');
            file_put_contents($jalur, 'isi');
            parent::__construct($jalur, $nama, null, null, true);
        }

        public function getClientOriginalName(): string
        {
            return $this->nama;
        }
    };

    $metode = new ReflectionMethod(JasaCekPage::class, 'namaBerkas');
    $metode->setAccessible(true);

    return $metode->invoke($komponen, $berkas);
}

it('memakai nama asli customer, bukan nama bersih yang terunggah', function () {
    expect(namaTersimpan('ILMU al-jarh wa Ta-dil.docx', "ILMU al-jarh wa Ta'dil.docx"))
        ->toBe("ILMU al-jarh wa Ta'dil.docx");
});

it('memakai nama terunggah bila peramban tidak menitipkan apa pun', function () {
    // Peramban lawas tanpa DataTransfer, atau JavaScript mati.
    expect(namaTersimpan('Skripsi Bab 1.pdf'))->toBe('Skripsi Bab 1.pdf');
});

it('ekstensi diambil dari berkas terunggah, bukan dari titipan', function () {
    // Titipan datang dari peramban, jadi tak boleh dipakai memalsukan jenis.
    expect(namaTersimpan('laporan.pdf', 'laporan.exe'))->toBe('laporan.pdf');
});

it('jalur folder pada titipan dibuang, hanya nama berkasnya yang dipakai', function () {
    expect(namaTersimpan('aman.docx', '../../etc/passwd.docx'))->toBe('passwd.docx');
    expect(namaTersimpan('aman.docx', 'C:\\Users\\x\\rahasia.docx'))->toBe('rahasia.docx');
});

it('titipan kosong atau spasi saja diabaikan', function () {
    expect(namaTersimpan('asli.docx', '   '))->toBe('asli.docx');
});
