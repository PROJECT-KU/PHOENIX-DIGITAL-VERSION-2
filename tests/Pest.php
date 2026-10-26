<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
 | Admin ber-izin akses_orcha, lengkap dengan EmployeeDetail-nya.
 |
 | Ada di sini, bukan di salah satu berkas uji, karena dipakai beberapa
 | berkas sekaligus — dan pembantu yang hidup di satu berkas hanya terbaca
 | berkas lain bila kebetulan dimuat lebih dulu. Menjalankan satu berkas
 | sendirian lalu gagal dengan "Call to undefined function" adalah kegagalan
 | yang tidak menyebut sebabnya.
 */
function adminRab(array $izin = ['akses_orcha']): \App\Models\User
{
    $role = \App\Models\Role::create(['name' => 'uji-rab-'.uniqid(), 'description' => 'Peran untuk uji RAB']);

    foreach ($izin as $nama) {
        $permission = \App\Models\Permission::firstOrCreate(
            ['name' => $nama],
            ['display_name' => $nama, 'group' => 'orcha', 'description' => 'uji']
        );
        $role->permissions()->attach($permission->id);
    }

    $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

    \App\Models\EmployeeDetail::create([
        'user_id' => $user->id,
        'jabatan' => 'Admin Uji',
        'nomor_rekening' => '1234567890',
        'tanggal_lahir' => '1995-01-01',
        'phone' => '081234567890',
        'alamat' => 'Yogyakarta',
    ]);

    return $user->fresh();
}
