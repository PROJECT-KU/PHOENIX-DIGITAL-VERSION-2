<?php

use App\Services\Telegram\AksiAgen;
use App\Support\BotTurnitin;
use App\Support\LaporanVps;
use Illuminate\Support\Facades\Cache;

/**
 * Jembatan VPS ⇄ Phoenix ⇄ agen Telegram.
 *
 * Yang dijaga: VPS tidak perlu membuka port apa pun, perintah dari Telegram
 * hanya yang terdaftar, dan laporan basi TIDAK pernah tampil seolah sehat.
 */
beforeEach(function () {
    Cache::flush();
    BotTurnitin::lupakanSkema();
});

/** Teks peringatan bot:pantau-vps (mode kering, tidak mengirim ke Telegram). */
function peringatanVps(): string
{
    \Illuminate\Support\Facades\Artisan::call('bot:pantau-vps', ['--kering' => true]);

    return \Illuminate\Support\Facades\Artisan::output();
}

function tokenVps(): array
{
    return ['Authorization' => 'Bearer '.BotTurnitin::buatToken(), 'Accept' => 'application/json'];
}

it('menerima laporan VPS dan menyimpannya', function () {
    $this->postJson('/api/bot-turnitin/lapor', [
        'mode' => 'penuh',
        'kuota' => 19,
        'kuota_teks' => 'Paket Standard: 19x tersisa',
        'memori_mb' => 520,
        'memori_total_mb' => 2241,
        'disk_persen' => 16,
        'beban' => 0.2,
        'selesai_hari_ini' => 1,
    ], tokenVps())->assertOk()->assertJson(['ok' => true, 'perintah' => null]);

    $data = LaporanVps::terakhir();

    expect($data['kuota'])->toBe(19)
        ->and($data['mode'])->toBe('penuh')
        ->and(LaporanVps::basi())->toBeFalse();
});

it('laporan tanpa token ditolak', function () {
    $this->postJson('/api/bot-turnitin/lapor', ['mode' => 'penuh'])->assertStatus(401);
    $this->postJson('/api/bot-turnitin/lapor', ['mode' => 'penuh'], ['Authorization' => 'Bearer salah'])->assertStatus(401);
});

it('perintah dari Telegram dijemput VPS sekali saja', function () {
    expect(LaporanVps::titipPerintah('restart'))->toBeTrue()
        ->and(LaporanVps::perintahMenunggu())->toBe('restart');

    // Detak pertama membawanya pulang…
    $this->postJson('/api/bot-turnitin/lapor', ['mode' => 'penuh'], tokenVps())
        ->assertOk()->assertJson(['perintah' => 'restart']);

    // …detak berikutnya tidak lagi, supaya tidak dijalankan dua kali.
    $this->postJson('/api/bot-turnitin/lapor', ['mode' => 'penuh'], tokenVps())
        ->assertOk()->assertJson(['perintah' => null]);
});

it('hanya perintah yang terdaftar yang bisa dititipkan', function () {
    expect(LaporanVps::titipPerintah('rm -rf /'))->toBeFalse()
        ->and(LaporanVps::titipPerintah('deploy'))->toBeFalse()
        ->and(LaporanVps::perintahMenunggu())->toBeNull();

    foreach (LaporanVps::PERINTAH as $sah) {
        expect(LaporanVps::titipPerintah($sah))->toBeTrue();
    }
});

// ===================== Tampilan di Telegram =====================

it('agen Telegram melaporkan VPS yang sehat', function () {
    LaporanVps::simpan([
        'mode' => 'penuh', 'kuota' => 19, 'kuota_teks' => 'Paket Standard: 19x tersisa',
        'memori_mb' => 520, 'memori_total_mb' => 2241, 'disk_persen' => 16,
        'selesai_hari_ini' => 3, 'gagal_hari_ini' => 0,
    ]);

    $teks = AksiAgen::jalankan('vps');

    expect($teks)->toContain('Hidup')
        ->and($teks)->toContain('19x tersisa')
        ->and($teks)->toContain('3 selesai, 0 gagal')
        ->and($teks)->not->toContain('kemungkinan MATI');
});

