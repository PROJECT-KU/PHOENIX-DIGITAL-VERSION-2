<?php

use Illuminate\Support\Facades\Blade;

/**
 * Tampilan dasbor panel (admin & karyawan) dengan bahasa rupa dsb-*.
 *
 * Dasbor pengurus tidak bisa dirender utuh di tes: kuerinya memakai MONTH()
 * milik MySQL, sedangkan tes memakai SQLite. Karena itu penjaganya membaca
 * hasil kompilasi Blade — cukup untuk menangkap kelas galat yang benar-benar
 * pernah terjadi saat membangunnya.
 */
dataset('tampilan dasbor', [
    'livewire/pages/admin/dashboard.blade.php',
    'livewire/pages/admin/dashboard-karyawan.blade.php',
    'livewire/pages/admin/online-users.blade.php',
    'livewire/pages/admin/partials/dasbor-gaya.blade.php',
]);

it('hasil kompilasinya PHP yang sah', function (string $berkas) {
    // Komentar CSS yang menyebut "<x-nama-komponen>" dikompilasi Blade menjadi
    // pemanggilan komponen sungguhan, lalu halaman jatuh dengan ParseError
    // "unexpected end of file" — tanpa menyebut baris CSS penyebabnya.
    $php = Blade::compileString(file_get_contents(resource_path('views/'.$berkas)));

    expect(fn () => token_get_all($php, TOKEN_PARSE))->not->toThrow(ParseError::class);
})->with('tampilan dasbor');

it('dasbor karyawan tampil dengan kerangka baru', function () {
    $peran = \App\Models\Role::create(['name' => 'uji-dasbor-'.uniqid(), 'description' => 'uji']);
    $peran->permissions()->attach(\App\Models\Permission::firstOrCreate(
        ['name' => 'view_dashboard'],
        ['display_name' => 'view_dashboard', 'group' => 'uji', 'description' => 'uji']
    )->id);
    $user = \App\Models\User::factory()->create(['role_id' => $peran->id, 'name' => 'Dewi Lestari']);
    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id, 'jabatan' => 'Staf Operasional', 'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01', 'phone' => '081234567890', 'alamat' => 'Yogyakarta',
    ]);

    $this->actingAs($user)->get('/admin/dashboard')
        ->assertOk()
        ->assertSee('class="dsb-hero"', false)
        ->assertSee(', Dewi', false)
        ->assertSee('Gaji &amp; Pinjaman', false)
        // Salam dari jam server, bukan skrip peramban yang membaca jam laptop.
        ->assertDontSee('getGreeting', false);
});

it('waktu relatif di dasbor ditulis dalam bahasa Indonesia', function () {
    // APP_LOCALE=en: diffForHumans() tanpa locale('id') mencetak "2 hours ago".
    $sumber = file_get_contents(resource_path('views/livewire/pages/admin/dashboard.blade.php'));

    preg_match_all('/->diffForHumans\(\)/', $sumber, $semua);
    preg_match_all("/->locale\('id'\)->diffForHumans\(\)/", $sumber, $berlocale);

    expect(count($semua[0]))->toBeGreaterThan(0)
        ->and(count($berlocale[0]))->toBe(count($semua[0]));
});

it('kotak ikon huruf dilepas dari aturan ikon SVG bawaan template', function () {
    // Template membawa `.bi { width: 1em; height: 1em }` — aturan untuk ikon
    // SVG. Pada ikon HURUF, ia mengunci kotak elemen di 16px sementara glifnya
    // digambar 24px, sehingga glif meluber ke kanan-bawah dan seluruh ikon di
    // dasbor tampak meleset dari pusat ubinnya. Diukur piksel per piksel:
    // sebelum +4,75/+3,42 px, sesudah di bawah 0,4/1,5 px.
    //
    // Dijaga di SUMBER karena gejalanya halus — tidak ada yang rusak, hanya
    // "kelihatan agak turun" — dan mudah hilang saat gaya dirapikan.
    $gaya = file_get_contents(resource_path('views/livewire/pages/admin/partials/dasbor-gaya.blade.php'));

    expect($gaya)->toContain('.dsb i.bi,')
        ->and($gaya)->toContain('.bt-panel i.bi { width: auto; height: auto; }');
});
