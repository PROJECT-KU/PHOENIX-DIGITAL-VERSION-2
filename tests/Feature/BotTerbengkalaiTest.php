<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderUpload;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\BotTurnitin;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Pengecekan bot yang menunggu tangan admin BERHARI-HARI.
 *
 * Bot melempar pekerjaannya ke admin dalam hitungan menit, tetapi tidak ada
 * apa pun yang memaksa admin menengok panel: yang gagal Jumat sore bisa
 * mengendap sampai Senin tanpa satu pun tanda, dan yang menunggu selama itu
 * adalah pelanggan yang sudah membayar.
 */
function adminBotPanel(): User
{
    $peran = Role::create(['name' => 'uji-panel-'.uniqid(), 'description' => 'uji']);
    foreach (['view_pemesanantoko', 'edit_pemesanantoko'] as $nama) {
        $peran->permissions()->attach(Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'uji', 'description' => 'uji']
        )->id);
    }

    return User::factory()->create(['role_id' => $peran->id])->fresh();
}

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

it('kartu pantau membuka tab "perlu admin" saat ada yang menunggu tindakan', function () {
    // Sebelumnya semua daftar berbaris jadi satu: pada hari ramai, yang
    // menunggu tindakan terdorong ke bawah oleh yang sedang berjalan dan yang
    // sudah selesai — persis seperti notifikasi lonceng yang tenggelam.
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(3)]);

    Livewire::actingAs(adminBotPanel())
        ->test(\App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin::class)
        ->assertSet('tab', 'perlu');
});

it('tanpa pekerjaan yang menunggu, tab yang terbuka adalah "berjalan"', function () {
    Livewire::actingAs(adminBotPanel())
        ->test(\App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin::class)
        ->assertSet('tab', 'berjalan');
});

it('tab yang dipilih admin tidak berubah sendiri saat panel memuat ulang', function () {
    // Panel ini di-poll tiap 30 detik. Kalau tabnya dihitung ulang tiap render,
    // daftar yang sedang dibaca admin tertutup sendiri tiap setengah menit.
    unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(3)]);

    Livewire::actingAs(adminBotPanel())
        ->test(\App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin::class)
        ->call('pilihTab', 'selesai')
        ->assertSet('tab', 'selesai')
        ->call('$refresh')
        ->assertSet('tab', 'selesai');
});

it('tab karangan dari peramban dikembalikan ke "perlu", bukan menampilkan daftar kosong', function () {
    Livewire::actingAs(adminBotPanel())
        ->test(\App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin::class)
        ->call('pilihTab', 'apa-saja')
        ->assertSet('tab', 'perlu');
});

it('hanya baris tab yang sedang dibuka yang dirender', function () {
    $perlu = unggahanBotTerbengkalai(['bot_diperbarui_at' => now()->subDays(3)]);
    $jalan = unggahanBotTerbengkalai([
        'bot_status' => BotTurnitin::MENUNGGU_HASIL,
        'bot_diambil_at' => now()->subMinutes(5),
        'bot_diperbarui_at' => now()->subMinute(),
        'bot_kode' => 'SC-AAA111BBB222',
    ]);

    $nomorJalan = $jalan->order->order_number;

    // Diperiksa lewat kunci BARISNYA, bukan nomor pesanannya: nomor yang
    // terbengkalai juga disebut kartu peringatan merah di atas daftar — dan
    // kartu itu memang SENGAJA tampil di tab mana pun.
    Livewire::actingAs(adminBotPanel())
        ->test(\App\Livewire\Pages\Admin\BotTurnitin\PanelBotTurnitin::class)
        ->assertSeeHtml('bt-perlu-'.$perlu->id)
        ->assertDontSeeHtml('bt-jalan-'.$jalan->id)
        ->call('pilihTab', 'berjalan')
        ->assertSeeHtml('bt-jalan-'.$jalan->id)
        ->assertSee($nomorJalan)
        ->assertDontSeeHtml('bt-perlu-'.$perlu->id);
});

it('baris "perlu dilengkapi" tidak bisa diambil alih jadi manual', function () {
    // Bot sudah mengunggah laporan plagiasinya; yang kurang tinggal hasil lain
    // yang memang selalu dikerjakan admin. Menandainya "manual" tidak mengubah
    // satu pun pekerjaan yang tersisa — ia hanya melenyapkan barisnya dari
    // daftar, sehingga sisa pekerjaan itu kehilangan pengingat terakhirnya.
    $up = unggahanBotTerbengkalai([
        'bot_status' => BotTurnitin::PERLU_DILENGKAPI,
        'bot_kode' => 'SC-AAA111BBB222',
        'bot_diperbarui_at' => now()->subDays(2),
    ]);

    expect(BotTurnitin::ambilAlih($up))->toBeFalse()
        ->and($up->fresh()->bot_status)->toBe(BotTurnitin::PERLU_DILENGKAPI)
        ->and(BotTurnitin::perluAdmin())->toHaveCount(1);
});

it('yang gagal sebelum terkirim boleh diambil alih dan kembali ke antrean manual', function () {
    $up = unggahanBotTerbengkalai(['bot_status' => BotTurnitin::GAGAL, 'bot_kode' => null]);

    expect(BotTurnitin::ambilAlih($up))->toBeTrue();

    $up->refresh();
    expect($up->bot_status)->toBe(BotTurnitin::MANUAL)
        ->and($up->status)->toBe('menunggu');
});