it('laporan basi dilaporkan sebagai kemungkinan mati, bukan sehat', function () {
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 19]);

    // Majukan waktu melewati batas basi.
    \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(LaporanVps::BASI_MENIT + 5));

    expect(LaporanVps::basi())->toBeTrue()
        ->and(AksiAgen::jalankan('vps'))->toContain('kemungkinan MATI');

    \Illuminate\Support\Carbon::setTestNow();
});

it('kuota menipis diberi tanda peringatan', function () {
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 2, 'kuota_teks' => 'Paket Standard: 2x tersisa']);

    expect(AksiAgen::jalankan('vps'))->toContain('⚠️');
});

it('tanpa laporan sama sekali, agen berkata belum ada laporan', function () {
    expect(AksiAgen::jalankan('vps'))->toContain('Belum ada laporan');
});

it('perintah VPS lewat Telegram menitipkan, bukan menjalankan langsung', function () {
    $teks = AksiAgen::jalankan('vps_jeda');

    expect($teks)->toContain('dititipkan')
        ->and(LaporanVps::perintahMenunggu())->toBe('jeda');

    AksiAgen::jalankan('vps_restart');
    expect(LaporanVps::perintahMenunggu())->toBe('restart');
});

it('aksi VPS terdaftar di menu bot', function () {
    $daftar = AksiAgen::daftar();

    expect($daftar)->toHaveKeys(['vps', 'vps_jeda', 'vps_lanjut', 'vps_restart'])
        ->and($daftar['vps']['ubah'])->toBeFalse()
        ->and($daftar['vps_restart']['ubah'])->toBeTrue();
});

// ===================== Peringatan otomatis =====================

it('peringatan hanya berbunyi saat keadaan berubah', function () {
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 19, 'disk_persen' => 16, 'gagal_hari_ini' => 0]);

    // Sehat: tidak ada yang dikirim.
    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('semuanya sehat')->assertSuccessful();

    // Kuota menipis: berbunyi sekali…
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 2, 'disk_persen' => 16, 'gagal_hari_ini' => 0]);
    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('tinggal 2x')->assertSuccessful();

    // …lalu diam, walau masalahnya masih ada.
    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('masalah lama masih ada')->assertSuccessful();

    // Pulih: dikabari sekali lagi.
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 20, 'disk_persen' => 16, 'gagal_hari_ini' => 0]);
    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('Pulih')->assertSuccessful();
});

it('laporan basi memicu peringatan VPS mati', function () {
    LaporanVps::simpan(['mode' => 'penuh', 'kuota' => 19]);
    \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(LaporanVps::BASI_MENIT + 3));

    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('kemungkinan mati')->assertSuccessful();

    \Illuminate\Support\Carbon::setTestNow();
});

it('belum pernah melapor tidak dianggap alarm', function () {
    // VPS bisa saja memang belum dipasang — jangan membangunkan orang untuk itu.
    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('semuanya sehat')->assertSuccessful();
});

it('mode bukan penuh ikut diperingatkan', function () {
    LaporanVps::simpan(['mode' => 'aman', 'kuota' => 19]);

    $this->artisan('bot:pantau-vps --kering')->expectsOutputToContain('tidak dalam mode penuh')->assertSuccessful();
});

/*
 * ----------------------------------------------------------------------
 * Dua penyedia: submitin (utama) & cekplagiat (cadangan)
 *
 * 6–13 Okt 2026 submitin mati 503 selama SEMINGGU tanpa satu pun peringatan:
 * pemantau hanya tahu "bot melapor / tidak", jadi bot yang rajin melapor tapi
 * tidak bisa mengerjakan apa pun tetap terbaca sehat. Tes ini menjaga supaya
 * keadaan tiap penyedia benar-benar sampai ke Telegram.
 * ----------------------------------------------------------------------
 */

