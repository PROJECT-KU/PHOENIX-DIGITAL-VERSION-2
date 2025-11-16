<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\DataAkun;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // seeder role
        $this->call([
            RoleSeeder::class,
            JobSeeder::class
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $financeRole = Role::firstOrCreate(['name' => 'finance']);
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);

        // User default (admin)
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role_id' => $adminRole->id
        ]);

        User::factory()->create([
            'name' => 'Finance',
            'email' => 'finance@example.com',
            'password' => Hash::make('finance123'),
            'role_id' => $financeRole->id
        ]);

        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('manager123'),
            'role_id' => $managerRole->id
        ]);

        User::factory()->create([
            'name' => 'Supervisor',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('supervisor123'),
            'role_id' => $supervisorRole->id
        ]);

        // Generate dummy users tambahan
        User::factory(10)->create([
            'role_id' => $userRole->id
        ]);

        // Generate 100 Product
        DataAkun::factory()->count(100)->create();

        Product::factory()->count(100)->create();
    }
}
