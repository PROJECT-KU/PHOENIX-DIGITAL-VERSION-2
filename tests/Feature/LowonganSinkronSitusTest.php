<?php

use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanForm;
use App\Livewire\Pages\Admin\LowonganPekerjaan\LowonganPekerjaanList;
use App\Models\Lowongan;
use Livewire\Livewire;

/**
 * Apa yang diisi admin HARUS sampai ke situs, apa adanya.
 *
 * Dua repo, dua bahasa: admin menulis ke DB lemon, situs membaca lewat
 * /api/jobs. Yang menghubungkan keduanya cuma nama kunci di JSON — dan nama
 * kunci tidak pernah berbunyi saat salah. Lowongan yang kehilangan satu
 * medan tetap tampil di situs, hanya saja kepingnya hilang, dan tidak ada
 * seorang pun yang tahu sampai pelamar bertanya "gajinya berapa ya?".
 *
 * Daftar medan di bawah DIAMBIL dari yang benar-benar dibaca situsnya:
 * src/js/lowongan.js (kartuLowongan, kepingFakta, lencanaBatas) dan
 * src/js/karir-detail.js.
 */
function lowonganSitus(array $ubah = []): Lowongan
{
    return Lowongan::create(array_merge([
        'title' => 'Programmer Laravel',
        'slug' => 'programmer-laravel-'.uniqid(),
        'is_active' => 'active',
        'unit' => 'reka',
        'jenis' => 'penuh_waktu',
        'pola_kerja' => 'hybrid',
        'lokasi' => 'Yogyakarta',
        'gaji' => 'Rp 5–8 juta / bulan',
        'jumlah_posisi' => 2,
        'ringkasan' => 'Mengerjakan aplikasi internal dan situs klien.',
        'batas_lamaran' => now()->addDays(14)->toDateString(),
        'descriptions' => '<p>Membangun fitur baru.</p>',
        'requirements' => '<ul><li>Minimal 2 tahun</li></ul>',
    ], $ubah));
}

it('mengirim seluruh medan yang dibaca kartu lowongan situs', function () {
    $l = lowonganSitus();

    $baris = $this->getJson('/api/jobs')->assertOk()->json('data.0');

    // kartuLowongan() + kepingFakta() + lencanaBatas() di src/js/lowongan.js
    expect($baris['title'])->toBe('Programmer Laravel')
        ->and($baris['slug'])->toBe($l->slug)
        ->and($baris['unit'])->toBe('reka')
        ->and($baris['ringkasan'])->toBe('Mengerjakan aplikasi internal dan situs klien.')
        ->and($baris['jenis'])->toBe('penuh_waktu')
        ->and($baris['pola_kerja'])->toBe('hybrid')
        ->and($baris['lokasi'])->toBe('Yogyakarta')
        ->and($baris['jumlah_posisi'])->toBe(2)
        ->and($baris['batas_lamaran'])->toBe(now()->addDays(14)->format('Y-m-d'))
        // waktuRelatif() membacanya; tanpa ini kaki kartu kosong.
        ->and($baris['created_at'])->not->toBeNull();
});

it('halaman detail situs menerima isi lengkapnya', function () {
    $l = lowonganSitus();

    $data = $this->getJson('/api/jobs/'.$l->slug)->assertOk()->json('data');

    // karir-detail.js membacanya.
    expect($data['descriptions'])->toBe('<p>Membangun fitur baru.</p>')
        ->and($data['requirements'])->toBe('<ul><li>Minimal 2 tahun</li></ul>')
        ->and($data['gaji'])->toBe('Rp 5–8 juta / bulan')
        ->and($data['posted_at'])->toBe($l->created_at->format('Y-m-d'));
});

it('yang dikosongkan admin dikirim null, bukan string kosong', function () {
    /*
     | Situs menyembunyikan keping yang nilainya "kosong" (if (job.lokasi)).
     | String kosong memang falsy di JS, tetapi "0" tidak — dan jumlah_posisi
     | yang terkirim sebagai "0" akan menggambar keping "0 posisi".
     */
    $l = lowonganSitus(['jenis' => null, 'pola_kerja' => null, 'lokasi' => null,
        'gaji' => null, 'jumlah_posisi' => null, 'batas_lamaran' => null]);

    $baris = $this->getJson('/api/jobs')->assertOk()->json('data.0');

    foreach (['jenis', 'pola_kerja', 'lokasi', 'jumlah_posisi', 'batas_lamaran'] as $medan) {
        expect($baris[$medan])->toBeNull();
    }

    expect($this->getJson('/api/jobs/'.$l->slug)->json('data.gaji'))->toBeNull();
});

it('menutup lowongan langsung melepasnya dari situs', function () {
    // Tombol Tutup di layar admin, lalu situs tidak boleh melihatnya lagi.
    $l = lowonganSitus(['batas_lamaran' => now()->subDay()->toDateString()]);
    expect($this->getJson('/api/jobs')->json('data'))->toHaveCount(0);

    $l->update(['batas_lamaran' => now()->addDays(5)->toDateString()]);
    expect($this->getJson('/api/jobs')->json('data'))->toHaveCount(1);

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->call('tutup', $l->id);

    expect($this->getJson('/api/jobs')->json('data'))->toHaveCount(0)
        ->and($this->getJson('/api/jobs/'.$l->slug)->status())->toBe(404);
});

it('salinan hasil gandakan tidak ikut tayang sebelum disunting', function () {
    // Dua lowongan kembar muncul bersamaan di situs adalah yang paling
    // mudah terjadi kalau salinannya langsung hidup.
    $asli = lowonganSitus();

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanList::class)
        ->call('gandakan', $asli->id);

    expect($this->getJson('/api/jobs')->json('data'))->toHaveCount(1);
});

it('slug yang dibagikan tetap hidup sesudah judulnya dibetulkan', function () {
    /*
     | Inilah akibat yang paling mahal dari slug yang ikut berubah: tautan
     | yang sudah disebar ke WhatsApp/LinkedIn/Google menunjuk alamat yang
     | tidak ada lagi, dan yang membagikannya tidak pernah tahu.
     */
    $l = lowonganSitus(['title' => 'Programer Laravel']);
    $slugDisebar = $l->slug;

    Livewire::actingAs(adminDaftarLowongan())
        ->test(LowonganPekerjaanForm::class, ['lowongan' => $l])
        ->set('title', 'Programmer Laravel')
        ->call('save');

    $this->getJson('/api/jobs/'.$slugDisebar)
        ->assertOk()
        ->assertJsonPath('data.title', 'Programmer Laravel');
});
