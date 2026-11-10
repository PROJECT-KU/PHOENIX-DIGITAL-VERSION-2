<?php

use App\Livewire\Pages\Admin\Orcha\Rab\OrchaRabSusun;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Mengganti satuan baris biaya yang SUDAH ada.
 *
 * Satuan dulu hanya bisa ditentukan sekali, saat barisnya dibuat. Begitu
 * salah, satu-satunya jalan adalah menghapus barisnya lalu mengetik ulang
 * seluruh isinya: nama, vendor, harga hasil negosiasi, catatan, hari
 * pemakaiannya. Yang terjadi berikutnya bukan admin mengetik ulang,
 * melainkan barisnya dibiarkan salah dan jumlahnya "dibetulkan" dengan angka
 * karangan.
 *
 * Hitungannya milik Orcha; yang dijaga di sini lemon mengirim ke medan yang
 * benar, dan MENAHAN perubahan yang akan menghasilkan angka berbahaya
 * alih-alih menebaknya.
 */
test('kotak satuan tergambar dengan nilai barisnya terpilih', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertSee('satuanBaris.21', false)
        ->assertSee('Dihitung per')
        // Penandanya WAJIB memakai $this->: tanpa itu @selected membaca
        // variabel pandangan yang belum tentu nilai terkini komponen.
        ->assertSeeHtml('value="kamar_malam" selected');
});

test('mengganti satuan mengirimkannya ke Orcha', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('satuanBaris.21', 'orang');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['satuan'] === 'orang');
});

test('pindah ke satuan berkapasitas tanpa kapasitas DITAHAN, bukan ditebak', function () {
    // Baris per rombongan: tidak punya kapasitas sama sekali.
    palsukanRabMalam([barisBiayaMalam([
        'satuan' => 'rombongan', 'satuan_label' => 'per rombongan',
        'kapasitas' => null, 'unit_manual' => null, 'pakai_durasi' => false,
    ])]);

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('satuanBaris.21', 'unit_hari');

    /*
     | Menebak kapasitasnya (1, misalnya) jauh lebih buruk daripada menahan:
     | 13 peserta dibagi 1 menghasilkan 13 bus yang terlihat seperti
     | hitungan sungguhan, dan penawarannya berangkat begitu saja.
     */
    expect($komponen->get('satuanTertahan')[21] ?? '')->toContain('berapa orang per unit');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21'));
});

test('mengisi kapasitas melepaskan satuan yang tertahan, sekali jalan', function () {
    palsukanRabMalam([barisBiayaMalam([
        'satuan' => 'rombongan', 'satuan_label' => 'per rombongan',
        'kapasitas' => null, 'unit_manual' => null, 'pakai_durasi' => false,
    ])]);

    /*
     | Inilah yang melepaskan kuncinya. Berurutan — satuan dulu, lalu
     | kapasitas — admin terjebak: satuannya ditolak karena kapasitas belum
     | ada, dan kapasitasnya tidak bisa diisi karena kotaknya hanya muncul
     | untuk satuan berkapasitas. Keduanya karena itu berangkat bersamaan.
     */
    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('satuanBaris.21', 'unit_hari')
        ->set('kapasitasBaris.21', '20');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['satuan'] === 'unit_hari'
        && $r['kapasitas'] === 20);
});

test('kotak kapasitas mengikuti satuan yang SEDANG dipilih, bukan yang tersimpan', function () {
    palsukanRabMalam([barisBiayaMalam([
        'satuan' => 'rombongan', 'satuan_label' => 'per rombongan',
        'kapasitas' => null, 'unit_manual' => null, 'pakai_durasi' => false,
    ])]);

    $komponen = Livewire::actingAs(adminRab())->test(OrchaRabSusun::class, ['rab' => 9]);

    // Baris per rombongan: belum ada kotak kapasitas.
    $komponen->assertDontSee('kapasitasBaris.21', false);

    // Begitu satuannya dipilih, kotaknya muncul — walau perubahannya
    // sendiri masih ditahan. Tanpa ini admin tidak punya tempat mengisi
    // angka yang diminta layar.
    $komponen->set('satuanBaris.21', 'kamar_malam')
        ->assertSee('kapasitasBaris.21', false)
        ->assertSee('Isi kamar');
});

test('mengosongkan kapasitas pada satuan berkapasitas ditahan', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    $komponen = Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('kapasitasBaris.21', '');

    // Kapasitas kosong pada satuan kamar membuat seluruh rombongan dihitung
    // muat dalam satu kamar.
    expect($komponen->get('satuanTertahan')[21] ?? '')->toContain('tidak boleh kosong');

    Http::assertNotSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21'));
});

test('kapasitas dikirim sendirian bila satuannya tidak berubah', function () {
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->set('kapasitasBaris.21', '4');

    Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
        && str_contains($r->url(), '/biaya/21')
        && $r['kapasitas'] === 4
        && ! isset($r['satuan']));
});

test('keping satuan lama tidak lagi mengulang isi kotaknya', function () {
    /*
     | Baris yang menyebut hal yang sama dua kali — keping "per kamar per
     | malam" di atas, kotak pilih bertuliskan sama di bawah — membuat yang
     | satu terbaca sebagai keterangan yang TIDAK ikut berubah, lalu admin
     | mengira suntingannya tidak tersimpan.
     */
    palsukanRabMalam([barisBiayaMalam()]);

    Livewire::actingAs(adminRab())
        ->test(OrchaRabSusun::class, ['rab' => 9])
        ->assertDontSeeHtml('<span class="rab-pil-satuan">per kamar per malam</span>')
        ->assertDontSee('kapasitas 2')
        // Kalimat penjelasannya tetap: ia menyebut hitungannya utuh.
        ->assertSee('Rp 800.000 × 2 kamar × 3 malam');
});
