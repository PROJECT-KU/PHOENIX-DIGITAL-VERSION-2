<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Task berulang: salinan periode berikutnya.
 *
 * Dilaporkan dari server: task bulanan 21 Agu–19 Sep tidak pernah memunculkan
 * periode 21 Sep–19 Okt. Penyebabnya syarat lama "tenggat BERIKUTNYA sudah
 * lewat" — salinannya baru dibuat pada 19 Okt, tepat di hari jatuh temponya.
 */
function karyawanTask(): User
{
    $peran = \App\Models\Role::create(['name' => 'uji-task-'.Str::random(5), 'description' => 'uji']);

    return User::factory()->create(['role_id' => $peran->id, 'status' => 'active']);
}

function taskBerulang(array $isian = []): Task
{
    return Task::create(array_merge([
        'id' => Str::uuid(),
        'group_id' => Str::uuid(),
        'user_id' => karyawanTask()->id,
        'periode_bulan' => 8,
        'periode_tahun' => 2026,
        'nama' => 'Laporan bulanan',
        'bobot' => 2,
        'deadline_mulai' => '2026-08-21',
        'deadline_selesai' => '2026-09-19',
        'progress' => 'belum',
        'ulang' => 'bulanan',
    ], $isian));
}

afterEach(fn () => Carbon::setTestNow());

it('salinan dibuat begitu periode berjalan berakhir, bukan sebulan kemudian', function () {
    $induk = taskBerulang();

    // Sehari sebelum tenggat: belum waktunya.
    Carbon::setTestNow('2026-09-18 06:30:00');
    $this->artisan('tasks:salin-berulang')->assertSuccessful();
    expect(Task::count())->toBe(1);

    // Hari tenggatnya: salinan periode berikutnya muncul.
    Carbon::setTestNow('2026-09-19 06:30:00');
    $this->artisan('tasks:salin-berulang')->assertSuccessful();

    $salinan = Task::where('id', '!=', $induk->id)->first();
    expect($salinan)->not->toBeNull()
        ->and($salinan->deadline_mulai->toDateString())->toBe('2026-09-21')
        ->and($salinan->deadline_selesai->toDateString())->toBe('2026-10-19')
        ->and($salinan->progress)->toBe('belum')
        ->and($salinan->ulang)->toBe('bulanan');
});

it('tanggal mulai digeser satu periode, bukan dihitung mundur dari panjang harinya', function () {
    // 21 Agu–19 Sep = 29 hari. Hitung mundur 29 hari dari 19 Okt memberi
    // 20 Sep — sehari meleset, karena Agustus lebih panjang dari September.
    taskBerulang();
    Carbon::setTestNow('2026-09-19 06:30:00');
    $this->artisan('tasks:salin-berulang')->assertSuccessful();

    expect(Task::whereDate('deadline_selesai', '2026-10-19')->first()->deadline_mulai->toDateString())
        ->toBe('2026-09-21');
});

it('tongkat estafet pindah ke salinan supaya rantainya tidak beranak-pinak', function () {
    $induk = taskBerulang();
    Carbon::setTestNow('2026-09-19 06:30:00');

    $this->artisan('tasks:salin-berulang')->assertSuccessful();
    expect($induk->fresh()->ulang)->toBe('tidak');

    // Dijalankan lagi di hari yang sama: tidak ada salinan kedua.
    $this->artisan('tasks:salin-berulang')->assertSuccessful();
    expect(Task::count())->toBe(2);

    // Hari-hari berikutnya juga tidak, sampai periode salinan itu berakhir.
    Carbon::setTestNow('2026-10-01 06:30:00');
    $this->artisan('tasks:salin-berulang')->assertSuccessful();
    expect(Task::count())->toBe(2);

    // Begitu 19 Okt tiba, giliran salinan itu yang beranak.
    Carbon::setTestNow('2026-10-19 06:30:00');
    $this->artisan('tasks:salin-berulang')->assertSuccessful();
    expect(Task::count())->toBe(3)
        ->and(Task::whereDate('deadline_selesai', '2026-11-19')->exists())->toBeTrue();
});

it('task yang lama terbengkalai menghasilkan satu salinan untuk periode berjalan', function () {
    // Tenggatnya lewat tiga bulan; jangan menumpuk tiga salinan yang semuanya
    // sudah telat — cukup satu untuk periode yang sedang berjalan.
    taskBerulang(['deadline_mulai' => '2026-05-21', 'deadline_selesai' => '2026-06-19']);
    Carbon::setTestNow('2026-09-19 06:30:00');

    $this->artisan('tasks:salin-berulang')->assertSuccessful();

    $salinan = Task::where('nama', 'Laporan bulanan')->where('ulang', 'bulanan')->get();
    expect($salinan)->toHaveCount(1)
        ->and($salinan->first()->deadline_selesai->toDateString())->toBe('2026-10-19')
        ->and($salinan->first()->deadline_mulai->toDateString())->toBe('2026-09-21');
});

it('task mingguan disalin per minggu', function () {
    taskBerulang(['ulang' => 'mingguan', 'deadline_mulai' => '2026-09-14', 'deadline_selesai' => '2026-09-18']);
    Carbon::setTestNow('2026-09-19 06:30:00');

    $this->artisan('tasks:salin-berulang')->assertSuccessful();

    $salinan = Task::where('ulang', 'mingguan')->whereDate('deadline_selesai', '2026-09-25')->first();
    expect($salinan)->not->toBeNull()
        ->and($salinan->deadline_mulai->toDateString())->toBe('2026-09-21');
});

it('tenggat akhir bulan tidak melompat ke bulan berikutnya', function () {
    taskBerulang(['deadline_mulai' => '2026-01-01', 'deadline_selesai' => '2026-01-31']);
    Carbon::setTestNow('2026-01-31 06:30:00');

    $this->artisan('tasks:salin-berulang')->assertSuccessful();

    // Februari tidak punya tanggal 31; jangan meluber ke 3 Maret.
    expect(Task::where('ulang', 'bulanan')->where('id', '!=', Task::first()->id)->first()->deadline_selesai->toDateString())
        ->toBe('2026-02-28');
});

it('--kering tidak menyalin apa pun', function () {
    taskBerulang();
    Carbon::setTestNow('2026-09-19 06:30:00');

    $this->artisan('tasks:salin-berulang', ['--kering' => true])->assertSuccessful();

    expect(Task::count())->toBe(1);
});
