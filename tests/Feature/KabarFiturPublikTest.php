<?php

use App\Mail\FiturPublikDitutupMail;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\FiturPublik;
use App\Support\KabarFiturPublik;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * Saat halaman toko ditutup, PEMBELI dikabari lewat surel.
 *
 * Penerimanya punya dua sumber yang saling meniadakan: alamat uji coba selama
 * JEDA_EMAIL_UJI terisi, dan data pelanggan bila dikosongkan.
 */
beforeEach(function () {
    Mail::fake();
    config(['jeda.email_uji' => null]);
});

afterEach(function () {
    Setting::query()->delete();
});

function pelanggan(string $email): Customer
{
    return Customer::create([
        'nama' => 'Pembeli '.uniqid(),
        'no_hp' => '0812'.random_int(10000000, 99999999),
        'email' => $email,
    ]);
}

function adminPublik(): User
{
    $peran = Role::create(['name' => 'uji-pub-'.uniqid(), 'description' => 'Peran uji']);

    foreach (['view_jeda_layanan', 'manage_jeda_layanan'] as $nama) {
        $p = Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'jasa', 'description' => 'uji']
        );
        $peran->permissions()->attach($p->id);
    }

    $user = User::factory()->create(['role_id' => $peran->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Admin Uji', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}

it('selama alamat uji terisi, pelanggan sungguhan tidak dikirimi apa pun', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    pelanggan('pembeli.a@contoh.test');
    pelanggan('pembeli.b@contoh.test');

    expect(KabarFiturPublik::penerima())->toBe(['bertojunikrisnanto@gmail.com'])
        ->and(KabarFiturPublik::modeUji())->toBeTrue();
});

it('bila alamat uji dikosongkan, penerimanya diambil dari data pelanggan', function () {
    pelanggan('pembeli.a@contoh.test');
    pelanggan('pembeli.b@contoh.test');

    expect(KabarFiturPublik::penerima())
        ->toContain('pembeli.a@contoh.test')
        ->toContain('pembeli.b@contoh.test')
        ->and(KabarFiturPublik::modeUji())->toBeFalse();
});

it('pelanggan tanpa surel sah tidak ikut dihitung', function () {
    pelanggan('sah@contoh.test');
    Customer::create(['nama' => 'Tanpa surel', 'no_hp' => '081299999999', 'email' => null]);

    expect(KabarFiturPublik::penerima())->toBe(['sah@contoh.test'])
        ->and(KabarFiturPublik::jumlahPenerima())->toBe(1);
});

it('surel terkirim saat halaman ditutup dari panel', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog');

    Mail::assertSent(FiturPublikDitutupMail::class, function ($mail) {
        return $mail->ditutup === true
            && $mail->namaHalaman === 'Blog'
            && $mail->hasBcc('bertojunikrisnanto@gmail.com');
    });
});

it('pelanggan tidak saling melihat alamat surelnya', function () {
    pelanggan('satu@contoh.test');
    pelanggan('dua@contoh.test');

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog');

    // Mail::to([...]) menaruh semua alamat di kolom To — itu kebocoran data
    // pelanggan, dan justru cara paling mudah tanpa disadari.
    Mail::assertSent(FiturPublikDitutupMail::class, function ($mail) {
        return $mail->hasBcc('satu@contoh.test')
            && $mail->hasBcc('dua@contoh.test')
            && ! $mail->hasTo('satu@contoh.test')
            && ! $mail->hasTo('dua@contoh.test');
    });
});

it('surel juga terkirim saat halaman dibuka kembali', function () {
    config(['jeda.email_uji' => 'bertojunikrisnanto@gmail.com']);
    FiturPublik::setel('blog', true);

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog');

    Mail::assertSent(FiturPublikDitutupMail::class, fn ($mail) => $mail->ditutup === false);
});

it('surel membawa versi teks di samping HTML-nya', function () {
    config(['jeda.email_uji' => 'uji@contoh.test']);

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog');

    Mail::assertSent(FiturPublikDitutupMail::class, function ($mail) {
        $dirakit = $mail->build();

        return $dirakit->textView === 'emails.fitur-publik-ditutup-teks';
    });
});

it('waktu mulai dicatat dan dibersihkan saat dibuka lagi', function () {
    FiturPublik::setel('blog', true, null, '2026-09-10 09:00:00');

    expect(FiturPublik::mulai('blog'))->not->toBeNull()
        ->and(FiturPublik::sampai('blog')->format('Y-m-d H:i'))->toBe('2026-09-10 09:00');

    FiturPublik::setel('blog', false);

    expect(FiturPublik::mulai('blog'))->toBeNull()
        ->and(FiturPublik::sampai('blog'))->toBeNull();
});

it('gagal kirim surel tidak membatalkan penutupan halamannya', function () {
    config(['jeda.email_uji' => 'uji@contoh.test']);
    Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP mati'));

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->call('alihkanFitur', 'blog');

    expect(FiturPublik::ditutup('blog'))->toBeTrue();
});

it('panel menyebutkan berapa pelanggan yang akan dikirimi', function () {
    pelanggan('satu@contoh.test');
    pelanggan('dua@contoh.test');

    // Angkanya harus terlihat SEBELUM tombolnya ditekan.
    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->assertSee('mengirim surel ke 2 pelanggan');
});

it('saat mode uji, panel menegaskan pelanggan belum dikirimi apa pun', function () {
    config(['jeda.email_uji' => 'uji@contoh.test']);
    pelanggan('satu@contoh.test');

    Livewire::actingAs(adminPublik())
        ->test(\App\Livewire\Pages\Admin\JedaLayanan\JedaLayananIndex::class)
        ->assertSee('Kabar surel masih ke alamat uji coba')
        ->assertSee('Pelanggan sungguhan belum dikirimi apa pun');
});