it('menyimpan keadaan kedua penyedia dari laporan VPS', function () {
    $this->postJson('/api/bot-turnitin/lapor', [
        'mode' => 'penuh',
        'penyedia' => 'cekplagiat.id',
        'koin_cadangan' => 44,
        'submitin_siap' => false,
        'submitin_pesan' => 'Form submitin tidak lengkap (layanan 503)',
        'cadangan_aktif' => true,
    ], tokenVps())->assertOk();

    $data = LaporanVps::terakhir();

    expect($data['submitin_siap'])->toBeFalse()
        ->and($data['koin_cadangan'])->toBe(44)
        ->and($data['penyedia'])->toBe('cekplagiat.id');
});

it('menyebut submitin mati dan sisa koin cadangan di pesan /vps', function () {
    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => false,
        'submitin_pesan' => 'Form submitin tidak lengkap (layanan 503)',
        'cadangan_aktif' => true,
        'koin_cadangan' => 2,
        'penyedia' => 'cekplagiat.id',
    ]);

    $pesan = (new ReflectionMethod(AksiAgen::class, 'vps'))->getClosure() ? null : null;
    $m = new ReflectionMethod(AksiAgen::class, 'vps');
    $m->setAccessible(true);
    $teks = $m->invoke(null);

    expect($teks)->toContain('submitin    : ❌')
        ->and($teks)->toContain('layanan 503')
        // Koin kurang dari sekali cek (22) harus kelihatan sebagai peringatan,
        // bukan angka polos yang mudah terlewat.
        ->and($teks)->toContain('⚠️ koin 2')
        ->and($teks)->toContain('Sedang pakai: cekplagiat.id');
});

it('menampilkan kuota submitin seperti biasa saat submitin sehat', function () {
    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => true,
        'kuota' => 19,
        'kuota_teks' => 'Paket Standard: 19x tersisa',
        'cadangan_aktif' => true,
        'koin_cadangan' => 44,
        'penyedia' => 'submitin.id',
    ]);

    $m = new ReflectionMethod(AksiAgen::class, 'vps');
    $m->setAccessible(true);
    $teks = $m->invoke(null);

    expect($teks)->toContain('submitin    : ✅')
        ->and($teks)->toContain('19x tersisa')
        ->and($teks)->toContain('cekplagiat  : ✅ koin 44')
        // Penyedia yang dipakai hanya disebut kalau BUKAN submitin.
        ->and($teks)->not->toContain('Sedang pakai');
});

it('memperingatkan saat submitin mati, lalu saat pulih', function () {
    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => false,
        'submitin_pesan' => 'Form submitin tidak lengkap (layanan 503)',
        'cadangan_aktif' => true,
        'koin_cadangan' => 44,
    ]);

    // Teks diperiksa utuh: dua expectsOutputToContain berurutan dicocokkan ke
    // baris yang berbeda, bukan ke pesan yang sama.
    expect(peringatanVps())
        ->toContain('submitin.id tidak bisa dipakai')
        ->toContain('dialihkan ke cekplagiat.id');

    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => true,
        'kuota' => 19,
        'cadangan_aktif' => true,
        'koin_cadangan' => 44,
    ]);

    expect(peringatanVps())->toContain('Pulih: submitin.id bisa dipakai lagi');
});

it('memperingatkan koin cadangan kurang untuk sekali cek', function () {
    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => true,
        'kuota' => 19,
        'cadangan_aktif' => true,
        'koin_cadangan' => 2,
    ]);

    $this->artisan('bot:pantau-vps', ['--kering' => true])
        ->expectsOutputToContain('Koin cekplagiat.id tinggal 2')
        ->assertSuccessful();
});

it('tidak berbunyi dua kali untuk sebab yang sama saat submitin mati', function () {
    // Kuota submitin 0 adalah AKIBAT layanannya mati, bukan masalah terpisah.
    LaporanVps::simpan([
        'mode' => 'penuh',
        'submitin_siap' => false,
        'submitin_pesan' => 'Form submitin tidak lengkap (layanan 503)',
        'kuota' => 0,
        'cadangan_aktif' => true,
        'koin_cadangan' => 44,
    ]);

    $this->artisan('bot:pantau-vps', ['--kering' => true])
        ->doesntExpectOutputToContain('Kuota paket submitin HABIS')
        ->assertSuccessful();
});
