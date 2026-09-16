<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderUpload;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\BotTurnitin;
use Illuminate\Support\Str;

/**
 * Pengecekan bot yang menunggu tangan admin BERHARI-HARI.
 *
 * Bot melempar pekerjaannya ke admin dalam hitungan menit, tetapi tidak ada
 * apa pun yang memaksa admin menengok panel: yang gagal Jumat sore bisa
 * mengendap sampai Senin tanpa satu pun tanda, dan yang menunggu selama itu
 * adalah pelanggan yang sudah membayar.
 */
function unggahanBotTerbengkalai(array $isian = []): OrderUpload
{
    $order = Order::create([
        'id' => Str::uuid(),
        'order_number' => 'INV-BOT-'.Str::upper(Str::random(5)),
        'customer_id' => Customer::create([
            'nama' => 'Pembeli Jasa',
            'no_hp' => '0812'.random_int(10000000, 99999999),
            'email' => 'bot'.uniqid().'@contoh.test',
        ])->id,
        'subtotal' => 5000, 'total' => 5000, 'unique_code' => 0,
        'status' => 'paid', 'payment_method' => 'qris_dinamis', 'expired_at' => now()->addDay(),
    ]);

    return OrderUpload::create(array_merge([
        'order_id' => $order->id,
        'jenis' => 'plagiasi',
        'nama_asli' => 'naskah.docx',
        'status' => 'diproses',
        'bot_status' => BotTurnitin::GAGAL,
        'bot_diperbarui_at' => now()->subHours(2),
    ], $isian));
}

it('yang baru gagal beberapa jam belum dianggap terbengkalai', function () {
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subHours(BotTurnitin::TERBENGKALAI_JAM - 2)]);

    expect(BotTurnitin::perluAdmin())->toHaveCount(1)
        ->and(BotTurnitin::terbengkalai())->toHaveCount(0);
});

it('yang menunggu lebih dari sehari ditandai terbengkalai', function () {
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(5)]);

    $terbengkalai = BotTurnitin::terbengkalai();

    expect($terbengkalai)->toHaveCount(1)
        ->and(BotTurnitin::lamaMenungguJam($terbengkalai->first()))->toBeGreaterThan(24);
});

it('perintah pengingat mengirim SATU ringkasan ke admin, bukan satu per pengecekan', function () {
    // Lima pengecekan terbengkalai tidak butuh lima lonceng; yang perlu
    // diketahui hanya "ada yang menunggu, terlama sekian hari".
    $peran = Role::create(['name' => 'uji-bot-'.uniqid(), 'description' => 'uji']);
    $peran->permissions()->attach(Permission::firstOrCreate(
        ['name' => 'view_pemesanantoko'],
        ['display_name' => 'view_pemesanantoko', 'group' => 'uji', 'description' => 'uji']
    )->id);
    User::factory()->create(['role_id' => $peran->id]);

    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(5)]);
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(3)]);

    $this->artisan('bot:ingatkan-terbengkalai')->assertSuccessful();

    expect(\Illuminate\Support\Facades\DB::table('notifications')->count())->toBe(1);

    $isi = \Illuminate\Support\Facades\DB::table('notifications')->first()->data;
    $isi = is_array($isi) ? $isi : json_decode($isi, true);

    expect($isi['body'])->toContain('2 pengecekan')
        ->and($isi['body'])->toContain('5 hari');
});

it('tidak mengirim apa pun saat tidak ada yang terbengkalai', function () {
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subHour()]);

    $this->artisan('bot:ingatkan-terbengkalai')->assertSuccessful();

    expect(\Illuminate\Support\Facades\DB::table('notifications')->count())->toBe(0);
});

it('--dry-run tidak mengirim notifikasi', function () {
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(4)]);

    $this->artisan('bot:ingatkan-terbengkalai', ['--dry-run' => true])->assertSuccessful();

    expect(\Illuminate\Support\Facades\DB::table('notifications')->count())->toBe(0);
});

it('pengingatnya terjadwal, bukan hanya perintah yang menunggu dipanggil', function () {
    // Tanpa baris jadwal, perintahnya ada tapi tidak pernah berjalan — dan
    // gejalanya persis sama dengan tidak ada fiturnya sama sekali.
    expect(file_get_contents(base_path('routes/console.php')))
        ->toContain("\$jadwalkan('bot:ingatkan-terbengkalai')");
});
