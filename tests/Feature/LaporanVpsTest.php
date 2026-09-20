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
